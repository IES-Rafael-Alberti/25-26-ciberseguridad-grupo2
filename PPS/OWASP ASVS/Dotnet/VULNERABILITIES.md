# Análisis de Vulnerabilidades - Proyecto API CRUD .NET

## Resumen Ejecutivo
Se han identificado **8 vulnerabilidades críticas y de alta severidad** en el proyecto API CRUD de .NET 9. Estas afectan principalmente a autenticación, autorización, validación de entrada y gestión de secretos.

---

## 🔴 Vulnerabilidades Críticas

### 1. **Gestión Insegura de Secretos JWT**
**Severidad:** 🔴 CRÍTICA  
**Ubicación:** `Program.cs`, `appsettings.json`  
**Descripción:**
- La clave JWT (`Jwt:Key`) se almacena en texto plano en `appsettings.json`
- No hay uso de Azure Key Vault, HashiCorp Vault, o .NET User Secrets en producción
- La configuración se puede exponer en repositorios públicos o en Docker

**Código vulnerable:**
```csharp
var jwtKey = jwtSection["Key"];  // Se lee directamente de la configuración
```

**Recomendaciones:**
```csharp
// ✅ Usar .NET User Secrets en desarrollo
dotnet user-secrets init
dotnet user-secrets set "Jwt:Key" "tu-clave-segura"

// ✅ Usar Azure Key Vault en producción
builder.Configuration.AddAzureKeyVault(
    new Uri($"https://{keyVaultName}.vault.azure.net/"),
    new DefaultAzureCredential()
);

// ✅ O usar variables de entorno
var jwtKey = Environment.GetEnvironmentVariable("JWT_KEY") 
    ?? throw new InvalidOperationException("JWT_KEY no configurada");
```

---

### 2. **Falta de Validación y Sanitización de Entrada**
**Severidad:** 🔴 CRÍTICA  
**Ubicación:** `UsuariosController.cs` - Métodos `CreateUsuario` y `UpdateUsuario`  
**Descripción:**
- No hay validación de longitud en campos de email, nombre y apellidos
- No se validan formatos de email
- Posible inyección de caracteres especiales o scripts
- Sin límites de tamaño en las cadenas de entrada

**Código vulnerable:**
```csharp
var usuario = new Usuario
{
    Nombre = createDto.Nombre,           // ❌ Sin validación
    Apellidos = createDto.Apellidos,     // ❌ Sin validación
    Email = createDto.Email,             // ❌ Sin validación de formato
    PasswordHash = BCrypt.Net.BCrypt.HashPassword(createDto.Password)
};
```

**Recomendaciones:**
```csharp
// ✅ Usar Data Annotations
public class UsuarioCreateDto
{
    [Required]
    [StringLength(100, MinimumLength = 1)]
    public string Nombre { get; set; }
    
    [Required]
    [StringLength(100, MinimumLength = 1)]
    public string Apellidos { get; set; }
    
    [Required]
    [EmailAddress]
    public string Email { get; set; }
    
    [Required]
    [StringLength(256, MinimumLength = 8)]
    [RegularExpression(@"^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$",
        ErrorMessage = "La contraseña debe contener mayúscula, minúscula, número y carácter especial")]
    public string Password { get; set; }
}
```

---

### 3. **Control de Acceso Deficiente en Actualización y Eliminación**
**Severidad:** 🔴 CRÍTICA  
**Ubicación:** `UsuariosController.cs` - Métodos `UpdateUsuario` y `DeleteUsuario`  
**Descripción:**
- Cualquier usuario autenticado puede actualizar o eliminar a cualquier otro usuario
- No hay verificación de que el usuario es propietario del recurso
- Falta autorización a nivel de recurso (Authorization Policy)

**Código vulnerable:**
```csharp
[HttpPut("{id:long}")]
[Authorize]  // ❌ Solo verifica que esté autenticado
public async Task<ActionResult<UsuarioReadDto>> UpdateUsuario(long id, UsuarioUpdateDto updateDto)
{
    var usuario = await _context.Usuarios.FindAsync(id);
    // ❌ Sin verificar si el usuario logueado es dueño del recurso
    usuario.Nombre = updateDto.Nombre;
    // ... actualiza sin verificación
}
```

