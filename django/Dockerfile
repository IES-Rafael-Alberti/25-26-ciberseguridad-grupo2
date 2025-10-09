# Use a lightweight Python image
FROM python:3.12-slim

# Prevent Python from writing pyc files & enable unbuffered logging
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

# Set work directory
WORKDIR /app

# Install Django directly (no requirements.txt)
RUN pip install --no-cache-dir Django

# Copy project files
COPY . .

# Expose the Django dev server port
EXPOSE 8000

# Default command: run Django’s development server
CMD ["python", "manage.py", "runserver", "0.0.0.0:8000"]
