# Informe Forense (Parte): Entorno Digital de la Víctima (Lassandra Cordalis)

**Proyecto:** AF · Proyecto 7

**Objeto del informe:** Documentar hallazgos derivados **exclusivamente** del análisis del entorno digital asociado a la víctima, con el fin de integrarlo posteriormente con los informes del resto del equipo.

> **Nota de integración (fuera de alcance de este documento):** Este informe debe correlacionarse con el análisis paralelo de CCTV/cámara y con los hallazgos sobre el posible cómplice (“Camillo”), para consolidar el informe pericial definitivo.

---

## 1. Resumen ejecutivo

Del análisis de evidencias atribuidas a la víctima se reconstruye una secuencia coherente de:

- **Escalada de acoso** por parte de un contacto identificado como **Atalus** (entorno académico), observada en comunicaciones y contexto de navegación.
- **Vinculación del sospechoso** al entorno personal de la víctima mediante rastros en **Contactos de Google** y huellas en **Telegram** (caché/strings).
- **Compromiso de cuenta** (Instagram) compatible con un acceso no habitual seguido de **alteración del perfil** (defacement) en un intervalo temporal muy corto.
- **Huella técnica** (metadatos EXIF) que permite vincular material multimedia a un identificador de dispositivo.

---

## 2. Alcance y limitaciones

**Alcance**

- Evidencias y artefactos asociados a la víctima (copias/volcados y exportaciones), con foco en: WhatsApp, historial web (Chrome/Google Takeout), Telegram (caché), e Instagram (actividad de cuenta y cambios de perfil).

**Limitaciones**

- Este documento **no atribuye culpabilidad definitiva**: describe hechos observables y su coherencia temporal.
- La identificación del vector exacto de obtención de credenciales se formula como **hipótesis técnica** y debe confirmarse por correlación con evidencias externas (p. ej., CCTV, artefactos físicos, peritaje del equipo informático intervenido).

---

## 3. Evidencias y material analizado

### 3.1. Evidencias gráficas incluidas (carpeta “Lassandra”)

**E-01** Conversación WhatsApp (captura/consulta de DB):

![Conversación WhatsApp](1.Conversación-whatsapp.png)

**E-02** Registro de inicio de sesión sospechoso en Instagram:

![Inicio de sesión sospechoso](2.inicio-sesion-sospechoso.png)

**E-03** Registro de cambios en el perfil (cambio de biografía):

![Cambios en el perfil](3.cambios-perfil.png)

**E-04** Estado del perfil de Instagram tras la alteración:

![Perfil tras el defacement](4.perfil-lassandra.png)

**E-05** Presencia de identificador “atalus” en Telegram (strings/caché):

![Atalus en Telegram](5.atalus-telegram.png)

**E-06** Registro de contacto en agenda de Google (vCard):

![Contacto en Google](6.contacto-atalus-google.png)

**E-07** Historial/búsqueda de la víctima relacionado con rechazo/amabilidad:

![Historial de búsquedas](7.historial-lassandra.png)

**E-08** Publicación de Instagram (evidencia de origen/fecha):

![Publicación clave](8.1.post-ig.png)

**E-09** Metadatos extraídos (EXIF) de la publicación:

![Metadatos EXIF](8.2.metadatos-post.png)

**E-10** Información de cámara (metadatos del dispositivo/captura):

![Información de cámara](9.info-camera.png)

---

## 4. Metodología (alto nivel)

- **Extracción y revisión** de artefactos (bases de datos, exportaciones, logs, capturas).
- **Búsqueda de indicadores** (nombres, identificadores, URLs, user agents) y su **correlación temporal**.
- **Validación cruzada**: contraste entre comunicaciones (WhatsApp), comportamiento de navegación (Chrome/Takeout) y actividad de cuenta (Instagram).
- **Análisis de metadatos** (EXIF) para identificación de dispositivo/origen en evidencias multimedia.

---

## 5. Cronología resumida (hechos observados)

