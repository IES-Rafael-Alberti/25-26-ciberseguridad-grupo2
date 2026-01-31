using Microsoft.EntityFrameworkCore;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.IdentityModel.Tokens;
using System.Text;
using UsuariosApi.Data;
using UsuariosApi.Services;
using AspNetCoreRateLimit;
using dotenv.net;
using Microsoft.AspNetCore.Diagnostics;
using Microsoft.AspNetCore.Http;
using System.IdentityModel.Tokens.Jwt;

// Cargar variables de entorno desde .env
DotEnv.Load();

var builder = WebApplication.CreateBuilder(args);

// Configurar Kestrel para aceptar localhost
builder.WebHost.ConfigureKestrel(options =>
{
    // Permitir cualquier hostname en desarrollo
    if (builder.Environment.IsDevelopment())
    {
        options.AllowSynchronousIO = true;
    }
});

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

// Registrar servicio de GitHub OAuth
builder.Services.AddHttpClient<IGitHubOAuthService, GitHubOAuthService>(client =>
{
    var timeoutSecondsStr = Environment.GetEnvironmentVariable("OAUTH_HTTP_TIMEOUT_SECONDS")
        ?? builder.Configuration["GitHub:HttpTimeoutSeconds"]
        ?? "10";

    if (!double.TryParse(timeoutSecondsStr, out var timeoutSeconds) || timeoutSeconds <= 0)
    {
        timeoutSeconds = 10;
    }

    client.Timeout = TimeSpan.FromSeconds(timeoutSeconds);
});

// Añadir soporte para sesiones (con DistributedCache en memoria)
builder.Services.AddDistributedMemoryCache();
builder.Services.AddSession(options =>
{
    options.IdleTimeout = TimeSpan.FromMinutes(30);
    options.Cookie.HttpOnly = true;
    options.Cookie.IsEssential = true;
    options.Cookie.SameSite = SameSiteMode.Lax;
    options.Cookie.SecurePolicy = builder.Environment.IsProduction()
        ? CookieSecurePolicy.Always
        : CookieSecurePolicy.None;
});

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

        options.Events = new JwtBearerEvents
        {
            OnTokenValidated = async context =>
            {
                // Revocación por jti (logout). Best-effort con DistributedCache.
                var cache = context.HttpContext.RequestServices.GetRequiredService<IDistributedCache>();

                var jti = context.Principal?.FindFirst(JwtRegisteredClaimNames.Jti)?.Value;
                if (string.IsNullOrWhiteSpace(jti))
                {
                    return;
                }

                var revoked = await cache.GetStringAsync($"revoked:{jti}");
                if (!string.IsNullOrEmpty(revoked))
                {
                    context.Fail("Token revocado");
                }
            }
        };
    });

// ==========================================
// CONFIGURACIÓN CORS RESTRICTIVO
// ==========================================
builder.Services.AddCors(options =>
{
    if (builder.Environment.IsDevelopment())
    {
        // En desarrollo, permitir cualquier origen
        options.AddPolicy("RestrictedPolicy", policyBuilder =>
        {
            policyBuilder
                .AllowAnyOrigin()
                .AllowAnyMethod()
                .AllowAnyHeader()
                .WithExposedHeaders("Authorization");
        });
    }
    else
    {
        // En producción, restricción por origen
        var origins = allowedOrigins.Split(',', System.StringSplitOptions.RemoveEmptyEntries)
            .Select(o => o.Trim())
            .ToArray();
        
        options.AddPolicy("RestrictedPolicy", policyBuilder =>
        {
            policyBuilder
                .WithOrigins(origins)
                .AllowAnyMethod()
                .AllowAnyHeader()
                .AllowCredentials()
                .WithExposedHeaders("Authorization");
        });
    }
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
// MANEJO DE ERRORES GENÉRICO (ASVS V16.5.1)
// ==========================================
app.UseExceptionHandler(errorApp =>
{
    errorApp.Run(async context =>
    {
        context.Response.StatusCode = StatusCodes.Status500InternalServerError;
        context.Response.ContentType = "application/json";

        var exceptionHandler = context.Features.Get<IExceptionHandlerFeature>();
        if (exceptionHandler?.Error != null)
        {
            app.Logger.LogError(exceptionHandler.Error, "Unhandled exception");
        }

        await context.Response.WriteAsJsonAsync(new { mensaje = "Error interno del servidor" });
    });
});

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

// HTTPS Redirection (solo en producción)
if (app.Environment.IsProduction())
{
    app.UseHsts();  // Agregar HSTS en producción
    app.UseHttpsRedirection();
}

// Usar sesiones
app.UseSession();

// CORS debe estar antes de Authentication/Authorization
app.UseCors("RestrictedPolicy");

// Rate Limiting (deshabilitado temporalmente en desarrollo)
if (app.Environment.IsProduction())
{
    app.UseIpRateLimiting();
}

// Security Headers
app.Use(async (context, next) =>
{
    // Headers defensivos (ASVS V3)
    context.Response.Headers.TryAdd("X-Content-Type-Options", "nosniff");
    context.Response.Headers.TryAdd("X-Frame-Options", "DENY");
    context.Response.Headers.TryAdd("Referrer-Policy", "strict-origin-when-cross-origin");
    context.Response.Headers.TryAdd("Permissions-Policy", "geolocation=(), microphone=(), camera=()");

    // Evitar cachear respuestas con datos sensibles o tokens
    if (context.Request.Path.StartsWithSegments("/api/usuarios") || context.Request.Path.StartsWithSegments("/api/auth"))
    {
        context.Response.Headers.TryAdd("Cache-Control", "no-store");
        context.Response.Headers.TryAdd("Pragma", "no-cache");
    }
    
    if (app.Environment.IsProduction())
    {
        context.Response.Headers.TryAdd("Strict-Transport-Security", "max-age=31536000; includeSubDomains");
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
