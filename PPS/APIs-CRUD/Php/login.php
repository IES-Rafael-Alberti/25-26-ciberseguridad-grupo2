<?php
// IMPORTANTE: session_start() UNA SOLA VEZ al inicio, antes de cualquier salida
session_start();

require_once 'config.php';
require_once 'vendor/autoload.php';

use Firebase\JWT\JWT;

// Función de login local con JWT y rate limiting

function login_local($email, $password) {
    // Rate limiting SOLO para login local
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $attemptKey = 'login_attempts_' . md5($ip . $email);
    $lockKey = 'login_lock_' . md5($ip . $email);
    
    // Verificar si está bloqueado
    if (isset($_SESSION[$lockKey]) && $_SESSION[$lockKey] > time()) {
        $remainingTime = $_SESSION[$lockKey] - time();
        $minutes = ceil($remainingTime / 60);
        http_response_code(429);
        echo json_encode([
            'error' => 'Demasiados intentos fallidos',
            'bloqueado' => true,
            'tiempo_restante' => $minutes . ' minuto' . ($minutes > 1 ? 's' : '')
        ]);
        exit;
    }
    
    if (!file_exists(DATA_FILE)) {
        http_response_code(500);
        echo json_encode(['error' => 'No existe el archivo de usuarios']);
        exit;
    }

    $usuarios = json_decode(file_get_contents(DATA_FILE), true);
    $found = null;

    foreach ($usuarios as $u) {
        if ($u['email'] === $email && password_verify($password, $u['password'])) {
            $found = $u;
            break;
        }
    }

if ($found) {
    // LOGIN EXITOSO - Resetear contadores
    unset($_SESSION[$attemptKey]);
    unset($_SESSION[$lockKey]);
    
    // Generar Access Token (corto - 15 minutos)
    $accessPayload = [
        'iat' => time(),
        'exp' => time() + 900, // 15 minutos
        'type' => 'access',
        'data' => [
            'id' => $found['id'],
            'email' => $found['email']
        ]
    ];
    
    // Generar Refresh Token (largo - 7 días)
    $refreshPayload = [
        'iat' => time(),
        'exp' => time() + 604800, // 7 días (60*60*24*7)
        'type' => 'refresh',
        'data' => [
            'id' => $found['id'],
            'email' => $found['email']
        ]
    ];

    $accessToken = JWT::encode($accessPayload, SECRET_KEY, 'HS256');
    $refreshToken = JWT::encode($refreshPayload, SECRET_KEY, 'HS256');
    
    http_response_code(200);
    echo json_encode([
        'mensaje' => 'Login correcto (local JWT)',
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'expires_in' => 900, // 15 minutos
        'usuario' => [
            'id' => $found['id'],
            'nombre' => $found['nombre'],
            'apellidos' => $found['apellidos'],
            'email' => $found['email']
        ]
    ]);
}
     else {
        // LOGIN FALLIDO - Incrementar intentos
        $_SESSION[$attemptKey] = ($_SESSION[$attemptKey] ?? 0) + 1;
        
        if ($_SESSION[$attemptKey] >= 5) {
            $_SESSION[$lockKey] = time() + 300; // Bloquear por 5 minutos
            http_response_code(429);
            echo json_encode([
                'error' => 'Demasiados intentos fallidos',
                'bloqueado' => true,
                'tiempo_restante' => '5 minutos'
            ]);
        } else {
            $intentosRestantes = 5 - $_SESSION[$attemptKey];
            http_response_code(401);
            echo json_encode([
                'error' => 'Credenciales incorrectas',
                'intentos_restantes' => $intentosRestantes
            ]);
        }
    }
    exit;
}

