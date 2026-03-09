<?php
// Capturamos el verbo HTTP que está usando el usuario
$metodo = $_SERVER['REQUEST_METHOD'];

// 1. Si el usuario investiga con OPTIONS (El Centinela confiesa)
if ($metodo === 'OPTIONS') {
    // Mandamos las cabeceras diciendo qué métodos permitimos
    header("Allow: GET, POST, OPTIONS, PREVIEW");
    header("Content-Length: 0");
    http_response_code(200);
    exit;
}

// 2. Si el usuario lanza el exploit con el verbo secreto PREVIEW
if ($metodo === 'PREVIEW') {
    http_response_code(200);
    header("Content-Type: text/plain; charset=UTF-8");
    echo "=== ACCESO DE EMERGENCIA CONCEDIDO ===\n\n";
    echo "Sistemas restaurados al 100%.\n";
    echo "Aquí tienes tu recompensa, Arquitecto: \n\n";
    echo "flag{PROTOCOLO_PREVIEW_ACTIVADO}\n";
    exit;
}

// 3. Comportamiento por defecto (Si entran normal por el navegador con GET o POST)
http_response_code(405); // Error 405: Method Not Allowed
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AETERNA - Terminal Raíz</title>
    <style>
        body { 
            background-color: #050505; 
            color: #ff3333; 
            font-family: 'Courier New', Courier, monospace; 
            text-align: center; 
            padding-top: 15%; 
        }
        h1 { 
            font-size: 3em; 
            text-shadow: 2px 2px #550000; 
            margin-bottom: 10px;
        }
        p { 
            font-size: 1.2em; 
        }
        .comando {
            color: #ffffff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>ESTADO: BLOQUEO TOTAL</h1>
    <p>Verbo de acceso '<span class="comando"><?php echo htmlspecialchars($metodo); ?></span>' revocado por el Protocolo de Cuarentena.</p>
    <p>El núcleo no procesará peticiones estándar.</p>
    <p>No insista.</p>
</body>
</html>