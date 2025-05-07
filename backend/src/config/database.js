import { config } from "dotenv";
import { Sequelize } from "sequelize";
import appConfig from "./config.js";
import logger from "../utils/logger.js";

// Load environment variables
config();

// Create Sequelize instance
const sequelize = new Sequelize(
  appConfig.database.name,
  appConfig.database.user,
  appConfig.database.password,
  {
    host: appConfig.database.host,
    port: appConfig.database.port,
    dialect: appConfig.database.dialect,
    logging: (msg) => logger.debug(msg),
    pool: {
      max: 5,
      min: 0,
      acquire: 30000,
      idle: 10000
    }
  }
);

// Test connection
const testConnection = async () => {
  try {
    await sequelize.authenticate();
    logger.info("Database connection has been established successfully.");
  } catch (error) {
    logger.error("Unable to connect to the database:", error);
    process.exit(1);
  }
};

// Sync database
const syncDatabase = async () => {
  try {
    await sequelize.sync({ alter: true });
    logger.info("Database synchronized successfully.");
  } catch (error) {
    logger.error("Error synchronizing database:", error);
    process.exit(1);
  }
};

testConnection();

export default sequelize;
export { testConnection, syncDatabase };
