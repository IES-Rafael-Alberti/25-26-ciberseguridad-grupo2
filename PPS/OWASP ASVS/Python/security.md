# Security (ASVS L1) — FastAPI

Este documento describe los controles de seguridad implementados en esta API FastAPI para alinearse con OWASP ASVS v5.0 Level 1.

## Secretos y configuración

- Los secretos se cargan desde variables de entorno (ver `.env.example`).
- Variables mínimas:
  - `JWT_SECRET_KEY` (obligatoria)
  - `ACCESS_TOKEN_EXPIRE_MINUTES` (por defecto `60`)
  - `GITHUB_CLIENT_ID` / `GITHUB_CLIENT_SECRET` (solo si se usa OAuth)

## Autenticación y sesión

- Autenticación por `Bearer` token (JWT) en endpoints protegidos.
- Tokens firmados con HS256 mediante `JWT_SECRET_KEY`.
- El JWT incluye `exp` (caducidad), `iat`, `nbf` y `jti`.
- Existe endpoint de logout que revoca el token actual mediante blacklist en memoria.

### Timeouts

- Expiración de access token: `ACCESS_TOKEN_EXPIRE_MINUTES` (recomendado: 15–60 min según riesgo).
- OAuth `state` cookie TTL: `OAUTH_STATE_TTL_SECONDS` (por defecto 300s).
- Timeout de llamadas HTTP a GitHub: `OAUTH_REQUEST_TIMEOUT_SECONDS` (por defecto 10s).

## Contraseñas

- Hash con `bcrypt`.
- Política mínima (configurable con `PASSWORD_MIN_LENGTH`/`PASSWORD_MAX_LENGTH`):
  - longitud mínima
  - 1 mayúscula, 1 minúscula, 1 dígito y 1 símbolo

## Rate limiting

- Endpoint de login limitado a `5/minute` para mitigar fuerza bruta.

## Cabeceras de seguridad

Se añaden cabeceras HTTP defensivas en middleware:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: no-referrer`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- `Cross-Origin-Opener-Policy: same-origin`
- `Cross-Origin-Resource-Policy: same-origin`
- `Strict-Transport-Security` solo si la app se sirve por HTTPS

## OAuth (GitHub)

- Se usa el parámetro `state` para mitigar CSRF.
- `state` se guarda en cookie `HttpOnly` y se valida en el callback.
- Para producción, configurar `OAUTH_COOKIE_SECURE=true` y servir **solo HTTPS**.

## Logging de seguridad

- Los eventos de seguridad se registran en `SECURITY_LOG_PATH` (por defecto `./logs/security.log`) con rotación diaria (14 días).
- Eventos registrados (ejemplos): `login_success`, `login_failed`, `oauth_github_callback_failed`.

## Manejo de errores

- Las excepciones no controladas retornan un error genérico: `{"detail": "Error interno"}`.
- Los detalles se registran en el log del servidor (no se exponen al cliente).
