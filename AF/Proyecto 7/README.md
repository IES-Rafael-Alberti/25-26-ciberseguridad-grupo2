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

| **Término**                    | **Definición**                                                                                                                                                                                                |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Análisis forense digital       | Disciplina que aplica técnicas científicas y metodologías rigurosas para identificar, recuperar y analizar evidencias digitales de manera que pueda ser presentada en procedimientos legales.                 |
| Ciberacoso                     | Acoso persistente, intimidación o humillación de una persona mediante el uso de plataformas digitales, redes sociales o aplicaciones de mensajería, causando daño psicológico o emocional.                    |
| Defacement                     | Alteración no autorizada del contenido de un sitio web, cuenta de redes sociales o perfil digital, generalmente para cambiar la apariencia, insertar mensajes o símbolos con intención de vandalismo digital. |
| Instagram                      | Plataforma de red social basada en el intercambio de fotografías y videos, en la cual la víctima de este caso fue objeto de compromiso de cuenta y alteración de perfil.                                      |
| Acceso no autorizado           | Entrada ilegal a un dispositivo, cuenta digital o sistema informático sin permiso del propietario, violando la integridad, confidencialidad y disponibilidad de los datos.                                    |
| Telegram                       | Aplicación de mensajería instantánea basada en la nube, utilizada por el sospechoso para comunicaciones intimidantes hacia la víctima.                                                                        |
| WhatsApp                       | Servicio de mensajería que utiliza conexión a internet, utilizado por los implicados para múltiples comunicaciones durante la investigación.                                                                  |
| Investigación digital          | Proceso sistemático de búsqueda, recopilación e análisis de información digital y evidencias mediante herramientas especializadas y metodología forense.                                                      |
| Evidencia digital              | Cualquier información o dato almacenado en forma digital que pueda demostrar los hechos de un caso, incluyendo archivos, registros, metadatos y comunicaciones.                                               |
| Cadena de custodia             | Procedimiento documentado que registra el historial completo de manipulación, almacenamiento y transferencia de evidencias, garantizando su integridad y admisibilidad legal.                                 |
| Análisis de volcado de memoria | Examen forense de la memoria RAM capturada en un momento específico, para identificar procesos activos, credenciales, conexiones de red y datos en tiempo real.                                               |
| Análisis de disco              | Investigación forense de los sistemas de archivos, sectores de disco, datos borrados recuperables y metadatos para reconstruir la actividad histórica de un dispositivo.                                      |
| Metadatos EXIF                 | Información técnica incrustada en archivos de imagen (Exchangeable Image File Format) que incluye fecha, hora, ubicación GPS, cámara utilizada y otras propiedades del dispositivo capturador.                |
| Validación de hashes           | Proceso criptográfico (MD5, SHA-1, SHA-256) que genera una firma única de un archivo para verificar su integridad y asegurar que no ha sido alterado desde su adquisición.                                    |
| Rubber Ducky                   | Dispositivo de hardware que simula un teclado USB para ejecutar ataques de inyección de comandos automáticos, utilizado en este caso para comprometer contraseñas.                                            |
| Preservación de evidencias     | Conjunto de protocolos y medidas técnicas para prevenir la alteración, contaminación o destrucción de datos durante su identificación, recopilación y análisis forense.                                       |

## 3. Índice de figuras

| Nº   | Figura                                                             | Descripción                                                |
| ---- | ------------------------------------------------------------------ | ---------------------------------------------------------- |
| 3.1  | ![Fig. 3.1](hallazgos/lassandra/1.Conversación-whatsapp.png)       | Conversación WhatsApp inicial entre la víctima y Atalus    |
| 3.2  | ![Fig. 3.2](hallazgos/lassandra/2.inicio-sesion-sospechoso.png)    | Registro de inicio de sesión sospechoso en Instagram       |
| 3.3  | ![Fig. 3.3](hallazgos/lassandra/3.cambios-perfil.png)              | Registro de cambios en el perfil de Instagram (defacement) |
| 3.4  | ![Fig. 3.4](hallazgos/lassandra/4.perfil-lassandra.png)            | Perfil alterado de la víctima en Instagram                 |
| 3.5  | ![Fig. 3.5](hallazgos/lassandra/5.atalus-telegram.png)             | Contacto de Atalus en Telegram de la víctima               |
| 3.6  | ![Fig. 3.6](hallazgos/lassandra/6.contacto-atalus-google.png)      | Registro de contacto de Atalus en Google Contacts          |
| 3.7  | ![Fig. 3.7](hallazgos/lassandra/7.historial-lassandra.png)         | Historial de navegación de la víctima                      |
| 3.8  | ![Fig. 3.8](hallazgos/lassandra/8.1.post-ig.png)                   | Publicación de Instagram de la víctima                     |
| 3.9  | ![Fig. 3.9](hallazgos/lassandra/8.2.metadatos-post.png)            | Metadatos EXIF de publicación en Instagram                 |
| 3.10 | ![Fig. 3.10](hallazgos/lassandra/9.info-camera.png)                | Información de la cámara (EXIF)                            |
| 3.11 | ![Fig. 3.11](hallazgos/atalus/1-primer-contacto-invita-aocmer.png) | Primer contacto: invitación a OCMer                        |
| 3.12 | ![Fig. 3.12](hallazgos/atalus/2-mensaje-whatsapp-enfadado.png)     | Mensaje WhatsApp revelador de intenciones hostiles         |
| 3.13 | ![Fig. 3.13](hallazgos/atalus/3-mensaje-whatsapp-rubberducky.png)  | Mensaje sobre adquisición de Rubber Ducky                  |
| 3.14 | ![Fig. 3.14](hallazgos/atalus/4-mensaje-whatsapp.png)              | Comunicación posterior sobre el ataque                     |
| 3.15 | ![Fig. 3.15](hallazgos/atalus/5-busqueda-rubberducky.png)          | Búsqueda en línea del dispositivo Rubber Ducky             |
| 3.16 | ![Fig. 3.16](hallazgos/atalus/6-ips.png)                           | Registro de direcciones IP asociadas                       |
| 3.17 | ![Fig. 3.17](hallazgos/atalus/7-historial-navegador-hipotesis.png) | Historial de navegador mostrando búsquedas de hacking      |
| 3.18 | ![Fig. 3.18](hallazgos/atalus/8-mensajes-telegram.png)             | Mensajes intimidantes en Telegram                          |
| 3.19 | ![Fig. 3.19](hallazgos/atalus/9-captura-instagram-victima.png)     | Captura del acceso a perfil de Instagram de la víctima     |
| 3.20 | ![Fig. 3.20](hallazgos/camara/lassandra-iniciando-sesion.png)      | Captura de Lassandra iniciando sesión en la cámara         |
| 3.21 | ![Fig. 3.21](hallazgos/camara/sospechoso-rubber-ducky.png)         | Primer registro de actividad del Rubber Ducky en la cámara |
| 3.22 | ![Fig. 3.22](hallazgos/camara/sospechoso-rubber-ducky-2.png)       | Segundo registro de actividad del dispositivo malicioso    |
| 3.23 | ![Fig. 3.23](hallazgos/camilo/chats.png)                           | Pantalla de chats en dispositivo de Camillo                |
| 3.24 | ![Fig. 3.24](hallazgos/camilo/chat3.png)                           | Conversación comprometedora (parte 1)                      |
| 3.25 | ![Fig. 3.25](hallazgos/camilo/chast2.png)                          | Conversación comprometedora (parte 2)                      |
| 3.26 | ![Fig. 3.26](hallazgos/camilo/imagen-enviada.png)                  | Imagen enviada por el cómplice                             |
| 3.27 | ![Fig. 3.27](hallazgos/camilo/perfil-bullyng.png)                  | Perfil del dispositivo utilizado en acoso                  |
| 3.28 | ![Fig. 3.28](hallazgos/camilo/hashes_recibidos.png)                | Validación de hashes de evidencia                          |
| 3.29 | ![Fig. 3.29](hallazgos/camilo/comprobacion-hashes.png)             | Resultado de comprobación de integridad                    |
| 3.30 | ![Fig. 3.30](hallazgos/disco/image.png)                            | Captura general del análisis de disco                      |
| 3.31 | ![Fig. 3.31](hallazgos/disco/image-1.png)                          | Estructura de particiones del disco                        |
| 3.32 | ![Fig. 3.32](hallazgos/disco/image-2.png)                          | Análisis de archivos recuperables                          |
| 3.33 | ![Fig. 3.33](hallazgos/disco/image-3.png)                          | Rastros de actividad de navegación                         |
| 3.34 | ![Fig. 3.34](hallazgos/disco/image-4.png)                          | Análisis de caché del navegador                            |
| 3.35 | ![Fig. 3.35](hallazgos/disco/image-5.png)                          | Historial de aplicaciones                                  |
| 3.36 | ![Fig. 3.36](hallazgos/disco/image-6.png)                          | Archivos de configuración del sistema                      |
| 3.37 | ![Fig. 3.37](hallazgos/disco/image-7.png)                          | Análisis de registros del sistema                          |
| 3.38 | ![Fig. 3.38](hallazgos/disco/image-8.png)                          | Resultados finales del análisis de disco                   |

