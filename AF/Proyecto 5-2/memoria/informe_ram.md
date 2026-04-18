# Memoria de Trabajo: Análisis Forense de Defacement Web

## 1. Entorno de Análisis y Preparación

El análisis se ha llevado a cabo sobre una captura de memoria RAM (RAM.bin) correspondiente a un servidor Linux alojado en la nube de Amazon Web Services (AWS).

Dadas las particularidades del análisis de memoria en sistemas Linux con la herramienta Volatility 2.6, el primer paso metodológico consistió en identificar la versión exacta del sistema operativo y compilar un perfil específico (Linuxubuntu_1604_aws_perfilx64) para poder estructurar y leer la memoria volátil correctamente.

## 2. Investigación Forense en Memoria RAM

La investigación se dividió en dos fases: el descarte de compromiso a nivel de sistema operativo (pruebas con resultado negativo) y la búsqueda de artefactos a nivel de aplicación web.

### 2.1. Pruebas con resultado negativo (Triaje del Sistema)

De acuerdo a las buenas prácticas forenses, se ejecutaron diversos plugins para buscar persistencia, rootkits o reverse shells. Todas estas pruebas arrojaron un resultado negativo, indicando que el atacante no obtuvo acceso a nivel de terminal o root.

- **Análisis de conexiones de red (`linux_netstat`)**: Se verificaron los puertos a la escucha y las conexiones establecidas. No se hallaron conexiones hacia IPs de Control y Mando (C2) ni puertos atípicos abiertos. Solo se evidenció tráfico legítimo en los puertos 80/443 y una conexión de administración por SSH.
- **Jerarquía de procesos (`linux_pstree` / `linux_pslist`)**: El árbol de procesos mostró un comportamiento estándar. No se encontraron procesos camuflados, scripts maliciosos ejecutándose en segundo plano, ni procesos de terminal (bash, sh) colgando del servicio web apache2.
- **Búsqueda de código inyectado (`linux_malfind`)**: Se buscó código ofuscado o inyecciones de memoria. La herramienta detectó regiones con permisos RWX (Lectura, Escritura y Ejecución) en los procesos de Apache. No obstante, el análisis de las instrucciones en ensamblador reveló que se trata de un falso positivo generado por la compilación JIT del motor de PHP; no hay inyección de shellcodes maliciosos.

### 2.2. Reconstrucción de los Hechos (Cronología del Ataque)

Tras descartar el compromiso del sistema, el análisis se enfocó en el vector de ataque web, recuperando de la memoria RAM el historial de comandos (bash) y fragmentos de los registros de Apache (access.log y error.log).

A partir de estas evidencias, se ha reconstruido la siguiente línea temporal (Timeline):

| Fecha y hora (UTC) | Evento | Detalle |
|---|---|---|
| 23 de Julio de 2018 - 11:08:35 UTC | Reconocimiento automatizado | El atacante desde la IP 94.242.54.22 inicia un escaneo activo utilizando la herramienta WPScan contra el servidor, enumerando los componentes, temas y plugins instalados en WordPress. |
| 23 de Julio de 2018 - 11:20:26 UTC | Explotación de la vulnerabilidad | El escáner detecta la presencia del plugin vulnerable Reflex Gallery. El atacante (suplantando su User-Agent) realiza una petición POST maliciosa explotando una vulnerabilidad de Arbitrary File Upload. |
| 23 de Julio de 2018 - 11:20:28 UTC | Infección y Defacement | Inmediatamente después, los registros muestran al atacante accediendo (GET) directamente al script PHP inyectado en la carpeta de uploads (ej. PSMOfbPom.php), tomando control de la web y materializando el defacement. |
| 23 de Julio de 2018 - 11:25:35 UTC | Intrusión de un segundo actor | Se detecta una segunda intrusión desde una IP distinta (88.0.112.115), repitiendo la misma petición POST para aprovechar la vulnerabilidad abierta y subir nuevas webshells (ej. vmGAbaiewrSSuMs.php). |
| 24 de Julio de 2018 - 05:24:19 UTC | Respuesta a Incidentes (Conexión Admin) | Se registra una conexión legítima vía SSH (evidenciada en el plugin linux_bash). El administrador del sistema se conecta para revisar los registros de acceso de Apache y evaluar los daños. |
| 24 de Julio de 2018 - 05:27:03 UTC | Adquisición de Evidencia (Volcado RAM) | El administrador descarga, compila y ejecuta la herramienta forense LiME para volcar la memoria RAM del servidor, generando el archivo RAM.bin que fundamenta el presente análisis. |

