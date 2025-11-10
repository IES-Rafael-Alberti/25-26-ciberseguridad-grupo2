# 

# **Proyecto 1: Development of a Forensic Analysis Methodology**

![gsg](./img/image1.png)

**Autores**  
Pablo González · Carlos Alcina · David Jiménez · Luis Carlos Romero  
**Índice**

[1\. Introducción](#1.-introducción)

[2\. Objetivos del proyecto](#2.-objetivos-del-proyecto)

[3\. Investigación de normas y estándares forenses](#3.-investigación-de-normas-y-estándares-forenses)

[4\. Comparativa y selección de estándares](#4.-comparativa-y-selección-de-estándares)

[5\. Desarrollo de la metodología de análisis forense propia](#5.-desarrollo-de-la-metodología-de-análisis-forense-propia)

[Fase 0 — Preparación y planificación](#fase-0-—-preparación-y-planificación)

[Fase 1 — Identificación y recolección preliminar](#fase-1-—-identificación-y-recolección-preliminar)

[Fase 2 — Adquisición de evidencia digital](#fase-2-—-adquisición-de-evidencia-digital)

[Fase 3 — Preservación y almacenamiento](#fase-3-—-preservación-y-almacenamiento)

[Fase 4 — Análisis de evidencias](#fase-4-—-análisis-de-evidencias)

[Fase 5 — Documentación y elaboración del informe](#fase-5-—-documentación-y-elaboración-del-informe)

[Fase 6 — Presentación de resultados](#fase-6-—-presentación-de-resultados)

[6\. Resumen esquemático de la metodología](#6.-resumen-esquemático-de-la-metodología)

[7\. Conclusiones](#7.-conclusiones)

[8\. Bibliografía](#8.-bibliografía)

## 

## **1\. Introducción**

En el momento actual de digitalización constante  y aumento de incidentes de ciberseguridad, las investigaciones forenses digitales se han convertido en un imprescindible para la detección, análisis y resolución de incidentes informáticos.

Nosotros tenemos de fortalecer sus servicios de respuesta a incidentes, ha encargado al equipo forense el desarrollo de una metodología de análisis forense propia, que garantice la integridad, validez legal y eficiencia técnica en el tratamiento de evidencias electrónicas.

## **2\. Objetivos del proyecto**

1. **Investigar normas y estándares forenses:** Analizar los principales estándares reconocidos internacionalmente aplicables al análisis forense digital.

2. **Comparar y seleccionar estándares adecuados:** Evaluar y justificar las normas que mejor se adapten al contexto empresarial.

3. **Desarrollar una metodología de análisis forense propia:** Diseñar un proceso integral desde la adquisición hasta la presentación de resultados.

4. **Documentar y resumir la metodología:** Elaborar un resumen esquemático que facilite su aplicación práctica.

5. **Presentar los hallazgos:** Exponer la metodología de forma clara, organizada y profesional.

## 

## **3\. Investigación de normas y estándares forenses** 

Durante la fase de investigación se analizaron las normas y estándares más relevantes en el ámbito del análisis forense digital, tanto internacionales como nacionales:

| Norma / Estándar | Organismo | Enfoque principal | Aplicación |
| ----- | ----- | ----- | ----- |
| **ISO/IEC 27037** | ISO / IEC | Directrices para la identificación, recolección, adquisición y preservación de evidencias digitales. | Procedimientos formales y trazabilidad. |
| **ISO/IEC 27042** | ISO / IEC | Guía para el análisis e interpretación de evidencias digitales. | Establece criterios de validez y reproducibilidad. |
| **ISO/IEC 27043** | ISO / IEC | Principios y modelo genérico para investigaciones de incidentes. | Proceso estructurado de investigación. |
| **NIST SP 800-86** | NIST (EE.UU.) | Integración de técnicas forenses en la respuesta a incidentes. | Orientación práctica y operativa. |
| **NIST SP 800-101 Rev.1** | NIST (EE.UU.) | Guía para forense de dispositivos móviles. | Casos de móviles y tablets. |
| **UNE 71506:2013** | AENOR (España) | Metodología para el análisis forense de evidencias electrónicas. | Aplicación práctica y contexto legal español. |
| **DFRWS / CFTT** | NIST / DFRWS | Validación de herramientas forenses. | Garantía de fiabilidad técnica. |

## 

## **4\. Comparativa y selección de estándares** 

Tras analizar los diferentes marcos normativos, se observan las siguientes diferencias:

* **ISO/IEC 27037-27043:** garantizan rigor, trazabilidad y admisibilidad legal; establecen un proceso metodológico completo y reproducible.

* **NIST SP 800-86 / 800-101:** destacan por su enfoque práctico y técnico, ideal para entornos de respuesta a incidentes.

* **UNE 71506:2013:** adapta los principios internacionales al marco jurídico y procedimental español.

**Selección final:**  
 Se adopta una metodología híbrida basada en:

* **ISO/IEC 27037, 27042 y 27043** como marco estructural internacional.

* **NIST SP 800-86 y 800-101** como guías prácticas para la fase operativa.

* **UNE 71506:2013** como referencia legal y documental para la presentación de informes en el contexto nacional.

Esta combinación garantiza admisibilidad legal, consistencia técnica y flexibilidad práctica, adaptándose tanto a clientes internacionales como al marco regulatorio español.

## 

## **5\. Desarrollo de la metodología de análisis forense propia** 

La metodología propuesta abarca todas las fases del proceso forense, asegurando el cumplimiento de los principios de integridad, trazabilidad, reproducibilidad y documentación.

### **Fase 0 — Preparación y planificación**

Garantizar que las operaciones forenses se realizan en un entorno controlado y documentado.

**Acciones:**

* Establecer políticas de actuación y roles (Responsable Forense, Analista, Custodio de Evidencia).

* Validar herramientas conforme a estándares CFTT (Computer Forensic Tool Testing).

* Preparar formularios de cadena de custodia, bitácora y plantillas de informe.

* Asegurar que el entorno de trabajo esté aislado y con control de accesos.

### **Fase 1 — Identificación y recolección preliminar**

Determina los sistemas afectados y las fuentes de evidencia relevantes.

**Acciones:**

* Identificar los dispositivos, usuarios y servicios implicados.

* Documentar el estado de los sistemas antes de cualquier intervención.

* Priorizar la obtención de datos volátiles (memoria RAM, procesos, sesiones de red).

* Registrar cada acción con fecha, hora y responsable.

### **Fase 2 — Adquisición de evidencia digital** 

Obtener copias exactas de la evidencia sin alterar su contenido.

**Procedimientos:**

* Realizar imágenes bit-a-bit de discos duros con herramientas validadas (FTK Imager, dd, Guymager).

* Generar y registrar hashes criptográficos (SHA-256) antes y después de la adquisición.

* Extraer y preservar evidencias de dispositivos móviles (según NIST SP 800-101).

* Documentar todos los parámetros utilizados, versión de herramientas y operador responsable.

* Completar la cadena de custodia con firmas y sellado físico/lógico.

### **Fase 3 — Preservación y almacenamiento**

Mantener la integridad de las evidencias durante su manipulación y almacenamiento.

**Acciones:**

* Almacenar las evidencias en medios protegidos y de solo lectura.

* Registrar cada acceso o movimiento de evidencia.

* Mantener una copia original y una copia de trabajo separadas.

* Verificar periódicamente la integridad mediante re-hashing.

### **Fase 4 — Análisis de evidencias**

Identificar, examinar e interpretar la información contenida en las evidencias.

**Actividades:**

* Análisis de memoria y procesos (detección de malware, conexiones activas).

* Análisis de disco: recuperación de archivos, logs, metadatos y cronología de uso.

* Correlación de eventos en una línea temporal que permita reconstruir los hechos.

* Análisis de red: tráfico sospechoso, exfiltraciones o conexiones externas.

* Validación de hallazgos mediante herramientas y scripts reproducibles.

### **Fase 5 — Documentación y elaboración del informe** 

Garantizar que los hallazgos puedan ser revisados, verificados y defendidos legalmente.

**Estructura del informe técnico:**

1. Introducción y objetivos de la investigación.

2. Metodología utilizada (citando ISO/NIST/UNE).

3. Descripción de evidencias y cadena de custodia.

4. Resultados del análisis (hechos comprobados).

5. Conclusiones y recomendaciones.

6. Anexos: hashes, herramientas, comandos y logs relevantes.

Hemos realizado dos versiones:

* **Informe técnico completo**, dirigido a peritos o especialistas.

* **Resumen ejecutivo**, dirigido a la dirección del cliente.

### **Fase 6 — Presentación de resultados**

Exponer los resultados de manera clara y profesional.

**Acciones:**

* Preparar la presentación oral con apoyo visual (timeline, gráficas, resumen del proceso).

* Asegurar que los resultados estén sustentados por evidencias verificables.

* Firmar y archivar la documentación oficial y la cadena de custodia.

* Recomendar medidas preventivas y correctivas.

## **6\. Resumen esquemático de la metodología**

| Fase | Descripción | Resultados esperados |
| ----- | ----- | ----- |
| **Preparación** | Planificación, validación de herramientas, roles definidos. | Entorno controlado y documentación lista. |
| **Identificación** | Localización de sistemas y fuentes de evidencia. | Fuentes priorizadas y documentadas. |
| **Adquisición** | Obtención de copias forenses verificables. | Evidencias íntegras con hashes y custodia. |
| **Preservación** | Almacenamiento seguro y controlado. | Evidencias protegidas y trazables. |
| **Análisis** | Examen técnico y correlación de información. | Hallazgos reproducibles y validados. |
| **Documentación** | Elaboración de informes y registros. | Informes completos y admisibles legalmente. |
| **Presentación** | Exposición oral y entrega formal. | Presentación clara y profesional. |

## **7\. Conclusiones**

La metodología desarrollada cumple con los principios y fases establecidas en las normas ISO/IEC 27037, 27042 y 27043, complementadas por las guías NIST SP 800-86 y UNE 71506\.

Proporciona un proceso integral, legalmente admisible, reproducible y adaptable a diferentes tipos de incidentes digitales.

El enfoque híbrido propuesto combina rigurosidad normativa con aplicabilidad práctica, lo que permite a la empresa ofrecer servicios forenses de alta calidad, orientados a resultados verificables y útiles tanto en el ámbito técnico como judicial.

## **8\. Bibliografía** {#8.-bibliografía}

* ISO/IEC 27037:2012 — *Guidelines for identification, collection, acquisition and preservation of digital evidence.*

* ISO/IEC 27042:2015 — *Guidelines for analysis and interpretation of digital evidence.*

* ISO/IEC 27043:2015 — *Incident investigation principles and processes.*

* NIST SP 800-86 — *Guide to Integrating Forensic Techniques into Incident Response.*

* NIST SP 800-101 Rev.1 — *Guidelines on Mobile Device Forensics.*

* UNE 71506:2013 — *Metodología para el análisis forense de evidencias electrónicas (AENOR).*

* NIST CFTT — *Computer Forensic Tool Testing Program*
