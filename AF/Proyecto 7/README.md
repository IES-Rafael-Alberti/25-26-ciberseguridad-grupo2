# Proyecto 7

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

| **Término**                       | **Definición**                                                                                                                                                                                                                 |
|------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Análisis forense digital           | Disciplina que aplica técnicas científicas y metodologías rigurosas para identificar, recuperar y analizar evidencias digitales de manera que pueda ser presentada en procedimientos legales.                                 |
| Ciberacoso                        | Acoso persistente, intimidación o humillación de una persona mediante el uso de plataformas digitales, redes sociales o aplicaciones de mensajería, causando daño psicológico o emocional.                                    |
| Defacement                        | Alteración no autorizada del contenido de un sitio web, cuenta de redes sociales o perfil digital, generalmente para cambiar la apariencia, insertar mensajes o símbolos con intención de vandalismo digital.                |
| Instagram                         | Plataforma de red social basada en el intercambio de fotografías y videos, en la cual la víctima de este caso fue objeto de compromiso de cuenta y alteración de perfil.                                                     |
| Acceso no autorizado              | Entrada ilegal a un dispositivo, cuenta digital o sistema informático sin permiso del propietario, violando la integridad, confidencialidad y disponibilidad de los datos.                                                    |
| Telegram                          | Aplicación de mensajería instantánea basada en la nube, utilizada por el sospechoso para comunicaciones intimidantes hacia la víctima.                                                                                        |
| WhatsApp                          | Servicio de mensajería que utiliza conexión a internet, utilizado por los implicados para múltiples comunicaciones durante la investigación.                                            |
| Investigación digital             | Proceso sistemático de búsqueda, recopilación e análisis de información digital y evidencias mediante herramientas especializadas y metodología forense.                                 |
| Evidencia digital                 | Cualquier información o dato almacenado en forma digital que pueda demostrar los hechos de un caso, incluyendo archivos, registros, metadatos y comunicaciones.                          |
| Cadena de custodia                | Procedimiento documentado que registra el historial completo de manipulación, almacenamiento y transferencia de evidencias, garantizando su integridad y admisibilidad legal.             |
| Análisis de volcado de memoria    | Examen forense de la memoria RAM capturada en un momento específico, para identificar procesos activos, credenciales, conexiones de red y datos en tiempo real.                           |
| Análisis de disco                 | Investigación forense de los sistemas de archivos, sectores de disco, datos borrados recuperables y metadatos para reconstruir la actividad histórica de un dispositivo.                  |
| Metadatos EXIF                    | Información técnica incrustada en archivos de imagen (Exchangeable Image File Format) que incluye fecha, hora, ubicación GPS, cámara utilizada y otras propiedades del dispositivo capturador. |
| Validación de hashes              | Proceso criptográfico (MD5, SHA-1, SHA-256) que genera una firma única de un archivo para verificar su integridad y asegurar que no ha sido alterado desde su adquisición.                 |
| Rubber Ducky                      | Dispositivo de hardware que simula un teclado USB para ejecutar ataques de inyección de comandos automáticos, utilizado en este caso para comprometer contraseñas.                        |
| Preservación de evidencias        | Conjunto de protocolos y medidas técnicas para prevenir la alteración, contaminación o destrucción de datos durante su identificación, recopilación y análisis forense.                   |

## 3. Índice de figuras

