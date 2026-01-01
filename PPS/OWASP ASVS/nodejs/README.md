# OWASP ASVS - Node.js Implementation

Implementación completa de 9 controles de seguridad OWASP ASVS (Application Security Verification Standard) en Node.js.

## Controles Implementados

### 1. Control V3.4 L1 - Headers de Seguridad
**Ubicación:** `src/config/securityHeaders.js`

Headers HTTP de seguridad obligatorios:
- `X-Content-Type-Options: nosniff` - Previene MIME type sniffing
- `X-Frame-Options: DENY` - Previene clickjacking
- `X-XSS-Protection: 1; mode=block` - Protección XSS en navegadores antiguos
- `Referrer-Policy: strict-origin-when-cross-origin` - Control de referrer
- `Permissions-Policy: geolocation=(), microphone=()` - Deshabilita features peligrosas
- `Strict-Transport-Security: max-age=31536000` - HSTS
- `Content-Security-Policy: default-src 'self'` - CSP

**CORS Restringido:** Solo `localhost:3000` y `127.0.0.1:3000`

### 2. Control V6.2.2 L1 - Validación de Contraseña Mejorada
**Ubicación:** `src/utils/validator.js` → `validatePassword()`

Requisitos de contraseña:
- ✅ Mínimo 8 caracteres
- ✅ Mayúscula + minúscula + número
- ✅ Carácter especial (!@#$%^&*)
- ✅ Feedback visual - Strength meter (0-100)
- ✅ Validación en tiempo real con `calculatePasswordStrength()`

Implementación:
```javascript
const validation = validatePassword('MySecureP@ss123');
// { valid: true, strength: 100 }
```

### 3. Control V6.3.1 L1 - Rate Limiting Brute Force
**Ubicación:** `src/utils/rateLimiter.js`

Protección contra ataques de fuerza bruta:
- **Límite:** 5 intentos fallidos
- **Bloqueo:** 5 minutos
- **Granularidad:** Por IP + email
- **Storage:** En memoria con timestamp

Flujo:
```javascript
const result = recordFailedAttempt(ip, email);
if (!result.allowed) {
  // Cuenta bloqueada por 5 minutos
  console.log(result.secondsRemaining); // segundos restantes
}
```

Implementación en `login()`:
```javascript
const rateLimitStatus = getAttemptStatus(ip, username);
if (rateLimitStatus.locked) {
  return res.status(429).json({ error: 'Too many failed attempts' });
}
```

### 4. Control V7.2.1 L1 - JWT con Expiración
**Ubicación:** `src/utils/tokenManager.js`

Tokens JWT seguros:
- **Access Token:** 15 minutos absolutos
- **Refresh Token:** 7 días máximo
- **Algoritmo:** HS256 (allowlist)
- **Tipo Distinguido:** `type: 'access' | 'refresh'`

Generación:
```javascript
const tokens = generateTokens(userId, username, email);
// {
//   accessToken: "eyJhbGc...",
//   refreshToken: "eyJhbGc...",
//   expiresIn: 900,  // segundos
//   tokenType: "Bearer"
// }
```

Tokens incluyen:
```json
{
  "id": 1,
  "username": "user123",
  "email": "user@example.com",
  "type": "access",
  "iat": 1234567890,
  "exp": 1234567890 + 900
}
```

### 5. Control V7.4.1 L1 - Logout + Token Blacklist
**Ubicación:** `src/utils/tokenManager.js`

Sistema de blacklist de tokens:
- **Storage:** `data/tokenBlacklist.json`
- **Validación:** Todo token se verifica contra blacklist
- **Limpieza Automática:** Cada hora se limpian tokens expirados
- **Prevención:** Reutilización de tokens post-logout

Implementación:
```javascript
// En logout
blacklistToken(accessToken);

// En verificación
if (isTokenBlacklisted(token)) {
  throw new Error('Token has been revoked');
}
```

### 6. Control V16.3.1 L2 - Logging Completo de Eventos
**Ubicación:** `src/utils/logger.js`

Eventos registrados en `logs/security.log`:

#### Tipos de eventos:
- `LOGIN_SUCCESS` → user_id, método (local/oauth)
- `LOGIN_FAILED` → intentos restantes
- `LOGIN_LOCKED` → minutos bloqueado
- `USER_REGISTERED` → user_id, email
- `LOGOUT` → user_id
- `ADMIN_ACCESS` → user_id, endpoint, método
- `INVALID_TOKEN` → razón
- `TOKEN_BLACKLISTED` → intento de uso
- `PASSWORD_CHANGED` → user_id
- `OAUTH_STATE_MISMATCH` → alerta CSRF

Formato del log (JSON):
```json
{
  "timestamp": "2025-01-31T10:30:45.123Z",
  "eventType": "LOGIN_SUCCESS",
  "userId": "5",
  "ip": "127.0.0.1",
  "userAgent": "Mozilla/5.0...",
  "status": "SUCCESS",
  "message": "User 5 logged in successfully via local",
  "additionalData": { "method": "local" }
}
```

Funciones disponibles:
```javascript
logLoginSuccess(userId, method, ip, userAgent);
logLoginFailed(username, reason, attemptsRemaining, ip, userAgent);
logLoginLocked(username, lockedMinutes, ip, userAgent);
logUserRegistered(userId, email, ip, userAgent);
logLogout(userId, ip, userAgent);
logAdminAccess(userId, endpoint, method, ip, userAgent);
getSecurityLogs(lines = 100); // Obtener últimos logs
```

### 7. Control V2.2.1 L1 - Validación Input Backend
**Ubicación:** `src/utils/validator.js`

Validación y sanitización explícita:

#### Email
```javascript
const validation = validateEmail('user@example.com');
if (!validation.valid) {
  // error: string
} else {
  // sanitized: 'user@example.com'
}
```
- Usa `validator.isEmail()` (FILTER_VALIDATE_EMAIL style)
- Normaliza dominios

#### Username
```javascript
const validation = validateUsername('john_doe');
// 3-20 caracteres, alfanuméricos + guiones bajos
```

#### Nombre de Usuario
```javascript
const validation = validateDisplayName("John Doe");
// 2-100 caracteres, solo letras + espacios + guiones + apóstrofes
```

#### Sanitización HTML
```javascript
const safe = sanitizeString('<script>alert("xss")</script>');
// Escapa: &lt;script&gt; ...
```

#### Validación requerida
```javascript
const validation = validateRequired(field, 'Username');
```

Reglas:
- Email: RFC 5322 estándar
- Password: Regex complejidad
- Username: `^[a-zA-Z0-9_]+$`
- Nombres: `^[a-zA-Z\s\-']+$`
- Sanitización: htmlspecialchars() equivalente

### 8. Control V8.3.1 L1 - Autenticación JWT Backend
**Ubicación:** `src/middleware/authMiddleware.js`

Middleware de verificación JWT:

```javascript
router.get('/admin', verificarAuth, (req, res) => {
  // req.user contiene el payload del token
  // {
  //   id: 1,
  //   username: 'user123',
  //   email: 'user@example.com',
  //   type: 'access',
  //   iat: 1234567890,
  //   exp: 1234567890 + 900
  // }
});
```

Validaciones:
- ✅ Token en formato `Authorization: Bearer <token>`
- ✅ Solo acepta algoritmo HS256 (allowlist)
- ✅ Solo acepta tipo `access` (no `refresh`)
- ✅ Verifica expiración automática
- ✅ Verifica contra blacklist
- ✅ Logging de intentos inválidos

Middleware opcionales:
```javascript
import { verificarAuthOpcional } from './authMiddleware.js';
import { verificarRefreshToken } from './authMiddleware.js';
import { verificarRol } from './authMiddleware.js';

// Autenticación opcional (no rechaza)
app.get('/public', verificarAuthOpcional, handler);

// Solo refresh tokens
app.post('/refresh', verificarRefreshToken, handler);

// Requiere rol específico
app.delete('/admin', verificarAuth, verificarRol(['admin']), handler);
```

### 9. Control V10.1.2 L1 - CSRF Protection OAuth
**Ubicación:** `src/utils/oauthState.js`

Protección contra CSRF en flujo OAuth GitHub:

#### Flujo de seguridad:
1. **Generación de State:**
   ```javascript
   const state = generateOAuthState();
   // Genera: bin2hex(random_bytes(16)) - 32 caracteres hex
   saveOAuthState(state); // Almacena en JSON con TTL 10 min
   ```

2. **Redirección a GitHub:**
   ```javascript
   // GET /usuarios/auth/github
   const authUrl = `https://github.com/login/oauth/authorize?
     client_id=...&
     redirect_uri=...&
     state=${state}&
     scope=user:email`;
   ```

3. **Validación en Callback:**
   ```javascript
   // GET /usuarios/auth/github/callback?code=xxx&state=yyy
   const validation = validateOAuthState(state);
   if (!validation.valid) {
     logOAuthStateMismatch(ip, userAgent);
     return res.status(400).json({ error: 'CSRF detected' });
   }
   ```

Características de seguridad:
- State único por request
- TTL: 10 minutos
- Single-use (se borra después de validar)
- Validación strict
- Logging de intentos fallidos
- Limpieza automática cada 30 minutos

## Instalación

```bash
cd nodejs
npm install
cp .env.example .env
```

Configurar `.env`:
```env
PORT=3000
NODE_ENV=development
JWT_SECRET=your-super-secret-key
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URI=http://localhost:3000/usuarios/auth/github/callback
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://127.0.0.1:3000
```

## Ejecución

```bash
npm start        # Modo producción
npm run dev      # Modo desarrollo (con watch)
```

El servidor se inicia en `http://localhost:3000`

## Estructura de Carpetas

```
nodejs/
├── src/
│   ├── config/
│   │   └── securityHeaders.js         # Control V3.4
│   ├── middleware/
│   │   ├── authMiddleware.js          # Control V8.3.1
│   │   └── errorHandler.js
│   ├── models/
│   │   └── user.js
│   ├── controllers/
│   │   └── userController.js          # Controles V2.2.1, V6.2.2, V6.3.1, V7.2.1, V7.4.1, V16.3.1
│   ├── routes/
│   │   └── users.js
│   ├── utils/
│   │   ├── validator.js               # Control V2.2.1
│   │   ├── tokenManager.js            # Control V7.2.1, V7.4.1
│   │   ├── rateLimiter.js             # Control V6.3.1
│   │   ├── logger.js                  # Control V16.3.1
│   │   └── oauthState.js              # Control V10.1.2
│   ├── db/
│   │   └── db.js
│   └── index.js                       # Entry point
├── data/
│   ├── tokenBlacklist.json            # Control V7.4.1
│   └── oauthStates.json               # Control V10.1.2
├── logs/
│   └── security.log                   # Control V16.3.1
├── package.json
├── .env.example
└── README.md
```

## API Endpoints

### Autenticación

#### Registro
```bash
POST /usuarios/register
Content-Type: application/json

{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "MySecureP@ss123",
  "passwordConfirm": "MySecureP@ss123"
}
```

**Respuesta (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "passwordStrength": 100,
    "lastPasswordChange": "2025-01-31T10:30:45.123Z",
    "createdAt": "2025-01-31T10:30:45.123Z",
    "updatedAt": "2025-01-31T10:30:45.123Z"
  }
}
```

#### Login Local
```bash
POST /usuarios/login
Content-Type: application/json

