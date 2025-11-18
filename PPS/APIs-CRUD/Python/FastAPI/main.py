from dotenv import load_dotenv
# Cargar variables de entorno desde .env
load_dotenv()

from fastapi import FastAPI, HTTPException, Depends, Request
from sqlalchemy.orm import Session
from typing import List
from fastapi import status
import os
from datetime import datetime, timedelta
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
import jwt
from slowapi import Limiter
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded


from . import models, schemas, utils, auth
from .database import engine, SessionLocal
from .auth import get_current_active_user

# Crear tablas
models.Base.metadata.create_all(bind=engine)

app = FastAPI(title="CRUD de Usuarios con Seguridad (bcrypt)")
app.include_router(auth.router)

# Rate limiter para prevenir ataques de fuerza bruta
limiter = Limiter(key_func=get_remote_address)
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, lambda request, exc: HTTPException(status_code=429, detail="Demasiadas solicitudes. Intenta más tarde."))

# Seguridad: bearer token
security = HTTPBearer()

# Ajustes JWT (se deben proporcionar mediante variables de entorno)
SECRET_KEY = os.environ.get("JWT_SECRET_KEY")
if not SECRET_KEY:
    raise RuntimeError("JWT_SECRET_KEY no está definido. Configure la variable de entorno JWT_SECRET_KEY y reinicie la aplicación.")

ACCESS_TOKEN_EXPIRE_MINUTES = int(os.environ.get("ACCESS_TOKEN_EXPIRE_MINUTES", "60"))

# Dependencia para sesión de BD
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


# Crear usuario
@app.post("/usuarios", response_model=schemas.UsuarioResponse, status_code=201)
def crear_usuario(usuario: schemas.UsuarioCreate, db: Session = Depends(get_db)):
    if not usuario.password or not usuario.email:
        raise HTTPException(status_code=400, detail="Email y password son obligatorios")

    db_usuario = db.query(models.Usuario).filter(models.Usuario.email == usuario.email).first()
    if db_usuario:
        raise HTTPException(status_code=400, detail="El email ya está registrado")

    hashed_pw = utils.hash_password(usuario.password)
    nuevo_usuario = models.Usuario(
        nombre=usuario.nombre,
        apellidos=usuario.apellidos,
        email=usuario.email,
        password=hashed_pw
    )

    db.add(nuevo_usuario)
    db.commit()
    db.refresh(nuevo_usuario)
    return nuevo_usuario


# Obtener todos los usuarios
@app.get("/usuarios", response_model=List[schemas.UsuarioResponse])
def obtener_usuarios(db: Session = Depends(get_db), current_user: models.Usuario = Depends(get_current_active_user)):
    # current_user dependency will be replaced by get_current_user when protecting endpoints
    return db.query(models.Usuario).all()


# Obtener usuario por ID
@app.get("/usuarios/{id}", response_model=schemas.UsuarioResponse)
def obtener_usuario(id: int, db: Session = Depends(get_db), current_user: models.Usuario = Depends(get_current_active_user)):
    usuario = db.query(models.Usuario).filter(models.Usuario.id == id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    return usuario


# Actualizar usuario
@app.put("/usuarios/{id}", response_model=schemas.UsuarioResponse)
def actualizar_usuario(id: int, datos: schemas.UsuarioUpdate, db: Session = Depends(get_db), current_user: models.Usuario = Depends(get_current_active_user)):
    usuario = db.query(models.Usuario).filter(models.Usuario.id == id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")

    # Cifrar nueva contraseña
    hashed_pw = utils.hash_password(datos.password)
    usuario.nombre = datos.nombre
    usuario.apellidos = datos.apellidos
    usuario.email = datos.email
    usuario.password = hashed_pw

    db.commit()
    db.refresh(usuario)
    return usuario


#  Eliminar usuario
@app.delete("/usuarios/{id}", status_code=200)
def eliminar_usuario(id: int, db: Session = Depends(get_db), current_user: models.Usuario = Depends(get_current_active_user)):
    usuario = db.query(models.Usuario).filter(models.Usuario.id == id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")

    db.delete(usuario)
    db.commit()
    return {"message": f"Usuario con id={id} eliminado correctamente"}

@app.post("/usuarios/login", response_model=schemas.LoginResponse)
@limiter.limit("5/minute")
def login(request: Request, usuario_login: schemas.UsuarioLogin, db: Session = Depends(get_db)):
    # Buscar usuario por email
    usuario = db.query(models.Usuario).filter(models.Usuario.email == usuario_login.email).first()
    if not usuario:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales inválidas"
        )

    # Verificar contraseña
    if not utils.verify_password(usuario_login.password, usuario.password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales inválidas"
        )

    # generar token JWT
    token = utils.create_access_token({"sub": str(usuario.id), "email": usuario.email}, SECRET_KEY, expires_minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    expiration = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)

    return {
        "mensaje": "Inicio de sesión exitoso",
        "usuario": usuario,
        "token": token,
        "expiration": expiration
    }