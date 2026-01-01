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

// Verificar que existan los parámetros necesarios
if (empty($_GET['code'])) {
    exit('Error: No se recibió el código de autorización');
}

// Verificar el state para protección CSRF
if (empty($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    exit('Invalid OAuth state');
}

// Limpiar el state de la sesión después de validarlo
unset($_SESSION['oauth_state']);

// Configuración de GitHub OAuth
$client_id = GITHUB_CLIENT_ID;
$client_secret = GITHUB_CLIENT_SECRET;
$redirect_uri = REDIRECT_URI;

try {
    // Intercambiar el código por un token de acceso
    $token_url = 'https://github.com/login/oauth/access_token';
    
    $params = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $_GET['code'],
        'redirect_uri' => $redirect_uri
    ];
    
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($response, true);
    
    if (empty($tokenData['access_token'])) {
        error_log('OAuth Error: ' . print_r($tokenData, true)); // Log interno
        exit('Error en la autenticación. Por favor, intenta de nuevo.');
    }
    
    $accessToken = $tokenData['access_token'];
    
    // Obtener datos del usuario de GitHub
    $user_url = 'https://api.github.com/user';
    
    $ch = curl_init($user_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'User-Agent: PHP-OAuth-App'
    ]);
    
    $userResponse = curl_exec($ch);
    curl_close($ch);
    
    $userData = json_decode($userResponse, true);
    
    if (empty($userData['id'])) {
        exit('Error obteniendo datos del usuario');
    }

    // Leer usuarios existentes
    $usuarios = [];
    if (file_exists(DATA_FILE)) {
        $usuarios = json_decode(file_get_contents(DATA_FILE), true);
    }
    
    // Buscar si el usuario ya existe (por github_id o email)
    $usuarioExistente = null;
    $indexExistente = null;
    
    foreach ($usuarios as $index => $u) {
        // Buscar por github_id o por email si coincide
        if ((isset($u['github_id']) && $u['github_id'] == $userData['id']) ||
            (isset($u['email']) && $userData['email'] && $u['email'] === $userData['email'])) {
            $usuarioExistente = $u;
            $indexExistente = $index;
            break;
        }
    }
    
if ($usuarioExistente) {
    // Usuario existe, actualizar datos (sanitizados)
    $usuarios[$indexExistente]['github_id'] = (int)$userData['id'];
    $usuarios[$indexExistente]['login'] = htmlspecialchars($userData['login'], ENT_QUOTES, 'UTF-8');
    $usuarios[$indexExistente]['nombre'] = htmlspecialchars($userData['name'] ?? $userData['login'], ENT_QUOTES, 'UTF-8');
    if ($userData['email'] && filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
        $usuarios[$indexExistente]['email'] = $userData['email'];
    }
    $usuarios[$indexExistente]['avatar'] = filter_var($userData['avatar_url'] ?? '', FILTER_SANITIZE_URL);
        $usuarios[$indexExistente]['oauth_provider'] = 'github';
        $usuarios[$indexExistente]['last_login'] = date('Y-m-d H:i:s');
        
        $userId = $usuarios[$indexExistente]['id'];
    } else {
        // Usuario nuevo, crear registro
        $nuevoId = count($usuarios) > 0 ? max(array_column($usuarios, 'id')) + 1 : 1;
        
$nuevoUsuario = [
    'id' => $nuevoId,
    'github_id' => (int)$userData['id'],
    'login' => htmlspecialchars($userData['login'], ENT_QUOTES, 'UTF-8'),
    'nombre' => htmlspecialchars($userData['name'] ?? $userData['login'], ENT_QUOTES, 'UTF-8'),
    'apellidos' => '',
    'email' => filter_var($userData['email'] ?? 'github_' . $userData['id'] . '@oauth.local', FILTER_VALIDATE_EMAIL) ?: 'github_' . $userData['id'] . '@oauth.local',
    'avatar' => filter_var($userData['avatar_url'] ?? '', FILTER_SANITIZE_URL),
    'oauth_provider' => 'github',
    'password' => '', // No hay password para usuarios OAuth
    'created_at' => date('Y-m-d H:i:s'),
    'last_login' => date('Y-m-d H:i:s')
];

        
        $usuarios[] = $nuevoUsuario;
        $userId = $nuevoId;
    }
    
    // Guardar en archivo JSON
    file_put_contents(DATA_FILE, json_encode($usuarios, JSON_PRETTY_PRINT));
    
    // ===== FIN GUARDAR/ACTUALIZAR =====
    
// Generar tokens (access + refresh)
$accessPayload = [
    'iat' => time(),
    'exp' => time() + 900, // 15 minutos
    'type' => 'access',
    'data' => [
        'id' => $userId,
        'email' => $userData['email'] ?? 'github_' . $userData['id'] . '@oauth.local'
    ]
];

$refreshPayload = [
    'iat' => time(),
    'exp' => time() + 604800, // 7 días
    'type' => 'refresh',
    'data' => [
        'id' => $userId,
        'email' => $userData['email'] ?? 'github_' . $userData['id'] . '@oauth.local'
    ]
];

$accessToken = JWT::encode($accessPayload, SECRET_KEY, 'HS256');
$refreshToken = JWT::encode($refreshPayload, SECRET_KEY, 'HS256');

// Guardar en sesión
$_SESSION['user'] = [
    'id' => $userId,
    'email' => $userData['email'] ?? 'github_' . $userData['id'] . '@oauth.local',
    'name' => $userData['name'] ?? $userData['login'],
    'login' => $userData['login']
];
$_SESSION['access_token'] = $accessToken;
$_SESSION['refresh_token'] = $refreshToken;

    
    // Redirigir a una página intermedia que guardará el token en localStorage
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Autenticación exitosa</title>
        <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Rajdhani', sans-serif;
                background: linear-gradient(135deg, #0a0e27 0%, #1a1447 50%, #2d1b69 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                color: #fff;
                text-align: center;
                margin: 0;
            }
            .success-box {
                background: rgba(10, 14, 39, 0.85);
                border: 2px solid rgba(0, 255, 136, 0.5);
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 0 60px rgba(0, 255, 136, 0.3);
            }
            h2 {
                color: #00ff88;
                font-size: 32px;
                margin-bottom: 20px;
                font-weight: 700;
            }
            p {
                font-size: 18px;
                margin-bottom: 20px;
                color: rgba(255, 255, 255, 0.8);
            }
            .loading {
                display: inline-block;
                width: 30px;
                height: 30px;
                border: 4px solid rgba(0, 255, 136, 0.3);
                border-top-color: #00ff88;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <div class="success-box">
            <h2>✓ Autenticación Exitosa</h2>
<p>Bienvenido <?php echo htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8'); ?>!</p>
            <p>Redirigiendo al panel de administración...</p>
            <div class="loading"></div>
        </div>
        
        <script>
                // Guardar ambos tokens en localStorage
                const accessToken = '<?php echo $accessToken; ?>';
                const refreshToken = '<?php echo $refreshToken; ?>';
                
                localStorage.setItem('access_token', accessToken);
                localStorage.setItem('refresh_token', refreshToken);
                
                // Redirigir después de guardar
                setTimeout(function() {
                    window.location.href = 'admin_usuarios.php';
                }, 1500);
        </script>
    </body>
    </html>
    <?php
    exit;
    
} catch (Exception $e) {
    exit('Error: ' . $e->getMessage());
}
?>