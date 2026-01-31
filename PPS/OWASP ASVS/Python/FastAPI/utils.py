import bcrypt
import jwt
from datetime import datetime, timedelta
from typing import Optional, Dict, Any
import logging
from logging.handlers import TimedRotatingFileHandler
import os
import re
import secrets


PASSWORD_MIN_LENGTH = int(os.environ.get("PASSWORD_MIN_LENGTH", "8"))
PASSWORD_MAX_LENGTH = int(os.environ.get("PASSWORD_MAX_LENGTH", "128"))


def validate_password_policy(password: str) -> None:
    """Valida complejidad mínima de contraseña (ASVS V6.2.2).

    Requisitos por defecto:
    - longitud >= PASSWORD_MIN_LENGTH
    - al menos 1 minúscula, 1 mayúscula, 1 dígito y 1 símbolo
    """
    if password is None:
        raise ValueError("La contraseña es obligatoria")

    if not (PASSWORD_MIN_LENGTH <= len(password) <= PASSWORD_MAX_LENGTH):
        raise ValueError(f"La contraseña debe tener entre {PASSWORD_MIN_LENGTH} y {PASSWORD_MAX_LENGTH} caracteres")

    if not re.search(r"[a-z]", password):
        raise ValueError("La contraseña debe incluir al menos una minúscula")
    if not re.search(r"[A-Z]", password):
        raise ValueError("La contraseña debe incluir al menos una mayúscula")
    if not re.search(r"\d", password):
        raise ValueError("La contraseña debe incluir al menos un número")
    if not re.search(r"[^A-Za-z0-9]", password):
        raise ValueError("La contraseña debe incluir al menos un carácter especial")


def _build_security_logger() -> logging.Logger:
    logger = logging.getLogger("security")
    if logger.handlers:
        return logger

    logger.setLevel(logging.INFO)

    log_path = os.environ.get("SECURITY_LOG_PATH", "./logs/security.log")
    os.makedirs(os.path.dirname(log_path), exist_ok=True)

    handler = TimedRotatingFileHandler(log_path, when="midnight", backupCount=14, encoding="utf-8")
    formatter = logging.Formatter("%(asctime)s\t%(levelname)s\t%(message)s")
    handler.setFormatter(formatter)
    logger.addHandler(handler)
    logger.propagate = False
    return logger


def security_log(event: str, request: Any = None, user_id: Optional[str] = None, extra: Optional[Dict[str, Any]] = None) -> None:
    """Registro de eventos de seguridad (ASVS V16.2.1/V16.3.1).

    - No debe lanzar excepciones (best-effort).
    - Incluye metadatos mínimos: ip, user_id, event.
    """
    try:
        logger = _build_security_logger()
        ip = None
        if request is not None:
            try:
                ip = request.client.host if request.client else None
            except Exception:
                ip = None
        payload: Dict[str, Any] = {"event": event, "user_id": user_id, "ip": ip}
        if extra:
            payload.update(extra)
        logger.info(str(payload))
    except Exception:
        # Nunca romper el flujo principal por logging
        pass


def hash_password(password: str) -> str:
    """Devuelve el hash seguro de una contraseña."""
    validate_password_policy(password)
    salt = bcrypt.gensalt()
    hashed = bcrypt.hashpw(password.encode('utf-8'), salt)
    return hashed.decode('utf-8')


def verify_password(password: str, hashed_password: str) -> bool:
    """Verifica si la contraseña coincide con su hash."""
    return bcrypt.checkpw(password.encode('utf-8'), hashed_password.encode('utf-8'))


def create_access_token(data: Dict[str, Any], secret_key: str, expires_minutes: int = 60, algorithm: str = "HS256") -> str:
    """Crea un JWT con los datos provistos y expiración en minutos."""
    to_encode = data.copy()
    now = datetime.utcnow()
    expire = now + timedelta(minutes=expires_minutes)
    to_encode.update(
        {
            "iat": now,
            "nbf": now,
            "exp": expire,
            "jti": secrets.token_urlsafe(16),
            "typ": "access",
        }
    )
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
