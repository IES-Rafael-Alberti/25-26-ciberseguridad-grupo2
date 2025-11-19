# Dossier de Servicios de Auditoría Ofensiva

## 1. Introducción

Este dossier presenta los servicios de auditoría ofensiva ofrecidos por nuestra empresa, describiendo en detalle su alcance, metodología aplicada y las herramientas utilizadas en cada caso. El objetivo es proporcionar a las organizaciones una visión clara y profesional de cómo evaluamos su seguridad ante amenazas reales.

---

## 2. Servicios de Auditoría Ofensiva

## 2.1 Auditoría de Seguridad de Aplicaciones Web

### **Descripción del servicio**

Evaluación exhaustiva de aplicaciones web para identificar vulnerabilidades que puedan comprometer datos, funcionalidades o infraestructura. Incluye análisis manual y automatizado.

### **Alcance**

* Validación de autenticación y gestión de sesiones
* Revisión de inyecciones (SQLi, XSS, LDAP, etc.)
* Validación de controles de acceso
* Pruebas de subida de archivos
* Fuzzing de entradas

### **Resultados esperados**

* Informe técnico con vulnerabilidades clasificadas por criticidad
* Recomendaciones precisas de mitigación
* Evidencias técnicas del impacto

### **Metodología aplicada**

* Basada en **OWASP Web Security Testing Guide (WSTG)**
* Enmarcada dentro de PTES para fases previas y posteriores

### **Herramientas empleadas**

* Burp Suite Pro
* OWASP ZAP
* Gobuster/Dirbuster
* SQLMap
* Nmap

---

## 2.2 Auditoría de Red Interna

### **Descripción del servicio**

Simulación de un atacante con acceso a la red interna de la organización. Se evalúa la robustez del entorno, configuraciones, accesos y posibles rutas de movimiento lateral.

### **Alcance**

* Descubrimiento de equipos y servicios
* Enumeración de puertos, protocolos y shares
* Pruebas de explotación interna
* Escalada de privilegios
* Movimiento lateral
* Validación de segmentación de red

### **Resultados esperados**

* Inventario real de la red
* Identificación de vectores de ataque internos
* Recomendaciones para mejorar segmentación y hardening

### **Metodología aplicada**

* Basada en PTES
* Tácticas mapeadas con MITRE ATT&CK

### **Herramientas empleadas**

* Nmap
* Nessus/OpenVAS
* BloodHound
* Mimikatz
* Metasploit Framework
* Wireshark

---

## 2.3 Auditoría de Seguridad en Dispositivos IoT

### **Descripción del servicio**

Evaluación de la seguridad en dispositivos conectados, incluyendo dispositivos industriales, cámaras IP, sensores, routers, etc.

### **Alcance**

* Identificación de firmware vulnerable
* Pruebas de credenciales por defecto
* Análisis de servicios expuestos
* Validación de comunicaciones (cifrado, autenticación)
* Explotación de vulnerabilidades conocidas

### **Resultados esperados**

* Informe detallado de riesgos
* Identificación de configuraciones inseguras
* Propuestas de endurecimiento

### **Metodología aplicada**

* PTES adaptado a entornos IoT
* Mapeo de tácticas con MITRE ATT&CK for ICS (si aplica)

### **Herramientas empleadas**

* Nmap
* Firmware Analysis Toolkit
* Shodan (cuando aplica)
* Wireshark
* Metasploit

---

## 3. Metodología General Aplicada en Todos los Servicios

Se adopta un enfoque híbrido combinando tres referencias principales:

### **3.1 PTES (Penetration Testing Execution Standard)**

* Define el ciclo completo de auditoría ofensiva
* Abarca desde la planificación hasta el reporte final

### **3.2 OWASP WSTG**

* Aplicado en auditorías web para pruebas específicas y profundas

### **3.3 MITRE ATT&CK**

* Utilizado para alinear ataques simulados con tácticas empleadas por adversarios reales

### **Fases aplicadas en cada auditoría**

1. Reconocimiento
2. Enumeración
3. Explotación
4. Escalada de privilegios
5. Persistencia y movimiento lateral (si aplica)
6. Post-explotación
7. Generación de informe

---

## 4. Herramientas de Auditoría por Servicio

| Servicio                 | Herramientas principales                                           |
| ------------------------ | ------------------------------------------------------------------ |
| Auditoría Web            | Burp Suite, ZAP, Nmap, SQLMap, Gobuster                            |
| Auditoría de Red Interna | Nessus, OpenVAS, Nmap, BloodHound, Mimikatz, Metasploit, Wireshark |
| Auditoría IoT            | Firmware Analysis Toolkit, Nmap, Wireshark, Metasploit             |

---

## 5. Conclusión

Los servicios de auditoría ofensiva aquí descritos permiten evaluar la seguridad de una organización desde la perspectiva de un atacante real. El uso de metodologías internacionales y herramientas reconocidas garantiza un análisis exhaustivo y profesional, orientado a identificar vulnerabilidades críticas y a fortalecer la postura de seguridad del cliente.
