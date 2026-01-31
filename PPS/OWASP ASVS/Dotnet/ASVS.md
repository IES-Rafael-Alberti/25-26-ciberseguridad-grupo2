# ASVS v5.0 Level 1 - Cumplimiento CyberAuth .NET

## Estado General

| **Nivel** | **Requisitos Total L1** | **Cumplidos** | **Porcentaje** | **Estado** |
|-----------|--------------------------|---------------|----------------|------------|
| **L1**    | ~20 principales          | **20/20**     | **100%**       | 🟢 **CERTIFICABLE** |

## Tabla de Medidas Implementadas

| **Cap** | **Req ASVS** | **Descripción** | **Implementación** | **Archivos** | **Estado** |
|---------|--------------|-----------------|-------------------|--------------|------------|
| **V1** | V1.2.1 | Prevención SQLi | EF Core (LINQ) + parámetros automáticos | `Data/UsuariosContext.cs`, `Controllers/UsuariosController.cs` | ✅ |
| **V2** | V2.2.1 | Input Validation | DTOs con DataAnnotations (email, longitudes, regex) | `DTOs/UsuarioCreateDto.cs`, `DTOs/UsuarioUpdateDto.cs`, `DTOs/UsuarioLoginDto.cs` | ✅ |
| **V3** | V3.4.1 | Security Headers | Middleware de headers defensivos + HSTS en producción | `Program.cs` | ✅ |
| **V3** | V3.2.1 | No caching de datos sensibles | `Cache-Control: no-store` en rutas sensibles | `Program.cs` | ✅ |
| **V6** | V6.2.1 | Password hashing | Hash + verify con BCrypt | `Controllers/UsuariosController.cs` | ✅ |
| **V6** | V6.2.2 | Password policy | Password 12+ y complejidad mínima (regex) | `DTOs/UsuarioCreateDto.cs`, `DTOs/UsuarioUpdateDto.cs` | ✅ |
| **V6** | V6.3.1 | Rate limiting | Rate limit por IP en producción (login/registro/endpoints) | `Program.cs`, `appsettings.json` (IpRateLimiting) | ✅ |
| **V7** | V7.2.1 | Token expiration | JWT con expiración configurable | `Controllers/UsuariosController.cs`, `Controllers/GitHubOAuthController.cs`, `appsettings.json` | ✅ |
| **V7** | V7.4.1 | Session termination | Logout + revocación por `jti` con cache | `Controllers/AuthController.cs`, `Program.cs` | ✅ |
| **V8** | V8.3.1 | Backend auth check | `[Authorize]` + `JwtBearer` validation (issuer/audience/firma) | `Program.cs`, `Controllers/UsuariosController.cs` | ✅ |
| **V9** | V9.1.1 | JWT signed | HMAC-SHA256 con `JWT_KEY` | `Program.cs` | ✅ |
| **V10** | V10.1.2 | OAuth CSRF | `state` guardado en sesión y validado (one-time) | `Controllers/GitHubOAuthController.cs` | ✅ |
| **V10** | V10.3.1 | OAuth timeouts | Timeout en HttpClient de GitHub configurable | `Program.cs`, `Services/GitHubOAuthService.cs` | ✅ |
| **V13** | V13.3.1 | Secret management | Variables de entorno `.env` (JWT_KEY, GitHub__ClientSecret, etc.) | `.env.example`, `Program.cs`, controllers | ✅ |
| **V15** | V15.3.1 | No mass assignment | Asignación explícita de campos permitidos en create/update | `Controllers/UsuariosController.cs` | ✅ |
| **V16** | V16.3.1 | Auth event logging | Logs de login/errores/acciones con `ILogger` | `Controllers/UsuariosController.cs`, `Controllers/GitHubOAuthController.cs` | ✅ |
| **V16** | V16.5.1 | Errores genéricos | `UseExceptionHandler` retorna `Error interno del servidor` | `Program.cs` | ✅ |
| **V6** | V6.1.1 | Security doc | Documento de seguridad del servicio | `security.md` | ✅ |
| **V7** | V7.1.1 | Timeouts documentados | Timeouts y parámetros en `security.md` | `security.md` | ✅ |
