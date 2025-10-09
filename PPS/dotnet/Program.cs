var builder = WebApplication.CreateBuilder(args);
var app = builder.Build();

app.MapGet("/", () => "Hola mundo desde del Grupo 2 de Ciberseguridad 25-26 !!!!");

app.Run();