## 3. Registro y Atributos de las Evidencias Digitales

A continuación, se listan los artefactos localizados mediante la investigación de la RAM. (Nota: Los detalles criptográficos y de tiempo (MAC time) se completarán tras la extracción física de los archivos desde la imagen del disco duro para garantizar su integridad forense).

### 3.1. Soportes Base Adquiridos

| Atributo | Soporte 1 | Soporte 2 |
|---|---|---|
| Nombre del archivo | RAM.bin | [Completar nombre imagen disco] |
| Localización original | Memoria Volátil del servidor | Disco físico del servidor |
| Tamaño | [Completar ej. 1.0 GB] | [Completar ej. 20 GB] |
| Valor Hash (SHA256/MD5) | [Completar Hash] | [Completar Hash] |

### 3.2. Artefactos y Evidencias Extraídas

#### Artefacto 1: Archivo de registro del servidor (Log)

| Atributo | Detalle de la Evidencia |
|---|---|
| Nombre del archivo | access.log (extraído como access_dumped.log) |
| Localización | /var/log/apache2/access.log |
| MAC Time (Modified) | Pendiente de corroboración cruzada en análisis de disco. |
| MAC Time (Accessed) | Pendiente de corroboración cruzada en análisis de disco. |
| MAC Time (Changed) | Pendiente de corroboración cruzada en análisis de disco. |
| Tamaño | 111.514 bytes |
| Valor Hash (MD5) | 325d4e7fad4213e46faf58dcf76af017 |
| Valor Hash (SHA256) | 46bf61392de369143890ae080e91502050f9478cd3d1dcb063c8223a6e58662e |
| Descripción / Atributo | Contiene el registro de las peticiones HTTP (WPScan y POST de explotación). Evidencia las IPs de los atacantes y la cronología de peticiones maliciosas. |

#### Artefacto 2: Script malicioso 1 (Webshell/Defacement)

| Atributo | Detalle de la Evidencia |
|---|---|
| Nombre del archivo | PSMOfbPom.php |
| Localización | /var/www/html/wordpress/wp-content/uploads/2018/07/PSMOfbPom.php |
| MAC Time (Modified) | Pendiente de Fase 2 (Análisis de Disco) |
| MAC Time (Accessed) | Pendiente de Fase 2 (Análisis de Disco) |
| MAC Time (Changed) | Pendiente de Fase 2 (Análisis de Disco) |
| Tamaño | Pendiente de Fase 2 (Análisis de Disco) |
| Valor Hash | Evidencia no residente en memoria activa (limitación de análisis SLUB en RAM). Extracción física y cálculo de hash delegados a la imagen del disco. |
| Descripción / Atributo | Script inyectado a través de la vulnerabilidad del plugin Reflex Gallery. Usado para comprometer la web. |

#### Artefacto 3: Script malicioso 2 (Webshell/Defacement)

| Atributo | Detalle de la Evidencia |
|---|---|
| Nombre del archivo | XLPYhlEtQOyiMKb.php |
| Localización | /var/www/html/wordpress/wp-content/uploads/2018/07/XLPYhlEtQOyiMKb.php |
| MAC Time (Modified) | Pendiente de Fase 2 (Análisis de Disco) |
| MAC Time (Accessed) | Pendiente de Fase 2 (Análisis de Disco) |
| MAC Time (Changed) | Pendiente de Fase 2 (Análisis de Disco) |
| Tamaño | Pendiente de Fase 2 (Análisis de Disco) |
| Valor Hash | Evidencia no residente en memoria activa (limitación de análisis SLUB en RAM). Extracción física y cálculo de hash delegados a la imagen del disco. |
| Descripción / Atributo | Segundo script inyectado durante el ataque. |

