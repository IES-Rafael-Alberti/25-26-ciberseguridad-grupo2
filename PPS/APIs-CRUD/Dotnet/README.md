# Python con FastAPI

###  [Enlace coleción Insomnia](./insomnia.json)

## Crear usuario
![alt text](./img/create.png)

## Eliminar usuario
![alt text](./img/delete.png)

## GetAll usuario
![alt text](./img/getall.png)

## GetById usuario
![alt text](./img/getid.png)

## Editar usuario
![alt text](./img/put.png)

## Login
![alt text](./img/login.png)

# Ejecutar proyecto 
Construir la imagen de Docker
```bash
docker build -t usuariosapi .  
```
Ejecutar el contenedor
```bash
docker run -d -p 8080:8080 --name usuariosapi usuariosapi
```
La API quedará accesible en:
```bash
http://localhost:8080
```