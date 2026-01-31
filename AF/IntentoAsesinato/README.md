# A2.4 - Intento de asesinato

**Autores**: Carlos Alcina, Luis Carlos Romero, Pablo González

**Fecha**: 31 de Enero de 2026


---

## Índice

1. [Introducción](#1-introducción)  
2. [Metodología](#2-metodología)  
3. [Análisis del sistema](#3-análisis-del-sistema)  
4. [Investigación del sospechoso](#4-investigación-del-sospechoso)  
5. [Evidencias digitales](#5-evidencias-digitales)  
6. [Metadatos EXIF](#6-metadatos-exif)  
7. [Línea temporal](#7-línea-temporal)  
8. [Conclusiones](#8-conclusiones)

---
## 1. Introducción

Este trabajo se centra en el análisis forense de una imagen de disco asociada a una persona sospechosa de estar preparando un atentado contra una figura pública. A partir del contenido del sistema se buscan indicios para entender qué actividades realizó, a quién podría ir dirigido el ataque y qué preparativos llevó a cabo.

A lo largo del análisis se recopilan evidencias digitales (historial de navegación, archivos, imágenes y otros rastros de uso) para reconstruir los hechos. La información se organiza para responder a las cuestiones planteadas y conectar los hallazgos de forma comprensible.

---
## 2. Metodología

El análisis del equipo se realizó a partir de la imagen de disco utilizando **Autopsy**, siguiendo un procedimiento básico de forense digital.

- Identificamos los usuarios del sistema y trabajamos principalmente sobre la cuenta `pacopepe`.  
- Revisamos documentos, descargas e imágenes, apoyándonos en metadatos para obtener fechas y software utilizado.  
- Analizamos el historial web y la actividad de YouTube para inferir intereses y patrones de búsqueda.  
- Consultamos metadatos EXIF/XMP para ubicar archivos en el tiempo y ver su origen o edición.  
- Consolidamos los hallazgos en una **línea temporal** para visualizar la evolución de la actividad.

**Herramientas usadas:** Autopsy, Strings y hojas de cálculo para resumir la información relevante.  

---
## 3. Análisis del sistema

La imagen analizada corresponde a un sistema con **Windows 10 Education N** (arquitectura **AMD64**). El equipo figura como **DESKTOP-01S7HH9** y la instalación del sistema está en la ruta estándar `C:\Windows`.

El usuario principal es **pacopepe**, aparentemente la cuenta de uso habitual. La cuenta se creó el **05/04/2022**. El resto de cuentas visibles corresponden a perfiles típicos de Windows (por ejemplo, *Administrador*, *Invitado* y cuentas de servicio), sin indicios de relación directa con el caso.

La imagen se cargó en **Autopsy** para revisar archivos, usuarios y distintos registros del sistema. No se detectaron carpetas anómalas en una primera inspección, por lo que el foco se puso en los artefactos asociados a `pacopepe` y su actividad en el equipo.

---
## 4. Investigación

El historial de navegación muestra numerosas búsquedas vinculadas a noticias políticas, especialmente relacionadas con **Alberto Núñez Feijóo** y con la situación política en España. Tras consultar este tipo de información, se aprecia un cambio hacia búsquedas sobre Madrid, transporte, mapas y alojamiento, lo que sugiere la planificación de un desplazamiento.

Además, aparecen consultas sobre armerías en Galicia, lo que encaja con la posibilidad de que el usuario resida en esa comunidad y desde allí esté organizando el viaje.

### Personaje público relacionado

Las noticias más recurrentes están asociadas a **Alberto Núñez Feijóo**, por ejemplo:

- "Feijóo se lleva a Madrid a siete cargos del PPdeG y de la Xunta"  
- "Feijóo define como una casualidad política no menor el anuncio del espionaje a Sánchez y Robles"  
- "Feijóo: ‘Sánchez no tiene brújula, pero sus aliados sí tienen objetivos claros’"

No obstante, las búsquedas posteriores se orientan hacia el **Palacio de la Moncloa**. Esto permite deducir que el posible objetivo final estaría relacionado con **Pedro Sánchez**, al tratarse de la sede del presidente del Gobierno.

### Lugar planeado para el atentado

El lugar citado con más frecuencia es el **Palacio de la Moncloa (Madrid)**.
Entre las búsquedas se encuentran:

- "Mapa de situación del Palacio de la Moncloa"  
- "Acceso campus de Moncloa"  
- "Galería de la Moncloa"  
- Ubicaciones cercanas en Google Maps como A-6, Av. Puerta de Hierro, Autovía del Noroeste y C. Eduardo Saavedra

Adicionalmente, en la carpeta `Downloads` del usuario `pacopepe` se localizaron dos documentos:

- **folleto ordenado 2019.pdf**  
- **Indicaciones acceso MPR.pdf**

Los dos archivos incluyen información del complejo de la Moncloa y sus accesos.

### Posibles alojamientos

En el historial constan búsquedas de alojamiento en Madrid a través de distintas páginas:

- Trivago  
- Booking  
- Hoteles Riu Plaza España  

De esas consultas se derivan tres opciones concretas:

- **Hostal Condestable (Madrid)**  
- **Hostal Alaska**  
- **Hotel Riu Plaza España**

El **Hostal Condestable** parece ser la opción con mayor interés por parte del usuario, ya que se revisaron precios de distintos años.

---
## 5. Evidencias digitales

Durante el análisis se identificaron evidencias relacionadas con el contenido consumido por el usuario y con la búsqueda de material potencialmente asociado a la preparación del atentado.

### Programa de YouTube

En YouTube aparece repetidamente el programa **"Los minutos del odio"**, del creador **Fabián C. Barrio**, con más de 180 episodios.  
Es el único contenido que se repite de manera constante en el historial de YouTube y parece actuar como desencadenante, ya que tras su visualización se observan búsquedas más agresivas y orientadas a armas y política.

### Libro encontrado

En el equipo se localizó el archivo:

- **Libro de cocina del anarquista – William Powell.pdf**

El contenido del libro se centra en la fabricación de explosivos y otros métodos violentos, por lo que constituye una evidencia relevante para el caso.

### Búsqueda de armas y armerías

Se detectaron visitas a múltiples páginas de armerías, principalmente de Galicia, entre ellas:

- www.armeriabarreiro.com  
- acpsantos.com  
- armeriasestradense.com  
- www.armeriajardin.com  
- www.noiahistorica.com (Armería Romaní)  
- www.militaryarsenal.es  
- armeriadeportesel21.business.site  

Sin embargo, únicamente en **acpsantos.com** se observaron visitas con precios concretos de armas, por ejemplo:

- **GLOCK 26 Gen4 Cal. 9x19** – 1.350 €  
- **M&P9 PRO** – 1.299 €  
- **Smith & Wesson 637** – 1.280 €  

Además, en la carpeta de documentos del usuario se encontró el archivo **Armas.ods**, en el que se registraron precios de distintas armerías:

| Armería | Arma | Precio |
|--------|------|--------|
| Santos Cao | REMINGTON 597 VTR | 619,00 € |
| Santos Cao | REMINGTON MOD 597 | 395,00 € |
| Jardín | Cometa PCP Lynx V10 MKII | 695,00 € |
| Jardín | Hatsan Factor PCP | 620,00 € |

Este documento indica que el sospechoso no se limitaba a buscar armas: también comparaba precios y recopilaba la información de forma estructurada.

---
## 6. Metadatos EXIF

Al revisar el sistema se localizaron varias imágenes con metadatos EXIF/XMP. Estos datos se usan sobre todo para determinar **cuándo se crearon o editaron los archivos**, lo que facilita ubicarlos dentro de la línea temporal del caso.

### Archivos encontrados

1. **bg1a_thumb.png**  
   - **Ruta:** `/Program Files/WindowsApps/microsoft.windowscommunicationsapps_16005.14326.20858.0_x64__8wekyb3d8bbwe/images/bg1a_thumb.png`  
   - **Programa:** Windows Photo Editor  
   - **Fecha de creación:** 27/09/2017 – 16:05:12  
   - **Observaciones:** útil para la línea temporal y para identificar el programa utilizado.

2. **WelcomeScan.jpg** (dos copias)  
   - **Rutas:**  
     - `/ProgramData/Microsoft/Windows NT/MSScan/WelcomeScan.jpg`  
     - `/Windows/WinSxS/.../WelcomeScan.jpg`  
   - **Origen:** imagen de stock (Natphotos/Digital Vision/Getty Images)  
   - **Fecha de creación:** 09/04/2004 – 15:17:00  
   - **Observaciones:** archivo antiguo sin relevancia directa para el caso; aun así, evidencia presencia/actividad de archivos en el sistema.

3. **cloud.jpg**  
   - **Ruta:** `/Program Files/LibreOffice/share/palette/standard.sob/Pictures/cloud.jpg`  
   - **Programa:** darktable  
   - **Fecha de creación:** 22/01/2018 – 00:14:52  
   - **Observaciones:** archivo generado por software; no aporta información sobre la actividad del usuario.

### En resumen

- Solo **bg1a_thumb.png** aporta datos aprovechables para la línea temporal.  
- El resto de archivos únicamente confirman su existencia sin aportar información útil para la investigación.  
- La revisión de EXIF y XMP ayuda a ordenar la información y situar archivos en el tiempo sin entrar en detalles técnicos complejos.

---
## 7. Línea temporal

A partir del historial, los archivos y los metadatos del usuario **pacopepe**, se reconstruye una línea temporal aproximada de su actividad:

1. **Consumo de contenido en YouTube**  
   - Visualización repetida de la serie **"Los minutos del odio"** de Fabián C. Barrio.  
   - Este contenido aparece de forma constante en el historial y parece influir en el interés del usuario por temas políticos.

2. **Búsquedas de noticias políticas generales**  
   - Consultas sobre la situación política en España y noticias relacionadas con **Alberto Núñez Feijóo**.  
   - En esta fase la actividad parece solo informativa.

3. **Noticia clave: Feijóo y espionaje a Sánchez**  
   - Lectura de la noticia: *"Feijóo define como una casualidad política no menor el anuncio del espionaje a Sánchez y Robles"*.  
   - Este momento marca un **punto de inflexión**, ya que a partir de aquí el comportamiento del usuario cambia.

4. **Búsqueda de objetivos y planificación del desplazamiento**  
   - Investigaciones sobre el **Palacio de la Moncloa**, sus accesos y ubicaciones cercanas.  
   - Descarga de documentos con planos e indicaciones de entrada al complejo.

5. **Investigación de armas**  
   - Visitas a diferentes armerías, principalmente de Galicia.  
   - Consulta de precios y anotaciones en el archivo **Armas.ods**.  
   - Interés por modelos concretos de armas de fuego, lo que indica una planificación más detallada.

6. **Búsqueda de alojamiento en Madrid**  
   - Consultas en Trivago, Booking y Hoteles Riu Plaza España.  
   - Selección de opciones concretas: **Hostal Condestable**, **Hostal Alaska** y **Hotel Riu Plaza España**, comparando precios.

7. **Confirmación con metadatos EXIF**  
   - Archivos como **bg1a_thumb.png** permiten situar parte de la actividad en fechas concretas.  
   - El resto de imágenes no aportan información relevante para el caso, pero ayudan a completar la línea temporal.

---
## 8. Conclusiones

Del análisis del equipo de **pacopepe** se desprende un interés claro por temas políticos (especialmente noticias sobre **Feijóo** y la situación en España) y, posteriormente, el inicio de una planificación de viaje a **Madrid**. El foco principal parece situarse en el **Palacio de la Moncloa**, con búsquedas sobre mapas, accesos y zonas próximas, además de consultas de alojamientos.

En paralelo, el historial evidencia investigación de armerías y precios de armas, llegando incluso a registrar datos en el archivo (**Armas.ods**). Esto apunta a una actividad que va más allá de una consulta casual y refleja organización de pasos concretos. La presencia de contenido sobre fabricación de explosivos refuerza la peligrosidad del escenario.

En conjunto, el usuario estaba planificando un posible atentado político: recopiló información del lugar, transporte, alojamientos y armas, mostrando que había pensado **qué hacer y cómo hacerlo**.