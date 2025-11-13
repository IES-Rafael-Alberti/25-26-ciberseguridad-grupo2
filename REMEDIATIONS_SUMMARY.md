# Resumen de Remediaciones de Vulnerabilidades Aplicadas
## Repositorio: 25-26-ciberseguridad-grupo2
**Fecha:** 13 de noviembre de 2025

---

## ✅ Vulnerabilidades Remediadas (Completadas)

### 1. Secretos en Repositorio (Severidad: ALTA)
**Estado:** ✅ REMEDIADO

**Problemas corregidos:**
- ❌ Se eliminó clave JWT hardcodeada de `appsettings.json` (.NET)
- ❌ Se eliminó clave por defecto de `main.py` (FastAPI)
- ✅ Ahora ambas APIs requieren `JWT_SECRET_KEY` o `Jwt__Key` como variable de entorno
- ✅ Lanzarán error al iniciar si la variable no está definida

**Cómo verificar:**
```bash
# FastAPI: intentar arrancar sin JWT_SECRET_KEY
cd PPS/APIs-CRUD/Python && uvicorn FastAPI.main:app
# Resultado: RuntimeError: JWT_SECRET_KEY no está definido...

# .NET: ya existe validación en Program.cs
cd PPS/APIs-CRUD/Dotnet && dotnet run
# Resultado: Exception si Jwt__Key no está en entorno
```

---

### 2. Enumeración de Usuarios (Severidad: MEDIA-BAJA)
**Estado:** ✅ REMEDIADO

**Cambios:**
- Mensajes de login unificados: "Credenciales inválidas" (antes: "email no encontrado" vs "contraseña incorrecta")
- Implementado en `FastAPI/main.py` y `UsuariosController.cs` (.NET)
- Previene descubrimiento de usuarios válidos por diferencias en mensajes

---

### 3. Ataques de Fuerza Bruta (Severidad: MEDIA)
**Estado:** ✅ REMEDIADO

**Implementaciones:**
- **FastAPI:** `slowapi` (v0.1.9) con límite de **5 intentos/minuto** en `POST /usuarios/login`
  - Decorador: `@limiter.limit("5/minute")`
  - Respuesta 429 cuando se excede el límite

- **.NET:** `AspNetCoreRateLimit` (v4.0.2) configurado en `appsettings.json`
  - Límite de **5 intentos/minuto** en `post:/usuarios/login`
  - Middleware: `app.UseIpRateLimiting()`

**Cómo verificar:**
```bash
# FastAPI: 6 solicitudes fallidas en 1 minuto
for i in {1..6}; do
  curl -X POST http://localhost:8000/usuarios/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}'
done
# Resultado: 5ª OK (401), 6ª falla (429 Too Many Requests)
```

---

### 4. Archivos Sensibles en Control de Versiones (Severidad: MEDIA)
**Estado:** ✅ REMEDIADO (parcialmente)

**Acciones:**
- ✅ Creado `.gitignore` en raíz con reglas para `*.db`, `.env`, `appsettings.Development.json`
- ✅ Ejecutados `git rm --cached` para archivos DB detectados
- ⚠️ Nota: Para eliminar completamente del historio, usa `git-filter-repo` o `BFG` (operación destructiva)

---

## 📋 Archivos Nuevos y Modificados

### Nuevos Archivos
1. **`.gitignore`** — Reglas de seguridad para evitar versionamiento de secretos
2. **`.env.example`** — Plantilla de variables de entorno requeridas
3. **`SECURITY_SETUP.md`** — Guía completa de configuración y seguridad
4. **`REMEDIATIONS_SUMMARY.md`** — Este archivo

### Modificados
- `PPS/APIs-CRUD/Dotnet/appsettings.json` — Quitada `Jwt:Key`, añadida sección `IpRateLimiting`
- `PPS/APIs-CRUD/Dotnet/Program.cs` — Middleware de rate limiting
- `PPS/APIs-CRUD/Dotnet/Dotnet.csproj` — Nueva dependencia `AspNetCoreRateLimit`
- `PPS/APIs-CRUD/Dotnet/Controllers/UsuariosController.cs` — Mensajes unificados
- `PPS/APIs-CRUD/Python/requirements.txt` — Añadido `slowapi`
- `PPS/APIs-CRUD/Python/FastAPI/main.py` — Rate limiting y JWT env var requerido
- `VULNERABILITIES.md` — Actualizado con resumen de remediaciones

---

## 🚀 Próximos Pasos Recomendados

### Inmediatos (antes de producción)
1. **Rotar claves expuestas:**
   ```bash
   export JWT_SECRET_KEY=$(openssl rand -base64 48)
   # Documenta y distribuye a tu equipo de forma segura
   ```

2. **Escanear dependencias actuales:**
   ```bash
   # Python
   pip install pip-audit
   pip-audit -r PPS/APIs-CRUD/Python/requirements.txt
   
   # .NET
   cd PPS/APIs-CRUD/Dotnet
   dotnet list package --vulnerable
   ```

3. **Pruebas de integración:**
   ```bash
   # Verifica que rate limiting funciona
   # Verifica que mensajes son genéricos
   # Verifica que variables de entorno son requeridas
   ```

### Corto plazo
- [ ] Implementar CORS con orígenes permitidos específicos (ver `SECURITY_SETUP.md`)
- [ ] Implementar refresh tokens para sesiones más seguras
- [ ] Añadir logging de intentos fallidos para auditoría
- [ ] Configurar secret manager (Azure Key Vault, AWS Secrets Manager, HashiCorp Vault)

### Mediano plazo
- [ ] Implementar token revocation (blacklist o versioning)
- [ ] Añadir autenticación multifactor (MFA)
- [ ] Realizar auditoría de seguridad con herramientas como OWASP ZAP o Burp Community
- [ ] Establecer política de rotación de secretos

---

## 📊 Matriz de Riesgos Residuales

| Vulnerabilidad | Antes | Después | Mitigación |
|---|---|---|---|
| Secretos en repo | 🔴 CRÍTICO | ✅ RESUELTO | Env vars requeridas |
| Enumeración usuarios | 🟠 MEDIA | ✅ RESUELTO | Mensajes genéricos |
| Fuerza bruta | 🟠 MEDIA | 🟡 BAJO | Rate limiting 5/min |
| DB en repo | 🟠 MEDIA | ✅ MITIGADO | `.gitignore` |
| JWT sin refresh | 🟡 BAJO | 🟡 BAJO | Tokens 60 min. exp. |
| CORS no restrictivo | 🟡 BAJO | 🟡 BAJO | Config pendiente |

---

## 🔐 Comandos de Verificación Rápida

```bash
# Verificar archivos no versionados
git status

# Ver commits de seguridad
git log --oneline | grep -i "security\|fix\|jwt\|rate"

# Verificar que .gitignore está aplicado
git check-ignore -v .env usuarios.db appsettings.Development.json

# Validar sintaxis (sin instalar deps)
python -m py_compile PPS/APIs-CRUD/Python/FastAPI/*.py

# Comprobar compilación .NET
cd PPS/APIs-CRUD/Dotnet && dotnet build --no-restore
```

---

## 📞 Contacto y Auditoría

Para preguntas sobre seguridad o reportar vulnerabilidades, revisa `SECURITY_SETUP.md`.

**Última actualización:** 13 de noviembre de 2025
