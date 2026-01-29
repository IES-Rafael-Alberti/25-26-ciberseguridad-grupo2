<?php
session_start();

function sendSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=()');
    
    // CORS local/dev + prod
    $allowedOrigins = [
        'http://localhost:3000',      // React dev
        'http://127.0.0.1:3000',     // React dev
        'http://localhost',           // PHP built-in server
        #'https://tudominio.com'       // Prod (cuando subas)
    ];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!empty($input['refresh_token'])) {
        // Cargar blacklist
        $blacklist = [];
        $blacklistFile = 'data/token_blacklist.json';
        
        if (file_exists($blacklistFile)) {
            $blacklist = json_decode(file_get_contents($blacklistFile), true);
        }
        
        // Agregar token a blacklist
        $blacklist[] = $input['refresh_token'];
        
        // Guardar blacklist
        file_put_contents($blacklistFile, json_encode($blacklist, JSON_PRETTY_PRINT));
    }
    
    // Limpiar sesión
    session_destroy();
    
    http_response_code(200);
    echo json_encode(['mensaje' => 'Sesión cerrada']);
    exit;
}