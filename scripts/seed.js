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

async function runSeeders() {
    try {
        const seedersPath = path.join(__dirname, "../src/database/seeders");
        const seederFiles = fs.readdirSync(seedersPath)
            .filter(file => file.endsWith(".js"))
            .sort();

        for (const file of seederFiles) {
            const seeder = await import(path.join(seedersPath, file));
            await seeder.up(sequelize.getQueryInterface(), Sequelize);
            console.log(`Seeder ${file} completed successfully`);
        }

        console.log("All seeders completed successfully");
    } catch (error) {
        console.error("Error running seeders:", error);
        process.exit(1);
    } finally {
        await sequelize.close();
    }
}

runSeeders(); 