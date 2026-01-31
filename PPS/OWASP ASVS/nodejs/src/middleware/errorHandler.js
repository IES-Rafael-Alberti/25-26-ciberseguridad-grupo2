/**
 * Middleware para capturar errores globales
 * y registrar logs de seguridad
 */

export const errorHandler = (err, req, res, next) => {
  console.error('Error:', err);

  // No exponer detalles internos
  const statusCode = err.statusCode || 500;
  const message = process.env.NODE_ENV === 'production' 
    ? 'Internal Server Error' 
    : err.message;

  res.status(statusCode).json({
    error: message,
    ...(process.env.NODE_ENV !== 'production' && { stack: err.stack })
  });
};

/**
 * Middleware para capturar IP del cliente
 */
export const captureClientIp = (req, res, next) => {
  req.clientIp = req.headers['x-forwarded-for']?.split(',')[0] || 
                 req.headers['x-real-ip'] || 
                 req.connection.remoteAddress || 
                 'UNKNOWN';
  next();
};

/**
 * Middleware para log de requests
 */
export const requestLogger = (req, res, next) => {
  const start = Date.now();

  res.on('finish', () => {
    const duration = Date.now() - start;
    console.log(
      `[${new Date().toISOString()}] ${req.method} ${req.path} - ${res.statusCode} - ${duration}ms`
    );
  });

  next();
};