// Función para registrar usuario
function register_user($data) {
    // Validaciones
    if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Validar email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email inválido']);
        exit;
    }

    // Validar contraseña
    if (strlen($data['password']) < 8) {
        http_response_code(400);
        echo json_encode(['error' => 'La contraseña debe tener al menos 8 caracteres']);
        exit;
    }

    if (!preg_match('/[A-Z]/', $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'La contraseña debe contener al menos una mayúscula']);
        exit;
    }

    if (!preg_match('/[a-z]/', $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'La contraseña debe contener al menos una minúscula']);
        exit;
    }

    if (!preg_match('/[0-9]/', $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'La contraseña debe contener al menos un número']);
        exit;
    }

    // Leer usuarios existentes
    $usuarios = [];
    if (file_exists(DATA_FILE)) {
        $usuarios = json_decode(file_get_contents(DATA_FILE), true);
    }

    // Verificar si el email ya existe
    foreach ($usuarios as $u) {
        if ($u['email'] === $data['email']) {
            http_response_code(409);
            echo json_encode(['error' => 'El email ya está registrado']);
            exit;
        }
    }

    // Crear nuevo usuario
    $nuevoId = count($usuarios) > 0 ? max(array_column($usuarios, 'id')) + 1 : 1;
    
    $nuevoUsuario = [
        'id' => $nuevoId,
        'nombre' => htmlspecialchars($data['nombre']),
        'apellidos' => htmlspecialchars($data['apellidos']),
        'email' => $data['email'],
        'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        'created_at' => date('Y-m-d H:i:s')
    ];

    $usuarios[] = $nuevoUsuario;

    // Guardar en archivo JSON
    if (!file_put_contents(DATA_FILE, json_encode($usuarios, JSON_PRETTY_PRINT))) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el usuario']);
        exit;
    }

    http_response_code(201);
    echo json_encode([
        'mensaje' => 'Usuario registrado exitosamente',
        'usuario' => [
            'id' => $nuevoId,
            'nombre' => $nuevoUsuario['nombre'],
            'apellidos' => $nuevoUsuario['apellidos'],
            'email' => $nuevoUsuario['email']
        ]
    ]);
    exit;
}

// Función para iniciar login OAuth
function login_oauth() {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
    $state = $_SESSION['oauth_state'];
    
    $client_id = GITHUB_CLIENT_ID;
    $redirect_uri = REDIRECT_URI;
    
    $authUrl = "https://github.com/login/oauth/authorize" .
               "?client_id=" . urlencode($client_id) .
               "&redirect_uri=" . urlencode($redirect_uri) .
               "&scope=user" .
               "&state=" . urlencode($state);
    
    header('Location: ' . $authUrl);
    exit();
}

// ===== PROCESAR PETICIONES ANTES DE CUALQUIER HTML =====

// Verificar si es una petición GET para iniciar OAuth
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'oauth') {
    login_oauth();
}

