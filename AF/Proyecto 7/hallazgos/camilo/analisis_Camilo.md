# Análisis del Dispositivo del Cómplice Camillo Richbald

---

> **Nota:** Este documento presenta un análisis técnico y cronológico de los hallazgos extraídos del dispositivo de Camillo Richbald, con soporte visual mediante capturas relevantes. No constituye un informe ejecutivo ni conclusivo, sino una exposición detallada de los hechos observados.

---

| Campo | Detalle |
|---|---|
| **Fecha del informe** | 27 de abril de 2026 |
| **Clasificación** | CONFIDENCIAL — Uso exclusivo judicial |
| **Fuentes analizadas** | tabla.csv · adb-backup-Camillo-Richbald.ab · msgstore.db · wa.db · key |
| **Período cubierto** | 28/04/2023 — 03/05/2023 |
| **Perito** | Luis Carlos Romero |

---


## Índice

1. [Introducción](#1-introducción)
2. [Fuentes Analizadas](#2-fuentes-analizadas)
3. [Identificación de Participantes](#3-identificación-de-participantes)
4. [Hallazgos por Fuente](#4-hallazgos-por-fuente)
5. [Mensajes Relevantes](#5-mensajes-relevantes)
6. [Tabla de Mensajes](#6-tabla-de-mensajes)
7. [Cronología del Ataque](#7-cronología-del-ataque)
8. [Observaciones Finales](#8-observaciones-finales)
9. [Registro de Hashes de Hallazgos](#9-registro-de-hallazgos)

---


## 1. Introducción

Este análisis técnico recoge los hallazgos obtenidos del estudio forense del dispositivo móvil de Camillo Richbald, en el contexto de un caso de ciberbullying. Se documentan los hechos observados, la cronología de los eventos y las pruebas visuales extraídas, sin emitir una valoración ejecutiva ni conclusiones jurídicas.

---


## 2. Fuentes Analizadas

### 2.1. tabla.csv — Exportación de base de datos WhatsApp

Archivo CSV exportado que contiene 55 registros de la tabla `message` de la base de datos SQLite de WhatsApp. Incluye los campos: `_id`, `chat_row_id`, `from_me`, `key_id`, `sender_jid_row_id`, `status`, `timestamp`, `received_timestamp`, `receipt_server_timestamp`, `message_type`, `text_data`, entre otros.

El campo `from_me = 1` identifica mensajes enviados por Camillo Richbald; `from_me = 0` identifica mensajes recibidos del interlocutor.

### 2.2. adb-backup-Camillo-Richbald.ab — Copia de seguridad ADB

| Campo | Valor |
|---|---|
| **Versión backup** | Android Backup v5 |
| **Cifrado** | No cifrado |
| **Compresión** | zlib |
| **Tamaño archivo** | 158.652 bytes |
| **Tamaño extraído** | 10.125.312 bytes |
| **Dispositivo** | Xiaomi (MIUI) |

Contiene datos de aplicaciones del sistema, manifiestos de apps instaladas, y tres mensajes SMS de valor forense (ver sección 4.2). WhatsApp no incluye sus datos multimedia en backups ADB estándar.

### 2.3. msgstore.db — Base de datos nativa WhatsApp

Base de datos SQLite nativa de WhatsApp extraída del dispositivo. Versión SQLite 3.28.0. Contiene 55 mensajes en la tabla `message`, información de medios en `message_media`, miniaturas en `message_thumbnail`, confirmaciones de lectura en `receipt_user`, y registro de llamadas en `call_log` (vacío).

### 2.4. wa.db — Base de datos de contactos WhatsApp

Base de datos SQLite de contactos de WhatsApp (versión de usuario 86). Contiene 3 contactos registrados, de los cuales uno es el interlocutor principal del caso.

### 2.5. key — Clave de cifrado de backup WhatsApp

Archivo de 158 bytes en formato Java Serialization (versión 5) que contiene la clave de cifrado AES utilizada por WhatsApp para cifrar sus copias de seguridad en Google Drive. No fue necesaria su aplicación al no estar el backup cifrado.

---

## 3. Identificación de Participantes

### 3.1. Camillo Richbald — Propietario del dispositivo (Cómplice)

| Campo | Valor |
|---|---|
| **Rol en la conversación** | `from_me = 1` — mensajes enviados desde el dispositivo |
| **Mensajes enviados** | 22 mensajes de texto + 1 imagen |
| **JID WhatsApp** | Propietario del dispositivo analizado |
| **Número verificado** | Confirmado por SMS de código WhatsApp recibido en el dispositivo |

### 3.2. Atalus Grasstem — Interlocutor (Presunto acusado principal)

| Campo | Valor |
|---|---|
| **Nombre en agenda** | Atalus Grasstem |
| **Número de teléfono** | +34634102818 |
| **JID WhatsApp** | 34634102818@s.whatsapp.net |
| **Fuente de identificación** | wa.db — tabla wa_contacts |
| **Rol en la conversación** | `from_me = 0` — mensajes recibidos en el dispositivo |
| **Mensajes enviados** | 31 mensajes de texto |

### 3.3. Víctima

| Campo | Valor |
|---|---|
| **Nombre mencionado** | Lassandra (citada directamente en los mensajes) |
| **Referencia en mensajes** | IDs 5-6: «La lassandra / Que me bloqueó la subnormal» |
| **Víctima anterior mencionada** | Roberto — mensaje ID 14, se cambió de centro educativo |

---

## 4. Hallazgos por Fuente

### 4.1. Hallazgos en msgstore.db / tabla.csv

#### 4.1.1. Estadísticas generales

| Campo | Valor |
|---|---|
| **Total mensajes** | 55 registros (53 con texto, 1 imagen, 1 mensaje de sistema) |
| **Periodo** | 28/04/2023 14:50:02 UTC — 29/04/2023 20:49:30 UTC |
| **Chat identificado** | Chat ID 2, creado el 28/04/2023 a las 14:50:02 UTC |
| **Mensajes de Camillo** | 22 enviados (from_me = 1) |
| **Mensajes de Atalus** | 31 recibidos (from_me = 0) |
| **Mensajes eliminados** | **Ninguno** (tabla message_revoked vacía) |
| **Mensajes editados** | **Ninguno** (tabla message_edit_info vacía) |
| **Llamadas registradas** | **Ninguna** (tabla call_log vacía) |

#### 4.1.2. Mensaje de sistema (ID=2) — Activación del chat

El mensaje de sistema ID=2 (`type=7`, `system_value=67`) corresponde a la notificación de cifrado extremo a extremo generada automáticamente por WhatsApp al iniciarse una nueva conversación. Su timestamp es **28/04/2023 14:50:02 UTC**, marcando el momento exacto de apertura del chat.

Este evento guarda correlación directa con el SMS de verificación de WhatsApp recibido **38 segundos antes** (ver sección 4.2).


#### 4.1.3. Imagen enviada (ID=52) — Hallazgo Crítico

A las 29/04/2023 20:33:52 UTC, Camillo Richbald envía una imagen acompañada del texto *«Ponle esto de foto de perfil verás que risas jajajjaja»*. Esta imagen fue utilizada para modificar el perfil público de la víctima, como confirma el interlocutor 16 minutos después.

![Captura imagen enviada](imagen-enviada.png)

| Campo | Valor |
|---|---|
| **ID mensaje** | 52 |
| **Timestamp envío** | 29/04/2023 20:33:52 UTC |
| **Ruta en dispositivo** | `Media/WhatsApp Images/Sent/IMG-20230429-WA0000.jpg` |
| **Nombre archivo** | 69751750-02ba-4c4f-a420-b1cddbb2f074.jpg |
| **Tamaño** | 5.297 bytes |
| **Dimensiones** | 225 × 225 píxeles |
| **Hash SHA-256 (archivo)** | `u46Rg0Dflo1uRpDNfRaRuyPVoBEUK7XoNsxvfrAxARo=` |
| **Hash SHA-256 (cifrado)** | `HEQdU9BoPm/zP586Y6qi1He23GYd0kYcfF2YWG7nKck=` |
| **Hash SHA-256 (original)** | `7LoUYBtLqr5h5znesFYbZOU0MotgJHBIblaXmMeIkCM=` |
| **URL CDN WhatsApp** | `mmg.whatsapp.net/v/t62.7118-24/18903714_1587769924968509_...` |
| **Thumbnail recuperada** | Sí (649 bytes, embebida en message_thumbnail de msgstore.db) |

La ruta completa en el dispositivo Android es: `/sdcard/WhatsApp/Media/WhatsApp Images/Sent/IMG-20230429-WA0000.jpg`. El archivo completo no está en el backup ADB, pero los hashes permiten identificarlo inequívocamente si se recupera mediante imagen forense completa del dispositivo.

---

### 4.2. Hallazgos en el ADB Backup — SMS

> ⚠️ **HALLAZGO CLAVE — CORRELACIÓN TEMPORAL:** El dispositivo de Camillo recibió un código de verificación de WhatsApp **38 segundos antes** de que comenzara la conversación incriminatoria. Esto indica que la cuenta fue activada/registrada expresamente antes del incidente.

| Nº | Fecha/Hora (UTC) | Remitente | Contenido |
|---|---|---|---|
| 1 | 28/04/2023 14:45:05 | 2377 (MasMovil) | Recarga de 3€ realizada con éxito |
| **2** | **28/04/2023 14:49:32** | **+34613155297** | **Código de verificación WhatsApp: 527-873** |
| 3 | 03/05/2023 14:37:55 | Xiaomi | Código de verificación cuenta Xiaomi: 141310 |

**Línea temporal crítica del SMS nº 2:**

| Hora (UTC) | Evento |
|---|---|
| 14:45:05 | Recarga de saldo (3€) — preparación del dispositivo |
| **14:49:32** | **Recepción del código de verificación de WhatsApp** |
| 14:50:02 | Creación del chat WhatsApp (mensaje de sistema ID=2) — **30 segundos después** |
| 14:50:11 | Primer mensaje de texto enviado por Camillo — **38 segundos después del SMS** |

---

## 5. Mensajes Relevantes

Los siguientes 15 mensajes constituyen las evidencias de mayor valor probatorio directo para la investigación del ciberbullying.

| ID | Fecha/Hora (UTC) | Remitente | Mensaje | Relevancia |
|---|---|---|---|---|
| 11 | 28/04/2023 14:54:09 | Atalus Grasstem | «No entre porque estuve recopilando información» | Recopilación de datos sobre la víctima |
| 12 | 28/04/2023 14:54:10 | Atalus Grasstem | «Para echarnos unas risas» | Intención de causar daño/burla |
| 14 | 28/04/2023 14:55:11 | **Camillo Richbald** | «Pero tampoco te pases, que la última vez el pobre Roberto se cambió de centro» | **Reconoce patrón reincidente y víctima anterior** |
| 15 | 28/04/2023 14:55:46 | **Camillo Richbald** | «Nos quedamos sin su dinero y nos jodimos un mes sin sobres del fifa» | **Admite sustracción económica a víctima anterior** |
| 19 | 28/04/2023 14:57:38 | Atalus Grasstem | «No, subnormal, utilizaré un ruberducky» | Anuncio del dispositivo de ataque |
| 22 | 28/04/2023 15:00:23 | Atalus Grasstem | «Es un dispositivo para robarle las contraseñas» | Descripción explícita del ataque informático |
| 23 | 28/04/2023 15:00:23 | Atalus Grasstem | «Luego nos colamos y le hacemos alguna putada» | Acceso no autorizado + intención de daño |
| 24 | 28/04/2023 15:00:23 | Atalus Grasstem | «K a mi nadie me vacila» | Móvil del ataque: represalia personal |
| 26 | 28/04/2023 15:00:40 | **Camillo Richbald** | «Va a ser legendario bro» | **Complicidad activa y aliento explícito** |
| 31 | 28/04/2023 15:02:00 | **Camillo Richbald** | «Además ni se te ocurra empezar las putadas sin mi eh?» | **Solicita participación directa en el ataque** |
| 33 | 28/04/2023 15:02:21 | **Camillo Richbald** | «Y esto va a quedar para la prosperidad» | **Conciencia de la gravedad del acto** |
| 52 | 29/04/2023 20:33:52 | **Camillo Richbald** | **[IMAGEN]** «Ponle esto de foto de perfil verás que risas jajajjaja» | **Aportación de contenido ofensivo para el perfil de la víctima** |
| 53 | 29/04/2023 20:49:30 | Atalus Grasstem | «Ya esta» | Confirmación de ejecución exitosa del ataque |
| 54 | 29/04/2023 20:49:30 | Atalus Grasstem | «Puedes mirar su perfil si quieres» | Perfil de la víctima comprometido y modificado |
| 55 | 29/04/2023 20:49:30 | Atalus Grasstem | «Así aprenderá jajajaja» | Intención de escarmiento y humillación pública |

---

## 6. Tabla de Mensajes

> Leyenda: **C** = Camillo Richbald (from_me=1) · **A** = Atalus Grasstem (from_me=0) · ★ = relevancia probatoria directa

| ID | Fecha/Hora (UTC) | De | Mensaje |
|---|---|---|---|
| 3 | 28/04/2023 14:50:11 | C | Bro, que pasó? |
| 4 | 28/04/2023 14:50:20 | C | Ayer no te vi en todo el dia |
| 5 | 28/04/2023 14:50:52 | A | La lassandra |
| 6 | 28/04/2023 14:51:00 | A | Que me bloqueó la subnormal |
| 7 | 28/04/2023 14:51:37 | C | Vaya huevos tiene la peña ermano |
| 8 | 28/04/2023 14:52:06 | C | No te me habrás deprimido por esa mierda no? |
| 9 | 28/04/2023 14:52:14 | C | Ke mañana hay partido |
| 10 | 28/04/2023 14:53:16 | A | Que cojones me voy a deprimir por eso |
| ★ 11 | 28/04/2023 14:54:09 | A | No entre porque estuve recopilando información |
| ★ 12 | 28/04/2023 14:54:10 | A | Para echarnos unas risas |
| 13 | 28/04/2023 14:54:46 | C | Jajajaja eres criminal |
| ★ 14 | 28/04/2023 14:55:11 | C | Pero tampoco te pases, que la última vez el pobre Roberto se cambió de centro |
| ★ 15 | 28/04/2023 14:55:46 | C | Nos quedamos sin su dinero y nos jodimos un mes sin sobres del fifa |
| 16 | 28/04/2023 14:56:24 | A | No te ralles primo |
| 17 | 28/04/2023 14:56:46 | A | Que está vez no voy ni a estar presente jajaja |
| 18 | 28/04/2023 14:57:06 | C | Vas a contratar a alguien? |
| ★ 19 | 28/04/2023 14:57:38 | A | No, subnormal, utilizaré un ruberducky |
| 20 | 28/04/2023 14:59:03 | C | Bro, a mi que me cuentas de inglés, y no me insultes que le meto al richi |
| 21 | 28/04/2023 15:00:22 | A | Que cabron ajajajsjs |
| ★ 22 | 28/04/2023 15:00:23 | A | Es un dispositivo para robarle las contraseñas |
| ★ 23 | 28/04/2023 15:00:23 | A | Luego nos colamos y le hacemos alguna putada |
| ★ 24 | 28/04/2023 15:00:23 | A | K a mi nadie me vacila |
| 25 | 28/04/2023 15:00:34 | C | Madre mía |
| ★ 26 | 28/04/2023 15:00:40 | C | Va a ser legendario bro |
| 27 | 28/04/2023 15:00:48 | C | Eres el Messi de la informatica |
| 28 | 28/04/2023 15:01:25 | A | Soy del Madrid anormal |
| 29 | 28/04/2023 15:01:38 | A | No te me pongas chulo que te la aplicó a ti también |
| 30 | 28/04/2023 15:01:45 | C | De chill Bro que era broma |
| ★ 31 | 28/04/2023 15:02:00 | C | Además ni se te ocurra empezar las putadas sin mi eh? |
| 32 | 28/04/2023 15:02:03 | C | Que te conozco |
| ★ 33 | 28/04/2023 15:02:21 | C | Y esto va a quedar para la prosperidad |
| 34 | 28/04/2023 15:02:23 | C | Quiero estar |
| 35 | 28/04/2023 15:02:31 | A | Va |
| 36 | 28/04/2023 15:02:40 | A | Te envío los detalles m cuanto los tenga |
| 37 | 28/04/2023 15:02:57 | A | Se va a cagar jajaja |
| 38 | 28/04/2023 15:04:37 | A | Ahora baja un rato que tenemos que hacer tiempo mientras el coso funciona |
| 39 | 28/04/2023 15:04:45 | C | Dame 5 minutos que tengo que preparar el balon |
| 40 | 29/04/2023 20:16:33 | A | Brother necesito tu ayuda |
| 41 | 29/04/2023 20:16:34 | A | Que no me funciona esto |
| 42 | 29/04/2023 20:16:35 | A | Voy ahora pa tu casa y lo hacemos con tu movil |
| 43 | 29/04/2023 20:16:35 | A | Que a m no me tira |
| 44 | 29/04/2023 20:16:35 | A | Te veo en cuanto llegue |
| 45 | 29/04/2023 20:16:36 | A | Mejor te llamo directamente porque ni los mensajes se envian |
| 46 | 29/04/2023 20:25:02 | A | Ya me funciona así que utilizaremos el mio |
| 47 | 29/04/2023 20:25:11 | A | A menos que vuelva a bloquear entonces sacamos el tuyo |
| 48 | 29/04/2023 20:25:27 | C | Me estas loqueando bro |
| 49 | 29/04/2023 20:25:43 | C | Como veas pero no me cambies de planes 7 veces cabron |
| 50 | 29/04/2023 20:26:17 | A | Céntrate en lo que viene ahora |
| 51 | 29/04/2023 20:26:24 | A | Que va a ser único |
| ★ 52 | 29/04/2023 20:33:52 | C | **[IMAGEN]** Ponle esto de foto de perfil verás que risas jajajjaja |
| ★ 53 | 29/04/2023 20:49:30 | A | Ya esta |
| ★ 54 | 29/04/2023 20:49:30 | A | Puedes mirar su perfil si quieres |
| ★ 55 | 29/04/2023 20:49:30 | A | Así aprenderá jajajaja |

---

## 7. Cronología del Ataque

| Fecha y Hora (UTC) | Evento |
|---|---|
| 28/04/2023 14:45:05 | Camillo recarga saldo (3€, MasMovil) — preparación del dispositivo |
| 28/04/2023 14:49:32 | Camillo recibe SMS con código de verificación de WhatsApp (527-873) desde +34613155297 |
| 28/04/2023 14:50:02 | Se crea el chat entre Camillo y Atalus en WhatsApp (mensaje de sistema E2E, ID=2) |
| 28/04/2023 14:50:11 | Inicio de la conversación: Camillo pregunta por la víctima |
| 28/04/2023 14:50:52 | Atalus menciona a la víctima (Lassandra) y explica que lo ha bloqueado |
| 28/04/2023 14:54:09 | Atalus revela haber recopilado información sobre la víctima |
| 28/04/2023 14:55:11 | Camillo menciona víctima anterior (Roberto) que cambió de centro educativo |
| 28/04/2023 14:57:38 | Atalus anuncia el uso de un Rubber Ducky para el ataque |
| 28/04/2023 15:00:23 | Atalus explica el plan: robar contraseñas y acceder a la cuenta de la víctima |
| 28/04/2023 15:00:40 | Camillo alienta activamente el plan («Va a ser legendario bro») |
| 28/04/2023 15:02:00 | Camillo solicita participar presencialmente en el ataque |
| 28/04/2023 15:04:37 | Atalus indica que el Rubber Ducky ya está en funcionamiento |
| 28/04/2023 15:04:45 | Camillo confirma que baja en 5 minutos — **encuentro físico** |
| 29/04/2023 20:16:33 | Atalus informa de problemas técnicos; plantea usar el móvil de Camillo como respaldo |
| 29/04/2023 20:25:02 | Atalus resuelve el problema; confirman que usarán su propio dispositivo |
| 29/04/2023 20:25:11 | Atalus advierte que si falla usarán el dispositivo de Camillo |
| 29/04/2023 20:33:52 | **Camillo envía imagen ofensiva** para usar como foto de perfil de la víctima |
| 29/04/2023 20:49:30 | **Atalus confirma la ejecución exitosa** del ataque y modificación del perfil de la víctima |
| 03/05/2023 14:37:55 | Camillo recibe código de verificación de cuenta Xiaomi (141310) — actividad posterior |

---


## 8. Observaciones Finales

Este análisis muestra, mediante los hallazgos y capturas presentadas, la secuencia de acciones y la implicación de los participantes en el caso de ciberbullying. Se documenta la planificación, ejecución y resultado del ataque, así como la participación activa de Camillo Richbald y Atalus Grasstem. No se emiten valoraciones jurídicas ni conclusiones ejecutivas.

**6. Integridad de las evidencias**
La base de datos msgstore.db no presenta mensajes eliminados (tabla `message_revoked` vacía) ni mensajes editados (`message_edit_info` vacía), lo que refuerza la integridad del registro analizado.

---

## 9. Registro de Hashes de Hallazgos

| Archivo | Algoritmo | Hash |
|---|---|---|
| tabla.csv | MD5/SHA1 | Verificación previa al análisis (ver sección 6.1 del informe principal) |
| adb-backup-Camillo-Richbald.ab | SHA-256 | Pendiente de cálculo sobre archivo original |
| msgstore.db | SHA-256 | Pendiente de cálculo sobre archivo original |
| wa.db | SHA-256 | Pendiente de cálculo sobre archivo original |
| key | SHA-256 | Pendiente de cálculo sobre archivo original |
| IMG-20230429-WA0000.jpg (imagen enviada) | SHA-256 | `u46Rg0Dflo1uRpDNfRaRuyPVoBEUK7XoNsxvfrAxARo=` (file_hash en msgstore.db) |
| IMG-20230429-WA0000.jpg (cifrado) | SHA-256 | `HEQdU9BoPm/zP586Y6qi1He23GYd0kYcfF2YWG7nKck=` (enc_file_hash en msgstore.db) |
| IMG-20230429-WA0000.jpg (original) | SHA-256 | `7LoUYBtLqr5h5znesFYbZOU0MotgJHBIblaXmMeIkCM=` (original_file_hash en msgstore.db) |

---
---

## Anexo: Capturas relevantes

### Conversaciones clave extraídas
![Captura chat general](chats.png)
![Captura chat detalle 1](chat3.png)
![Captura chat detalle 2](chast2.png)
