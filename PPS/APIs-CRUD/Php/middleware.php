<?php
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Variable para sesión OAuth (usada para validar sesión OAuth)
session_start();

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