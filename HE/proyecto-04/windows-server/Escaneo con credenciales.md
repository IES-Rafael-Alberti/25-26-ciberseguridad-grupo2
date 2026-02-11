
# Escaneo con credenciales (Nessus) – Comparativa con escaneo sin credenciales

**Proyecto 4 – Hunting Vulnerabilities (Windows Server)**  
**Activo analizado:** 192.168.122.168 (Windows Server 2008 R2)  
**Herramienta:** Tenable Nessus (misma herramienta en ambos escaneos)

Este documento resume el **escaneo con credenciales** ("caja blanca") y compara sus resultados con el escaneo anterior **sin credenciales** ("caja negra") para identificar qué cambia y por qué.

> **Credenciales proporcionadas para el ejercicio:** `vagrant/vagrant`.

**Informes utilizados (adjuntos en el repositorio):**

- Escaneo sin credenciales: [nessus/Win-Nessus-Vulnerabilidades.pdf](nessus/Win-Nessus-Vulnerabilidades.pdf) / [nessus/Win-Nessus-Vulnerabilidades.html](nessus/Win-Nessus-Vulnerabilidades.html)
- Escaneo con credenciales: [nessus/Proyecto4_CajaBlanca.pdf](nessus/Proyecto4_CajaBlanca.pdf) / [nessus/Proyecto4_CajaBlanca_34wfld.html](nessus/Proyecto4_CajaBlanca_34wfld.html)

---
![alt text](<img/2. caja negra.png>)

## 1. Objetivo

Realizar un **análisis con credenciales de acceso** sobre el mismo host para:

- Aumentar la visibilidad (inventario real de software, parches y configuración).
- Detectar vulnerabilidades que **no pueden validarse** únicamente desde la red.
- Comparar diferencias frente al escaneo sin credenciales y justificar por qué varía el número de hallazgos.

Además, se busca evidenciar la diferencia entre:

- **Caja negra (sin credenciales):** simula lo que podría ver un atacante **solo desde la red** (puertos, servicios, banners, comportamiento remoto).
- **Caja blanca (con credenciales):** simula un escenario con **acceso autenticado** (cuenta válida) y permite comprobar vulnerabilidades/configuraciones **desde dentro** del sistema (inventario, parches, archivos, configuración, permisos).

---

## 2. Configuración de ambos escaneos (misma herramienta)

### 2.1. Escaneo anterior (sin credenciales) – “Win Nessus”

Según el bloque “Information about this scan” del informe:

- **Scan name:** My Basic Network Scan
- **Scan policy used:** Basic Network Scan
- **Port scanner(s):** `nessus_syn_scanner`
- **Credentialed checks:** no
- **Duración:** 1459 segundos (≈ 24 min 19 s)

### 2.2. Escaneo con credenciales – “Proyecto4_CajaBlanca”

Según el bloque “Information about this scan” del informe:

- **Scan name:** Proyecto4_CajaBlanca
- **Scan policy used:** Basic Network Scan
- **Port scanner(s):** `wmi_netstat`
- **Credentialed checks:** yes, as `192.168.122.168\vagrant` via SMB
- **Duración:** 1770 segundos (≈ 29 min 30 s)

**Cómo encaja aquí `vagrant/vagrant`:**

- La cuenta utilizada para autenticar en el host es `vagrant` con contraseña `vagrant`.
- En Nessus esto se configura típicamente en **Credentials → Windows** (SMB) para habilitar *local/credentialed checks*.
- El propio informe valida que la autenticación fue correcta al indicar: “Credentialed checks: yes … via SMB”.

> Nota: el hecho de que aparezca “Credentialed checks: yes … via SMB” indica que el escáner **sí autenticó correctamente** y pudo ejecutar comprobaciones locales (Windows/SMB/WMI), que en un escaneo de caja negra no se ejecutan.

---

## 3. Comparativa de resultados (resumen por severidad)

Los siguientes conteos se extraen del panel “Vulnerabilities by Host” del informe de Nessus para el host `192.168.122.168`.

| Severidad | Sin credenciales (Win Nessus) | Con credenciales (Proyecto4_CajaBlanca) |
| :--- | ---: | ---: |
| **Critical** | 7 | 137 |
| **High** | 2 | 413 |
| **Medium** | 4 | 147 |
| **Low** | 1 | 22 |
| **Info** | 112 | 364 |

**Lectura rápida:** con credenciales, Nessus pasa de un escaneo principalmente de **exposición de servicios** (banners/puertos) a uno con **validación interna** (software instalado, rutas, versiones reales, configuración del sistema), lo que dispara el volumen de hallazgos.

Para dejar clara la diferencia metodológica (caja negra vs caja blanca), en la práctica cambia:

| Aspecto | Caja negra (sin credenciales) | Caja blanca (con credenciales) |
| :--- | :--- | :--- |
| **Fuente de evidencia** | Respuesta remota del servicio (banner, handshake, comportamiento) | Datos del sistema (registro, paquetes, binarios, rutas, configuración, parches) |
| **Tipo de checks** | Principalmente *remote checks* y enumeración de red | *Remote checks* + *local/credentialed checks* |
| **Riesgo observado** | “Qué tan expuesto está desde fuera” | “Qué tan débil está por dentro” (y qué tan fácil es escalar/comprometer con acceso) |
| **Probables omisiones** | Software vulnerable instalado pero no expuesto o sin banner | Menos omisiones en inventario; sigue habiendo límites si faltan permisos/servicios |

---

## 4. Diferencias clave respecto al escaneo anterior

### 4.1. Por qué aumentan los hallazgos con credenciales

En un escaneo autenticado, Nessus puede:

- **Enumerar software real instalado** y obtener versiones desde el sistema (no solo por banner).
- Detectar librerías/componentes vulnerables **dentro del disco** (por ejemplo `.jar` en rutas concretas).
- Ejecutar comprobaciones “local checks” que requieren permisos y acceso a SMB/WMI.
- Identificar configuración insegura/políticas del host con más precisión.

En resumen, el incremento no implica que el servidor “empeore” de golpe; significa que el escaneo con credenciales ofrece un **inventario y verificación mucho más completos**.

También implica un cambio importante en el tipo de conclusiones:

- En **caja negra**, un hallazgo suele estar ligado a un **servicio alcanzable** (impacto inmediato desde red).
- En **caja blanca**, aparecen además hallazgos de **higiene interna** (software EoL, librerías vulnerables en disco, parches ausentes), que pueden no ser explotables “desde Internet” directamente, pero sí elevan el riesgo en escenarios de **movimiento lateral**, **post-explotación** o **insider threat**.

### 4.2. Ejemplos de hallazgos que aparecen gracias a credenciales

En el informe con credenciales aparecen hallazgos con **rutas y versiones internas**, por ejemplo:

- **156860 – Apache Log4j 1.x Multiple Vulnerabilities** (Log4j 1.x sin soporte y con múltiples CVEs).
- **182252 – Apache Log4j SEoL (<= 1.x)** (software en fin de soporte).
- Evidencias en rutas tipo `C:\ManageEngine\...\log4j-1.2.15.jar`, `C:\Program Files\elasticsearch-1.1.1\lib\log4j-1.2.17.jar`, etc.
- **151425 – Apache Struts … Possible Remote Code Execution (S2-061)** y **159667 – … (S2-062)**, con CVEs referenciados en el propio reporte.

Este tipo de salida (path + versión exacta) es especialmente útil para:

- Reducir discusiones sobre “si está o no instalado”.
- Facilitar remediación (saber exactamente qué componente y dónde está).

Es decir, el escaneo de **caja blanca** no solo “encuentra más”, sino que aporta **mejor evidencia** para priorizar y corregir.

### 4.3. Hallazgos típicos del escaneo sin credenciales (orientado a exposición)

En el informe sin credenciales predominan hallazgos derivados de servicios expuestos y banners, por ejemplo:

- **105752 – Elasticsearch Transport Protocol Unspecified Remote Code Execution**.
- **90192 – ManageEngine Desktop Central 8/9 < Build 91100 Multiple RCE**.

Este enfoque es ideal para responder a: “¿Qué ve un atacante externo sin acceso?”

### 4.4. Limitaciones y lecturas correctas (para evitar malentendidos)

- **Caja negra** puede generar *falsos negativos* por falta de visibilidad (software vulnerable instalado pero no identificable desde red).
- **Caja blanca** reduce esos falsos negativos y mejora la evidencia, pero puede variar según permisos de la cuenta (si la cuenta no tiene privilegios suficientes, algunas comprobaciones no se ejecutan).
- El salto de volumen (por ejemplo, en **High/Medium**) no significa necesariamente “más explotación remota”, sino “más elementos internos detectados” (componentes, librerías, configuraciones y parches).

---

## 5. Conclusión

El escaneo con credenciales (**Proyecto4_CajaBlanca**) aporta una visión mucho más completa del estado real del servidor y revela un volumen mayor de incidencias porque añade **comprobaciones internas** y evidencia (versiones/rutas). Para una auditoría técnica y un plan de remediación, este es el enfoque recomendado.

Como práctica, ambos enfoques se complementan:

- **Sin credenciales:** mide exposición externa y riesgo “desde fuera”.
- **Con credenciales:** detecta debilidades internas, inventario real y priorización fina para corregir.

Para cumplir el objetivo del ejercicio, el escaneo **con credenciales** se realizó con la **misma herramienta (Nessus)** que el escaneo anterior, empleando las credenciales proporcionadas `vagrant/vagrant`, y la diferencia queda reflejada tanto en el número de hallazgos como en el **tipo de evidencia** (rutas/versiones/configuración) propia de un enfoque de **caja blanca**.