{
  "username": "john_doe",
  "password": "MySecureP@ss123"
}
```

**Respuesta (200):**
```json
{
  "message": "Login successful",
  "accessToken": "eyJhbGc...",
  "refreshToken": "eyJhbGc...",
  "expiresIn": 900,
  "tokenType": "Bearer",
  "user": { ... }
}
```

**Respuesta (401 - Rate Limited):**
```json
{
  "error": "Too many failed attempts",
  "message": "Try again in 234 seconds",
  "secondsRemaining": 234
}
```

#### GitHub OAuth - Obtener URL
```bash
GET /usuarios/auth/github

# Respuesta
{
  "authUrl": "https://github.com/login/oauth/authorize?client_id=...&state=..."
}
```

#### GitHub OAuth - Callback
```bash
GET /usuarios/auth/github/callback?code=xxx&state=yyy
# Redirige a frontend con tokens en query params
```

#### Logout
```bash
POST /usuarios/logout
Authorization: Bearer {accessToken}

# Respuesta
{
  "message": "Logout successful"
}
```

### Usuarios (Requieren JWT)

#### Obtener Todos
```bash
GET /usuarios
Authorization: Bearer {accessToken}
```

#### Obtener por ID
```bash
GET /usuarios/:id
Authorization: Bearer {accessToken}
```

#### Actualizar
```bash
PUT /usuarios/:id
Authorization: Bearer {accessToken}
Content-Type: application/json

