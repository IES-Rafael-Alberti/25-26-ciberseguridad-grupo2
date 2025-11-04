package com.pgs.crud_java.controller;


import com.pgs.crud_java.model.Usuario;
import com.pgs.crud_java.service.UsuarioService;
import org.springframework.http.ResponseEntity;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Optional;

@RestController
@RequestMapping("/usuarios")
public class UsuarioController {

    private final UsuarioService service;
    private final PasswordEncoder passwordEncoder;

    public UsuarioController(UsuarioService service, PasswordEncoder passwordEncoder) {
        this.service = service;
        this.passwordEncoder = passwordEncoder;
    }

    @PostMapping("/login")
    public ResponseEntity<?> login(@RequestBody Usuario usuario) {
        System.out.println("➡️ Intentando login con: " + usuario.getEmail());

        // Buscar el usuario en BD
        Optional<Usuario> usuarioBD = service.obtenerPorEmail(usuario.getEmail());
        if (usuarioBD.isEmpty()) {
            return ResponseEntity.status(401).body("Usuario no encontrado");
        }

        // Verificar la contraseña
        if (!passwordEncoder.matches(usuario.getPassword(), usuarioBD.get().getPassword())) {
            return ResponseEntity.status(401).body("Contraseña incorrecta");
        }

        // Si llega aquí, todo bien 👌
        return ResponseEntity.ok("Login exitoso ✅");
    }



    @PostMapping("/register")
    public ResponseEntity<Usuario> crearUsuario(@RequestBody Usuario usuario) {
        return ResponseEntity.status(201).body(service.crearUsuario(usuario, passwordEncoder));
    }

    @GetMapping
    public List<Usuario> obtenerTodos() {
        return service.obtenerTodos();
    }

    @GetMapping("/{id}")
    public ResponseEntity<Usuario> obtenerPorId(@PathVariable Long id) {
        return service.obtenerPorId(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }

    @PutMapping("/{id}")
    public ResponseEntity<Usuario> actualizarUsuario(@PathVariable Long id, @RequestBody Usuario usuario) {
        Usuario actualizado = service.actualizarUsuario(id, usuario);
        if (actualizado == null) return ResponseEntity.notFound().build();
        return ResponseEntity.ok(actualizado);
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminarUsuario(@PathVariable Long id) {
        service.eliminarUsuario(id);
        return ResponseEntity.ok().build();
    }
}