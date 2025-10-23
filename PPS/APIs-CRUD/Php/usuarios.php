<?php
header('Content-Type: application/json');

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

// Obtener método y ruta
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
$id = $segments[count($segments) - 1] ?? null;
$isUserIdRequest = is_numeric($id);

// Procesar según método HTTP
switch ($method) {
    // CREAR USUARIO (POST)
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y password son obligatorios']);
            exit;
        }

        $usuarios = getUsuarios();
        $nextId = count($usuarios) ? max(array_column($usuarios, 'id')) + 1 : 1;

        $nuevoUsuario = [
            'id' => $nextId,
            'nombre' => $input['nombre'] ?? '',
            'apellidos' => $input['apellidos'] ?? '',
            'email' => $input['email'],
            'password' => $input['password']
        ];

        $usuarios[] = $nuevoUsuario;
        saveUsuarios($usuarios);

        http_response_code(201);
        echo json_encode(['mensaje' => 'Usuario creado correctamente', 'usuario' => $nuevoUsuario]);
        break;

    //  OBTENER TODOS (GET /usuarios)
    //  OBTENER POR ID (GET /usuarios/{id})
    case 'GET':
        $usuarios = getUsuarios();

        if ($isUserIdRequest) {
            $usuario = null;
            foreach ($usuarios as $u) {
                if ($u['id'] == $id) {
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
            http_response_code(200);
            echo json_encode($usuarios);
        }
        break;

    //  ACTUALIZAR USUARIO (PUT /usuarios/{id})
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
                $u['password'] = $input['password'] ?? $u['password'];
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

    // ELIMINAR USUARIO (DELETE /usuarios/{id})
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
