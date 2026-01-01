export const sendSecurityHeaders = (req, res, next) => {
  // Previene MIME type sniffing
  res.setHeader('X-Content-Type-Options', 'nosniff');

  // Previene clickjacking
  res.setHeader('X-Frame-Options', 'DENY');

  // Protección XSS en navegadores antiguos
  res.setHeader('X-XSS-Protection', '1; mode=block');

  // Control de Referrer-Policy
  res.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

  // Permissions-Policy (formerly Feature-Policy)
  res.setHeader('Permissions-Policy', 'geolocation=(), microphone=()');

  // Strict-Transport-Security (HSTS)
  res.setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

  // Content-Security-Policy
  res.setHeader('Content-Security-Policy', "default-src 'self'; script-src 'self'");

  next();
};

/**
 * Configuración CORS con restricción local-only
 * Solo permite localhost:3000 y 127.0.0.1:3000
 */
export const corsOptions = {
  origin: function (origin, callback) {
    const allowedOrigins = [
      'http://localhost:3000',
      'http://127.0.0.1:3000',
      'http://localhost:3001',
      'http://127.0.0.1:3001'
    ];

    if (!origin || allowedOrigins.includes(origin)) {
      callback(null, true);
    } else {
      callback(new Error('CORS not allowed from this origin: ' + origin));
    }
  },
  credentials: true,
  optionsSuccessStatus: 200,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization']
};
