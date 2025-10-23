import express from "express";
import cors from "cors";
import sequelize from "./db/db.js";
import usersRouter from "./routes/users.js";

const app = express();
app.use(cors());
app.use(express.json());

// Routes
app.use("/users", usersRouter);
app.get('/', (req, res) => {
  res.send('Hola mundo desde Node.js!');
});

// Sync database
sequelize.sync({ alter: true })
  .then(() => console.log("Database synchronized"))
  .catch(err => console.error("Error synchronizing the database:", err));

const PORT = process.env.PORT || 8080;
app.listen(PORT, () => console.log(`Server running at http://localhost:${PORT}`));
