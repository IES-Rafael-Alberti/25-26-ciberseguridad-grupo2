using Microsoft.EntityFrameworkCore;
using UsuariosApi.Data;

var builder = WebApplication.CreateBuilder(args);

// Añadir DbContext
builder.Services.AddDbContext<UsuariosContext>(options =>
    options.UseSqlite(builder.Configuration.GetConnectionString("UsuariosDb")));

// Añadir controladores
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();

var app = builder.Build();

// Migraciones automáticas al iniciar
using (var scope = app.Services.CreateScope())
{
    var db = scope.ServiceProvider.GetRequiredService<UsuariosContext>();
    db.Database.Migrate(); // <-- Esto crea la DB y aplica migraciones
}


app.UseHttpsRedirection();
app.UseAuthorization();

app.MapControllers();

app.Run();
