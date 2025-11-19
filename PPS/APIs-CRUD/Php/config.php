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