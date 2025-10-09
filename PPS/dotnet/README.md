# .NET Web App con Docker

## Crear el proyecto .NET Web

```bash
dotnet new web -o MyServer
cd MyServer
dotnet run
```

Abre el navegador en la dirección que indique la consola para comprobar que la aplicación funciona correctamente.

---

## Crear el archivo `Dockerfile`

```dockerfile
FROM mcr.microsoft.com/dotnet/sdk:9.0 AS build
WORKDIR /app

COPY *.csproj .
RUN dotnet restore

COPY . .
RUN dotnet publish -c Release -o out

FROM mcr.microsoft.com/dotnet/aspnet:9.0 AS runtime
WORKDIR /app
COPY --from=build /app/out .

EXPOSE 8080

ENV ASPNETCORE_URLS=http://+:8080

ENTRYPOINT ["dotnet", "dotnet.dll"]
```

---

## Construir la imagen Docker

```bash
docker build -t myserver .
```

---

## Ejecutar el contenedor

```bash
docker run -p 8080:8080 myserver
```
Abre tu navegador en **http://localhost:8080** y deberías ver la aplicación .NET Web funcionando

![alt text](./img/image.png)
