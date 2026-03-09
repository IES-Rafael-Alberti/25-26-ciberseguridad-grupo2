<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AETERNA - Sistema de Contención</title>
    <style>
        body {
            background-color: #020202;
            color: #dcdcdc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        h1 { font-size: 2.5em; color: #ff4444; margin-bottom: 0; }
        p { max-width: 600px; margin-top: 10px; font-size: 1.1em; }

        #open-terminal-btn {
            background-color: transparent;
            border: 2px solid #00ff00;
            color: #00ff00;
            padding: 15px 30px;
            font-size: 1.2em;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            cursor: pointer;
            margin-top: 30px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.4);
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        #open-terminal-btn:hover {
            background-color: #00ff00;
            color: #020202;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.7);
        }

        #terminal-window {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 800px;
            height: 80vh;
            background-color: rgba(10, 10, 10, 0.95);
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.8);
            overflow: hidden;
            flex-direction: column;
            z-index: 1000;
        }

        #terminal-header {
            background-color: #222;
            padding: 8px 15px;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #999;
            font-size: 0.9em;
            font-family: sans-serif;
        }

        .window-controls { display: flex; gap: 8px; }
        .control { width: 12px; height: 12px; border-radius: 50%; }
        .close { background-color: #ff5f56; cursor: pointer; }
        .minimize { background-color: #ffbd2e; }
        .maximize { background-color: #27c93f; }

        #terminal-body {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            color: #00ff00;
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            text-align: left;
            line-height: 1.4;
        }

        #output { white-space: pre-wrap; word-wrap: break-word; }
        .rojo { color: #ff3333; }
        .blanco { color: #ffffff; }
        .prompt { color: #00aaff; font-weight: bold; margin-right: 10px; }

        .input-line { display: flex; align-items: center; margin-top: 15px;}
        #comando-input {
            background: transparent;
            border: none;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 15px;
            outline: none;
            flex-grow: 1;
            caret-color: #00ff00;
        }

        #terminal-body::-webkit-scrollbar { width: 8px; }
        #terminal-body::-webkit-scrollbar-track { background: #0a0a0a; }
        #terminal-body::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
    </style>
</head>
<body>

    <div id="web-content">
        <h1>SISTEMA DE DEFENSA CREADO</h1>
        <p>El núcleo de AETERNA ha detectado una corrupción masiva de datos y ha entrado en modo de contención severo. Los protocolos estándar de comunicación están inoperativos.</p>
        <p>Se requiere un Arquitecto de Protocolos de nivel superior para iniciar diagnóstico de emergencia.</p>
        
        <button id="open-terminal-btn">_INICIAR_PROTOCOLO_SHELL_</button>
    </div>

    <div id="terminal-window">
        <div id="terminal-header">
            <span>AETERNA Root Shell v1.0 [CONEXIÓN LOCAL]</span>
            <div class="window-controls">
                <div class="control minimize"></div>
                <div class="control maximize"></div>
                <div class="control close" onclick="closeTerminal()"></div>
            </div>
        </div>
        
        <div id="terminal-body">
            <div id="output"><span class="rojo">=== ERROR CRÍTICO: BLOQUEO DE PROTOCOLO ESTÁNDAR ===</span>
Usted se encuentra fuera de la cuarentena de red.
Interrogue al sistema local para encontrar una brecha.
Utilice herramientas de diagnóstico como 'curl'.

Escriba 'help' para ver comandos básicos.</div>
            
            <div class="input-line">
                <span class="prompt">arquitecto@aeterna:~$</span>
                <input type="text" id="comando-input" autocomplete="off" spellcheck="false">
            </div>
        </div>
    </div>

    <script>
        const btnOpen = document.getElementById('open-terminal-btn');
        const windowTerminal = document.getElementById('terminal-window');
        const inputComando = document.getElementById('comando-input');
        const output = document.getElementById('output');
        const terminalBody = document.getElementById('terminal-body');

        btnOpen.addEventListener('click', () => {
            windowTerminal.style.display = 'flex';
            inputComando.focus();
        });

        function closeTerminal() {
            windowTerminal.style.display = 'none';
        }

        terminalBody.addEventListener('click', () => inputComando.focus());

        inputComando.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const cmd = this.value.trim();
                const cmdLower = cmd.toLowerCase();
                let respuesta = '';

                if (cmd === '') {
                    respuesta = '';
                } 
                else if (cmdLower === 'help') {
                    respuesta = "Comandos disponibles en modo emergencia:\n - help: Muestra esta ayuda.\n - clear: Limpia la terminal.\n - curl [opciones] [URL]: Interactúa con servicios web locales.";
                } 
                else if (cmdLower === 'clear') {
                    output.innerHTML = ''; 
                    this.value = '';
                    return; 
                } 
                else if (cmd.startsWith('curl')) {
                    // Ahora aceptamos cualquier URL que empiece por http (ej. localhost, aeterna.local...)
                    if (!cmd.includes('http')) {
                        respuesta = "<span class='blanco'>Uso de curl simulado:</span> curl -i -X [MÉTODO] http://aeterna.local";
                    } 
                    else if (cmd.includes('OPTIONS')) {
                        respuesta = "<span class='blanco'>HTTP/1.1 200 OK\nHost: server\nAllow: GET, POST, OPTIONS, TRACE\nContent-Length: 0\nContent-Type: text/plain</span>";
                    } 
                    else if (cmd.includes('TRACE')) {
                        respuesta = "<span class='blanco'>HTTP/1.1 200 OK\nContent-Type: text/plain\n\n</span><span class='rojo'>=== ACCESO DE EMERGENCIA CONCEDIDO ===</span>\nRestaurando Núcleo de Ciudad AETERNA...\n[###############] 100%\n\n<span class='blanco'>Aquí tienes tu recompensa, Arquitecto:</span>\n\nflag{s1stem_r3bo0t_succ3ss}";
                    } 
                    else {
                        respuesta = "<span class='blanco'>HTTP/1.1 405 Method Not Allowed\nAllow: GET, POST, OPTIONS, TRACE\n\n</span><span class='rojo'>ESTADO: BLOQUEO TOTAL</span>\nVerbo de acceso revocado por el Protocolo de Cuarentena.\nEl núcleo no procesará peticiones estándar. No insista.";
                    }
                } 
                else {
                    respuesta = "<span class='rojo'>zsh: command not found: " + cmd.split(' ')[0] + "</span>\nPruebe con 'help' o 'curl'.";
                }

                // Construcción limpia para que no se peguen las líneas
                const promptLine = `<span class="prompt">arquitecto@aeterna:~$</span> ${cmd}`;
                const resLine = respuesta ? `\n${respuesta}` : '';
                
                if (output.innerHTML === '') {
                    output.innerHTML = `${promptLine}${resLine}`;
                } else {
                    output.innerHTML += `\n\n${promptLine}${resLine}`;
                }
                
                this.value = ''; 
                terminalBody.scrollTo(0, terminalBody.scrollHeight);
            }
        });
    </script>
</body>
</html>