namespace UsuariosApi.Models
{
    /// <summary>
    /// Registro de auditoría para rastrear cambios en usuarios
    /// </summary>
    public class AuditLog
    {
        public long Id { get; set; }
        public long UsuarioId { get; set; }
        public string Accion { get; set; } = string.Empty;  // CREATE, UPDATE, DELETE, LOGIN, FAILED_LOGIN
        public string? Detalles { get; set; }
        public string? DireccionIP { get; set; }
        public DateTime FechaHora { get; set; } = DateTime.UtcNow;
        public bool Exitoso { get; set; }
    }
}