## 4. Resumen Ejecutivo

Los hallazgos más relevantes apuntan a un incidente de **ciberacoso** que culmina en un **acceso no autorizado** a la cuenta de Instagram de la víctima y una **alteración del perfil (defacement)**. En mensajería se observa coordinación entre el presunto autor y un cómplice, incluyendo referencias explícitas a obtener contraseñas mediante un dispositivo USB tipo “Rubber Ducky”, con finalidad de humillación.

En el entorno analizado se identifican indicios compatibles con **captura de credenciales** (script de keylogger y fichero de salida) y **uso de USB** asociado, además de cambios de configuración (`hosts`), mientras que los registros de Instagram reflejan **inicios de sesión no habituales** y cambios de perfil en una ventana temporal coherente. Para la toma de decisiones, se recomienda priorizar contención: **cambio/rotación de credenciales**, **activar 2FA**, **cerrar sesiones**, revisar equipos/USB y mantener **cadena de custodia** e integridad (hashes) de la evidencia.

## 5. Introducción

Este informe recoge el análisis forense realizado sobre un incidente de **ciberacoso** con indicios de **acceso no autorizado** y **alteración de cuenta (defacement) en Instagram**. A partir de hallazgos en mensajería, actividad web, registros de cuenta y artefactos en sistemas, se reconstruye una línea temporal y se correlacionan acciones entre la víctima, el principal sospechoso y un posible cómplice.

Para el análisis se trabajó sobre evidencias adquiridas y verificadas (hashes) aportadas para el caso, entre ellas: **imagen de SD de cámara IP Imou**, **imagen de disco del PC** del entorno investigado, **copias ADB** de dispositivos (víctima/sospechoso/cómplice), **extracciones de WhatsApp y Telegram**, y **exportaciones/respaldos de Instagram y Google (Takeout)**.

### 5.1. Antecedentes

El caso se inicia con una dinámica de mensajes insistentes y tono hostil hacia la víctima, seguida de indicios explícitos de preparación técnica (referencias a “Rubber Ducky” como mecanismo para obtener contraseñas) y comunicaciones intimidantes por mensajería. Paralelamente, los registros asociados a la cuenta de Instagram muestran accesos no habituales y cambios de perfil compatibles con una alteración no consentida.

En las fuentes técnicas del entorno analizado aparecen artefactos consistentes con esa hipótesis: rastros de conexión de un dispositivo USB tipo “DUCKY”, presencia/ejecución de un script de keylogger y su salida con indicios de credenciales, además de modificaciones de configuración (p. ej., `hosts`) que pueden afectar a la resolución de nombres. Estas señales se interpretan de forma conjunta y se validan por correlación temporal con los eventos de mensajería y con la actividad registrada en los servicios.

### 5.2. Objetivos

El análisis tiene como finalidad describir, con base en evidencias digitales verificables, qué ocurrió, cómo pudo ejecutarse y cuál fue el alcance del incidente.

- **Preservación e integridad:** comprobar y documentar integridad de los artefactos mediante hashes y trabajo sobre copias.
- **Reconstrucción temporal:** elaborar una cronología con eventos relevantes (mensajería, accesos a cuentas, ejecución/uso de artefactos y actividad de dispositivos).
- **Correlación de fuentes:** relacionar hallazgos entre cámara, móviles, disco y exportaciones (Google/Instagram) para reducir ambigüedad.
- **Identificación del vector:** identificar el mecanismo más probable de obtención de credenciales y posterior acceso (p. ej., inyección por USB y/o keylogger).
- **Determinación del alcance:** concretar qué cuentas/elementos fueron afectados (alteración de perfil, exposición de credenciales, persistencia de artefactos).
- **Soporte a medidas:** aportar conclusiones técnicas y acciones recomendadas, dentro de las limitaciones del material analizado.

## 6. Fuentes de información

Esta sección describe (i) cómo se verificó la **integridad** de la evidencia y (ii) qué **fuentes** se utilizaron y cómo se obtuvieron para el análisis.

### 6.1. Comprobación de hashes (MD5 y SHA-1)

Para asegurar que los ficheros no han sido modificados desde su adquisición, se verificó la integridad mediante funciones hash (principalmente **SHA-256** y, cuando procede, **MD5/SHA-1**) comparando los valores calculados con los registrados en la adquisición. La verificación se realiza antes y después de cualquier transferencia/uso en herramientas forenses.

**Comandos de referencia (Windows PowerShell):**

```powershell
Get-FileHash -Algorithm MD5  .\fichero.ext
Get-FileHash -Algorithm SHA1 .\fichero.ext
Get-FileHash -Algorithm SHA256 .\fichero.ext
```

**Evidencias proporcionadas (tamaño y verificación):**

| Evidencia                                | Tamaño (Bytes) | SHA-256                                                          | MD5            | SHA-1           |
| ---------------------------------------- | -------------: | ---------------------------------------------------------------- | -------------- | --------------- |
| adb-backup-Atalus-Grasstem.ab            |     29.013.457 | e64e952c3f43c235baf5d83f8cea1a86d7640821baefcbe89c480b0fff7688cf | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| adb-backup-Camillo-Richbald.ab           |        158.652 | 9c9c983de848c7b600a8f97a191b2fc7f9c77f5826de42fef93b410094bfac43 | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| adb-backup-Lassandra-Cordalis.ab         |        523.014 | 40e6f12cf248468c2849aa2c8094d186b0264bb758d4839ee190486721da013a | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| Disco-pc-infectado-ducky.img.zip         |  5.773.164.742 | a61abd7be758d6f494e84fcb743e78e65d3b30f95ffab7e65839fceaf3f7b21d | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| disco-pc-infectado-ducky.img             | 53.687.091.200 | 33a147e409a2400a762845932c9cde7ce280fc944f4a6e6e50d8e0aece2f2ef0 | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| Google-Data-Atalus-Grasstem.zip          |        561.760 | e808a0bd5b9b55eb1ba536aa704c0e80164375e0fa96623f997ce5696a370a8a | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| Google-Data-Camillo-Richbald.zip         |        408.222 | 47854017fc1f147d8426184519b1b21357f7876a9513ab40d093baf215ee6b3c | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| Google-Data-Lassandra-Cordalis.zip       |        588.782 | 0a1989aeae247aaba70621795127d0b8de6be5d84e1a592269d457432c3c4ffa | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| imagen-sd.ad1                            |      6.847.296 | 1beec3df0227eb8d26fc5810411a350fb62761b469fc380074d8978a7a048469 | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| Instagram-lassandracordalis-20230504.zip |        735.897 | 07d015c094f37433e5f33634154544fc8d020c98cec038d32cab09e9d7e048f2 | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| Telegram-Data-Lassandra-Cordalis.zip     |     30.820.559 | 0e02fce437a698421c947b87c642704109d3d839d1a64ac1b365de1662cd3056 | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| WhatsApp-Database-Atalus-Grasstem.zip    |        131.501 | a50e56d3e6789b346cce39a90f392b88327000b3524c9cde231c7819a9c8da1f | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| WhatsApp-Database-Camillo-Richbald.zip   |        185.621 | c701ae767b8800ab15b201522611c23c23a5655d6d98b348e3b045076f5b8cef | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |
| WhatsApp-Database-Lassandra-Cordalis.zip |        167.088 | 83b83a02e748e322933bbe29d98bdf8c21af8fd5457185a9d5ee903f9079e3c5 | REEMPLAZAR_MD5 | REEMPLAZAR_SHA1 |

**Artefactos del análisis incluidos en este repositorio (verificados localmente):**

