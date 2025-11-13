using Microsoft.EntityFrameworkCore;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.IdentityModel.Tokens;
using System.Text;
using UsuariosApi.Data;

var builder = WebApplication.CreateBuilder(args);

// Añadir DbContext
builder.Services.AddDbContext<UsuariosContext>(options =>
    options.UseSqlite(builder.Configuration.GetConnectionString("UsuariosDb")));

// Configuración JWT desde appsettings
var jwtSection = builder.Configuration.GetSection("Jwt");
var jwtKey = jwtSection["Key"];
var jwtIssuer = jwtSection["Issuer"];
var jwtAudience = jwtSection["Audience"];

if (string.IsNullOrEmpty(jwtKey))
{
    throw new Exception("Jwt:Key no configurada en appsettings.json");
}

var signingKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtKey));

// Añadir autenticación JWT
builder.Services.AddAuthentication(options =>
{
    options.DefaultAuthenticateScheme = JwtBearerDefaults.AuthenticationScheme;
    options.DefaultChallengeScheme = JwtBearerDefaults.AuthenticationScheme;
})
    .AddJwtBearer(options =>
    {
        options.TokenValidationParameters = new TokenValidationParameters
        {
            ValidateIssuer = true,
            ValidateAudience = true,
            ValidateLifetime = true,
            ValidateIssuerSigningKey = true,
            ValidIssuer = jwtIssuer,
            ValidAudience = jwtAudience,
            IssuerSigningKey = signingKey
        };
    });

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

// Añadir middleware de autenticación antes de authorization
app.UseAuthentication();
app.UseAuthorization();

app.MapControllers();

app.Run();
