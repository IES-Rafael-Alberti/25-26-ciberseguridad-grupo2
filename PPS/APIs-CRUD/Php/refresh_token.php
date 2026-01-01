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
       # 'https://tudominio.com'       // Prod (cuando subas)
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
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Endpoint para renovar tokens
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['refresh_token'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Refresh token requerido']);
        exit;
    }
    
    try {
        // Validar refresh token
        $decoded = JWT::decode($input['refresh_token'], new Key(SECRET_KEY, 'HS256'));
        
        // Verificar que es un refresh token
        if (!isset($decoded->type) || $decoded->type !== 'refresh') {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        // Verificar que el refresh token no está en blacklist
        $blacklist = [];
        if (file_exists('data/token_blacklist.json')) {
            $blacklist = json_decode(file_get_contents('data/token_blacklist.json'), true);
        }
        
        if (in_array($input['refresh_token'], $blacklist)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token revocado']);
            exit;
        }
        
        // Generar nuevo access token (corto - 15 minutos)
        $newAccessToken = [
            'iat' => time(),
            'exp' => time() + 900, // 15 minutos
            'type' => 'access',
            'data' => [
                'id' => $decoded->data->id,
                'email' => $decoded->data->email
            ]
        ];
        
        $jwt = JWT::encode($newAccessToken, SECRET_KEY, 'HS256');
        
        http_response_code(200);
        echo json_encode([
            'access_token' => $jwt,
            'expires_in' => 900
        ]);
        
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Refresh token inválido o expirado']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);