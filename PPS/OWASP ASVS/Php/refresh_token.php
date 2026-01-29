<?php
session_start();

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