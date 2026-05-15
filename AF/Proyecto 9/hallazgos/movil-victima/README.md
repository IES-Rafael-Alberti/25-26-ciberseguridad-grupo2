# Informe Forense — Smartphone de la Víctima
**Caso:** Proyecto 9 — Murder Investigation  
**Dispositivo:** Samsung SHV-E250L (Galaxy Note II LTE)  
**Fecha del crimen:** 17 de julio de 2017  
**Analistas:** — Pablo González Silva

---

## 1. Cadena de custodia y verificación de integridad

El volcado físico fue adquirido en formato `.mdf` dividido en 7 particiones correspondientes a las distintas áreas de almacenamiento del dispositivo:

| Partición | Archivo | Relevancia forense |
|---|---|---|
| USERDATA | `SHV-E250L_Physical_20170717_USERDATA.mdf` |  Alta — apps, datos de usuario |
| SYSTEM | `SHV-E250L_Physical_20170717_SYSTEM.mdf` | Media — configuración del sistema |
| CACHE | `SHV-E250L_Physical_20170717_CACHE.mdf` | Media — datos temporales |
| EFS | `SHV-E250L_Physical_20170717_EFS.mdf` | Media — IMEI, identificadores |
| RADIO | `SHV-E250L_Physical_20170717_RADIO.mdf` | Baja — firmware de radio |
| TOMBSTONES | `SHV-E250L_Physical_20170717_TOMBSTONES.mdf` | Baja — logs de crashes |
| HIDDEN | `SHV-E250L_Physical_20170717_HIDDEN.mdf` | Baja — partición del fabricante |

El análisis se realizó sobre la partición USERDATA mediante **Autopsy 4.22.1**, cargando el archivo `.mdf` directamente como imagen de disco. Las bases de datos SQLite extraídas fueron analizadas con **DB Browser for SQLite**.

---

## 2. Identificación del dispositivo

| Campo | Valor |
|---|---|
| Modelo | **SHV-E250L** (Samsung Galaxy Note II LTE) |
| Fabricante | Samsung |
| Identificador interno | `t0ltelgt` |
| Sistema operativo | Android 4.4.2 (KitKat) |
| Número de serie | `4300d1b15be8b061` |
| Procesador | Exynos 4 |
| Fecha de compilación | 24 de agosto de 2015, 16:27:41 KST |
| Nombre del dispositivo | **Betty** (SHV-E250L) |
| Cuenta de correo principal | **bettyhallym@gmail.com** |
| Región configurada | Reino Unido (en-GB) |
| Idioma del sistema | Inglés |
| Idiomas del teclado | Coreano, Inglés estadounidense |
| Zona horaria | Asia/Seúl (KST = UTC+9) |
| Sistema de seguridad Knox | **Activado** |

El nombre del dispositivo ("Betty") y la cuenta de correo confirman que el terminal pertenece a la víctima, **Hallym Betty**. La zona horaria UTC+9 es consistente con la localización del crimen en Corea del Sur.

---

## 3. Cuenta de Google y actividad del día del crimen

Extraído del archivo `com.android.chrome_preferences.xml`:

```
google.services.username: bettyhallym@gmail.com
pref_last_custom_tab_url: https://www.amazon.com/Bach-Complete-Organ/dp/B001R3YJS8
chrome_to_mobile_last_updated_timestamp: 2017-07-12T08:52:25.000Z
```

La última URL visitada en Chrome corresponde a una búsqueda en Amazon de **"Bach Complete Organ Works"**, una recopilación de música de órgano de Johann Sebastian Bach. Este dato es forense­mente relevante porque el marido declaró que *"su mujer había puesto música"*: la víctima había estado buscando música en Amazon días antes del crimen.

### Sincronización de Google el día del crimen

Extraído de los datos de `com.google.android.gms`:

| Evento | Hora (UTC+9) |
|---|---|
| Último sync de contactos completo | 2017-07-14 18:17:39 |
| **Sync inicio el día del crimen** | **2017-07-17 15:05:50** |
| **Sync fin el día del crimen** | **2017-07-17 15:05:52** |

El teléfono de la víctima realizó una sincronización con los servidores de Google a las **15:05:50 UTC+9**, apenas 5 minutos después de la hora de llegada declarada por el marido (~15:00). Esto demuestra que el dispositivo estaba **activo y con conexión a internet** en ese momento, lo que es consistente con que la víctima estaba viva y en el hogar a esa hora.

