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
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Función para verificar autenticación
function verificarAuth() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader)) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
    
    $token = str_replace('Bearer ', '', $authHeader);
    
    try {
        $decoded = JWT::decode($token, new Key(SECRET_KEY, 'HS256'));
        
        // Verificar que es un access token
        if (!isset($decoded->type) || $decoded->type !== 'access') {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        return $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido o expirado']);
        exit;
    }
}

// API para obtener todos los usuarios
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_users') {
    verificarAuth();
    
    if (!file_exists(DATA_FILE)) {
        echo json_encode([]);
        exit;
    }
    
    $usuarios = json_decode(file_get_contents(DATA_FILE), true);
    
    // Ocultar contraseñas
    foreach ($usuarios as &$usuario) {
        unset($usuario['password']);
    }
    
    echo json_encode($usuarios);
    exit;
}

// API para actualizar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update_user') {
    verificarAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!file_exists(DATA_FILE)) {
        http_response_code(500);
        echo json_encode(['error' => 'Archivo no encontrado']);
        exit;
    }
    
    $usuarios = json_decode(file_get_contents(DATA_FILE), true);
    $encontrado = false;
    
    foreach ($usuarios as &$usuario) {
        if ($usuario['id'] === (int)$input['id']) {
            // Validar email
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Email inválido']);
                exit;
            }
            
            // Verificar que el email no esté ya en uso
            foreach ($usuarios as $u) {
                if ($u['id'] !== (int)$input['id'] && $u['email'] === $input['email']) {
                    http_response_code(409);
                    echo json_encode(['error' => 'El email ya está en uso']);
                    exit;
                }
            }
            
            $usuario['nombre'] = htmlspecialchars($input['nombre'], ENT_QUOTES, 'UTF-8');
            $usuario['apellidos'] = htmlspecialchars($input['apellidos'], ENT_QUOTES, 'UTF-8');
            $usuario['email'] = $input['email'];
            
            // Solo actualizar contraseña si se proporciona una nueva
            if (!empty($input['password'])) {
                $usuario['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }
            
            $encontrado = true;
            break;
        }
    }
    
    if ($encontrado) {
        file_put_contents(DATA_FILE, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['mensaje' => 'Usuario actualizado']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
    exit;
}

// API para eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'delete_user') {
    verificarAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!file_exists(DATA_FILE)) {
        http_response_code(500);
        echo json_encode(['error' => 'Archivo no encontrado']);
        exit;
    }
    
    $usuarios = json_decode(file_get_contents(DATA_FILE), true);
    $nuevosUsuarios = array_filter($usuarios, function($u) use ($input) {
        return $u['id'] !== (int)$input['id'];
    });
    
    // Reindexar array
    $nuevosUsuarios = array_values($nuevosUsuarios);
    
    file_put_contents(DATA_FILE, json_encode($nuevosUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['mensaje' => 'Usuario eliminado']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN PANEL // Gestión de Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1447 50%, #2d1b69 100%);
            min-height: 100vh;
            color: #fff;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            width: 200%;
            height: 200%;
            background: 
                repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0, 255, 255, 0.03) 2px, rgba(0, 255, 255, 0.03) 4px),
                repeating-linear-gradient(90deg, transparent, transparent 2px, rgba(255, 0, 255, 0.03) 2px, rgba(255, 0, 255, 0.03) 4px);
            animation: gridMove 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 48px;
            font-weight: 900;
            background: linear-gradient(45deg, #00ffff, #ff00ff, #00ffff);
            background-size: 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientShift 3s ease infinite;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 10px;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .subtitle {
            color: rgba(0, 255, 255, 0.7);
            font-size: 16px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #00ffff, #00ff88);
            color: #0a0e27;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Orbitron', sans-serif;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 255, 255, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff0088, #ff4444);
            color: #fff;
        }

        .btn-danger:hover {
            box-shadow: 0 10px 30px rgba(255, 0, 136, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .search-box {
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(0, 255, 255, 0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-family: 'Rajdhani', sans-serif;
        }

        .search-box input:focus {
            outline: none;
            border-color: #00ffff;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .user-card {
            background: rgba(10, 14, 39, 0.85);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(0, 255, 255, 0.3);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .user-card:hover::before {
            left: 100%;
        }

        .user-card:hover {
            border-color: #00ffff;
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);
            transform: translateY(-5px);
        }

        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .user-id {
            background: linear-gradient(135deg, #00ffff, #00ff88);
            color: #0a0e27;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .user-name {
            font-size: 24px;
            font-weight: 700;
            color: #00ffff;
            margin-bottom: 5px;
            font-family: 'Orbitron', sans-serif;
        }

        .user-email {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .user-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .user-meta-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            background: rgba(0, 255, 255, 0.05);
            border-radius: 5px;
            border-left: 3px solid rgba(0, 255, 255, 0.5);
        }

        .user-meta-label {
            color: rgba(0, 255, 255, 0.8);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .user-meta-value {
            color: rgba(255, 255, 255, 0.9);
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        .user-actions button {
            flex: 1;
            padding: 10px;
            font-size: 12px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: rgba(10, 14, 39, 0.95);
            border: 2px solid rgba(0, 255, 255, 0.5);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 0 60px rgba(0, 255, 255, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: scale(0.9) translateY(-20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #00ffff;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(0, 255, 255, 0.9);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(0, 255, 255, 0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-family: 'Rajdhani', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00ffff;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .modal-actions button {
            flex: 1;
        }

        .loading {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #00ffff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .users-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ADMIN PANEL</h1>
            <div class="subtitle">// GESTIÓN DE USUARIOS //</div>
        </div>

        <div class="actions">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar por nombre o email...">
            </div>
            <button class="btn" onclick="location.reload()">
                ↻ RECARGAR
            </button>
            <button class="btn btn-danger" onclick="logout()">
                ⏻ CERRAR SESIÓN
            </button>
        </div>

        <div id="usersContainer" class="users-grid">
            <div class="loading">Cargando usuarios...</div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">EDITAR USUARIO</div>
            <form id="editForm">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label>NOMBRE</label>
                    <input type="text" id="edit-nombre" required>
                </div>
                <div class="form-group">
                    <label>APELLIDOS</label>
                    <input type="text" id="edit-apellidos" required>
                </div>
                <div class="form-group">
                    <label>EMAIL</label>
                    <input type="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label>NUEVA CONTRASEÑA (dejar vacío para no cambiar)</label>
                    <input type="password" id="edit-password" placeholder="••••••••">
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn">GUARDAR</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">CANCELAR</button>
                </div>
            </form>
        </div>
    </div>

    <script src="auth.js"></script>
    <script>
        let allUsers = [];

        // Cargar usuarios al iniciar
        window.addEventListener('DOMContentLoaded', loadUsers);

        async function loadUsers() {
            try {
                const response = await auth.fetch('admin_usuarios.php?action=get_users');
                
                if (!response.ok) {
                    throw new Error('No autorizado');
                }

                allUsers = await response.json();
                displayUsers(allUsers);

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('usersContainer').innerHTML = 
                    '<div class="empty-state">' +
                    '<div class="empty-state-icon">⚠️</div>' +
                    '<h3>Error al cargar usuarios</h3>' +
                    '<p>' + error.message + '</p>' +
                    '</div>';
            }
        }

        function displayUsers(users) {
            const container = document.getElementById('usersContainer');

            if (users.length === 0) {
                container.innerHTML = 
                    '<div class="empty-state">' +
                    '<div class="empty-state-icon">👥</div>' +
                    '<h3>No hay usuarios registrados</h3>' +
                    '</div>';
                return;
            }

            container.innerHTML = users.map(user => `
                <div class="user-card">
                    <div class="user-header">
                        <div>
                            <div class="user-name">${escapeHtml(user.nombre)} ${escapeHtml(user.apellidos)}</div>
                            <div class="user-email">${escapeHtml(user.email)}</div>
                        </div>
                        <div class="user-id">ID: ${user.id}</div>
                    </div>
                    
                    <div class="user-meta">
                        ${user.github_id ? `
                            <div class="user-meta-item">
                                <span class="user-meta-label">GitHub ID</span>
                                <span class="user-meta-value">${user.github_id}</span>
                            </div>
                        ` : ''}
                        ${user.login ? `
                            <div class="user-meta-item">
                                <span class="user-meta-label">Login</span>
                                <span class="user-meta-value">@${escapeHtml(user.login)}</span>
                            </div>
                        ` : ''}
                        ${user.oauth_provider ? `
                            <div class="user-meta-item">
                                <span class="user-meta-label">Proveedor</span>
                                <span class="user-meta-value">${escapeHtml(user.oauth_provider).toUpperCase()}</span>
                            </div>
                        ` : ''}
                        ${user.created_at ? `
                            <div class="user-meta-item">
                                <span class="user-meta-label">Creado</span>
                                <span class="user-meta-value">${formatDate(user.created_at)}</span>
                            </div>
                        ` : ''}
                        ${user.last_login ? `
                            <div class="user-meta-item">
                                <span class="user-meta-label">Último acceso</span>
                                <span class="user-meta-value">${formatDate(user.last_login)}</span>
                            </div>
                        ` : ''}
                    </div>

                    <div class="user-actions">
                        <button class="btn" onclick='editUser(${JSON.stringify(user)})'>
                            ✎ EDITAR
                        </button>
                        <button class="btn btn-danger" onclick="deleteUser(${user.id})">
                            ✕ ELIMINAR
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function editUser(user) {
            document.getElementById('edit-id').value = user.id;
            document.getElementById('edit-nombre').value = user.nombre;
            document.getElementById('edit-apellidos').value = user.apellidos;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-password').value = '';
            
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const userData = {
                id: document.getElementById('edit-id').value,
                nombre: document.getElementById('edit-nombre').value,
                apellidos: document.getElementById('edit-apellidos').value,
                email: document.getElementById('edit-email').value,
                password: document.getElementById('edit-password').value
            };

            try {
                const response = await auth.fetch('admin_usuarios.php?action=update_user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                });

                const data = await response.json();

                if (response.ok) {
                    alert('✓ Usuario actualizado correctamente');
                    closeModal();
                    loadUsers();
                } else {
                    alert('✗ Error: ' + data.error);
                }
            } catch (error) {
                alert('✗ Error de conexión');
            }
        });

        async function deleteUser(id) {
            if (!confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')) {
                return;
            }

            try {
                const response = await auth.fetch('admin_usuarios.php?action=delete_user', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                const data = await response.json();

                if (response.ok) {
                    alert('✓ Usuario eliminado correctamente');
                    loadUsers();
                } else {
                    alert('✗ Error: ' + data.error);
                }
            } catch (error) {
                alert('✗ Error de conexión');
            }
        }

        function logout() {
            if (confirm('¿Estás seguro de cerrar sesión?')) {
                auth.logout();
            }
        }

        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filtered = allUsers.filter(user => 
                user.nombre.toLowerCase().includes(searchTerm) ||
                user.apellidos.toLowerCase().includes(searchTerm) ||
                user.email.toLowerCase().includes(searchTerm)
            );
            displayUsers(filtered);
        });

        // Utilidades
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>