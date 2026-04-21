## 1. Juramento y declaración de abstención

Los peritos abajo firmantes manifiestan, bajo juramento o promesa de decir verdad, que han actuado y actuarán con la mayor objetividad posible, considerando tanto lo que pueda favorecer como lo que pueda perjudicar a cualquiera de las partes. Asimismo, declaran conocer las sanciones penales en las que podrían incurrir si incumplen su deber como peritos.

En cumplimiento de las mejores prácticas y estándares de la industria, los peritos declaran expresamente:

- Que no existe conflicto de interés alguno que pueda comprometer la objetividad del presente informe.
- Que no tienen parentesco, vínculo matrimonial o situación de hecho asimilable con ninguna de las partes, ni con sus abogados o procuradores.
- Que no tienen interés directo ni indirecto en el objeto del pleito ni en su resolución.
- Que no han prestado servicios profesionales anteriormente a ninguna de las partes en relación directa con este caso.


## 2. Palabras clave

- **Análisis forense:** disciplina orientada a identificar, preservar, analizar y presentar evidencias digitales de forma objetiva y verificable.
- **Cadena de custodia:** conjunto de medidas y registro documental que garantizan el control de la evidencia (quién la custodia, cuándo, cómo se transfiere y cómo se preserva).
- **Integridad de la evidencia:** propiedad por la cual una evidencia se mantiene inalterada; se respalda con verificaciones repetibles (p. ej., hashes).
- **Hash (MD5/SHA1/SHA256):** huella criptográfica usada para comprobar integridad; si el hash cambia, el contenido ha cambiado.
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
- **IOC (Indicador de compromiso):** evidencia observable asociada a actividad maliciosa (IPs, rutas, nombres de ficheros, patrones en logs, etc.).
- **WPScan:** herramienta de enumeración/escaneo de WordPress usada frecuentemente para identificar versiones, temas y plugins vulnerables.
- **CVE (Common Vulnerabilities and Exposures):** identificador público de una vulnerabilidad; en este caso se referencia **CVE-2015-4133** (Reflex Gallery).
- **Subida arbitraria de ficheros (Arbitrary File Upload):** vulnerabilidad que permite cargar archivos no autorizados (p. ej., PHP) en el servidor, posibilitando su ejecución.
- **Webshell:** script (habitualmente PHP) que proporciona al atacante capacidad de ejecutar acciones remotas en el servidor a través de HTTP.
- **Permisos RWX:** páginas/regiones de memoria con permisos de lectura, escritura y ejecución; pueden ser indicio de inyección, aunque también pueden aparecer por comportamientos legítimos (p. ej., JIT).
- **JIT (Just-In-Time):** técnica de ejecución/optimización que puede generar regiones RWX legítimas y producir falsos positivos en análisis de memoria.

## 3. Índice de figuras

| Figura | Descripción | Archivo |
|---:|---|---|
| 1 | Comprobación de hash de la evidencia RAM.bin | [hallazgos/memoria/9-comprobacion-hash.png](hallazgos/memoria/9-comprobacion-hash.png) |
| 2 | Creación/compilación de perfil Volatility para Linux (x64) | [hallazgos/memoria/1-creando-perfil.png](hallazgos/memoria/1-creando-perfil.png) |
| 3 | `linux_netstat`: conexiones/puertos observados en memoria | [hallazgos/memoria/2-linux_netstat.png](hallazgos/memoria/2-linux_netstat.png) |
| 4 | `pslist/pstree`: árbol/listado de procesos sin indicios evidentes de terminal maliciosa | [hallazgos/memoria/3-pslist-nohaynada.png](hallazgos/memoria/3-pslist-nohaynada.png) |
| 5 | `linux_malfind`: regiones RWX en Apache (evaluación de falso positivo JIT) | [hallazgos/memoria/4-malfind.png](hallazgos/memoria/4-malfind.png) |
| 6 | Evidencia de reconocimiento (WPScan) en access.log recuperado | [hallazgos/memoria/5-access.log-wpscan.png](hallazgos/memoria/5-access.log-wpscan.png) |
| 7 | Evidencia de explotación: subida de payload (petición POST) | [hallazgos/memoria/6-access.log-subida-payload.png](hallazgos/memoria/6-access.log-subida-payload.png) |
| 8 | Evidencia de explotación asociada al plugin Reflex Gallery (FileUploader) | [hallazgos/memoria/7-access-log-subida-payload-plugin-reflex-gallery.png](hallazgos/memoria/7-access-log-subida-payload-plugin-reflex-gallery.png) |
| 9 | Referencia visual de la vulnerabilidad (CVE-2015-4133) | [img/memoria/8-cve.png](img/memoria/8-cve.png) |
| 10 | Artefacto en disco: `reflex-gallery.3.1.3.zip` localizado en el sistema | [hallazgos/disco/image.png](hallazgos/disco/image.png) |
| 11 | Artefacto en disco: `php.php` del cargador del plugin (vector de subida) | [hallazgos/disco/image-1.png](hallazgos/disco/image-1.png) |
| 12 | Fragmento de código: ausencia de restricción de extensiones/sanitización | [hallazgos/disco/image-2.png](hallazgos/disco/image-2.png) |

