import { DataTypes } from 'sequelize';
import sequelize from '../db/db.js';
import bcrypt from 'bcrypt';

const User = sequelize.define('User', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  username: {
    type: DataTypes.STRING(20),
    allowNull: false,
    unique: true,
    validate: {
      len: [3, 20],
      is: /^[a-zA-Z0-9_]+$/
    }
  },
  email: {
    type: DataTypes.STRING,
    allowNull: false,
    unique: true,
    validate: {
      isEmail: true
    }
  },
  password: {
    type: DataTypes.STRING,
    allowNull: true, // Null si usa OAuth
    set(value) {
      if (value && value !== this.getDataValue('password')) {
        // Hashear antes de guardar
        this.setDataValue('password', value);
      }
    }
  },
  // Control V6.2.2: Password strength metadata
  passwordStrength: {
    type: DataTypes.INTEGER,
    allowNull: true,
    comment: 'Password strength score (0-100)'
  },
  lastPasswordChange: {
    type: DataTypes.DATE,
    allowNull: true
  },
  // OAuth fields
  githubId: {
    type: DataTypes.STRING,
    allowNull: true,
    unique: true
  },
  oauthProvider: {
    type: DataTypes.STRING,
    allowNull: true,
    comment: 'Provider: github, google, etc'
  },
  lastLogin: {
    type: DataTypes.DATE,
    allowNull: true
  },
  loginCount: {
    type: DataTypes.INTEGER,
    defaultValue: 0
  },
  isActive: {
    type: DataTypes.BOOLEAN,
    defaultValue: true
  },
  createdAt: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  },
  updatedAt: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  }
});

// Hook para hashear contraseña antes de crear/actualizar
User.beforeCreate(async (user) => {
  if (user.password) {
    user.password = await bcrypt.hash(user.password, 10);
  }
});

User.beforeUpdate(async (user) => {
  if (user.changed('password')) {
    user.password = await bcrypt.hash(user.password, 10);
    user.lastPasswordChange = new Date();
  }
});

/**
 * Método para verificar contraseña
 */
User.prototype.verifyPassword = async function (password) {
  return await bcrypt.compare(password, this.password);
};

/**
 * Método para obtener datos seguros del usuario
 */
User.prototype.toSafeJSON = function () {
  const { password, ...safe } = this.toJSON();
  return safe;
};

export default User;
