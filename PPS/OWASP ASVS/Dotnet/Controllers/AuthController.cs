using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Caching.Distributed;
using System.IdentityModel.Tokens.Jwt;

namespace UsuariosApi.Controllers
{
    [ApiController]
    [Route("api/auth")]
    [Authorize]
    public class AuthController : ControllerBase
    {
        private readonly IDistributedCache _cache;
        private readonly ILogger<AuthController> _logger;

        public AuthController(IDistributedCache cache, ILogger<AuthController> logger)
        {
            _cache = cache;
            _logger = logger;
        }

        /// <summary>
        /// Invalida el token actual (logout) marcando su jti como revocado.
        /// Nota: Revocación en cache en memoria (válida por proceso).
        /// </summary>
        [HttpPost("logout")]
        public async Task<IActionResult> Logout()
        {
            var jti = User.FindFirst(JwtRegisteredClaimNames.Jti)?.Value;
            if (string.IsNullOrWhiteSpace(jti))
            {
                // Si no hay jti, no se puede revocar de forma fiable
                return Ok(new { mensaje = "Sesión finalizada" });
            }

            // Expirar la revocación cuando expire el token.
            var expValue = User.FindFirst(JwtRegisteredClaimNames.Exp)?.Value;
            if (long.TryParse(expValue, out var expSeconds))
            {
                var exp = DateTimeOffset.FromUnixTimeSeconds(expSeconds);
                var ttl = exp - DateTimeOffset.UtcNow;
                if (ttl < TimeSpan.Zero)
                {
                    ttl = TimeSpan.FromMinutes(5);
                }

                await _cache.SetStringAsync(
                    $"revoked:{jti}",
                    "1",
                    new DistributedCacheEntryOptions { AbsoluteExpirationRelativeToNow = ttl }
                );
            }
            else
            {
                // Fallback TTL
                await _cache.SetStringAsync(
                    $"revoked:{jti}",
                    "1",
                    new DistributedCacheEntryOptions { AbsoluteExpirationRelativeToNow = TimeSpan.FromHours(1) }
                );
            }

            _logger.LogInformation("Logout: token revocado (jti={Jti})", jti);
            return Ok(new { mensaje = "Sesión finalizada" });
        }
    }
}