---

## 4. Aplicaciones instaladas (destacadas)

Extraído de la carpeta `app/` del USERDATA:

| Aplicación | Package | Relevancia |
|---|---|---|
| Amazon Alexa | `com.amazon.dee.app` | Vinculación con el Echo del hogar |
| Nest | `com.nest.android` | Cámaras de seguridad |
| Dropbox | `com.dropbox.android` | Almacenamiento en nube |
| Google Hangouts | `com.google.android.talk` | Mensajería |
| Chrome | `com.android.chrome` | Navegador principal |
| Samsung Apps | `com.sec.android.app.samsungapps` | — |

**Ausencias significativas:**

- No se encontró **WhatsApp** (`com.whatsapp`) instalado.
- No se encontró **Samsung SmartThings** (`com.samsung.android.smartthings`) instalado.

La ausencia de SmartThings en el teléfono de la víctima es relevante: el control del ecosistema inteligente del hogar (sensores, TV, rutinas) estaba en manos del marido, no de la víctima.

### App de Nest — Token expirado

El archivo `cache1952722469.json` de la app Nest contenía un token de acceso con los siguientes datos:

```json
"userid": "6584791",
"email": "bettyhallym@gmail.com",
"access_token_expiration_secs": 1499912348
```

El token expiraba el **13 de julio de 2017 a las 11:19 UTC+9**, cuatro días antes del crimen. Esto indica que la app Nest **no estaba autenticada** el día del asesinato, lo que podría explicar por qué no hay grabaciones de cámaras Nest disponibles como evidencia.

---

## 5. Dispositivos Bluetooth vinculados

Extraído de `USERDATA/misc/bluedroid/bt_config.xml`:

| Dispositivo | Nombre | MAC |
|---|---|---|
| Amazon Echo | Echo-2W5 | `74:c2:46:88:5d:09` |
| Teléfono del marido | **Simon** (SHV-E250S) | `50:f5:20:a5:7d:cc` |
| Pulsera inteligente | MI1A | `88:0f:10:f6:c8:b7` |

**Hallazgos relevantes:**

El teléfono del marido aparece registrado en el historial Bluetooth del teléfono de la víctima con el nombre **"Simon"**, confirmando la identidad del marido. El modelo del teléfono del marido es **SHV-E250S**, variante del mismo Samsung Galaxy Note II pero para un operador diferente.

La pulsera **MI1A** corresponde a una Xiaomi Mi Band 1A, identificada en la escena del crimen en el suelo junto a la víctima. Sus datos biométricos (frecuencia cardíaca, pasos, actividad) podrían proporcionar información sobre el estado físico de la víctima en el momento del crimen.

El Amazon Echo (`Echo-2W5`) también aparece como dispositivo Bluetooth conocido, confirmando que la víctima había interactuado con el Echo directamente.

### Actividad Bluetooth el 13 de julio

Extraído de `message_store.db` (archivo XML de preferencias Bluetooth):

```xml
last_discovering_time: 2017-07-13 17:10:47 UTC+9
discoverable_end_timestamp: 2017-07-13 16:40:30 UTC+9
```

El teléfono estuvo en modo búsqueda Bluetooth el **13 de julio a las 17:10 UTC+9**, cuatro días antes del crimen.

---

## 6. Análisis de bases de datos

### 6.1 SMS y mensajería (mmssms.db)

La base de datos `mmssms.db` de `com.android.providers.telephony` fue extraída y analizada. Las tablas `sms`, `pdu` (MMS) y `threads` estaban **vacías**. No se encontraron mensajes de texto ni MMS almacenados en el dispositivo.

La ausencia total de SMS es inusual en un dispositivo en uso activo y podría indicar borrado previo de mensajes.

### 6.2 Llamadas

La tabla `calls` no existe en `mmssms.db`. La base de datos `contacts2.db` de `com.android.providers.contacts` fue accedida pero no contenía registros de llamadas.

### 6.3 Correo electrónico (EmailProvider.db)

Accedido desde `USERDATA/data/com.android.email/databases/EmailProvider.db`. Se encontraron correos redactados en **coreano**, cuyo contenido tras traducción resultó ser de carácter rutinario:

