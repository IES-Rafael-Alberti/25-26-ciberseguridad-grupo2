namespace UsuariosApi.DTOs
{
    public class UsuarioLoginDto
    {
        public string Email { get; set; } = string.Empty;
        public string Password { get; set; } = string.Empty;
    }

    public class LoginResponseDto
    {
        public string Mensaje { get; set; } = string.Empty;
        public UsuarioReadDto Usuario { get; set; } = new UsuarioReadDto();
    }
}
