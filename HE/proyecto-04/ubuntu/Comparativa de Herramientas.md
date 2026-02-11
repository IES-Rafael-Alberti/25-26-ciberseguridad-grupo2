# Informe Comparativo de Herramientas de Análisis de Vulnerabilidades

**Proyecto:** Hunting Vulnerabilities - SecureLogistics

**Fecha:** 11 de Febrero de 2026

Tenemos como objetivo comparar la eficacia, precisión y presentación de resultados entre Tenable Nessus y Greenbone OpenVAS en un entorno de auditoría de Caja Negra.

## 1. Introducción y Metodología

Para este estudio comparativo se ha realizado un escaneo de vulnerabilidades sobre el mismo activo (Servidor Linux - IP: 192.168.122.209) utilizando dos herramientas líderes en el mercado bajo las mismas condiciones de red y sin credenciales (Caja Negra).

- Herramienta A: Tenable Nessus (Versión Comercial/Essentials)
- Herramienta B: Greenbone OpenVAS (Open Source)

## 2. Resumen Cuantitativo de Hallazgos

A continuación, se presenta una matriz comparativa basada en los informes generados por ambas herramientas.

| Métrica | Tenable Nessus (Caja Negra) | Greenbone OpenVAS |
| --- | --- | --- |
| Total Hallazgos | 46 | 8 (Filtrados por QoD > 70%) |
| Críticas | 1 | 0 |
| Altas (High) | 1 | 1 |
| Medias (Medium) | 6 | 4 |
| Bajas (Low) | 4 | 3 |
| Informativas | 34 | 0 (No mostradas en resumen) |
| Tiempo de Escaneo | Rápido (< 20 min estim.) | Lento (~2h 45min según reporte*) |

*Nota: El reporte de OpenVAS indica inicio a las 16:12 y fin a las 18:56, lo cual denota un tiempo de ejecución significativamente mayor.

## 3. Análisis Cualitativo

### 3.1. Capacidad de Detección (Precisión)

**Detección de Sistema Operativo (El factor diferenciador):**

- Nessus: Identificó correctamente que el sistema es Ubuntu 14.04.x. Lo más importante es que marcó esto como una vulnerabilidad CRÍTICA (Plugin 201408: Canonical Ubuntu Linux SEOL), alertando que el soporte ha finalizado y el sistema es inherentemente inseguro.
- OpenVAS: Aunque detectó servicios, no generó una alerta de seguridad de alto nivel indicando que el sistema operativo está obsoleto (End of Life), lo cual es un riesgo grave para la organización.

**Cifrados Débiles (SSL/TLS y SSH):**

Ambas herramientas detectaron eficazmente los problemas de cifrado.

- Coincidencia: Ambas identificaron la vulnerabilidad SWEET32 (CVE-2016-2183) como riesgo Alto.
- Nessus agrupó mejor los problemas de certificados SSL (Auto-firmados, no confiables).
- OpenVAS fue muy granular en los problemas de SSH (desglosando algoritmos KEX, MAC y algoritmos de cifrado en alertas separadas), lo cual es técnico pero puede abultar el reporte.

### 3.2. Calidad del Reporte y Usabilidad

**Nessus:**

- Pros: El informe es ejecutivo, visualmente limpio y fácil de interpretar para una gerencia (CISO). La clasificación por colores y la agrupación de vulnerabilidades ("Remediation") es superior.
- Claridad: Separa claramente la vulnerabilidad de la solución.

**OpenVAS:**

- Pros: Ofrece detalles técnicos muy profundos, incluyendo las referencias a los OID y métodos de detección.
- Contras: El formato PDF generado es muy plano, con mucho texto técnico y difícil de leer "de un vistazo". Divide un mismo problema (ej. debilidad en SSH) en múltiples hallazgos, lo que puede dar una falsa sensación de volumen de problemas sin priorizarlos adecuadamente.

## 4. Fortalezas y Debilidades

Basado en la experiencia de uso y los resultados obtenidos en este proyecto, se detallan las fortalezas y debilidades observadas en cada herramienta.

### Tenable Nessus

**Fortalezas (+):**

- Velocidad: El motor de escaneo es considerablemente más rápido, completando el análisis en una fracción del tiempo que requirió OpenVAS.
- Inteligencia de Amenazas: Su base de plugins (VPR) es capaz de priorizar vulnerabilidades contextuales, como identificar que un S.O. obsoleto es un riesgo Crítico inmediato.
- Reportes Ejecutivos: Genera informes listos para presentar a directivos, con gráficos claros y soluciones agrupadas.
- Facilidad de Uso: Interfaz intuitiva y proceso de instalación sencillo en múltiples plataformas.

**Debilidades (-):**

- Coste: Es una herramienta comercial. Aunque la versión Essentials es gratuita, tiene una limitación de 16 IPs, lo que no escala para empresas medianas sin pagar licencia.
- Caja Negra Limitada: Al igual que cualquier escáner, sin credenciales puede perderse configuraciones internas (aunque esto se mitigó en la Fase 3 del proyecto).

### Greenbone OpenVAS

**Fortalezas (+):**

- Coste Cero (Open Source): Al ser software libre, permite escanear un número ilimitado de IPs sin coste de licencia, ideal para consultoras pequeñas o estudiantes.
- Granularidad Técnica: Ofrece un nivel de detalle técnico muy alto en sus hallazgos, desglosando cada variante de un problema (útil para administradores de sistemas puristas).
- Flexibilidad: Permite una configuración muy profunda de los perfiles de escaneo si se tiene el conocimiento adecuado.

**Debilidades (-):**

- Rendimiento: El tiempo de escaneo fue excesivo (casi 3 horas para un solo host), lo cual puede ser inviable en redes grandes con ventanas de mantenimiento cortas.
- Falta de Contexto: No alertó sobre la obsolescencia crítica del S.O. en el resumen principal.
- Complejidad: La interfaz y la configuración inicial son complejas y propensas a errores.
- Reportes: Los informes por defecto son densos, planos y difíciles de digerir para personal no técnico.

## 5. Conclusión y Recomendación

Tras analizar los resultados de SecureLogistics:

**Herramienta Ganadora:** Tenable Nessus.

**Justificación:**

- Nessus fue capaz de identificar el riesgo más crítico del servidor: la obsolescencia del Sistema Operativo. OpenVAS pasó por alto clasificar esto como una vulnerabilidad crítica en su resumen.
- La velocidad de escaneo de Nessus fue superior.
- La presentación del informe de Nessus facilita la justificación de presupuesto ante la Junta Directiva, cumpliendo mejor con el objetivo de negocio del proyecto.

**Nota sobre Caja Blanca:**

Cabe destacar que, aunque esta comparativa se centró en Caja Negra, el análisis adicional de Caja Blanca con Nessus (realizado en la fase 3) reveló 172 vulnerabilidades (incluyendo 17 críticas como Log4j y Kernel exploits), demostrando que ningún escaneo de caja negra es suficiente para asegurar un activo por completo.