| Fecha | Hecho | Evidencia |
|---|---|---|
| 26–27/04/2023 | Interacciones y escalada de tensión/acoso en WhatsApp; búsqueda de apoyo/asesoramiento para rechazar a alguien sin conflicto | E-01, E-07 |
| 28/04/2023 08:29 | Inicio de sesión no habitual en Instagram desde entorno Windows/Firefox (registro de actividad) | E-02 |
| 29/04/2023 12:44 | Acceso a Instagram desde terminal móvil identificado (S25 / Android 9) | E-02 |
| 29/04/2023 12:47 | Alteración del perfil (biografía) | E-03, E-04 |
| 30/04/2023 10:28 | Acceso considerado legítimo desde terminal de la víctima (Android 11 / 2201117TY) | E-02 |

---

## 6. Hallazgos

### 6.1. Acto 1 — El detonante y el acoso (WhatsApp + contexto de navegación)

La secuencia inicial se sitúa entre el **26 y 27 de abril de 2023**, donde se observan intentos insistentes de contacto por parte de **Atalus** y respuestas de la víctima buscando evitar conflicto.

Adicionalmente, el historial de navegación muestra una búsqueda explícita orientada a **cómo rechazar amablemente** a alguien, coherente con una intención de minimizar el impacto interpersonal.

![Historial de búsquedas](7.historial-lassandra.png)

**Extracto de mensajes (WhatsApp, evidencia representativa):**

| ID | Fecha y Hora (UTC) | Remitente | Mensaje |
|---:|---|---|---|
| 3 | 26/04/2023 15:12:36 | Atalus | Holita |
| 4 | 26/04/2023 15:12:50 | Lassandra | Holaa |
| 5 | 26/04/2023 15:12:53 | Lassandra | *(registro raw: “pp” / interpretación no concluyente)* |
| 6 | 26/04/2023 15:14:40 | Atalus | Soy Atalus, de clase 🤩 |
| 8 | 26/04/2023 15:17:13 | Atalus | Quieres ir a tomar algo un día de estos🫣 |
| 9 | 26/04/2023 15:18:08 | Lassandra | No puedo, tengo muchas prácticas atrasadas 😅 |
| 12 | 26/04/2023 15:18:45 | Atalus | Vamos el viernes, te parece? |
| 19 | 27/04/2023 16:26:31 | Atalus | Ayer te vi saliendo del cine 😡😡😡😡😡😡😡 |
| 20 | 27/04/2023 16:26:32 | Atalus | No era que estabas ocupada?😡 |
| 22 | 27/04/2023 16:31:32 | Atalus | Crees que soy tonto? |
| 25 | 27/04/2023 16:32:24 | Lassandra | No me controles |
| 26 | 27/04/2023 16:43:40 | Atalus | No te controlo, pero no me gusta que me vacilen |
| 27 | 27/04/2023 16:54:46 | Lassandra | Si que me controlas, y más q lo vi con mis propios ojos, y muchas amigas me dijeron que me estás acosando, quedas advertido. PESAO |

![Conversación WhatsApp](1.Conversación-whatsapp.png)

**Interpretación técnica (parcial):** la narrativa observada es compatible con una dinámica de presión y control previa al incidente de intrusión en redes sociales.

---

### 6.2. Acto 2 — Confirmación del vínculo (Google Contactos + Telegram)

Para descartar un atacante anónimo ajeno al entorno de la víctima, se buscaron trazas del sospechoso en otras fuentes:

- **Contactos de Google:** aparece el nombre del sospechoso en el vCard de “todos los contactos”, sugiriendo relación previa o presencia en agenda.
- **Telegram:** pese a no disponer de conversación persistente visible, se observa el identificador `|atalus;;;dR` mediante análisis de strings/caché sobre el volcado.

![Contacto en Google](6.contacto-atalus-google.png)

![Atalus en Telegram](5.atalus-telegram.png)

**Nota contextual:** no se observan coincidencias relevantes del nombre del presunto cómplice (“Camillo Richbald”) en estas fuentes de la víctima, lo cual sugiere ausencia de interacción directa o técnicas de ocultación.

