namespace UsuariosApi.DTOs
{
    public class LoginResponseDto
    {
        public string Mensaje { get; set; } = string.Empty;
        public UsuarioReadDto? Usuario { get; set; }
        public string Token { get; set; } = string.Empty;
        public DateTime Expiration { get; set; }
    }
}
