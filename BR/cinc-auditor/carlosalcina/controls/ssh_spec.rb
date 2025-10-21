# controls/ssh_spec.rb
control "ssh-01" do
  title "Comprobaciones básicas de seguridad para OpenSSH"
  desc  "Asegura configuración de sshd según buenas prácticas (No root, no password auth, etc.)"

  impact 1.0
  tag "nist": ["AC-17"], "level": "medium"


  describe sshd_active_config do
    its('PermitRootLogin') { should cmp 'no' }
    its('PasswordAuthentication') { should cmp 'no' }
    its('X11Forwarding') { should cmp 'no' }
  end

  describe sshd_active_config do
    its('MaxAuthTries') { should be <= "4" }
    its('LoginGraceTime') { should be <= "60" } # segundos
    its('ClientAliveInterval') { should be <= "300" } # segundos
    its('ClientAliveCountMax') { should be <="3" }
  end

  # Revisar que el archivo tenga permisos correctos
  describe file(sshd_active_config.active_path) do
    it { should exist }
    it { should be_owned_by 'root' }
    it { should be_grouped_into 'root' }
    its('mode') { should cmp '0644' }
  end
end
