import User from "../models/user.js";
import bcrypt from "bcrypt";

// Crear usuario
export const createUser = async (req, res) => {
  try {
    const user = await User.create(req.body);
    res.status(201).json(user);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
};

// Obtener todos los usuarios
export const getUsers = async (req, res) => {
  const users = await User.findAll();
  res.json(users);
};

// Obtener usuario por ID
export const getUserById = async (req, res) => {
  const user = await User.findByPk(req.params.id);
  if (user) res.json(user);
  else res.status(404).json({ error: "Usuario no encontrado" });
};

// Actualizar usuario
export const updateUser = async (req, res) => {
  const user = await User.findByPk(req.params.id);
  if (!user) return res.status(404).json({ error: "Usuario no encontrado" });

  try {
    await user.update(req.body);
    res.json(user);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
};

// Eliminar usuario
export const deleteUser = async (req, res) => {
  const user = await User.findByPk(req.params.id);
  if (!user) return res.status(404).json({ error: "Usuario no encontrado" });

  await user.destroy();
  res.json({ mensaje: "Usuario eliminado correctamente" });
};

// Login
export const login = async (req, res) => {
  try {
    const { username, password } = req.body;

    const user = await User.findOne({ where: { username } });
    if (!user) {
      return res
        .status(401)
        .json({ message: "Usuario o contraseña incorrectos" });
    }

    const validPassword = await bcrypt.compare(password, user.password);
    if (!validPassword) {
      return res
        .status(401)
        .json({ message: "Usuario o contraseña incorrectos" });
    }

    res.status(200).json({ message: "Login exitoso", userId: user.id });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Register
export const register = async (req, res) => {
  try {
    const { username, password, email } = req.body;

    const hashedPassword = await bcrypt.hash(password, 10);
    const user = await User.create({
      username,
      password: hashedPassword,
      email,
    });

    res
      .status(201)
      .json({ message: "Usuario registrado exitosamente", userId: user.id });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