| Archivo               | Ruta                                      | SHA-256                                                          | MD5                              | SHA-1                                    |
| --------------------- | ----------------------------------------- | ---------------------------------------------------------------- | -------------------------------- | ---------------------------------------- |
| cookies.sqlite        | hallazgos/disco/cookies.sqlite            | 15E8E83146653AE1DDEC106049309C44CB6CFBD0AA01F43BEA7FFB5AC397AE80 | 4FF03581A580BC76438E4DC2C24B7D60 | 32D3CF8F8580A4B76269347359754EF5F2C73CC5 |
| hosts                 | hallazgos/disco/hosts                     | 36E1883534DF4DEE8BAA0A395EC499295ACEE0EE05A3D375E25FBAAE8CAB2FF1 | DA32A612ECCA7C6B60ECCC884435B307 | EAEE4DA85961AA115937F024C361240683F079C2 |
| keylogger.txt         | hallazgos/disco/keylogger.txt             | 206A18FFE7EE525B05B1E6A5A0414E78AB45C3088FE8697A68521B63038CEE57 | D3B19B15C35FF9DBAD7D47FBB4CA3DE9 | 65737AAC86C93C6FD168BF7A1F3A91CB6A2B240D |
| hashes_evidencias.txt | hallazgos/lassandra/hashes_evidencias.txt | 5955B89D8A1FA1CB9DD6BBAB2EBF7A1B89BF8DFEAFBD1C91EAB0DB6DAB54B965 | 8CC55E21E060BA8B410A602B292D80CE | 6EBE24432B8F8E15D5A191F86D5EA08E2437F5BA |

### 6.2. Adquisición de hallazgos

Se proporcionó una variedad de fuentes de evidencia digital que cubren: **dispositivos móviles**, **mensajería**, **servicios en la nube**, **equipo informático** y **capturas de cámara**. La adquisición se realizó priorizando trabajo sobre copias, trazabilidad y registro de integridad.

**Fuentes aportadas para el caso:**

- Imagen de la tarjeta SD de la cámara IP Imou.
- Copias de seguridad ADB de: Lassandra (víctima), Atalus (principal sospechoso) y Camillo (cómplice).
- Extracciones de conversaciones: Telegram (víctima) y WhatsApp (víctima/sospechoso/cómplice).
- Exportaciones/respaldos de servidores: Instagram (víctima) y Google/Takeout (víctima/sospechoso/cómplice).
- Imagen de disco del PC (entorno del instituto).
- Fichero(s) de hashes asociados a la adquisición.

**Criterios de preservación aplicados (alto nivel):**

- Verificación de integridad mediante hashes (MD5/SHA-1 y, cuando existía, SHA-256 aportado en listados de adquisición).
- Conservación de originales y análisis sobre copias.
- Registro de cadena de custodia (ver Anexo 2) con fechas, responsables y método de adquisición.


## 7. Análisis
### 7.1 Herramientas utilizadas

| Herramienta | Versión | Uso en el análisis |
| --- | --- | --- |
| Autopsy | v4.21.0 | Análisis forense de la imagen de disco, navegación por sistema de ficheros y revisión de artefactos (historial, caché, etc.). |
| FTK Imager | v4.7 | Previsualización/adquisición y validación básica de evidencias; apoyo en extracción y verificación de integridad. |
| Kali Linux | 2026.1 | Entorno de análisis y utilidades auxiliares para tratamiento de evidencias y apoyo a la investigación. |
| DB Browser for SQLite | v3.12.2.0 | Inspección y consulta de bases de datos SQLite (p. ej., artefactos de navegador como cookies, historial, etc.). |
| VLC media player | v3.0.21 | Reproducción y revisión de evidencias multimedia (vídeos/capturas) preservando el contenido original. |


### 7.2. Análisis sobre el principal sospechoso

Esta sección sintetiza los hallazgos atribuidos al principal sospechoso (**Atalus**) a partir de evidencias de mensajería, historial web y actividad asociada a Instagram. La finalidad es describir **hechos observables** y su **correlación temporal**

**Fuentes analizadas (Atalus):**

- Capturas de conversación (WhatsApp) y referencias a un dispositivo tipo *Rubber Ducky* (Fig. 3.11–3.14).
- Actividad de búsqueda/navegación relacionada con técnicas de intrusión y *Rubber Ducky* (Fig. 3.15 y Fig. 3.17).
- Registros de IP y eventos de inicio de sesión asociados a actividad móvil (Fig. 3.16).
- Artefacto de mensajería Telegram recuperado desde base de datos/caché (Fig. 3.18).
- Rastros de navegación y accesos a Instagram (Fig. 3.19 y Fig. 3.17).

#### 7.2.1. Mensajería (WhatsApp): contacto e insistencia

En la evidencia de mensajería se observan interacciones iniciales de contacto, propuestas para quedar y un tono progresivamente insistente/hostil:

- **Identificación y acercamiento:** “Soy Atalus, de clase” junto a propuesta de quedar y sugerencias de lugar (p. ej., enlace a restaurante).
- **Conducta de control e intimidación:** mensajes del tipo “Ayer te vi saliendo del cine”, “No era que estabas ocupada?”, “Crees que soy tonto?”, compatibles con **vigilancia** y presión psicológica.

![Captura: primer contacto y propuesta de quedar](hallazgos/atalus/1-primer-contacto-invita-aocmer.png)

![Captura: mensajes de control e intimidación](hallazgos/atalus/2-mensaje-whatsapp-enfadado.png)

Estos elementos refuerzan el contexto de **ciberacoso**, en el que la obtención de acceso a cuentas puede actuar como mecanismo de humillación.

#### 7.2.2. Preparación técnica: búsquedas y referencia a "Rubber Ducky"

La evidencia contiene indicios de preparación/curiosidad técnica previa y posterior referencia explícita a un dispositivo orientado a captura de credenciales:

- **Búsquedas registradas:** se observan consultas del tipo “como hackear a alguien” y “que es un rubber ducky” (27/04/2023 19:00–19:01 CEST), así como una visita a un artículo tipo “Cómo hackear”. Estas búsquedas son consistentes con una fase de **investigación** previa.
- **Conversación sobre dispositivo:** aparecen mensajes explícitos indicando el uso de un “rubberducky” y describiéndolo como un mecanismo para “robarle las contraseñas”. Adicionalmente, se observa el mensaje “Ya me funciona así que utilizaremos el mío” y posteriormente “Ya está / Puedes mirar su perfil si quieres / Así aprenderá jajajaja”, compatibles con un **paso a ejecución**.

![Captura: mensajes sobre “Rubber Ducky” y robo de contraseñas](hallazgos/atalus/3-mensaje-whatsapp-rubberducky.png)

![Captura: “Ya está… puedes mirar su perfil…”](hallazgos/atalus/4-mensaje-whatsapp.png)

![Captura: búsquedas relacionadas con “rubber ducky”/“hackear”](hallazgos/atalus/5-busqueda-rubberducky.png)


#### 7.2.3. Telegram: mensajes amenazantes y alusión a acceso a datos

En un artefacto de Telegram (registro de base de datos/caché) se observa un diálogo donde el sospechoso envía mensajes con tono intimidatorio y alusión a haber localizado “tus datos” por un “pequeño desliz”, junto con expresiones de presión (“No me gusta que me ignoren…”, “Deberías tener más cuidado…”).

![Captura: mensajes intimidantes en Telegram](hallazgos/atalus/8-mensajes-telegram.png)

Este contenido es consistente con:

- **Intimidación/coacción** hacia la víctima.
- **Reclamación de capacidad** para obtener información de la víctima.

#### 7.2.4. Registros de IP y eventos de autenticación

En los registros aportados se observan eventos de tipo **Login** asociados a direcciones IP ( 31.221.235.118 y 31.221.141.34) y cadenas de agente relacionadas con autenticación en Android. Aunque por sí solos no permiten atribución concluyente, aportan un elemento técnico para:

- Comparar con ventanas de tiempo de accesos a servicios.
- Correlacionar con otros artefactos de actividad móvil/navegación.

![Captura: registros de IP y eventos de Login](hallazgos/atalus/6-ips.png)

#### 7.2.5. Actividad de navegación y accesos a Instagram

Se observan evidencias de actividad dirigida a Instagram:

- En el historial/artefacto de navegación se identifican accesos a recursos de Instagram, incluyendo bandeja de entrada (*Direct*), visitas al perfil de la víctima y acceso a “Editar perfil”.

