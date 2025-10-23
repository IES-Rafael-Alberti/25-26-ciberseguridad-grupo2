using BCrypt.Net;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using UsuariosApi.Data;
using UsuariosApi.DTOs;
using UsuariosApi.Models;

namespace UsuariosApi.Controllers
{
    [ApiController]
    [Route("usuarios")]
    public class UsuariosController : ControllerBase
    {
        private readonly UsuariosContext _context;

        public UsuariosController(UsuariosContext context)
        {
            _context = context;
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
    }
}
