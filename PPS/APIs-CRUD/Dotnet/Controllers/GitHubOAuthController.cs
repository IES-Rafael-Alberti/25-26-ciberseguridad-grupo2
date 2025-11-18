using Microsoft.AspNetCore.Mvc;
using Microsoft.IdentityModel.Tokens;
using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using UsuariosApi.Data;
using UsuariosApi.DTOs;
using UsuariosApi.Models;
using UsuariosApi.Services;
using Microsoft.EntityFrameworkCore;

namespace UsuariosApi.Controllers
{
    [ApiController]
    [Route("auth")]
    public class GitHubOAuthController : ControllerBase
    {
        private readonly IGitHubOAuthService _gitHubService;
        private readonly UsuariosContext _context;
        private readonly IConfiguration _configuration;
        private readonly ILogger<GitHubOAuthController> _logger;

        public GitHubOAuthController(
            IGitHubOAuthService gitHubService,
            UsuariosContext context,
            IConfiguration configuration,
            ILogger<GitHubOAuthController> logger)
        {
            _gitHubService = gitHubService;
            _context = context;
            _configuration = configuration;
            _logger = logger;
        }

        /// <summary>
        /// Inicia el flujo de autenticación con GitHub
        /// Redirige al usuario a GitHub para autorizar
        /// </summary>
        [HttpGet("github/login")]
        public IActionResult GitHubLogin()
        {
            try
            {
                var clientId = _configuration["GitHub:ClientId"];
                var redirectUri = _configuration["GitHub:RedirectUri"];

                if (string.IsNullOrEmpty(clientId) || string.IsNullOrEmpty(redirectUri))
                {
                    _logger.LogError("GitHub ClientId o RedirectUri no configurados");
                    return BadRequest(new { mensaje = "Configuración de GitHub no disponible" });
                }

                var scopes = "user:email";
                var state = Guid.NewGuid().ToString();

                // Guardar estado en sesión para validación de seguridad (opcional pero recomendado)
                HttpContext.Session.SetString("GitHubOAuthState", state);

                var authUrl = $"https://github.com/login/oauth/authorize?client_id={clientId}&redirect_uri={Uri.EscapeDataString(redirectUri)}&scope={scopes}&state={state}";

                _logger.LogInformation("Iniciando autenticación con GitHub");
                return Redirect(authUrl);
            }
            catch (Exception ex)
            {
                _logger.LogError("Error al iniciar autenticación con GitHub: {0}", ex.Message);
                return StatusCode(500, new { mensaje = "Error al iniciar autenticación" });
            }
        }

        /// <summary>
        /// Callback de GitHub - Maneja la respuesta de autorización
        /// </summary>
        [HttpGet("github/callback")]
        public async Task<IActionResult> GitHubCallback([FromQuery] string code, [FromQuery] string? state)
        {
            try
            {
                if (string.IsNullOrEmpty(code))
                {
                    _logger.LogWarning("Callback de GitHub sin código de autorización");
                    return BadRequest(new { mensaje = "Código de autorización no proporcionado" });
                }

                // Validar estado (seguridad CSRF)
                var savedState = HttpContext.Session.GetString("GitHubOAuthState");
                if (!string.IsNullOrEmpty(savedState) && savedState != state)
                {
                    _logger.LogWarning("Estado inválido en callback de GitHub");
                    return BadRequest(new { mensaje = "Estado inválido" });
                }

                // Obtener token
                var tokenResponse = await _gitHubService.ExchangeCodeForTokenAsync(code);
                if (tokenResponse == null)
                {
                    _logger.LogWarning("No se pudo obtener el token de GitHub");
                    return Unauthorized(new { mensaje = "No se pudo autenticar con GitHub" });
                }

                // Obtener información del usuario
                var gitHubUser = await _gitHubService.GetUserInfoAsync(tokenResponse.AccessToken);
                if (gitHubUser == null)
                {
                    _logger.LogWarning("No se pudo obtener la información del usuario de GitHub");
                    return Unauthorized(new { mensaje = "No se pudo obtener la información del usuario" });
                }

                // Buscar o crear usuario en base de datos
                var usuario = await _context.Usuarios.FirstOrDefaultAsync(u => 
                    u.Email == (gitHubUser.Email ?? $"{gitHubUser.Login}@github.local"));

                if (usuario == null)
                {
                    // Crear nuevo usuario
                    usuario = new Usuario
                    {
                        Email = gitHubUser.Email ?? $"{gitHubUser.Login}@github.local",
                        Nombre = gitHubUser.Name ?? gitHubUser.Login,
                        Apellidos = "GitHub User",
                        PasswordHash = BCrypt.Net.BCrypt.HashPassword(Guid.NewGuid().ToString()), // Contraseña aleatoria
                        CreatedAt = DateTime.UtcNow
                    };

                    _context.Usuarios.Add(usuario);
                    await _context.SaveChangesAsync();

                    _logger.LogInformation("Nuevo usuario creado desde GitHub: {0}", usuario.Email);
                }

                // Generar JWT
                var jwt = GenerateJWT(usuario);

                // Generar respuesta
                var loginResponse = new GitHubLoginResponseDto
                {
                    Mensaje = "Autenticación con GitHub exitosa",
                    Usuario = new UsuarioReadDto
                    {
                        Id = usuario.Id,
                        Nombre = usuario.Nombre,
                        Apellidos = usuario.Apellidos,
                        Email = usuario.Email
                    },
                    Token = jwt.Token,
                    Expiration = jwt.Expiration
                };

                _logger.LogInformation("Autenticación exitosa con GitHub para usuario: {0}", usuario.Email);

                // Redirigir a la página de inicio con el token
                var returnUrl = _configuration["GitHub:ReturnUrl"] ?? "http://localhost:8000";
                var redirectUrl = $"{returnUrl}?token={jwt.Token}&userId={usuario.Id}&email={Uri.EscapeDataString(usuario.Email)}";

                return Redirect(redirectUrl);
            }
            catch (Exception ex)
            {
                _logger.LogError("Error en callback de GitHub: {0}", ex.Message);
                return StatusCode(500, new { mensaje = "Error al procesar la autenticación" });
            }
        }

