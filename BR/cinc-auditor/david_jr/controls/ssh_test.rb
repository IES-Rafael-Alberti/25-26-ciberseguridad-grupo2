control 'ssh-1' do
  impact 1.0
  title 'El servicio SSH debe estar activo y habilitado'
  desc 'Comprueba que el servicio SSH está en ejecución y habilitado al arranque'
  
  describe service('ssh') do
    it { should be_installed }
    it { should be_enabled }
    it { should be_running }
  end
end

control 'ssh-2' do
  impact 0.7
  title 'El puerto 22 debe estar escuchando'
  desc 'Verifica que SSH escucha en el puerto 22 TCP'
  
  describe port(22) do
    it { should be_listening }
    its('protocols') { should include 'tcp' }
  end
end

control 'ssh-3' do
  impact 0.9
  title 'Configuración segura en sshd_config'
  desc 'Asegura que el acceso root por contraseña está deshabilitado'
  
  describe sshd_config do
    its('PermitRootLogin') { should_not cmp 'yes' }
    its('PasswordAuthentication') { should cmp 'no' }
  end
end
