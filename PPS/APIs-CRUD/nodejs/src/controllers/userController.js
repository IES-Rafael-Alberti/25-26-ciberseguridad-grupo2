import User from "../models/user.js";
import bcrypt from "bcrypt";
import jwt from "jsonwebtoken";
import fetch from "node-fetch";

const JWT_SECRET = process.env.JWT_SECRET || "secret123";
const JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || "1h";

// Crear usuario (admin/manual) — hashea si viene password
export const createUser = async (req, res) => {
  try {
    const payload = { ...req.body };
    if (payload.password) {
      payload.password = await bcrypt.hash(payload.password, 10);
    }
    const user = await User.create(payload);
    const { password, ...userSafe } = user.toJSON();
    res.status(201).json(userSafe);
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

    const token = jwt.sign(
      { id: user.id, username: user.username },
      JWT_SECRET,
      { expiresIn: JWT_EXPIRES_IN }
    );

    const { password: pwd, ...userSafe } = user.toJSON();
    res.status(200).json({ message: "Login exitoso", token, user: userSafe });
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

    const token = jwt.sign(
      { id: user.id, username: user.username },
      JWT_SECRET,
      { expiresIn: JWT_EXPIRES_IN }
    );

    res
      .status(201)
      .json({ message: "Usuario registrado exitosamente", token, userId: user.id });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Redirige al consentimiento de GitHub
export const githubAuth = (req, res) => {
  const clientId = process.env.GITHUB_CLIENT_ID;
  const scope = "user:email";
  const redirectUri = process.env.GITHUB_CALLBACK_URL;
  const url = `https://github.com/login/oauth/authorize?client_id=${clientId}&scope=${encodeURIComponent(scope)}&redirect_uri=${encodeURIComponent(redirectUri)}`;
  return res.redirect(url);
};

// Callback: intercambia code por token, obtiene perfil y emite JWT
export const githubCallback = async (req, res) => {
  try {
    const code = req.query.code;
    if (!code) return res.status(400).json({ message: "Missing code" });

    // Intercambio por access token
    const tokenResp = await fetch("https://github.com/login/oauth/access_token", {
  method: "POST",
  headers: { "Accept": "application/json", "Content-Type": "application/json" },
  body: JSON.stringify({
    client_id: process.env.GITHUB_CLIENT_ID,
    client_secret: process.env.GITHUB_CLIENT_SECRET,
    code,
    redirect_uri: process.env.GITHUB_CALLBACK_URL
  })
});

    const tokenJson = await tokenResp.json();
    if (!tokenJson.access_token) {
      return res.status(400).json({ message: "Error obtaining access token", details: tokenJson });
    }
    const accessToken = tokenJson.access_token;

    // Obtener datos de usuario
    const userResp = await fetch("https://api.github.com/user", {
      headers: { Authorization: `token ${accessToken}`, "User-Agent": "node.js" }
    });
    const profile = await userResp.json();

    // Obtener email primario si no viene en profile.email
    let email = profile.email;
    if (!email) {
      const emailsResp = await fetch("https://api.github.com/user/emails", {
        headers: { Authorization: `token ${accessToken}`, "User-Agent": "node.js" }
      });
      const emails = await emailsResp.json();
      const primary = Array.isArray(emails) && emails.find(e => e.primary) || emails[0];
      email = primary && primary.email;
    }

    // Buscar o crear usuario local
    let user = null;
    if (email) {
      user = await User.findOne({ where: { email } });
    }
    if (!user) {
      // crear username único si hace falta
      const username = profile.login ? `gh_${profile.login}` : `gh_${profile.id}`;
      const randomPassword = Math.random().toString(36).slice(2, 12);
      const hashed = await bcrypt.hash(randomPassword, 10);
      user = await User.create({
        username,
        password: hashed,
        email: email || `${username}@github.local`
      });
    }

    // Firmar JWT y devolver
    const token = jwt.sign({ id: user.id, username: user.username }, JWT_SECRET, { expiresIn: JWT_EXPIRES_IN });
    // Responder con JSON (puedes redirigir a front-end incluyendo token en URL/fragment)
    return res.json({ message: "GitHub OAuth login successful", token, userId: user.id });
  } catch (err) {
    return res.status(500).json({ message: err.message });
  }
};
