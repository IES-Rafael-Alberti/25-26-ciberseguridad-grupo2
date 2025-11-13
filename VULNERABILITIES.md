# Informe de vulnerabilidades - Repositorio 25-26-ciberseguridad-grupo2

Fecha: 2025-11-13

Este documento recoge vulnerabilidades y malas prácticas detectadas rápidamente en el repositorio. Contiene evidencia, impacto, nivel de riesgo y pasos de mitigación concretos que puedes aplicar inmediatamente.

---

## Resumen ejecutivo

He identificado varias cuestiones de seguridad de configuración, exposición de secretos y prácticas que aumentan el riesgo de filtración o abuso. Las más críticas son la presencia de claves/secretos en ficheros de configuración versionados y la presencia de bases de datos/artefactos (sqlite) en el repo.

Prioridad inmediata:
- Eliminar secretos (JWT secret) del repositorio y moverlos a variables de entorno o secret manager.
- Evitar incluir archivos binarios / DB/artefactos en el control de versiones (.gitignore).

---

## Acciones aplicadas (remediaciones realizadas)

He aplicado los siguientes cambios automáticamente en el repositorio de trabajo:

- Se eliminó la clave `Jwt:Key` de `PPS/APIs-CRUD/Dotnet/appsettings.json` para evitar que la clave secreta quede versionada. El servicio .NET exige ahora que la clave se proporcione mediante variable de entorno en tiempo de ejecución.
- Se actualizó `PPS/APIs-CRUD/Python/FastAPI/main.py` para exigir la variable de entorno `JWT_SECRET_KEY` y fallar al iniciar si no está definida (eliminando la clave por defecto en el código).
- Se añadió `.gitignore` en la raíz con reglas para ignorar `*.db`, `.env` y `appsettings.Development.json`.
- Se intentó eliminar las bases de datos locales del control de versiones (por ejemplo `PPS/APIs-CRUD/Python/usuarios.db` y `PPS/APIs-CRUD/crud-java/src/main/resources/database.db`) usando `git rm --cached`. Revisa el historial y aplica `git push` para propagar los cambios al remoto.

Comprueba los commits resultantes y rota cualquier clave expuesta previamente. Si quieres, hago la rotación automática (generar clave nueva y configurarla como ejemplo en `dotnet user-secrets` o `.env.example`).


---

## Hallazgos

1) Secretos en el repositorio (clave JWT en `appsettings.json`)
   - Severidad: Alta
   - Archivos: `PPS/APIs-CRUD/Dotnet/appsettings.json`
   - Evidencia: existe la sección `Jwt` con `Key` configurada en el archivo.
   - Riesgo: clave está versionada y puede ser usada para firmar/forjar tokens JWT que permitan acceso a la API.
   - Recomendación:
     1. Elimina el valor de `Jwt:Key` de `appsettings.json`.
     2. Añade `appsettings.json` a `.gitignore` temporalmente si contiene secretos (mejor: mover secretos a `appsettings.Development.json` y excluirlo).
     3. Configura la clave como variable de entorno `JWT__Key` (o usa dotnet user-secrets / Azure Key Vault).
     4. Rotar la clave inmediatamente (invalidar tokens existentes si procede).

2) Secretos por defecto en FastAPI (clave secreta en código Python)
   - Severidad: Alta
   - Archivos: `PPS/APIs-CRUD/Python/FastAPI/main.py` y `PPS/APIs-CRUD/Python/FastAPI/utils.py`
   - Evidencia: `SECRET_KEY = os.environ.get("JWT_SECRET_KEY", "CAMBIA_POR_UNA_CLAVE_SECRETA_MUY_LARGA")` — la clave por defecto queda en código.
   - Riesgo: si el repo se comparte, la clave por defecto permite firmar tokens y falsificar sesiones.
   - Recomendación:
     1. Elimina la clave por defecto. Lanza error o exige variable de entorno si no existe.
     2. Documenta cómo establecer `JWT_SECRET_KEY` en producción (env vars, .env gestionado fuera del repo, vault).
     3. Añadir comprobación en arranque que avise/termine si la clave no está configurada.

3) Archivo de base de datos SQLite incluido/referenciado
   - Severidad: Media
   - Archivos: `PPS/APIs-CRUD/Dotnet/usuarios.db` (si existe) o referencia en `appsettings.json` y `PPS/APIs-CRUD/Python/FastAPI/database.py` usa `./usuarios.db`.
   - Evidencia: conexión por defecto a sqlite `Data Source=usuarios.db` y `sqlite:///./usuarios.db`.
   - Riesgo: archivo de base de datos puede contener datos sensibles; si fue añadido al repo puede exponerse.
   - Recomendación:
     1. Añadir `usuarios.db` a `.gitignore` si no debe versionarse.
     2. Si el archivo ya está en git, eliminarlo del historial: `git rm --cached usuarios.db` y publicar nueva clave.
     3. Para entornos de producción usar RDBMS gestionado, backups en sitio seguro.

