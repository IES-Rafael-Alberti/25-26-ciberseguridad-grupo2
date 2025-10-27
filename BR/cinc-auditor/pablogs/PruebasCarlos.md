## Proyecto: Profile de Cinc-Auditor (SSH)

### Descripción

Este profile de **Cinc-Auditor** fue creado por mi compañero para testear la configuración y seguridad del servicio **OpenSSH**.
Su objetivo es asegurar que el servidor SSH cumpla con buenas prácticas de seguridad, incluyendo restricciones de login, autenticación y permisos de archivos.

Los tests se ejecutaron correctamente y todos los controles pasaron sin errores ✅.

---

### 1. Instalación de Cinc-Auditor

Sigue la guía oficial:
👉 [https://cinc.sh/start/auditor/](https://cinc.sh/start/auditor/)

#### En Kali / Debian / Ubuntu:

```bash
curl https://omnitruck.cinc.sh/install.sh | sudo bash -s -- -P cinc-auditor
```

Para verificar que se instaló correctamente:

```bash
cinc-auditor version
```

---

### 2. Estructura del Profile

El profile se encuentra en la carpeta:

```
/tests/ssh_profile/
```

Con el archivo de controles:

```
controls/ssh_spec.rb
```

---

### 3. Tests Incluidos

El archivo `ssh_spec.rb` contiene un conjunto de controles de seguridad básicos y efectivos:

#### 🔹 Comprobación de parámetros críticos en `sshd_config`

```ruby
describe sshd_active_config do
  its('PermitRootLogin') { should cmp 'no' }
  its('PasswordAuthentication') { should cmp 'no' }
  its('X11Forwarding') { should cmp 'no' }
end
```

🟢 *Resultado:* Todos los parámetros están configurados correctamente según las buenas prácticas.

#### 🔹 Límites de intentos de autenticación y tiempos de sesión

```ruby
describe sshd_active_config do
  its('MaxAuthTries') { should be <= "4" }
  its('LoginGraceTime') { should be <= "60" }
  its('ClientAliveInterval') { should be <= "300" }
  its('ClientAliveCountMax') { should be <= "3" }
end
```

🟢 *Resultado:* Todos los valores cumplen los límites recomendados.

#### 🔹 Permisos del archivo `sshd_config`

```ruby
describe file(sshd_active_config.active_path) do
  it { should exist }
  it { should be_owned_by 'root' }
  it { should be_grouped_into 'root' }
  its('mode') { should cmp '0644' }
end
```

🟢 *Resultado:* El archivo existe, pertenece a root y tiene los permisos correctos.

---

###  4. Ejecución de los Tests

Para ejecutar los controles, basta con posicionarse en la carpeta del profile y ejecutar:

```bash
cinc-auditor exec .
```

O, si se desea ejecutar en un host remoto:

```bash
cinc-auditor exec . -t ssh://usuario@ip
```

---

### 5. Resultado Esperado

La ejecución del profile mostró todos los controles exitosos:

```
Profile: SSH Configuration
Version: 0.1.0
Target:  local://

  ✔  ssh-01: Comprobaciones básicas de seguridad para OpenSSH

Summary: 1 successful control, 0 failures, 0 skipped
```

---

### 💡 6. Conclusión

Los tests del profile SSH se ejecutaron correctamente y confirmaron que la configuración de **OpenSSH** cumple con los estándares de seguridad esperados:

* No permite login como root.
* No permite autenticación por contraseña.
* Usa límites razonables de intentos y tiempos.
* Tiene permisos seguros en `sshd_config`.

✅ **Resultado final:** Todos los controles pasaron sin errores.
![Pruebas Carlos](./images/pruebascarlos.jpg)

---

### 📚 Referencias

* [Documentación oficial de Cinc-Auditor](https://docs.cinc.sh/)
* [InSpec SSH Resource Reference]([https://docs.chef.io/insp](https://docs.chef.io/insp)