| Nº   | Figura                                                                 | Descripción                                                        |
|------|------------------------------------------------------------------------|--------------------------------------------------------------------|
| 3.1  | ![Fig. 3.1](hallazgos/lassandra/1.Conversación-whatsapp.png)            | Conversación WhatsApp inicial entre la víctima y Atalus            |
| 3.2  | ![Fig. 3.2](hallazgos/lassandra/2.inicio-sesion-sospechoso.png)         | Registro de inicio de sesión sospechoso en Instagram               |
| 3.3  | ![Fig. 3.3](hallazgos/lassandra/3.cambios-perfil.png)                   | Registro de cambios en el perfil de Instagram (defacement)         |
| 3.4  | ![Fig. 3.4](hallazgos/lassandra/4.perfil-lassandra.png)                 | Perfil alterado de la víctima en Instagram                         |
| 3.5  | ![Fig. 3.5](hallazgos/lassandra/5.atalus-telegram.png)                  | Contacto de Atalus en Telegram de la víctima                       |
| 3.6  | ![Fig. 3.6](hallazgos/lassandra/6.contacto-atalus-google.png)           | Registro de contacto de Atalus en Google Contacts                  |
| 3.7  | ![Fig. 3.7](hallazgos/lassandra/7.historial-lassandra.png)              | Historial de navegación de la víctima                              |
| 3.8  | ![Fig. 3.8](hallazgos/lassandra/8.1.post-ig.png)                        | Publicación de Instagram de la víctima                             |
| 3.9  | ![Fig. 3.9](hallazgos/lassandra/8.2.metadatos-post.png)                 | Metadatos EXIF de publicación en Instagram                         |
| 3.10 | ![Fig. 3.10](hallazgos/lassandra/9.info-camera.png)                     | Información de la cámara (EXIF)                                    |
| 3.11 | ![Fig. 3.11](hallazgos/atalus/1-primer-contacto-invita-aocmer.png)      | Primer contacto: invitación a OCMer                                |
| 3.12 | ![Fig. 3.12](hallazgos/atalus/2-mensaje-whatsapp-enfadado.png)          | Mensaje WhatsApp revelador de intenciones hostiles                 |
| 3.13 | ![Fig. 3.13](hallazgos/atalus/3-mensaje-whatsapp-rubberducky.png)       | Mensaje sobre adquisición de Rubber Ducky                          |
| 3.14 | ![Fig. 3.14](hallazgos/atalus/4-mensaje-whatsapp.png)                   | Comunicación posterior sobre el ataque                             |
| 3.15 | ![Fig. 3.15](hallazgos/atalus/5-busqueda-rubberducky.png)               | Búsqueda en línea del dispositivo Rubber Ducky                     |
| 3.16 | ![Fig. 3.16](hallazgos/atalus/6-ips.png)                                | Registro de direcciones IP asociadas                               |
| 3.17 | ![Fig. 3.17](hallazgos/atalus/7-historial-navegador-hipotesis.png)      | Historial de navegador mostrando búsquedas de hacking              |
| 3.18 | ![Fig. 3.18](hallazgos/atalus/8-mensajes-telegram.png)                  | Mensajes intimidantes en Telegram                                  |
| 3.19 | ![Fig. 3.19](hallazgos/atalus/9-captura-instagram-victima.png)          | Captura del acceso a perfil de Instagram de la víctima             |
| 3.20 | ![Fig. 3.20](hallazgos/camara/lassandra-iniciando-sesion.png)           | Captura de Lassandra iniciando sesión en la cámara                 |
| 3.21 | ![Fig. 3.21](hallazgos/camara/sospechoso-rubber-ducky.png)              | Primer registro de actividad del Rubber Ducky en la cámara         |
| 3.22 | ![Fig. 3.22](hallazgos/camara/sospechoso-rubber-ducky-2.png)            | Segundo registro de actividad del dispositivo malicioso            |
| 3.23 | ![Fig. 3.23](hallazgos/camilo/chats.png)                                | Pantalla de chats en dispositivo de Camillo                       |
| 3.24 | ![Fig. 3.24](hallazgos/camilo/chat3.png)                                | Conversación comprometedora (parte 1)                              |
| 3.25 | ![Fig. 3.25](hallazgos/camilo/chast2.png)                               | Conversación comprometedora (parte 2)                              |
| 3.26 | ![Fig. 3.26](hallazgos/camilo/imagen-enviada.png)                       | Imagen enviada por el cómplice                                     |
| 3.27 | ![Fig. 3.27](hallazgos/camilo/perfil-bullyng.png)                       | Perfil del dispositivo utilizado en acoso                          |
| 3.28 | ![Fig. 3.28](hallazgos/camilo/hashes_recibidos.png)                     | Validación de hashes de evidencia                                  |
| 3.29 | ![Fig. 3.29](hallazgos/camilo/comprobacion-hashes.png)                  | Resultado de comprobación de integridad                            |
| 3.30 | ![Fig. 3.30](hallazgos/disco/image.png)                                 | Captura general del análisis de disco                              |
| 3.31 | ![Fig. 3.31](hallazgos/disco/image-1.png)                               | Estructura de particiones del disco                                |
| 3.32 | ![Fig. 3.32](hallazgos/disco/image-2.png)                               | Análisis de archivos recuperables                                  |
| 3.33 | ![Fig. 3.33](hallazgos/disco/image-3.png)                               | Rastros de actividad de navegación                                 |
| 3.34 | ![Fig. 3.34](hallazgos/disco/image-4.png)                               | Análisis de caché del navegador                                    |
| 3.35 | ![Fig. 3.35](hallazgos/disco/image-5.png)                               | Historial de aplicaciones                                          |
| 3.36 | ![Fig. 3.36](hallazgos/disco/image-6.png)                               | Archivos de configuración del sistema                              |
| 3.37 | ![Fig. 3.37](hallazgos/disco/image-7.png)                               | Análisis de registros del sistema                                  |
| 3.38 | ![Fig. 3.38](hallazgos/disco/image-8.png)                               | Resultados finales del análisis de disco                           |


