import validator from 'validator';

export const validateEmail = (email) => {
  if (!email || typeof email !== 'string') {
    return { valid: false, error: 'Email is required' };
  }

  const trimmed = email.trim();

  if (!validator.isEmail(trimmed)) {
    return { valid: false, error: 'Invalid email format' };
  }

  return { valid: true, sanitized: validator.normalizeEmail(trimmed) };
};

/**
 * Valida un nombre de usuario
 * - Alfanuméricos y guiones bajos
 * - 3-20 caracteres
 */
export const validateUsername = (username) => {
  if (!username || typeof username !== 'string') {
    return { valid: false, error: 'Username is required' };
  }

  const trimmed = username.trim();

  if (trimmed.length < 3 || trimmed.length > 20) {
    return { valid: false, error: 'Username must be 3-20 characters' };
  }

  if (!/^[a-zA-Z0-9_]+$/.test(trimmed)) {
    return { valid: false, error: 'Username can only contain letters, numbers, and underscores' };
  }

  return { valid: true, sanitized: trimmed };
};

/**
 * Valida una contraseña
 * V6.2.2 L1 Requirements:
 * ✅ Mínimo 8 caracteres
 * ✅ Mayúscula + minúscula + número
 * ✅ Carácter especial (!@#$%^&*)
 */
export const validatePassword = (password) => {
  if (!password || typeof password !== 'string') {
    return { 
      valid: false, 
      error: 'Password is required',
      strength: 0
    };
  }

  const errors = [];
  let strength = 0;

  // Longitud mínima
  if (password.length < 8) {
    errors.push('At least 8 characters');
  } else {
    strength += 25;
  }

  // Mayúscula
  if (!/[A-Z]/.test(password)) {
    errors.push('At least one uppercase letter');
  } else {
    strength += 25;
  }

  // Minúscula
  if (!/[a-z]/.test(password)) {
    errors.push('At least one lowercase letter');
  } else {
    strength += 25;
  }

  // Número
  if (!/[0-9]/.test(password)) {
    errors.push('At least one number');
  } else {
    strength += 12;
  }

  // Carácter especial
  if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
    errors.push('At least one special character (!@#$%^&*)');
  } else {
    strength += 13;
  }

  if (errors.length > 0) {
    return { 
      valid: false, 
      errors,
      strength: Math.min(strength, 100)
    };
  }

  return { 
    valid: true, 
    strength: 100 
  };
};

/**
 * Calcula la fortaleza de la contraseña (0-100)
 * Usado para feedback visual en tiempo real (strength meter)
 */
export const calculatePasswordStrength = (password) => {
  if (!password) return 0;

  let strength = 0;

  // Longitud
  if (password.length >= 8) strength += 20;
  if (password.length >= 12) strength += 10;
  if (password.length >= 16) strength += 10;

  // Tipos de caracteres
  if (/[a-z]/.test(password)) strength += 15;
  if (/[A-Z]/.test(password)) strength += 15;
  if (/[0-9]/.test(password)) strength += 15;
  if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 15;

  return Math.min(strength, 100);
};

/**
 * Sanitiza un string para HTML
 * Previene XSS escapando caracteres especiales
 */
export const sanitizeString = (str) => {
  if (typeof str !== 'string') return '';
  
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
};

/**
 * Valida y sanitiza un nombre de usuario para actualización
 */
export const validateDisplayName = (name) => {
  if (!name || typeof name !== 'string') {
    return { valid: false, error: 'Name is required' };
  }

  const trimmed = name.trim();

  if (trimmed.length < 2 || trimmed.length > 100) {
    return { valid: false, error: 'Name must be 2-100 characters' };
  }

  if (!/^[a-zA-Z\s\-']+$/.test(trimmed)) {
    return { valid: false, error: 'Name can only contain letters, spaces, hyphens, and apostrophes' };
  }

  const sanitized = sanitizeString(trimmed);
  return { valid: true, sanitized };
};

/**
 * Valida que un campo no esté vacío
 */
export const validateRequired = (field, fieldName) => {
  if (!field || (typeof field === 'string' && field.trim() === '')) {
    return { valid: false, error: `${fieldName} is required` };
  }
  return { valid: true };
};
