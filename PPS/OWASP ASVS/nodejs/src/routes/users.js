import express from 'express';
import {
  register,
  login,
  logout,
  getUsers,
  getUserById,
  updateUser,
  deleteUser,
  getGitHubAuthUrl,
  githubCallback
} from '../controllers/userController.js';
import { verificarAuth } from '../middleware/authMiddleware.js';

const router = express.Router();

// Rutas públicas
router.post('/register', register);
router.post('/login', login);
router.get('/auth/github', getGitHubAuthUrl);
router.get('/auth/github/callback', githubCallback);

// Rutas protegidas (requieren JWT)
router.post('/logout', verificarAuth, logout);
router.get('/', verificarAuth, getUsers);
router.get('/:id', verificarAuth, getUserById);
router.put('/:id', verificarAuth, updateUser);
router.delete('/:id', verificarAuth, deleteUser);

export default router;
