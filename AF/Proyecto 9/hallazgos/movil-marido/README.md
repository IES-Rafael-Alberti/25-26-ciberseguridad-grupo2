Modelo: Samsung Galaxy Note II (SHV-E250L)
Sistema Operativo: Android 4.4.2
Firmware: E250LKLUKOH4

# Índices

## Índice de hallazgos (evidencias)

- `bt_config.xml` (Bluedroid): [archivos/bt_config.xml](archivos/bt_config.xml)
- Capturas de apoyo: [img/](img/)

## Índice de figuras

- [Figura 1 — Modelo del dispositivo](#figura-1--modelo-del-dispositivo)
- [Figura 2 — Cuentas (accounts)](#figura-2--cuentas-accounts)
- [Figura 3 — Propietario (owners)](#figura-3--propietario-owners)
- [Figura 4 — Apps/servicios (listado 1)](#figura-4--appsservicios-listado-1)
- [Figura 5 — Apps/servicios (listado 2)](#figura-5--appsservicios-listado-2)
- [Figura 6 — Mensajes/correos (messages)](#figura-6--mensajescorreos-messages)

---

# Hallazgos (para integrar en informe mayor)

## Hallazgos más importantes

- Identidad/cuenta: aparece la cuenta **`simonhallym@gmail.com`** y el nombre mostrado **"Hallym Simon"**.
- Domótica/IoT: evidencias de uso/vinculación con **SmartThings** y presencia de **Commands for Alexa**; correos asociados a **Nest**.
- Bluetooth: adaptador local con nombre **"Simon (SHV-E250S)"** y MAC **50:F5:20:A5:7D:CC**; emparejamientos con **Amazon Echo (Echo-2W5)** y **LG HBS900** con LinkKey presente en Bluedroid.

## Bluetooth (Bluedroid Android 4.x)

Evidencia: [archivos/bt_config.xml](archivos/bt_config.xml)

### Adaptador local

| Campo               | Valor                            | Interpretación forense                                  |
| ------------------- | -------------------------------- | ------------------------------------------------------- |
| Nombre dispositivo  | Simon (SHV-E250S)                | Samsung Galaxy Note II variante coreana SHV-E250S       |
| Dirección Bluetooth | 50:F5:20:A5:7D:CC                | MAC Bluetooth única del dispositivo analizado           |
| BluezMigrationDone  | 1                                | El sistema migró desde stack BlueZ a Bluedroid          |
| ScanMode            | 0                                | Bluetooth posiblemente no visible/discoverable          |
| DiscoveryTimeout    | 120                              | Tiempo de descubrimiento Bluetooth configurado en 120 s |

Notas (bt_config.xml):
- Se observan claves locales BLE (`LE_LOCAL_KEY_*`), indicio de configuración/uso de Bluetooth LE (útil para correlación y resolución de direcciones en algunos escenarios).

---

### Dispositivos remotos detectados/emparejados

| MAC               | Nombre            | Tipo probable                         | DevType | Clase Bluetooth (decimal) | Timestamp Unix | Fecha aprox. UTC        | Emparejado  | Link Key | Fabricante | Servicios detectados                                     | Observaciones forenses                                                           |
| ----------------- | ----------------- | ------------------------------------- | ------- | ------------------------- | -------------- | ----------------------- | ----------- | -------- | ---------- | -------------------------------------------------------- | -------------------------------------------------------------------------------- |
| 1C:AF:05:9E:19:74 | Betty (SHV-E250L) | Samsung Galaxy Note II                | 1       | 5898764                   | 1499931533     | 2017-07-13 04:58:53 UTC | No evidente | No       | N/D        | N/D                                                      | Posible segundo terminal Samsung asociado al usuario. Variante coreana SHV-E250L |
| 74:C2:46:88:5D:09 | Echo-2W5          | Amazon Echo                           | 1       | 787476                    | 1500194150     | 2017-07-16 05:55:50 UTC | Sí          | Sí       | 69         | A2DP, AVRCP, Handsfree/Audio sink y servicio propietario | Dispositivo claramente emparejado. Conserva LinkKey válida                       |
| 4A:C3:55:48:C7:77 | Desconocido       | BLE aleatorio                         | 1       | N/D                       | N/D            | N/D                     | No evidente | No       | N/D        | N/D                                                      | Dirección aleatoria BLE (AddrType=1). Posible beacon o dispositivo temporal      |
| 88:0F:10:F6:C8:B7 | MI1A              | Xiaomi Mi Band/Mi device              | 2       | 7936                      | 1500194153     | 2017-07-16 05:55:53 UTC | No claro    | No       | N/D        | N/D                                                      | Dispositivo BLE. Posible wearable Xiaomi                                         |
| B8:AD:3E:01:5B:6A | LG HBS900         | Auriculares Bluetooth LG Tone Infinim | 1       | 2360324                   | 1500193456     | 2017-07-16 05:44:16 UTC | Sí          | Sí       | 10         | Serial Port, Headset, Handsfree, A2DP, AVRCP             | Headset estéreo claramente emparejado y usado                                    |

Notas (bt_config.xml):
- Para **Echo-2W5** y **LG HBS900** hay `LinkKey` almacenada y lista de UUIDs de servicio, lo que refuerza que existió emparejamiento y uso.

---

### Blacklists configuradas

#### ExactNameBlacklist

| Nombre bloqueado |
| ---------------- |
| Motorola IHF1000 |
| i.TechBlueBAND   |
| X5 Stereo v1.3   |
| KML_CAN          |

#### PartialNameBlacklist

- BMW
- Audi
- Parrot
- Car / CAR

Otros (ver evidencia):
- `AddressBlacklist`: lista extensa de MAC (consultar directamente [archivos/bt_config.xml](archivos/bt_config.xml)).
- `FixedPinZerosKeyboardBlacklist`: `00:0F:F6`.

---

## Cuentas, propietario, apps y mensajes (capturas)

### Figura 1 — Modelo del dispositivo

![](img/0-modelo-movil.png)

Hallazgos:
- Referencia al modelo **SHV‑E250L**, coherente con el contexto del terminal analizado.

Interpretación forense:
- Permite acotar **artefactos esperables** por versión/época (Android 4.x, TouchWiz), rutas típicas y compatibilidad de apps.
- Ayuda a justificar por qué aparecen determinados componentes/servicios (p. ej., stack Bluedroid, Samsung Push/servicios Samsung, etc.).

Correlaciones útiles:
- Contrastar el **modelo/variante** con los identificadores que aparecen en otros artefactos (nombre Bluetooth, firmware, apps de operador/región).
- Si más adelante se integra en un informe mayor, esta figura sirve como **evidencia de contexto técnico** (no como prueba de uso por sí sola).

---

### Figura 2 — Cuentas (accounts)

![](img/contactos-cuentas.png)

Hallazgos:
- Se observa la cuenta **`simonhallym@gmail.com`** con tipo `com.google`.
- Se observa la misma cuenta vinculada a **SmartThings** (`com.smartthings.android`).

Interpretación forense:
- `com.google` indica que el dispositivo tenía configurada una **cuenta Google activa**, normalmente usada para sincronización (contactos, calendario, apps) y servicios (Play Services, Gmail, Drive).
- La presencia de `com.smartthings.android` sugiere **vinculación del dispositivo a una plataforma de domótica/IoT**; esto suele implicar tokens de sesión, logs de conexión y potencial inventario de dispositivos asociados (según datos disponibles).

Correlaciones útiles:
- La cuenta `simonhallym@gmail.com` aparece en varias capturas: sirve como **clave de unión** entre propietario (Figura 3), listado de apps (Figuras 4–5) y mensajes (Figura 6).
- Conectar esta evidencia con Bluetooth (p. ej., Echo) ayuda a sostener un **escenario IoT** (SmartThings/Alexa/Nest) aunque las fuentes sean distintas.


---

### Figura 3 — Propietario (owners)

![](img/nombre-simon.png)

Hallazgos:
- `account_name`: **`simonhallym@gmail.com`**.
- `display_name`: **"Hallym Simon"**.
- Presencia de `gaia_id` (identificador interno asociado a la cuenta Google).

Interpretación forense:
- `display_name` aporta un **nombre mostrado** asociado al perfil; es útil para contexto, pero no siempre es único ni verificable sin más fuentes.
- `gaia_id` suele ser un identificador **estable** ligado a la cuenta Google; es especialmente útil para correlacionar registros donde el correo no aparezca pero sí el ID.

Correlaciones útiles:
- Cruzar `gaia_id` con otros artefactos que lo almacenen (apps Google/servicios) puede fortalecer la **atribución técnica**.

---

### Figura 4 — Apps/servicios (listado 1)

![](img/apps-instalads.png)

Hallazgos (muestra):
- Ecosistema Google/Samsung (Google, Gmail, Hangouts, Samsung Security Policy Update, etc.).
- Servicios cloud/terceros: **Dropbox**, **IFTTT**.
- Domótica/IoT: **SmartThings Mobile**.

Interpretación forense:
- La combinación **Dropbox + servicios Google** apunta a uso de **almacenamiento cloud**, relevante para búsqueda de copias, sincronizaciones y posibles transferencias de ficheros.
- **IFTTT** suele indicar automatizaciones (disparadores/acciones) que pueden conectar apps/servicios (p. ej., domótica, notificaciones, backups). Esto puede ser clave para explicar eventos automáticos sin intervención manual directa.
- La presencia de múltiples servicios Samsung/Google también sugiere un dispositivo con **cuenta principal** bien integrada y con servicios en segundo plano.

Correlaciones útiles:
- Si existen logs de red/router, buscar conexiones a dominios típicos de Dropbox/Google/IFTTT para asociar ventanas de actividad.
- Relacionar SmartThings + IFTTT con los indicios IoT (Figura 6: Nest; Bluetooth: Echo) para consolidar el **ecosistema de dispositivos**.

---

### Figura 5 — Apps/servicios (listado 2)

![](img/apps-instaladas2.png)

Hallazgos (muestra):
- Presencia de **Commands for Alexa**.
- Presencia de **Google Drive** y **Google Play services** (servicios de sincronización/soporte habituales del ecosistema Google).
- Presencia de **Samsung Push Service**.

Interpretación forense:
- **Commands for Alexa** sugiere interacción con el ecosistema Alexa (skills/comandos). Esto es relevante si el caso busca evidencias de control de dispositivos/escenas o actividad domótica.
- **Google Drive** incrementa la probabilidad de existencia de documentos/fotos sincronizados, y de trazas locales (caché, metadatos de ficheros, cuentas).
- **Samsung Push Service** indica un canal de notificaciones/servicios Samsung que puede dejar rastros de registro y asociarse a cuentas Samsung (si existieran en el conjunto de evidencias).

Correlaciones útiles:
- La presencia de Alexa se alinea con el emparejamiento Bluetooth de un **Echo** (en `bt_config.xml`), reforzando coherencia del entorno.

---

### Figura 6 — Mensajes/correos (messages)

![](img/correos.png)

Hallazgos (muestra):
- Destinatario repetido: **`<simonhallym@gmail.com>`**.
- Correos de **Nest** (`news@nest-email.com`) y **Pandora** (`pandora@pandora.com`).
- Correos de servicios Samsung (contenido en coreano).

Interpretación forense:
- Estos mensajes son indicios de **alta/uso de servicios** (Nest, Pandora) vinculados a la cuenta observada; pueden servir para justificar la pertenencia al ecosistema IoT/entretenimiento.
- Si el origen de `messages` incluye timestamps (no visibles en esta captura), permitiría construir una **línea temporal** de onboarding/actividad.

Correlaciones útiles:
- Nest (correo) + SmartThings/Alexa (apps) + Echo (Bluetooth) permiten plantear un escenario consistente de **hogar conectado**.
