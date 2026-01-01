<?php
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Variable para sesión OAuth (usada para validar sesión OAuth)
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


function validateAuth() {
    $headers = getallheaders();

    // Primero, chequea si hay sesión OAuth activa
    if (isset($_SESSION['user'])) {
        // Usuario autenticado vía OAuth
        return $_SESSION['user'];
    }

    // Si no sesión OAuth, validar JWT propio
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        error_log('JWT Error: ' . $e->getMessage());
        echo json_encode(['error' => 'Token inválido o no autorizado']);
        exit;
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key(SECRET_KEY, 'HS256'));
        return $decoded->data; // Retorna los datos del usuario propio
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido o expirado: ' . $e->getMessage()]);
        exit;
    }
}