**Recomendaciones:**
```csharp
[HttpPut("{id:long}")]
[Authorize]
public async Task<ActionResult<UsuarioReadDto>> UpdateUsuario(long id, UsuarioUpdateDto updateDto)
{
    var usuarioId = long.Parse(User.FindFirst(JwtRegisteredClaimNames.Sub)?.Value 
        ?? throw new UnauthorizedAccessException());
    
    // ✅ Verificar que el usuario solo actualiza su propio perfil
    if (usuarioId != id)
    {
        return Forbid("No tienes permiso para actualizar este usuario");
    }
    
    var usuario = await _context.Usuarios.FindAsync(id);
    if (usuario == null)
        return NotFound();
    
    // ... resto del código
}
```

---

### 4. **Contraseña Débil sin Requisitos**
**Severidad:** 🔴 CRÍTICA  
**Ubicación:** `UsuariosController.cs` - Método `CreateUsuario`  
**Descripción:**
- No hay validación de fortaleza de contraseña
- Se aceptan contraseñas muy cortas (sin mínimo de caracteres)
- Sin requisitos de complejidad (mayúscula, número, carácter especial)

**Código vulnerable:**
```csharp
if (string.IsNullOrWhiteSpace(createDto.Email) || string.IsNullOrWhiteSpace(createDto.Password))
{
    return BadRequest("Email y password son obligatorios");
}
// ❌ No hay validación de fortaleza
PasswordHash = BCrypt.Net.BCrypt.HashPassword(createDto.Password)
```

**Recomendaciones:**
```csharp
private bool IsPasswordStrong(string password)
{
    if (password.Length < 12) return false;
    if (!password.Any(char.IsUpper)) return false;
    if (!password.Any(char.IsLower)) return false;
    if (!password.Any(char.IsDigit)) return false;
    if (!password.Any(c => "@$!%*?&".Contains(c))) return false;
    return true;
}

if (!IsPasswordStrong(createDto.Password))
{
    return BadRequest("La contraseña debe tener mínimo 12 caracteres, incluyendo mayúscula, minúscula, número y carácter especial");
}
```

---

## 🟠 Vulnerabilidades Altas

### 5. **Información Sensible en Logs**
**Severidad:** 🟠 ALTA  
**Ubicación:** Proyecto completo  
**Descripción:**
- No hay protección contra logueo de información sensible
- Las credenciales pueden terminar en logs
- Mensajes de error genéricos no están implementados

**Recomendaciones:**
```csharp
public class SensitiveDataFilter : ILoggerProvider
{
    // Implementar para filtrar información sensible de logs
}

// En appsettings.json
"Logging": {
    "LogLevel": {
        "Default": "Information",
        "Microsoft.AspNetCore": "Warning",
        "UsuariosApi.Controllers": "Warning"  // Reducir verbosidad
    }
}
```

---

### 6. **Falta de HTTPS/TLS Enforcement**
**Severidad:** 🟠 ALTA  
**Ubicación:** `Program.cs`, `Dockerfile`  
**Descripción:**
- `app.UseHttpsRedirection()` está presente pero puede ser bypasseado
- El Dockerfile expone puerto 8080 sin especificar HTTPS
- Sin HSTS (HTTP Strict Transport Security)

**Código:**
```csharp
app.UseHttpsRedirection();  // ⚠️ Insuficiente
```

**Recomendaciones:**
```csharp
app.UseHsts();  // ✅ Agregar
app.UseHttpsRedirection();

// Agregar política HSTS en appsettings
app.UseHsts();

// En Dockerfile
EXPOSE 8080 8443  # ✅ Agregar HTTPS
```

---

### 7. **Rate Limiting Insuficiente**
**Severidad:** 🟠 ALTA  
**Ubicación:** `appsettings.json`, `Program.cs`  
**Descripción:**
- Solo se aplica rate limiting al endpoint `/usuarios/login`
- No hay protección en otros endpoints
- Límite de 5 intentos por minuto es débil (vulnerable a ataques distribuidos)
- Sin protección contra ataques de fuerza bruta en GetUsuarios

**Configuración vulnerable:**
```json
"GeneralRules": [
  {
    "Endpoint": "post:/usuarios/login",
    "Period": "1m",
    "Limit": 5  // ❌ Muy permisivo
  }
]
```

**Recomendaciones:**
```json
"GeneralRules": [
  {
    "Endpoint": "post:/usuarios/login",
    "Period": "1m",
    "Limit": 3
  },
  {
    "Endpoint": "post:/usuarios",
    "Period": "1h",
    "Limit": 10
  },
  {
    "Endpoint": "*:/usuarios",
    "Period": "1m",
    "Limit": 100
  }
]
```

---

### 8. **Falta de Validación de CORS**
**Severidad:** 🟠 ALTA  
**Ubicación:** `Program.cs`  
**Descripción:**
- No hay configuración de CORS explícita
- Se permite `"AllowedHosts": "*"` en producción
- Posible exposición a ataques CSRF

