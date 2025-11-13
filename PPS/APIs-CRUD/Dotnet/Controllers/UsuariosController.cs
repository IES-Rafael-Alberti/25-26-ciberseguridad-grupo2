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
    [Route("usuarios")]
    [Authorize]
    public class UsuariosController : ControllerBase
    {
        private readonly UsuariosContext _context;
        private readonly IConfiguration _configuration;

        public UsuariosController(UsuariosContext context, IConfiguration configuration)
        {
            _context = context;
            _configuration = configuration;
        }

        // GET /usuarios
        [HttpGet]
        public async Task<ActionResult<IEnumerable<UsuarioReadDto>>> GetUsuarios()
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

        // GET /usuarios/{id}
        [HttpGet("{id:long}")]
        public async Task<ActionResult<UsuarioReadDto>> GetUsuario(long id)
        {
            var usuario = await _context.Usuarios.FindAsync(id);
            if (usuario == null)
                return NotFound();

            var dto = new UsuarioReadDto
            {
                Id = usuario.Id,
                Nombre = usuario.Nombre,
                Apellidos = usuario.Apellidos,
                Email = usuario.Email
            };

            return Ok(dto);
        }

        // POST /usuarios
    [HttpPost]
    [AllowAnonymous]
    public async Task<ActionResult<UsuarioReadDto>> CreateUsuario(UsuarioCreateDto createDto)
        {
            if (string.IsNullOrWhiteSpace(createDto.Email) || string.IsNullOrWhiteSpace(createDto.Password))
            {
                return BadRequest("Email y password son obligatorios");
            }

            if (await _context.Usuarios.AnyAsync(u => u.Email == createDto.Email))
            {
                return BadRequest("El email ya está registrado");
            }

            var usuario = new Usuario
            {
                Nombre = createDto.Nombre,
                Apellidos = createDto.Apellidos,
                Email = createDto.Email,
                PasswordHash = BCrypt.Net.BCrypt.HashPassword(createDto.Password)
            };

            _context.Usuarios.Add(usuario);
            await _context.SaveChangesAsync();

            var readDto = new UsuarioReadDto
            {
                Id = usuario.Id,
                Nombre = usuario.Nombre,
                Apellidos = usuario.Apellidos,
                Email = usuario.Email
            };

            return CreatedAtAction(nameof(GetUsuario), new { id = usuario.Id }, readDto);
        }

        // PUT /usuarios/{id}
        [HttpPut("{id:long}")]
        public async Task<ActionResult<UsuarioReadDto>> UpdateUsuario(long id, UsuarioUpdateDto updateDto)
        {
            var usuario = await _context.Usuarios.FindAsync(id);
            if (usuario == null)
                return NotFound();

            usuario.Nombre = updateDto.Nombre;
            usuario.Apellidos = updateDto.Apellidos;
            usuario.Email = updateDto.Email;
            usuario.PasswordHash = BCrypt.Net.BCrypt.HashPassword(updateDto.Password);

            await _context.SaveChangesAsync();

            var readDto = new UsuarioReadDto
            {
                Id = usuario.Id,
                Nombre = usuario.Nombre,
                Apellidos = usuario.Apellidos,
                Email = usuario.Email
            };

            return Ok(readDto);
        }

        // DELETE /usuarios/{id}
        [HttpDelete("{id:long}")]
        public async Task<IActionResult> DeleteUsuario(long id)
        {
            var usuario = await _context.Usuarios.FindAsync(id);
            if (usuario == null)
                return NotFound();

            _context.Usuarios.Remove(usuario);
            await _context.SaveChangesAsync();

            return Ok(new { message = $"Usuario con id={id} eliminado correctamente" });
        }

        // POST /usuarios/login
        [HttpPost("login")]
        [AllowAnonymous]
        public async Task<ActionResult<LoginResponseDto>> Login(UsuarioLoginDto loginDto)
        {
            if (string.IsNullOrWhiteSpace(loginDto.Email) || string.IsNullOrWhiteSpace(loginDto.Password))
            {
                return BadRequest("Email y contraseña son obligatorios");
            }

            var usuario = await _context.Usuarios.FirstOrDefaultAsync(u => u.Email == loginDto.Email);
            if (usuario == null)
            {
                return Unauthorized("Credenciales inválidas");
            }

            bool passwordValida = BCrypt.Net.BCrypt.Verify(loginDto.Password, usuario.PasswordHash);
            if (!passwordValida)
            {
                return Unauthorized("Credenciales inválidas");
            }            // generar JWT
            var jwtKey = _configuration["Jwt:Key"];
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

    }
}
