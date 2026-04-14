# **Informe de Investigación Forense: Proyecto 5 \- Incident on Linux Server I**

###   **1\. Identificar la vulnerabilidad**

![alt text](img/1.png)

* **Archivo Vulnerable:** `/var/www/ping.php`  
* **Tipo de Vulnerabilidad:** Inyección de Comandos (**Command Injection**).  
* **Explicación:** El script `ping.php` toma una dirección IP del usuario y la pasa directamente a un comando del sistema (como `shell_exec("ping " . $target)`) sin filtrar caracteres especiales.  
* **Evidencia Técnica (RAM):** Mediante el análisis de cadenas de texto (`strings`) en el volcado de memoria, se ha recuperado el rastro del *payload* inyectado en el formulario web. El atacante utilizó el operador lógico `&&` para encadenar comandos maliciosos a la ejecución legítima del ping.  
* **Evidencia en Disco:** El archivo se localiza en la ruta raíz del servidor web (`/var/www/`).  
* ![][image2]  
  RAM:  
  ![][image3]

  ###  **2\. IP, Cliente y Sistema Operativo del atacante**

* **IP del Atacante:** **192.168.1.6** (Confirmado por la conexión `ESTABLISHED` en el proceso `smbd` de la RAM y por el nombre del archivo de log en Samba).  
* **IP del Servidor:** 192.168.1.28.  
* **Dato extra:** Los logs de Apache (`/var/log/apache2/access.log`) contienen las peticiones hacia `ping.php` desde la IP `.6`. El campo *User-Agent* de estas peticiones identifica el Navegador y el Sistema Operativo exacto del atacante.  
* ![][image4]

 **3\. Determinación de la exfiltración de datos**

* **Datos exfiltrados confirmados:** Se ha verificado el robo del archivo de cuentas del sistema **`/etc/passwd`**.  
* **Método de robo (Web):** Según el rastro en memoria, el atacante ejecutó `192.168.1.28 && cat /etc/passwd > passwd.txt`. Esto volcó las credenciales a un archivo de texto accesible vía web para su descarga.![][image5]  
* **Actividad de Red:** Se confirma una conexión activa mediante el protocolo SMB (proceso `smbd`, PID 11114\) entre el servidor y la IP del atacante.  
* **Acciones Antiforenses:** La existencia de un registro en `/var/log/samba/log.192.168.1.6` con **0 bytes** de tamaño evidencia una purga intencionada de los logs para ocultar la lista completa de archivos que el atacante navegó y extrajo de forma remota.

![][image6]

###  **4\. El misterio del "archivo original"**

* El archivo `/etc/passwd` original no muestra actividad de modificación en el sistema de archivos porque el atacante solo realizó una **operación de lectura**. Al redirigir la salida a un archivo nuevo creado por él (`passwd.txt`) o leer los datos a través del servicio Samba (SMB), los *timestamps* de modificación del archivo original permanecen intactos. El atacante operó principalmente en la memoria RAM y en archivos temporales/nuevos, evitando así activar alertas basadas en la integridad de los archivos originales del sistema.


  ###  **5\. Propuesta de Soluciones (Reparación)**

  **Código Seguro:** Implementar una validación estricta de la entrada del usuario. Antes de ejecutar el comando, el sistema debe verificar que el dato recibido cumple estrictamente con el formato de una dirección IP (usando `filter_var` con `FILTER_VALIDATE_IP`).  
  **Saneamiento:** Utilizar funciones de escape de argumentos como `escapeshellarg()` en PHP para asegurar que cualquier carácter especial inyectado sea interpretado como texto plano y no como un comando ejecutable.  
  **Endurecimiento del Sistema (Hardening):**  
* Eliminar los privilegios de `sudo` para el usuario `www-data`.  
* Deshabilitar el servicio Samba o restringirlo a redes locales seguras.  
* Implementar un WAF (Web Application Firewall) para bloquear peticiones con caracteres de encadenamiento de comandos (`&&`, `;`, `|`).

