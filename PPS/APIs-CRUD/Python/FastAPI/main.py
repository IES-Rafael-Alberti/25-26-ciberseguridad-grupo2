from fastapi import FastAPI, HTTPException, Depends
from sqlalchemy.orm import Session
from typing import List
from fastapi import status

from . import models, schemas, utils
from .database import engine, SessionLocal

# Crear tablas
models.Base.metadata.create_all(bind=engine)

app = FastAPI(title="CRUD de Usuarios con Seguridad (bcrypt)")

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
def obtener_usuarios(db: Session = Depends(get_db)):
    return db.query(models.Usuario).all()


# Obtener usuario por ID
@app.get("/usuarios/{id}", response_model=schemas.UsuarioResponse)
def obtener_usuario(id: int, db: Session = Depends(get_db)):
    usuario = db.query(models.Usuario).filter(models.Usuario.id == id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    return usuario


# Actualizar usuario
@app.put("/usuarios/{id}", response_model=schemas.UsuarioResponse)
def actualizar_usuario(id: int, datos: schemas.UsuarioUpdate, db: Session = Depends(get_db)):
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
def eliminar_usuario(id: int, db: Session = Depends(get_db)):
    usuario = db.query(models.Usuario).filter(models.Usuario.id == id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")

    db.delete(usuario)
    db.commit()
    return {"message": f"Usuario con id={id} eliminado correctamente"}



@app.post("/usuarios/login", response_model=schemas.LoginResponse)
def login(usuario_login: schemas.UsuarioLogin, db: Session = Depends(get_db)):
    # Buscar usuario por email
    usuario = db.query(models.Usuario).filter(models.Usuario.email == usuario_login.email).first()
    if not usuario:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales inválidas (email no encontrado)"
        )

    # Verificar contraseña
    if not utils.verify_password(usuario_login.password, usuario.password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales inválidas (contraseña incorrecta)"
        )

    return {
        "mensaje": "Inicio de sesión exitoso",
        "usuario": usuario
    }