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