        /// <summary>
        /// Endpoint POST para obtener el callback (alternativa a GET si lo prefieres)
        /// </summary>
        [HttpPost("github/callback")]
        public async Task<IActionResult> GitHubCallbackPost([FromBody] GitHubCallbackRequestDto request)
        {
            try
            {
                if (string.IsNullOrEmpty(request.Code))
                {
                    return BadRequest(new { mensaje = "Código de autorización no proporcionado" });
                }

                // Obtener token
                var tokenResponse = await _gitHubService.ExchangeCodeForTokenAsync(request.Code);
                if (tokenResponse == null)
                {
                    return Unauthorized(new { mensaje = "No se pudo autenticar con GitHub" });
                }

                // Obtener información del usuario
                var gitHubUser = await _gitHubService.GetUserInfoAsync(tokenResponse.AccessToken);
                if (gitHubUser == null)
                {
                    return Unauthorized(new { mensaje = "No se pudo obtener la información del usuario" });
                }

                // Buscar o crear usuario
                var usuario = await _context.Usuarios.FirstOrDefaultAsync(u => 
                    u.Email == (gitHubUser.Email ?? $"{gitHubUser.Login}@github.local"));

                if (usuario == null)
                {
                    usuario = new Usuario
                    {
                        Email = gitHubUser.Email ?? $"{gitHubUser.Login}@github.local",
                        Nombre = gitHubUser.Name ?? gitHubUser.Login,
                        Apellidos = "GitHub User",
                        PasswordHash = BCrypt.Net.BCrypt.HashPassword(Guid.NewGuid().ToString()),
                        CreatedAt = DateTime.UtcNow
                    };

                    _context.Usuarios.Add(usuario);
                    await _context.SaveChangesAsync();

                    _logger.LogInformation("Nuevo usuario creado desde GitHub: {0}", usuario.Email);
                }

                // Generar JWT
                var jwt = GenerateJWT(usuario);

                return Ok(new GitHubLoginResponseDto
                {
                    Mensaje = "Autenticación con GitHub exitosa",
                    Usuario = new UsuarioReadDto
                    {
                        Id = usuario.Id,
                        Nombre = usuario.Nombre,
                        Apellidos = usuario.Apellidos,
                        Email = usuario.Email
                    },
                    Token = jwt.Token,
                    Expiration = jwt.Expiration
                });
            }
            catch (Exception ex)
            {
                _logger.LogError("Error en callback POST de GitHub: {0}", ex.Message);
                return StatusCode(500, new { mensaje = "Error al procesar la autenticación" });
            }
        }

        /// <summary>
        /// Genera un JWT para el usuario autenticado
        /// </summary>
        private (string Token, DateTime Expiration) GenerateJWT(Usuario usuario)
        {
            var jwtKey = _configuration["Jwt:Key"];
            var jwtIssuer = _configuration["Jwt:Issuer"];
            var jwtAudience = _configuration["Jwt:Audience"];
            var expiresMinutes = int.Parse(_configuration["Jwt:ExpiresMinutes"] ?? "60");

            var claims = new[]
            {
                new Claim(JwtRegisteredClaimNames.Sub, usuario.Id.ToString()),
                new Claim(JwtRegisteredClaimNames.Email, usuario.Email),
                new Claim("name", usuario.Nombre),
                new Claim("oauth_provider", "github")
            };

            var key = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtKey));
            var creds = new SigningCredentials(key, SecurityAlgorithms.HmacSha256);

            var expiration = DateTime.UtcNow.AddMinutes(expiresMinutes);
            var token = new JwtSecurityToken(
                issuer: jwtIssuer,
                audience: jwtAudience,
                claims: claims,
                expires: expiration,
                signingCredentials: creds
            );

            var tokenString = new JwtSecurityTokenHandler().WriteToken(token);
            return (tokenString, expiration);
        }
    }
}