---

### 6.3. Acto 3 — Hipótesis de compromiso de credenciales (acceso no habitual)

Se documenta un **inicio de sesión no habitual** en Instagram el **28/04/2023 a las 08:29** desde un entorno **Windows NT 10.0 / Firefox 72.0**. Posteriormente, el **29/04/2023 12:44** se registra un acceso desde un terminal móvil **Android 9 / S25**.

![Inicio de sesión sospechoso](2.inicio-sesion-sospechoso.png)

**Hipótesis técnica (a confirmar por correlación):** la obtención de credenciales podría haberse producido mediante acceso físico a un equipo con credenciales guardadas (p. ej., extracción local), dada la falta de señales claras en el historial de navegación de la víctima sobre descargas o malware. Esta hipótesis debe validarse con las evidencias externas del equipo.

---

### 6.4. Acto 4 — Intrusión y defacement en Instagram

Tras el acceso desde el terminal “S25”, se observa un cambio de perfil consistente con **defacement**: modificación de la biografía a un texto trivial (“Blablabla”), en un intervalo temporal estrecho.

![Cambios en el perfil](3.cambios-perfil.png)

![Perfil tras el defacement](4.perfil-lassandra.png)

**Lectura pericial (parcial):** la proximidad temporal entre el login y el cambio de perfil refuerza la relación entre el acceso y la acción de vandalización.

---

### 6.5. Acto 5 — Huella técnica (metadatos EXIF de publicación)

Se analizan metadatos de una publicación (archivo `343608201...webp`) atribuida al **27/04/2023 19:09**. En el volcado se observa un identificador de dispositivo/cámara del tipo `android-...`, útil para **cotejo** con terminales físicos en caso de intervención.

![Publicación clave](8.1.post-ig.png)

![Metadatos EXIF](8.2.metadatos-post.png)

Complementariamente, se incorpora la captura de “información de cámara” (metadatos del dispositivo) como soporte del rastro técnico.

![Información de cámara](9.info-camera.png)

---

## 7. Conclusiones (parciales) y puntos para correlación

- Existen indicios consistentes de **acoso previo** y escalada emocional antes del incidente de Instagram.
- El sospechoso (Atalus) está **vinculado al entorno digital** de la víctima (contactos y trazas en caché).
- Los registros de Instagram muestran una **secuencia de accesos** y una **alteración del perfil** compatible con acceso no autorizado.
- El análisis de metadatos aporta una **huella técnica** susceptible de comparación con dispositivos físicos.

**Puntos a correlacionar con el resto del equipo**

- Confirmación del vector de obtención de credenciales (evidencia CCTV, artefactos físicos, trazas en equipos del centro).
- Correlación de IPs/identificadores con otros hallazgos de infraestructura/red.

---

## 8. Cadena de custodia e integridad (hashes)

Los artefactos principales analizados constan en el documento de integridad: `hashes_evidencias.txt`.

**Resumen (hash → archivo):**

```text
40e6f12cf248468c2849aa2c8094d186b0264bb758d4839ee190486721da013a  adb-backup-Lassandra-Cordalis.ab
1beec3df0227eb8d26fc5810411a350fb62761b469fc380074d8978a7a048469  imagen-sd.ad1
0e02fce437a698421c947b87c642704109d3d839d1a64ac1b365de1662cd3056  Telegram-Data-Lassandra-Cordalis.zip
0a1989aeae247aaba70621795127d0b8de6be5d84e1a592269d457432c3c4ffa  Google-Data-Lassandra-Cordalis.zip
07d015c094f37433e5f33634154544fc8d020c98cec038d32cab09e9d7e048f2  Instagram-lassandracordalis-20230504.zip
83b83a02e748e322933bbe29d98bdf8c21af8fd5457185a9d5ee903f9079e3c5  WhatsApp-Database-Lassandra-Cordalis.zip
```

---

**Fin del informe (Parte – Lassandra).**
