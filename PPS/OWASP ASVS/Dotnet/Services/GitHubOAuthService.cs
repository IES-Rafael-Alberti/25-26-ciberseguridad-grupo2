using UsuariosApi.DTOs;
using System.Text.Json;
using System.Net.Http.Headers;

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

                using var request = new HttpRequestMessage(HttpMethod.Post, GitHubTokenUrl);
                request.Headers.Accept.Clear();
                request.Headers.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
                request.Headers.UserAgent.Clear();
                request.Headers.UserAgent.Add(new ProductInfoHeaderValue("UsuariosApi", "1.0"));
                request.Content = content;

                var response = await _httpClient.SendAsync(request);

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
                _logger.LogError(ex, "Excepción al intercambiar código por token");
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
                using var request = new HttpRequestMessage(HttpMethod.Get, GitHubUserUrl);
                request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", accessToken);
                request.Headers.Accept.Clear();
                request.Headers.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
                request.Headers.UserAgent.Clear();
                request.Headers.UserAgent.Add(new ProductInfoHeaderValue("UsuariosApi", "1.0"));

                var response = await _httpClient.SendAsync(request);

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
                _logger.LogError(ex, "Excepción al obtener información del usuario");
                return null;
            }
        }
    }
}
