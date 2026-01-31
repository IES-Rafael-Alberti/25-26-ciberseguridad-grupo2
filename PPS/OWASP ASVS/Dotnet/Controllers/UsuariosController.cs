using BCrypt.Net;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using UsuariosApi.Data;
using UsuariosApi.DTOs;
using UsuariosApi.Models;
using Microsoft.AspNetCore.Authorization;
using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using Microsoft.IdentityModel.Tokens;
using System.Text;
using Microsoft.Extensions.Configuration;

namespace UsuariosApi.Controllers
{
    [ApiController]
    [Route("api/usuarios")]
    [Authorize]
    public class UsuariosController : ControllerBase
    {
        private readonly UsuariosContext _context;
        private readonly IConfiguration _configuration;
        private readonly ILogger<UsuariosController> _logger;

        public UsuariosController(UsuariosContext context, IConfiguration configuration, ILogger<UsuariosController> logger)
        {
            _context = context;
            _configuration = configuration;
            _logger = logger;
        }

        // GET /api/usuarios
        [HttpGet]
        public async Task<ActionResult<IEnumerable<UsuarioReadDto>>> GetUsuarios()
        {
            try
            {
                var usuarios = await _context.Usuarios
                    .Select(u => new UsuarioReadDto
                    {
                        Id = u.Id,
                        Nombre = u.Nombre,
                        Apellidos = u.Apellidos,
                        Email = u.Email
                    })
                    .ToListAsync();

                return Ok(usuarios);
            }
            catch (Exception ex)
            {
                _logger.LogError("Error al obtener usuarios: {0}", ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
        }

        // GET /api/usuarios/{id}
        [HttpGet("{id:long}")]
        public async Task<ActionResult<UsuarioReadDto>> GetUsuario(long id)
        {
            try
            {
                // Verificar que el usuario solo pueda ver su propio perfil
                var usuarioId = GetUsuarioIdDelToken();
                if (usuarioId != id)
                {
                    _logger.LogWarning("Intento de acceso no autorizado al usuario {0} por usuario {1}", id, usuarioId);
                    return Forbid();
                }

                var usuario = await _context.Usuarios.FindAsync(id);
                if (usuario == null)
                    return NotFound(new { mensaje = "Usuario no encontrado" });

                var dto = new UsuarioReadDto
                {
                    Id = usuario.Id,
                    Nombre = usuario.Nombre,
                    Apellidos = usuario.Apellidos,
                    Email = usuario.Email
                };

                return Ok(dto);
            }
            catch (Exception ex)
            {
                _logger.LogError("Error al obtener usuario {0}: {1}", id, ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
        }

        // POST /api/usuarios (Registro)
        [HttpPost]
        [AllowAnonymous]
        public async Task<ActionResult<UsuarioReadDto>> CreateUsuario(UsuarioCreateDto createDto)
        {
            try
            {
                // La validación de Data Annotations se ejecuta automáticamente
                if (!ModelState.IsValid)
                {
                    return BadRequest(ModelState);
                }

                // Verificar que el email no esté registrado
                if (await _context.Usuarios.AnyAsync(u => u.Email == createDto.Email.ToLower()))
                {
                    return BadRequest(new { mensaje = "El email ya está registrado" });
                }

                var usuario = new Usuario
                {
                    Nombre = createDto.Nombre.Trim(),
                    Apellidos = createDto.Apellidos.Trim(),
                    Email = createDto.Email.ToLower(),
                    PasswordHash = BCrypt.Net.BCrypt.HashPassword(createDto.Password),
                    CreatedAt = DateTime.UtcNow
                };

                _context.Usuarios.Add(usuario);
                await _context.SaveChangesAsync();

                _logger.LogInformation("Nuevo usuario registrado: {0}", usuario.Email);

                var readDto = new UsuarioReadDto
                {
                    Id = usuario.Id,
                    Nombre = usuario.Nombre,
                    Apellidos = usuario.Apellidos,
                    Email = usuario.Email
                };

                return CreatedAtAction(nameof(GetUsuario), new { id = usuario.Id }, readDto);
            }
            catch (DbUpdateException ex)
            {
                _logger.LogError("Error de base de datos al crear usuario: {0}", ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
            catch (Exception ex)
            {
                _logger.LogError("Error inesperado al crear usuario: {0}", ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
        }

        // PUT /api/usuarios/{id}
        [HttpPut("{id:long}")]
        public async Task<ActionResult<UsuarioReadDto>> UpdateUsuario(long id, UsuarioUpdateDto updateDto)
        {
            try
            {
                // La validación de Data Annotations se ejecuta automáticamente
                if (!ModelState.IsValid)
                {
                    return BadRequest(ModelState);
                }

                // Verificar que el usuario solo pueda actualizar su propio perfil
                var usuarioId = GetUsuarioIdDelToken();
                if (usuarioId != id)
                {
                    _logger.LogWarning("Intento de actualización no autorizado del usuario {0} por usuario {1}", id, usuarioId);
                    return Forbid();
                }

                var usuario = await _context.Usuarios.FindAsync(id);
                if (usuario == null)
                    return NotFound(new { mensaje = "Usuario no encontrado" });

                // Verificar si el nuevo email ya está en uso por otro usuario
                if (usuario.Email != updateDto.Email.ToLower() && 
                    await _context.Usuarios.AnyAsync(u => u.Email == updateDto.Email.ToLower()))
                {
                    return BadRequest(new { mensaje = "El email ya está registrado" });
                }

                usuario.Nombre = updateDto.Nombre.Trim();
                usuario.Apellidos = updateDto.Apellidos.Trim();
                usuario.Email = updateDto.Email.ToLower();
                usuario.PasswordHash = BCrypt.Net.BCrypt.HashPassword(updateDto.Password);
                usuario.UpdatedAt = DateTime.UtcNow;

                await _context.SaveChangesAsync();

                _logger.LogInformation("Usuario {0} actualizado", usuario.Email);

                var readDto = new UsuarioReadDto
                {
                    Id = usuario.Id,
                    Nombre = usuario.Nombre,
                    Apellidos = usuario.Apellidos,
                    Email = usuario.Email
                };

                return Ok(readDto);
            }
            catch (DbUpdateException ex)
            {
                _logger.LogError("Error de base de datos al actualizar usuario {0}: {1}", id, ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
            catch (Exception ex)
            {
                _logger.LogError("Error inesperado al actualizar usuario {0}: {1}", id, ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
        }

        // DELETE /api/usuarios/{id}
        [HttpDelete("{id:long}")]
        public async Task<IActionResult> DeleteUsuario(long id)
        {
            try
            {
                // Verificar que el usuario solo pueda eliminar su propia cuenta
                var usuarioId = GetUsuarioIdDelToken();
                if (usuarioId != id)
                {
                    _logger.LogWarning("Intento de eliminación no autorizado del usuario {0} por usuario {1}", id, usuarioId);
                    return Forbid();
                }

                var usuario = await _context.Usuarios.FindAsync(id);
                if (usuario == null)
                    return NotFound(new { mensaje = "Usuario no encontrado" });

                _context.Usuarios.Remove(usuario);
                await _context.SaveChangesAsync();

                _logger.LogInformation("Usuario {0} eliminado", usuario.Email);

                return Ok(new { mensaje = "Usuario eliminado correctamente" });
            }
            catch (DbUpdateException ex)
            {
                _logger.LogError("Error de base de datos al eliminar usuario {0}: {1}", id, ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
            catch (Exception ex)
            {
                _logger.LogError("Error inesperado al eliminar usuario {0}: {1}", id, ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
        }

        // POST /api/usuarios/login
        [HttpPost("login")]
        [AllowAnonymous]
        public async Task<ActionResult<LoginResponseDto>> Login(UsuarioLoginDto loginDto)
        {
            try
            {
                if (string.IsNullOrWhiteSpace(loginDto.Email) || string.IsNullOrWhiteSpace(loginDto.Password))
                {
                    _logger.LogWarning("Intento de login con credenciales vacías");
                    return BadRequest(new { mensaje = "Email y contraseña son obligatorios" });
                }

                var usuario = await _context.Usuarios.FirstOrDefaultAsync(u => u.Email == loginDto.Email.ToLower());
                if (usuario == null)
                {
                    _logger.LogWarning("Intento de login con email no registrado: {0}", loginDto.Email);
                    return Unauthorized(new { mensaje = "Credenciales inválidas" });
                }

                bool passwordValida = BCrypt.Net.BCrypt.Verify(loginDto.Password, usuario.PasswordHash);
                if (!passwordValida)
                {
                    _logger.LogWarning("Intento de login fallido para usuario: {0}", usuario.Email);
                    return Unauthorized(new { mensaje = "Credenciales inválidas" });
                }

                // Generar JWT
                var jwtKey = _configuration["Jwt:Key"];
                if (string.IsNullOrEmpty(jwtKey))
                {
                    _logger.LogError("Jwt:Key no configurada");
                    return StatusCode(500, new { mensaje = "Error interno del servidor" });
                }

                var jwtIssuer = _configuration["Jwt:Issuer"];
                var jwtAudience = _configuration["Jwt:Audience"];
                var expiresMinutes = int.Parse(_configuration["Jwt:ExpiresMinutes"] ?? "60");

                var claims = new[]
                {
                    new Claim(JwtRegisteredClaimNames.Sub, usuario.Id.ToString()),
                    new Claim(JwtRegisteredClaimNames.Email, usuario.Email),
                    new Claim("name", usuario.Nombre)
                };

                var key = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtKey));
                var creds = new SigningCredentials(key, SecurityAlgorithms.HmacSha256);

                var token = new JwtSecurityToken(
                    issuer: jwtIssuer,
                    audience: jwtAudience,
                    claims: claims,
                    expires: DateTime.UtcNow.AddMinutes(expiresMinutes),
                    signingCredentials: creds
                );

                var tokenString = new JwtSecurityTokenHandler().WriteToken(token);

                _logger.LogInformation("Login exitoso para usuario: {0}", usuario.Email);

                var response = new LoginResponseDto
                {
                    Mensaje = "Inicio de sesión exitoso",
                    Usuario = new UsuarioReadDto
                    {
                        Id = usuario.Id,
                        Nombre = usuario.Nombre,
                        Apellidos = usuario.Apellidos,
                        Email = usuario.Email
                    },
                    Token = tokenString,
                    Expiration = token.ValidTo
                };

                return Ok(response);
            }
            catch (Exception ex)
            {
                _logger.LogError("Error inesperado en login: {0}", ex.Message);
                return StatusCode(500, new { mensaje = "Error interno del servidor" });
            }
        }

        // Método auxiliar privado para obtener el ID del usuario del token
        private long GetUsuarioIdDelToken()
        {
            var userIdClaim = User.FindFirst(JwtRegisteredClaimNames.Sub)?.Value;
            if (long.TryParse(userIdClaim, out var userId))
            {
                return userId;
            }
            throw new UnauthorizedAccessException("No se pudo extraer el ID del usuario del token");
        }
    }
}
