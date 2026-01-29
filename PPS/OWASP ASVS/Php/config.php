<?php
// config.php - Cargar variables de entorno

// Función para cargar .env
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception('.env file not found');
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear línea KEY=VALUE
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Quitar comillas si las tiene
        $value = trim($value, '"\'');

        // Establecer variable de entorno si no existe
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Cargar .env
loadEnv(__DIR__ . '/.env');

// Definir constantes desde variables de entorno
define('SECRET_KEY', getenv('JWT_SECRET_KEY'));
define('GITHUB_CLIENT_ID', getenv('GITHUB_CLIENT_ID'));
define('GITHUB_CLIENT_SECRET', getenv('GITHUB_CLIENT_SECRET'));
define('APP_ENV', getenv('APP_ENV'));
define('APP_HOST', getenv('APP_HOST'));
define('DATA_FILE', 'data/usuarios.json');

// Configurar según entorno
$isProduction = APP_ENV === 'production';
$protocol = $isProduction ? 'https://' : 'http://';
define('BASE_URL', $protocol . APP_HOST);
define('REDIRECT_URI', BASE_URL . '/oauth_callback.php');

// Logging completo ASVS V16
function securityLog($event, $userId = null, $details = []) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $logEntry = [
        'time' => date('Y-m-d H:i:s'),
        'ip' => $ip,
        'user_id' => $userId,
        'event' => $event,
        'details' => $details
    ];
    
    $logFile = 'logs/security_' . date('Y-m-d') . '.log';
    if (!is_dir('logs')) mkdir('logs', 0755, true);
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    
    error_log("SEC[$event] user:$userId ip:$ip");
}

// ===== AGREGAR ESTAS 3 FUNCIONES AL FINAL =====

// 1. Headers ASVS V3.4 L1 (Local-only)
function sendSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=()');
    
    // CORS solo LOCAL para desarrollo/ejercicio
    $allowedLocal = ['http://localhost:3000', 'http://127.0.0.1:3000', 'http://localhost'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowedLocal)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }
    
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// 2. Allowlist JWT algorithms ASVS V9.1.2 L1
function validateJWTAlgorithm($alg) {
    $allowedAlgs = ['HS256']; // Documentado en security.md
    return in_array($alg, $allowedAlgs);
}

// 3. Validación inputs mejorada V2.2 L1
function sanitizeInput($input) {
    if (is_string($input)) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    return $input;
}
?>