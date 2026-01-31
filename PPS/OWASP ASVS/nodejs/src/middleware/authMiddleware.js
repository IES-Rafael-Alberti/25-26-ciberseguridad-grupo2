/**
 * Control V8.3.1 L1: Autenticación JWT Backend
 * Middleware para verificar JWT tokens con:
 * - Verificación de JWT válido
 * - Solo acepta "access" type (allowlist algoritmo)
 * - Decodifica con jsonwebtoken (HS256)
 * - Previene token inválido/expirado
 */

import { verifyToken } from '../utils/tokenManager.js';
import { logInvalidToken } from '../utils/logger.js';

/**
 * Middleware para verificar autenticación JWT
 * Extrae el token del header Authorization: Bearer <token>
 */
export const verificarAuth = (req, res, next) => {
  try {
    // Obtener token del header Authorization
    const authHeader = req.headers.authorization;

    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        error: 'No token provided',
        message: 'Authorization header with Bearer token is required'
      });
    }

    // Extraer token
    const token = authHeader.slice(7); // Remover "Bearer "

    // Verificar token (solo acepta type: 'access')
    const decoded = verifyToken(token, 'access');

    // Adjuntar usuario al request
    req.user = decoded;

    next();
  } catch (error) {
    const ip = req.ip || req.connection.remoteAddress || 'UNKNOWN';
    const userAgent = req.get('user-agent') || 'UNKNOWN';

    logInvalidToken(error.message, ip, userAgent);

    let statusCode = 401;
    let message = 'Invalid token';

    // Mensajes específicos
    if (error.message.includes('expired')) {
      statusCode = 401;
      message = 'Token has expired';
    } else if (error.message.includes('revoked')) {
      statusCode = 401;
      message = 'Token has been revoked';
    } else if (error.message.includes('type')) {
      statusCode = 403;
      message = 'Invalid token type';
    } else if (error.message.includes('algorithm')) {
      statusCode = 403;
      message = 'Invalid token algorithm';
    }

    return res.status(statusCode).json({
      error: message,
      details: error.message
    });
  }
};

/**
 * Middleware opcional para verificación de token
 * No rechaza si no hay token, pero lo verifica si existe
 */
export const verificarAuthOpcional = (req, res, next) => {
  try {
    const authHeader = req.headers.authorization;

    if (authHeader && authHeader.startsWith('Bearer ')) {
      const token = authHeader.slice(7);
      const decoded = verifyToken(token, 'access');
      req.user = decoded;
    }

    next();
  } catch (error) {
    // No rechazar si hay error, solo ignorar
    next();
  }
};

/**
 * Middleware para verificar refresh token
 * Usado en endpoint para refrescar access token
 */
export const verificarRefreshToken = (req, res, next) => {
  try {
    const { refreshToken } = req.body;

    if (!refreshToken) {
      return res.status(400).json({
        error: 'Refresh token is required'
      });
    }

    // Verificar que es un refresh token
    const decoded = verifyToken(refreshToken, 'refresh');

    req.user = decoded;
    next();
  } catch (error) {
    const ip = req.ip || req.connection.remoteAddress || 'UNKNOWN';
    const userAgent = req.get('user-agent') || 'UNKNOWN';

    logInvalidToken(error.message, ip, userAgent);

    return res.status(401).json({
      error: 'Invalid refresh token',
      details: error.message
    });
  }
};

/**
 * Middleware para requerir rol específico
 * Ejemplo: verificarRol('admin')
 */
export const verificarRol = (rolesRequeridos = []) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ error: 'Unauthorized' });
    }

    if (rolesRequeridos.length > 0 && !rolesRequeridos.includes(req.user.role)) {
      return res.status(403).json({ error: 'Insufficient permissions' });
    }

    next();
  };
};
