import express from "express";
import {
  createUser,
  getUsers,
  getUserById,
  updateUser,
  deleteUser,
  login,
  register,
  githubAuth,
  githubCallback
} from "../controllers/userController.js";
import { authenticateToken } from "../middleware/authMiddleware.js";

const router = express.Router();

// Public
router.post("/register", register);
router.post("/login", login);

// GitHub OAuth
router.get("/auth/github", githubAuth);
router.get("/auth/github/callback", githubCallback);

// Protect remaining routes
router.use(authenticateToken);

router.post("/", createUser);
router.get("/", getUsers);
router.get("/:id", getUserById);
router.put("/:id", updateUser);
router.delete("/:id", deleteUser);

export default router;
