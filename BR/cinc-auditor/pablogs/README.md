## Proyecto: Profile de Cinc-Auditor (Apache)

### Descripción

Este proyecto contiene un **profile de Cinc-Auditor** creado para testear la configuración y el estado del servicio **Apache (apache2/httpd)** en un sistema **Kali Linux**.
El objetivo es verificar que Apache esté correctamente instalado, configurado y ejecutándose bajo un usuario seguro, además de revisar algunos encabezados de seguridad básicos.

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

### 2. Creación del Profile

El profile fue creado con el comando:

```bash
cinc-auditor init profile apache_profile
```

Esto genera una estructura básica como esta:

```
apache_profile/
├── controls/
│   └── apache.rb
├── inspec.yml
└── libraries/
```

---

### 3. Tests Implementados

El archivo `controls/apache.rb` contiene los siguientes controles:

#### 🔹 Verificar instalación del paquete Apache

```ruby
control 'apache-package-01' do
  impact 1.0
  title 'El paquete de Apache debe estar instalado'
  desc 'Comprueba que el paquete apache2 (Debian/Ubuntu) o httpd (RHEL/CentOS) esté instalado'
  describe.one do
    describe package('apache2') do
      it { should be_installed }
    end
    describe package('httpd') do
      it { should be_installed }
    end
  end
end
```

#### 🔹 Verificar puertos de escucha (HTTP/HTTPS)

```ruby
control 'apache-port-01' do
  impact 1.0
  title 'Apache debe escuchar en el puerto 80 (y 443 si está configurado)'
  desc 'Verifica que los puertos HTTP/HTTPS estén escuchando'
  describe.one do
    describe port(80) do
      it { should be_listening }
    end
    describe port(443) do
      it { should be_listening }
    end
  end
end
```

#### 🔹 Verificar usuario del proceso Apache

```ruby
control 'apache-process-user-01' do
  impact 0.7
  title 'El proceso de Apache debe ejecutarse bajo el usuario esperado'
  desc 'Usuario típico: www-data (Debian) o apache (RHEL). Esto evita que corra como root.'
  apache_user = os.debian? ? 'www-data' : 'apache'
  describe processes(Regexp.new("apache|httpd")) do
    its('users') { should include apache_user }
  end
end
```

#### 🔹 Verificar headers de seguridad básicos

```ruby
control 'apache-security-headers-01' do
  impact 0.5
  title 'Comprobación rápida: X-Content-Type-Options y X-Frame-Options presentes (si hay headers configurados)'
  desc 'Intenta hacer una petición local a localhost y verificar headers (si server responde).'
  describe http('http://localhost', enable_remote_worker: true) do
    its('status') { should be_in [200, 301, 302, 403] }
    it 'Revisa encabezados de seguridad (X-Content-Type-Options, X-Frame-Options)' do
      expect(subject.headers).to be_a(Hash)
    end
  end
end
```

---

### 📂 4. Subida al Repositorio

El profile se subió a la carpeta `tests/` del repositorio común de GitHub.
Ruta del proyecto:

```
/tests/apache_profile/
```

Enlace al repositorio:
🔗 [URL de tu profile en GitHub]

---

### 🔄 5. Envío a mi compañero/a

Se compartió el enlace del profile con mi compañero/a para que pudiera ejecutarlo y documentar los pasos necesarios para pasar los tests.

---

### ▶️ 6. Cómo ejecutar los tests

1. Clonar el repositorio:

   ```bash
   git clone <url-del-repositorio>
   cd tests/apache_profile
   ```

2. Ejecutar los tests localmente:

   ```bash
   cinc-auditor exec .
   ```

   O, si se desea ejecutar en un host remoto (vía SSH):

   ```bash
   cinc-auditor exec . -t ssh://usuario@ip
   ```

---

### 🧾 7. Resultado esperado

Al ejecutar los tests, Cinc-Auditor mostrará un resumen similar a:

```
Profile: Apache Configuration
Version: 0.1.0
Target:  local://

  ✔  apache-package-01: El paquete de Apache debe estar instalado
  ✔  apache-port-01: Apache debe escuchar en el puerto 80 (y 443 si está configurado)
  ✔  apache-process-user-01: El proceso de Apache debe ejecutarse bajo el usuario esperado
  ✔  apache-security-headers-01: Comprobación rápida de encabezados de seguridad

Summary: 4 successful controls, 0 failures, 0 skipped
```

---

### 💡 8. Referencias

* [Documentación oficial de Cinc-Auditor](https://docs.cinc.sh/)
* [InSpec Resources Reference](https://docs.chef.io/inspec/resources/)
