# Node.js Express Docker App

Este proyecto es una aplicación sencilla de Node.js usando Express, preparada para ejecutarse en Docker.

## Cómo ejecutar localmente

1. Instala las dependencias:
   npm install
2. Docker
    docker build -t nodejs .
    docker run -p 3000:3000 nodejs