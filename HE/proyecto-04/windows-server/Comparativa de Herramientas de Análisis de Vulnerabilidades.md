# Comparativa de Herramientas de Análisis de Vulnerabilidades
**Proyecto 4 - Hunting Vulnerabilities**

---

## 1. Introducción
En esta sección del proyecto se comparan dos de las herramientas más utilizadas en el mercado para la gestión de vulnerabilidades: **Tenable Nessus Essentials** (software comercial) y **Greenbone OpenVAS** (software Open Source).

El objetivo es contrastar su eficacia, facilidad de uso y profundidad de reporte sobre el mismo activo objetivo: **Windows Server 2008 R2 (192.168.122.168)**.

---

## 2. Tabla Comparativa de Resultados

Basándonos en los informes técnicos generados (adjuntos en el repositorio), se observan las siguientes diferencias cuantitativas:

| Característica | Tenable Nessus | Greenbone OpenVAS |
| :--- | :---: | :---: |
| **Total Vulnerabilidades** | 35 | 16 |
| **Críticas / Altas** | 6 (Críticas + Altas) | 8 (High) |
| **Tiempo de Escaneo** | 25 minutos | 55 minutos |
| **Facilidad de Configuración** | Alta (Instalador web) | Media/Baja (Requiere configuración compleja) |
| **Calidad del Reporte** | Ejecutiva y limpia | Técnica y densa |

---

## 3. Análisis de Detección

### 3.1. Coincidencias Clave
Ambas herramientas identificaron correctamente los problemas de seguridad más graves del servidor, lo que valida la auditoría:
* **Sistema Operativo Obsoleto:** Ambas herramientas alertaron sobre el "End of Life" de Microsoft Windows Server 2008 R2.
* **Servicios Web Vulnerables:** Tanto Nessus como OpenVAS detectaron los servicios expuestos en puertos no estándar (8022, 8080, 9200).

### 3.2. Diferencias en la Clasificación (CVSS)
* **Nessus** tiende a agrupar vulnerabilidades. Por ejemplo, en el informe se ve una entrada llamada *"Zohocorp ManageEngine Desktop Central (Multiple Issues)"* que agrupa 10 CVEs distintos. Esto hace el informe más legible para la directiva.
* **OpenVAS** tiende a listar cada CVE por separado, lo que puede inflar el número de resultados ("ruido") o hacer que el informe sea más largo y difícil de leer para un perfil no técnico.

### 3.3. Detección de Falsos Positivos/Negativos
Nessus demostró una mayor precisión al identificar la versión exacta de **Elasticsearch (1.1.1)** y marcarla inmediatamente como **Crítica (CVSS 9.8)**. OpenVAS detectó el servicio, pero su base de datos de firmas (NVT) comunitaria a veces tiene un retraso respecto a la versión comercial de Nessus, lo que puede afectar a la puntuación de riesgo.

---

## 4. Fortalezas y Debilidades

### Tenable Nessus
* **Fortalezas:**
    * **Precisión:** Menor tasa de falsos positivos.
    * **Interfaz:** Muy intuitiva y moderna.
    * **Reportes:** Genera PDFs listos para entregar a gerencia con gráficas claras.
    * **Instalación:** Sencilla y contenida (todo en un servicio).
* **Debilidades:**
    * La versión gratuita limita el número de IPs (16).
    * Es código cerrado (propietario).

### Greenbone OpenVAS
* **Fortalezas:**
    * **Open Source:** Totalmente gratuito y sin límites de IPs.
    * **Flexibilidad:** Permite modificar los scripts de escaneo.
* **Debilidades:**
    * **Complejidad:** La instalación y mantenimiento son difíciles (especialmente en gestión de bases de datos).
    * **Interfaz:** Anticuada y poco amigable para el usuario (UX).
    * **Recursos:** Consume muchos más recursos de hardware y espacio en disco.

---

En el contexto de **SecureLogistics**, lo que más pesa es poder hacer una **evaluación rápida** y explicar el riesgo de forma clara a la Junta Directiva. En ese escenario, **Nessus encaja mejor**.

Más allá del número de hallazgos, la diferencia está en *cómo* se presenta la información: Nessus genera informes más “listos para decisión”, con mensajes directos (por ejemplo, sistema sin soporte o riesgo crítico) y un formato fácil de consumir para un perfil ejecutivo (CISO/Junta).

**OpenVAS** sigue siendo una opción muy válida si el objetivo es trabajar a nivel más técnico, se busca una solución **sin coste de licencia** y se cuenta con tiempo/equipo para asumir la instalación y el mantenimiento. En resumen: Nessus para convencer y priorizar rápido; OpenVAS para profundizar y operar con recursos ajustados.