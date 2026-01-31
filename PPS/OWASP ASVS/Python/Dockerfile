# ==========================================
#  Dockerfile para la API CRUD de Usuarios
# ==========================================

# Imagen base ligera de Python
FROM python:3.13-slim

# Evita que Python genere archivos .pyc y buffer en stdout
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

# Establecer el directorio de trabajo dentro del contenedor
WORKDIR /FastAPI

# Instalar dependencias del sistema necesarias para bcrypt y SQLite
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libffi-dev \
    libssl-dev \
    sqlite3 \
 && rm -rf /var/lib/apt/lists/*

# Copiar los archivos de dependencias (si existe requirements.txt)
COPY requirements.txt .

# Instalar las dependencias de Python
RUN pip install --no-cache-dir -r requirements.txt

# Copiar el código fuente de la aplicación
COPY ./FastAPI ./FastAPI

# Exponer el puerto 8081 (donde correrá la API)
EXPOSE 8080

# Comando por defecto para ejecutar Uvicorn
CMD ["uvicorn", "FastAPI.main:app", "--host", "0.0.0.0", "--port", "8080"]
