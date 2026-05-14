# Proyecto 9
1. [Juramento y declaración de abstención](#1-juramento-y-declaración-de-abstención)
2. [Palabras clave](#2-palabras-clave)
3. [Índice de figuras](#3-índice-de-figuras)
4. [Resumen Ejecutivo](#4-resumen-ejecutivo)
5. [Introducción](#5-introducción)
   1. [Antecedentes](#51-antecedentes)
   2. [Objetivos](#52-objetivos)
6. [Fuentes de información](#6-fuentes-de-información)
   1. [Comprobación de hashes (MD5 y SHA-1)](#61-comprobación-de-hashes-md5-y-sha-1)
   2. [Adquisición de hallazgos](#62-adquisición-de-hallazgos)
7. [Análisis](#7-análisis)
   1. [Herramientas utilizadas](#71-herramientas-utilizadas)
   2. [Cronología del ataque](#76-cronología-del-ataque)
   3. [Google OnHub](#72-google-onhub)
   4. [TV Intelligence](#73-tv-intelligence)
8. [Limitaciones](#8-limitaciones)
9. [Conclusiones](#9-conclusiones)
11. [Anexo 2. Cadena de custodia](#11-anexo-2-cadena-de-custodia)
12. [Anexo 3. Otras necesidades](#12-anexo-3-otras-necesidades)
    1. [Índice de hallazgos](#121-índice-de-hallazgos)

## 1. Juramento y declaración de abstención

Los peritos abajo firmantes manifiestan, bajo juramento o promesa de decir verdad, que han actuado y actuarán con la mayor objetividad posible, considerando tanto lo que pueda favorecer como lo que pueda perjudicar a cualquiera de las partes. Asimismo, declaran conocer las sanciones penales en las que podrían incurrir si incumplen su deber como peritos.

En cumplimiento de las mejores prácticas y estándares de la industria, los peritos declaran expresamente:

- Que no existe conflicto de interés alguno que pueda comprometer la objetividad del presente informe.
- Que no tienen parentesco, vínculo matrimonial o situación de hecho asimilable con ninguna de las partes, ni con sus abogados o procuradores.
- Que no tienen interés directo ni indirecto en el objeto del pleito ni en su resolución.
- Que no han prestado servicios profesionales anteriormente a ninguna de las partes en relación directa con este caso.

## 2. Palabras clave

Google OnHub, Chrome OS, DNS, red Wi-Fi, ARP, Kodi, OSMC, TV inteligente, Bluetooth, hash, análisis forense, evidencias digitales.

## 3. Índice de figuras

| Nº   | Figura                                                          | Descripción                                              |
| ---- | --------------------------------------------------------------- | -------------------------------------------------------- |
| 3.1  | ![Fig. 3.1](hallazgos/google-on-hub/image.png)                 | Información principal del router Google OnHub           |
| 3.2  | ![Fig. 3.2](hallazgos/google-on-hub/image-1.png)               | Información adicional del router                         |
| 3.3  | ![Fig. 3.3](hallazgos/google-on-hub/image-2.png)               | Consulta geográfica del DNS no estándar                  |
| 3.4  | ![Fig. 3.4](hallazgos/google-on-hub/image-3.png)               | Tabla ARP del router                                     |
| 3.5  | ![Fig. 3.5](hallazgos/google-on-hub/image-4.png)               | Red inalámbrica principal                                |
| 3.6  | ![Fig. 3.6](hallazgos/google-on-hub/image-5.png)               | Red de invitados                                         |
| 3.7  | ![Fig. 3.7](hallazgos/google-on-hub/image-6.png)               | Red mesh del entorno                                     |
| 3.8  | ![Fig. 3.8](hallazgos/google-on-hub/image-7.png)               | Interfaces de red del dispositivo                        |
| 3.9  | ![Fig. 3.9](hallazgos/google-on-hub/image-8.png)               | Interfaces de red adicionales                            |
| 3.10 | ![Fig. 3.10](hallazgos/google-on-hub/image-9.png)              | Detalle adicional de interfaces                          |
| 3.11 | ![Fig. 3.11](hallazgos/google-on-hub/image-10.png)             | Última captura de interfaces del OnHub                   |
| 3.12 | ![Fig. 3.12](hallazgos/tv-intelligence/image.png)              | `kodi.log` del dispositivo multimedia                    |
| 3.13 | ![Fig. 3.13](hallazgos/tv-intelligence/image-1.png)            | Zona horaria configurada en la TV                        |
| 3.14 | ![Fig. 3.14](hallazgos/tv-intelligence/image-2.png)            | Primer dispositivo Bluetooth detectado                   |
| 3.15 | ![Fig. 3.15](hallazgos/tv-intelligence/image-3.png)            | Segundo dispositivo Bluetooth detectado                  |
| 3.16 | ![Fig. 3.16](hallazgos/tv-intelligence/image-4.png)            | Verificación de hashes del paquete analizado             |

## 4. Resumen ejecutivo

## 5. Introducción

### 5.1. Antecedentes

### 5.2. Objetivos

## 6. Fuentes de información

### 6.1. Comprobación de hashes (MD5 y SHA-1)

Para asegurar que los ficheros no han sido modificados desde su adquisición, se verificó la integridad mediante funciones hash, principalmente **MD5** y **SHA-256** cuando la fuente lo aportaba. La verificación se realiza antes y después de cualquier transferencia o uso en herramientas forenses.

**Evidencia con verificación explícita:**

| Evidencia | Hash MD5 | Hash SHA-256 |
| --- | --- | --- |
| `TV_Inteligente.zip` | `D9D2B3B3048A836289CEC02C6353B6E9` | `5423EA3F60D4AD0874346D3BA31C8783E5F2CE4B15B261BA0085E07F11E650E6` |

### 6.2. Adquisición de hallazgos

Se trabajó sobre capturas y documentos de análisis ya extraídos, organizados en dos bloques principales:

- Evidencias del **Google OnHub**: capturas del panel de estado, red Wi-Fi, DNS, tabla ARP e interfaces.
- Evidencias de **TV Intelligence**: captura del `kodi.log`, zona horaria, dispositivos Bluetooth y comprobación de hashes.

La adquisición se apoyó en trabajo sobre copias y en la preservación de los ficheros originales dentro del repositorio.

## 7. Análisis

### 7.1. Herramientas utilizadas

| Herramienta | Uso en el análisis |
| --- | --- |
| Visor de imágenes / capturas | Revisión de paneles, logs y pantallas de estado. |
| Herramientas de hash | Verificación de integridad de archivos aportados. |
| Interpretación manual de logs | Extracción de contexto técnico desde `kodi.log` y paneles del router. |

### 7.2. Google OnHub

El router analizado corresponde a un **Google OnHub** que ejecuta **Chrome OS 9460.40.5** en canal estable, sobre hardware **Qualcomm 8064**, identificado internamente como `whirlwind`.

Los datos más relevantes del dispositivo son los siguientes:

- IP del router: `192.168.86.1`
- IP WAN: `192.168.165.9`
- Gateway WAN: `192.168.165.1`
- Velocidad de enlace WAN: 100 Mbps
- SSID principal: `HOME`
- SSID de invitados: `HOME-guest`
- Banda de 2,4 GHz: canal 6, ancho HT20
- Banda de 5 GHz: canal 36, ancho de 80 MHz
- Estado de la red de invitados: activa

La red de invitados estaba habilitada en el momento del diagnóstico, lo que amplía la superficie de exposición del entorno doméstico y permite acceso a Internet sin necesidad de usar la red principal.

En la configuración DNS aparecen `8.8.8.8` y `8.8.4.4` como resolutores de Google, junto con `210.115.225.11`, que no encaja con un DNS público habitual. La comprobación geográfica de esta IP la sitúa en **Corea del Sur**, por lo que conviene tratarla como un dato anómalo hasta verificar su origen.

La tabla ARP y el inventario de interfaces muestran actividad en `br-lan`, `br-guest`, `wan0`, `wlan-2400mhz`, `wlan-5000mhz`, `lan0` y otras interfaces internas. Entre los registros relevantes destaca el host `osmc`, que aparece desconectado pero con actividad histórica, junto con otros dispositivos Android y varios equipos conectados por Wi-Fi o Ethernet.

Hallazgos técnicos principales:

- La red de invitados estaba habilitada, lo que amplía la superficie de exposición de la red doméstica.
- El host `osmc` figura en el historial del router y encaja con un dispositivo multimedia basado en Kodi.
- El hostname `ademanafe` resulta anómalo y merece verificación adicional por no seguir una nomenclatura estándar.
- La coexistencia de varios dispositivos Android en un intervalo temporal cercano sugiere una red doméstica con actividad diversa en el mismo periodo.

### 7.3. TV Intelligence

El segundo conjunto de evidencias corresponde a un dispositivo multimedia con **Kodi 17.3** ejecutándose sobre **Linux ARM (Thumb) 32-bit**. El propio registro indica que se trata de una instalación asociada a **Open Source Media Center 2017.06-1-kodi**, con mapeos internos típicos de un entorno OSMC:

- `special://masterprofile/` -> `/home/osmc/.kodi/userdata`
- `special://home/` -> `/home/osmc/.kodi`
- `special://temp/` -> `/home/osmc/.kodi/temp`

Este detalle es relevante porque enlaza directamente con el host `osmc` detectado en el OnHub.

La captura de la zona horaria muestra `America/New_York`, lo que aporta contexto sobre la configuración regional del sistema. No implica por sí solo una incidencia, pero sí permite alinear la cronología de eventos si se cruzan otros registros.

La evidencia Bluetooth contiene dos identificadores llamativos en la caché del sistema:

- `Echo-2W5`
- `M11A`

Estos nombres sugieren periféricos o dispositivos próximos detectados por el sistema multimedia, útiles para ampliar el inventario de activos asociados al entorno.

En conjunto, la evidencia no apunta a una intrusión por sí misma, sino a la identificación y caracterización de un sistema multimedia con sus artefactos de configuración, ubicación horaria y dispositivos Bluetooth asociados.

### 7.4. Correlación de evidencias

La correlación entre ambos análisis es el punto más útil del informe.

El router OnHub registra el dispositivo `osmc` como parte del entorno de red, mientras que la evidencia de la TV muestra precisamente un sistema **Kodi/OSMC** funcionando en una plataforma ARM. Esa concordancia permite concluir que ambos conjuntos de pruebas pertenecen al mismo ecosistema doméstico y que la TV inteligente formaba parte de la red administrada por el OnHub.

Además, la red de invitados activa en el router y la presencia de varios equipos conectados o registrados en su historial indican un entorno con actividad suficiente como para requerir interpretación contextual de cada artefacto. Desde un punto de vista forense, esto obliga a tratar cada evidencia dentro de su entorno de red para evitar atribuciones erróneas.

## 8. Limitaciones

## 9. Conclusiones

## 11. Anexo 2. Cadena de custodia

La siguiente tabla documenta la cadena de custodia de los archivos y evidencias digitales tratados en el presente informe, asegurando la trazabilidad, integridad y control de acceso en cada etapa del proceso forense.

| Nº | Ruta / Archivo | Descripción / Contenido | Responsable | Fecha/Hora adquisición | Método de adquisición | Observaciones |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | hallazgos/google-on-hub/image.png | Información principal del router | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 2 | hallazgos/google-on-hub/image-1.png | Información adicional del router | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 3 | hallazgos/google-on-hub/image-2.png | Consulta geográfica del DNS | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 4 | hallazgos/google-on-hub/image-3.png | Tabla ARP | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 5 | hallazgos/google-on-hub/image-4.png | Red inalámbrica principal | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 6 | hallazgos/google-on-hub/image-5.png | Red de invitados | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 7 | hallazgos/google-on-hub/image-6.png | Red mesh | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 8 | hallazgos/google-on-hub/image-7.png | Interfaces de red 1 | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 9 | hallazgos/google-on-hub/image-8.png | Interfaces de red 2 | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 10 | hallazgos/google-on-hub/image-9.png | Interfaces de red 3 | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 11 | hallazgos/google-on-hub/image-10.png | Interfaces de red 4 | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 12 | hallazgos/tv-intelligence/image.png | `kodi.log` del dispositivo | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 13 | hallazgos/tv-intelligence/image-1.png | Zona horaria configurada | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 14 | hallazgos/tv-intelligence/image-2.png | Dispositivo Bluetooth Echo-2W5 | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 15 | hallazgos/tv-intelligence/image-3.png | Dispositivo Bluetooth M11A | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 16 | hallazgos/tv-intelligence/image-4.png | Comprobación de hashes | Luis Carlos Romero | 2026-05-14 | Captura de pantalla | Original digital |
| 17 | hallazgos/tv-intelligence/kodi.log | Archivo de log del sistema Kodi | Luis Carlos Romero | 2026-05-14 | Extracción directa | Archivo de 22.477 bytes |
| 18 | hallazgos/tv-intelligence/74_C2_46_88_5D_09 | Archivo de dispositivo Bluetooth Echo-2W5 | Luis Carlos Romero | 2026-05-14 | Extracción directa | Identificador MAC |
| 19 | hallazgos/tv-intelligence/88_0F_10_F6_C8_B7 | Archivo de dispositivo Bluetooth M11A | Luis Carlos Romero | 2026-05-14 | Extracción directa | Identificador MAC |
| 20 | hallazgos/tv-intelligence/hash_74-C2-46-88-5D-09.csv | Hash de integridad Echo-2W5 | Luis Carlos Romero | 2026-05-14 | Extracción directa | Verificación SHA |
| 21 | hallazgos/tv-intelligence/has_bluethoot_88-0F-10-F6-C8-B7.csv | Hash de integridad M11A | Luis Carlos Romero | 2026-05-14 | Extracción directa | Verificación SHA |
| 22 | hallazgos/tv-intelligence/hash_timezone.csv | Hash de integridad zona horaria | Luis Carlos Romero | 2026-05-14 | Extracción directa | Verificación SHA |
| 23 | hallazgos/tv-intelligence/kodlog_hash.csv | Hash de integridad kodi.log | Luis Carlos Romero | 2026-05-14 | Extracción directa | Verificación SHA |
| 24 | hallazgos/tv-intelligence/timezone | Archivo de configuración horaria | Luis Carlos Romero | 2026-05-14 | Extracción directa | `America/New_York`

## 12. Anexo 3. Otras necesidades

### 12.1. Índice de hallazgos

| **Ruta** | **Contenido** | **Sección** | **Observación** |
| --- | --- | --- | --- |
| hallazgos/google-on-hub/image.png | Información principal | hallazgos/google-on-hub/ | Router Google OnHub |
| hallazgos/google-on-hub/image-1.png | Información adicional | hallazgos/google-on-hub/ | Datos de estado del dispositivo |
| hallazgos/google-on-hub/image-2.png | DNS no estándar | hallazgos/google-on-hub/ | Geolocalización del resolver |
| hallazgos/google-on-hub/image-3.png | Tabla ARP | hallazgos/google-on-hub/ | Inventario de red |
| hallazgos/google-on-hub/image-4.png | Red principal | hallazgos/google-on-hub/ | SSID `HOME` |
| hallazgos/google-on-hub/image-5.png | Red de invitados | hallazgos/google-on-hub/ | SSID `HOME-guest` |
| hallazgos/google-on-hub/image-6.png | Red mesh | hallazgos/google-on-hub/ | Topología de red |
| hallazgos/google-on-hub/image-7.png | Interfaces 1 | hallazgos/google-on-hub/ | Interfaces de red |
| hallazgos/google-on-hub/image-8.png | Interfaces 2 | hallazgos/google-on-hub/ | Interfaces de red |
| hallazgos/google-on-hub/image-9.png | Interfaces 3 | hallazgos/google-on-hub/ | Interfaces de red |
| hallazgos/google-on-hub/image-10.png | Interfaces 4 | hallazgos/google-on-hub/ | Interfaces de red |
| hallazgos/tv-intelligence/image.png | `kodi.log` | hallazgos/tv-intelligence/ | Kodi / OSMC |
| hallazgos/tv-intelligence/image-1.png | Zona horaria | hallazgos/tv-intelligence/ | `America/New_York` |
| hallazgos/tv-intelligence/image-2.png | Bluetooth 1 | hallazgos/tv-intelligence/ | Dispositivo Echo-2W5 |
| hallazgos/tv-intelligence/image-3.png | Bluetooth 2 | hallazgos/tv-intelligence/ | Dispositivo M11A |
| hallazgos/tv-intelligence/image-4.png | Hashes | hallazgos/tv-intelligence/ | Verificación de integridad |
| hallazgos/tv-intelligence/kodi.log | Archivo de log | hallazgos/tv-intelligence/ | 22.477 bytes, 17/07/2017 |
| hallazgos/tv-intelligence/74_C2_46_88_5D_09 | MAC Echo-2W5 | hallazgos/tv-intelligence/ | 24 (1 KB), 16/07/2017 |
| hallazgos/tv-intelligence/88_0F_10_F6_C8_B7 | MAC M11A | hallazgos/tv-intelligence/ | 20 (1 KB), 16/07/2017 |
| hallazgos/tv-intelligence/hash_74-C2-46-88-5D-09.csv | Hash Echo-2W5 | hallazgos/tv-intelligence/ | CSV |
| hallazgos/tv-intelligence/has_bluethoot_88-0F-10-F6-C8-B7.csv | Hash M11A | hallazgos/tv-intelligence/ | CSV |
| hallazgos/tv-intelligence/hash_timezone.csv | Hash timezone | hallazgos/tv-intelligence/ | CSV |
| hallazgos/tv-intelligence/kodlog_hash.csv | Hash kodi.log | hallazgos/tv-intelligence/ | CSV |
| hallazgos/tv-intelligence/timezone | Zona horaria | hallazgos/tv-intelligence/ | `America/New_York`

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