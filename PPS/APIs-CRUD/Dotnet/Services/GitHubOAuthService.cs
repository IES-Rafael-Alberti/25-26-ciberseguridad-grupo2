using UsuariosApi.DTOs;
using System.Text.Json;

namespace UsuariosApi.Services
{
    /// <summary>
    /// Servicio para gestionar la autenticación con GitHub OAuth
    /// </summary>
    public interface IGitHubOAuthService
    {
        Task<GitHubTokenResponseDto?> ExchangeCodeForTokenAsync(string code);
        Task<GitHubUserDto?> GetUserInfoAsync(string accessToken);
    }

    public class GitHubOAuthService : IGitHubOAuthService
    {
        private readonly HttpClient _httpClient;
        private readonly IConfiguration _configuration;
        private readonly ILogger<GitHubOAuthService> _logger;

        private const string GitHubTokenUrl = "https://github.com/login/oauth/access_token";
        private const string GitHubUserUrl = "https://api.github.com/user";

        public GitHubOAuthService(HttpClient httpClient, IConfiguration configuration, ILogger<GitHubOAuthService> logger)
        {
            _httpClient = httpClient;
            _configuration = configuration;
            _logger = logger;
        }

        /// <summary>
        /// Intercambia el código de autorización por un token de acceso
        /// </summary>
        public async Task<GitHubTokenResponseDto?> ExchangeCodeForTokenAsync(string code)
        {
            try
            {
                var clientId = _configuration["GitHub:ClientId"];
                var clientSecret = _configuration["GitHub:ClientSecret"];

                if (string.IsNullOrEmpty(clientId) || string.IsNullOrEmpty(clientSecret))
                {
                    _logger.LogError("GitHub ClientId o ClientSecret no configurados");
                    return null;
                }

                var requestBody = new
                {
                    client_id = clientId,
                    client_secret = clientSecret,
                    code = code
                };

                var content = new StringContent(
                    JsonSerializer.Serialize(requestBody),
                    System.Text.Encoding.UTF8,
                    "application/json"
                );

                // Configurar headers para obtener respuesta JSON
                _httpClient.DefaultRequestHeaders.Accept.Clear();
                _httpClient.DefaultRequestHeaders.Accept.Add(
                    new System.Net.Http.Headers.MediaTypeWithQualityHeaderValue("application/json")
                );

                var response = await _httpClient.PostAsync(GitHubTokenUrl, content);

                if (!response.IsSuccessStatusCode)
                {
                    _logger.LogError("Error al intercambiar código por token: {0}", response.StatusCode);
                    return null;
                }

                var jsonResponse = await response.Content.ReadAsStringAsync();
                var tokenResponse = JsonSerializer.Deserialize<GitHubTokenResponseDto>(jsonResponse);

                if (tokenResponse == null)
                {
                    _logger.LogError("No se pudo deserializar la respuesta del token de GitHub");
                    return null;
                }

                _logger.LogInformation("Token de GitHub obtenido exitosamente");
                return tokenResponse;
            }
            catch (Exception ex)
            {
                _logger.LogError("Excepción al intercambiar código por token: {0}", ex.Message);
                return null;
            }
        }

        /// <summary>
        /// Obtiene la información del usuario autenticado
        /// </summary>
        public async Task<GitHubUserDto?> GetUserInfoAsync(string accessToken)
        {
            try
            {
                _httpClient.DefaultRequestHeaders.Authorization =
                    new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", accessToken);

                _httpClient.DefaultRequestHeaders.Accept.Clear();
                _httpClient.DefaultRequestHeaders.Accept.Add(
                    new System.Net.Http.Headers.MediaTypeWithQualityHeaderValue("application/json")
                );

                _httpClient.DefaultRequestHeaders.Add("User-Agent", "UsuariosApi");

                var response = await _httpClient.GetAsync(GitHubUserUrl);

                if (!response.IsSuccessStatusCode)
                {
                    _logger.LogError("Error al obtener información del usuario: {0}", response.StatusCode);
                    return null;
                }

                var jsonResponse = await response.Content.ReadAsStringAsync();
                var userInfo = JsonSerializer.Deserialize<GitHubUserDto>(jsonResponse);

                if (userInfo == null)
                {
                    _logger.LogError("No se pudo deserializar la información del usuario de GitHub");
                    return null;
                }

                _logger.LogInformation("Información del usuario obtenida: {0}", userInfo.Login);
                return userInfo;
            }
            catch (Exception ex)
            {
                _logger.LogError("Excepción al obtener información del usuario: {0}", ex.Message);
                return null;
            }
        }
    }
}
