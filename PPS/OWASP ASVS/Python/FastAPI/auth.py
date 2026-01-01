from dotenv import load_dotenv
load_dotenv()

from fastapi import APIRouter, Depends, HTTPException, status, Request
from fastapi.responses import RedirectResponse, JSONResponse
import requests
from sqlalchemy.orm import Session
import os
import jwt
import secrets
import time
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from typing import Optional

from . import models, schemas, utils
from .database import SessionLocal

# --- GitHub OAuth Settings ---
GITHUB_CLIENT_ID = os.environ.get("GITHUB_CLIENT_ID")
GITHUB_CLIENT_SECRET = os.environ.get("GITHUB_CLIENT_SECRET")
GITHUB_REDIRECT_URI = "http://localhost:8000/auth/github/callback"

OAUTH_STATE_COOKIE = "oauth_state"
OAUTH_STATE_TTL_SECONDS = int(os.environ.get("OAUTH_STATE_TTL_SECONDS", "300"))
OAUTH_REQUEST_TIMEOUT_SECONDS = float(os.environ.get("OAUTH_REQUEST_TIMEOUT_SECONDS", "10"))
OAUTH_COOKIE_SECURE = os.environ.get("OAUTH_COOKIE_SECURE", "false").lower() == "true"

# --- JWT Settings ---
SECRET_KEY = os.environ.get("JWT_SECRET_KEY")
ACCESS_TOKEN_EXPIRE_MINUTES = int(os.environ.get("ACCESS_TOKEN_EXPIRE_MINUTES", "60"))

# Revocación simple en memoria (válida para un solo proceso)
TOKEN_BLACKLIST: dict[str, int] = {}


def _cleanup_blacklist(now_ts: int) -> None:
    expired = [jti for jti, exp in TOKEN_BLACKLIST.items() if exp <= now_ts]
    for jti in expired:
        TOKEN_BLACKLIST.pop(jti, None)


def _is_revoked(payload: dict) -> bool:
    jti = payload.get("jti")
    exp = payload.get("exp")
    if not jti or not exp:
        return False
    now_ts = int(time.time())
    _cleanup_blacklist(now_ts)
    return jti in TOKEN_BLACKLIST

router = APIRouter(
    prefix="/auth",
    tags=["Authentication"],
)

security = HTTPBearer()

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

def get_current_active_user(credentials: HTTPAuthorizationCredentials = Depends(security), db: Session = Depends(get_db)):
    """Dependencia que valida el token Bearer y devuelve el usuario actual."""
    if not SECRET_KEY:
        raise HTTPException(status_code=500, detail="JWT no configurado")

    token = credentials.credentials
    try:
        payload = utils.decode_access_token(token, SECRET_KEY)
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token expirado")
    except Exception:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token inválido")

    if _is_revoked(payload):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token revocado")

    user_id = payload.get("sub") or payload.get("user_id")
    if user_id is None:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token inválido (sin sub)")

    usuario = db.query(models.Usuario).filter(models.Usuario.id == int(user_id)).first()
    if not usuario:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Usuario no encontrado")

    return usuario


@router.post("/logout")
async def logout(credentials: HTTPAuthorizationCredentials = Depends(security)):
    """Invalida el token actual (best-effort, blacklist en memoria)."""
    if not SECRET_KEY:
        raise HTTPException(status_code=500, detail="JWT no configurado")

    token = credentials.credentials
    try:
        payload = utils.decode_access_token(token, SECRET_KEY)
    except jwt.ExpiredSignatureError:
        # Ya expirado: considerarlo finalizado
        return {"detail": "Sesión finalizada"}
    except Exception:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token inválido")

    jti = payload.get("jti")
    exp = payload.get("exp")
    if jti and exp:
        TOKEN_BLACKLIST[str(jti)] = int(exp)

    return {"detail": "Sesión finalizada"}

