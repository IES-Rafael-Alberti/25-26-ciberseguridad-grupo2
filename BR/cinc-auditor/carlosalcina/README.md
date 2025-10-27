# Control: ssh-01 – Comprobaciones básicas de seguridad para OpenSSH

## Descripción
Este control valida la configuración de seguridad del servicio **OpenSSH (sshd)**, asegurando que cumpla con buenas prácticas y estándares de seguridad.  

---


## Pruebas realizadas

### 1. Configuración principal de `sshd`

| Parámetro | Valor esperado | Propósito |
|------------|----------------|------------|
| `PermitRootLogin` | `no` | Evita el acceso directo como usuario root. |
| `PasswordAuthentication` | `no` | Deshabilita autenticación por contraseña; se exige clave pública. |
| `X11Forwarding` | `no` | Desactiva reenvío gráfico para reducir superficie de ataque. |

---

### 2. Parámetros de control de sesión y autenticación

| Parámetro | Condición esperada | Descripción |
|------------|--------------------|--------------|
| `MaxAuthTries` | ≤ 4 | Límite de intentos de autenticación por conexión. |
| `LoginGraceTime` | ≤ 60 segundos | Tiempo máximo de espera antes de cerrar conexión sin login. |
| `ClientAliveInterval` | ≤ 300 segundos | Intervalo entre mensajes keep-alive. |
| `ClientAliveCountMax` | ≤ 3 | Número máximo de intentos keep-alive fallidos antes de cerrar sesión. |

---

### 3. Permisos del archivo de configuración

| Verificación | Resultado esperado |
|---------------|--------------------|
| Existencia del archivo | Debe existir |
| Propietario | `root` |
| Grupo | `root` |
| Permisos | `0644` (lectura global, solo root puede escribir) |

