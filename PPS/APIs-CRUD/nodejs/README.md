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

## Estudiar usando herramientas de IA las posibles vulnerabilidades de nuestras aplicaciones.

GitHub Copilot

* JWT_SECRET está en el repo (.env incluido) — secreto expuesto.
* src/models/user.js recrea la tabla en cada ejecución (User.sync({ force: true })) — pérdida de datos.
* getUsers / getUserById devuelven registros completos (incluyendo password).
* No hay validación/sanitización de entradas en register/login/create/update (riesgo de datos inválidos/inyección).
* CORS está totalmente abierto (app.use(cors())) — origenes no restringidos.
* No hay protección HTTP básica (helmet) ni limitador de peticiones (rate limiter).
* El middleware de autenticación mencionado en rutas (../middleware/authMiddleware.js) no aparece en el repositorio — riesgo de error o rutas no protegidas.
Controladores incompletos: hashing de password / generación de * JWT no implementados totalmente (archivo userController.js está parcial).
* No existe mecanismo de revocación/blacklist de tokens (solo expiración).

## Repetir el estudio de vulnerabilidades con la autenticación OAuth implementada

GitHub Copilot

* Inventario de secretos: confirmar que GITHUB_CLIENT_SECRET y JWT_SECRET no están en el repo.
* CSRF / state: la callback debe validar el parámetro state (generar/guardar y comparar).
* Redirect URI: comprobar que la URL de callback en la App de GitHub está restringida y coincide exactamente.
* Exposición de tokens: no incluir access_token / JWT en querystring ni logs; preferir cuerpo JSON o cookie segura.
* Scopes mínimos: comprobar que solo se solicitan scopes necesarios (user:email).
* Almacenamiento del secret: GITHUB_CLIENT_SECRET solo en servidor, no en frontend.
* Creación de usuario por OAuth: normalizar/sanitizar username/email; evitar roles automáticos (no crear admin).
* Revocación / expiración: revisar cómo se invalidan tokens JWT si se rota secret.
* CORS y orígenes: comprobar que el frontend autorizado es el único permitido.
* Logging: no registrar access_token, code, secrets ni emails sensibles.
* Rate limiting / brute force: asegurar límites en endpoints públicos (incluyendo /auth/github/callback).
* Dependencias: volver a ejecutar npm audit / Snyk / CodeQL ahora que añadiste fetch/bibliotecas.