## 4. Resumen Ejecutivo

Este informe resume el análisis forense de un incidente de **defacement** en un WordPress alojado en un servidor Linux (AWS), a partir de **memoria RAM (RAM.bin)** y corroboración de artefactos en **disco**.

La evidencia es consistente con un **ataque web**: reconocimiento automatizado (trazas de WPScan) y posterior explotación de una **subida arbitraria de ficheros** en el plugin **Reflex Gallery** (**CVE-2015-4133**), permitiendo cargar y ejecutar scripts PHP desde `wp-content/uploads/2018/07/`. Se observan, al menos, dos orígenes de actividad con el mismo patrón (IP `94.242.54.22` y `88.0.112.115`).

## 5. Introducción

El presente informe pericial expone los resultados del **análisis forense** realizado tras un incidente de **alteración no autorizada de contenido web (defacement)** que afectó a un sitio corporativo basado en WordPress y ejecutado sobre un servidor Linux alojado en AWS.

El objetivo de este documento es ofrecer una reconstrucción técnica, basada en evidencias, de los hechos ocurridos, identificando el vector de ataque, los artefactos asociados y el alcance del incidente, con el fin de facilitar la toma de decisiones y la adopción de medidas correctoras.

Para la elaboración del informe se han analizado los siguientes soportes/evidencias facilitadas por el responsable técnico del sistema:

- **Captura de memoria RAM** del servidor (RAM.bin).
- **Imagen forense del disco** del servidor, utilizada para corroboración de artefactos en el sistema de ficheros.

La metodología aplicada prioriza la **integridad de la evidencia** (verificación mediante funciones hash y trabajo sobre copias), la **reproducibilidad** (procedimientos y herramientas documentadas) y la **trazabilidad** entre hallazgos, artefactos e interpretaciones.

### 5.1. Antecedentes

El incidente se inicia cuando el responsable del sitio web recibe avisos de terceros (usuarios/clientes) informando de que la página muestra contenido alterado de forma no autorizada (**defacement**) y/o presenta un comportamiento anómalo.

El activo afectado corresponde a un entorno **WordPress** desplegado sobre un servidor **Linux** alojado en **AWS**, con servicio web **Apache**. Tras la detección, se considera que la causa más probable es un ataque remoto a través de la aplicación (capa web), por tratarse de un servicio expuesto a Internet.

En una primera revisión operativa, se identifican indicios en los **registros del servicio web** (principalmente `access.log` y `error.log`) compatibles con actividad automatizada sobre recursos de WordPress y con la aparición/ejecución de ficheros PHP no esperados en rutas típicas de subida de contenidos (carpeta `uploads`). Estos indicios motivan la apertura formal del análisis forense.

Con el fin de preservar la evidencia y posibilitar un análisis reproducible, se procede a:

- Aislar el sistema afectado (o limitar su exposición) para evitar nuevas modificaciones y contaminación de artefactos.
- Adquirir y custodiar una **captura de memoria** del servidor (**RAM.bin**), con verificación de integridad mediante hashes.
- Disponer de una **imagen forense de disco** para la corroboración de artefactos (ficheros y registros) sin trabajar sobre el sistema original.

Las evidencias facilitadas se sitúan en la ventana temporal de finales de **julio de 2018** y permiten orientar el análisis hacia los **registros de Apache** (peticiones HTTP y errores) y los componentes de WordPress (plugins/temas), con el objetivo de reconstruir el vector de entrada y el alcance de la alteración observada.

### 5.2. Objetivos

El presente análisis tiene como finalidad determinar, con base en evidencias digitales verificables, las circunstancias técnicas del incidente y su alcance.

De forma específica, se establecen los siguientes objetivos:

- **Preservación e integridad:** verificar y documentar la integridad de las evidencias suministradas (memoria y disco) mediante sumas hash y procedimientos reproducibles.
- **Reconstrucción temporal:** elaborar una línea de tiempo de los eventos relevantes a partir de artefactos en memoria y en registros del servicio web.
- **Identificación del vector de entrada:** determinar el mecanismo de compromiso (p. ej., vulnerabilidad explotada en componentes de WordPress) y la ruta técnica empleada.
- **Identificación de artefactos e IOCs:** localizar y describir evidencias asociadas (rutas, ficheros, IPs, User-Agent, recursos solicitados) que permitan detectar y contener actividad similar.
- **Determinación del alcance:** evaluar, hasta donde lo permitan las evidencias aportadas, qué componentes se han visto afectados (defacement/webshells) y si existen indicios de compromiso a nivel de sistema.
- **Soporte a respuesta:** aportar conclusiones técnicas orientadas a medidas correctoras y preventivas, sin exceder las limitaciones del material analizado.

--LUISKA

6- Fuentes de información

6.1- Adquisición de evidencias

7- Análisis

7.1- Herramientas utilizadas

7.2- Procesos

7.2.1- Análisis de xxx

7.2.2- Análisis de yyy

-- PABLO

8- Limitaciones

## 9. Conclusiones

Con base en el análisis del volcado de memoria **RAM.bin** y la corroboración de artefactos en **disco**, el incidente investigado es consistente con un **defacement** derivado de un **ataque web** contra un WordPress expuesto a Internet. La causa más probable es la explotación de una vulnerabilidad de **subida arbitraria de ficheros** en el plugin **Reflex Gallery** (**CVE-2015-4133**), donde se evidencian controles insuficientes en el manejo/validación de entradas y de los ficheros subidos.

Los registros recuperados (incluyendo fragmentos de **access.log** obtenidos desde RAM y registros disponibles en disco) reflejan un patrón de **reconocimiento automatizado (WPScan)** seguido de explotación mediante **peticiones POST** y ejecución posterior de ficheros PHP alojados en `wp-content/uploads/2018/07/`. Se identifican como IOCs relevantes las IPs `94.242.54.22` y `88.0.112.115`, así como nombres de scripts observados (p. ej., `PSMOfbPom.php`, `XLPYhlEtQOyiMKb.php`).

En cuanto al alcance, el triaje del sistema en memoria (conexiones, procesos y búsqueda de inyección) no aporta indicadores concluyentes de **compromiso a nivel de sistema operativo**; las detecciones RWX en procesos de Apache son compatibles con **falsos positivos** (comportamiento legítimo tipo JIT). Por tanto, con la evidencia disponible, el impacto más consistente se circunscribe a la **capa de aplicación** (WordPress) y a la **ejecución de payloads** subidos.

Se recomienda retirar o actualizar el componente vulnerable (Reflex Gallery), revisar el resto de plugins/temas, sanear la carpeta `uploads` eliminando scripts no autorizados y endurecer la configuración para **impedir la ejecución de PHP** en rutas de subida. Asimismo, mantener la trazabilidad (hashes, copias de logs y evidencias) para soportar contención, erradicación y posibles acciones posteriores.

10- Anexo 1. Sobre el perito

11- Anexo 2. Sumas de verificación

12- Anexo 3. Otras necesidades