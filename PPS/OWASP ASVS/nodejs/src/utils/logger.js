import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const LOG_DIR = path.join(__dirname, '../../logs');
const LOG_FILE = path.join(LOG_DIR, 'security.log');

// Crear directorio de logs si no existe
if (!fs.existsSync(LOG_DIR)) {
  fs.mkdirSync(LOG_DIR, { recursive: true });
}

/**
 * Escribe un evento de seguridad en el log
 * Incluye timestamp, evento, usuario, IP y detalles
 */
export const securityLog = (eventType, details = {}) => {
  const timestamp = new Date().toISOString();
  
  const logEntry = {
    timestamp,
    eventType,
    userId: details.userId || 'ANONYMOUS',
    ip: details.ip || 'UNKNOWN',
    userAgent: details.userAgent || 'UNKNOWN',
    status: details.status || 'INFO',
    message: details.message || '',
    additionalData: details.additionalData || {}
  };

  const logString = JSON.stringify(logEntry) + '\n';

  try {
    fs.appendFileSync(LOG_FILE, logString);
    console.log(`[${eventType}] ${logEntry.message}`);
  } catch (error) {
    console.error('Error writing to security log:', error);
  }
};

/**
 * LOGIN_SUCCESS - Login exitoso
 * user_id, método (local/oauth)
 */
export const logLoginSuccess = (userId, method, ip, userAgent) => {
  securityLog('LOGIN_SUCCESS', {
    userId,
    ip,
    userAgent,
    status: 'SUCCESS',
    message: `User ${userId} logged in successfully via ${method}`,
    additionalData: { method }
  });
};

/**
 * LOGIN_FAILED - Login fallido
 * intentos restantes
 */
export const logLoginFailed = (username, reason, attemptsRemaining, ip, userAgent) => {
  securityLog('LOGIN_FAILED', {
    userId: username,
    ip,
    userAgent,
    status: 'FAILED',
    message: `Failed login attempt for ${username} - ${reason}`,
    additionalData: { 
      reason,
      attemptsRemaining,
      timestamp: new Date().toISOString()
    }
  });
};

/**
 * LOGIN_LOCKED - Cuenta bloqueada por rate limiting
 * minutos bloqueado
 */
export const logLoginLocked = (username, lockedMinutes, ip, userAgent) => {
  securityLog('LOGIN_LOCKED', {
    userId: username,
    ip,
    userAgent,
    status: 'SECURITY_ALERT',
    message: `Account locked for ${username} - too many failed attempts`,
    additionalData: { 
      lockedMinutes,
      timestamp: new Date().toISOString()
    }
  });
};

/**
 * USER_REGISTERED - Nuevo usuario registrado
 * user_id, email
 */
export const logUserRegistered = (userId, email, ip, userAgent) => {
  securityLog('USER_REGISTERED', {
    userId,
    ip,
    userAgent,
    status: 'SUCCESS',
    message: `New user registered: ${userId} (${email})`,
    additionalData: { email }
  });
};

/**
 * LOGOUT - Usuario deslogueado
 * user_id
 */
export const logLogout = (userId, ip, userAgent) => {
  securityLog('LOGOUT', {
    userId,
    ip,
    userAgent,
    status: 'SUCCESS',
    message: `User ${userId} logged out`,
    additionalData: { timestamp: new Date().toISOString() }
  });
};

/**
 * ADMIN_ACCESS - Acceso a endpoints administrativos
 * user_id, endpoint, método
 */
export const logAdminAccess = (userId, endpoint, method, ip, userAgent) => {
  securityLog('ADMIN_ACCESS', {
    userId,
    ip,
    userAgent,
    status: 'INFO',
    message: `Admin access: ${userId} accessed ${endpoint} via ${method}`,
    additionalData: { endpoint, method }
  });
};

/**
 * INVALID_TOKEN - Token inválido o expirado
 */
export const logInvalidToken = (reason, ip, userAgent) => {
  securityLog('INVALID_TOKEN', {
    ip,
    userAgent,
    status: 'SECURITY_ALERT',
    message: `Invalid token attempt: ${reason}`,
    additionalData: { reason }
  });
};

/**
 * TOKEN_BLACKLISTED - Intento de usar token en blacklist
 */
export const logTokenBlacklisted = (ip, userAgent) => {
  securityLog('TOKEN_BLACKLISTED', {
    ip,
    userAgent,
    status: 'SECURITY_ALERT',
    message: 'Attempt to use blacklisted token',
    additionalData: { timestamp: new Date().toISOString() }
  });
};

/**
 * PASSWORD_CHANGED - Contraseña cambiada
 */
export const logPasswordChanged = (userId, ip, userAgent) => {
  securityLog('PASSWORD_CHANGED', {
    userId,
    ip,
    userAgent,
    status: 'SUCCESS',
    message: `Password changed for user ${userId}`,
    additionalData: {}
  });
};

/**
 * OAUTH_STATE_MISMATCH - CSRF protection: state parameter mismatch
 */
export const logOAuthStateMismatch = (ip, userAgent) => {
  securityLog('OAUTH_STATE_MISMATCH', {
    ip,
    userAgent,
    status: 'SECURITY_ALERT',
    message: 'OAuth state parameter mismatch - possible CSRF attack',
    additionalData: { timestamp: new Date().toISOString() }
  });
};

/**
 * OAUTH_SUCCESS - OAuth login exitoso
 */
export const logOAuthSuccess = (userId, provider, ip, userAgent) => {
  securityLog('OAUTH_SUCCESS', {
    userId,
    ip,
    userAgent,
    status: 'SUCCESS',
    message: `User ${userId} logged in via ${provider}`,
    additionalData: { provider }
  });
};

/**
 * Obtiene los logs de seguridad
 */
export const getSecurityLogs = (lines = 100) => {
  try {
    if (!fs.existsSync(LOG_FILE)) {
      return [];
    }

    const content = fs.readFileSync(LOG_FILE, 'utf8');
    const logLines = content.trim().split('\n');
    
    return logLines.slice(-lines).map(line => {
      try {
        return JSON.parse(line);
      } catch {
        return { raw: line };
      }
    });
  } catch (error) {
    console.error('Error reading security logs:', error);
    return [];
  }
};
