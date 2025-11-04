import { DataTypes } from 'sequelize';
import sequelize from '../db/db.js';

const User = sequelize.define('User', {
    id: {
        type: DataTypes.INTEGER,
        primaryKey: true,
        autoIncrement: true
    },
    username: {
        type: DataTypes.STRING,
        allowNull: false,
        unique: true
    },
    password: {
        type: DataTypes.STRING,
        allowNull: false
    },
    email: {
        type: DataTypes.STRING,
        allowNull: false,
        unique: true
    }
}, {
    // Force sync to recreate the table
    timestamps: true
});

// Force sync only in development
const syncDB = async () => {
    await User.sync({ force: true });
    console.log("User table (re)created");
};

syncDB();

export default User;
