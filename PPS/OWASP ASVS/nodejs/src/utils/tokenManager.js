import jwt from 'jsonwebtoken';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const BLACKLIST_FILE = path.join(__dirname, '../../data/tokenBlacklist.json');

// Crear directorio de datos si no existe
const DATA_DIR = path.dirname(BLACKLIST_FILE);
if (!fs.existsSync(DATA_DIR)) {
  fs.mkdirSync(DATA_DIR, { recursive: true });
}

// Crear archivo de blacklist si no existe
if (!fs.existsSync(BLACKLIST_FILE)) {
  fs.writeFileSync(BLACKLIST_FILE, JSON.stringify([]));
}

const JWT_SECRET = process.env.JWT_SECRET || 'change-this-in-production';
const ALLOWED_ALGORITHMS = ['HS256']; // Allowlist de algoritmos

/**
 * Genera un par de tokens (access + refresh)
 * V7.2.1 L1: Access token 15 minutos, Refresh token 7 días
 */
export const generateTokens = (userId, username, email) => {
  if (!userId || !username) {
    throw new Error('userId and username are required');
  }

  const now = Math.floor(Date.now() / 1000);

  // Access Token - 15 minutos (900 segundos)
  const accessToken = jwt.sign(
    {
      id: userId,
      username,
      email,
      type: 'access',
      iat: now,
      exp: now + (15 * 60) // 15 minutos
    },
    JWT_SECRET,
    { algorithm: 'HS256' }
  );

  // Refresh Token - 7 días
  const refreshToken = jwt.sign(
    {
      id: userId,
      username,
      type: 'refresh',
      iat: now,
      exp: now + (7 * 24 * 60 * 60) // 7 días
    },
    JWT_SECRET,
    { algorithm: 'HS256' }
  );

  return {
    accessToken,
    refreshToken,
    expiresIn: 15 * 60, // segundos
    tokenType: 'Bearer'
  };
};

/**
 * Verifica un token JWT
 * V8.3.1 L1: Verificación con allowlist de algoritmo y tipo
 */
export const verifyToken = (token, expectedType = 'access') => {
  if (!token) {
    throw new Error('Token is required');
  }

  try {
    // Verificar que el token está en blacklist
    if (isTokenBlacklisted(token)) {
      throw new Error('Token has been revoked');
    }

    // Decodificar y verificar con HS256
    const decoded = jwt.verify(token, JWT_SECRET, {
      algorithms: ALLOWED_ALGORITHMS
    });

    // Validar tipo de token
    if (decoded.type !== expectedType) {
      throw new Error(`Invalid token type. Expected ${expectedType}, got ${decoded.type}`);
    }

    return decoded;
  } catch (error) {
    throw new Error(`Token verification failed: ${error.message}`);
  }
};

/**
 * Refresca un access token usando un refresh token
 */
export const refreshAccessToken = (refreshToken) => {
  try {
    const decoded = verifyToken(refreshToken, 'refresh');

    // Generar nuevo access token
    const newAccessToken = jwt.sign(
      {
        id: decoded.id,
        username: decoded.username,
        email: decoded.email,
        type: 'access',
        iat: Math.floor(Date.now() / 1000),
        exp: Math.floor(Date.now() / 1000) + (15 * 60)
      },
      JWT_SECRET,
      { algorithm: 'HS256' }
    );

    return {
      accessToken: newAccessToken,
      expiresIn: 15 * 60,
      tokenType: 'Bearer'
    };
  } catch (error) {
    throw new Error(`Failed to refresh token: ${error.message}`);
  }
};

/**
 * Añade un token a la blacklist (para logout)
 * V7.4.1 L1: Logout + Token Blacklist
 */
export const blacklistToken = (token) => {
  try {
    // No decodificar completamente, solo obtener el JWT sin verificar
    const decoded = jwt.decode(token);

    if (!decoded || !decoded.exp) {
      throw new Error('Invalid token');
    }

    const blacklist = loadBlacklist();

    // Verificar si el token ya está en blacklist
    if (blacklist.some(entry => entry.token === token)) {
      return; // Ya está en blacklist
    }

    // Añadir a blacklist con su tiempo de expiración
    blacklist.push({
      token,
      addedAt: new Date().toISOString(),
      expiresAt: new Date(decoded.exp * 1000).toISOString()
    });

    // Guardar blacklist
    fs.writeFileSync(BLACKLIST_FILE, JSON.stringify(blacklist, null, 2));
  } catch (error) {
    console.error('Error blacklisting token:', error);
  }
};

/**
 * Verifica si un token está en la blacklist
 */
export const isTokenBlacklisted = (token) => {
  try {
    const blacklist = loadBlacklist();

    const isBlacklisted = blacklist.some(entry => entry.token === token);

    return isBlacklisted;
  } catch (error) {
    console.error('Error checking token blacklist:', error);
    return false;
  }
};

/**
 * Carga la blacklist de tokens desde archivo
 */
const loadBlacklist = () => {
  try {
    if (!fs.existsSync(BLACKLIST_FILE)) {
      return [];
    }

    const content = fs.readFileSync(BLACKLIST_FILE, 'utf8');
    return JSON.parse(content) || [];
  } catch (error) {
    console.error('Error loading blacklist:', error);
    return [];
  }
};

/**
 * Limpia tokens expirados de la blacklist
 * Ejecutar periódicamente para mantener el archivo limpio
 */
export const cleanupExpiredTokens = () => {
  try {
    const blacklist = loadBlacklist();
    const now = new Date();

    // Filtrar tokens que aún están expirados
    const cleaned = blacklist.filter(entry => {
      const expiresAt = new Date(entry.expiresAt);
      return expiresAt > now;
    });

    if (cleaned.length !== blacklist.length) {
      fs.writeFileSync(BLACKLIST_FILE, JSON.stringify(cleaned, null, 2));
      console.log(`Cleaned ${blacklist.length - cleaned.length} expired tokens from blacklist`);
    }
  } catch (error) {
    console.error('Error cleaning expired tokens:', error);
  }
};

/**
 * Obtiene la información del token sin verificar firma
 * Útil para debugging (NO usar en producción)
 */
export const decodeTokenForDebug = (token) => {
  return jwt.decode(token);
};

// Ejecutar limpieza cada hora
setInterval(cleanupExpiredTokens, 60 * 60 * 1000);
