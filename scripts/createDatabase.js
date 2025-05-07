import { config } from "dotenv";
import mysql from "mysql2/promise";

config();

async function createDatabase() {
  const connection = await mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
  });

  try {
    await connection.query(`CREATE DATABASE IF NOT EXISTS ${process.env.DB_NAME}`);
    console.log("Database created successfully");
  } catch (error) {
    console.error("Error creating database:", error);
    process.exit(1);
  } finally {
    await connection.end();
  }
}

createDatabase(); 