# Guía de Configuración de Variables de Entorno y Seguridad

## Configuración de variables de entorno

### 1. Generar clave segura para JWT

```bash
# Linux/macOS
export JWT_SECRET_KEY=$(openssl rand -base64 48)

# Windows (PowerShell)
$bytes = New-Object Byte[] 36
(New-Object System.Security.Cryptography.RNGCryptoServiceProvider).GetBytes($bytes)
$key = [Convert]::ToBase64String($bytes)
$env:JWT_SECRET_KEY = $key
```

### 2. Configurar FastAPI

```bash
cd PPS/APIs-CRUD/Python

# Instalar dependencias
python -m pip install -r requirements.txt

# Ejecutar con variable de entorno
export JWT_SECRET_KEY="$(openssl rand -base64 48)"
export ACCESS_TOKEN_EXPIRE_MINUTES=60
uvicorn FastAPI.main:app --reload --host 0.0.0.0 --port 8000
```

### 3. Configurar .NET

#### Opción A: Usar `dotnet user-secrets` (Recomendado para desarrollo)

```bash
cd PPS/APIs-CRUD/Dotnet

# Inicializar user-secrets
dotnet user-secrets init

# Configurar JWT_SECRET_KEY
dotnet user-secrets set "Jwt:Key" "$(openssl rand -base64 48)"

# Ejecutar
dotnet run
```

#### Opción B: Usar variables de entorno

```bash
cd PPS/APIs-CRUD/Dotnet

# Exportar clave segura
export Jwt__Key="$(openssl rand -base64 48)"

# Restaurar dependencias y ejecutar
dotnet restore
dotnet build
dotnet run
```

#### Opción C: Usar `.env` con herramientas externas

```bash
# Crear archivo .env (no versionado)
echo "Jwt__Key=$(openssl rand -base64 48)" > .env

# Ejecutar con dotenv (requiere herramienta como dotenv-cli)
dotenv run dotnet run
```

### 4. Usar `.env.example` como referencia

```bash
# Copiar plantilla
cp .env.example .env

# Editar y configurar variables
vi .env  # o tu editor preferido

# Cargar en la sesión actual (con herramientas como `direnv` o manualmente)
```

---

## Checklist de Seguridad antes de Producción

- [ ] JWT_SECRET_KEY está configurado y es una clave fuerte (mínimo 48 caracteres aleatorios).
- [ ] Archivos `.env` no están versionados en Git (revisa `.gitignore`).
- [ ] `.gitignore` incluye `*.db`, `*.sqlite3`, `.env`, y archivos sensibles.
- [ ] Las claves y secretos están configurados mediante variables de entorno, `user-secrets`, o un secret manager (Azure Key Vault, AWS Secrets Manager).
- [ ] Rate limiting está habilitado en ambas APIs (FastAPI con `@limiter.limit()` y .NET con `IpRateLimiting`).
- [ ] Mensajes de error en login son genéricos (no revelan si el usuario existe).
- [ ] CORS está configurado con orígenes permitidos específicos (no `"*"`).
- [ ] HTTPS está habilitado en producción.
- [ ] Las dependencias se han escaneado con `pip-audit` (Python) y `dotnet list package --vulnerable` (.NET).
- [ ] Logs no contienen información sensible (contraseñas, tokens).

---

## Escanear dependencias vulnerables

### Python

```bash
pip install pip-audit
pip-audit -r PPS/APIs-CRUD/Python/requirements.txt
```

### .NET

```bash
cd PPS/APIs-CRUD/Dotnet
dotnet list package --vulnerable
```

---

## Configurar CORS (si necesitas múltiples orígenes)

### FastAPI

```python
from fastapi.middleware.cors import CORSMiddleware

origins = [
    "http://localhost:3000",
    "https://example.com",
]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

### .NET

```csharp
builder.Services.AddCors(options =>
{
    options.AddPolicy("AllowSpecificOrigins", builder =>
    {
        builder
            .WithOrigins("http://localhost:3000", "https://example.com")
            .AllowAnyHeader()
            .AllowAnyMethod();
    });
});

app.UseCors("AllowSpecificOrigins");
```

---

## Contacto y Reportes de Seguridad

Para reportar vulnerabilidades, contacta con el equipo de seguridad del proyecto.

