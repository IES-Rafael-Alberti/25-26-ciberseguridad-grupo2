# Análisis del router Google OnHub

## Descripción del dispositivo

El archivo analizado corresponde al informe de diagnóstico de un router **Google OnHub** instalado en la vivienda. El dispositivo ejecuta **Chrome OS** versión `9460.40.5` en canal estable, sobre hardware **Qualcomm 8064**, identificado internamente como `whirlwind`.

**Datos principales**

- **Firmware:** `CHROMEOS_RELEASE_VERSION=9460.40.5`
- **Board:** `whirlwind-signed-mpkeys`
- **País de operación:** Estados Unidos (`dfs_fcc`, `_country._code: "us"`)
- **IP del router:** `192.168.86.1`
- **IP WAN:** `192.168.165.9`
- **Gateway WAN:** `192.168.165.1`
- **Velocidad de enlace WAN:** 100 Mbps

![Información principal](image.png)
![Información adicional](image-1.png)

---

## Red Wi-Fi

| Parámetro | Valor |
|---|---|
| SSID principal | `HOME` |
| SSID de invitados | `HOME-guest` |
| Estado de la red de invitados | Activa |
| Banda de 2,4 GHz | Canal 6, ancho HT20 |
| Banda de 5 GHz | Canal 36, ancho de 80 MHz |

La red de invitados estaba habilitada en el momento del diagnóstico, lo que implica que dispositivos externos podrían haberse conectado a Internet sin necesidad de acceder a la red principal.

---

## Servidores DNS configurados

```text
8.8.8.8        -> Google DNS primario
8.8.4.4        -> Google DNS secundario
210.115.225.11 -> DNS no estándar
```

La presencia del tercer servidor DNS, `210.115.225.11`, es atípica y no corresponde a un proveedor público de DNS habitual. La consulta geográfica muestra que la IP pertenece a **Corea del Sur (KR)**, por lo que conviene verificar si se trata de una configuración manual, heredada o no autorizada.

![Consulta geográfica del DNS](image-2.png)

---

## Evidencias de red

- `/proc/net/arp`

![Tabla ARP](image-3.png)

- Red inalámbrica principal

![Wireless network](image-4.png)

- Red de invitados

![Guest network](image-5.png)

- Red mesh

![Mesh network](image-6.png)

- Interfaces de red

![Interfaces de red 1](image-7.png)
![Interfaces de red 2](image-8.png)
![Interfaces de red 3](image-9.png)
![Interfaces de red 4](image-10.png)

---

## Dispositivos registrados en la red

### Dispositivos activos en el momento del diagnóstico

| Hostname | IP | Banda | OUI | Identificación |
|---|---|---|---|---|
| `ademanafe` | `192.168.86.29` | Wi-Fi 2,4 GHz | `2016d8` | Dispositivo no identificado |
| `*XDU` *(censurado)* | `192.168.86.22` | Wi-Fi 5 GHz | `18b430` | Desconocido |
| `dp-535302W5` | `192.168.86.21` | Wi-Fi 5 GHz | `a002dc` | Dispositivo no identificado |
| `st-***` *(censurado)* | `192.168.86.27` | Ethernet | `d052a8` | Desconocido |

### Dispositivos desconectados con registro histórico

| Hostname | Última conexión Unix | Última conexión UTC | OUI | Identificación |
|---|---|---|---|---|
| `osmc` | `1500271637` | 17 jul 2017, 15:33:57 UTC | `b827eb` | Raspberry Pi / Smart TV con Kodi |
| `android-***` | `1500272120` | 17 jul 2017, 15:35:20 UTC | `109266` | Dispositivo Android |
| `android-***` | `1500275536` | 17 jul 2017, 16:32:16 UTC | `1caf05` | Dispositivo Android |
| `android-***` | `1500272385` | 17 jul 2017, 15:39:45 UTC | `50f520` | Dispositivo Android |

---

## Hallazgos relevantes

### 1. El dispositivo OSMC estaba desconectado

El router registra `osmc` como no conectado, con última actividad el 17 de julio de 2017 a las 15:33:57 UTC. Este dato debe cruzarse con la información del `kodi.log` para construir una línea temporal consistente.

### 2. El hostname `ademanafe` es anómalo

El nombre `ademanafe` no sigue un patrón habitual de nomenclatura automática. El dispositivo estaba conectado por Wi-Fi en la banda de 2,4 GHz con IP `192.168.86.29`. Para identificarlo con mayor precisión conviene cruzar el OUI `2016d8` con una base de datos de fabricantes.

### 3. Existen varios dispositivos Android registrados

Tres dispositivos Android aparecen en el historial del router con nombres anonimizados (`android-***`), todos con última conexión dentro de un intervalo temporal muy próximo. Ninguno figuraba conectado en el momento del diagnóstico.

### 4. La red de invitados estaba activa

La red `HOME-guest` estaba habilitada, por lo que cualquier persona dentro del alcance del router podría haber obtenido acceso a Internet sin usar la contraseña de la red principal.

---

## Línea temporal extraída del OnHub

| Hora UTC | Evento |
|---|---|
| 17 jul 2017, 15:33:57 | Última conexión registrada del dispositivo `osmc` |
| 17 jul 2017, 15:35:20 | Última conexión de un dispositivo Android (`109266`) |
| 17 jul 2017, 15:39:45 | Última conexión de un dispositivo Android (`50f520`) |
| 17 jul 2017, 16:32:16 | Última conexión de un dispositivo Android (`1caf05`) |

---

## Conclusiones preliminares

- La Smart TV con Kodi (`osmc`) estaba desconectada de la red en el momento de generar el diagnóstico.
- La presencia de un DNS no estándar (`210.115.225.11`) es un punto de interés que debería revisarse.
- El dispositivo `ademanafe` requiere identificación, ya que su nombre no parece autogenerado y estaba activo en la red.
- La red de invitados activa abre la posibilidad de que hubiera dispositivos externos conectados sin quedar reflejados en la LAN principal.

---

## Resumen de conectividad

- Dispositivos conectados a `br-lan`: 9
- Dispositivo(s) conectado(s) a `wan0`: 1
- Dispositivos con IP fuera de rango: 2
- Dispositivo(s) conectados a `br-guest` (dentro del rango): 1

Estos conteos se han extraído de la tabla ARP y registros del router presentes en el volcado.
