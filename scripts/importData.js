import { config } from "dotenv";
import mysql from "mysql2/promise";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function importData() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME,
        multipleStatements: true
    });

    try {
        // Đọc file SQL
        const sqlPath = path.join(__dirname, "../src/database/qlnhansu.sql");
        const sqlContent = fs.readFileSync(sqlPath, "utf8");

        // Tách các câu lệnh SQL
        const statements = sqlContent.split(";").filter(stmt => stmt.trim());

        // Thực thi từng câu lệnh
        for (const statement of statements) {
            try {
                if (statement.trim()) {
                    await connection.query(statement);
                    console.log("Executed statement successfully");
                }
            } catch (error) {
                // Bỏ qua lỗi nếu bảng đã tồn tại
                if (error.code === "ER_TABLE_EXISTS_ERROR") {
                    console.log("Table already exists, skipping...");
                    continue;
                }
                // Bỏ qua lỗi nếu dữ liệu đã tồn tại
                if (error.code === "ER_DUP_ENTRY") {
                    console.log("Duplicate entry, skipping...");
                    continue;
                }
                console.error("Error executing statement:", error);
            }
        }

        console.log("Data import completed");
    } catch (error) {
        console.error("Error importing data:", error);
    } finally {
        await connection.end();
    }
}

importData(); 