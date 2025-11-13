using Microsoft.EntityFrameworkCore;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.IdentityModel.Tokens;
using System.Text;
using UsuariosApi.Data;
using AspNetCoreRateLimit;
using dotenv.net;

// Cargar variables de entorno desde .env
DotEnv.Load();

var builder = WebApplication.CreateBuilder(args);

// ==========================================
// CONFIGURACIÓN DESDE VARIABLES DE ENTORNO
// ==========================================
var jwtKey = Environment.GetEnvironmentVariable("JWT_KEY") 
    ?? builder.Configuration["Jwt:Key"]
    ?? throw new InvalidOperationException("JWT_KEY no configurada en variables de entorno o appsettings.json");

var jwtIssuer = Environment.GetEnvironmentVariable("JWT_ISSUER")
    ?? builder.Configuration["Jwt:Issuer"] 
    ?? "UsuariosApi";

var jwtAudience = Environment.GetEnvironmentVariable("JWT_AUDIENCE")
    ?? builder.Configuration["Jwt:Audience"]
    ?? "UsuariosApiClient";

var allowedOrigins = Environment.GetEnvironmentVariable("ALLOWED_ORIGINS")
    ?? builder.Configuration["Cors:AllowedOrigins"]
    ?? "https://localhost:3000";

// ==========================================
// AGREGAR SERVICIOS
// ==========================================

// Añadir DbContext
builder.Services.AddDbContext<UsuariosContext>(options =>
    options.UseSqlite(builder.Configuration.GetConnectionString("UsuariosDb")));

// Configuración JWT
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
            IssuerSigningKey = signingKey,
            ClockSkew = TimeSpan.Zero  // No permitir sesgo de reloj
        };
    });

// ==========================================
// CONFIGURACIÓN CORS RESTRICTIVO
// ==========================================
builder.Services.AddCors(options =>
{
    var origins = allowedOrigins.Split(',', System.StringSplitOptions.RemoveEmptyEntries);
    
    options.AddPolicy("RestrictedPolicy", policyBuilder =>
    {
        policyBuilder
            .WithOrigins(origins)
            .AllowAnyMethod()
            .AllowAnyHeader()
            .AllowCredentials()
            .WithExposedHeaders("Authorization");
    });
});

// Añadir controladores
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();

// ==========================================
// RATE LIMITING MEJORADO
// ==========================================
builder.Services.AddMemoryCache();
builder.Services.Configure<IpRateLimitOptions>(builder.Configuration.GetSection("IpRateLimiting"));
builder.Services.AddSingleton<IIpPolicyStore, MemoryCacheIpPolicyStore>();
builder.Services.AddSingleton<IRateLimitCounterStore, MemoryCacheRateLimitCounterStore>();
builder.Services.AddSingleton<IHttpContextAccessor, HttpContextAccessor>();
builder.Services.AddSingleton<IRateLimitConfiguration, RateLimitConfiguration>();
builder.Services.AddSingleton<IProcessingStrategy, AsyncKeyLockProcessingStrategy>();

// ==========================================
// CONSTRUIR APLICACIÓN
// ==========================================
var app = builder.Build();

// ==========================================
// MIGRACIONES AUTOMÁTICAS
// ==========================================
using (var scope = app.Services.CreateScope())
{
    var db = scope.ServiceProvider.GetRequiredService<UsuariosContext>();
    db.Database.Migrate();
}

// ==========================================
// MIDDLEWARE DE SEGURIDAD
// ==========================================

// HTTPS Redirection
if (app.Environment.IsProduction())
{
    app.UseHsts();  // Agregar HSTS en producción
}
app.UseHttpsRedirection();

// CORS debe estar antes de Authentication/Authorization
app.UseCors("RestrictedPolicy");

// Rate Limiting
app.UseIpRateLimiting();

// Security Headers
app.Use(async (context, next) =>
{
    context.Response.Headers.Add("X-Content-Type-Options", "nosniff");
    context.Response.Headers.Add("X-Frame-Options", "DENY");
    context.Response.Headers.Add("X-XSS-Protection", "1; mode=block");
    context.Response.Headers.Add("Referrer-Policy", "strict-origin-when-cross-origin");
    context.Response.Headers.Add("Permissions-Policy", "geolocation=(), microphone=(), camera=()");
    
    if (app.Environment.IsProduction())
    {
        context.Response.Headers.Add("Strict-Transport-Security", "max-age=31536000; includeSubDomains; preload");
    }
    
    await next();
});

// Autenticación y Autorización
app.UseAuthentication();
app.UseAuthorization();

// ==========================================
// MAPEO DE RUTAS
// ==========================================
app.MapControllers();

app.Run();