{
  "email": "newemail@example.com",
  "password": "NewSecureP@ss456"  // Opcional
}
```

#### Eliminar
```bash
DELETE /usuarios/:id
Authorization: Bearer {accessToken}
```

## Testing

### Test de Contraseña
```javascript
import { validatePassword, calculatePasswordStrength } from './src/utils/validator.js';

// Test 1: Contraseña válida
const valid = validatePassword('MySecureP@ss123');
console.log(valid); // { valid: true, strength: 100 }

// Test 2: Contraseña corta
const short = validatePassword('Short1!');
console.log(short);
// {
//   valid: false,
//   errors: ['At least 8 characters', ...],
//   strength: 50
// }

// Test 3: Strength en tiempo real
const strength = calculatePasswordStrength('MyPass123!@#$');
console.log(strength); // 95
```

### Test de Rate Limiting
```javascript
import { recordFailedAttempt, getAttemptStatus } from './src/utils/rateLimiter.js';

const ip = '127.0.0.1';
const email = 'user@example.com';

// Simular 5 intentos fallidos
for (let i = 0; i < 5; i++) {
  const result = recordFailedAttempt(ip, email);
  console.log(`Attempt ${i + 1}:`, result);
  // Intento 1: { allowed: true, attemptsRemaining: 4 }
  // Intento 5: { allowed: false, locked: true, secondsRemaining: 300 }
}

