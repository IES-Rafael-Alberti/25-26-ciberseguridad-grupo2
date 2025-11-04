<?php
header('Content-Type: application/json');

define('DATA_FILE', __DIR__ . '/data/usuarios.json');

// Cargar usuarios
if (!file_exists(DATA_FILE)) {
    http_response_code(500);
    echo json_encode(['error' => 'No existe el archivo de usuarios']);
    exit;
}

$usuarios = json_decode(file_get_contents(DATA_FILE), true);
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['email']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan email o password']);
    exit;
}

$email = $input['email'];
$password = $input['password'];

// Buscar usuario
$found = null;
foreach ($usuarios as $u) {
    if ($u['email'] === $email && $u['password'] === $password) {
        $found = $u;
        break;
    }
}

if ($found) {
    // LOGIN CORRECTO
    http_response_code(200);
    echo json_encode([
        'mensaje' => 'Login correcto',
        'usuario' => [
            'id' => $found['id'],
            'nombre' => $found['nombre'],
            'apellidos' => $found['apellidos'],
            'email' => $found['email']
        ]
    ]);
} else {
    // LOGIN INCORRECTO
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales incorrectas']);
}
