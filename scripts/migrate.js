import { config } from "dotenv";
import { Sequelize } from "sequelize";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const sequelize = new Sequelize(
    process.env.DB_NAME,
    process.env.DB_USER,
    process.env.DB_PASSWORD,
    {
        host: process.env.DB_HOST,
        dialect: "mysql",
        logging: false,
    }
);

async function runMigrations() {
    try {
        const migrationsPath = path.join(__dirname, "../src/database/migrations");
        const migrationFiles = fs.readdirSync(migrationsPath)
            .filter(file => file.endsWith(".js"))
            .sort();

        for (const file of migrationFiles) {
            const migration = await import(path.join(migrationsPath, file));
            await migration.up(sequelize.getQueryInterface(), Sequelize);
            console.log(`Migration ${file} completed successfully`);
        }

        console.log("All migrations completed successfully");
    } catch (error) {
        console.error("Error running migrations:", error);
        process.exit(1);
    } finally {
        await sequelize.close();
    }
}

runMigrations(); 