// Verificar estado
const status = getAttemptStatus(ip, email);
console.log(status);
// { locked: true, secondsRemaining: 298, ... }
```

### Test de JWT
```javascript
import { generateTokens, verifyToken, blacklistToken } from './src/utils/tokenManager.js';

// Generar tokens
const tokens = generateTokens(1, 'john_doe', 'john@example.com');

// Verificar access token
const decoded = verifyToken(tokens.accessToken, 'access');
console.log(decoded); // { id: 1, username: 'john_doe', type: 'access', ... }

// Intentar con refresh token (falla)
try {
  verifyToken(tokens.refreshToken, 'access'); // Error: Invalid token type
} catch (err) {
  console.log(err.message);
}

// Blacklist y verificación
blacklistToken(tokens.accessToken);
try {
  verifyToken(tokens.accessToken, 'access'); // Error: Token has been revoked
} catch (err) {
  console.log(err.message);
}
```

### Test de Validación Input
```javascript
import { 
  validateEmail, 
  validateUsername, 
  validatePassword,
  sanitizeString 
} from './src/utils/validator.js';

// Email
validateEmail('user@example.com');
// { valid: true, sanitized: 'user@example.com' }

// Username
validateUsername('john_doe');
// { valid: true, sanitized: 'john_doe' }

// Sanitización
sanitizeString('<script>alert("xss")</script>');
// '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;'
```

## Logging y Monitoreo

### Ver Logs de Seguridad
```javascript
import { getSecurityLogs } from './src/utils/logger.js';