@router.get("/github/login")
async def github_login():
    """
    Redirects the user to GitHub for authentication.
    """
    if not GITHUB_CLIENT_ID:
        raise HTTPException(status_code=500, detail="OAuth no configurado")

    state = secrets.token_urlsafe(32)
    redirect = RedirectResponse(
        f"https://github.com/login/oauth/authorize"
        f"?client_id={GITHUB_CLIENT_ID}"
        f"&redirect_uri={GITHUB_REDIRECT_URI}"
        f"&scope=user:email"
        f"&state={state}"
    )
    redirect.set_cookie(
        key=OAUTH_STATE_COOKIE,
        value=state,
        max_age=OAUTH_STATE_TTL_SECONDS,
        httponly=True,
        samesite="lax",
        secure=OAUTH_COOKIE_SECURE,
    )
    utils.security_log(event="oauth_github_login", request=None, user_id=None, extra=None)
    return redirect

@router.get("/github/callback")
async def github_callback(request: Request, code: str, state: Optional[str] = None, db: Session = Depends(get_db)):
    """
    Handles the callback from GitHub after authentication.
    """
    if not (GITHUB_CLIENT_ID and GITHUB_CLIENT_SECRET):
        raise HTTPException(status_code=500, detail="OAuth no configurado")

    cookie_state = request.cookies.get(OAUTH_STATE_COOKIE)
    if not state or not cookie_state or state != cookie_state:
        utils.security_log(event="oauth_github_callback_failed", request=request, user_id=None, extra={"reason": "invalid_state"})
        raise HTTPException(status_code=400, detail="OAuth state inválido")

    # Exchange the code for an access token
    token_url = "https://github.com/login/oauth/access_token"
    token_data = {
        "client_id": GITHUB_CLIENT_ID,
        "client_secret": GITHUB_CLIENT_SECRET,
        "code": code,
        "redirect_uri": GITHUB_REDIRECT_URI,
    }
    headers = {"Accept": "application/json"}
    token_r = requests.post(token_url, data=token_data, headers=headers, timeout=OAUTH_REQUEST_TIMEOUT_SECONDS)
    token_r.raise_for_status()
    token_json = token_r.json()
    github_token = token_json.get("access_token")

    if not github_token:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Could not get access token from GitHub",
        )

    # Get user info from GitHub
    user_url = "https://api.github.com/user"
    headers = {"Authorization": f"token {github_token}"}
    user_r = requests.get(user_url, headers=headers, timeout=OAUTH_REQUEST_TIMEOUT_SECONDS)
    user_r.raise_for_status()
    user_json = user_r.json()
    
    github_email = user_json.get("email")
    if not github_email:
        # If email is private, get it from a different endpoint
        try:
            emails_r = requests.get("https://api.github.com/user/emails", headers=headers, timeout=OAUTH_REQUEST_TIMEOUT_SECONDS)
            emails_r.raise_for_status()
            emails = emails_r.json()
            primary_email = next((email["email"] for email in emails if email["primary"]), None)
            if not primary_email:
                raise HTTPException(status_code=400, detail="GitHub email not available")
            github_email = primary_email
        except requests.exceptions.HTTPError as e:
            raise HTTPException(status_code=400, detail=f"No se pudo obtener email de GitHub: {str(e)}")


    # Check if user exists in the database
    user = db.query(models.Usuario).filter(models.Usuario.email == github_email).first()

    if not user:
        # Create a new user
        new_user = models.Usuario(
            email=github_email,
            nombre=user_json.get("name", ""),
            apellidos="", # GitHub doesn't provide last name
            password=utils.hash_password(os.urandom(16).hex()) # Generate a random password
        )
        db.add(new_user)
        db.commit()
        db.refresh(new_user)
        user = new_user

    # Create a JWT token
    jwt_data = {"sub": str(user.id)}
    jwt_token = utils.create_access_token(jwt_data, SECRET_KEY, expires_minutes=ACCESS_TOKEN_EXPIRE_MINUTES)

    utils.security_log(event="oauth_github_callback_success", request=request, user_id=str(user.id), extra={"email": github_email})

    response = JSONResponse(content={"access_token": jwt_token, "token_type": "bearer"})
    response.delete_cookie(OAUTH_STATE_COOKIE)
    return response
