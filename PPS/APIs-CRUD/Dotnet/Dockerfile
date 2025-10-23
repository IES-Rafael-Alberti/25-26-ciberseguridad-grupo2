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
WORKDIR /app
COPY --from=build /app/publish .

# Exponer puerto 8080
EXPOSE 8080

# Comando por defecto
ENTRYPOINT ["dotnet", "Dotnet.dll"]
