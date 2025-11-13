import bcrypt
import jwt
from datetime import datetime, timedelta
from typing import Optional, Dict, Any


def hash_password(password: str) -> str:
    """Devuelve el hash seguro de una contraseña."""
    salt = bcrypt.gensalt()
    hashed = bcrypt.hashpw(password.encode('utf-8'), salt)
    return hashed.decode('utf-8')


def verify_password(password: str, hashed_password: str) -> bool:
    """Verifica si la contraseña coincide con su hash."""
    return bcrypt.checkpw(password.encode('utf-8'), hashed_password.encode('utf-8'))


def create_access_token(data: Dict[str, Any], secret_key: str, expires_minutes: int = 60, algorithm: str = "HS256") -> str:
    """Crea un JWT con los datos provistos y expiración en minutos."""
    to_encode = data.copy()
    expire = datetime.utcnow() + timedelta(minutes=expires_minutes)
    to_encode.update({"exp": expire})
    token = jwt.encode(to_encode, secret_key, algorithm=algorithm)
    # PyJWT >=2.0 devuelve str
    return token


def decode_access_token(token: str, secret_key: str, algorithms: Optional[list] = None) -> Dict[str, Any]:
    """Decodifica y valida un JWT. Lanza jwt exceptions en caso de error.

    Devuelve el payload si es válido.
    """
    if algorithms is None:
        algorithms = ["HS256"]
    payload = jwt.decode(token, secret_key, algorithms=algorithms)
    return payload
