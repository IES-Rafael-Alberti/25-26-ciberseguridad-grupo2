# CRUD de la Entidad Usuario

## Descripción
Esta tarea consiste en implementar un **CRUD** (Crear, Leer, Actualizar y Eliminar) para la entidad `Usuario`. Esto permitirá gestionar los usuarios desde la API de manera sencilla.

### Entidad `Usuario`
Campos principales:
- `id` (Long): Identificador único del usuario.
- `nombre` (String)
- `apellidos` (String)
- `email` (String)
- `password` (String)


---

## Endpoints

### 1. Crear Usuario
- **Método:** POST  
- **URL:** `/usuarios`  
- **Request Body (JSON):**
```json
{
  "nombre": "Juan",
  "apellidos": "Pérez",
  "email": "juan@mail.com",
  "password": "1234"
}
```
- **Descripción:** Crea un nuevo usuario en la base de datos.

**Ejemplo cURL:**
```bash
curl -X POST http://localhost:8080/usuarios \
-H "Content-Type: application/json" \
-d '{"nombre":"Juan","apellidos":"Pérez","email":"juan@mail.com","password":"1234"}'
```

---

### 2. Obtener Todos los Usuarios
- **Método:** GET  
- **URL:** `/usuarios`  
- **Descripción:** Devuelve la lista de todos los usuarios registrados.

**Ejemplo cURL:**
```bash
curl -X GET http://localhost:8080/usuarios
```

---

### 3. Obtener Usuario por ID
- **Método:** GET  
- **URL:** `/usuarios/{id}`  
- **Descripción:** Devuelve los datos de un usuario específico según su ID.

**Ejemplo cURL:**
```bash
curl -X GET http://localhost:8080/usuarios/1
```

---

### 4. Actualizar Usuario
- **Método:** PUT  
- **URL:** `/usuarios/{id}`  
- **Request Body (JSON):**
```json
{
  "nombre": "Juan",
  "apellidos": "Pérez Actualizado",
  "email": "juan@mail.com",
  "password": "5678"
}
```
- **Descripción:** Actualiza los datos de un usuario específico.

**Ejemplo cURL:**
```bash
curl -X PUT http://localhost:8080/usuarios/1 \
-H "Content-Type: application/json" \
-d '{"nombre":"Juan","apellidos":"Pérez Actualizado","email":"juan@mail.com","password":"5678"}'
```

---

### 5. Eliminar Usuario
- **Método:** DELETE  
- **URL:** `/usuarios/{id}`  
- **Descripción:** Elimina un usuario según su ID.

**Ejemplo cURL:**
```bash
curl -X DELETE http://localhost:8080/usuarios/1
```

---

## Notas adicionales
- Manejar los **códigos de respuesta HTTP** correctamente:
  - 200 OK → Éxito en GET, PUT, DELETE
  - 201 Created → Éxito en POST
  - 404 Not Found → Usuario no encontrado
  - 400 Bad Request → Datos inválidos
- Validar que los campos `email` y `password` no estén vacíos.
- Para simplificar la tarea, no se implementa autenticación ni permisos.
