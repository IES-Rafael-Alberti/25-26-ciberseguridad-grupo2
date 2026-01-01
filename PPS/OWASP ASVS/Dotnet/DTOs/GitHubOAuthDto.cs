using System.Text.Json.Serialization;

namespace UsuariosApi.DTOs
{
    /// <summary>
    /// Respuesta del token de GitHub
    /// </summary>
    public class GitHubTokenResponseDto
    {
        [JsonPropertyName("access_token")]
        public string AccessToken { get; set; } = string.Empty;

        [JsonPropertyName("token_type")]
        public string TokenType { get; set; } = string.Empty;

        [JsonPropertyName("scope")]
        public string Scope { get; set; } = string.Empty;
    }

    /// <summary>
    /// Información del usuario de GitHub
    /// </summary>
    public class GitHubUserDto
    {
        [JsonPropertyName("id")]
        public long Id { get; set; }

        [JsonPropertyName("login")]
        public string Login { get; set; } = string.Empty;

        [JsonPropertyName("name")]
        public string? Name { get; set; }

        [JsonPropertyName("email")]
        public string? Email { get; set; }

        [JsonPropertyName("avatar_url")]
        public string? AvatarUrl { get; set; }

        [JsonPropertyName("bio")]
        public string? Bio { get; set; }

        [JsonPropertyName("company")]
        public string? Company { get; set; }

        [JsonPropertyName("blog")]
        public string? Blog { get; set; }

        [JsonPropertyName("location")]
        public string? Location { get; set; }

        [JsonPropertyName("public_repos")]
        public int PublicRepos { get; set; }

        [JsonPropertyName("followers")]
        public int Followers { get; set; }

        [JsonPropertyName("following")]
        public int Following { get; set; }

        [JsonPropertyName("created_at")]
        public DateTime CreatedAt { get; set; }
    }

    /// <summary>
    /// Respuesta del login OAuth
    /// </summary>
    public class GitHubLoginResponseDto
    {
        public string Mensaje { get; set; } = "Autenticación con GitHub exitosa";
        public UsuarioReadDto Usuario { get; set; } = new();
        public string Token { get; set; } = string.Empty;
        public DateTime Expiration { get; set; }
    }

    /// <summary>
    /// Solicitud de callback de GitHub
    /// </summary>
    public class GitHubCallbackRequestDto
    {
        public string Code { get; set; } = string.Empty;
        public string? State { get; set; }
    }
}
