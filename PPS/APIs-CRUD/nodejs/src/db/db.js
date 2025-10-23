const { Sequelize } = require('sequelize');

const sequelize = new Sequelize('database', 'username', 'password', {
  host: 'localhost',
  dialect: 'sqlite', 
  storage: './db.sqlite', 
});

module.exports = sequelize;