// Si es POST, procesar login o registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Determinar si es login o registro
    if (isset($input['action']) && $input['action'] === 'register') {
        register_user($input);
    } else {
        // Login tradicional
        if (empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan email o password']);
            exit;
        }
        login_local($input['email'], $input['password']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CYBER AUTH // Sistema de Acceso</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        /* Animación de fondo cyberpunk */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0, 255, 255, 0.03) 2px, rgba(0, 255, 255, 0.03) 4px),
                repeating-linear-gradient(90deg, transparent, transparent 2px, rgba(255, 0, 255, 0.03) 2px, rgba(255, 0, 255, 0.03) 4px);
            animation: gridMove 20s linear infinite;
            pointer-events: none;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Efectos de luz neon flotantes */
        .neon-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.3;
            animation: float 6s ease-in-out infinite;
        }

        .neon-circle:nth-child(1) {
            width: 300px;
            height: 300px;
            background: #00ffff;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .neon-circle:nth-child(2) {
            width: 200px;
            height: 200px;
            background: #ff00ff;
            bottom: 15%;
            right: 15%;
            animation-delay: 2s;
        }

        .neon-circle:nth-child(3) {
            width: 250px;
            height: 250px;
            background: #00ff88;
            top: 50%;
            right: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.1); }
        }

        .container {
            background: rgba(10, 14, 39, 0.85);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(0, 255, 255, 0.3);
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 450px;
            box-shadow: 
                0 0 60px rgba(0, 255, 255, 0.3),
                inset 0 0 60px rgba(0, 255, 255, 0.05);
            position: relative;
            z-index: 10;
            animation: containerGlow 3s ease-in-out infinite alternate;
        }

        @keyframes containerGlow {
            0% { box-shadow: 0 0 60px rgba(0, 255, 255, 0.3), inset 0 0 60px rgba(0, 255, 255, 0.05); }
            100% { box-shadow: 0 0 80px rgba(255, 0, 255, 0.4), inset 0 0 80px rgba(255, 0, 255, 0.08); }
        }

        h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 32px;
            font-weight: 900;
            text-align: center;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #00ffff, #ff00ff, #00ffff);
            background-size: 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientShift 3s ease infinite;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .subtitle {
            text-align: center;
            color: rgba(0, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .tab {
            flex: 1;
            padding: 12px;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid rgba(0, 255, 255, 0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .tab:hover {
            background: rgba(0, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .tab.active {
            background: linear-gradient(135deg, #00ffff, #00ff88);
            color: #0a0e27;
            border-color: #00ffff;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: rgba(0, 255, 255, 0.9);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(0, 255, 255, 0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s;
            font-family: 'Rajdhani', sans-serif;
        }

        input:focus {
            outline: none;
            border-color: #00ffff;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
            background: rgba(0, 0, 0, 0.7);
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00ffff, #00ff88);
            color: #0a0e27;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            font-family: 'Orbitron', sans-serif;
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        button:hover::before {
            left: 100%;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 255, 255, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .oauth-btn {
            background: linear-gradient(135deg, #24292e, #1a1f23);
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .oauth-btn:hover {
            background: linear-gradient(135deg, #1a1f23, #0d1117);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
        }

        .separator {
            text-align: center;
            margin: 25px 0;
            color: rgba(255, 255, 255, 0.5);
            position: relative;
            font-size: 13px;
            letter-spacing: 1px;
        }

        .separator::before,
        .separator::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(0, 255, 255, 0.3), transparent);
        }

        .separator::before {
            left: 0;
        }

        .separator::after {
            right: 0;
        }

        #resultado {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success {
            background: rgba(0, 255, 136, 0.2);
            border: 1px solid rgba(0, 255, 136, 0.5);
            color: #00ff88;
        }

        .error {
            background: rgba(255, 0, 136, 0.2);
            border: 1px solid rgba(255, 0, 136, 0.5);
            color: #ff0088;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 255, 255, 0.3);
            border-top-color: #00ffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .error strong {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .error span {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="neon-circle"></div>
    <div class="neon-circle"></div>
    <div class="neon-circle"></div>

    <div class="container">
        <h2>CYBER AUTH</h2>
        <div class="subtitle">// Sistema de Autenticación Segura //</div>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('login')">ACCESO</div>
            <div class="tab" onclick="switchTab('register')">REGISTRO</div>
        </div>

        <!-- Formulario de LOGIN -->
        <div id="loginForm" class="form-container active">
            <form id="loginFormElement">
                <div class="form-group">
                    <label for="login-email">EMAIL</label>
                    <input type="email" id="login-email" name="email" placeholder="usuario@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="login-password">CONTRASEÑA</label>
                    <input type="password" id="login-password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit">INICIAR SESIÓN</button>
            </form>

            <div class="separator">O continúa con</div>

            <a href="login.php?action=oauth" class="oauth-btn">
                <svg width="20" height="20" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
                </svg>
                GITHUB
            </a>
        </div>

        <!-- Formulario de REGISTRO -->
        <div id="registerForm" class="form-container">
            <form id="registerFormElement">
                <div class="form-group">
                    <label for="reg-nombre">NOMBRE</label>
                    <input type="text" id="reg-nombre" name="nombre" placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label for="reg-apellidos">APELLIDOS</label>
                    <input type="text" id="reg-apellidos" name="apellidos" placeholder="Tus apellidos" required>
                </div>
                <div class="form-group">
                    <label for="reg-email">EMAIL</label>
                    <input type="email" id="reg-email" name="email" placeholder="usuario@dominio.com" required>
                </div>
                <div class="form-group">
                    <label for="reg-password">CONTRASEÑA</label>
                    <input type="password" id="reg-password" name="password" placeholder="Mín. 8 caracteres" required>
                    <div class="password-strength" id="password-strength"></div>
                </div>
                <button type="submit">CREAR CUENTA</button>
            </form>
        </div>

        <div id="resultado"></div>
    </div>

    <script>
        // Cambiar entre tabs
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.form-container').forEach(f => f.classList.remove('active'));
            
            if (tab === 'login') {
                document.querySelector('.tab:first-child').classList.add('active');
                document.getElementById('loginForm').classList.add('active');
            } else {
                document.querySelector('.tab:last-child').classList.add('active');
                document.getElementById('registerForm').classList.add('active');
            }
            
            document.getElementById('resultado').innerHTML = '';
        }

        // Manejar login
document.getElementById('loginFormElement').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    const resultado = document.getElementById('resultado');
    
    resultado.innerHTML = '<div class="loading"></div>';
    
    try {
        const response = await fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
if (response.ok) {
    console.log('Respuesta del servidor:', data); // DEBUG
    
    // Guardar tokens (compatible con ambos formatos)
    if (data.access_token) {
        // Formato nuevo (con refresh tokens)
        localStorage.setItem('access_token', data.access_token);
        localStorage.setItem('refresh_token', data.refresh_token);
        console.log('✅ Tokens guardados (nuevo formato)');
    } else if (data.token) {
        // Formato antiguo (sin refresh tokens) - guardar como access_token
        localStorage.setItem('access_token', data.token);
        console.log('✅ Token guardado (formato antiguo)');
    } else {
        console.error('❌ ERROR: No se encontró ningún token en la respuesta');
        console.log('Data recibida:', data);
    }
    
    resultado.className = 'success';
    resultado.innerHTML = 
        '<strong>✓ ACCESO CONCEDIDO</strong><br>' +
        'Bienvenido ' + data.usuario.nombre + '<br>' +
        'Redirigiendo al sistema...';
    
    setTimeout(function() {
        window.location.href = 'admin_usuarios.php';
    }, 1500);
}
    else {
            resultado.className = 'error';
            
            // Si está bloqueado, mostrar mensaje especial
            if (data.bloqueado) {
                resultado.innerHTML = 
                    '<strong>🔒 CUENTA BLOQUEADA TEMPORALMENTE</strong><br>' +
                    'Demasiados intentos fallidos<br>' +
                    'Espera <strong>' + data.tiempo_restante + '</strong> para intentar de nuevo';
            } else if (data.intentos_restantes !== undefined) {
                resultado.innerHTML = 
                    '<strong>✗ CREDENCIALES INCORRECTAS</strong><br>' +
                    data.error + '<br>' +
                    '<span style="color: #ff8800;">Intentos restantes: ' + data.intentos_restantes + '</span>';
            } else {
                resultado.innerHTML = '<strong>✗ ERROR</strong><br>' + data.error;
            }
        }
    } catch (error) {
        resultado.className = 'error';
        resultado.innerHTML = '<strong>✗ ERROR DE CONEXIÓN</strong><br>Verifica tu conexión';
    }
});

        // Manejar registro
        document.getElementById('registerFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const nombre = document.getElementById('reg-nombre').value;
            const apellidos = document.getElementById('reg-apellidos').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;
            const resultado = document.getElementById('resultado');
            
            resultado.innerHTML = '<div class="loading"></div>';
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        action: 'register',
                        nombre, 
                        apellidos, 
                        email, 
                        password 
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    resultado.className = 'success';
                    resultado.innerHTML = 
                        '<strong>✓ REGISTRO EXITOSO</strong><br>' +
                        'Cuenta creada para ' + data.usuario.email + '<br>' +
                        'Ya puedes iniciar sesión';
                    
                    setTimeout(function() {
                        switchTab('login');
                        document.getElementById('login-email').value = email;
                    }, 2000);
                    
                } else {
                    resultado.className = 'error';
                    resultado.innerHTML = '<strong>✗ ERROR</strong><br>' + data.error;
                }
            } catch (error) {
                resultado.className = 'error';
                resultado.innerHTML = '<strong>✗ ERROR DE CONEXIÓN</strong><br>Verifica tu conexión';
            }
        });

        // Validación de contraseña en tiempo real
        document.getElementById('reg-password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthDiv = document.getElementById('password-strength');
            
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength++;
            else feedback.push('Mín. 8 caracteres');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('Una mayúscula');
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('Una minúscula');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('Un número');
            
            const colors = ['#ff0088', '#ff8800', '#ffff00', '#00ff88'];
            const labels = ['Débil', 'Media', 'Buena', 'Fuerte'];
            
            if (password.length > 0) {
                strengthDiv.innerHTML = 
                    '<div style="color: ' + colors[strength-1] + '">' + labels[strength-1] + '</div>' +
                    '<div class="strength-bar" style="width: ' + (strength * 25) + '%; background: ' + colors[strength-1] + '"></div>' +
                    (feedback.length > 0 ? '<div style="color: rgba(255,255,255,0.5); margin-top: 5px;">Falta: ' + feedback.join(', ') + '</div>' : '');
            } else {
                strengthDiv.innerHTML = '';
            }
        });
    </script>
</body>
</html>