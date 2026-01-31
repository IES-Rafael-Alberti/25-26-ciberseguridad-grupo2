import { Sequelize } from 'sequelize';

const sequelize = new Sequelize({
  dialect: 'sqlite',
  storage: process.env.DB_PATH || './database.sqlite',
  logging: process.env.NODE_ENV === 'development' ? console.log : false,
  // Control V5.1.1: Validación de integridad
  define: {
    timestamps: true,
    underscored: false
  }
});

try {
  await sequelize.authenticate();
  console.log('✓ Database connection established');
} catch (error) {
  console.error('✗ Unable to connect to database:', error);
  process.exit(1);
}

export default sequelize;
