# Informe de Análisis de Vulnerabilidades - Proyecto 4

**Cliente:** SecureLogistics  
**Proyecto:** Auditoría de Seguridad (Hunting Vulnerabilities)  
**Fecha del Análisis:** 11 de Febrero de 2026
**Activo Auditado:** Servidor Windows (192.168.122.168)  
**Herramienta:** Tenable Nessus Essentials  
**Modalidad:** Caja Negra (Black Box) - Sin credenciales

---

## 1. Resumen Ejecutivo

A petición de la dirección de **SecureLogistics**, se ha realizado una evaluación de vulnerabilidades en modalidad de "Caja Negra" sobre el servidor con IP **192.168.122.168**.

El análisis revela un estado de seguridad **CRÍTICO**. El servidor opera con un sistema operativo obsoleto y sin soporte desde hace años, lo que impide la aplicación de parches de seguridad modernos. Además, se han detectado servicios expuestos (Elasticsearch y ManageEngine) con vulnerabilidades conocidas que permiten la **Ejecución Remota de Código (RCE)**.

Esto significa que un atacante externo podría tomar el control total del servidor con privilegios de administrador sin necesidad de conocer ninguna contraseña. Se recomienda aislar el equipo de la red inmediatamente.

---

## 2. Alcance y Metodología

* **Objetivo:** Identificar vulnerabilidades en la infraestructura de servidores de SecureLogistics para justificar la inversión en seguridad.
* **Alcance:** Escaneo de red sobre el host `192.168.122.168`.
* **Metodología:** Análisis de puertos y detección de servicios mediante escáner automatizado (Nessus), analizando las respuestas del servidor para correlacionarlas con bases de datos de CVEs (Common Vulnerabilities and Exposures).

---

## 3. Resumen de Hallazgos

Se han identificado un total de **35 incidencias**, distribuidas por severidad de la siguiente manera:

| Severidad | Cantidad | Descripción |
| :--- | :---: | :--- |
| **CRÍTICA** | **4** | Vulnerabilidades explotables remotamente que otorgan control total. |
| **ALTA** | **2** | Fallos que comprometen la integridad o confidencialidad de datos. |
| **MEDIA** | **7** | Configuraciones inseguras que facilitan ataques (ej. Man-in-the-Middle). |
| **BAJA/INFO** | **22** | Fugas de información y detección de servicios. |

---

## 4. Detalle de Vulnerabilidades Críticas

A continuación se detallan los hallazgos más graves que requieren atención inmediata.

### 4.1. Sistema Operativo Obsoleto (End of Life)
* **Severidad:** 🔴 **CRÍTICA (CVSS 10.0)** 
* **Activo Afectado:** `tcp/0` (Sistema Operativo Base)
* **Descripción:** El servidor ejecuta **Microsoft Windows Server 2008 R2 Standard Service Pack 1**. El soporte para esta versión ha finalizado, lo que significa que el fabricante (Microsoft) ya no publica actualizaciones de seguridad.
* **Impacto:** Cualquier nueva vulnerabilidad descubierta en Windows será explotable indefinidamente, dejando a la organización expuesta a ransomware y espionaje.
* **Solución:** Migrar a un sistema operativo con soporte activo, como Windows Server 2019 o 2022[.

### 4.2. ManageEngine Desktop Central - Ejecución Remota de Código (RCE)
* **Severidad:** 🔴 **CRÍTICA (CVSS 10.0)** 
* **CVE:** CVE-2015-82001 
* **Puerto:** `8022/tcp` 
* **Descripción:** Se detectó la versión **9 (Build 91084)** de ManageEngine Desktop Central. Esta versión contiene un fallo en el script `statusUpdate` que falla al sanitizar la entrada del usuario.
* **Impacto:** Un atacante no autenticado puede subir un archivo malicioso (ej. PHP) y ejecutar código arbitrario con privilegios de **NT AUTHORITY\SYSTEM** (máximos privilegios en Windows).
* **Solución:** Actualizar inmediatamente a ManageEngine Desktop Central versión 9 build 91100 o superior.

### 4.3. Elasticsearch - Ejecución Remota de Código (Unspecified RCE)
* **Severidad:** 🔴 **CRÍTICA (CVSS 9.8)** 
* **CVE:** CVE-2015-5377 
* **Puerto:** `9200/tcp`
* **Descripción:** El servicio Elasticsearch instalado es la versión **1.1.1**. Las versiones anteriores a la 1.6.1 son vulnerables a ataques a través del protocolo de transporte.
* **Impacto:** Permite a un atacante remoto ejecutar código arbitrario en el sistema, comprometiendo la base de datos y el servidor subyacente.
* **Solución:** Actualizar Elasticsearch a la versión 1.6.1, 1.7.0 o superior, y restringir el acceso al puerto 9200 mediante Firewall.

---

## 5. Otras Vulnerabilidades Relevantes

### 5.1. SMB Signing Not Required
* **Severidad:** 🟠 **MEDIA (CVSS 5.3)** 
* **Puerto:** `445/tcp`
* **Descripción:** El servidor SMB no requiere firma digital en los paquetes.
* **Impacto:** Permite ataques de tipo *Man-in-the-Middle* (MitM) donde un atacante puede interceptar y modificar el tráfico entre el servidor y el cliente.
* **Solución:** Forzar la firma de mensajes ("Digitally sign communications") en la política de grupo de Windows.

### 5.2. Servicios Web Vulnerables (GlassFish / Jenkins)
* **Descripción:** Se han detectado múltiples servidores web antiguos, incluyendo **Oracle GlassFish Server 4.0**  y **Jetty (Jenkins)** en puertos no estándar como 8080, 8181 y 8484.
* **Riesgo:** Estos servicios exponen paneles de administración y versiones de Java antiguas que aumentan la superficie de ataque.

---

## 6. Conclusiones y Recomendaciones

El análisis de caja negra ha sido suficiente para determinar que el servidor **no cumple con los mínimos estándares de seguridad**. La presencia de software sin soporte (Windows 2008 R2) combinada con vulnerabilidades de ejecución remota (RCE) de puntuación 10.0 hace que este activo sea un riesgo extremo para SecureLogistics.

**Hoja de Ruta Recomendada:**

1.  **Inmediato:** Aislar el servidor de la red pública y restringir el acceso a los puertos 9200, 8022 y 3389 solo a IPs de administración a través de VPN.
2.  **Corto Plazo:** Realizar una copia de seguridad de los datos y planificar la migración a un servidor con Windows Server 2022.
3.  **Medio Plazo:** Establecer una política de gestión de parches para evitar que software crítico (como ManageEngine o Elasticsearch) quede desactualizado durante años.

---