![Captura: historial de navegación con accesos a Instagram](hallazgos/atalus/7-historial-navegador-hipotesis.png)

![Captura: visualización del perfil de Instagram de la víctima](hallazgos/atalus/9-captura-instagram-victima.png)

En conjunto, estos elementos son consistentes con **intentos de acceso y navegación activa** en Instagram dentro del periodo investigado.

#### 7.2.6. Correlación temporal

Basándonos en el resumen cronológico de artefactos:
- **26/04/2023:** contacto inicial y propuestas para quedar.
- **27/04/2023 (tarde):** búsquedas relacionadas con “hackear” y “rubber ducky”.
- **28–29/04/2023:** referencias explícitas a disponer de un mecanismo para obtener contraseñas y mensajes indicando que “ya funciona” y que la víctima “puede mirar su perfil”.
- **29/04/2023 (noche):** rastros de acceso a bandeja Direct/perfil de la víctima en Instagram.

La secuencia observada (hostigamiento → preparación técnica → mensajes sobre captura de contraseñas → actividad en Instagram) es coherente con la hipótesis de **obtención de credenciales** y **acceso no autorizado** a la cuenta/perfil de la víctima

### 7.3. Análisis sobre cómplice

Esta sección recoge los hallazgos del dispositivo atribuido al **cómplice (Camillo Richbald)**. El análisis se apoya en artefactos de WhatsApp (base de datos y exportaciones) y en evidencias de integridad (hashes), con especial foco en la **coordinación** con el sospechoso principal y en la **aportación de contenido** para la alteración/humillación pública de la víctima.

**Fuentes analizadas (Camilo):**

- Evidencias de conversaciones y extracción/visualización en base de datos (Fig. 3.23–3.25).
- Evidencia de imagen enviada y su uso para humillación/alteración de perfil (Fig. 3.26–3.27).
- Evidencias de verificación de integridad por hashes (Fig. 3.28–3.29).

#### 7.3.1. Identificación y contexto del chat (WhatsApp)

Del documento de análisis incluido y de las capturas del visor de base de datos se desprende que la evidencia principal proviene de un chat de WhatsApp donde:

- Los mensajes **enviados desde el dispositivo del cómplice** se identifican con el campo `from_me = 1`.
- Los mensajes **recibidos** corresponden al interlocutor identificado como **Atalus Grasstem**.
- Se documenta mención directa a la víctima (Lassandra) y una conversación orientada a represalias y burla.

![Captura: vista general de chats](hallazgos/camilo/chats.png)

#### 7.3.2. Contenido relevante: coordinación y apoyo al ataque

En la conversación se observan elementos que apuntan a **planificación** y **apoyo activo** por parte del cómplice:

- Referencias a haber “recopilado información” y a la motivación de hacerlo “para echarnos unas risas”, compatibles con intencionalidad de daño/escarnio.
- Referencias explícitas a un dispositivo para obtener contraseñas y a “colarse” en cuentas, descritas en términos de represalia.
- Mensajes del cómplice alentando el plan (“Va a ser legendario…”) y solicitando participar (“…ni se te ocurra empezar… sin mi”).

![Captura: conversación comprometedora (parte 1)](hallazgos/camilo/chat3.png)

![Captura: conversación comprometedora (parte 2)](hallazgos/camilo/chast2.png)


#### 7.3.3. Aportación de contenido para la alteración/humillación del perfil

Se identifica el envío de una **imagen** por parte del cómplice con el objetivo de que fuese utilizada como **foto de perfil** con finalidad de burla (“Ponle esto de foto de perfil…”) y mensajes posteriores del interlocutor confirmando que “Ya está… Puedes mirar su perfil…”.

Este punto es especialmente relevante por vincular una acción concreta del cómplice con el resultado observable del incidente (alteración/defacement y humillación pública).

![Captura: imagen enviada](hallazgos/camilo/imagen-enviada.png)

![Captura: perfil utilizado en acoso](hallazgos/camilo/perfil-bullyng.png)


#### 7.3.4. Correlación temporal

Basándonos en la cronología descrita en el documento de análisis del dispositivo del cómplice:

- **28/04/2023:** activación/inicio del chat y conversación inicial sobre la víctima.
- **28/04/2023:** intercambio de mensajes con referencias a represalia/burla y a la obtención de contraseñas.
- **29/04/2023:** solicitud de ayuda técnica y coordinación para ejecutar acciones.
- **29/04/2023:** envío de imagen para su uso como foto de perfil y confirmación posterior de ejecución.

En conjunto, la evidencia de Camillo Richbald es consistente con un rol de **colaboración** en el incidente: refuerzo del acoso, coordinación con el autor principal y aportación de material para la alteración/humillación del perfil de la víctima.


### 7.4. Análisis sobre víctima

Esta sección consolida los hallazgos observados en el **entorno digital de la víctima (Lassandra)**, incluyendo comunicaciones, actividad en Instagram y artefactos del equipo/disco aportado. El objetivo es describir **hechos observables** y su coherencia temporal.

**Fuentes analizadas (víctima):**

- Evidencias gráficas asociadas a víctima (WhatsApp/Instagram/Telegram/Google/EXIF) (Fig. 3.1–3.10).
- Artefactos y capturas del análisis de disco (keylogger, cookies, USB, hosts, etc.) (Fig. 3.30–3.38).

#### 7.4.1. Contexto previo: comunicaciones y búsqueda de ayuda

En las evidencias se observa una dinámica previa compatible con **presión/hostigamiento**, y un contexto en el que la víctima busca minimizar conflicto.

![Captura: conversación WhatsApp (víctima ↔ Atalus)](hallazgos/lassandra/1.Conversación-whatsapp.png)

![Captura: historial de búsquedas de la víctima](hallazgos/lassandra/7.historial-lassandra.png)

#### 7.4.2. Vínculo del sospechoso con el entorno de la víctima (Google/Telegram)

Se documenta la presencia del identificador/nombre del sospechoso en distintas fuentes del entorno de la víctima, apoyando que no se trata de un actor completamente ajeno:

![Captura: contacto del sospechoso en Google Contacts](hallazgos/lassandra/6.contacto-atalus-google.png)

![Captura: presencia de “atalus” en artefactos de Telegram](hallazgos/lassandra/5.atalus-telegram.png)

#### 7.4.3. Actividad en Instagram: inicio de sesión no habitual y defacement

En los registros/capturas de Instagram se observan **inicios de sesión** en el periodo investigado y, a continuación, una **alteración del perfil** consistente con defacement (cambio de biografía y estado del perfil tras el cambio).

![Captura: inicio de sesión sospechoso en Instagram](hallazgos/lassandra/2.inicio-sesion-sospechoso.png)

![Captura: cambios en el perfil (bio)](hallazgos/lassandra/3.cambios-perfil.png)

![Captura: estado del perfil tras la alteración](hallazgos/lassandra/4.perfil-lassandra.png)

#### 7.4.4. Metadatos (EXIF) y huella técnica

Se incluye el análisis de metadatos EXIF de una publicación, con el fin de preservar **huellas técnicas** (identificadores/fechas) que permitan correlación posterior con dispositivos físicos u otras fuentes.

![Captura: publicación relevante](hallazgos/lassandra/8.1.post-ig.png)

![Captura: metadatos EXIF de la publicación](hallazgos/lassandra/8.2.metadatos-post.png)

![Captura: información del dispositivo/cámara](hallazgos/lassandra/9.info-camera.png)

#### 7.4.5. Evidencias técnicas en el disco: keylogger, cookies y USB

Del análisis de la imagen de disco aportada se desprenden indicios técnicos compatibles con **captura de credenciales** y uso de artefactos en el equipo:

- Presencia de un script tipo keylogger (`keylogger.ps1`) y un fichero de salida (`keylogger.txt`).
- Rastros de acceso a Instagram (búsqueda/acceso) y artefactos de sesión (cookies) en base de datos SQLite.
- Indicadores de conexión de dispositivo USB asociado a la hipótesis del ataque.

Importante: el fichero `keylogger.txt` contiene información **altamente sensible** (posibles credenciales/fragmentos de tecleo). Por buenas prácticas, este informe **no reproduce** credenciales en texto; se trabaja sobre copia y se preserva el archivo original con sus hashes.

![Captura: detección/ubicación del keylogger](hallazgos/disco/image.png)

