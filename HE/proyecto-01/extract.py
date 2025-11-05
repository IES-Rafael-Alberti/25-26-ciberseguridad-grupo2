import re
import base64
from pathlib import Path

# Ruta al README.md cargado
readme_path = Path("./README.md")

# Carpeta de salida para imágenes
output_dir = Path("./img")
output_dir.mkdir(exist_ok=True)

# Leer el contenido del README
text = readme_path.read_text(encoding="utf-8")

# Buscar imágenes base64
pattern = re.compile(r"\[image(\d+)\]:\s*<data:image/png;base64,([^>]+)>", re.DOTALL)
matches = pattern.findall(text)

# Guardar imágenes extraídas
saved_files = []
for idx, b64_data in matches:
    img_data = base64.b64decode(b64_data)
    img_path = output_dir / f"image{idx}.png"
    with open(img_path, "wb") as f:
        f.write(img_data)
    saved_files.append(str(img_path))

saved_files
