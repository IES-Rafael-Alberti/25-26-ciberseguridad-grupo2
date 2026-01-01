# Informe de Implementación OWASP ASVS 4.0

**Proyecto:** API REST Java Spring Boot (CRUD + OAuth)
**Fecha:** 31 de Enero de 2026
**Objetivo:** Planificar y aplicar controles de seguridad OWASP ASVS (Nivel 1).

---

## 1. Dependencias (Input Validation)
Se asegura la inclusión de la librería de validación estándar para cumplir con **ASVS V5 (Validation)**.

**Archivo:** `pom.xml`
```xml
<dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-validation</artifactId>
</dependency>
```

---

## 2. Configuración de Seguridad y Cabeceras (ASVS V14 & V4)

Se ha blindado la configuración de Spring Security para incluir cabeceras HTTP seguras, cerrar rutas públicas no autorizadas y configurar CORS estrictamente.

**Archivo:** `src/main/java/com/pgs/crud_java/security/SecurityConfig.java`

```java
package com.pgs.crud_java.security;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.config.Customizer;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.header.writers.XXssProtectionHeaderWriter;
import org.springframework.web.cors.CorsConfiguration;
import org.springframework.web.cors.CorsConfigurationSource;
import org.springframework.web.cors.UrlBasedCorsConfigurationSource;

import java.util.Arrays;

@Configuration
@EnableWebSecurity
public class SecurityConfig {

    @Bean
    public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {
        return http
            // ASVS V14.5.3 - Configuración CORS Estricta
            .cors(Customizer.withDefaults())
            .csrf(csrf -> csrf.disable()) // API Stateless
            
            // ASVS V14.4 - Cabeceras de Seguridad HTTP
            .headers(headers -> headers
                // V14.4.1 - Content Type Options (nosniff)
                .contentTypeOptions(Customizer.withDefaults())
                // V14.4.7 - X-Frame-Options (DENY para evitar Clickjacking)
                .frameOptions(frame -> frame.deny())
                // V14.4.3 - Content Security Policy (CSP)
                .contentSecurityPolicy(csp -> csp.policyDirectives("default-src 'self'"))
                // V14.4.1 - XSS Protection
                .xssProtection(xss -> xss.headerValue(XXssProtectionHeaderWriter.HeaderValue.ENABLED_MODE_BLOCK))
            )
            
            // ASVS V4.1 - Control de Acceso (Least Privilege)
            .authorizeHttpRequests(auth -> auth
                .requestMatchers("/usuarios/register", "/usuarios/login**", "/error").permitAll()
                .requestMatchers("/v3/api-docs/**", "/swagger-ui/**").permitAll()
                // 🛑 Bloqueo por defecto: Todo lo demás requiere autenticación
                .anyRequest().authenticated()
            )
            .oauth2Login(oauth2 -> oauth2.defaultSuccessUrl("/home"))
            .sessionManagement(session -> session.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
            .oauth2ResourceServer(oauth -> oauth.jwt(Customizer.withDefaults()))
            .build();
    }

    // Bean para permitir peticiones solo desde el Frontend local
    @Bean
    CorsConfigurationSource corsConfigurationSource() {
        CorsConfiguration configuration = new CorsConfiguration();
        configuration.setAllowedOrigins(Arrays.asList("http://localhost:3000", "http://127.0.0.1:3000"));
        configuration.setAllowedMethods(Arrays.asList("GET", "POST", "PUT", "DELETE", "OPTIONS"));
        configuration.setAllowedHeaders(Arrays.asList("Authorization", "Content-Type"));
        UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
        source.registerCorsConfiguration("/**", configuration);
        return source;
    }
}
```

---

## 3. Modelo de Datos y Validación de Entradas (ASVS V5 & V2)

Se aplican anotaciones de validación para asegurar la integridad de los datos y la complejidad de las contraseñas.

**Archivo:** `src/main/java/com/pgs/crud_java/model/Usuario.java`

```java
package com.pgs.crud_java.model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import lombok.Data;

@Entity
@Data
public class Usuario {
    
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @NotBlank(message = "El nombre es obligatorio")
    private String nombre;

    private String apellidos;

    // ASVS V5.1.4 - Validación de tipos estructurados (Email)
    @Email(message = "Formato de email inválido")
    @NotBlank
    @Column(unique = true)
    private String email;

    // ASVS V2.1.1 (Longitud > 8) y V2.1.9 (Complejidad)
    @Pattern(regexp = "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,20}$",
             message = "La contraseña debe tener min 8 caracteres, una mayúscula, una minúscula y un número.")
    private String password;
}
```

---

## 4. Controladores Seguros (ASVS V5)

Se fuerza la ejecución de las validaciones definidas en el modelo usando `@Valid`.

**Archivo:** `src/main/java/com/pgs/crud_java/controller/UsuarioController.java`

```java
@PostMapping("/register")
// Se añade @Valid para disparar las protecciones de ASVS V5 Input Validation
public ResponseEntity<Usuario> crearUsuario(@Valid @RequestBody Usuario usuario) {
    return ResponseEntity.status(201).body(service.crearUsuario(usuario, passwordEncoder));
}
```

---

## 5. Lógica de Negocio y Cifrado (ASVS V2.4)

Se corrige la vulnerabilidad de actualización de usuario para asegurar que las contraseñas nunca se guarden en texto plano.

**Archivo:** `src/main/java/com/pgs/crud_java/service/UsuarioService.java`

```java
public Usuario actualizarUsuario(Long id, Usuario usuario) {
    return repository.findById(id).map(u -> {
        u.setNombre(usuario.getNombre());
        u.setApellidos(usuario.getApellidos());
        u.setEmail(usuario.getEmail());
        
        // ASVS V2.4.1 - Almacenamiento seguro de credenciales
        // Si viene password, se encripta antes de guardar.
        if (usuario.getPassword() != null && !usuario.getPassword().isBlank()) {
            u.setPassword(new BCryptPasswordEncoder().encode(usuario.getPassword()));
        }
        return repository.save(u);
    }).orElse(null);
}
```