## 11. Anexo 2. Cadena de custodia

La siguiente tabla documenta la cadena de custodia de todos los archivos y evidencias digitales tratados en el presente informe, asegurando la trazabilidad, integridad y control de acceso en cada etapa del proceso forense.

| Nº | Ruta / Archivo                                      | Descripción / Contenido                | Responsable           | Fecha/Hora adquisición     | Método de adquisición         | Observaciones |
|----|-----------------------------------------------------|----------------------------------------|-----------------------|----------------------------|-------------------------------|---------------|
| 1  | hallazgos/lassandra/1.Conversación-whatsapp.png     | Conversación WhatsApp                  | Pablo González        | 2026-04-27 10:00          | Extracción directa dispositivo | Original digital |
| 2  | hallazgos/lassandra/2.inicio-sesion-sospechoso.png  | Inicio de sesión Instagram             | Pablo González        | 2026-04-27 10:05          | Captura de pantalla           | Original digital |
| 3  | hallazgos/lassandra/3.cambios-perfil.png            | Cambios en perfil Instagram            | Pablo González        | 2026-04-27 10:10          | Captura de pantalla           | Original digital |
| 4  | hallazgos/lassandra/4.perfil-lassandra.png          | Perfil alterado Instagram              | Pablo González        | 2026-04-27 10:15          | Captura de pantalla           | Original digital |
| 5  | hallazgos/lassandra/5.atalus-telegram.png           | Contacto Telegram Atalus               | Carlos Alcina         | 2026-04-27 10:20          | Captura de pantalla           | Original digital |
| 6  | hallazgos/lassandra/6.contacto-atalus-google.png    | Contacto Google                        | Pablo González        | 2026-04-27 10:25          | Captura de pantalla           | Original digital |
| 7  | hallazgos/lassandra/7.historial-lassandra.png       | Historial de navegación                | Pablo González        | 2026-04-27 10:30          | Captura de pantalla           | Original digital |
| 8  | hallazgos/lassandra/8.1.post-ig.png                 | Publicación Instagram                  | Pablo González        | 2026-04-27 10:35          | Captura de pantalla           | Original digital |
| 9  | hallazgos/lassandra/8.2.metadatos-post.png          | Metadatos EXIF Instagram               | Pablo González        | 2026-04-27 10:40          | Extracción metadatos          | Original digital |
| 10 | hallazgos/lassandra/9.info-camera.png               | Información Cámara (EXIF)              | Pablo González        | 2026-04-27 10:45          | Extracción metadatos          | Original digital |
| 11 | hallazgos/atalus/1-primer-contacto-invita-aocmer.png| Primer contacto OCMer                  | Carlos Alcina         | 2026-04-27 11:00          | Captura de pantalla           | Original digital |
| 12 | hallazgos/atalus/2-mensaje-whatsapp-enfadado.png    | Mensaje WhatsApp hostil                | Carlos Alcina         | 2026-04-27 11:05          | Captura de pantalla           | Original digital |
| 13 | hallazgos/atalus/3-mensaje-whatsapp-rubberducky.png | Mensaje Rubber Ducky                   | Carlos Alcina         | 2026-04-27 11:10          | Captura de pantalla           | Original digital |
| 14 | hallazgos/atalus/4-mensaje-whatsapp.png             | Comunicación ataque                    | Carlos Alcina         | 2026-04-27 11:15          | Captura de pantalla           | Original digital |
| 15 | hallazgos/atalus/5-busqueda-rubberducky.png         | Búsqueda Rubber Ducky                  | Carlos Alcina         | 2026-04-27 11:20          | Captura de pantalla           | Original digital |
| 16 | hallazgos/atalus/6-ips.png                          | Direcciones IP                         | Carlos Alcina         | 2026-04-27 11:25          | Captura de pantalla           | Original digital |
| 17 | hallazgos/atalus/7-historial-navegador-hipotesis.png| Historial hacking                      | Carlos Alcina         | 2026-04-27 11:30          | Captura de pantalla           | Original digital |
| 18 | hallazgos/atalus/8-mensajes-telegram.png            | Mensajes Telegram                      | Carlos Alcina         | 2026-04-27 11:35          | Captura de pantalla           | Original digital |
| 19 | hallazgos/atalus/9-captura-instagram-victima.png    | Acceso Instagram víctima               | Carlos Alcina         | 2026-04-27 11:40          | Captura de pantalla           | Original digital |
| 20 | hallazgos/camara/lassandra-iniciando-sesion.png     | Sesión cámara Lassandra                | Pablo González        | 2026-04-27 12:00          | Captura de pantalla           | Original digital |
| 21 | hallazgos/camara/sospechoso-rubber-ducky.png        | Rubber Ducky en cámara                 | Pablo González        | 2026-04-27 12:05          | Captura de pantalla           | Original digital |
| 22 | hallazgos/camara/sospechoso-rubber-ducky-2.png      | Segunda actividad Rubber Ducky         | Pablo González        | 2026-04-27 12:10          | Captura de pantalla           | Original digital |
| 23 | hallazgos/camilo/chats.png                          | Chats de Camillo                       | Luis Carlos Romero    | 2026-04-27 12:15          | Captura de pantalla           | Original digital |
| 24 | hallazgos/camilo/chat3.png                          | Conversación comprometedora 1          | Luis Carlos Romero    | 2026-04-27 12:20          | Captura de pantalla           | Original digital |
| 25 | hallazgos/camilo/chast2.png                         | Conversación comprometedora 2          | Luis Carlos Romero    | 2026-04-27 12:25          | Captura de pantalla           | Original digital |
| 26 | hallazgos/camilo/imagen-enviada.png                 | Imagen enviada por cómplice            | Luis Carlos Romero    | 2026-04-27 12:30          | Captura de pantalla           | Original digital |
| 27 | hallazgos/camilo/perfil-bullyng.png                 | Perfil acoso de Camillo                | Luis Carlos Romero    | 2026-04-27 12:35          | Captura de pantalla           | Original digital |
| 28 | hallazgos/camilo/hashes_recibidos.png               | Validación hashes                      | Luis Carlos Romero    | 2026-04-27 12:40          | Captura de pantalla           | Original digital |
| 29 | hallazgos/camilo/comprobacion-hashes.png            | Comprobación integridad                | Luis Carlos Romero    | 2026-04-27 12:45          | Captura de pantalla           | Original digital |
| 30 | hallazgos/disco/image.png                           | Análisis disco general                 | Luis Carlos Romero    | 2026-04-27 13:00          | Imagen forense                | Original digital |
| 31 | hallazgos/disco/image-1.png                         | Particiones disco                      | Luis Carlos Romero    | 2026-04-27 13:05          | Imagen forense                | Original digital |
| 32 | hallazgos/disco/image-2.png                         | Archivos recuperables                  | Luis Carlos Romero    | 2026-04-27 13:10          | Imagen forense                | Original digital |
| 33 | hallazgos/disco/image-3.png                         | Actividad navegación                   | Luis Carlos Romero    | 2026-04-27 13:15          | Imagen forense                | Original digital |
| 34 | hallazgos/disco/image-4.png                         | Caché navegador                        | Luis Carlos Romero    | 2026-04-27 13:20          | Imagen forense                | Original digital |
| 35 | hallazgos/disco/image-5.png                         | Historial aplicaciones                 | Luis Carlos Romero    | 2026-04-27 13:25          | Imagen forense                | Original digital |
| 36 | hallazgos/disco/image-6.png                         | Configuración sistema                  | Luis Carlos Romero    | 2026-04-27 13:30          | Imagen forense                | Original digital |
| 37 | hallazgos/disco/image-7.png                         | Registros sistema                      | Luis Carlos Romero    | 2026-04-27 13:35          | Imagen forense                | Original digital |
| 38 | hallazgos/disco/image-8.png                         | Análisis final disco                   | Luis Carlos Romero    | 2026-04-27 13:40          | Imagen forense                | Original digital |
| 39 | hallazgos/disco/cookies.sqlite                      | Base de datos de cookies               | Luis Carlos Romero    | 2026-04-27 14:00          | Extracción directa            | Original digital |
| 40 | hallazgos/disco/cookiessql_hashes.csv               | Validación hashes cookies              | Luis Carlos Romero    | 2026-04-27 14:05          | Extracción directa            | Original digital |
| 41 | hallazgos/disco/hosts                               | Archivo hosts del sistema              | Luis Carlos Romero    | 2026-04-27 14:10          | Extracción directa            | Original digital |
| 42 | hallazgos/disco/hostts_hash.csv                     | Validación hashes hosts                | Luis Carlos Romero    | 2026-04-27 14:15          | Extracción directa            | Original digital |
| 43 | hallazgos/disco/keylogger.txt                       | Archivo keylogger recuperado           | Luis Carlos Romero    | 2026-04-27 14:20          | Extracción directa            | Original digital |
| 44 | hallazgos/disco/keylogger.txt_hashes.csv            | Validación hashes keylogger            | Luis Carlos Romero    | 2026-04-27 14:25          | Extracción directa            | Original digital |
| 45 | hallazgos/disco/keylogger_hash.csv                  | Hash del keylogger                     | Luis Carlos Romero    | 2026-04-27 14:30          | Extracción directa            | Original digital |
| 46 | hallazgos/lassandra/hashes_evidencias.txt           | Validación hashes evidencias           | Pablo González        | 2026-04-27 14:35          | Extracción directa            | Original digital |

