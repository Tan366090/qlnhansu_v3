import { config } from "dotenv";
import mysql from "mysql2/promise";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

config();

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function setupDatabase() {
    console.log("Starting database setup...");

    // 1. Tạo kết nối root để tạo database
    const rootConnection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD
    });

    try {
        // 2. Tạo database nếu chưa tồn tại
        await rootConnection.query(`CREATE DATABASE IF NOT EXISTS ${process.env.DB_NAME}`);
        console.log("Database created/verified");

        // 3. Đóng kết nối root
        await rootConnection.end();

        // 4. Tạo kết nối mới với database
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME,
            multipleStatements: true
        });

        try {
            // 5. Kiểm tra các bảng hiện có
            const [tables] = await connection.query("SHOW TABLES");
            const existingTables = tables.map(t => t[`Tables_in_${process.env.DB_NAME}`]);
            console.log("Existing tables:", existingTables);

            // 6. Backup dữ liệu nếu có bảng tồn tại
            if (existingTables.length > 0) {
                console.log("Backing up existing data...");
                const backupDir = path.join(__dirname, "../backups");
                if (!fs.existsSync(backupDir)) {
                    fs.mkdirSync(backupDir);
                }
                const timestamp = new Date().toISOString().replace(/[:.]/g, "-");
                const backupFile = path.join(backupDir, `backup-${timestamp}.sql`);
                
                let backupContent = "";
                for (const table of existingTables) {
                    const [createTable] = await connection.query(`SHOW CREATE TABLE ${table}`);
                    backupContent += `\n\n-- Table structure for ${table}\n`;
                    backupContent += createTable[0]["Create Table"] + ";\n\n";
                    
                    const [rows] = await connection.query(`SELECT * FROM ${table}`);
                    if (rows.length > 0) {
                        backupContent += `-- Data for ${table}\n`;
                        for (const row of rows) {
                            const values = Object.values(row).map(v => 
                                v === null ? "NULL" : 
                                typeof v === "string" ? `'${v.replace(/'/g, "''")}'` : v
                            );
                            backupContent += `INSERT INTO ${table} VALUES (${values.join(", ")});\n`;
                        }
                    }
                }
                fs.writeFileSync(backupFile, backupContent);
                console.log(`Backup created: ${backupFile}`);
            }

            // 7. Import dữ liệu từ file SQL
            console.log("Importing data from SQL file...");
            const sqlPath = path.join(__dirname, "../src/database/qlnhansu.sql");
            const sqlContent = fs.readFileSync(sqlPath, "utf8");
            
            // Tách các câu lệnh SQL và thực thi từng câu một
            const statements = sqlContent.split(";").filter(stmt => stmt.trim());
            
            for (const statement of statements) {
                try {
                    if (statement.trim()) {
                        // Bỏ qua các câu lệnh DELIMITER và TRIGGER
                        if (statement.includes("DELIMITER") || statement.includes("CREATE TRIGGER")) {
                            console.log("Skipping DELIMITER/TRIGGER statement");
                            continue;
                        }

                        // Bỏ qua các câu lệnh ALTER TABLE nếu bảng đã tồn tại
                        if (statement.includes("ALTER TABLE") && existingTables.some(table => 
                            statement.includes(`\`${table}\``))) {
                            console.log(`Skipping ALTER TABLE for existing table: ${statement.match(/`([^`]+)`/)[1]}`);
                            continue;
                        }
                        
                        await connection.query(statement);
                    }
                } catch (error) {
                    // Bỏ qua các lỗi thông thường
                    if (error.code === "ER_TABLE_EXISTS_ERROR" || 
                        error.code === "ER_DUP_ENTRY" ||
                        error.code === "ER_MULTIPLE_PRI_KEY" ||
                        error.code === "ER_DUP_KEYNAME" ||
                        error.code === "ER_CANT_CREATE_TABLE" ||
                        error.code === "ER_PARSE_ERROR") {
                        console.log(`Skipping error: ${error.message}`);
                        continue;
                    }
                    console.error("Error executing statement:", error);
                }
            }
            console.log("Data import completed");

            // 8. Chạy migrations
            console.log("Running migrations...");
            const migrationsPath = path.join(__dirname, "../src/database/migrations");
            const migrationFiles = fs.readdirSync(migrationsPath)
                .filter(file => file.endsWith(".js"))
                .sort();

            for (const file of migrationFiles) {
                try {
                    const migration = await import(path.join(migrationsPath, file));
                    await migration.up(connection, mysql);
                    console.log(`Migration ${file} completed`);
                } catch (error) {
                    console.error(`Error in migration ${file}:`, error);
                }
            }
            console.log("All migrations completed");

        } finally {
            await connection.end();
        }

    } catch (error) {
        console.error("Error during setup:", error);
        process.exit(1);
    }
}

setupDatabase(); 