**Recomendaciones:**
```csharp
// En Program.cs
builder.Services.AddCors(options =>
{
    options.AddPolicy("ProductionPolicy", policyBuilder =>
    {
        policyBuilder
            .WithOrigins("https://tudominio.com")  // ✅ Específico
            .AllowAnyMethod()
            .AllowAnyHeader()
            .AllowCredentials()
            .WithExposedHeaders("Authorization");
    });
});

app.UseCors("ProductionPolicy");

// En appsettings.json
"AllowedHosts": "tudominio.com"  // ✅ No usar "*"
```

---

## 🟡 Vulnerabilidades Medias

### 9. **Información Excesiva en Errores**
**Severidad:** 🟡 MEDIA  
**Ubicación:** `UsuariosController.cs`  
**Descripción:**
- Se devuelve "Credenciales inválidas" en ambos casos (usuario no existe y contraseña incorrecta)
- Aunque esto es correcto, falta hacer explícito el manejo de excepciones de la base de datos

**Mejor práctica:**
```csharp
try
{
    // ... código
}
catch (DbUpdateException ex)
{
    _logger.LogError("Error de base de datos: {0}", ex.Message);
    return StatusCode(500, "Error interno del servidor");
}
catch (Exception ex)
{
    _logger.LogError("Error inesperado: {0}", ex.Message);
    return StatusCode(500, "Error interno del servidor");
}
```

---

### 10. **Ausencia de Auditoría**
**Severidad:** 🟡 MEDIA  
**Ubicación:** Proyecto completo  
**Descripción:**
- No hay registro de acciones (quién modificó qué, cuándo)
- No se registran intentos de acceso no autorizados
- Sin trazabilidad de cambios en datos sensibles

**Recomendaciones:**
- Agregar campos `CreatedAt`, `UpdatedAt`, `CreatedBy` en Usuario
- Implementar tabla de auditoría
- Registrar eventos de seguridad (login fallido, acceso denegado, etc.)

---

## 📋 Tabla de Resumen

| # | Vulnerabilidad | Severidad | Categoría | Estado |
|---|---|---|---|---|
| 1 | Gestión Insegura de Secretos JWT | 🔴 CRÍTICA | CWE-798 | ⚠️ |
| 2 | Falta de Validación de Entrada | 🔴 CRÍTICA | CWE-20 | ⚠️ |
| 3 | Control de Acceso Deficiente | 🔴 CRÍTICA | CWE-639 | ⚠️ |
| 4 | Contraseñas Débiles | 🔴 CRÍTICA | CWE-521 | ⚠️ |
| 5 | Información Sensible en Logs | 🟠 ALTA | CWE-532 | ⚠️ |
| 6 | Falta de HTTPS/TLS | 🟠 ALTA | CWE-295 | ⚠️ |
| 7 | Rate Limiting Insuficiente | 🟠 ALTA | CWE-770 | ⚠️ |
| 8 | Validación CORS Deficiente | 🟠 ALTA | CWE-345 | ⚠️ |
| 9 | Información Excesiva en Errores | 🟡 MEDIA | CWE-209 | ⚠️ |
| 10 | Ausencia de Auditoría | 🟡 MEDIA | CWE-778 | ⚠️ |

---

## ✅ Plan de Remediación Prioritario

### Fase 1 (URGENTE - Semana 1)
- [ ] Implementar manejo seguro de secretos (User Secrets / Key Vault)
- [ ] Agregar validación de entrada con Data Annotations
- [ ] Implementar autorización a nivel de recurso
- [ ] Aplicar política de contraseñas fuertes

### Fase 2 (CRÍTICO - Semana 2)
- [ ] Configurar CORS restrictivo
- [ ] Mejorar rate limiting en todos los endpoints
- [ ] Implementar HSTS
- [ ] Agregar validación de errores segura

### Fase 3 (IMPORTANTE - Semana 3)
- [ ] Implementar auditoría
- [ ] Configurar logging seguro
- [ ] Realizar pruebas de penetración
- [ ] Documentar políticas de seguridad

---

## 🔗 Referencias

- [OWASP Top 10 2023](https://owasp.org/Top10/)
- [CWE/SANS Top 25](https://cwe.mitre.org/top25/)
- [Microsoft Security Best Practices](https://docs.microsoft.com/en-us/dotnet/fundamentals/code-analysis/overview)
- [ASP.NET Core Security Documentation](https://docs.microsoft.com/en-us/aspnet/core/security/)
