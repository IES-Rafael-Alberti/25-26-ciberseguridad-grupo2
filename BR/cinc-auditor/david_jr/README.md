# Perfil InSpec - Configuración Segura de SSH

Este perfil de **InSpec** contiene controles para verificar que el servicio **SSH** esté configurado de forma segura y cumpla con las mejores prácticas de seguridad.

---

## Pasos para Cumplir los Controles

### **Control `ssh-1`: El servicio SSH debe estar activo y habilitado**

**Objetivo:** Asegurar que el servicio `ssh` está instalado, habilitado y corriendo.

```bash
sudo apt update
sudo apt install openssh-server -y
sudo systemctl enable ssh
sudo systemctl start ssh
systemctl status ssh
```

---

### **Control `ssh-2`: El puerto 22 debe estar escuchando**

**Objetivo:** Confirmar que SSH está escuchando en el puerto 22 mediante protocolo TCP.

```bash
sudo ss -tuln | grep ':22'
# o
sudo netstat -tuln | grep ':22'

sudo nano /etc/ssh/sshd_config
```
Asegurarse de que exista:
```
Port 22
```

Reiniciar el servicio SSH:
```bash
sudo systemctl restart ssh
```

---

### **Control `ssh-3`: Configuración segura en sshd_config**

**Objetivo:** Garantizar que no se permita el acceso root ni la autenticación por contraseña.

```bash
sudo nano /etc/ssh/sshd_config
```
Configurar:
```
PermitRootLogin no
PasswordAuthentication no
```

Guardar y reiniciar:
```bash
sudo systemctl restart ssh
sudo grep -E 'PermitRootLogin|PasswordAuthentication' /etc/ssh/sshd_config
```

---

## Autor
Luiska.