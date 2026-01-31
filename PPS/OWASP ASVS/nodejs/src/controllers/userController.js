import { Op } from 'sequelize';
import User from '../models/user.js';
import {
  validateEmail,
  validateUsername,
  validatePassword,
  calculatePasswordStrength
} from '../utils/validator.js';
import { generateTokens, blacklistToken } from '../utils/tokenManager.js';
import {
  recordFailedAttempt,
  recordSuccessfulAttempt,
  getAttemptStatus
} from '../utils/rateLimiter.js';
import {
  logLoginSuccess,
  logLoginFailed,
  logLoginLocked,
  logUserRegistered,
  logLogout,
  logAdminAccess,
  logPasswordChanged
} from '../utils/logger.js';
import fetch from 'node-fetch';

const GITHUB_TOKEN_URL = 'https://github.com/login/oauth/access_token';
const GITHUB_USER_URL = 'https://api.github.com/user';
const GITHUB_CLIENT_ID = process.env.GITHUB_CLIENT_ID;
const GITHUB_CLIENT_SECRET = process.env.GITHUB_CLIENT_SECRET;
const GITHUB_REDIRECT_URI = process.env.GITHUB_REDIRECT_URI;

export const register = async (req, res) => {
  const ip = req.ip || 'UNKNOWN';
  const userAgent = req.get('user-agent') || 'UNKNOWN';

  try {
    const { username, email, password, passwordConfirm } = req.body;

    // Validar email
    const emailValidation = validateEmail(email);
    if (!emailValidation.valid) {
      return res.status(400).json({ error: emailValidation.error });
    }

    // Validar username
    const usernameValidation = validateUsername(username);
    if (!usernameValidation.valid) {
      return res.status(400).json({ error: usernameValidation.error });
    }

    // Validar contraseña (V6.2.2 L1)
    const passwordValidation = validatePassword(password);
    if (!passwordValidation.valid) {
      return res.status(400).json({
        error: 'Password does not meet requirements',
        requirements: passwordValidation.errors,
        strength: passwordValidation.strength
      });
    }

    // Verificar confirmación de contraseña
    if (password !== passwordConfirm) {
      return res.status(400).json({ error: 'Passwords do not match' });
    }

    // Verificar si el usuario ya existe
    const existingUser = await User.findOne({
      where: {
        [Op.or]: [
          { username: usernameValidation.sanitized },
          { email: emailValidation.sanitized }
        ]
      }
    });

    if (existingUser) {
      return res.status(409).json({ error: 'Username or email already in use' });
    }

    // Crear usuario
    const user = await User.create({
      username: usernameValidation.sanitized,
      email: emailValidation.sanitized,
      password: password, // Se hashea en el hook de Sequelize
      passwordStrength: calculatePasswordStrength(password),
      lastPasswordChange: new Date()
    });

    // Control V16.3.1 L2: Logging
    logUserRegistered(user.id, user.email, ip, userAgent);

    res.status(201).json({
      message: 'User registered successfully',
      user: user.toSafeJSON()
    });
  } catch (error) {
    console.error('Registration error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
};

// ===== LOGIN LOCAL =====

export const login = async (req, res) => {
  const ip = req.ip || 'UNKNOWN';
  const userAgent = req.get('user-agent') || 'UNKNOWN';

  try {
    const { username, password } = req.body;

    // Validar entrada
    if (!username || !password) {
      return res.status(400).json({ error: 'Username and password are required' });
    }

    // Verificar rate limiting (V6.3.1 L1)
    const rateLimitStatus = getAttemptStatus(ip, username);
    if (rateLimitStatus.locked) {
      logLoginLocked(username, rateLimitStatus.secondsRemaining, ip, userAgent);
      return res.status(429).json({
        error: 'Too many failed attempts',
        message: `Try again in ${rateLimitStatus.secondsRemaining} seconds`,
        secondsRemaining: rateLimitStatus.secondsRemaining
      });
    }

    // Buscar usuario
    const user = await User.findOne({ where: { username } });

    if (!user || !user.password) {
      const attemptResult = recordFailedAttempt(ip, username);
      logLoginFailed(username, 'User not found or no password set', attemptResult.attemptsRemaining, ip, userAgent);

      return res.status(401).json({
        error: 'Invalid username or password',
        attemptsRemaining: attemptResult.attemptsRemaining
      });
    }

    // Verificar contraseña
    const passwordValid = await user.verifyPassword(password);

    if (!passwordValid) {
      const attemptResult = recordFailedAttempt(ip, user.email);
      logLoginFailed(username, 'Invalid password', attemptResult.attemptsRemaining, ip, userAgent);

      return res.status(401).json({
        error: 'Invalid username or password',
        attemptsRemaining: attemptResult.attemptsRemaining
      });
    }

    // Login exitoso
    recordSuccessfulAttempt(ip, user.email);

    // Generar tokens (V7.2.1 L1)
    const tokens = generateTokens(user.id, user.username, user.email);

    // Actualizar último login
    await user.update({
      lastLogin: new Date(),
      loginCount: user.loginCount + 1
    });

    // Control V16.3.1 L2: Logging
    logLoginSuccess(user.id, 'local', ip, userAgent);

    res.status(200).json({
      message: 'Login successful',
      user: user.toSafeJSON(),
      ...tokens
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
};

// ===== LOGOUT =====

/**
 * Control V7.4.1 L1: Logout + Token Blacklist
 * Control V16.3.1 L2: Logging
 */
export const logout = async (req, res) => {
  const ip = req.ip || 'UNKNOWN';
  const userAgent = req.get('user-agent') || 'UNKNOWN';

  try {
    if (!req.user) {
      return res.status(401).json({ error: 'Not authenticated' });
    }

    // Obtener token del header
    const authHeader = req.headers.authorization;
    if (authHeader && authHeader.startsWith('Bearer ')) {
      const token = authHeader.slice(7);
      // Añadir token a blacklist
      blacklistToken(token);
    }

    // Control V16.3.1 L2: Logging
    logLogout(req.user.id, ip, userAgent);

    res.status(200).json({ message: 'Logout successful' });
  } catch (error) {
    console.error('Logout error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
};

// ===== OBTENER USUARIOS =====

export const getUsers = async (req, res) => {
  try {
    const users = await User.findAll({
      attributes: { exclude: ['password'] }
    });
    res.json(users);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
};

// ===== OBTENER USUARIO POR ID =====

export const getUserById = async (req, res) => {
  try {
    const user = await User.findByPk(req.params.id, {
      attributes: { exclude: ['password'] }
    });

    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    res.json(user);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
};

// ===== ACTUALIZAR USUARIO =====

/**
 * Control V2.2.1 L1: Input Validation
 * Control V16.3.1 L2: Logging
 */
export const updateUser = async (req, res) => {
  const ip = req.ip || 'UNKNOWN';
  const userAgent = req.get('user-agent') || 'UNKNOWN';

  try {
    const user = await User.findByPk(req.params.id);

    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    // Solo el usuario mismo o admin puede actualizar
    if (req.user.id !== user.id && req.user.role !== 'admin') {
      return res.status(403).json({ error: 'Unauthorized' });
    }

    const updates = {};

    // Validar email si se proporciona
    if (req.body.email) {
      const emailValidation = validateEmail(req.body.email);
      if (!emailValidation.valid) {
        return res.status(400).json({ error: emailValidation.error });
      }
      updates.email = emailValidation.sanitized;
    }

    // Validar username si se proporciona
    if (req.body.username) {
      const usernameValidation = validateUsername(req.body.username);
      if (!usernameValidation.valid) {
        return res.status(400).json({ error: usernameValidation.error });
      }
      updates.username = usernameValidation.sanitized;
    }

    // Cambiar contraseña si se proporciona
    if (req.body.password) {
      const passwordValidation = validatePassword(req.body.password);
      if (!passwordValidation.valid) {
        return res.status(400).json({
          error: 'Password does not meet requirements',
          requirements: passwordValidation.errors
        });
      }
      updates.password = req.body.password;
      updates.passwordStrength = calculatePasswordStrength(req.body.password);
      logPasswordChanged(user.id, ip, userAgent);
    }

    await user.update(updates);

    // Control V16.3.1 L2: Logging
    if (Object.keys(updates).length > 0) {
      logAdminAccess(req.user.id, `/usuarios/${user.id}`, 'PUT', ip, userAgent);
    }

    res.json(user.toSafeJSON());
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
};

// ===== ELIMINAR USUARIO =====

export const deleteUser = async (req, res) => {
  const ip = req.ip || 'UNKNOWN';
  const userAgent = req.get('user-agent') || 'UNKNOWN';

  try {
    const user = await User.findByPk(req.params.id);

    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    // Solo el usuario mismo o admin puede eliminar
    if (req.user.id !== user.id && req.user.role !== 'admin') {
      return res.status(403).json({ error: 'Unauthorized' });
    }

    await user.destroy();

    logAdminAccess(req.user.id, `/usuarios/${user.id}`, 'DELETE', ip, userAgent);

    res.json({ message: 'User deleted successfully' });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
};

// ===== GITHUB OAUTH LOGIN =====

/**
 * Control V10.1.2 L1: CSRF Protection OAuth
 * State parameter para prevenir CSRF
 */
export const getGitHubAuthUrl = async (req, res) => {
  try {
    const { generateOAuthState, saveOAuthState } = await import('../utils/oauthState.js');

    const state = generateOAuthState();
    saveOAuthState(state);

    const authUrl = new URL('https://github.com/login/oauth/authorize');
    authUrl.searchParams.set('client_id', GITHUB_CLIENT_ID);
    authUrl.searchParams.set('redirect_uri', GITHUB_REDIRECT_URI);
    authUrl.searchParams.set('scope', 'user:email');
    authUrl.searchParams.set('state', state);

    res.json({ authUrl: authUrl.toString() });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
};

/**
 * Control V10.1.2 L1: OAuth callback con validación de state
 * Control V16.3.1 L2: Logging
 */
export const githubCallback = async (req, res) => {
  const ip = req.ip || 'UNKNOWN';
  const userAgent = req.get('user-agent') || 'UNKNOWN';

  try {
    const { code, state } = req.query;

    if (!code || !state) {
      return res.status(400).json({ error: 'Missing code or state parameter' });
    }

    // Validar state (V10.1.2 L1: CSRF Protection)
    const { validateOAuthState } = await import('../utils/oauthState.js');
    const stateValidation = validateOAuthState(state);

    if (!stateValidation.valid) {
      const { logOAuthStateMismatch } = await import('../utils/logger.js');
      logOAuthStateMismatch(ip, userAgent);
      return res.status(400).json({ error: 'Invalid or expired state parameter' });
    }

    // Intercambiar código por token
    const tokenResponse = await fetch(GITHUB_TOKEN_URL, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        client_id: GITHUB_CLIENT_ID,
        client_secret: GITHUB_CLIENT_SECRET,
        code,
        redirect_uri: GITHUB_REDIRECT_URI
      })
    });

    const tokenData = await tokenResponse.json();

    if (tokenData.error) {
      return res.status(400).json({ error: 'Failed to get GitHub token' });
    }

    // Obtener datos del usuario de GitHub
    const userResponse = await fetch(GITHUB_USER_URL, {
      headers: {
        Authorization: `Bearer ${tokenData.access_token}`,
        Accept: 'application/json'
      }
    });

    const githubUser = await userResponse.json();

    if (!githubUser.id) {
      return res.status(400).json({ error: 'Failed to get GitHub user info' });
    }

    // Buscar o crear usuario
    let user = await User.findOne({ where: { githubId: githubUser.id } });

    if (!user) {
      // Crear nuevo usuario
      user = await User.create({
        username: githubUser.login,
        email: githubUser.email,
        githubId: githubUser.id,
        oauthProvider: 'github'
      });

      logUserRegistered(user.id, user.email, ip, userAgent);
    }

    // Actualizar último login
    await user.update({
      lastLogin: new Date(),
      loginCount: user.loginCount + 1
    });

    // Generar tokens (V7.2.1 L1)
    const tokens = generateTokens(user.id, user.username, user.email);

    logLoginSuccess(user.id, 'github', ip, userAgent);

    // Redirigir con tokens en query params (en producción, usar POST + session)
    const redirectUrl = new URL(process.env.FRONTEND_URL || 'http://localhost:3000');
    redirectUrl.searchParams.set('accessToken', tokens.accessToken);
    redirectUrl.searchParams.set('refreshToken', tokens.refreshToken);

    res.redirect(redirectUrl.toString());
  } catch (error) {
    console.error('GitHub callback error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
};