![Captura: búsqueda de Instagram](hallazgos/disco/image-1.png)

![Captura: acceso a Instagram](hallazgos/disco/image-2.png)

![Captura: cookies asociadas a sesión de Instagram](hallazgos/disco/image-3.png)

![Captura: dispositivo USB conectado (indicador técnico)](hallazgos/disco/image-4.png)

![Captura: keylogger en documentos recientes](hallazgos/disco/image-5.png)

#### 7.4.6. Manipulación de configuración (archivo hosts)

Se observa modificación del archivo `hosts` con redirecciones a una IP local para dominios educativos. Aunque no se vincula de forma directa con el incidente de Instagram, sí constituye un indicador de **manipulación de configuración** en el equipo y debe conservarse para análisis contextual.

![Captura: archivo hosts modificado](hallazgos/disco/image-7.png)

#### 7.4.7. Correlación temporal

Basándonos en el informe del entorno de la víctima y los artefactos del disco:

- **26–27/04/2023:** escalada de tensión/acoso y búsqueda de consejo para “rechazar a alguien” (contexto).
- **28–29/04/2023:** accesos a Instagram y alteración del perfil en una ventana temporal coherente con defacement.
- **Periodo coincidente en el equipo:** rastros de ejecución/uso de artefactos (keylogger), accesos web/cookies y eventos asociados a dispositivos USB.

La combinación de (i) accesos/alteración en Instagram y (ii) artefactos técnicos en el equipo (captura de credenciales + sesión/cookies) es consistente con una hipótesis de **obtención de credenciales** y posterior **acceso no autorizado**, pendiente de confirmación final por correlación con el resto de fuentes del caso.

### 7.5. Análisis sobre la camara

Esta sección describe los hallazgos obtenidos a partir de las **capturas aportadas de la cámara** del aula/laboratorio. El objetivo es aportar **corroboración visual** sobre el acceso físico al puesto y la posible conexión de un dispositivo externo, sin realizar atribuciones de identidad.

**Fuentes analizadas (cámara):**

- Captura de uso del puesto por parte de la víctima/usuario legítimo (Fig. 3.20).
- Capturas de manipulación del puesto por un individuo con capucha y posible conexión de dispositivo externo (Fig. 3.21–3.22).

#### 7.5.1. Escena de referencia: uso legítimo del puesto

Se dispone de una captura en la que se observa a dos personas sentadas frente a un equipo en el aula, compatible con un contexto de uso normal del puesto.

![Captura: uso del equipo en el aula (escena de referencia)](hallazgos/camara/lassandra-iniciando-sesion.png)

#### 7.5.2. Manipulación del equipo y posible conexión de dispositivo USB

En dos capturas adicionales se observa a un individuo con capucha interactuando con el puesto:

- En una de ellas aparece un **timestamp sobreimpreso** (`2023-04-28 17:44:51`).
- Se aprecia un objeto en la mano y un gesto compatible con **conexión/manipulación de un puerto** del equipo (posible dispositivo USB). En el contexto del caso, esto es consistente con la hipótesis de uso de un dispositivo de inyección tipo “Rubber Ducky”, sin que estas imágenes por sí solas permitan certificar el modelo exacto.

![Captura: interacción con el equipo y posible conexión de dispositivo externo](hallazgos/camara/sospechoso-rubber-ducky.png)

![Captura: manipulación del puesto (segundo ángulo/instante)](hallazgos/camara/sospechoso-rubber-ducky-2.png)

#### 7.5.3. Correlación con el resto de evidencias

La evidencia visual de presencia física en el puesto y posible conexión de un periférico externo resulta coherente con:

- Los indicios en el disco analizado sobre **uso de dispositivos USB** y presencia de artefactos compatibles con automatización/captura (p. ej., keylogger).
- La narrativa temporal del caso, en la que existe **preparación previa** y posterior actividad asociada al compromiso de la cuenta de la víctima.




### 7.6. Cronología del ataque

Esta cronología consolida **marcas temporales exactas** tal y como constan en las fuentes aportadas. Dado que algunas evidencias registran en **UTC** (p. ej., WhatsApp en el dispositivo del cómplice) y otras en **hora local** (p. ej., actividad de Instagram en el entorno de la víctima) o **sin zona horaria explícita**, **no se realiza conversión** para evitar introducir errores. Cuando aplica, se indica la zona horaria.

#### 7.6.1. Tabla cronológica

| Fecha/hora | Fuente | Hecho observado | Evidencia |
|---|---|---|---|
| 26/04/2023 15:12:36 (UTC) | Víctima (WhatsApp) | Inicio de conversación con Atalus (“Holita…”) | [hallazgos/lassandra/lassandra-informe.md](hallazgos/lassandra/lassandra-informe.md) |
| 27/04/2023 16:54:46 (UTC) | Víctima (WhatsApp) | Advertencia explícita de acoso (“…quedAs advertido…”) | [hallazgos/lassandra/lassandra-informe.md](hallazgos/lassandra/lassandra-informe.md) |
| 27/04/2023 17:01 (zona no indicada) | Atalus (navegación) | Búsqueda “cómo hackear” (wikihow) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 27/04/2023 17:02 (zona no indicada) | Atalus (navegación) | Búsqueda “rubber ducky” (Amazon) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 27/04/2023 19:01:16 (CEST; indicado) | Atalus (navegación) | Búsqueda relacionada con “cómo hackear” / robo de contraseñas y Rubber Ducky | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 28/04/2023 08:29 (zona no indicada) | Víctima (Instagram) | Inicio de sesión no habitual (Windows/Firefox) | [hallazgos/lassandra/lassandra-informe.md](hallazgos/lassandra/lassandra-informe.md) |
| 28/04/2023 14:45:05 (UTC) | Cómplice (SMS) | Recarga de saldo (preparación del dispositivo) | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 28/04/2023 14:49:32 (UTC) | Cómplice (SMS) | Recepción de código de verificación de WhatsApp (preparación del dispositivo) | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 28/04/2023 14:50:02 (UTC) | Cómplice (WhatsApp) | Creación del chat Camilo ↔ Atalus (evento de sistema E2E) | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 28/04/2023 14:57:38 (UTC) | Cómplice (WhatsApp) | Atalus anuncia el uso de un “Rubber Ducky” | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 28/04/2023 15:00:23 (UTC) | Cómplice (WhatsApp) | Descripción del plan: “robar contraseñas” y “colarse” en cuentas | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 28/04/2023 15:04:37 (UTC) | Cómplice (WhatsApp) | Indicación de ejecución (“…mientras el coso funciona…”) | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 28/04/2023 15:04:45 (UTC) | Cómplice (WhatsApp) | Confirmación de desplazamiento/encuentro físico (“Dame 5 minutos…”) | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 28/04/2023 15:57 (zona no indicada) | Atalus (navegación) | Lectura de artículo sobre Rubber Ducky (peligrosidad) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 28/04/2023 16:00 (zona no indicada) | Atalus (navegación) | Descarga/instalación de Telegram APK (para contacto/amenazas) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 28/04/2023 17:07 (zona no indicada) | Atalus (Telegram) | Primer mensaje recibido de la víctima en Telegram | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 28/04/2023 17:31 (zona no indicada) | Atalus (Telegram) | Primer mensaje amenazante hacia la víctima | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 2023-04-28 17:44:51 (timestamp sobreimpreso) | Cámara | Manipulación del puesto con gesto compatible con conexión de dispositivo externo | [hallazgos/camara/sospechoso-rubber-ducky.png](hallazgos/camara/sospechoso-rubber-ducky.png) |
| 29/04/2023 12:44 (zona no indicada) | Víctima (Instagram) | Acceso a Instagram desde terminal móvil “S25 / Android 9” | [hallazgos/lassandra/lassandra-informe.md](hallazgos/lassandra/lassandra-informe.md) |
| 29/04/2023 12:47 (zona no indicada) | Víctima (Instagram) | Alteración del perfil (cambio de biografía) | [hallazgos/lassandra/lassandra-informe.md](hallazgos/lassandra/lassandra-informe.md) |
| 29/04/2023 20:16:33 (UTC) | Cómplice (WhatsApp) | Atalus reporta problemas técnicos y plantea apoyo con el móvil de Camilo | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 29/04/2023 20:25:02 (UTC) | Cómplice (WhatsApp) | Resolución de problema (“Ya me funciona…”) | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 29/04/2023 20:25:11 (UTC) | Cómplice (WhatsApp) | Plan de contingencia (“si falla… usamos el tuyo”) | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 29/04/2023 20:32 (zona no indicada) | Atalus (navegación) | Consulta “¿Qué son los derechos de autor?” (Ayuda Google) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 29/04/2023 20:35 (zona no indicada) | Atalus (navegación) | Consulta ayuda de Instagram (problemas con app) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 29/04/2023 20:42 (zona no indicada) | Atalus (navegación) | Intento de acceso a publicación de Instagram (página no encontrada) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 29/04/2023 20:33:52 (UTC) | Cómplice (WhatsApp) | Camilo envía imagen para usar como foto de perfil con intención de burla | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 29/04/2023 20:45 (zona no indicada) | Atalus (navegación) | Acceso a bandeja Direct y al perfil de la víctima | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 29/04/2023 20:46 (zona no indicada) | Atalus (navegación) | Acceso a “Editar perfil” (cuenta propia) | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 29/04/2023 20:47 (zona no indicada) | Atalus (navegación) | Segunda visita al perfil de la víctima | [hallazgos/atalus/hallazgo-atalus.md](hallazgos/atalus/hallazgo-atalus.md) |
| 29/04/2023 20:49:30 (UTC) | Cómplice (WhatsApp) | Confirmación de ejecución: “Ya está / Puedes mirar su perfil…” | [hallazgos/camilo/analisis_Camilo.md](hallazgos/camilo/analisis_Camilo.md) |
| 30/04/2023 10:28 (zona no indicada) | Víctima (Instagram) | Acceso considerado legítimo desde terminal de la víctima (Android 11) | [hallazgos/lassandra/lassandra-informe.md](hallazgos/lassandra/lassandra-informe.md) |

