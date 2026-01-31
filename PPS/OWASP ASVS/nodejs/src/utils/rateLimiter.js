/**
 * Control V6.3.1 L1: Rate Limiting Brute Force
 * 5 intentos fallidos → bloqueo por 5 minutos
 * Por IP + email
 * Contador en sesión con timestamp
 */

// Estructura en memoria: Map<key> = { attempts, firstAttempt, lockedUntil }
const attempts = new Map();

const FAILED_ATTEMPT_LIMIT = 5;
const LOCK_DURATION_MS = 5 * 60 * 1000; // 5 minutos
const ATTEMPT_WINDOW_MS = 15 * 60 * 1000; // 15 minutos para resetear intentos

/**
 * Genera una clave única para rate limiting
 * Combina: IP + email
 */
const generateKey = (ip, email) => {
  return `${ip}:${email}`;
};

/**
 * Registra un intento fallido de login
 * Retorna: { allowed: boolean, attemptsRemaining: number, lockedUntil?: Date }
 */
export const recordFailedAttempt = (ip, email) => {
  const key = generateKey(ip, email);
  const now = Date.now();

  // Obtener registro actual o crear uno nuevo
  let record = attempts.get(key);

  if (!record) {
    // Nuevo registro
    record = {
      attempts: 1,
      firstAttempt: now,
      lockedUntil: null
    };
    attempts.set(key, record);

    return {
      allowed: true,
      attemptsRemaining: FAILED_ATTEMPT_LIMIT - 1,
      locked: false
    };
  }

  // Verificar si está bloqueado
  if (record.lockedUntil && now < record.lockedUntil) {
    const secondsRemaining = Math.ceil((record.lockedUntil - now) / 1000);
    return {
      allowed: false,
      locked: true,
      lockedUntil: new Date(record.lockedUntil),
      secondsRemaining,
      message: `Too many failed attempts. Try again in ${secondsRemaining} seconds.`
    };
  }

  // Resetear si ha pasado el window de tiempo
  if (now - record.firstAttempt > ATTEMPT_WINDOW_MS) {
    record.attempts = 1;
    record.firstAttempt = now;
    record.lockedUntil = null;
    attempts.set(key, record);

    return {
      allowed: true,
      attemptsRemaining: FAILED_ATTEMPT_LIMIT - 1,
      locked: false
    };
  }

  // Incrementar intentos
  record.attempts += 1;

  // Verificar si se alcanzó el límite
  if (record.attempts >= FAILED_ATTEMPT_LIMIT) {
    record.lockedUntil = now + LOCK_DURATION_MS;
    attempts.set(key, record);

    const secondsRemaining = LOCK_DURATION_MS / 1000;
    return {
      allowed: false,
      locked: true,
      lockedUntil: new Date(record.lockedUntil),
      secondsRemaining,
      message: `Too many failed attempts (${record.attempts}/${FAILED_ATTEMPT_LIMIT}). Account locked for ${secondsRemaining} seconds.`,
      attemptsRemaining: 0
    };
  }

  // Aún hay intentos disponibles
  attempts.set(key, record);

  return {
    allowed: true,
    locked: false,
    attemptsRemaining: FAILED_ATTEMPT_LIMIT - record.attempts,
    attempts: record.attempts
  };
};

/**
 * Registra un login exitoso
 * Limpia los intentos fallidos para ese usuario
 */
export const recordSuccessfulAttempt = (ip, email) => {
  const key = generateKey(ip, email);
  attempts.delete(key);
};

/**
 * Obtiene el estado actual de intentos
 */
export const getAttemptStatus = (ip, email) => {
  const key = generateKey(ip, email);
  const record = attempts.get(key);

  if (!record) {
    return {
      attempts: 0,
      locked: false,
      attemptsRemaining: FAILED_ATTEMPT_LIMIT
    };
  }

  const now = Date.now();

  // Verificar si está bloqueado
  if (record.lockedUntil && now < record.lockedUntil) {
    const secondsRemaining = Math.ceil((record.lockedUntil - now) / 1000);
    return {
      attempts: record.attempts,
      locked: true,
      lockedUntil: new Date(record.lockedUntil),
      secondsRemaining,
      attemptsRemaining: 0
    };
  }

  // Resetear si ha pasado el window de tiempo
  if (now - record.firstAttempt > ATTEMPT_WINDOW_MS) {
    attempts.delete(key);
    return {
      attempts: 0,
      locked: false,
      attemptsRemaining: FAILED_ATTEMPT_LIMIT
    };
  }

  return {
    attempts: record.attempts,
    locked: false,
    attemptsRemaining: FAILED_ATTEMPT_LIMIT - record.attempts
  };
};

/**
 * Limpia los intentos fallidos
 * Ejecutar en tests o mantenimiento
 */
export const clearAttempts = () => {
  attempts.clear();
};

/**
 * Obtiene estadísticas de rate limiting
 */
export const getRateLimitStats = () => {
  const stats = {
    totalTrackedKeys: attempts.size,
    lockedAccounts: 0,
    activeAttempts: 0
  };

  const now = Date.now();

  for (const [key, record] of attempts.entries()) {
    // Contar bloqueados
    if (record.lockedUntil && now < record.lockedUntil) {
      stats.lockedAccounts++;
    }

    // Contar activos
    if (now - record.firstAttempt < ATTEMPT_WINDOW_MS) {
      stats.activeAttempts++;
    }
  }

  return stats;
};
