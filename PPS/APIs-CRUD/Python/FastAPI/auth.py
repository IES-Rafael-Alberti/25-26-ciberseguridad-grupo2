from dotenv import load_dotenv
load_dotenv()

from fastapi import APIRouter, Depends, HTTPException, status, Request
from fastapi.responses import RedirectResponse
import requests
from sqlalchemy.orm import Session
import os
from jose import jwt
from datetime import datetime, timedelta
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials

from . import models, schemas, utils
from .database import SessionLocal

# --- GitHub OAuth Settings ---
GITHUB_CLIENT_ID = os.environ.get("GITHUB_CLIENT_ID")
GITHUB_CLIENT_SECRET = os.environ.get("GITHUB_CLIENT_SECRET")
GITHUB_REDIRECT_URI = "http://localhost:8000/auth/github/callback"

# --- JWT Settings ---
SECRET_KEY = os.environ.get("JWT_SECRET_KEY")
ACCESS_TOKEN_EXPIRE_MINUTES = int(os.environ.get("ACCESS_TOKEN_EXPIRE_MINUTES", "60"))

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
    token = credentials.credentials
    try:
        payload = utils.decode_access_token(token, SECRET_KEY)
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token expirado")
    except Exception:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token inválido")

    user_id = payload.get("sub") or payload.get("user_id")
    if user_id is None:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token inválido (sin sub)")

    usuario = db.query(models.Usuario).filter(models.Usuario.id == int(user_id)).first()
    if not usuario:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Usuario no encontrado")

    return usuario

@router.get("/github/login")
async def github_login():
    """
    Redirects the user to GitHub for authentication.
    """
    print(f"GITHUB_CLIENT_ID: {GITHUB_CLIENT_ID}")
    return RedirectResponse(
        f"https://github.com/login/oauth/authorize?client_id={GITHUB_CLIENT_ID}&redirect_uri={GITHUB_REDIRECT_URI}&scope=user:email"
    )

@router.get("/github/callback")
async def github_callback(code: str, db: Session = Depends(get_db)):
    """
    Handles the callback from GitHub after authentication.
    """
    # Exchange the code for an access token
    token_url = "https://github.com/login/oauth/access_token"
    token_data = {
        "client_id": GITHUB_CLIENT_ID,
        "client_secret": GITHUB_CLIENT_SECRET,
        "code": code,
        "redirect_uri": GITHUB_REDIRECT_URI,
    }
    headers = {"Accept": "application/json"}
    token_r = requests.post(token_url, data=token_data, headers=headers)
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
    user_r = requests.get(user_url, headers=headers)
    user_r.raise_for_status()
    user_json = user_r.json()
    
    github_email = user_json.get("email")
    if not github_email:
        # If email is private, get it from a different endpoint
        try:
            emails_r = requests.get("https://api.github.com/user/emails", headers=headers)
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

    return {"access_token": jwt_token, "token_type": "bearer"}