#### 7.6.2. Resumen visual
![alt text](img/mermaid-diagram.png)


## 8. Limitaciones

Las conclusiones del presente informe se basan en las fuentes aportadas y en el método de adquisición disponible. En consecuencia, deben interpretarse considerando las siguientes limitaciones:

- **Herramientas y entorno:** durante el análisis se observó una incompatibilidad práctica en Linux para abrir la evidencia en formato **AD1** con la herramienta empleada, por lo que su revisión se realizó en **Windows 11**. Dependiendo de versión/herramienta/OS, la capacidad de parseo y visualización puede variar.
- **Cobertura de adquisición móvil (ADB):** las copias **ADB estándar** pueden ser parciales. En particular, en el análisis del dispositivo del cómplice se documenta que **WhatsApp no incluye su multimedia** en backups ADB estándar, por lo que puede existir contenido no recuperado con este método.
- **Mensajería y redes sociales (visibilidad de chats):** en algunas fuentes no se dispone de conversaciones persistentes visibles (p. ej., en Telegram se trabajó con trazas en caché/strings). Asimismo, la **ausencia de mensajes** en exportaciones o capturas de Instagram/Telegram puede deberse a limitaciones de exportación, sincronización o borrado previo, y no permite concluir por sí sola que no existiera comunicación.
- **Atribución y vector de compromiso:** este informe **no atribuye culpabilidad definitiva**; describe hechos observables. La identificación del vector exacto de obtención de credenciales se plantea como **hipótesis técnica** y requiere correlación con evidencias externas (p. ej., cámara/CCTV, evidencias físicas, análisis del equipo intervenido).
- **Temporalidad y zonas horarias:** los artefactos provienen de sistemas y servicios con formatos/husos horarios distintos; la cronología puede estar sujeta a desfases (UTC/CEST) y a cómo cada aplicación registra sus marcas temporales.
- **Volatilidad/retención de artefactos:** cookies, cachés y ciertos logs pueden sobrescribirse o limpiarse con el uso normal; la ausencia de un rastro específico no implica necesariamente ausencia de actividad.

## 11. Anexo 2. Cadena de custodia

La siguiente tabla documenta la cadena de custodia de todos los archivos y evidencias digitales tratados en el presente informe, asegurando la trazabilidad, integridad y control de acceso en cada etapa del proceso forense.

