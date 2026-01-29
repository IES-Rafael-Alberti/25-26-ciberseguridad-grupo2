// auth.js - Sistema de auto-refresh de tokens

class AuthManager {
    constructor() {
        this.accessToken = localStorage.getItem('access_token');
        this.refreshToken = localStorage.getItem('refresh_token');
        this.refreshInterval = null;
        
        // Iniciar renovación automática si hay tokens
        if (this.accessToken && this.refreshToken) {
            this.startAutoRefresh();
        }
    }
    
    // Guardar tokens
    setTokens(accessToken, refreshToken) {
        this.accessToken = accessToken;
        this.refreshToken = refreshToken;
        localStorage.setItem('access_token', accessToken);
        localStorage.setItem('refresh_token', refreshToken);
        this.startAutoRefresh();
    }
    
    // Obtener access token actual
    getAccessToken() {
        return this.accessToken;
    }
    
    // Renovar access token
    async refreshAccessToken() {
        try {
            const response = await fetch('refresh_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    refresh_token: this.refreshToken
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.accessToken = data.access_token;
                localStorage.setItem('access_token', data.access_token);
                console.log('✅ Token renovado automáticamente');
                return true;
            } else {
                console.error('❌ Error al renovar token:', data.error);
                this.logout();
                return false;
            }
        } catch (error) {
            console.error('❌ Error en refresh:', error);
            return false;
        }
    }
    
    // Auto-renovar cada 12 minutos (antes de que expire a los 15)
    startAutoRefresh() {
        // Limpiar intervalo previo
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        
        // Renovar cada 12 minutos (720000 ms)
        this.refreshInterval = setInterval(() => {
            console.log('🔄 Renovando token automáticamente...');
            this.refreshAccessToken();
        }, 720000); // 12 minutos
    }
    
    // Hacer petición con auto-refresh
    async fetch(url, options = {}) {
        // Primera petición
        options.headers = options.headers || {};
        options.headers['Authorization'] = 'Bearer ' + this.accessToken;
        
        let response = await fetch(url, options);
        
        // Si token expiró (401), renovar y reintentar
        if (response.status === 401) {
            console.log('⚠️ Token expirado, renovando...');
            const refreshed = await this.refreshAccessToken();
            
            if (refreshed) {
                // Reintentar con nuevo token
                options.headers['Authorization'] = 'Bearer ' + this.accessToken;
                response = await fetch(url, options);
            } else {
                // Si no puede renovar, ir al login
                window.location.href = 'login.php';
                return response;
            }
        }
        
        return response;
    }
    
    // Cerrar sesión
    logout() {
        // Agregar refresh token a blacklist
        if (this.refreshToken) {
            fetch('logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    refresh_token: this.refreshToken
                })
            });
        }
        
        // Limpiar
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        clearInterval(this.refreshInterval);
        window.location.href = 'login.php';
    }
}

// Instancia global
const auth = new AuthManager();