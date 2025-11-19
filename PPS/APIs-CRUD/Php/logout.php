<?php
session_start();

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