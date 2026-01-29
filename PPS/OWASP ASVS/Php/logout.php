<?php
session_start();

require_once 'config.php';
sendSecurityHeaders(); 

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
    
    // <-- AGREGAR EXACTAMENTE AQUÍ (LÍNEA ~17):
    securityLog('logout', $_SESSION['user']['id'] ?? null, ['has_refresh' => !empty($input['refresh_token'] ?? '')]);
    
    http_response_code(200);
    echo json_encode(['mensaje' => 'Sesión cerrada']);
    exit;
}