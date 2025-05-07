import bcrypt from "bcryptjs";
import db from "./config/database.js";

async function addAdminUser() {
    try {
        const username = "admin";
        const password = "admin123";
        const email = "admin@example.com";
        const role = "admin";

        // Hash password
        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(password, salt);

        // Insert admin user
        const [result] = await db.query(
            "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)",
            [username, hashedPassword, email, role]
        );

        console.log("Admin user created successfully:", result);
    } catch (error) {
        console.error("Error creating admin user:", error);
    }
}

addAdminUser(); 