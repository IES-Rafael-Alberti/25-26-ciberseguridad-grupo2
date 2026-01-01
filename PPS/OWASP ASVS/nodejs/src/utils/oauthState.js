
import crypto from 'crypto';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OAUTH_STATE_FILE = path.join(__dirname, '../../data/oauthStates.json');

// Crear directorio de datos si no existe
const DATA_DIR = path.dirname(OAUTH_STATE_FILE);
if (!fs.existsSync(DATA_DIR)) {
  fs.mkdirSync(DATA_DIR, { recursive: true });
}

// Crear archivo si no existe
if (!fs.existsSync(OAUTH_STATE_FILE)) {
  fs.writeFileSync(OAUTH_STATE_FILE, JSON.stringify({}));
}

/**
 * Genera un state parameter seguro para OAuth
 * bin2hex(random_bytes(16)) en Node.js
 */
export const generateOAuthState = () => {
  // Generar 16 bytes aleatorios y convertir a hex
  return crypto.randomBytes(16).toString('hex');
};

/**
 * Guarda el state parameter con su timestamp
 * Para validación posterior
 */
export const saveOAuthState = (state) => {
  try {
    const states = loadOAuthStates();

    states[state] = {
      createdAt: Date.now(),
      expiresAt: Date.now() + (10 * 60 * 1000) // 10 minutos
    };

    fs.writeFileSync(OAUTH_STATE_FILE, JSON.stringify(states, null, 2));
  } catch (error) {
    console.error('Error saving OAuth state:', error);
    throw error;
  }
};

/**
 * Valida un state parameter
 * Previene CSRF en el callback de GitHub
 */
export const validateOAuthState = (state) => {
  try {
    if (!state) {
      return { valid: false, reason: 'State parameter missing' };
    }

    const states = loadOAuthStates();

    if (!states[state]) {
      return { valid: false, reason: 'State parameter not found or expired' };
    }

    const stateData = states[state];
    const now = Date.now();

    // Verificar que no haya expirado
    if (now > stateData.expiresAt) {
      // Borrar el estado expirado
      delete states[state];
      fs.writeFileSync(OAUTH_STATE_FILE, JSON.stringify(states, null, 2));
      return { valid: false, reason: 'State parameter expired' };
    }

    // Borrar el estado después de validar (single-use)
    delete states[state];
    fs.writeFileSync(OAUTH_STATE_FILE, JSON.stringify(states, null, 2));

    return { valid: true };
  } catch (error) {
    console.error('Error validating OAuth state:', error);
    return { valid: false, reason: 'Internal error' };
  }
};

/**
 * Carga los states almacenados
 */
const loadOAuthStates = () => {
  try {
    if (!fs.existsSync(OAUTH_STATE_FILE)) {
      return {};
    }

    const content = fs.readFileSync(OAUTH_STATE_FILE, 'utf8');
    return JSON.parse(content) || {};
  } catch (error) {
    console.error('Error loading OAuth states:', error);
    return {};
  }
};

/**
 * Limpia states expirados
 * Ejecutar periódicamente
 */
export const cleanupExpiredStates = () => {
  try {
    const states = loadOAuthStates();
    const now = Date.now();
    let cleaned = 0;

    for (const [state, data] of Object.entries(states)) {
      if (now > data.expiresAt) {
        delete states[state];
        cleaned++;
      }
    }

    if (cleaned > 0) {
      fs.writeFileSync(OAUTH_STATE_FILE, JSON.stringify(states, null, 2));
      console.log(`Cleaned ${cleaned} expired OAuth states`);
    }
  } catch (error) {
    console.error('Error cleaning expired OAuth states:', error);
  }
};

// Ejecutar limpieza cada 30 minutos
setInterval(cleanupExpiredStates, 30 * 60 * 1000);
