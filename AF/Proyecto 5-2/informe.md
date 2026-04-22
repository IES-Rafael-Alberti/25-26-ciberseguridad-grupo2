# Informe pericial forense sobre incidente de seguridad en WordPress (Defacement y análisis de memoria/disco)

## Índice

1. [Juramento y declaración de abstención](#1-juramento-y-declaración-de-abstención)
2. [Palabras clave](#2-palabras-clave)
3. [Índice de figuras](#3-índice-de-figuras)
4. [Resumen Ejecutivo](#4-resumen-ejecutivo)
5. [Introducción](#5-introducción)
	1. [Antecedentes](#51-antecedentes)
	2. [Objetivos](#52-objetivos)
6. [Fuentes de información](#6-fuentes-de-información)
	1. [Comprobación de hashes (MD5 y SHA1)](#61-comprobación-de-hashes-md5-y-sha-1)
	2. [Adquisición de hallazgos](#62-adquisición-de-hallazgos)
7. [Análisis](#7-análisis)
	1. [Herramientas utilizadas](#71-herramientas-utilizadas)
	2. [Procesos](#72-procesos)
		1. [Análisis de la imagen de disco](#721-análisis-de-la-imagen-de-disco)
		2. [Análisis del volcado de memoria](#722-análisis-del-volcado-de-memoria)
		3. [Cronología del ataque](#73-cronología-del-ataque)
8. [Limitaciones](#8-limitaciones)
9. [Conclusiones](#9-conclusiones)
10. [Anexo 1. Sobre el perito](#10-anexo-1-sobre-el-perito)
11. [Anexo 2. Cadena de custodia](#11-anexo-2-cadena-de-custodia)
12. [Anexo 3. Otras necesidades](#12-anexo-3-otras-necesidades)

## 1. Juramento y declaración de abstención

Los peritos abajo firmantes manifiestan, bajo juramento o promesa de decir verdad, que han actuado y actuarán con la mayor objetividad posible, considerando tanto lo que pueda favorecer como lo que pueda perjudicar a cualquiera de las partes. Asimismo, declaran conocer las sanciones penales en las que podrían incurrir si incumplen su deber como peritos.

En cumplimiento de las mejores prácticas y estándares de la industria, los peritos declaran expresamente:

- Que no existe conflicto de interés alguno que pueda comprometer la objetividad del presente informe.
- Que no tienen parentesco, vínculo matrimonial o situación de hecho asimilable con ninguna de las partes, ni con sus abogados o procuradores.
- Que no tienen interés directo ni indirecto en el objeto del pleito ni en su resolución.
- Que no han prestado servicios profesionales anteriormente a ninguna de las partes en relación directa con este caso.


## 2. Palabras clave

- **Análisis forense:** disciplina orientada a identificar, preservar, analizar y presentar hallazgos digitales de forma objetiva y verificable.
- **Cadena de custodia:** conjunto de medidas y registro documental que garantizan el control de la hallazgo (quién la custodia, cuándo, cómo se transfiere y cómo se preserva).
- **Integridad de la hallazgo:** propiedad por la cual una hallazgo se mantiene inalterada; se respalda con verificaciones repetibles (p. ej., hashes).
- **Hash (MD5/SHA1):** huella criptográfica usada para comprobar integridad; si el hash cambia, el contenido ha cambiado.
- **Volcado de memoria (RAM dump):** captura del contenido de la memoria en un instante; puede contener procesos, conexiones, fragmentos de logs y comandos que no quedan persistidos en disco.
- **Imagen de disco:** copia forense de un soporte o partición (p. ej., `.dd` o contenedor forense) que permite analizar el sistema de archivos sin modificar el original.
- **LiME:** herramienta habitual en Linux para adquisición de memoria volátil (obtención del volcado RAM).
- **Volatility:** framework de análisis de memoria que permite extraer artefactos (procesos, conexiones, módulos, etc.) a partir de un volcado RAM.
- **Perfil de Volatility (Linux):** conjunto de símbolos/estructura del kernel necesario para interpretar correctamente la memoria de un sistema Linux concreto.
- **MAC time:** conjunto de marcas temporales asociadas a un fichero: **M**odified (modificación), **A**ccessed (acceso), **C**hanged (cambio de metadatos/permisos).
- **Timeline (línea de tiempo):** correlación temporal de eventos y artefactos (logs, MAC time, procesos) para reconstruir la secuencia del incidente.
- **Log / registro:** fichero con eventos generados por un servicio (p. ej., Apache) que permite atribución técnica y reconstrucción de actividad.
- **Apache `access.log`:** registro de peticiones HTTP; incluye IP origen, recurso solicitado, código de respuesta y, habitualmente, el **User-Agent**.
- **User-Agent:** cadena que identifica el cliente que realiza la petición web (herramienta/navegador y, a veces, sistema operativo); útil para detectar automatización.
- **IOC (Indicador de compromiso):** hallazgo observable asociada a actividad maliciosa (IPs, rutas, nombres de ficheros, patrones en logs, etc.).
- **WPScan:** herramienta de enumeración/escaneo de WordPress usada frecuentemente para identificar versiones, temas y plugins vulnerables.
- **CVE (Common Vulnerabilities and Exposures):** identificador público de una vulnerabilidad; en este caso se referencia **CVE-2015-4133** (Reflex Gallery).
- **Subida arbitraria de ficheros (Arbitrary File Upload):** vulnerabilidad que permite cargar archivos no autorizados (p. ej., PHP) en el servidor, posibilitando su ejecución.
- **Webshell:** script (habitualmente PHP) que proporciona al atacante capacidad de ejecutar acciones remotas en el servidor a través de HTTP.
- **Permisos RWX:** páginas/regiones de memoria con permisos de lectura, escritura y ejecución; pueden ser indicio de inyección, aunque también pueden aparecer por comportamientos legítimos (p. ej., JIT).
- **JIT (Just-In-Time):** técnica de ejecución/optimización que puede generar regiones RWX legítimas y producir falsos positivos en análisis de memoria.

## 3. Índice de figuras

| Figura | Descripción | Archivo |
|---:|---|---|
| 1 | Comprobación de hash del hallazgo RAM.bin | [hallazgos/memoria/9-comprobacion-hash.png](hallazgos/memoria/9-comprobacion-hash.png) |
| 2 | Creación/compilación de perfil Volatility para Linux (x64) | [hallazgos/memoria/1-creando-perfil.png](hallazgos/memoria/1-creando-perfil.png) |
| 3 | `linux_netstat`: conexiones/puertos observados en memoria | [hallazgos/memoria/2-linux_netstat.png](hallazgos/memoria/2-linux_netstat.png) |
| 4 | `pslist/pstree`: árbol/listado de procesos sin indicios evidentes de terminal maliciosa | [hallazgos/memoria/3-pslist-nohaynada.png](hallazgos/memoria/3-pslist-nohaynada.png) |
| 5 | `linux_malfind`: regiones RWX en Apache (evaluación de falso positivo JIT) | [hallazgos/memoria/4-malfind.png](hallazgos/memoria/4-malfind.png) |
| 6 | hallazgo de reconocimiento (WPScan) en access.log recuperado | [hallazgos/memoria/5-access.log-wpscan.png](hallazgos/memoria/5-access.log-wpscan.png) |
| 7 | hallazgo de explotación: subida de payload (petición POST) | [hallazgos/memoria/6-access.log-subida-payload.png](hallazgos/memoria/6-access.log-subida-payload.png) |
| 8 | hallazgo de explotación asociada al plugin Reflex Gallery (FileUploader) | [hallazgos/memoria/7-access-log-subida-payload-plugin-reflex-gallery.png](hallazgos/memoria/7-access-log-subida-payload-plugin-reflex-gallery.png) |
| 9 | Referencia visual de la vulnerabilidad (CVE-2015-4133) | [img/memoria/8-cve.png](img/memoria/8-cve.png) |
| 10 | Artefacto en disco: `reflex-gallery.3.1.3.zip` localizado en el sistema | [hallazgos/disco/reflex.png](hallazgos/disco/reflex.png) |
| 11 | Archivo PHP vulnerable (`php.php`) | [hallazgos/disco/php-vulnerable.png](hallazgos/disco/php-vulnerable.png) |
| 12 | Fragmento de código vulnerable (sin restricción/sanitización) | [hallazgos/disco/parte-vulnerable.png](hallazgos/disco/parte-vulnerable.png) |
| 13 | Registro de errores (error.log) con actividad sospechosa | [hallazgos/disco/error-log.png](hallazgos/disco/error-log.png) |
| 14 | Registro de accesos (access.log) con conexiones relevantes | [hallazgos/disco/acces-log.png](hallazgos/disco/acces-log.png) |
| 15 | Archivos PHP señuelo subidos en uploads | [hallazgos/disco/php-eliminados.png](hallazgos/disco/php-eliminados.png) |
| 16 | Modificación de `index.html` tras el defacement | [hallazgos/disco/html-modificado.png](hallazgos/disco/html-modificado.png) |

## 4. Resumen Ejecutivo

Este informe resume el análisis forense de un incidente de **defacement** en un WordPress alojado en un servidor Linux (AWS), a partir de **memoria RAM (RAM.bin)** y corroboración de artefactos en **disco**.

La hallazgo es consistente con un **ataque web**: reconocimiento automatizado (trazas de WPScan) y posterior explotación de una **subida arbitraria de ficheros** en el plugin **Reflex Gallery** (**CVE-2015-4133**), permitiendo cargar y ejecutar scripts PHP desde `wp-content/uploads/2018/07/`. Se observan, al menos, dos orígenes de actividad con el mismo patrón (IP `94.242.54.22` y `88.0.112.115`).

## 5. Introducción

El presente informe pericial expone los resultados del **análisis forense** realizado tras un incidente de **alteración no autorizada de contenido web (defacement)** que afectó a un sitio corporativo basado en WordPress y ejecutado sobre un servidor Linux alojado en AWS.

El objetivo de este documento es ofrecer una reconstrucción técnica, basada en hallazgos, de los hechos ocurridos, identificando el vector de ataque, los artefactos asociados y el alcance del incidente, con el fin de facilitar la toma de decisiones y la adopción de medidas correctoras.

Para la elaboración del informe se han analizado los siguientes soportes/hallazgos facilitadas por el responsable técnico del sistema:

- **Captura de memoria RAM** del servidor (RAM.bin).
- **Imagen forense del disco** del servidor, utilizada para corroboración de artefactos en el sistema de ficheros.

La metodología aplicada prioriza la **integridad de la hallazgo** (verificación mediante funciones hash y trabajo sobre copias), la **reproducibilidad** (procedimientos y herramientas documentadas) y la **trazabilidad** entre hallazgos, artefactos e interpretaciones.

### 5.1. Antecedentes

El incidente se inicia cuando el responsable del sitio web recibe avisos de terceros (usuarios/clientes) informando de que la página muestra contenido alterado de forma no autorizada (**defacement**) y/o presenta un comportamiento anómalo.

El activo afectado corresponde a un entorno **WordPress** desplegado sobre un servidor **Linux** alojado en **AWS**, con servicio web **Apache**. Tras la detección, se considera que la causa más probable es un ataque remoto a través de la aplicación (capa web), por tratarse de un servicio expuesto a Internet.

En una primera revisión operativa, se identifican indicios en los **registros del servicio web** (principalmente `access.log` y `error.log`) compatibles con actividad automatizada sobre recursos de WordPress y con la aparición/ejecución de ficheros PHP no esperados en rutas típicas de subida de contenidos (carpeta `uploads`). Estos indicios motivan la apertura formal del análisis forense.

Con el fin de preservar la hallazgo y posibilitar un análisis reproducible, se procede a:

- Aislar el sistema afectado (o limitar su exposición) para evitar nuevas modificaciones y contaminación de artefactos.
- Adquirir y custodiar una **captura de memoria** del servidor (**RAM.bin**), con verificación de integridad mediante hashes.
- Disponer de una **imagen forense de disco** para la corroboración de artefactos (ficheros y registros) sin trabajar sobre el sistema original.

Los hallazgos facilitadas se sitúan en la ventana temporal de finales de **julio de 2018** y permiten orientar el análisis hacia los **registros de Apache** (peticiones HTTP y errores) y los componentes de WordPress (plugins/temas), con el objetivo de reconstruir el vector de entrada y el alcance de la alteración observada.

### 5.2. Objetivos

El presente análisis tiene como finalidad determinar, con base en hallazgos digitales verificables, las circunstancias técnicas del incidente y su alcance.

De forma específica, se establecen los siguientes objetivos:

- **Preservación e integridad:** verificar y documentar la integridad de los hallazgos suministradas (memoria y disco) mediante sumas hash y procedimientos reproducibles.
- **Reconstrucción temporal:** elaborar una línea de tiempo de los eventos relevantes a partir de artefactos en memoria y en registros del servicio web.
- **Identificación del vector de entrada:** determinar el mecanismo de compromiso (p. ej., vulnerabilidad explotada en componentes de WordPress) y la ruta técnica empleada.
- **Identificación de artefactos e IOCs:** localizar y describir hallazgos asociadas (rutas, ficheros, IPs, User-Agent, recursos solicitados) que permitan detectar y contener actividad similar.
- **Determinación del alcance:** evaluar, hasta donde lo permitan los hallazgos aportadas, qué componentes se han visto afectados (defacement/webshells) y si existen indicios de compromiso a nivel de sistema.
- **Soporte a respuesta:** aportar conclusiones técnicas orientadas a medidas correctoras y preventivas, sin exceder las limitaciones del material analizado.

6- Fuentes de información

6.1- Comprobación de hashes (MD5 y SHA 1)


#### Sumas de verificación de hallazgos principales

| hallazgo | MD5 original | MD5 verificado | SHA1 original | SHA1 verificado |
|-----------|------------------------------------------|------------------------------------------|----------------------------------------------------------|----------------------------------------------------------|
| Imagen de disco (disco.dd) | bac5561328b477f0508fab7c5d9ee0a6 | bac5561328b477f0508fab7c5d9ee0a6 | 5b0a9cc8ff4ebd5aa3e1e36d8713e3b24b072e79 | 5b0a9cc8ff4ebd5aa3e1e36d8713e3b24b072e79 |
| Volcado de memoria (memoria.lime) | e063c257d2f41ddee65ea1fdabe64e95 | e063c257d2f41ddee65ea1fdabe64e95 | bc2ebb435e75b3406280a2967b1c2696fc3e160a | bc2ebb435e75b3406280a2967b1c2696fc3e160a |

La verificación de hashes garantiza que los archivos analizados no han sido alterados durante el proceso forense y que los resultados obtenidos son reproducibles.

Para la elaboración de este informe se han consultado las siguientes fuentes:
- hallazgos digitales proporcionadas (imagen de disco y volcado de memoria RAM).
- Documentación técnica de los sistemas involucrados (WordPress, plugins instalados, sistema operativo Linux).
- Bases de datos de vulnerabilidades (CVE, NIST, Exploit-DB) para identificación de fallos conocidos.
- Manuales y documentación oficial de herramientas forenses empleadas (LiME, Volatility, hashdeep, etc.).
- Logs y registros extraídos de los sistemas analizados.

6.2- Adquisición de hallazgos

La adquisición de hallazgos se realizó siguiendo procedimientos forenses estandarizados para garantizar la integridad y trazabilidad:
- Se recibieron una imagen forense del disco y un volcado de memoria RAM.
- Se verificó la integridad mediante el cálculo de sumas hash (MD5/SHA1) antes y después de cada manipulación.
- Se documentó la cadena de custodia, registrando fechas, responsables y acciones realizadas sobre cada hallazgo.
- Las copias de trabajo se generaron a partir de los originales, preservando estos en almacenamiento seguro y sin alteraciones.

7- Análisis

7.1- Herramientas utilizadas

Para el análisis de los hallazgos se emplearon las siguientes herramientas:
- **LiME**: para la adquisición del volcado de memoria RAM en sistemas Linux.
- **Volatility**: para el análisis de la memoria RAM y extracción de artefactos (procesos, conexiones, módulos, strings, etc.).
- **hashdeep**: para el cálculo y verificación de sumas hash de archivos y hallazgos.
- **Autopsy/The Sleuth Kit**: para el análisis de la imagen de disco y recuperación de archivos borrados o modificados.
- **Grep, strings, less**: utilidades de análisis manual de texto y búsqueda de patrones en logs y memoria.
- **Herramientas de análisis de logs**: para la revisión y correlación de eventos en los registros de Apache y del sistema.

7.2- Procesos

El análisis se estructuró en varias fases:
1. **Verificación de integridad**: comprobación de los hashes de los hallazgos recibidas.
2. **Montaje y exploración de la imagen de disco**: identificación de rutas relevantes, archivos sospechosos y artefactos de interés.
3. **Análisis del volcado de memoria**: extracción de procesos activos, conexiones de red, módulos cargados y búsqueda de indicadores de compromiso.
4. **Correlación temporal**: elaboración de una línea de tiempo de eventos a partir de los metadatos de archivos y registros de logs.
5. **Identificación de IOCs**: localización de direcciones IP, rutas, nombres de archivos y patrones asociados a la intrusión.
6. **Documentación y reporte**: registro detallado de hallazgos, procedimientos y resultados obtenidos.

7.2.1- Análisis de la imagen de disco


Se montó la imagen de disco en modo solo lectura para evitar alteraciones. Se revisaron las rutas asociadas a la aplicación web (WordPress), especialmente la carpeta `wp-content/uploads`, donde se identificaron archivos PHP sospechosos. Se analizaron los logs de Apache (`access.log`, `error.log`) para detectar patrones de acceso anómalos, intentos de subida de archivos y ejecución de scripts no autorizados. Se emplearon herramientas de recuperación para identificar archivos borrados o modificados recientemente.

#### Archivos PHP subidos en uploads de WordPress
Durante el análisis de la carpeta `wp-content/uploads` se identificaron varios archivos PHP subidos de forma no autorizada. Al inspeccionar su contenido, se observó que no contienen código malicioso típico (como webshells o backdoors), sino un bloque de texto que corresponde a una cabecera PGP firmada con metadatos de repositorios de Ubuntu. Este hallazgo es inusual, ya que los archivos PHP subidos no ejecutan código, sino que parecen haber sido utilizados como señuelo, relleno o para ocultar actividad. Es posible que el atacante intentara evadir mecanismos de detección o simplemente probar la capacidad de subida de archivos. Se recomienda mantener vigilancia sobre este tipo de archivos y restringir la ejecución de PHP en rutas de subida.

#### Modificación del archivo index.html
Se detectó que el archivo `index.html` de la aplicación web fue modificado. Este tipo de alteración es característico de ataques de defacement, donde el atacante sustituye o altera la página principal para mostrar mensajes, imágenes o simplemente para evidenciar el compromiso del sistema. La modificación del `index.html` constituye una prueba clara de acceso no autorizado y manipulación de los contenidos web. Se recomienda conservar una copia íntegra del archivo alterado como hallazgo y comparar su contenido con versiones legítimas para identificar los cambios introducidos. Este hallazgo refuerza la hipótesis de un ataque dirigido a la capa de aplicación, con impacto visible para los usuarios y potencial afectación reputacional.

7.2.2- Análisis del volcado de memoria

El volcado de memoria fue procesado con Volatility, identificando procesos activos en el momento de la adquisición, conexiones de red abiertas y módulos cargados en memoria. Se buscaron cadenas de texto y artefactos relacionados con la ejecución de payloads y comandos sospechosos. Se correlacionaron los hallazgos de memoria con los eventos registrados en disco para reconstruir la secuencia del ataque y determinar el alcance del compromiso.

### 7.3. Cronología del ataque


La siguiente tabla muestra únicamente los principales hitos relacionados con la actividad del atacante (IP 94.242.54.22):

| Evento                                              | Fecha y hora (UTC/CEST)   | Artefacto/Origen    | IP implicada     |
|-----------------------------------------------------|---------------------------|---------------------|------------------|
| Explotación de vulnerabilidad en plugin Reflex Gallery | 23/07/2018 11:22          | access.log / Disco  | 94.242.54.22     |
| Subida y ejecución de WebShells maliciosas            | 24/07/2018 02:46          | access.log / Disco  | 94.242.54.22     |
| Creación del archivo de defacement (index.html)       | 24/07/2018 14:00          | Sistema de archivos | 94.242.54.22     |

## 8. Limitaciones

Este análisis tiene límites por cómo se obtuvo y cómo “vive” la evidencia. En RAM, al ser un servidor Linux en AWS con un kernel específico, Volatility no traía un perfil listo para usar y hubo que crear uno a medida; eso añade trabajo y puede hacer que algunos artefactos no salgan completos si el perfil no encaja al 100%.

Además, no todo lo que pasó se puede reconstruir entero: la memoria es volátil y los ficheros pueden no estar “residentes” cuando se hace el volcado. Aunque vemos nombres/rutas de scripts y trazas en logs, no siempre es posible recuperar el contenido completo de todas las webshells o payloads.

Por último, hay señales de que el atacante intentó dejar menos rastro (borrado de ficheros en `wp-content/uploads/`), lo que obliga a tirar de recuperación en disco (carving) y eso suele devolver fragmentos sueltos y sin metadatos. También puede haber manipulación de tiempos (timestomping), y algunas alertas de memoria (como RWX en Apache) pueden ser falsos positivos por comportamientos legítimos (p. ej., JIT), así que se ha priorizado la correlación con logs y la corroboración en disco del componente vulnerable.

Adicionalmente, el análisis en disco muestra que algunos ficheros con extensión `.php` localizados en rutas de subida (`wp-content/uploads/`) no contienen una webshell típica, sino contenido no ejecutable (cabeceras PGP/metadata). Esto puede indicar archivos señuelo, pruebas de subida o limpieza parcial posterior, lo que limita la capacidad de atribuir con certeza el payload final únicamente a partir del sistema de ficheros.

## 9. Conclusiones

Con base en el análisis del volcado de memoria **RAM.bin** y la corroboración de artefactos en **disco**, el incidente investigado es consistente con un **defacement** derivado de un **ataque web** contra un WordPress expuesto a Internet. La causa más probable es la explotación de una vulnerabilidad de **subida arbitraria de ficheros** en el plugin **Reflex Gallery** (**CVE-2015-4133**), donde se evidencian controles insuficientes en el manejo/validación de entradas y de los ficheros subidos.

La corroboración en disco refuerza el impacto visible del incidente: se ha identificado una **modificación de `index.html`** compatible con defacement. Asimismo, en la ruta `wp-content/uploads/2018/07/` se han localizado varios ficheros `.php` subidos de forma no autorizada cuyo contenido no corresponde a una webshell convencional (p. ej., cabeceras PGP), lo que es consistente con el uso de **señuelos/pruebas de subida** o con una **limpieza parcial** posterior al incidente.

Los registros recuperados (incluyendo fragmentos de **access.log** obtenidos desde RAM y registros disponibles en disco) reflejan un patrón de **reconocimiento automatizado (WPScan)** seguido de explotación mediante **peticiones POST** y ejecución posterior de ficheros PHP alojados en `wp-content/uploads/2018/07/`. Se identifican como IOCs relevantes las IPs `94.242.54.22` y `88.0.112.115`, así como nombres de scripts observados (p. ej., `PSMOfbPom.php`, `XLPYhlEtQOyiMKb.php`).

En cuanto al alcance, el triaje del sistema en memoria (conexiones, procesos y búsqueda de inyección) no aporta indicadores concluyentes de **compromiso a nivel de sistema operativo**; las detecciones RWX en procesos de Apache son compatibles con **falsos positivos** (comportamiento legítimo tipo JIT). Por tanto, con la evidencia disponible, el impacto más consistente se circunscribe a la **capa de aplicación** (WordPress) y a la **ejecución de payloads** subidos.

Se recomienda retirar o actualizar el componente vulnerable (Reflex Gallery), revisar el resto de plugins/temas, sanear la carpeta `uploads` eliminando scripts no autorizados y endurecer la configuración para **impedir la ejecución de PHP** en rutas de subida. Asimismo, mantener la trazabilidad (hashes, copias de logs y hallazgos) para soportar contención, erradicación y posibles acciones posteriores.

## 10. Anexo 1. Sobre el perito

Los peritos responsables del presente informe son:

- **Carlos Alcina**
	- **Titulación:** Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM)
	- **Correo:** calcrom0607@g.educaand.es

- **Pablo González**
	- **Titulación:** Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM) y Técnico Superior en Desarrollo de Aplicaciones Web (DAW)
	- **Correo:** pablo.gonzalez@g.educaand.es

- **Luis Carlos Romero**
	- **Titulación:** Técnico Superior en Desarrollo de Aplicaciones Web (DAW)
	- **Correo:** luiscarlos.romero@g.educaand.es

## 11. Anexo 2. Cadena de custodia

### 11.1. Información del caso

| Campo | Valor |
|---|---|
| Número de Caso | 06 |
| Tipo de Investigación | Análisis forense de incidente de seguridad web |
| Fecha de Adquisición | 18/04/2026 |
| Lugar de Adquisición | C. Amiel, s/n, 11012 Barriada de la Paz, Cádiz |
| Apertura | lunes, 20 de abril de 2026, 08:00 |
| Cierre | miércoles, 22 de abril de 2026, 23:45 |

### 11.2. Identificación de evidencias (soportes base)

| ID | Evidencia | Identificador/archivo | Tamaño | Hash/Integridad |
|---|---|---|---:|---|
| E-01 | Volcado de memoria RAM | RAM.bin | 1.073.336.384 bytes | MD5: e063c257d2f41ddee65ea1fdabe64e95; SHA1: bc2ebb435e75b3406280a2967b1c2696fc3e160a |
| E-02 | Imagen forense de disco | Disc.E01 (extraído de Disc.E01.zip) | 984M (aprox.) | SHA256 (Disc.E01): 8e90936d626024e01db33c129d2317a5dac6feedd6fa7c31fe1fbab365261e4a; MD5 (Disc.E01): bac5561328b477f0508fab7c5d9ee0a6; SHA1 (Disc.E01): 5b0a9cc8ff4ebd5aa3e1e36d8713e3b24b072e79; SHA256 (Disc.E01.zip): 4e1b3861944f1e3da6869b4fb40fb864b18e1197c77d5915aa74c0943f0b10ff |

### 11.3. Preservación del hallazgo original

| Campo | Valor |
|---|---|
| Fecha de Entrega | 22/04/2026 |
| Hora de Entrega | 23:45 |
| Recibido por | Manuel Jesús Rivas Sánchez |
| Ubicación en el Juzgado | C. Amiel, s/n, 11012 Barriada de la Paz, Cádiz |

### 11.4. Creación y verificación de copias

| Campo | Valor |
|---|---|
| Fecha y Hora de Creación | 20/04/2026, 08:00 |
| Técnico Responsable | Carlos Alcina Romero (Grupo 2) |
| Hash de la Copia (SHA-256) | Disc.E01: 8e90936d626024e01db33c129d2317a5dac6feedd6fa7c31fe1fbab365261e4a; Disc.E01.zip: 4e1b3861944f1e3da6869b4fb40fb864b18e1197c77d5915aa74c0943f0b10ff |
| Verificación de Integridad | Sí |

| Campo | Valor |
|---|---|
| Entregado a | Manuel Jesús Rivas Sánchez |
| Fecha y Hora de Entrega | 22/04/2026, 23:45 |

### 11.5. Registro de accesos y verificaciones

| Campo | Valor |
|---|---|
| Fecha y Hora | lunes, 20 de abril de 2026, 08:00 |
| Propósito | Inicio del análisis / verificación inicial |
| Técnico | Luis Carlos Romero Navarro (Grupo 2) |
| Hash Verificado (SHA-256) | Disc.E01: 8e90936d626024e01db33c129d2317a5dac6feedd6fa7c31fe1fbab365261e4a |
| Verificación de Integridad | Sí |

| Campo | Valor |
|---|---|
| Fecha y Hora | miércoles, 22 de abril de 2026, 23:45 |
| Propósito | Cierre del análisis / verificación final |
| Técnico | Pablo González Silva (Grupo 2) |
| Hash Verificado (SHA-256) | Disc.E01: 8e90936d626024e01db33c129d2317a5dac6feedd6fa7c31fe1fbab365261e4a |
| Verificación de Integridad | Sí |

### 11.6. Artefactos derivados (extraídos para el análisis)

Los siguientes elementos se consideran **artefactos derivados** obtenidos de las evidencias base (principalmente E-02) y/o recuperados desde memoria, manteniendo verificación de integridad mediante hashes.

| ID | Artefacto | Ruta/Origen | Hash (MD5) | Hash (SHA1) |
|---|---|---|---|---|
| A-01 | access.log (Apache) | /var/log/apache2/access.log | 325d4e7fad4213e46faf58dcf76af017 | b9008fda5c891b12fd3b9bdc3a8bd5f958341057 |
| A-02 | error.log (Apache) | /var/log/apache2/error.log | 496044572974077b25d87ecc950ec4bc | 064fdd82955e34d2872aede4aa97fc983f830fb6 |
| A-03 | reflex-gallery.3.1.3.zip | /home/ubuntu/reflex-gallery.3.1.3.zip | 61c84a3520511ebda3a502b77d90f617 | db17794998222e9c9a6d5b98af02754eff58a5d6 |
| A-04 | php.php (FileUploader) | /var/www/html/wordpress/wp-content/plugins/reflex-gallery/admin/scripts/FileUploader/php.php | 020410718b64647311d6c4594e229bc5 | 5e8f0d5a917d2937318a9bafd0529135bd473e70 |

### 11.7. Registro de custodia y actuaciones

| Fecha/hora | Actuación | Responsable | Observaciones |
|---|---|---|---|
| 13/04/2026 | Adquisición/recepción de evidencias | Carlos Alcina Romero (Grupo 2) | Registro inicial de E-01 y E-02. |
| lunes, 20 de abril de 2026, 08:00 | Apertura del análisis | Luis Carlos Romero Navarro (Grupo 2) | Inicio del análisis sobre copias de trabajo; verificación de hashes disponibles. |
| 20/04/2026–22/04/2026 | Extracción y verificación de artefactos | Pablo González Silva (Grupo 2) | Extracción de logs y ficheros de interés y verificación por hash (A-01 a A-04). |
| miércoles, 22 de abril de 2026, 23:45 | Cierre del análisis | Pablo González Silva (Grupo 2) | Cierre de actuaciones y preparación de entregables. |

## 12. Anexo 3. Otras necesidades

### 12.1. Índice de hallazgos

| Ruta | Contenido | MAC | Tamaño (bytes) | HASH MD5 | HASH SHA1 |
|---|---|---|---:|---|---|
| /var/log/apache2/access.log | access.log | M: 24/07/2018 05:19:11 | 111514 | 325d4e7fad4213e46faf58dcf76af017 | b9008fda5c891b12fd3b9bdc3a8bd5f958341057 |
| /var/log/apache2/error.log | error.log | M: 23/07/2018 11:10:16 | 1369 | 496044572974077b25d87ecc950ec4bc | 064fdd82955e34d2872aede4aa97fc983f830fb6 |
| /home/ubuntu/reflex-gallery.3.1.3.zip | reflex-gallery.3.1.3.zip | M: 20/07/2018 09:35:53 | 650283 | 61c84a3520511ebda3a502b77d90f617 | db17794998222e9c9a6d5b98af02754eff58a5d6 |
| /var/www/html/wordpress/wp-content/plugins/reflex-gallery/admin/scripts/FileUploader/php.php | php.php | M: 20/07/2018 09:54:05 | 5590 | 020410718b64647311d6c4594e229bc5 | 5e8f0d5a917d2937318a9bafd0529135bd473e70 |
| /var/www/html/wordpress/index.html | index.html | M/C: 23/07/2018 14:00:18 CEST | N/D | N/D (no extraído) | N/D (no extraído) |
| /var/www/html/wordpress/wp-content/uploads/2018/07/PLoeJFOEVoc.php | PLoeJFOEVoc.php | M/A/C/B: 23/07/2018 16:22:37 CEST | 102 | N/D (no extraído) | N/D (no extraído) |
| /var/www/html/wordpress/wp-content/uploads/2018/07/XLPYhlEtQOyiMKb.php | XLPYhlEtQOyiMKb.php | M: 23/07/2018 20:34:03 CEST; C/A: 24/07/2018 02:46:56 CEST; B: 24/07/2018 02:46:55 CEST | 4257532 | N/D (no extraído) | N/D (no extraído) |
| /var/www/html/wordpress/wp-content/uploads/2018/07/vmGAbaiewrSSuMs.php | vmGAbaiewrSSuMs.php | M: 24/07/2018 01:59:00 CEST; C/A: 24/07/2018 02:46:56 CEST; B: 24/07/2018 02:46:54 CEST | 106824 | N/D (no extraído) | N/D (no extraído) |
| /var/www/html/wordpress/wp-content/uploads/2018/07/yDdoSpsx.php | yDdoSpsx.php | M: 24/07/2018 01:59:00 CEST; C/A: 24/07/2018 02:46:56 CEST; B: 24/07/2018 02:46:54 CEST | 109196 | N/D (no extraído) | N/D (no extraído) |

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
			<td><img src="img/firma_carlos.png" alt="Firma Carlos Alcina" height="60"></td>
			<td>22/04/2026</td>
		</tr>
		<tr>
			<td>Pablo González</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM) y Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="img/firma_pg.jpeg" alt="Firma de Pablo González" height="60"></td>
			<td>22/04/2026</td>
		</tr>
		<tr>
			<td>Luis Carlos Romero</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="img/lc_firma.png" alt="Firma de Luis Carlos Romero" height="60"></td>
			<td>22/04/2026</td>
		</tr>
	</tbody>
</table>

