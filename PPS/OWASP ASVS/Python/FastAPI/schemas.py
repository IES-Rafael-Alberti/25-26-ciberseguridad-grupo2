from pydantic import BaseModel, EmailStr, Field, ConfigDict, field_validator
from datetime import datetime

from . import utils

class UsuarioBase(BaseModel):
    nombre: str = Field(min_length=1, max_length=80)
    apellidos: str = Field(min_length=1, max_length=120)
    email: EmailStr

    @field_validator("nombre", "apellidos")
    @classmethod
    def strip_names(cls, v: str) -> str:
        v = (v or "").strip()
        if not v:
            raise ValueError("Campo obligatorio")
        return v

class UsuarioCreate(UsuarioBase):
    password: str = Field(min_length=utils.PASSWORD_MIN_LENGTH, max_length=utils.PASSWORD_MAX_LENGTH)

    @field_validator("password")
    @classmethod
    def password_policy(cls, v: str) -> str:
        utils.validate_password_policy(v)
        return v

class UsuarioUpdate(UsuarioBase):
    password: str = Field(min_length=utils.PASSWORD_MIN_LENGTH, max_length=utils.PASSWORD_MAX_LENGTH)

    @field_validator("password")
    @classmethod
    def password_policy(cls, v: str) -> str:
        utils.validate_password_policy(v)
        return v

class UsuarioResponse(UsuarioBase):
    id: int
    model_config = ConfigDict(from_attributes=True)

class UsuarioLogin(BaseModel):
    email: EmailStr
    password: str = Field(min_length=1, max_length=utils.PASSWORD_MAX_LENGTH)


class LoginResponse(BaseModel):
    mensaje: str
    usuario: UsuarioResponse
    token: str
    expiration: datetime

    model_config = ConfigDict(from_attributes=True)