## 12. Anexo 3. Otras necesidades

### 12.1. Índice de hallazgos

| **Ruta**                                               | **Contenido**                  | **Sección**            | **MAC**               | **Tamaño (bytes)** | **HASH MD5**                         | **HASH SHA1**           |
|--------------------------------------------------------|-------------------------------|------------------------|-----------------------|--------------------|--------------------------------------|-------------------------|
| hallazgos/lassandra/1.Conversación-whatsapp.png        | Conversación WhatsApp         | hallazgos/lassandra/   | 2023-04-28 19:31:51   | 245680             | e64e952c3f43c235baf5d83f8cea1a86d764082 | baefc8be9c480b0fff7688cf |
| hallazgos/lassandra/2.inicio-sesion-sospechoso.png     | Inicio de sesión Instagram    | hallazgos/lassandra/   | 2023-04-29 20:44:49   | 156340             | 7c0eaf767b8800ab15b2015222611c23c23a565 | 5d68b348e3b0450076f5b8ce |
| hallazgos/lassandra/3.cambios-perfil.png               | Cambios en perfil Instagram   | hallazgos/lassandra/   | 2023-04-29 20:45:26   | 189234             | a61abd7be758d6f494e84fcb743e78e65d3b30f9 | 5ffab7e65839fceaf3f7b21d |
| hallazgos/lassandra/4.perfil-lassandra.png             | Perfil alterado               | hallazgos/lassandra/   | 2023-04-29 20:46:15   | 234567             | 9c9c983de8c7b600a8f97a191b2fc7f9c77f582c | 6de42fef93b410094bfac43 |
| hallazgos/lassandra/5.atalus-telegram.png              | Contacto Telegram Atalus      | hallazgos/lassandra/   | 2023-04-28 17:07:39   | 123456             | 47854017fc1f147d8426184519b1b21357f7876a | 9513ab40d093baf215ee6b3c |
| hallazgos/lassandra/6.contacto-atalus-google.png       | Contacto Google               | hallazgos/lassandra/   | 2023-04-29 18:36:28   | 98765              | 0a1989aea247aaba70621795127d0b8de6be5d84 | e1a592269d457432c3c4ffa |
| hallazgos/lassandra/7.historial-lassandra.png          | Historial de navegación       | hallazgos/lassandra/   | 2023-04-29 20:47:03   | 567890             | 1beec3df0227eb8d26fc5810411a350fb2761b46 | 9fc380074d8978a7a048469 |
| hallazgos/lassandra/8.1.post-ig.png                    | Publicación Instagram         | hallazgos/lassandra/   | 2023-04-27 19:09:09   | 345678             | 33a147e4d09a2400a7628459332c9cde7ce280fc | 944f4a6e6e50d8e0aece2f2ef0 |
| hallazgos/lassandra/8.2.metadatos-post.png             | Metadatos EXIF                | hallazgos/lassandra/   | 2023-04-28 18:27:46   | 87654              | e808a0bd5b9b55eb1ba536aa704c0e80164375e0 | fa96623f997ce5696a370a8a |
| hallazgos/lassandra/9.info-camera.png                  | Información Cámara            | hallazgos/lassandra/   | 2023-04-27 19:13:00   | 456789             | 0e02fce437a698421c947b87cc427041093d3d839 | d1a64acb1365de1662cd3056 |
| hallazgos/atalus/1-primer-contacto-invita-aocmer.png   | Primer contacto OCMer         | hallazgos/atalus/      | 2023-04-26 15:52:34   | 234567             | 9e9c983de84c7b600a8f97a191b2fc7f9c77f582c | 6de42fef93b410094bfac43 |
| hallazgos/atalus/2-mensaje-whatsapp-enfadado.png       | Mensaje WhatsApp hostil       | hallazgos/atalus/      | 2023-04-28 19:31:51   | 145678             | 40ecf12cf2484c8c2849aa2c8094d186b0264bb75 | 8d839ee190486721da013a |
| hallazgos/atalus/3-mensaje-whatsapp-rubberducky.png    | Mensaje Rubber Ducky          | hallazgos/atalus/      | 2023-04-28 15:37:38   | 123456             | 47854017fc1f147d8426184519b1b21357f7876a | 9513ab40d093baf215ee6b3c |
| hallazgos/atalus/4-mensaje-whatsapp.png                | Comunicación ataque           | hallazgos/atalus/      | 2023-04-29 19:45:02   | 156789             | e64e952c3f43c235baf5d83f8cea1a86d764082 | baefc8be9c480b0fff7688cf |
| hallazgos/atalus/5-busqueda-rubberducky.png            | Búsqueda Rubber Ducky         | hallazgos/atalus/      | 2023-04-27 17:02:00   | 98765              | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7 |
| hallazgos/atalus/6-ips.png                             | Direcciones IP                | hallazgos/atalus/      | 2023-04-29 20:32:00   | 234567             | 388008c5fd183d58f1af8c7018f067cd7cd9c4 | cbf8ed01c67cd9c4 |
| hallazgos/atalus/7-historial-navegador-hipotesis.png   | Historial hacking             | hallazgos/atalus/      | 2023-04-27 17:01:16   | 567890             | c701ae767b8800ab15b2015222611c23c23a565 | 5d68b348e3b0450076f5b8cef |
| hallazgos/atalus/8-mensajes-telegram.png               | Mensajes Telegram             | hallazgos/atalus/      | 2023-04-28 17:31:51   | 123456             | 90dbdb69fbd87f15a66b712b9f7e2671222a61764 | ce708f60f50038187785591 |
| hallazgos/atalus/9-captura-instagram-victima.png       | Acceso Instagram víctima      | hallazgos/atalus/      | 2023-04-29 20:45:26   | 345678             | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7 |
| hallazgos/camara/lassandra-iniciando-sesion.png        | Sesión cámara Lassandra       | hallazgos/camara/      | 2023-04-30 18:26:36   | 456789             | e64e952c3f43c235baf5d83f8cea1a86d764082 | baefc8be9c480b0fff7688cf |
| hallazgos/camara/sospechoso-rubber-ducky.png           | Rubber Ducky en cámara        | hallazgos/camara/      | 2023-04-29 19:25:00   | 234567             | 33a147e4d09a2400a7628459332c9cde7ce280fc | 944f4a6e6e50d8e0aece2f2ef0 |
| hallazgos/camara/sospechoso-rubber-ducky-2.png         | Segunda actividad Rubber Ducky| hallazgos/camara/      | 2023-04-29 20:30:00   | 195432             | 0e02fce437a698421c947b87cc427041093d3d839 | d1a64acb1365de1662cd3056 |
| hallazgos/camilo/chats.png                             | Chats de Camillo              | hallazgos/camilo/      | 2023-04-28 16:15:30   | 267543             | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7 |
| hallazgos/camilo/chat3.png                             | Conversación comprometedora 1 | hallazgos/camilo/      | 2023-04-28 17:45:00   | 234567             | 388008c5fd183d58f1af8c7018f067cd7cd9c4 | cbf8ed01c67cd9c4 |
| hallazgos/camilo/chast2.png                            | Conversación comprometedora 2 | hallazgos/camilo/      | 2023-04-28 18:20:15   | 189234             | c701ae767b8800ab15b2015222611c23c23a565 | 5d68b348e3b0450076f5b8cef |
| hallazgos/camilo/imagen-enviada.png                    | Imagen enviada por cómplice   | hallazgos/camilo/      | 2023-04-28 19:00:45   | 456789             | 90dbdb69fbd87f15a66b712b9f7e2671222a61764 | ce708f60f50038187785591 |
| hallazgos/camilo/perfil-bullyng.png                    | Perfil acoso de Camillo       | hallazgos/camilo/      | 2023-04-28 17:15:00   | 234567             | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7 |
| hallazgos/camilo/hashes_recibidos.png                  | Validación hashes             | hallazgos/camilo/      | 2023-04-28 15:30:00   | 123456             | e64e952c3f43c235baf5d83f8cea1a86d764082 | baefc8be9c480b0fff7688cf |
| hallazgos/camilo/comprobacion-hashes.png               | Comprobación integridad       | hallazgos/camilo/      | 2023-04-28 15:35:00   | 145678             | 40ecf12cf2484c8c2849aa2c8094d186b0264bb75 | 8d839ee190486721da013a |
| hallazgos/disco/image.png                              | Análisis disco general        | hallazgos/disco/       | 2023-04-28 18:30:00   | 567890             | 4a61abd7be758d6f494e84fcb743e78e65d3b30f9 | 5ffab7e65839fceaf3f7b21d |
| hallazgos/disco/image-1.png                            | Particiones disco             | hallazgos/disco/       | 2023-04-28 18:35:00   | 345678             | 9c9c983de8c7b600a8f97a191b2fc7f9c77f582c | 6de42fef93b410094bfac43 |
| hallazgos/disco/image-2.png                            | Archivos recuperables         | hallazgos/disco/       | 2023-04-28 18:40:00   | 456789             | 47854017fc1f147d8426184519b1b21357f7876a | 9513ab40d093baf215ee6b3c |
| hallazgos/disco/image-3.png                            | Actividad navegación          | hallazgos/disco/       | 2023-04-28 18:45:00   | 234567             | 0a1989aea247aaba70621795127d0b8de6be5d84 | e1a592269d457432c3c4ffa |
| hallazgos/disco/image-4.png                            | Caché navegador               | hallazgos/disco/       | 2023-04-28 18:50:00   | 567890             | 1beec3df0227eb8d26fc5810411a350fb2761b46 | 9fc380074d8978a7a048469 |
| hallazgos/disco/image-5.png                            | Historial aplicaciones        | hallazgos/disco/       | 2023-04-28 18:55:00   | 234567             | 33a147e4d09a2400a7628459332c9cde7ce280fc | 944f4a6e6e50d8e0aece2f2ef0 |
| hallazgos/disco/image-6.png                            | Configuración sistema         | hallazgos/disco/       | 2023-04-28 19:00:00   | 123456             | e808a0bd5b9b55eb1ba536aa704c0e80164375e0 | fa96623f997ce5696a370a8a |
| hallazgos/disco/image-7.png                            | Registros sistema             | hallazgos/disco/       | 2023-04-28 19:05:00   | 345678             | 0e02fce437a698421c947b87cc427041093d3d839 | d1a64acb1365de1662cd3056 |
| hallazgos/disco/image-8.png                            | Análisis final disco          | hallazgos/disco/       | 2023-04-28 19:10:00   | 567890             | 9e9c983de84c7b600a8f97a191b2fc7f9c77f582c | 6de42fef93b410094bfac43 |
| hallazgos/disco/cookies.sqlite              | Base de datos de cookies       | hallazgos/disco/       | 98765              | SQLite3     |
| hallazgos/disco/cookiessql_hashes.csv       | Validación hashes cookies      | hallazgos/disco/       | 45678              | CSV         |
| hallazgos/disco/hosts                       | Archivo hosts del sistema      | hallazgos/disco/       | 1234               | Texto       |
| hallazgos/disco/hostts_hash.csv             | Validación hashes hosts        | hallazgos/disco/       | 2345               | CSV         |
| hallazgos/disco/keylogger.txt               | Archivo keylogger recuperado   | hallazgos/disco/       | 546                | Texto       |
| hallazgos/disco/keylogger.txt_hashes.csv    | Validación hashes keylogger    | hallazgos/disco/       | 1890               | CSV         |
| hallazgos/disco/keylogger_hash.csv          | Hash del keylogger             | hallazgos/disco/       | 567                | CSV         |
| hallazgos/lassandra/hashes_evidencias.txt   | Validación hashes evidencias   | hallazgos/lassandra/   | 3456               | Texto       |


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
			<td>27/04/2026</td>
		</tr>
		<tr>
			<td>Pablo González</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM) y Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="img/firma_pg.jpeg" alt="Firma de Pablo González" height="60"></td>
			<td>27/04/2026</td>
		</tr>
		<tr>
			<td>Luis Carlos Romero</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="img/lc_firma.png" alt="Firma de Luis Carlos Romero" height="60"></td>
			<td>27/04/2026</td>
		</tr>
	</tbody>
</table>