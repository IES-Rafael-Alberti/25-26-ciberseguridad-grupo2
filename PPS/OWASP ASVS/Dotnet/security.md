# Security (ASVS L1) — .NET (UsuariosApi)

Este documento resume controles de seguridad implementados para alineación con OWASP ASVS v5.0 Level 1.

## Secretos y configuración

- Secretos mediante variables de entorno (cargadas desde `.env` con `dotenv.net`):
  - `JWT_KEY` (obligatoria)
  - `JWT_ISSUER`, `JWT_AUDIENCE` (recomendadas)
  - `JWT_EXPIRES_MINUTES` (por defecto 60)
  - `GitHub__ClientId`, `GitHub__ClientSecret` (solo si se usa OAuth)
- En `appsettings.json` la clave JWT queda como placeholder.

## Autenticación (JWT)

- JWT firmado con HS256.
- Validación estricta: issuer, audience, lifetime y firma (`ClockSkew = 0`).
- El token incluye `jti` y `nbf` para soporte de revocación y validez temporal.

## Terminación de sesión (logout)

- Endpoint: `POST /api/auth/logout`
- Revoca el token actual marcando su `jti` como revocado en `IDistributedCache`.
- En cada request autenticada, `OnTokenValidated` comprueba si el `jti` está revocado.

Nota: la revocación con cache en memoria es válida para un solo proceso. En despliegues multi-instancia, usar Redis u otro store compartido.

## OAuth (GitHub)

- Flujo OAuth con GitHub en `GET /api/auth/github/login` y callback `GET /api/auth/github/callback`.
- Mitigación CSRF: `state` generado en login, guardado en sesión y validado en callback (one-time).
- Timeouts en llamadas HTTP a GitHub configurables con `OAUTH_HTTP_TIMEOUT_SECONDS` (por defecto 10s).

## Validación de entrada

- DTOs con DataAnnotations:
  - `EmailAddress`
  - longitudes máximas
  - política de contraseña (12+ y complejidad mínima)

## Rate limiting

- Rate limit por IP en producción con `AspNetCoreRateLimit`.
- Reglas definidas en `IpRateLimiting:GeneralRules` (login, registro, endpoints de usuarios).

## Cabeceras de seguridad

Middleware de headers defensivos:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- HSTS en producción

También se añade `Cache-Control: no-store` para rutas sensibles (`/api/usuarios` y `/api/auth`).

## Manejo de errores

- `UseExceptionHandler` retorna error genérico: `{ "mensaje": "Error interno del servidor" }`.
- Los detalles se registran en logs del servidor.
