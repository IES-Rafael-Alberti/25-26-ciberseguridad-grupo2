# Informe de Análisis de Vulnerabilidades (Caja Negra)

**Proyecto:** Hunting Vulnerabilities - SecureLogistics

**Fecha del Análisis:** 11 de Febrero de 2026

**Objetivo:** Evaluar la postura de seguridad externa del servidor Linux (192.168.122.209) mediante una auditoría de doble verificación (Double-Check) utilizando dos motores de escaneo líderes.

**Herramientas Utilizadas:**

- Tenable Nessus (Escaneo Básico de Red)
- Greenbone OpenVAS (Escaneo Completo y Rápido)

## 1. Resumen Ejecutivo Consolidado

La auditoría de Caja Negra (sin credenciales) ha identificado vulnerabilidades significativas que comprometen la seguridad del servidor. Al combinar la potencia de ambas herramientas, se ha obtenido una radiografía completa de la superficie de ataque externa.

Matriz de Hallazgos Combinados:

| Severidad | Hallazgo Principal | Herramienta de Detección |
| --- | --- | --- |
| CRÍTICA | Sistema Operativo Obsoleto (Ubuntu 14.04) | Nessus (OpenVAS no lo priorizó) |
| ALTA | Vulnerabilidad SWEET32 (Cifrados Débiles) | Ambas (OpenVAS detectó en puerto 631) |
| MEDIA | Debilidades en Protocolo SSH (Algoritmos) | Ambas (OpenVAS detalló claves/KEX) |
| MEDIA | Protocolos TLS Obsoletos (1.0 / 1.1) | Ambas |
| BAJA | Revelación de Timestamps (TCP/ICMP) | OpenVAS |

## 2. Análisis Detallado de Vulnerabilidades

A continuación se detallan los riesgos confirmados por los escaneos.

### 2.1. Obsolescencia del Sistema Operativo (End of Life)

Severidad: CRÍTICA (CVSS 10.0)

Fuente: Detectado principalmente por Nessus (Plugin 201408).

**Descripción:**
El servidor ejecuta Ubuntu 14.04 LTS. El soporte oficial para esta versión ha finalizado. OpenVAS detectó múltiples servicios antiguos pero no correlacionó esto en una alerta crítica de "S.O. Obsoleto" en su reporte principal, lo cual resalta la importancia de usar Nessus para esta detección.

**Impacto:**
Falta total de parches de seguridad para nuevas vulnerabilidades. Es la raíz de la mayoría de los problemas de cifrado detectados, ya que las librerías base (OpenSSL) son antiguas.

Acción: Actualizar/Migrar a Ubuntu 22.04 LTS o superior inmediatamente.

### 2.2. Vulnerabilidad SWEET32 y Cifrados Débiles (HTTPS/CUPS)

Severidad: ALTA (CVSS 7.5)

Fuente: Ambas. (OpenVAS lo identificó específicamente en el puerto 631/tcp).

**Descripción:**
Los servicios web (incluyendo el servicio de impresión CUPS detectado por OpenVAS) aceptan cifrados de bloque de 64 bits (3DES).

Detalle OpenVAS: TLS_RSA_WITH_3DES_EDE_CBC_SHA en puerto 631.

Detalle Nessus: SSL Medium Strength Cipher Suites (Plugin 42873).

**Impacto:**
Permite ataques de colisión para descifrar tráfico seguro (HTTPS) si se capturan suficientes datos.

Acción: Deshabilitar suites de cifrado 3DES y RC4 en la configuración de Apache/Nginx y CUPS.

### 2.3. Configuración Insegura de SSH

Severidad: MEDIA (CVSS 5.3 - 6.5)

Fuente: Ambas (OpenVAS proporcionó el desglose técnico más granular).

Hallazgos Técnicos (OpenVAS & Nessus):