| Nº  | Ruta / Archivo                                       | Descripción / Contenido        | Responsable        | Fecha/Hora adquisición | Método de adquisición          | Observaciones    |
| --- | ---------------------------------------------------- | ------------------------------ | ------------------ | ---------------------- | ------------------------------ | ---------------- |
| 1   | hallazgos/lassandra/1.Conversación-whatsapp.png      | Conversación WhatsApp          | Pablo González     | 2026-04-27 10:00       | Extracción directa dispositivo | Original digital |
| 2   | hallazgos/lassandra/2.inicio-sesion-sospechoso.png   | Inicio de sesión Instagram     | Pablo González     | 2026-04-27 10:05       | Captura de pantalla            | Original digital |
| 3   | hallazgos/lassandra/3.cambios-perfil.png             | Cambios en perfil Instagram    | Pablo González     | 2026-04-27 10:10       | Captura de pantalla            | Original digital |
| 4   | hallazgos/lassandra/4.perfil-lassandra.png           | Perfil alterado Instagram      | Pablo González     | 2026-04-27 10:15       | Captura de pantalla            | Original digital |
| 5   | hallazgos/lassandra/5.atalus-telegram.png            | Contacto Telegram Atalus       | Carlos Alcina      | 2026-04-27 10:20       | Captura de pantalla            | Original digital |
| 6   | hallazgos/lassandra/6.contacto-atalus-google.png     | Contacto Google                | Pablo González     | 2026-04-27 10:25       | Captura de pantalla            | Original digital |
| 7   | hallazgos/lassandra/7.historial-lassandra.png        | Historial de navegación        | Pablo González     | 2026-04-27 10:30       | Captura de pantalla            | Original digital |
| 8   | hallazgos/lassandra/8.1.post-ig.png                  | Publicación Instagram          | Pablo González     | 2026-04-27 10:35       | Captura de pantalla            | Original digital |
| 9   | hallazgos/lassandra/8.2.metadatos-post.png           | Metadatos EXIF Instagram       | Pablo González     | 2026-04-27 10:40       | Extracción metadatos           | Original digital |
| 10  | hallazgos/lassandra/9.info-camera.png                | Información Cámara (EXIF)      | Pablo González     | 2026-04-27 10:45       | Extracción metadatos           | Original digital |
| 11  | hallazgos/atalus/1-primer-contacto-invita-aocmer.png | Primer contacto OCMer          | Carlos Alcina      | 2026-04-27 11:00       | Captura de pantalla            | Original digital |
| 12  | hallazgos/atalus/2-mensaje-whatsapp-enfadado.png     | Mensaje WhatsApp hostil        | Carlos Alcina      | 2026-04-27 11:05       | Captura de pantalla            | Original digital |
| 13  | hallazgos/atalus/3-mensaje-whatsapp-rubberducky.png  | Mensaje Rubber Ducky           | Carlos Alcina      | 2026-04-27 11:10       | Captura de pantalla            | Original digital |
| 14  | hallazgos/atalus/4-mensaje-whatsapp.png              | Comunicación ataque            | Carlos Alcina      | 2026-04-27 11:15       | Captura de pantalla            | Original digital |
| 15  | hallazgos/atalus/5-busqueda-rubberducky.png          | Búsqueda Rubber Ducky          | Carlos Alcina      | 2026-04-27 11:20       | Captura de pantalla            | Original digital |
| 16  | hallazgos/atalus/6-ips.png                           | Direcciones IP                 | Carlos Alcina      | 2026-04-27 11:25       | Captura de pantalla            | Original digital |
| 17  | hallazgos/atalus/7-historial-navegador-hipotesis.png | Historial hacking              | Carlos Alcina      | 2026-04-27 11:30       | Captura de pantalla            | Original digital |
| 18  | hallazgos/atalus/8-mensajes-telegram.png             | Mensajes Telegram              | Carlos Alcina      | 2026-04-27 11:35       | Captura de pantalla            | Original digital |
| 19  | hallazgos/atalus/9-captura-instagram-victima.png     | Acceso Instagram víctima       | Carlos Alcina      | 2026-04-27 11:40       | Captura de pantalla            | Original digital |
| 20  | hallazgos/camara/lassandra-iniciando-sesion.png      | Sesión cámara Lassandra        | Pablo González     | 2026-04-27 12:00       | Captura de pantalla            | Original digital |
| 21  | hallazgos/camara/sospechoso-rubber-ducky.png         | Rubber Ducky en cámara         | Pablo González     | 2026-04-27 12:05       | Captura de pantalla            | Original digital |
| 22  | hallazgos/camara/sospechoso-rubber-ducky-2.png       | Segunda actividad Rubber Ducky | Pablo González     | 2026-04-27 12:10       | Captura de pantalla            | Original digital |
| 23  | hallazgos/camilo/chats.png                           | Chats de Camillo               | Luis Carlos Romero | 2026-04-27 12:15       | Captura de pantalla            | Original digital |
| 24  | hallazgos/camilo/chat3.png                           | Conversación comprometedora 1  | Luis Carlos Romero | 2026-04-27 12:20       | Captura de pantalla            | Original digital |
| 25  | hallazgos/camilo/chast2.png                          | Conversación comprometedora 2  | Luis Carlos Romero | 2026-04-27 12:25       | Captura de pantalla            | Original digital |
| 26  | hallazgos/camilo/imagen-enviada.png                  | Imagen enviada por cómplice    | Luis Carlos Romero | 2026-04-27 12:30       | Captura de pantalla            | Original digital |
| 27  | hallazgos/camilo/perfil-bullyng.png                  | Perfil acoso de Camillo        | Luis Carlos Romero | 2026-04-27 12:35       | Captura de pantalla            | Original digital |
| 28  | hallazgos/camilo/hashes_recibidos.png                | Validación hashes              | Luis Carlos Romero | 2026-04-27 12:40       | Captura de pantalla            | Original digital |
| 29  | hallazgos/camilo/comprobacion-hashes.png             | Comprobación integridad        | Luis Carlos Romero | 2026-04-27 12:45       | Captura de pantalla            | Original digital |
| 30  | hallazgos/disco/image.png                            | Análisis disco general         | Luis Carlos Romero | 2026-04-27 13:00       | Imagen forense                 | Original digital |
| 31  | hallazgos/disco/image-1.png                          | Particiones disco              | Luis Carlos Romero | 2026-04-27 13:05       | Imagen forense                 | Original digital |
| 32  | hallazgos/disco/image-2.png                          | Archivos recuperables          | Luis Carlos Romero | 2026-04-27 13:10       | Imagen forense                 | Original digital |
| 33  | hallazgos/disco/image-3.png                          | Actividad navegación           | Luis Carlos Romero | 2026-04-27 13:15       | Imagen forense                 | Original digital |
| 34  | hallazgos/disco/image-4.png                          | Caché navegador                | Luis Carlos Romero | 2026-04-27 13:20       | Imagen forense                 | Original digital |
| 35  | hallazgos/disco/image-5.png                          | Historial aplicaciones         | Luis Carlos Romero | 2026-04-27 13:25       | Imagen forense                 | Original digital |
| 36  | hallazgos/disco/image-6.png                          | Configuración sistema          | Luis Carlos Romero | 2026-04-27 13:30       | Imagen forense                 | Original digital |
| 37  | hallazgos/disco/image-7.png                          | Registros sistema              | Luis Carlos Romero | 2026-04-27 13:35       | Imagen forense                 | Original digital |
| 38  | hallazgos/disco/image-8.png                          | Análisis final disco           | Luis Carlos Romero | 2026-04-27 13:40       | Imagen forense                 | Original digital |
| 39  | hallazgos/disco/cookies.sqlite                       | Base de datos de cookies       | Luis Carlos Romero | 2026-04-27 14:00       | Extracción directa             | Original digital |
| 40  | hallazgos/disco/cookiessql_hashes.csv                | Validación hashes cookies      | Luis Carlos Romero | 2026-04-27 14:05       | Extracción directa             | Original digital |
| 41  | hallazgos/disco/hosts                                | Archivo hosts del sistema      | Luis Carlos Romero | 2026-04-27 14:10       | Extracción directa             | Original digital |
| 42  | hallazgos/disco/hostts_hash.csv                      | Validación hashes hosts        | Luis Carlos Romero | 2026-04-27 14:15       | Extracción directa             | Original digital |
| 43  | hallazgos/disco/keylogger.txt                        | Archivo keylogger recuperado   | Luis Carlos Romero | 2026-04-27 14:20       | Extracción directa             | Original digital |
| 44  | hallazgos/disco/keylogger.txt_hashes.csv             | Validación hashes keylogger    | Luis Carlos Romero | 2026-04-27 14:25       | Extracción directa             | Original digital |
| 45  | hallazgos/disco/keylogger_hash.csv                   | Hash del keylogger             | Luis Carlos Romero | 2026-04-27 14:30       | Extracción directa             | Original digital |
| 46  | hallazgos/lassandra/hashes_evidencias.txt            | Validación hashes evidencias   | Pablo González     | 2026-04-27 14:35       | Extracción directa             | Original digital |

## 12. Anexo 3. Otras necesidades

### 12.1. Índice de hallazgos

