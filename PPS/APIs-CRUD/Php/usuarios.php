<?php
header('Content-Type: application/json');

require_once 'middleware.php';

define('DATA_FILE', __DIR__ . '/data/usuarios.json');

// Crear archivo si no existe
if (!file_exists(DATA_FILE)) {
    file_put_contents(DATA_FILE, json_encode([]));
}

function getUsuarios() {
    return json_decode(file_get_contents(DATA_FILE), true);
}

function saveUsuarios($usuarios) {
    file_put_contents(DATA_FILE, json_encode($usuarios, JSON_PRETTY_PRINT));
}

// Obtener método
$method = $_SERVER['REQUEST_METHOD'];

// ⚠️ SOLO VALIDAR TOKEN SI NO ES POST (crear usuario)
if ($method !== 'POST') {
    $usuarioAutenticado = validateAuth(); // Cambiado para validar JWT o sesión OAuth
}

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
$id = $segments[count($segments) - 1] ?? null;
$isUserIdRequest = is_numeric($id);

// Procesar según método HTTP
switch ($method) {
    // CREAR USUARIO (POST) - SIN TOKEN
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y password son obligatorios']);
            exit;
        }

        $usuarios = getUsuarios();
        
        // Verificar si el email ya existe
        foreach ($usuarios as $u) {
            if ($u['email'] === $input['email']) {
                http_response_code(400);
                echo json_encode(['error' => 'El email ya está registrado']);
                exit;
            }
        }
        
        $nextId = count($usuarios) ? max(array_column($usuarios, 'id')) + 1 : 1;

        $nuevoUsuario = [
            'id' => $nextId,
            'nombre' => $input['nombre'] ?? '',
            'apellidos' => $input['apellidos'] ?? '',
            'email' => $input['email'],
            'password' => password_hash($input['password'], PASSWORD_DEFAULT)
        ];

        $usuarios[] = $nuevoUsuario;
        saveUsuarios($usuarios);

        http_response_code(201);
        // No devolver el password hasheado
        unset($nuevoUsuario['password']);
        echo json_encode(['mensaje' => 'Usuario creado correctamente', 'usuario' => $nuevoUsuario]);
        break;

    // Resto del código igual...
    case 'GET':
        $usuarios = getUsuarios();

        if ($isUserIdRequest) {
            $usuario = null;
            foreach ($usuarios as $u) {
                if ($u['id'] == $id) {
                    unset($u['password']); // No mostrar password
                    $usuario = $u;
                    break;
                }
            }

            if ($usuario) {
                http_response_code(200);
                echo json_encode($usuario);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
        } else {
            // No mostrar passwords en el listado
            $usuariosSinPassword = array_map(function($u) {
                unset($u['password']);
                return $u;
            }, $usuarios);
            http_response_code(200);
            echo json_encode($usuariosSinPassword);
        }
        break;

    case 'PUT':
        if (!$isUserIdRequest) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el ID del usuario']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $usuarios = getUsuarios();
        $actualizado = false;

        foreach ($usuarios as &$u) {
            if ($u['id'] == $id) {
                $u['nombre'] = $input['nombre'] ?? $u['nombre'];
                $u['apellidos'] = $input['apellidos'] ?? $u['apellidos'];
                $u['email'] = $input['email'] ?? $u['email'];
                if (isset($input['password'])) {
                    $u['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
                }
                $actualizado = true;
                break;
            }
        }

        if ($actualizado) {
            saveUsuarios($usuarios);
            http_response_code(200);
            echo json_encode(['mensaje' => 'Usuario actualizado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
        }
        break;

    case 'DELETE':
        if (!$isUserIdRequest) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el ID del usuario']);
            exit;
        }

        $usuarios = getUsuarios();
        $nuevoListado = array_filter($usuarios, fn($u) => $u['id'] != $id);

        if (count($nuevoListado) === count($usuarios)) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
        } else {
            saveUsuarios(array_values($nuevoListado));
            http_response_code(200);
            echo json_encode(['mensaje' => 'Usuario eliminado correctamente']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}