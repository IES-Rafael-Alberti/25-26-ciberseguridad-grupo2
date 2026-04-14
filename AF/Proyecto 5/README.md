# Proyecto 5: Incident on Linux Server I

## Índice

1. [Juramento y declaración de abstención](#1-juramento-y-declaración-de-abstención)
2. [Palabras clave](#2-palabras-clave)
3. [Índice de figuras](#3-índice-de-figuras)
4. [Resumen ejecutivo](#4-resumen-ejecutivo)
5. [Introducción](#5-introducción)
	1. [Antecedentes](#51-antecedentes)
	2. [Objetivos](#52-objetivos)
6. [Fuentes de información](#6-fuentes-de-información)
	1. [Comprobación de hashes (SHA-256)](#61-comprobación-de-hashes-sha-256)
	2. [Adquisición de hallazgos](#62-adquisición-de-hallazgos)
7. [Análisis](#7-análisis)
	1. [Herramientas utilizadas](#71-herramientas-utilizadas)
	2. [Procesos](#72-procesos)
		1. [Análisis de memoria RAM](#721-análisis-de-memoria-ram)
		2. [Análisis de imagen de disco](#722-análisis-de-imagen-de-disco)
8. [Limitaciones](#8-limitaciones)
9. [Conclusiones](#9-conclusiones)
10. [Anexo 1. Sobre el perito](#10-anexo-1-sobre-el-perito)
11. [Anexo 2. Sumas de verificación](#11-anexo-2-sumas-de-verificación)
12. [Anexo 3. Otras necesidades](#12-anexo-3-otras-necesidades)

---

## 1. Juramento y declaración de abstención

Los peritos abajo firmantes manifiestan, bajo juramento o promesa de decir verdad, que han actuado y actuarán con la mayor objetividad posible, considerando tanto lo que pueda favorecer como lo que pueda perjudicar a cualquiera de las partes. Asimismo, declaran conocer las sanciones penales en las que podrían incurrir si incumplen su deber como peritos.

En cumplimiento de las mejores prácticas y estándares de la industria, los peritos declaran expresamente:

- Que no existe conflicto de interés alguno que pueda comprometer la objetividad del presente informe.
- Que no tienen parentesco, vínculo matrimonial o situación de hecho asimilable con ninguna de las partes, ni con sus abogados o procuradores.
- Que no tienen interés directo ni indirecto en el objeto del pleito ni en su resolución.
- Que no han prestado servicios profesionales anteriormente a ninguna de las partes en relación directa con este caso.

## 2. Palabras clave

- **Hash (SHA-256):** huella criptográfica usada para comprobar integridad; si el hash cambia, el contenido ha cambiado.
- **Imagen de disco (`.dd`):** copia bit a bit de un soporte/partición; permite analizar el sistema de archivos sin modificar el original.
- **Volcado de memoria (RAM dump):** captura del contenido de la memoria en un instante; puede contener procesos, conexiones y comandos que no quedan persistidos en disco.
- **MAC time:** conjunto de marcas de tiempo de un fichero: **M**odified (modificación), **A**ccessed (acceso), **C**hanged (cambio de metadatos/permisos).
- **Timeline (línea de tiempo):** correlación temporal de eventos y artefactos (MAC time, logs, ejecución de procesos) para reconstruir la secuencia del incidente.
- **Log / registro:** fichero de eventos generado por un servicio (p. ej., Apache o Samba) que permite atribución técnica y reconstrucción de actividad.
- **Apache `access.log`:** registro de peticiones HTTP; incluye IP origen, recurso solicitado, código de respuesta y a menudo el **User-Agent**.
- **User-Agent:** cadena que identifica el cliente que realiza la petición web (navegador/versión y, en muchos casos, sistema operativo).
- **Inyección de comandos (Command Injection):** vulnerabilidad donde entrada del usuario se concatena a una orden del sistema sin validación/escape, permitiendo ejecutar comandos arbitrarios.
- **RCE (Remote Code Execution):** capacidad de ejecutar código/comandos en el servidor remotamente; en este caso se materializa a través de la inyección en `ping.php`.
- **Payload:** parte “activa” del ataque (cadena/comando) diseñada para conseguir un efecto concreto (lectura de ficheros, creación de artefactos, etc.).
- **Encadenamiento de comandos (`&&`, `;`, `|`):** operadores de shell que permiten ejecutar órdenes adicionales; son indicadores típicos en inyecciones.
- **Redirección de salida (`>`):** operador de shell que vuelca la salida de un comando a un fichero; puede usarse para crear archivos con datos exfiltrados.
- **Exfiltración:** salida no autorizada de información desde el sistema comprometido hacia un tercero.
- **SMB / Samba:** protocolo y servicio de compartición de ficheros en red; en Linux suele estar gestionado por el proceso `smbd`.
- **smbd:** daemon/proceso de Samba que gestiona conexiones SMB; una conexión establecida puede indicar transferencia de ficheros.
- **Antiforense (acción antiforense):** técnica para dificultar la investigación (p. ej., borrado/vaciado de logs o eliminación de rastros).

## 3. Índice de figuras

| Nº | Figura | Descripción | Sección |
|----|--------|-------------|---------|
| 1  | ![Anexo_1.png](hallazgos/Anexo_1.png) | Extracto de access.log con peticiones desde 192.168.1.6 a ping.php y User-Agent | Anexo 3 |
| 2  | ![Anexo_2.png](hallazgos/Anexo_2.png) | Fragmento del código de /var/www/ping.php donde se ejecuta el comando del sistema con el parámetro recibido | Anexo 3 |
| 3  | ![Anexo_3.png](hallazgos/Anexo_3.png) | Evidencia del parámetro/payload con encadenamiento de comandos y referencia a passwd.txt | Anexo 3 |
| 4  | ![Anexo_4.png](hallazgos/Anexo_4.png) | Conexión SMB establecida entre servidor y atacante (asociada a smbd) | Anexo 3 |
| 5  | ![Anexo_5.png](hallazgos/Anexo_5.png) | Evidencia en disco de log.192.168.1.6 con 0 bytes (posible purga antiforense) | Anexo 3 |
| 6  | ![Anexo_6.png](hallazgos/Anexo_6.png) | Detalle adicional de la conexión SMB establecida | Anexo 3 |
| 7  | ![Anexo_7.png](hallazgos/Anexo_7.png) | Salida de Volatility (linux_bash) con el comando sudo nano /var/www/ping.php | Anexo 3 |
| 8  | ![hashes-verification.png](hallazgos/hashes-verification.png) | Sumas de verificación SHA-256 de las evidencias adquiridas | Anexo 2 |

## 4. Resumen ejecutivo

Este informe detalla la investigación de un incidente de seguridad en un servidor web **Apache**. Se identificó una vulnerabilidad de **inyección de comandos** en el archivo `ping.php`, explotada por un atacante desde la IP **192.168.1.6**. El acceso no autorizado permitió comprometer el servidor de la compañía (**192.168.1.28**).

El ataque resultó en la exfiltración del contenido del archivo `/etc/passwd`, almacenado en un nuevo archivo `passwd.txt`. La investigación documenta el método de ataque, los datos comprometidos y la evidencia de que el archivo original no fue modificado. Se concluye que el atacante logró acceso no autorizado al servidor, comprometiendo la seguridad de la información almacenada en él.

## 5. Introducción

### 5.1. Antecedentes

La organización detectó indicios de **actividad anómala y posible salida de información** desde un servidor Linux corporativo (**192.168.1.28**).

Para esclarecer los hechos, se ha llevado a cabo una investigación basada en las adquisiciones facilitadas (imagen de almacenamiento y volcado de memoria), complementada con el análisis de artefactos del sistema (registros de distintos servicios, procesos en ejecución y cadenas recuperadas en memoria).

### 5.2. Objetivos

Los objetivos de este informe forense son:

- Identificar la **vulnerabilidad** explotada en la aplicación web y explicar su mecanismo (inyección de comandos).
- Determinar la **IP de origen**, el **cliente (User-Agent)** y el **sistema operativo** empleado por el atacante a partir de evidencias de red y registros.
- Verificar qué **datos fueron exfiltrados** y por qué canal/servicio se produjo la salida.
- Explicar por qué el **archivo original** objeto del robo (p. ej., un fichero del sistema) puede no reflejar cambios en sus marcas de tiempo durante el incidente.
- Proponer **medidas de reparación y mitigación** para evitar recurrencias (validación/saneamiento de entradas, hardening y controles de registro/monitorización).

## 6. Fuentes de información

### 6.1. Comprobación de hashes (SHA-256)

| Archivo | Hash SHA-256 original | Hash SHA-256 verificado |
|---|---|---|
| perfil_memoria.zip | `18b30b973223b8ab233aa1581bccd35bef6c678b29e671b3fe3a7ee5ea24b076` | `18b30b973223b8ab233aa1581bccd35bef6c678b29e671b3fe3a7ee5ea24b076` |
| captura_ram.lime.zip | `632d3d95260753029d7c9ade15e0dcab69b8fe7eb08d7001d9f923b22ddf003f` | `632d3d95260753029d7c9ade15e0dcab69b8fe7eb08d7001d9f923b22ddf003f` |
| imagen_disco.dd.zip | `b0189203fa682fd086ed3c52a3723ac46ab896a2fb8e4daf49ed6228bc7d3b76` | `b0189203fa682fd086ed3c52a3723ac46ab896a2fb8e4daf49ed6228bc7d3b76` |
| captura_ram.lime | `0f5d751208b08450e298b8d27f22451dd2ae158dfc1cb80b974f360e9a88ff05` | `0f5d751208b08450e298b8d27f22451dd2ae158dfc1cb80b974f360e9a88ff05` |
| image_disco.dd | `9f2b2dace6cfebec1b6f956fc231e199c00f39e05d50286b8f284043537d65d9` | `9f2b2dace6cfebec1b6f956fc231e199c00f39e05d50286b8f284043537d65d9` |


### 6.2. Adquisición de hallazgos
te dejo la parte del ping:
![alt text](hallazgos/ping.png)
## 7. Análisis

### 7.1. Herramientas utilizadas

| Herramienta | Uso en la investigación |
|---|---|
| Volatility (Framework v2.6) | Análisis forense avanzado de memoria RAM. Utilizado para extraer el historial de comandos (`linux_bash`), conexiones de red activas (`linux_netstat`), procesos (`linux_pstree`) y archivos abiertos (`linux_lsof`) en el momento del incidente. |
| FTK Imager | Herramienta para el análisis forense de discos y volúmenes. Permite examinar y extraer artefactos, generar líneas de tiempo (MAC times) y visualizar evidencias sin alterarlas. |
| Comandos de gestión de archivos (Linux) | Comandos como `unzip`, `mkdir` y `cp`, utilizados en Kali Linux para preparar el entorno forense (descompresión y carga manual de evidencias, por ejemplo perfiles `.zip` para Volatility). |
| Comandos de exploración nativos | Comandos como `cat` y `ls -la`, usados para inspección directa de evidencias en disco (p. ej., comprobar el estado de `/var/log/samba/log.192.168.1.6` o revisar permisos de rutas web). |
| `grep` | Filtrado de texto y búsqueda de indicadores/artefactos concretos en evidencias o salidas de herramientas. |
| `strings` | Extracción de cadenas legibles desde volcados binarios (p. ej., memoria) para localizar indicadores. |
| `sha256sum` | Cálculo y verificación de integridad mediante hashes SHA-256 de las evidencias adquiridas. |

### 7.2. Procesos

En este apartado se documenta la metodología seguida para el análisis de las dos fuentes principales de evidencia: **memoria RAM** (volcado `captura_ram.lime`) e **imagen de disco** (`image_disco.dd`).

Durante todo el proceso se ha trabajado **sobre copias** de las evidencias y se ha comprobado la **integridad** mediante SHA-256 (ver sección 6.1 y anexo de sumas). Las capturas de pantalla y extractos más relevantes se incluyen como anexos para respaldar los hallazgos.

Para la **presentación de hallazgos**, en cada vestigio se ha documentado: **ruta de localización**, **descripción del contenido**, **MAC time**, **tamaño lógico** y **valor hash** (cuando aplica), referenciando la evidencia visual correspondiente en anexos.

#### 7.2.1. Análisis de memoria RAM

El análisis de memoria se orientó a identificar **actividad en ejecución**, **conexiones de red** y **rastros de comandos/payloads** que no necesariamente quedan reflejados en disco.

1. **Preparación y validación**
	- Se verificó el hash de `captura_ram.lime` y de sus contenedores comprimidos.
	- Se utilizó Volatility Framework (perfil Linux proporcionado para el caso) para poder ejecutar plugins Linux de forma consistente.
2. **Enumeración inicial del sistema en memoria**
	- Se revisaron procesos y servicios relevantes para el caso (Apache y Samba), así como puertos en escucha y conexiones activas.
3. **Identificación de conexiones de red relevantes (SMB)**
	- Se localizaron conexiones establecidas hacia el puerto SMB, asociadas al proceso `smbd`.
	- Evidencia: conexión TCP entre el servidor (**192.168.1.28**) y el atacante (**192.168.1.6**) (ver `hallazgos/Anexo_4.png` y `hallazgos/Anexo_6.png`).
4. **Recuperación de historial de comandos (traza de terminal)**
	- Se extrajo el historial de comandos en memoria para reconstruir acciones realizadas durante la ventana del incidente.
	- Evidencia: aparición del comando de edición del fichero web (`sudo nano /var/www/ping.php`) (ver `hallazgos/Anexo_7.png`).
5. **Búsqueda de indicadores y cadenas en memoria (payloads)**
	- Se realizaron búsquedas de texto/indicadores en memoria para localizar rastros de la inyección.
	- Evidencia: cadena compatible con el encadenamiento de comandos y redirección a `passwd.txt` (ver `hallazgos/Anexo_3.png`).
6. **Documentación y anexos**
	- Los resultados (salidas relevantes y capturas) se consolidaron como anexos para su trazabilidad en el informe.

#### 7.2.2. Análisis de imagen de disco

El análisis de disco se centró en localizar **artefactos persistentes**: código vulnerable, registros del sistema y evidencias de actividad del atacante.

1. **Apertura de la imagen y trabajo en modo solo lectura**
	- Se verificó el hash de `image_disco.dd` (sección 6.1) y se analizó la imagen en modo de solo lectura.
	- Se extrajeron metadatos de los ficheros de interés (MAC time, tamaño lógico) para su documentación posterior.
2. **Localización y revisión del recurso web vulnerable**
	- Se localizó el fichero `/var/www/ping.php` y se revisó su contenido para validar el origen de la vulnerabilidad.
	- Evidencia: uso de llamada al sistema con entrada controlada por el usuario sin validación estricta (ver `hallazgos/ping.png` y `hallazgos/Anexo_2.png`).
3. **Correlación con registros web (Apache)**
	- Se analizaron los logs de Apache (p. ej., `/var/log/apache2/access.log`) filtrando por el recurso `ping.php` y la IP **192.168.1.6**.
	- Evidencia: peticiones hacia `ping.php` desde la IP del atacante y User-Agent que identifica cliente y sistema operativo (ver `hallazgos/Anexo_1.png`).
4. **Revisión de rastros del servicio Samba (SMB)**
	- Se revisaron los logs del servicio Samba, especialmente el fichero de log por IP.
	- Evidencia: existencia de `/var/log/samba/log.192.168.1.6` con **tamaño 0 bytes**, compatible con un borrado/limpieza del registro (ver `hallazgos/Anexo_5.png`).
5. **Búsqueda de artefactos de exfiltración**
	- Se revisó el árbol de `/var/www/` y otros directorios relevantes en busca de ficheros generados durante el incidente (por ejemplo, volcados a texto accesibles por web), y se correlacionó con los indicadores obtenidos en RAM y con los accesos en los logs.
6. **Documentación y anexos**
	- Los extractos relevantes y capturas se referenciaron como anexos para justificar cada conclusión del análisis.

## 8. Limitaciones

### 8.1. Falta de registros detallados en Samba

La principal limitación de esta investigación es la ausencia de información en el archivo `/var/log/samba/log.192.168.1.6`, que tiene 0 bytes. La configuración por defecto de Samba solo se define la ruta y el tamaño máximo del log, pero no se especifica el nivel de registro (`log level`).

Por defecto, Samba utiliza `log level = 0`, lo que significa que no se almacena prácticamente ninguna información sobre las conexiones o actividades.

**Consecuencias de esta limitación:**

- No se puede determinar con exactitud qué otros archivos navegó o extrajo el atacante vía SMB.
- El alcance real de la exfiltración queda indeterminado — solo se confirma `/etc/passwd`, pero podrían existir otros archivos robados que no dejaron rastro.


### 8.2. Identificación limitada del atacante

La IP identificada como origen del ataque es 192.168.1.6, una dirección privada de red local (RFC 1918). Esto supone varias limitaciones:

- No es posible rastrear al atacante más allá del perímetro de la red interna.
- No se puede asegurar si 192.168.1.6 corresponde a la máquina real del atacante o a un equipo intermedio comprometido (pivote).
- La atribución definitiva del responsable requeriría el análisis forense del dispositivo físico con esa IP en el momento del incidente.

### 8.3. Alcance desconocido de la sesión SMB

Se confirma la existencia de una sesión SMB activa, pero debido a la falta de registros:

- No se puede reconstruir el árbol de directorios navegado ni el listado de archivos transferidos por el atacante.
- No se puede determinar si la sesión SMB fue utilizada también para subir herramientas adicionales al servidor (malware, scripts, etc.).

## 9. Conclusiones

## 10. Anexo 1. Sobre el perito


Los peritos responsables de este informe son:

- Carlos Alcina  
	Titulación: Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM)  
	Correo: calcrom0607@g.educaand.es

- Pablo González  
	Titulación: Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM) y Técnico Superior en Desarrollo de Aplicaciones Web (DAW)  
	Correo: pablo.gonzalez@g.educaand.es

- Luis Carlos Romero  
	Titulación: Técnico Superior en Desarrollo de Aplicaciones Web (DAW)  
	Correo: luiscarlos.romero@g.educaand.es

## 11. Anexo 2. Sumas de verificación
![alt text](hallazgos/hashes-verification.png)

## 12. Anexo 3. Otras necesidades

### 12.1. Índice de evidencias (capturas)

Las siguientes capturas se adjuntan como soporte de los hallazgos descritos en la sección 7.2:

- `hallazgos/Anexo_1.png`: extracto de `access.log` con peticiones desde **192.168.1.6** a `ping.php` y User-Agent.
- `hallazgos/Anexo_2.png`: fragmento del código de `/var/www/ping.php` donde se ejecuta el comando del sistema con el parámetro recibido.
- `hallazgos/Anexo_3.png`: evidencia del parámetro/payload con encadenamiento de comandos y referencia a `passwd.txt`.
- `hallazgos/Anexo_4.png`: conexión SMB establecida entre servidor y atacante (asociada a `smbd`).
- `hallazgos/Anexo_5.png`: evidencia en disco de `log.192.168.1.6` con **0 bytes** (posible purga antiforense).
- `hallazgos/Anexo_6.png`: detalle adicional de la conexión SMB establecida.
- `hallazgos/Anexo_7.png`: salida de Volatility (`linux_bash`) con el comando `sudo nano /var/www/ping.php`.

-------------------------------------------------------------

<table>
	<thead>
		<tr>
			<th>Nombre y Apellidos</th>
			<th>Cargo / Titulación</th>
			<th>Firma</th>
			<th>Fecha</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Carlos Alcina</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM)</td>
			<td><img src="hallazgos/firma_carlos.png" alt="Firma Carlos Alcina" height="60"></td>
			<td>14/04/2026</td>
		</tr>
		<tr>
			<td>Pablo González</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM) y Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="hallazgos/firma_pg.jpeg" alt="Firma de Pablo González" height="60"></td>
			<td>14/04/2026</td>
		</tr>
		<tr>
			<td>Luis Carlos Romero</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="hallazgos/lc_firma.png" alt="Firma de Luis Carlos Romero" height="60"></td>
			<td>14/04/2026</td>
		</tr>
	</tbody>
</table>
