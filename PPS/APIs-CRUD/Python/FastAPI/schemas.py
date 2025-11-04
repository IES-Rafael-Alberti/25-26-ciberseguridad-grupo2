from pydantic import BaseModel, EmailStr

class UsuarioBase(BaseModel):
    nombre: str
    apellidos: str
    email: EmailStr

class UsuarioCreate(UsuarioBase):
    password: str

class UsuarioUpdate(UsuarioBase):
    password: str

class UsuarioResponse(UsuarioBase):
    id: int

    class Config:
        orm_mode = True

class UsuarioLogin(BaseModel):
    email: EmailStr
    password: str


class LoginResponse(BaseModel):
    mensaje: str
    usuario: UsuarioResponse

