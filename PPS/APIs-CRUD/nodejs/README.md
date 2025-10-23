# CRUD de Usuario en Node.js

## Descripción

Este proyecto es un **CRUD (Crear, Leer, Actualizar y Eliminar)** de la entidad `Usuario` desarrollado con **Node.js y Express**, usando **SQLite** como base de datos para simplificar las pruebas. Permite gestionar usuarios desde una API REST y probar todos los endpoints fácilmente desde Postman.

---

## Tecnologías

* Node.js 18+
* Express 4.x
* SQLite3
* Sequelize (ORM)
* Docker (opcional para desplegar la app)

---

## Endpoints de la API

### 1. Crear Usuario

* **Método:** POST
* **URL:** `/usuarios`
* **Body (JSON):**

```json
{
  "nombre": "Juan",
  "apellidos": "Pérez",
  "email": "juan@mail.com",
  "password": "1234"
}
```

* **Respuesta:** 201 Created

### 2. Obtener Todos los Usuarios

* **Método:** GET
* **URL:** `/usuarios`
* **Respuesta:** Lista de usuarios, 200 OK

### 3. Obtener Usuario por ID

* **Método:** GET
* **URL:** `/usuarios/{id}`
* **Respuesta:** Usuario con el ID especificado, 200 OK
* **Si no existe:** 404 Not Found

### 4. Actualizar Usuario

* **Método:** PUT
* **URL:** `/usuarios/{id}`
* **Body (JSON):**

```json
{
  "nombre": "Juan",
  "apellidos": "Pérez Actualizado",
  "email": "juan@mail.com",
  "password": "5678"
}
```

* **Respuesta:** Usuario actualizado, 200 OK
* **Si no existe:** 404 Not Found

### 5. Eliminar Usuario

* **Método:** DELETE
* **URL:** `/usuarios/{id}`
* **Respuesta:** 200 OK

---

## Ejecutar el proyecto

### Local

1. Clonar el repositorio:

```bash
git clone <url-del-repo>
```

2. Instalar dependencias:

```bash
npm install
```

3. Ejecutar la aplicación:

```bash
node src/index.js
```

4. La API estará disponible en:

```
http://localhost:8080/usuarios
```

### Con Docker

1. Construir la imagen:

```bash
docker build -t crud-node .
```

2. Ejecutar el contenedor:

```bash
docker run -p 8080:8080 crud-node
```

---

## Probar con Postman

1. Abrir Postman y usar los endpoints descritos arribas.