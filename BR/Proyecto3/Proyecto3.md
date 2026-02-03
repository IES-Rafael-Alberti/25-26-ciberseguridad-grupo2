# Proyecto 3 — Bitácora de comandos

Resumen y explicación (wiki): [BR/Proyecto3/WIKI.md](WIKI.md)

---

cat /etc/sysctl.d/99-security-hardening.conf
# Aleatorización del espacio de direcciones (ASLR)
# Hace difícil que un exploit adivine dónde se encuentran las funciones del sistema en la memoria.
kernel.randomize_va_space = 2

# Restringir el acceso a los punteros del kernel
# Evita que un atacante vea las direcciones de memoria del kernel para atacarlas.
kernel.kptr_restrict = 2

# Restringir el acceso al buffer de mensajes del kernel (dmesg)
# Evita que usuarios sin privilegios lean logs que podrían revelar información sensible.
kernel.dmesg_restrict = 1

# Protección contra PTRACE
# Evita que un proceso pueda "depurar" o leer la memoria de otro proceso (técnica común para robar credenciales).
kernel.yama.ptrace_scope = 1

# Endurecimiento de BPF JIT
# Protege contra ataques que usan el compilador JIT del kernel.
net.core.bpf_jit_harden = 2

# Ignorar ICMP Echo Requests (Ping)
net.ipv4.icmp_echo_ignore_all = 1

# Deshabilitar el reenvío de paquetes
net.ipv4.ip_forward = 0

# Protección contra IP Spoofing
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1

# Deshabilitar ICMP Redirects
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv4.conf.all.secure_redirects = 0

# Deshabilitar IPv6 en todas las interfaces
net.ipv6.conf.all.disable_ipv6 = 1
net.ipv6.conf.default.disable_ipv6 = 1
net.ipv6.conf.lo.disable_ipv6 = 1



cat /etc/modprobe.d/blacklist-security.conf 
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

sudo aa-status
apparmor module is loaded.
106 profiles are loaded.
106 profiles are in enforce mode.
   /usr/lib/snapd/snap-confine
   /usr/lib/snapd/snap-confine//mount-namespace-capture-helper

sudo aa-enforce /etc/apparmor.d/*

sudo apt update
sudo apt install git build-essential linux-headers-$(uname -r) dkms -y
git clone https://github.com/lkrg-org/lkrg.git
cd lkrg
sudo make
sudo make install

sudo lsmod | grep lkrg
sudo dmesg | grep LKRG

sudo touch /etc/cloud/cloud-init.disabled
sudo apt purge cloud-init -y
sudo rm -rf /etc/cloud/ /var/lib/cloud/

sudo systemctl stop multipathd
sudo systemctl disable multipathd
sudo systemctl disable motd-news.timer


sudo apt install ufw
sudo ufw default deny incoming  # Bloquear todo lo que entra
sudo ufw default allow outgoing # Permitir salir a internet
sudo ufw allow ssh              # IMPORTANTE: Permitir SSH o te quedas fuera
sudo ufw enable


sudo apt remove --purge build-essential git
sudo apt autoremove

sudo apt purge snapd
rm -rf ~/snap