const logs = getSecurityLogs(50); // Últimos 50 eventos
logs.forEach(log => {
  console.log(`[${log.timestamp}] ${log.eventType} - ${log.message}`);
});
```

### Ejemplo de Logs
```
[2025-01-31T10:30:45.123Z] USER_REGISTERED - New user registered: john_doe (john@example.com)
[2025-01-31T10:31:12.456Z] LOGIN_SUCCESS - User 1 logged in successfully via local
[2025-01-31T10:35:00.789Z] LOGIN_FAILED - Failed login attempt for john_doe - Invalid password
[2025-01-31T10:36:45.012Z] LOGIN_LOCKED - Account locked for john_doe - too many failed attempts
[2025-01-31T10:38:15.345Z] LOGOUT - User 1 logged out
[2025-01-31T10:39:30.678Z] INVALID_TOKEN - Token verification failed: Token has expired
[2025-01-31T10:40:50.901Z] OAUTH_STATE_MISMATCH - OAuth state parameter mismatch - possible CSRF attack
```

## Variables de Entorno

| Variable | Descripción | Default |
|----------|-------------|---------|
| `PORT` | Puerto del servidor | 3000 |
| `NODE_ENV` | Entorno (development/production) | development |
| `JWT_SECRET` | Clave para firmar JWT | change-this |
| `JWT_ALGORITHM` | Algoritmo JWT (solo HS256) | HS256 |
| `ACCESS_TOKEN_EXPIRY` | Expiración access token | 15m |
| `REFRESH_TOKEN_EXPIRY` | Expiración refresh token | 7d |
| `DB_PATH` | Ruta SQLite | ./database.sqlite |
| `GITHUB_CLIENT_ID` | ID de cliente GitHub OAuth | - |
| `GITHUB_CLIENT_SECRET` | Secret de cliente GitHub | - |
| `GITHUB_REDIRECT_URI` | URI de redirección OAuth | - |
| `CORS_ALLOWED_ORIGINS` | Orígenes CORS permitidos | localhost:3000 |
| `LOG_FILE` | Ruta de logs | ./logs/security.log |

## Resumen de Controles

| Control | Ubicación | Descripción |
|---------|-----------|-------------|
| **V3.4 L1** | `config/securityHeaders.js` | Headers de seguridad HTTP (HSTS, CSP, etc) |
| **V6.2.2 L1** | `utils/validator.js` | Validación de contraseña mejorada |
| **V6.3.1 L1** | `utils/rateLimiter.js` | Rate limiting contra brute force |
| **V7.2.1 L1** | `utils/tokenManager.js` | JWT con expiración y refresh tokens |
| **V7.4.1 L1** | `utils/tokenManager.js` | Logout y token blacklist |
| **V16.3.1 L2** | `utils/logger.js` | Logging completo de eventos |
| **V2.2.1 L1** | `utils/validator.js` | Validación input backend |
| **V8.3.1 L1** | `middleware/authMiddleware.js` | Autenticación JWT backend |
| **V10.1.2 L1** | `utils/oauthState.js` | CSRF protection en OAuth |

## Notas de Seguridad

⚠️ **Producción:**
- Cambiar `JWT_SECRET` a una clave fuerte
- Usar HTTPS obligatoriamente
- Configurar `NODE_ENV=production`
- Usar base de datos persistente (PostgreSQL/MySQL)
- Implementar HTTPS/TLS para OAuth
- Usar variables de entorno seguros (no en código)
- Configurar logging persistente
- Implementar rotación de logs

⚠️ **CORS:**
- Solo acepta `localhost:3000` y `127.0.0.1:3000`
- Cambiar en producción según dominio real

⚠️ **Refresh Tokens:**
- Los refresh tokens se envían en query params (demo)
- En producción: usar httpOnly cookies
- Rotar tokens periódicamente

## Referencias OWASP

- [OWASP ASVS 4.0](https://owasp.org/www-project-application-security-verification-standard/)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [OWASP Cross-Site Request Forgery (CSRF) Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)

## Licencia

MIT