- Algoritmos de Host Obsoletos: Soporte para ssh-dss (DSA), considerado inseguro.
- Intercambio de Claves (KEX) Débil: Uso de SHA-1 (diffie-hellman-group1-sha1) y grupos MODP de 1024 bits.
- Cifrados Simétricos Débiles: Soporte para 3des-cbc, aes128-cbc, y arcfour (RC4).
- Vulnerabilidad Terrapin: Nessus identificó específicamente la vulnerabilidad Terrapin Prefix Truncation (CVE-2023-48795).

Acción: Endurecer /etc/ssh/sshd_config eliminando algoritmos CBC, RC4, DSA y KEX SHA-1.

### 2.4. Protocolos TLS Deprecados

Severidad: MEDIA

Fuente: Ambas.

**Descripción:**
Se detectó el soporte activo para TLS 1.0 y TLS 1.1. Estos protocolos tienen fallos criptográficos conocidos y han sido deprecados por los estándares modernos.

Acción: Forzar el uso exclusivo de TLS 1.2 y 1.3.

### 2.5. Fugas de Información (Timestamps)

Severidad: BAJA

Fuente: Principalmente OpenVAS.

**Descripción:**
El servidor responde a peticiones ICMP Timestamp (Type 13) y revela Timestamps TCP.

**Impacto:**
Permite a un atacante calcular el tiempo de actividad (uptime) exacto del servidor y potencialmente predecir secuencias de números aleatorios.

## 3. Resumen Ejecutivo

El escaneo de Caja Negra sobre el servidor 192.168.122.209 evidencia una **exposición externa de alto riesgo** por dos motivos principales:

- **Riesgo crítico estructural:** el servidor ejecuta **Ubuntu 14.04 LTS**, sistema operativo sin soporte (EOL), lo que implica **ausencia de parches** y aumenta la probabilidad de explotación de fallos conocidos y futuros.
- **Debilidades criptográficas y de acceso remoto:** se confirma la presencia de **cifrados débiles (SWEET32/3DES)**, **protocolos TLS obsoletos (1.0/1.1)** y **configuración insegura en SSH** (KEX y algoritmos legacy), lo que incrementa el riesgo de interceptación, degradación criptográfica y abuso de vectores de acceso.

La combinación de Nessus y OpenVAS aporta corroboración cruzada: Nessus prioriza el riesgo de plataforma (EOL), mientras OpenVAS añade visibilidad adicional (por ejemplo, exposición asociada a CUPS/puerto 631) y hallazgos de enumeración como timestamps.

## 4. Recomendaciones

### Prioridad 0 (Inmediata)

- **Planificar y ejecutar la migración del sistema operativo** (Ubuntu 22.04 LTS o superior). Esta medida es la más efectiva para reducir riesgo, ya que ataca la causa raíz.
- **Reducir superficie de ataque mientras se migra:** limitar el acceso a los servicios expuestos desde redes no confiables y revisar que solo permanezcan publicados los puertos estrictamente necesarios.

### Prioridad 1 (Hardening de servicios)

- **TLS/HTTPS:** deshabilitar **TLS 1.0 y 1.1** y forzar **TLS 1.2 y 1.3**.
- **Cifrados SWEET32:** eliminar **3DES** y **RC4** de las suites permitidas (servidor web y servicios asociados, incluyendo CUPS si aplica).
- **SSH:** endurecer `/etc/ssh/sshd_config` eliminando **CBC**, **RC4**, **ssh-dss (DSA)** y **KEX basados en SHA-1**, manteniendo algoritmos modernos.

### Prioridad 2 (Reducción de exposición y señalización)

- **CUPS/puerto 631:** validar necesidad operativa; si no es imprescindible para impresión externa, **cerrar o restringir** su acceso.
- **Timestamps (ICMP/TCP):** deshabilitar respuestas ICMP Timestamp y minimizar la exposición de información de tiempo cuando sea posible.

### Prioridad 3 (Verificación)

- Tras aplicar cambios, **repetir el escaneo de Caja Negra** para confirmar la eliminación de TLS obsoleto, suites débiles y endurecimiento de SSH.

