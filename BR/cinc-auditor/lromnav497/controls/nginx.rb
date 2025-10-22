# -----------------------------------------------
# PERFIL: lromnav497 - Auditoría básica de nginx
# -----------------------------------------------

control 'nginx-01' do
  impact 1.0
  title 'Verificar que nginx está instalado'
  desc 'Comprueba que el paquete nginx está presente en el sistema'
  describe package('nginx') do
    it { should be_installed }
  end
end

control 'nginx-02' do
  impact 1.0
  title 'Verificar que nginx está en ejecución'
  desc 'Comprueba que el servicio nginx está activo y habilitado'
  describe service('nginx') do
    it { should be_running }
    it { should be_enabled }
  end
end

# ------------------------------
# Nuevo: verificar el puerto HTTP
# ------------------------------
control 'nginx-03' do
  impact 0.9
  title 'Verificar que nginx escucha en el puerto 80'
  desc 'Comprueba que el puerto 80 (HTTP) está abierto y en uso por nginx'
  describe port(80) do
    it { should be_listening }
    its('processes') { should include 'nginx' }
  end
end

# ------------------------------
# Nuevo: verificar archivo de configuración
# ------------------------------
control 'nginx-04' do
  impact 0.8
  title 'Verificar que el archivo de configuración de nginx existe'
  desc 'Comprueba la presencia y permisos del archivo principal de configuración'
  describe file('/etc/nginx/nginx.conf') do
    it { should exist }
    it { should be_file }
    it { should be_readable }
  end
end

# ------------------------------
# Nuevo: validar contenido del archivo de configuración
# ------------------------------
control 'nginx-05' do
  impact 0.7
  title 'Verificar que nginx.conf contiene directiva worker_processes'
  desc 'Confirma que el archivo contiene configuraciones básicas esperadas'
  describe file('/etc/nginx/nginx.conf') do
    its('content') { should match /worker_processes\s+(auto|\d+)/ }
  end
end


# ------------------------------
# Nuevo: verificar permisos de la carpeta web
# ------------------------------
control 'nginx-06' do
  impact 0.7
  title 'Verificar permisos de la carpeta raíz del sitio web'
  desc 'El directorio /var/www/html debe existir y tener permisos seguros'
  describe file('/var/www/html') do
    it { should exist }
    it { should be_directory }
    it { should be_readable.by('owner') }
  end
end

# ------------------------------
# Nuevo: verificar el proceso nginx
# ------------------------------
control 'nginx-07' do
  impact 0.9
  title 'Verificar que el proceso nginx está activo'
  desc 'Confirma que hay al menos un proceso nginx corriendo'
  describe processes('nginx') do
    its('entries.length') { should be > 0 }
  end
end

# ------------------------------
# Nuevo: verificar respuesta HTTP local
# ------------------------------
control 'nginx-08' do
  impact 1.0
  title 'Verificar que nginx responde en localhost'
  desc 'Realiza una solicitud HTTP a localhost y valida que responde con 200'
  describe http('http://127.0.0.1') do
    its('status') { should cmp 200 }
  end
end
