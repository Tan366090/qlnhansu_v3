import { config } from "dotenv";
import mysql from "mysql2/promise";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function checkDatabase() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        // Kiểm tra các bảng hiện có
        const [tables] = await connection.query("SHOW TABLES");
        console.log("Existing tables:", tables.map(t => t[`Tables_in_${process.env.DB_NAME}`]));

        // Kiểm tra cấu trúc từng bảng
        for (const table of tables) {
            const tableName = table[`Tables_in_${process.env.DB_NAME}`];
            const [columns] = await connection.query(`DESCRIBE ${tableName}`);
            console.log(`\nStructure of table ${tableName}:`);
            console.table(columns);
        }

        // Kiểm tra số lượng bản ghi trong mỗi bảng
        for (const table of tables) {
            const tableName = table[`Tables_in_${process.env.DB_NAME}`];
            const [count] = await connection.query(`SELECT COUNT(*) as count FROM ${tableName}`);
            console.log(`\nNumber of records in ${tableName}:`, count[0].count);
        }
    } catch (error) {
        console.error("Error checking database:", error);
    } finally {
        await connection.end();
    }
}

checkDatabase(); 