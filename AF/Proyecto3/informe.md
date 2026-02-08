# Proyecto 3 – Elaboración del informe (Parte 2)

**Caso:** Unfaithful Employee – análisis forense del disco de Richard Eduardo Warner (InnovaTech Solutions)

**Fecha del informe:** 2026-02-08

---

## Índice

1. [Resumen ejecutivo](#1-resumen-ejecutivo)
2. [Alcance, objetivos y limitaciones](#2-alcance-objetivos-y-limitaciones)
3. [Adquisición y verificación de integridad](#3-adquisición-y-verificación-de-integridad)
4. [Metodología de análisis](#4-metodología-de-análisis)
5. [Línea de tiempo (cronología)](#5-línea-de-tiempo-cronología)
6. [Hallazgos técnicos](#6-hallazgos-técnicos)
7. [Conclusiones](#7-conclusiones)
8. [Recomendaciones](#8-recomendaciones)
9. [Anexo A – Índice de hallazgos](#anexo-a--índice-de-hallazgos)

---

## 1. Resumen ejecutivo

Durante el análisis forense del soporte asignado a **Richard Eduardo Warner** se han identificado indicios consistentes con **uso indebido del equipo corporativo**, **violación de políticas de seguridad** (uso de USB y software no estándar), **planificación de desplazamiento** posterior a su salida, y **evidencia directa de exfiltración/venta de información** mediante intercambio de correos y uso de un enlace a Google Drive con pago en Bitcoin.

No obstante, existe un factor crítico que condiciona el valor probatorio: **la verificación de integridad de la imagen forense no es consistente**, ya que **MD5 y SHA‑256 no coinciden con los hashes de referencia** facilitados por el departamento de sistemas, mientras que **SHA‑1 sí coincide**. Por ello, las conclusiones deben considerarse **indicativas a nivel interno** y **no aptas como evidencia pericial concluyente** hasta que se resuelva la discrepancia de integridad.

---

## 2. Alcance, objetivos y limitaciones

### 2.1 Alcance
- Análisis de la imagen forense proporcionada del disco de trabajo de Richard.
- Revisión de artefactos del sistema (usuarios, logins, equipo/SO), Registro de Windows (histórico USB y autoarranque), navegación web (Opera/Firefox) y correo (Thunderbird/MBOX), así como descargas relevantes.

### 2.2 Objetivos (según enunciado)
- Verificar integridad (hashes MD5/SHA‑1/SHA‑256) de la imagen.
- Construir cronología: inicios de sesión, navegación, dispositivos USB y eventos relevantes.
- Identificar actividad no autorizada (mal uso de recursos, exfiltración de datos).
- Documentar resultados y evidencia de forma clara y reproducible.

### 2.3 Limitaciones y consideraciones
- **Integridad no confirmada:** al no coincidir MD5 y SHA‑256 con las referencias, los artefactos y hallazgos deben tratarse como **potenciales** hasta disponer de una imagen íntegra y verificada.
- Parte de los campos solicitados para cada hallazgo (p. ej. **tamaño lógico** y **hash del fichero**) **no constan explícitamente en la Parte 1**. En el Anexo A se dejan marcados como **N/D** cuando no se dispone del dato en la documentación aportada.

---

## 3. Adquisición y verificación de integridad

### 3.1 Evidencia recibida
- **Imagen forense del disco duro** del equipo usado por Richard.
- **Hashes de referencia** (aportados por Alan, departamento de sistemas):
  - MD5: `dfdfba2231e3fa409676b1b737474208`
  - SHA‑1: `f476a81089a10f9d5393aa8c2f8bbccdb87f7d3c`
  - SHA‑256: `66d6ee7a61ea7a986e8f6bb54b9986f79d95b5a0278bef86678ed42ace320d96`

### 3.2 Hashes calculados y comparación
Hashes obtenidos por el equipo investigador:
- MD5 (calculado): `DFDFBA2231E3FA409676B1B737474288` → **NO coincide**
- SHA‑1 (calculado): `F476A81089A10F9D5393AA8C2F8BBCCDB87F7D3C` → **COINCIDE**
- SHA‑256 (calculado): `66D6EE7A61EA7A986E8F6BB54B9986F79D95B5A0278BEF86678ED42ACE320D9B` → **NO coincide**

Evidencia visual: `AF/Proyecto3/img/image-8.png`

### 3.3 Implicaciones
- La discrepancia **impide afirmar autenticidad e inmutabilidad** de la imagen con el nivel requerido en un contexto judicial.
- Recomendación: solicitar la **imagen original**, metadatos de adquisición y repetir adquisición si procede.

---

## 4. Metodología de análisis

La investigación se realizó mediante:
- Cálculo de hashes de la imagen con utilidades de línea de comandos (CMD/PowerShell).
- Identificación de usuario y último inicio de sesión mediante análisis de artefactos del sistema
- Determinación de equipo/SO a partir de información de sistema.
- Análisis del historial USB desde el Registro/artefactos del sistema.
- Revisión de navegación (Opera como navegador principal) y correlación temporal con accesos a Google Drive y sitios de viajes.
- Revisión de correo Thunderbird en formato MBOX para detectar evidencia explícita de exfiltración/venta.

---

## 5. Línea de tiempo (cronología)

> Nota: se listan los hitos más relevantes documentados en [la parte 1](investigacion.md).

| Fecha/Hora (CET) | Evento | Fuente/artefacto |
|---|---|---|
| 2023-02-20 20:21:46 | Lectura de noticias (La Vanguardia) | Historial Opera |
| 2023-02-20 20:26:27 | Visitas a Mundo Deportivo | Historial Opera |
| 2023-02-20 23:54:47 | Último intento fallido de contraseña | Artefactos de cuenta/seguridad |
| 2023-02-22 01:27:42 | Conexión USB: Kingston DataTraveler | Registro/USB history |
| 2023-02-22 01:32:38–01:36:23 | Búsquedas hoteles/vuelos (Booking/Vueling/Edreams) | Historial Opera |
| 2023-02-22 14:55:18 | Último inicio de sesión de `richard` | Artefactos de logon |
| 2023-02-22 15:06 (aprox.) | Confirmación de pago por “Tom” | Correos Thunderbird (MBOX) |
| 2023-02-22 22:46:40 | Descarga `pulseaudio-1.1.zip` | Descargas usuario |
| 2023-02-22 22:58:13–22:58:14 | Conexión ROOT_HUB30 / VirtualBox USB Tablet | Registro/USB history |

---

## 6. Hallazgos técnicos

### H1. Integridad de imagen no verificada completamente
- **Descripción:** SHA‑1 coincide con la referencia, pero MD5 y SHA‑256 difieren.
- **Impacto:** reduce el valor probatorio; requiere revalidación/recaptura.
- **Evidencia:** `AF/Proyecto3/img/image-8.png`.

### H2. Existencia del usuario `richard` y último login
- **Descripción:** se identifica cuenta local asociada a Richard (usuario estándar). Último inicio de sesión: **2023-02-22 14:55:18 CET**, con **16 logins**.
- **Evidencia visual:**
  - `AF/Proyecto3/img/image-1.png`
  - `AF/Proyecto3/img/image-3.png`
  - `AF/Proyecto3/img/image.png`

### H3. Identificación del equipo y sistema operativo
- **Nombre del equipo:** **LADRONERA**
- **SO:** **Windows 10 Pro Education N** (AMD64)
- **Owner:** Richard
- **Evidencia:** `AF/Proyecto3/img/image-2.png`

### H4. Uso de dispositivos USB (violación de política)
- **Descripción:** se documenta conexión de dispositivos USB el **22/02/2023**, incluyendo almacenamiento.
- **Dispositivos destacados:**
  - Kingston Technology DataTraveler 100 G3/G4/SE9 G2/50 – `002618525C8EF0B0E87D2853` – **01:27:42 CET**
  - ROOT_HUB30 – **22:58:13 CET**
  - VirtualBox USB Tablet – `58t12c8f4c0&0d1` – **22:58:14 CET**
- **Interpretación:** por su naturaleza de almacenamiento, el dispositivo Kingston es compatible con copia física de información.
- **Evidencia:** `AF/Proyecto3/img/image-4.png`

### H5. Actividad en línea (fútbol, rock/heavy y recursos corporativos)
- **Descripción:** actividad frecuente en YouTube (música rock/heavy) y medios deportivos (Mundo Deportivo), además de navegación de viajes (Vueling/Booking/Edreams).
- **Indicador de uso indebido:** accesos a YouTube **15:54–16:50 CET** (horario laboral).
- **Evidencia:**
  - `AF/Proyecto3/img/image-5.png`
  - `AF/Proyecto3/img/image-6.png`

### H6. Planificación de desplazamiento posterior (Las Palmas de Gran Canaria)
- **Descripción:** búsquedas de vuelos y hoteles a **Las Palmas de Gran Canaria** en la madrugada del 22/02.
- **Evidencia:** `AF/Proyecto3/img/image-7.png`

### H7. Software/navegadores no estándar y autoarranque
- **Descripción:** Opera como navegador principal (415 registros) y Firefox instalado. Opera configurado para ejecutarse al iniciar sesión.
- **Artefacto citado:** clave de ejecución en `HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Run`.
- **Evidencia:**
  - `AF/Proyecto3/img/image-9.png`
  - `AF/Proyecto3/img/image-11.png`

### H8. Evidencia directa de exfiltración/venta de información (correo + Drive + Bitcoin)
- **Descripción:** correos de Thunderbird (MBOX) muestran negociación con “Tom” e intercambio de enlace a Google Drive protegido por contraseña y pago en Bitcoin.
- **Indicadores clave:**
  - Correo de Tom: `proba2.seguridade@gmail.com`
  - Correo de Richard: `proba1.seguridade@gmail.com`
  - Enlace Drive: `https://drive.google.com/file/d/1Uw8umw_mZJdLbXfjBQzeRkeEQqzz-3s0/view?usp=share_link`
  - Dirección BTC: `bc1qar0srrr7xfkvy5l643lydnw9re59gtzzwf5mdq`
  - Contraseña compartida: `pa$$word @|`
- **Evidencia:** `AF/Proyecto3/img/image-10.png`

### H9. Descargas/actividad post‑exfiltración
- **Archivo:** `C:\Users\Richard\Downloads\pulseaudio-1.1.zip`
  - Descarga: **2023-02-22 22:46:40 CET**
  - Acceso: **2023-02-22 22:46:04 CET**
- **Carpeta:** `C:\Users\Richard\Ubuntu` (creación/modificación: 22/02/2023)
- **Interpretación:** preparación de entorno alternativo (Linux/VM) coherente con ocultación o continuación de actividades.

---

## 7. Conclusiones

### 7.1 Conclusiones técnicas
- La discrepancia de hashes (MD5/SHA‑256) obliga a tratar la imagen como **no verificada**; se requiere repetición del proceso de verificación con material fuente.
- Existe evidencia de **violación de políticas de seguridad**, incluyendo conexión de **USB de almacenamiento** el día clave.
- La correlación de eventos (USB, accesos a Drive, correo con contraseña/pago, y búsquedas de viaje) apoya un escenario de **exfiltración planificada**.
- La evidencia en correo (MBOX) contiene elementos explícitos de transacción (pago BTC, contraseña, enlace Drive), compatibles con **venta de información**.

### 7.2 Conclusiones ejecutivas (para dirección)
- Riesgo confirmado de **fuga de información** atribuible al usuario investigado, con indicios de monetización y coordinación con un tercero.
- Se recomienda activar respuesta a incidentes: contención, revocación de credenciales, análisis de alcance (qué datos pudieron salir), y medidas disciplinarias/legales cuando la integridad quede confirmada.

---

## 8. Recomendaciones

- **Integridad/cadena de custodia:** solicitar la imagen original, metadatos de adquisición y rehacer hashes; si es posible, reacquirir desde el dispositivo fuente con procedimiento documentado.
- **Contención y remediación:** reset de contraseñas, rotación de claves, revocación de tokens, revisión de accesos a repositorios internos y servicios cloud.
- **Prevención:** endurecer políticas de USB (bloqueo por GPO/EDR), listas de software permitido (AppLocker/WDAC), y monitorización de navegadores no corporativos.
- **Detección:** alertas por subida a cloud no corporativo (Drive), patrones de exfiltración y conexiones a wallets/cripto-exchanges.
