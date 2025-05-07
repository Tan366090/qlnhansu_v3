import { config } from "dotenv";
import mysql from "mysql2/promise";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function backupDatabase() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        // Tạo thư mục backup nếu chưa tồn tại
        const backupDir = path.join(__dirname, "../backups");
        if (!fs.existsSync(backupDir)) {
            fs.mkdirSync(backupDir);
        }

        // Tạo tên file backup với timestamp
        const timestamp = new Date().toISOString().replace(/[:.]/g, "-");
        const backupFile = path.join(backupDir, `backup-${timestamp}.sql`);

        // Lấy danh sách các bảng
        const [tables] = await connection.query("SHOW TABLES");
        
        let backupContent = "";
        
        // Backup từng bảng
        for (const table of tables) {
            const tableName = table[`Tables_in_${process.env.DB_NAME}`];
            
            // Lấy cấu trúc bảng
            const [createTable] = await connection.query(`SHOW CREATE TABLE ${tableName}`);
            backupContent += `\n\n-- Table structure for ${tableName}\n`;
            backupContent += createTable[0]["Create Table"] + ";\n\n";
            
            // Lấy dữ liệu
            const [rows] = await connection.query(`SELECT * FROM ${tableName}`);
            if (rows.length > 0) {
                backupContent += `-- Data for ${tableName}\n`;
                for (const row of rows) {
                    const values = Object.values(row).map(v => 
                        v === null ? "NULL" : 
                        typeof v === "string" ? `'${v.replace(/'/g, "''")}'` : v
                    );
                    backupContent += `INSERT INTO ${tableName} VALUES (${values.join(", ")});\n`;
                }
            }
        }

        // Ghi file backup
        fs.writeFileSync(backupFile, backupContent);
        console.log(`Backup created successfully: ${backupFile}`);
    } catch (error) {
        console.error("Error creating backup:", error);
        process.exit(1);
    } finally {
        await connection.end();
    }
}

backupDatabase(); 