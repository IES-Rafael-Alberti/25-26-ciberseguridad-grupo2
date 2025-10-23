package com.pgs.crud_java.service;


import com.pgs.crud_java.model.Usuario;
import com.pgs.crud_java.repository.UsuarioRepository;
import org.springframework.stereotype.Service;

import java.util.List;
import java.util.Optional;

@Service
public class UsuarioService {

    private final UsuarioRepository repository;

    public UsuarioService(UsuarioRepository repository) {
        this.repository = repository;
    }

    public Usuario crearUsuario(Usuario usuario) {
        return repository.save(usuario);
    }

    public List<Usuario> obtenerTodos() {
        return repository.findAll();
    }

    public Optional<Usuario> obtenerPorId(Long id) {
        return repository.findById(id);
    }

    public Usuario actualizarUsuario(Long id, Usuario usuario) {
        return repository.findById(id).map(u -> {
            u.setNombre(usuario.getNombre());
            u.setApellidos(usuario.getApellidos());
            u.setEmail(usuario.getEmail());
            u.setPassword(usuario.getPassword());
            return repository.save(u);
        }).orElse(null);
    }

    public void eliminarUsuario(Long id) {
        repository.deleteById(id);
    }
}