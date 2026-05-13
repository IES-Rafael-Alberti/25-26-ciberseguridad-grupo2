# Análisis de los registros JSON de Amazon Echo (Alexa)
**Caso:** Murder Investigation — 17 de julio de 2017  
**Dispositivo:** Amazon Echo | Tipo: AB72C64C86AW2 | S/N: B0F00715535302W5  
**Cuenta registrada:** A32TRBM6QOXJ5H  
**Zona horaria del caso:** UTC+9 (Corea del Sur)

---

## Línea temporal de actividad

| Hora (UTC+9) | JSON | Estado | Comando / Evento |
|---|---|---|---|
| 11:57:35 | 14 | INVALID | *"alexa kinda patton a.m. next monday"* — comando no reconocido |
| 12:07:00 | 15 | DISCARDED¹ | Sonido ambiental captado — no dirigido al dispositivo |
| 14:31:04 | 16 | DISCARDED¹ | Sonido ambiental captado — no dirigido al dispositivo |
| 14:45:29 | 17 | SUCCESS | Wake word "Alexa" detectado |
| **14:45:31** | **13** | **SUCCESS** | **"Wake up" → Alexa responde: *"Good Morning! Today is National Ice Cream Day"*** |
| 14:45:43 | 18 | SUCCESS | "Alexa stop" |
| 15:01:54 | 12 | SUCCESS | Wake word "Alexa" detectado |
| **15:01:55** | **11** | **SUCCESS** | **"Turn on TV"** |
| 15:06:03 | 10 | SUCCESS | Wake word "Alexa" detectado |
| **15:06:06** | **9** | **SUCCESS** | **"Turn on Pandora" → Pandora Station activada** |
| **15:12:39** | **8** | **SUCCESS** | **"Alexa how could you do this what are the flooding" → Alexa no comprende** |
| 15:12:58 | 6 | SUCCESS | Wake word "Alexa" detectado |
| **15:13:02** | **5** | **SUCCESS** | **"Stop"** |
| 15:20:05 | 4 | SUCCESS | Wake word "Alexa" detectado |
| **15:20:07** | **3** | **SUCCESS** | **"Turn off TV"** |
| 15:20:32 | 2 | SUCCESS | Wake word "Alexa" detectado |
| **15:20:34** | **1** | **SUCCESS** | **"Who is Yes?" → Alexa responde sobre la banda Yes** |

¹ `DISCARDED_NON_DEVICE_DIRECTED_INTENT`: el micrófono captó audio ambiente pero Alexa determinó que no era un comando dirigido al dispositivo. Estos registros indican que había actividad sonora en el hogar en esos momentos.

---

## Hallazgos destacados

### 1. El dispositivo estaba activo toda la mañana del día del crimen
La primera interacción registrada es a las **11:57**, y la última a las **15:20**. Esto demuestra que el Echo estaba en funcionamiento y conectado a internet durante todo el período relevante.

### 2. Sonidos ambientales captados a las 14:31 (JSON 16)
A las **14:31:04** el micrófono de Alexa captó audio que no fue interpretado como un comando. Esto es forense­mente significativo: el dispositivo estaba escuchando activamente y registró actividad sonora en el hogar **29 minutos antes** de que se encendiera la TV (15:01).

### 3. Secuencia crítica entre 15:12 y 15:13 — posible momento del crimen
A las **15:12:39** alguien dice: *"Alexa, how could you do this, what are the flooding"*. Alexa registra el texto completo pero no comprende la pregunta. El audio transcrito (WAV 7 y 8) incluye frases como:
- *"I can't believe you would do this to me"*
- *"How could you do this? What are you thinking?"*
- *"Stop"*

Estas frases, captadas por el micrófono de Alexa a las **15:12–15:13**, son consistentes con una confrontación o altercado. El comando "Stop" a las 15:13:02 pone fin a ese episodio.

### 4. La TV se apaga a las 15:20 — inconsistencia con el testimonio del marido
El marido declaró estar viendo una película en el dormitorio con auriculares. Sin embargo:
- La TV se **encendió** a las **15:01:55** mediante Alexa.
- La TV se **apagó** a las **15:20:07** mediante Alexa.
- Si el marido estaba en el dormitorio con auriculares, ¿quién encendió y apagó la TV del salón mediante Alexa?

### 5. Música de Pandora activa durante el período crítico
A las **15:06:06** se activa Pandora a través de Alexa. El marido declaró que no escuchó nada porque *"su mujer había puesto música"*. Los registros confirman que la música se activó mediante Alexa a las 15:06, pero no aclaran quién dio la orden.

### 6. Comando "Wake up" a las 14:45 — inicio de la secuencia
La actividad se reanuda a las 14:45 con un saludo matutino solicitado a Alexa. Esto sitúa a alguien interactuando con el dispositivo desde al menos las 14:45, **antes** de la hora de llegada declarada (~15:00).

---

## Metadatos técnicos del dispositivo

| Campo | Valor |
|---|---|
| Device Type | AB72C64C86AW2 (Amazon Echo 1ª generación) |
| Serial Number | B0F00715535302W5 |
| Customer ID | A32TRBM6QOXJ5H |
| Todos los registros | 2017-07-17 |
| Formato timestamp | Unix epoch en milisegundos |

---

## Conclusión

Los registros JSON de Alexa constituyen una evidencia digital de alto valor. La secuencia de comandos entre las **15:01 y 15:20** muestra interacción activa con el dispositivo durante la ventana horaria del crimen. La frase registrada a las **15:12:39** (*"how could you do this"*) y los audios WAV asociados (7 y 8) sugieren que Alexa captó audio de una confrontación. La apagada de la TV a las 15:20 coincide con el período en que el marido afirma haber encontrado a la víctima.
