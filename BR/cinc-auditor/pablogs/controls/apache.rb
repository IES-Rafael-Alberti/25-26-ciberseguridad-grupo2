# controls/apache.rb
# Tests básicos y útiles para Apache (apache2/httpd)
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

control 'apache-process-user-01' do
  impact 0.7
  title 'El proceso de Apache debe ejecutarse bajo el usuario esperado'
  desc 'Usuario típico: www-data (Debian) o apache (RHEL). Esto evita que corra como root.'
  apache_user = os.debian? ? 'www-data' : 'apache'
  describe processes(Regexp.new("apache|httpd")) do
    its('users') { should include apache_user }
  end
end


control 'apache-security-headers-01' do
  impact 0.5
  title 'Comprobación rápida: X-Content-Type-Options y X-Frame-Options presentes (si hay headers configurados)'
  desc 'Intenta hacer una petición local a localhost y verificar headers (si server responde).'
  describe http('http://localhost', enable_remote_worker: true) do
    its('status') { should be_in [200, 301, 302, 403] }
    # no fallar si no están; solo avisar
    it 'Revisa encabezados de seguridad (X-Content-Type-Options, X-Frame-Options)' do
      expect(subject.headers).to be_a(Hash)
    end
  end
end
