# ==========================================
# Dockerfile para API CRUD Usuarios .NET 9
# ==========================================

# 1️⃣ Imagen base con SDK para build
FROM mcr.microsoft.com/dotnet/sdk:9.0 AS build
WORKDIR /src

# Copiar archivos de proyecto y restaurar dependencias
COPY *.csproj .
RUN dotnet restore

# Copiar todo el código y compilar
COPY . .
RUN dotnet publish -c Release -o /app/publish

# 2️⃣ Imagen runtime ligera
FROM mcr.microsoft.com/dotnet/aspnet:9.0 AS runtime

# Crear usuario no-root para ejecutar la aplicación
RUN groupadd -r appuser && useradd -r -g appuser appuser

WORKDIR /app
COPY --from=build /app/publish .

# Cambiar permisos para el usuario no-root
RUN chown -R appuser:appuser /app

# Cambiar al usuario no-root
USER appuser

# Exponer puertos HTTPS y HTTP
EXPOSE 8080 8443

# Variables de entorno por defecto
ENV ASPNETCORE_URLS=https://+:8443;http://+:8080 \
    ASPNETCORE_ENVIRONMENT=Production

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8080/health || exit 1

# Comando por defecto
ENTRYPOINT ["dotnet", "Dotnet.dll"]
