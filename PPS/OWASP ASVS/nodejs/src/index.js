import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import sequelize from './db/db.js';
import usersRouter from './routes/users.js';
import { sendSecurityHeaders, corsOptions } from './config/securityHeaders.js';
import { errorHandler, captureClientIp, requestLogger } from './middleware/errorHandler.js';

const app = express();
const PORT = process.env.PORT || 3000;

// ===== MIDDLEWARE DE SEGURIDAD =====

// Control V1: Capturar IP del cliente
app.use(captureClientIp);

// Control V1: Headers de seguridad (V3.4 L1)
app.use(sendSecurityHeaders);

// Control V1: CORS con restricción local-only
app.use(cors(corsOptions));

// Parser
app.use(express.json({ limit: '10kb' })); // Limitar tamaño del body
app.use(express.urlencoded({ limit: '10kb', extended: true }));

// Request logging
app.use(requestLogger);

// ===== RUTAS =====

app.get('/', (req, res) => {
  res.json({
    message: 'OWASP ASVS - Node.js Security API',
    version: '1.0.0',
    controls: [
      'V3.4 L1 - Security Headers',
      'V6.2.2 L1 - Password Validation',
      'V6.3.1 L1 - Rate Limiting',
      'V7.2.1 L1 - JWT with Expiration',
      'V7.4.1 L1 - Logout & Token Blacklist',
      'V16.3.1 L2 - Security Logging',
      'V2.2.1 L1 - Input Validation',
      'V8.3.1 L1 - JWT Authentication',
      'V10.1.2 L1 - CSRF Protection OAuth'
    ],
    endpoints: {
      auth: {
        'POST /usuarios/register': 'Register new user',
        'POST /usuarios/login': 'Login with credentials',
        'POST /usuarios/logout': 'Logout (requires JWT)',
        'GET /usuarios/auth/github': 'Get GitHub OAuth URL',
        'GET /usuarios/auth/github/callback': 'GitHub OAuth callback'
      },
      users: {
        'GET /usuarios': 'Get all users (requires JWT)',
        'GET /usuarios/:id': 'Get user by ID (requires JWT)',
        'PUT /usuarios/:id': 'Update user (requires JWT)',
        'DELETE /usuarios/:id': 'Delete user (requires JWT)'
      }
    }
  });
});

// Rutas de usuarios
app.use('/usuarios', usersRouter);

// ===== ERROR HANDLING =====

// 404
app.use((req, res) => {
  res.status(404).json({ error: 'Route not found' });
});

// Error handler global
app.use(errorHandler);

// ===== SINCRONIZACIÓN DE BASE DE DATOS E INICIO DEL SERVIDOR =====

const startServer = async () => {
  try {
    // Sincronizar base de datos
    await sequelize.sync({ alter: process.env.NODE_ENV === 'development' });
    console.log('✓ Database synchronized');

    // Iniciar servidor
    app.listen(PORT, () => {
      console.log(`✓ Server running at http://localhost:${PORT}`);
      console.log(`✓ Environment: ${process.env.NODE_ENV || 'development'}`);
      console.log(`✓ CORS origins: ${process.env.CORS_ALLOWED_ORIGINS || 'http://localhost:3000'}`);
    });
  } catch (error) {
    console.error('✗ Failed to start server:', error);
    process.exit(1);
  }
};

startServer();

export default app;
