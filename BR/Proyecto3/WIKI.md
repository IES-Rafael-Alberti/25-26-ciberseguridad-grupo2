# Wiki — Proyecto 3 (Hardening Linux)

## Índice

- [1. Objetivo y alcance](#1-objetivo-y-alcance)
- [2. Hardening con sysctl (kernel y red)](#2-hardening-con-sysctl-kernel-y-red)
- [3. Bloqueo de módulos con modprobe](#3-bloqueo-de-módulos-con-modprobe)
- [4. AppArmor (estado y enforcement)](#4-apparmor-estado-y-enforcement)
- [5. LKRG (protección runtime del kernel)](#5-lkrg-protección-runtime-del-kernel)
- [6. Cloud-init (deshabilitar y purgar)](#6-cloud-init-deshabilitar-y-purgar)
- [7. Deshabilitar servicios (multipathd y motd-news)](#7-deshabilitar-servicios-multipathd-y-motd-news)
- [8. Firewall con UFW](#8-firewall-con-ufw)
- [9. Limpieza (paquetes y snapd)](#9-limpieza-paquetes-y-snapd)
- [10. Checklist de verificación final](#10-checklist-de-verificación-final)

---

## 1. Objetivo y alcance

Este proyecto documenta el endurecimiento (hardening) de un sistema Linux mediante:

- Parámetros de kernel y red vía `sysctl`.
- Reducción de superficie de ataque bloqueando módulos del kernel.
- Control de acceso obligatorio con AppArmor.
- Protección adicional del kernel en runtime con LKRG.
- Deshabilitar componentes/servicios no necesarios.
- Configuración básica de firewall con UFW.
- Limpieza de paquetes para minimizar herramientas instaladas.

**Nota:** algunas medidas son “agresivas” (por ejemplo, deshabilitar IPv6 o ignorar ICMP echo/ping) y pueden afectar conectividad, monitorización o compatibilidad. Abajo se detallan riesgos y verificación.

---

## 2. Hardening con sysctl (kernel y red)

### 2.1. Objetivo
Ajustar parámetros de kernel para dificultar explotación, reducir fuga de información y endurecer el stack de red.

### 2.2. Configuración aplicada
Has revisado el fichero con:

```bash
cat /etc/sysctl.d/99-security-hardening.conf
```

Y su contenido (resumen por categorías) es:

- **Memoria / mitigaciones**
  - `kernel.randomize_va_space = 2` (ASLR fuerte)
  - `kernel.kptr_restrict = 2` (restringe punteros del kernel)
  - `kernel.dmesg_restrict = 1` (restringe lectura de dmesg)
  - `kernel.yama.ptrace_scope = 1` (endurece ptrace)

- **BPF JIT**
  - `net.core.bpf_jit_harden = 2`

- **Red IPv4**
  - `net.ipv4.icmp_echo_ignore_all = 1` (ignora ping)
  - `net.ipv4.ip_forward = 0` (sin reenvío)
  - `net.ipv4.conf.all.rp_filter = 1` y `net.ipv4.conf.default.rp_filter = 1` (anti-spoofing)
  - `net.ipv4.conf.all.accept_redirects = 0`, `net.ipv4.conf.default.accept_redirects = 0`, `net.ipv4.conf.all.secure_redirects = 0` (sin redirects)

- **Red IPv6**
  - `net.ipv6.conf.all.disable_ipv6 = 1`
  - `net.ipv6.conf.default.disable_ipv6 = 1`
  - `net.ipv6.conf.lo.disable_ipv6 = 1`

### 2.3. Cómo aplicar los cambios 
```bash
sudo sysctl --system
```

### 2.4. Verificación rápida

```bash
sysctl kernel.randomize_va_space
sysctl kernel.kptr_restrict
sysctl kernel.dmesg_restrict
sysctl kernel.yama.ptrace_scope
sysctl net.core.bpf_jit_harden
sysctl net.ipv4.ip_forward
sysctl net.ipv4.icmp_echo_ignore_all
sysctl net.ipv6.conf.all.disable_ipv6
```

### 2.5. Impacto/riesgos

- Deshabilitar IPv6 puede romper entornos donde IPv6 sea requerido (DNS, redes corporativas, cloud, etc.).
- Ignorar ICMP echo (ping) puede dificultar monitorización/diagnóstico.
- `rp_filter=1` puede interferir con routing asimétrico o algunas VPN/entornos multi-homed.

---

## 3. Bloqueo de módulos con modprobe

### 3.1. Objetivo
Reducir superficie de ataque impidiendo cargar drivers o protocolos innecesarios.

### 3.2. Configuración aplicada
Has revisado el fichero con:

```bash
cat /etc/modprobe.d/blacklist-security.conf
```

Y se bloquean módulos forzando “instalación falsa”:

```conf
install usb-storage /bin/false
install firewire-core /bin/false
install firewire-ohci /bin/false
install bluetooth /bin/false
install cramfs /bin/false
install squashfs /bin/false
install udf /bin/false
install dccp /bin/false
install sctp /bin/false
install rds /bin/false
install tipc /bin/false
```

### 3.3. Verificación

- Comprobar si un módulo está cargado:

```bash
lsmod | egrep 'usb_storage|bluetooth|sctp|dccp|tipc|rds'
```

- Probar carga manual (debería fallar) (solo si tienes consola local y sabes lo que haces):

```bash
sudo modprobe usb-storage
```

### 3.4. Impacto/riesgos

- Bloquear `usb-storage` impide montar pendrives/discos USB (útil en hardening, pero afecta operativa).
- Bloquear `bluetooth` elimina soporte BT.
- Bloquear `squashfs`/`udf` puede afectar lectura de ciertos medios/formatos.

---

## 4. AppArmor (estado y enforcement)

### 4.1. Objetivo
Asegurar que AppArmor está activo y aplicando perfiles en modo `enforce`.

### 4.2. Comandos ejecutados

- Estado:

```bash
sudo aa-status
```

En tu salida se observa:

- “apparmor module is loaded.”
- “106 profiles are loaded.”
- “106 profiles are in enforce mode.”

- Forzar `enforce` (global):

```bash
sudo aa-enforce /etc/apparmor.d/*
```

### 4.3. Verificación

```bash
sudo aa-status
sudo aa-status --enforced
```
---

## 5. LKRG (protección runtime del kernel)

### 5.1. Objetivo
Añadir una capa extra de detección/mitigación en runtime ante ciertos ataques al kernel (integridad/credenciales).

### 5.2. Instalación realizada

```bash
sudo apt update
sudo apt install git build-essential linux-headers-$(uname -r) dkms -y

git clone https://github.com/lkrg-org/lkrg.git
cd lkrg
sudo make
sudo make install
```

### 5.3. Verificación

```bash
sudo lsmod | grep lkrg
sudo dmesg | grep LKRG
```

---

## 6. Cloud-init (deshabilitar y purgar)

### 6.1. Objetivo
Eliminar cloud-init en entornos donde no se use (reduce superficie y servicios innecesarios).

### 6.2. Acciones realizadas

```bash
sudo touch /etc/cloud/cloud-init.disabled
sudo apt purge cloud-init -y
sudo rm -rf /etc/cloud/ /var/lib/cloud/
```

### 6.3. Impacto/riesgos

- En máquinas cloud (AWS/Azure/GCP, imágenes cloud, etc.) cloud-init puede ser crítico para networking, SSH keys, hostname, userdata, etc. Purgarlo puede romper reprovisionado o automatización.

---

## 7. Deshabilitar servicios (multipathd y motd-news)

### 7.1. Objetivo
Reducir servicios en ejecución y “ruido” del sistema.

### 7.2. Acciones realizadas

```bash
sudo systemctl stop multipathd
sudo systemctl disable multipathd
sudo systemctl disable motd-news.timer
```

### 7.3. Verificación

```bash
systemctl is-enabled multipathd
systemctl is-active multipathd
systemctl is-enabled motd-news.timer
systemctl list-timers | grep motd-news
```

### 7.4. Impacto/riesgos

- `multipathd` es relevante en almacenamiento multipath (SAN). En un PC normal suele ser prescindible; en servidores con multipath puede ser necesario.

---

## 8. Firewall con UFW

### 8.1. Objetivo
Aplicar una política básica: bloquear entrante, permitir saliente y permitir SSH.

### 8.2. Configuración realizada

```bash
sudo apt install ufw
sudo ufw default deny incoming  # Bloquear todo lo que entra
sudo ufw default allow outgoing # Permitir salir a internet
sudo ufw allow ssh              # IMPORTANTE: Permitir SSH o te quedas fuera
sudo ufw enable
```

### 8.3. Verificación

```bash
sudo ufw status verbose
sudo ss -tulpn
```

### 8.4. Impacto/riesgos

- Si se habilita UFW sin permitir el puerto de administración (SSH u otro), puedes perder acceso remoto.
- En servidores con servicios publicados, habrá que abrir puertos explícitos.

---

## 9. Limpieza (paquetes y snapd)

### 9.1. Objetivo
Reducir herramientas instaladas tras compilar LKRG y eliminar Snap si no se usa.

### 9.2. Acciones realizadas

```bash
sudo apt remove --purge build-essential git
sudo apt autoremove

sudo apt purge snapd
rm -rf ~/snap
```

### 9.3. Impacto/riesgos

- Quitar `git`/`build-essential` está bien si no se compila nada más, pero complica futuras actualizaciones manuales (por ejemplo, recompilar LKRG tras un update de kernel).
- Purgar `snapd` puede afectar software instalado vía Snap y dependencias de la distro.

---

## 10. Checklist de verificación final

- Sysctl aplicado y valores correctos:
  - `sysctl kernel.randomize_va_space kernel.kptr_restrict kernel.dmesg_restrict kernel.yama.ptrace_scope`
  - `sysctl net.ipv4.ip_forward net.ipv4.icmp_echo_ignore_all net.ipv6.conf.all.disable_ipv6`
- AppArmor activo y en enforce:
  - `sudo aa-status`
- LKRG cargado:
  - `sudo lsmod | grep lkrg`
  - `sudo dmesg | grep LKRG`
- UFW habilitado y SSH permitido:
  - `sudo ufw status verbose`
- Servicios deshabilitados según objetivo:
  - `systemctl is-enabled multipathd motd-news.timer`