- *"Te llamaré más tarde."*
- *"Gracias por enviarme el correo electrónico."*
- *"Por favor, contáctame a mi teléfono móvil."*
- *"Vamos a almorzar juntos."*
- *"Avísame más tarde."*

No se encontró contenido relevante para la investigación en los correos analizados.

### 6.4 Historial del navegador

La base de datos del navegador no contenía registros de historial de navegación. La última URL registrada fue obtenida de las preferencias de Chrome (`pref_last_custom_tab_url`), no del historial propiamente dicho.

---

## 7. Aplicaciones descargadas

Extraído de la carpeta `downloads/`:

| Archivo | Fecha | Relevancia |
|---|---|---|
| `com.amazon.dee.app_2017-07-03.apk` | 3 julio 2017 | APK de Amazon Alexa descargado manualmente |
| `com.nest.android-5.0.0.25.apk` | 12 julio 2017 | APK de Nest descargado 5 días antes del crimen |

La víctima descargó manualmente el APK de Nest el **12 de julio**, un día antes de que expirara el token de la app. Esto sugiere que intentaba reinstalar o actualizar la aplicación.

---

## 8. Datos de Facebook

Se accedió a la carpeta `facedata` del dispositivo. Los archivos encontrados estaban corruptos o inaccesibles. No fue posible extraer datos de actividad de Facebook del dispositivo.

No se encontró la aplicación de Facebook instalada (`com.facebook.katana` no estaba presente en el USERDATA).

---

## 9. Correlación con otras evidencias

| Dato del móvil | Correlación |
|---|---|
| Sync Google a las 15:05:50 | Víctima viva y con conexión a las 15:05, 7 minutos antes de la confrontación captada por Alexa (15:12) |
| SmartThings ausente en el móvil de la víctima | El control del hogar inteligente era exclusivo del marido |
| Token Nest expirado el 13 de julio | Sin grabaciones de cámaras disponibles para el día del crimen |
| Bluetooth con "Simon" (SHV-E250S) | Confirma identidad del marido y proximidad física reciente |
| Pulsera MI1A vinculada | Datos biométricos de la víctima pendientes de análisis en la smartband |
| APK de Alexa instalado | La víctima tenía la app de Alexa, pero no SmartThings |
| SMS y llamadas vacíos | Posible borrado de comunicaciones previo al crimen |
| Última URL: música Bach en Amazon | Consistente con la narrativa del marido sobre la música, pero la búsqueda es de días anteriores |

---

## 10. Conclusiones

El análisis forense del smartphone de la víctima (Samsung SHV-E250L, Betty Hallym) ha permitido establecer los siguientes hechos:

**Confirmados:**

- La víctima es **Hallym Betty**, con cuenta Google `bettyhallym@gmail.com`.
- El teléfono estaba activo y conectado a internet a las **15:05:50 UTC+9** del día del crimen, lo que sitúa a la víctima con vida al menos hasta esa hora.
- El marido (**Simon**) tenía su teléfono (SHV-E250S) emparejado por Bluetooth con el de la víctima, confirmando su identidad y convivencia.
- La víctima **no tenía SmartThings instalado**, lo que significa que el control del ecosistema inteligente del hogar (sensores, TV, rutinas de Alexa) era responsabilidad exclusiva del marido.

**Indicios relevantes:**

- La **ausencia total de SMS y llamadas** en un dispositivo en uso activo es anómala y podría indicar borrado deliberado.
- El **token de Nest expirado** cuatro días antes del crimen elimina las cámaras de seguridad como fuente de evidencia visual.
- Los **datos biométricos de la pulsera MI1A** (encontrada en el suelo junto a la víctima) aún no han sido analizados y podrían proporcionar información crítica sobre la hora exacta del fallecimiento.

---

## Anexo — Herramientas y metodología

| Fase | Herramienta | Uso |
|---|---|---|
| Análisis del volcado | Autopsy 4.22.1 | Carga del USERDATA.mdf, navegación del sistema de archivos |
| Análisis de bases de datos | DB Browser for SQLite | Consultas SQL sobre mmssms.db, contacts2.db, EmailProvider.db |
| Análisis de preferencias XML | Lectura manual | chrome_preferences.xml, bt_config.xml, message_store.db |
| Análisis de JSON | Python (conversión de timestamps) | cache1952722469.json (Nest), datos de Google sync |
