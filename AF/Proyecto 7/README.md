# Proyecto 7

## Índice

1. [Juramento y declaración de abstención](#1-juramento-y-declaración-de-abstención)
2. [Palabras clave](#2-palabras-clave)
3. [Índice de figuras](#3-índice-de-figuras)
4. [Resumen Ejecutivo](#4-resumen-ejecutivo)
5. [Introducción](#5-introducción)
	1. [Antecedentes](#51-antecedentes)
	2. [Objetivos](#52-objetivos)
6. [Fuentes de información](#6-fuentes-de-información)
	1. [Comprobación de hashes (MD5 y SHA1)](#61-comprobación-de-hashes-md5-y-sha-1)
	2. [Adquisición de hallazgos](#62-adquisición-de-hallazgos)
7. [Análisis](#7-análisis)
	1. [Herramientas utilizadas](#71-herramientas-utilizadas)
	2. [Procesos](#72-procesos)
		1. [Análisis de la imagen de disco](#721-análisis-de-la-imagen-de-disco)
		2. [Análisis del volcado de memoria](#722-análisis-del-volcado-de-memoria)
		3. [Cronología del ataque](#73-cronología-del-ataque)
8. [Limitaciones](#8-limitaciones)
9. [Conclusiones](#9-conclusiones)
10. [Anexo 1. Sobre el perito](#10-anexo-1-sobre-el-perito)
11. [Anexo 2. Cadena de custodia](#11-anexo-2-cadena-de-custodia)
12. [Anexo 3. Otras necesidades](#12-anexo-3-otras-necesidades)

## 1. Juramento y declaración de abstención

Los peritos abajo firmantes manifiestan, bajo juramento o promesa de decir verdad, que han actuado y actuarán con la mayor objetividad posible, considerando tanto lo que pueda favorecer como lo que pueda perjudicar a cualquiera de las partes. Asimismo, declaran conocer las sanciones penales en las que podrían incurrir si incumplen su deber como peritos.

En cumplimiento de las mejores prácticas y estándares de la industria, los peritos declaran expresamente:

- Que no existe conflicto de interés alguno que pueda comprometer la objetividad del presente informe.
- Que no tienen parentesco, vínculo matrimonial o situación de hecho asimilable con ninguna de las partes, ni con sus abogados o procuradores.
- Que no tienen interés directo ni indirecto en el objeto del pleito ni en su resolución.
- Que no han prestado servicios profesionales anteriormente a ninguna de las partes en relación directa con este caso.

<table>
	<thead>
		<tr>
			<th>Nombre y Apellidos</th>
			<th>Cargo / Titulación</th>
			<th>Firma</th>
			<th>Fecha</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Carlos Alcina</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM)</td>
			<td><img src="img/firma_carlos.png" alt="Firma Carlos Alcina" height="60"></td>
			<td>27/04/2026</td>
		</tr>
		<tr>
			<td>Pablo González</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Multiplataforma (DAM) y Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="img/firma_pg.jpeg" alt="Firma de Pablo González" height="60"></td>
			<td>27/04/2026</td>
		</tr>
		<tr>
			<td>Luis Carlos Romero</td>
			<td>Técnico Superior en Desarrollo de Aplicaciones Web (DAW)</td>
			<td><img src="img/lc_firma.png" alt="Firma de Luis Carlos Romero" height="60"></td>
			<td>27/04/2026</td>
		</tr>
	</tbody>
</table>

