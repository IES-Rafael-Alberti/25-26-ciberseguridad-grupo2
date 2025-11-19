# Informe Técnico de Auditoría Ofensiva

## 1. Introducción

Este informe técnico presenta un análisis detallado de los principales tipos de ataques cibernéticos, las fases de un ataque ofensivo, las metodologías más utilizadas en auditorías de seguridad y las herramientas de monitorización empleadas en procesos de pentesting. El objetivo es construir una base sólida para el diseño de servicios profesionales de auditoría ofensiva en entornos corporativos.

---

## 2. Clasificación y análisis de tipos de ataque

La comprensión de los distintos tipos de ataques es fundamental para identificar, evaluar y mitigar vulnerabilidades dentro de una organización.

### 2.1 Ataques basados en credenciales

**Fuerza bruta**: uso de intentos automatizados para adivinar contraseñas. Herramientas típicas incluyen Hydra o Medusa. Este ataque se enfoca en servicios como SSH, FTP o paneles web.

**Credential Stuffing**: reutilización de credenciales filtradas. Muy efectivo si los usuarios repiten contraseñas en múltiples servicios.

### 2.2 Ataques a aplicaciones web

**Inyección SQL (SQLi)**: manipulación de consultas SQL para obtener acceso a datos sensibles o alterar la base de datos.

**Cross-Site Scripting (XSS)**: permite ejecutar scripts maliciosos en el navegador de la víctima.

**Path Traversal**: acceso no autorizado a archivos del sistema usando rutas como `../../etc/passwd`.

**Remote Code Execution (RCE)**: ejecución remota de código, lo que permite tomar control del servidor.

### 2.3 Ingeniería social

**Phishing**: suplantación de identidad para obtener credenciales o información sensible.

**Vishing/Smishing**: variantes mediante llamadas o SMS.

### 2.4 Ataques a redes internas

**Sniffing**: captura de tráfico para obtener contraseñas o información sin cifrar.

**ARP Spoofing**: redirección de tráfico para realizar ataques Man in the Middle.

**Escalada de privilegios**: aprovechamiento de fallos en configuraciones para obtener acceso administrativo.

### 2.5 Ataques a dispositivos IoT

Incluyen vulnerabilidades por contraseñas por defecto, firmware inseguro o puertos expuestos.

---

## 3. Fases de un ataque ofensivo

Los ataques ofensivos profesionales siguen un ciclo estructurado que permite llevar a cabo evaluaciones sistemáticas.

### 3.1 Reconocimiento

Recolección pasiva y activa de información sobre el objetivo, como direcciones IP, servicios y dominios.

### 3.2 Enumeración

Identificación detallada de usuarios, servicios activos, versiones y posibles puertas de entrada.

### 3.3 Explotación

Ejecución de técnicas para vulnerar sistemas, como SQLi, XSS o explotación de vulnerabilidades.

### 3.4 Escalada de privilegios

Obtención de permisos más altos mediante fallos en configuraciones o errores del sistema.

### 3.5 Movimiento lateral

Desplazamiento dentro de la red para comprometer sistemas adicionales.

### 3.6 Persistencia

Creación de puertas traseras o usuarios para mantener el acceso al sistema.

### 3.7 Exfiltración

Extracción de información sensible.

### 3.8 Eliminación de huellas

Borrado de trazas y registros para evitar detección.

---

## 4. Metodologías de pentesting

Se analizan las metodologías más reconocidas internacionalmente.

### 4.1 OWASP Web Security Testing Guide (WSTG)

Enfocada en aplicaciones web. Proporciona casos de prueba detallados y estructurados.

### 4.2 PTES (Penetration Testing Execution Standard)

Método estándar para auditorías completas, desde reconocimiento hasta reporte.

### 4.3 MITRE ATT&CK

Modelo basado en tácticas reales de adversarios, útil para simulaciones avanzadas.

### 4.4 Comparativa

* **OWASP WSTG**: ideal para auditorías web; muy detallada.
* **PTES**: cubre auditorías completas; estándar profesional.
* **MITRE ATT&CK**: excelente para ataques simulados avanzados; no es una metodología completa.

### 4.5 Metodología recomendada

Combinación de **PTES** como estructura general y **OWASP WSTG** para auditorías web, complementadas con **MITRE ATT&CK** en simulaciones de amenazas avanzadas.

---

## 5. Evaluación de herramientas de monitorización

Se seleccionan herramientas según funciones, coste, escalabilidad y facilidad de uso.

### 5.1 Herramientas seleccionadas

* **Nmap**: escaneo de puertos y servicios (gratuito).
* **Burp Suite**: auditoría web (versión Pro con licencia anual).
* **Nessus / OpenVAS**: escaneo de vulnerabilidades.
* **Wireshark**: captura de tráfico.
* **Metasploit**: explotación y post-explotación.
* **Gobuster/Dirbuster**: descubrimiento de recursos web.

### 5.2 Comparativa

* Burp Suite: interfaz intuitiva, gran potencia en pruebas web.
* Nessus: fácil de usar, alto nivel de automatización.
* OpenVAS: alternativa gratuita, con curva de aprendizaje mayor.
* Metasploit: marco completo para explotación.

---

## 6. Estimación de inversión

La inversión depende del tamaño del equipo y servicios ofrecidos.

### 6.1 Costes aproximados

* Burp Suite Pro: 399€/año.
* Nessus Pro: 3.000€/año.
* Hardware específico: 1.000€ - 1.500€.
* Formación anual: 800€.

---

## 7. Conclusiones

La investigación realizada permite establecer una base sólida para desarrollar servicios profesionales de auditoría ofensiva, apoyados en metodologías reconocidas y herramientas eficaces. El conocimiento de los principales tipos de ataques, sus fases y las herramientas adecuadas contribuye a realizar evaluaciones exhaustivas y alineadas con estándares del sector.

---
