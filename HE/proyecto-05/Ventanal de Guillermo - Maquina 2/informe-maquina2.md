# Informe Técnico de Pruebas de Penetración (Pentesting)

**Objetivo:** Servidor DC-Company (Windows Server 2016)
**Dominio:** DescuidadaCorp.local
**Auditor:** Pablo González

---

## 1. Resumen Ejecutivo

Se ha llevado a cabo una auditoría de seguridad integral sobre el servidor con dirección IP **192.168.122.205**. Durante las pruebas, se ha logrado comprometer por completo el sistema objetivo, obteniendo el máximo nivel de privilegios (**NT AUTHORITY\SYSTEM**).

El compromiso inicial se logró explotando una vulnerabilidad crítica en el protocolo **SMBv1 (MS17-010 / EternalBlue)**. Posteriormente, se demostró el impacto del robo de credenciales mediante técnicas de **Pass-the-Hash** y se evidenciaron graves deficiencias en las políticas de seguridad corporativas y en la gestión de parches del sistema operativo.

---

## 2. Alcance y Preparación

### Fase de Preparación

Verificación de la dirección IP de la máquina atacante (Kali Linux) en la red NAT del laboratorio.

> IP atacante: **192.168.122.40**

![Preparación](../evidencias/maquina2/1.iplinux.png)

---

## 3. Fases de la Auditoría Técnica

### 3.1 Reconocimiento y Escaneo

Se identificó la máquina objetivo mediante un barrido de red con `nmap -sn`.

![Reconocimiento](../evidencias/maquina2/2.ipvictima.png)

Se realizó un escaneo completo de puertos:

```bash
nmap -p- -sS --min-rate 5000
```

![Puertos](../evidencias/maquina2/3.nmap-scan-services.png)

Posteriormente, se ejecutó un escaneo profundo:

```bash
nmap -sC -sV
```

![Enumeración](../evidencias/maquina2/4.scan-enumerado.png)
![Sistema](../evidencias/maquina2/5.datos-sistema.png)

---

### 3.2 Enumeración

#### SMB

Intento de acceso anónimo:

![SMB](../evidencias/maquina2/6.smbclient-result.png)

#### RPC / SMB

![Enum4linux](../evidencias/maquina2/7.1.enum4linux.png)
![Denegado](../evidencias/maquina2/7.2.denegado-users.png)

#### LDAP

![LDAP](../evidencias/maquina2/8.LDAP-anonymous-bind-denegado.png)

---

### 3.3 Análisis de Vulnerabilidades

Detección de vulnerabilidad crítica **MS17-010 (EternalBlue)**:

![Vuln](../evidencias/maquina2/9.1.vulnerabilidad-ms17-010-eternalblue.png)
![Zoom](../evidencias/maquina2/9.2.zoom-eternalblue.png)

---

### 3.4 Explotación

Configuración del exploit:

![Config](../evidencias/maquina2/10.1.configuracion-exploit-ms17-010.png)

Ejecución exitosa:

![Exploit](../evidencias/maquina2/10.2.explotacion-exitosa-meterpreter.png)

---

### 3.5 Post-Explotación

Verificación de privilegios:

![Privilegios](../evidencias/maquina2/11.post-explotacion-privilegios.png)

Extracción de credenciales:

![Hashdump](../evidencias/maquina2/12.post-explotacion-hashdump-credenciales.png)

---

### 3.6 Movimiento Lateral

Pass-the-Hash:

![PTH](../evidencias/maquina2/13.Pass-the-Hash.png)

WinRM:

![WinRM Config](../evidencias/maquina2/14.configuracion-winrm-pth.png)

Acceso exitoso:

![WinRM](../evidencias/maquina2/15.explotacion-winrm-exitosa.png)

---

### 3.7 Auditoría de Seguridad

Fuerza bruta en WinRM:

![Bruteforce](../evidencias/maquina2/16.winrm-bruteforce-audit.png)

RDP expuesto:

![RDP](../evidencias/maquina2/17.RDP-service-audit.png)

Tokens e impersonation:

![Tokens](../evidencias/maquina2/18.token-impersonation-audit.png)

Servicios:

![Servicios](../evidencias/maquina2/19.service-configuration-audit.png)

Conexiones:

![Netstat](../evidencias/maquina2/20.netstat-conexiones-internas.png)

Vulnerabilidades locales:

![Local Exploits](../evidencias/maquina2/21.local-exploit-suggester-results.png)

---

## 4. Conclusiones y Recomendaciones

### Riesgo: CRÍTICO

Se recomienda aplicar de forma inmediata:

* **Gestión de parches:** aplicar MS17-010 y actualizar el sistema
* **Deshabilitar SMBv1**
* **Políticas de bloqueo de cuentas**
* **Restringir acceso a WinRM y RDP**

---

## 5. Contexto del Proyecto

Este informe forma parte del **Proyecto 5: Guillermo's Window**, cuyo objetivo es:

* Definir un acuerdo de pentesting
* Identificar y explotar vulnerabilidades
* Documentar resultados

La empresa ficticia **"La Descuidada S.A."** solicitó la auditoría sobre dos máquinas, siendo este informe correspondiente a la **Máquina 2**.

---

## 6. Entrega

* Informe en Markdown
* Publicación en GitHub
* Documentación completa del proceso

---

*Fin del informe*
