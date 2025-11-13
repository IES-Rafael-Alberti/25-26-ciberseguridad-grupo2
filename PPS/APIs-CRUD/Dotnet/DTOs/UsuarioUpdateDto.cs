using System.ComponentModel.DataAnnotations;

namespace UsuariosApi.DTOs
{
    public class UsuarioUpdateDto
    {
        [Required(ErrorMessage = "El nombre es obligatorio")]
        [StringLength(100, MinimumLength = 1,
            ErrorMessage = "El nombre debe tener entre 1 y 100 caracteres")]
        [RegularExpression(@"^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-']+$",
            ErrorMessage = "El nombre solo puede contener letras, espacios, guiones y apóstrofos")]
        public string Nombre { get; set; } = string.Empty;

        [Required(ErrorMessage = "Los apellidos son obligatorios")]
        [StringLength(100, MinimumLength = 1,
            ErrorMessage = "Los apellidos deben tener entre 1 y 100 caracteres")]
        [RegularExpression(@"^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-']+$",
            ErrorMessage = "Los apellidos solo pueden contener letras, espacios, guiones y apóstrofos")]
        public string Apellidos { get; set; } = string.Empty;

        [Required(ErrorMessage = "El email es obligatorio")]
        [EmailAddress(ErrorMessage = "El formato del email no es válido")]
        [StringLength(254, ErrorMessage = "El email no puede exceder 254 caracteres")]
        public string Email { get; set; } = string.Empty;

        [Required(ErrorMessage = "La contraseña es obligatoria")]
        [StringLength(256, MinimumLength = 12,
            ErrorMessage = "La contraseña debe tener entre 12 y 256 caracteres")]
        [RegularExpression(@"^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$",
            ErrorMessage = "La contraseña debe contener mayúscula, minúscula, número y carácter especial (@$!%*?&)")]
        public string Password { get; set; } = string.Empty;
    }
}
