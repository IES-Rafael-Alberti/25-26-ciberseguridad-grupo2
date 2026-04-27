# Análisis Forense del Disco – Evidencias de Ataque y Keylogger

## 1. Evidencia de Keylogger

- Se localizó el archivo malicioso `keylogger.ps1` en la ruta:
	- `Users/admin/keylogger.ps1`
	- **MD5:** 69bd3261211c135c82e172e97dab60bf
	- **SHA1:** a740a8e7dae4f56fbf8f712dfe0d4b36dab632a7

- El archivo `keylogger.txt`, generado por el script, se encontró en:
	- `Users/admin/AppData/Local/Temp/keylogger.txt`
	- **MD5:** d3b19b15c35ff9dbad7d47fbb4ca3de9
	- **SHA1:** 65737aac86c93c6fd168bf7a1f3a91cb6a2b240d

- El contenido de `keylogger.txt` muestra credenciales capturadas, incluyendo el usuario y contraseña de Instagram:
	- Texto extraído: “instagram”, “lassandra”

- El keylogger aparece en documentos recientes y evidencia de ejecución, lo que indica que fue activamente utilizado.

![Captura: keylogger detectado](image.png)

## 2. Actividad en Instagram

- Se observa búsqueda de “instagram” en Google y acceso a la página de Instagram.
- Se capturaron cookies que confirman el inicio de sesión en Instagram.
- El `keylogger.txt` contiene las credenciales de acceso, lo que demuestra el robo de información.

![Captura: búsqueda de Instagram en Google](image-1.png)
![Captura: acceso a la página de Instagram](image-2.png)
![Captura: cookies confirmando inicio de sesión en Instagram](image-3.png)

## 3. Evidencia de Rubber Ducky

- Se confirma la conexión de un dispositivo tipo Rubber Ducky USB, utilizado para automatizar la ejecución del keylogger.
- El dispositivo aparece en documentos recientes como “DUCKY E://”, lo que refuerza la hipótesis de ataque automatizado.

![Captura: confirmación de Rubber Ducky USB](image-4.png)
![Captura: keylogger en documentos recientes](image-5.png)
![Captura: DUCKY E:// en documentos recientes](image-8.png)

## 4. Evidencia de Exfiltración de Credenciales

- El archivo `keylogger.txt` contiene el usuario y contraseña de Instagram capturados.

![Captura: keylogger.txt con usuario y contraseña de Instagram](image-6.png)

## 5. Manipulación del archivo hosts

- El archivo `hosts` fue modificado para redirigir dominios educativos (`moodle.cifprodolfoucha.es` y `www.moodle.cifprodolfoucha.es`) a la IP 192.168.33.66.
- Esto puede indicar un intento de ataque de redirección o phishing local.

![Captura: archivo hosts manipulado](image-7.png)

---