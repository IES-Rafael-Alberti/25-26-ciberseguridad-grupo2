package com.pgs.crud_java.model;

import jakarta.persistence.*;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import lombok.Data;
import jakarta.validation.constraints.Pattern;


@Entity
@Table(name = "usuarios")
@Data
public class Usuario {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @NotBlank(message = "El nombre no puede estar vacío")
    private String nombre;

    private String apellidos;

    @Email(message = "Formato de email inválido") // ASVS V5.1.4
    @NotBlank
    @Column(unique = true)
    private String email;

    // ASVS V2.1.1 (Longitud) y V2.1.9 (Complejidad)
    // Mínimo 8 caracteres, una mayúscula, una minúscula, un número.
    @Pattern(regexp = "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{8,20}$",
            message = "La contraseña debe tener 8 chars, mayúscula, minúscula y número")
    private String password;

}