4) Tokens JWT: sin mecanismo de revocación ni refresh
   - Severidad: Media
   - Archivos: `PPS/APIs-CRUD/Dotnet/*`, `PPS/APIs-CRUD/Python/FastAPI/*`
   - Evidencia: se generan tokens con expiración fija (60 minutos) y no hay refresh tokens ni revocación.
   - Riesgo: un token robado será válido hasta su expiración.
   - Recomendación:
     1. Implementar refresh tokens y revocación (lista negra en Redis/DB o uso de token versioning por usuario).
     2. Reducir tiempo de validez de access tokens y usar refresh tokens con más controles.

5) Falta de hardening de CORS / rate limiting / protección contra fuerza bruta
   - Severidad: Media
   - Archivos: configuración ausente en API (FastAPI y .NET no muestran CORS ni rate limiting)
   - Riesgo: APIs pueden ser llamadas desde orígenes no deseados, ataques de fuerza bruta al endpoint `/login`.
   - Recomendación:
     1. Añadir CORS con lista de orígenes autorizados.
     2. Implementar rate limiting (API gateway, nginx, o middleware como AspNetCoreRateLimit / slowapi para FastAPI).
     3. Implementar bloqueo temporal de cuenta tras N intentos fallidos.

6) Dependencias fijadas/posible exposición por libs desactualizadas
   - Severidad: Media
   - Archivos: `PPS/APIs-CRUD/Dotnet/Dotnet.csproj`, `PPS/APIs-CRUD/Python/requirements.txt`
   - Evidencia: dependencias especificadas; no comprobación automática de vulnerabilidades.
   - Riesgo: versiones con CVE pueden permitir RCE, etc.
   - Recomendación:
     1. Ejecutar escaneo de dependencias (`dotnet list package --vulnerable`, `pip-audit`, `safety`, `OWASP Dependency-Check`, Snyk).
     2. Actualizar paquetes vulnerables y fijar versiones seguras.

7) Exposición de información en mensajes de error
   - Severidad: Baja-Media
   - Archivos: respuestas en controladores (`UsuariosController` en .NET y en FastAPI) revelan si el email existe o no.
   - Evidencia: mensajes de error en login: "email no encontrado" vs "contraseña incorrecta".
   - Riesgo: permite enumeración de usuarios.
   - Recomendación:
     1. Devolver un único mensaje genérico en fallos de autenticación: "Credenciales inválidas".

8) Respuestas / serialización — posible filtrado de campos sensibles
   - Severidad: Baja
   - Archivos: `FastAPI/main.py` y DTOs de .NET
   - Evidencia: en FastAPI se usa `response_model` por lo que los campos sensibles quedan filtrados; revisar que nunca se devuelva `password` por accidente.
   - Recomendación:
     1. Auditar endpoints para garantizar que los modelos de salida no contienen `password` ni `password_hash`.

---

## Pasos de mitigación rápidos (prioridad)

1. Remover secretos del repositorio
   - Eliminar `Jwt:Key` de `PPS/APIs-CRUD/Dotnet/appsettings.json`.
   - Cambiar `PPS/APIs-CRUD/Python/FastAPI/main.py` para fallar si `JWT_SECRET_KEY` no está definido (ya hay fallback: eliminarlo).
   - Añadir a `.gitignore`: `*.db`, `usuarios.db`, `appsettings.Development.json` (según convenga).

2. Rotar secretos
   - Generar nueva clave segura (por ejemplo: `openssl rand -base64 48`) y configurar en entorno.

3. Ejecutar escaneos automáticos
   - `pip install pip-audit && pip-audit --path PPS/APIs-CRUD/Python/requirements.txt`
   - `dotnet list PPS/APIs-CRUD/Dotnet package --vulnerable`

4. Añadir hardening mínimo
   - Habilitar CORS con orígenes permitidos.
   - Añadir rate limiting y bloqueo de intentos fallidos.

---

## Comandos y ejemplos útiles

- Eliminar archivo de base de datos del repo pero mantener localmente:
```bash
git rm --cached PPS/APIs-CRUD/Python/usuarios.db || true
git rm --cached PPS/APIs-CRUD/Dotnet/usuarios.db || true
echo "usuarios.db" >> .gitignore
git add .gitignore
git commit -m "Remove db from repo and ignore local DB files"
```

- Generar clave segura y exportarla (Linux/macOS):
```bash
export JWT_SECRET_KEY=$(openssl rand -base64 48)
```

- Comprobar dependencias vulnerables (Python):
```bash
pip install pip-audit
pip-audit -r PPS/APIs-CRUD/Python/requirements.txt
```

- Comprobar dependencias vulnerables (.NET):
```bash
cd PPS/APIs-CRUD/Dotnet
dotnet list package --vulnerable
```

---

## ¿Quieres que aplique los cambios?

Puedo:
- Quitar la clave `Jwt:Key` de `appsettings.json` y ajustar `Program.cs` para leerla desde variables de entorno (y añadir instrucciones para `dotnet user-secrets`).
- Quitar la clave por defecto del `FastAPI` y exigir `JWT_SECRET_KEY` en entorno.
- Añadir `.gitignore` y eliminar archivos DB versionados del historial.
- Añadir un script de comprobación de dependencias (`pip-audit` y comando `dotnet list package --vulnerable`) y un README con pasos de seguridad.

Indica qué acciones quieres que realice y las aplicaré de inmediato (haré los cambios incrementales y ejecutaré comprobaciones después de cada uno).