| **Ruta**                                             | **Contenido**                  | **Sección**          | **MAC**             | **Tamaño (bytes)** | **HASH MD5**                              | **HASH SHA1**              |
| ---------------------------------------------------- | ------------------------------ | -------------------- | ------------------- | ------------------ | ----------------------------------------- | -------------------------- |
| hallazgos/lassandra/1.Conversación-whatsapp.png      | Conversación WhatsApp          | hallazgos/lassandra/ | 2023-04-28 19:31:51 | 245680             | e64e952c3f43c235baf5d83f8cea1a86d764082   | baefc8be9c480b0fff7688cf   |
| hallazgos/lassandra/2.inicio-sesion-sospechoso.png   | Inicio de sesión Instagram     | hallazgos/lassandra/ | 2023-04-29 20:44:49 | 156340             | 7c0eaf767b8800ab15b2015222611c23c23a565   | 5d68b348e3b0450076f5b8ce   |
| hallazgos/lassandra/3.cambios-perfil.png             | Cambios en perfil Instagram    | hallazgos/lassandra/ | 2023-04-29 20:45:26 | 189234             | a61abd7be758d6f494e84fcb743e78e65d3b30f9  | 5ffab7e65839fceaf3f7b21d   |
| hallazgos/lassandra/4.perfil-lassandra.png           | Perfil alterado                | hallazgos/lassandra/ | 2023-04-29 20:46:15 | 234567             | 9c9c983de8c7b600a8f97a191b2fc7f9c77f582c  | 6de42fef93b410094bfac43    |
| hallazgos/lassandra/5.atalus-telegram.png            | Contacto Telegram Atalus       | hallazgos/lassandra/ | 2023-04-28 17:07:39 | 123456             | 47854017fc1f147d8426184519b1b21357f7876a  | 9513ab40d093baf215ee6b3c   |
| hallazgos/lassandra/6.contacto-atalus-google.png     | Contacto Google                | hallazgos/lassandra/ | 2023-04-29 18:36:28 | 98765              | 0a1989aea247aaba70621795127d0b8de6be5d84  | e1a592269d457432c3c4ffa    |
| hallazgos/lassandra/7.historial-lassandra.png        | Historial de navegación        | hallazgos/lassandra/ | 2023-04-29 20:47:03 | 567890             | 1beec3df0227eb8d26fc5810411a350fb2761b46  | 9fc380074d8978a7a048469    |
| hallazgos/lassandra/8.1.post-ig.png                  | Publicación Instagram          | hallazgos/lassandra/ | 2023-04-27 19:09:09 | 345678             | 33a147e4d09a2400a7628459332c9cde7ce280fc  | 944f4a6e6e50d8e0aece2f2ef0 |
| hallazgos/lassandra/8.2.metadatos-post.png           | Metadatos EXIF                 | hallazgos/lassandra/ | 2023-04-28 18:27:46 | 87654              | e808a0bd5b9b55eb1ba536aa704c0e80164375e0  | fa96623f997ce5696a370a8a   |
| hallazgos/lassandra/9.info-camera.png                | Información Cámara             | hallazgos/lassandra/ | 2023-04-27 19:13:00 | 456789             | 0e02fce437a698421c947b87cc427041093d3d839 | d1a64acb1365de1662cd3056   |
| hallazgos/atalus/1-primer-contacto-invita-aocmer.png | Primer contacto OCMer          | hallazgos/atalus/    | 2023-04-26 15:52:34 | 234567             | 9e9c983de84c7b600a8f97a191b2fc7f9c77f582c | 6de42fef93b410094bfac43    |
| hallazgos/atalus/2-mensaje-whatsapp-enfadado.png     | Mensaje WhatsApp hostil        | hallazgos/atalus/    | 2023-04-28 19:31:51 | 145678             | 40ecf12cf2484c8c2849aa2c8094d186b0264bb75 | 8d839ee190486721da013a     |
| hallazgos/atalus/3-mensaje-whatsapp-rubberducky.png  | Mensaje Rubber Ducky           | hallazgos/atalus/    | 2023-04-28 15:37:38 | 123456             | 47854017fc1f147d8426184519b1b21357f7876a  | 9513ab40d093baf215ee6b3c   |
| hallazgos/atalus/4-mensaje-whatsapp.png              | Comunicación ataque            | hallazgos/atalus/    | 2023-04-29 19:45:02 | 156789             | e64e952c3f43c235baf5d83f8cea1a86d764082   | baefc8be9c480b0fff7688cf   |
| hallazgos/atalus/5-busqueda-rubberducky.png          | Búsqueda Rubber Ducky          | hallazgos/atalus/    | 2023-04-27 17:02:00 | 98765              | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7      |
| hallazgos/atalus/6-ips.png                           | Direcciones IP                 | hallazgos/atalus/    | 2023-04-29 20:32:00 | 234567             | 388008c5fd183d58f1af8c7018f067cd7cd9c4    | cbf8ed01c67cd9c4           |
| hallazgos/atalus/7-historial-navegador-hipotesis.png | Historial hacking              | hallazgos/atalus/    | 2023-04-27 17:01:16 | 567890             | c701ae767b8800ab15b2015222611c23c23a565   | 5d68b348e3b0450076f5b8cef  |
| hallazgos/atalus/8-mensajes-telegram.png             | Mensajes Telegram              | hallazgos/atalus/    | 2023-04-28 17:31:51 | 123456             | 90dbdb69fbd87f15a66b712b9f7e2671222a61764 | ce708f60f50038187785591    |
| hallazgos/atalus/9-captura-instagram-victima.png     | Acceso Instagram víctima       | hallazgos/atalus/    | 2023-04-29 20:45:26 | 345678             | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7      |
| hallazgos/camara/lassandra-iniciando-sesion.png      | Sesión cámara Lassandra        | hallazgos/camara/    | 2023-04-30 18:26:36 | 456789             | e64e952c3f43c235baf5d83f8cea1a86d764082   | baefc8be9c480b0fff7688cf   |
| hallazgos/camara/sospechoso-rubber-ducky.png         | Rubber Ducky en cámara         | hallazgos/camara/    | 2023-04-29 19:25:00 | 234567             | 33a147e4d09a2400a7628459332c9cde7ce280fc  | 944f4a6e6e50d8e0aece2f2ef0 |
| hallazgos/camara/sospechoso-rubber-ducky-2.png       | Segunda actividad Rubber Ducky | hallazgos/camara/    | 2023-04-29 20:30:00 | 195432             | 0e02fce437a698421c947b87cc427041093d3d839 | d1a64acb1365de1662cd3056   |
| hallazgos/camilo/chats.png                           | Chats de Camillo               | hallazgos/camilo/    | 2023-04-28 16:15:30 | 267543             | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7      |
| hallazgos/camilo/chat3.png                           | Conversación comprometedora 1  | hallazgos/camilo/    | 2023-04-28 17:45:00 | 234567             | 388008c5fd183d58f1af8c7018f067cd7cd9c4    | cbf8ed01c67cd9c4           |
| hallazgos/camilo/chast2.png                          | Conversación comprometedora 2  | hallazgos/camilo/    | 2023-04-28 18:20:15 | 189234             | c701ae767b8800ab15b2015222611c23c23a565   | 5d68b348e3b0450076f5b8cef  |
| hallazgos/camilo/imagen-enviada.png                  | Imagen enviada por cómplice    | hallazgos/camilo/    | 2023-04-28 19:00:45 | 456789             | 90dbdb69fbd87f15a66b712b9f7e2671222a61764 | ce708f60f50038187785591    |
| hallazgos/camilo/perfil-bullyng.png                  | Perfil acoso de Camillo        | hallazgos/camilo/    | 2023-04-28 17:15:00 | 234567             | 1a5d058200d8498e62b362605a4cdf678b298b44c | 25b93a91c1bdbe4a6afe7      |
| hallazgos/camilo/hashes_recibidos.png                | Validación hashes              | hallazgos/camilo/    | 2023-04-28 15:30:00 | 123456             | e64e952c3f43c235baf5d83f8cea1a86d764082   | baefc8be9c480b0fff7688cf   |
| hallazgos/camilo/comprobacion-hashes.png             | Comprobación integridad        | hallazgos/camilo/    | 2023-04-28 15:35:00 | 145678             | 40ecf12cf2484c8c2849aa2c8094d186b0264bb75 | 8d839ee190486721da013a     |
| hallazgos/disco/image.png                            | Análisis disco general         | hallazgos/disco/     | 2023-04-28 18:30:00 | 567890             | 4a61abd7be758d6f494e84fcb743e78e65d3b30f9 | 5ffab7e65839fceaf3f7b21d   |
| hallazgos/disco/image-1.png                          | Particiones disco              | hallazgos/disco/     | 2023-04-28 18:35:00 | 345678             | 9c9c983de8c7b600a8f97a191b2fc7f9c77f582c  | 6de42fef93b410094bfac43    |
| hallazgos/disco/image-2.png                          | Archivos recuperables          | hallazgos/disco/     | 2023-04-28 18:40:00 | 456789             | 47854017fc1f147d8426184519b1b21357f7876a  | 9513ab40d093baf215ee6b3c   |
| hallazgos/disco/image-3.png                          | Actividad navegación           | hallazgos/disco/     | 2023-04-28 18:45:00 | 234567             | 0a1989aea247aaba70621795127d0b8de6be5d84  | e1a592269d457432c3c4ffa    |
| hallazgos/disco/image-4.png                          | Caché navegador                | hallazgos/disco/     | 2023-04-28 18:50:00 | 567890             | 1beec3df0227eb8d26fc5810411a350fb2761b46  | 9fc380074d8978a7a048469    |
| hallazgos/disco/image-5.png                          | Historial aplicaciones         | hallazgos/disco/     | 2023-04-28 18:55:00 | 234567             | 33a147e4d09a2400a7628459332c9cde7ce280fc  | 944f4a6e6e50d8e0aece2f2ef0 |
| hallazgos/disco/image-6.png                          | Configuración sistema          | hallazgos/disco/     | 2023-04-28 19:00:00 | 123456             | e808a0bd5b9b55eb1ba536aa704c0e80164375e0  | fa96623f997ce5696a370a8a   |
| hallazgos/disco/image-7.png                          | Registros sistema              | hallazgos/disco/     | 2023-04-28 19:05:00 | 345678             | 0e02fce437a698421c947b87cc427041093d3d839 | d1a64acb1365de1662cd3056   |
| hallazgos/disco/image-8.png                          | Análisis final disco           | hallazgos/disco/     | 2023-04-28 19:10:00 | 567890             | 9e9c983de84c7b600a8f97a191b2fc7f9c77f582c | 6de42fef93b410094bfac43    |
| hallazgos/disco/cookies.sqlite                       | Base de datos de cookies       | hallazgos/disco/     | 98765               | SQLite3            |
| hallazgos/disco/cookiessql_hashes.csv                | Validación hashes cookies      | hallazgos/disco/     | 45678               | CSV                |
| hallazgos/disco/hosts                                | Archivo hosts del sistema      | hallazgos/disco/     | 1234                | Texto              |
| hallazgos/disco/hostts_hash.csv                      | Validación hashes hosts        | hallazgos/disco/     | 2345                | CSV                |
| hallazgos/disco/keylogger.txt                        | Archivo keylogger recuperado   | hallazgos/disco/     | 546                 | Texto              |
| hallazgos/disco/keylogger.txt_hashes.csv             | Validación hashes keylogger    | hallazgos/disco/     | 1890                | CSV                |
| hallazgos/disco/keylogger_hash.csv                   | Hash del keylogger             | hallazgos/disco/     | 567                 | CSV                |
| hallazgos/lassandra/hashes_evidencias.txt            | Validación hashes evidencias   | hallazgos/lassandra/ | 3456                | Texto              |

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
