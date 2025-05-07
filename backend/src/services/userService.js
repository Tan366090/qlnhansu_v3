import bcrypt from "bcrypt.js";
import jwt from "jsonwebtoken";
import pool from "../config/database.js";
import logger from "../config/logger.js";

class UserService {
    async createUser(userData) {
        try {
            const hashedPassword = await bcrypt.hash(userData.password, 10);
            const [result] = await pool.query(
                "INSERT INTO users (email, password, name, phone) VALUES (?, ?, ?, ?)",
                [userData.email, hashedPassword, userData.name, userData.phone]
            );
            return { id: result.insertId };
        } catch (error) {
            logger.error("Error creating user:", error);
            throw new Error("Failed to create user");
        }
    }

    async authenticateUser(email, password) {
        try {
            const [users] = await pool.query("SELECT * FROM users WHERE email = ?", [email]);
            if (users.length === 0) {
                throw new Error("User not found");
            }

            const user = users[0];
            const isValid = await bcrypt.compare(password, user.password);
            if (!isValid) {
                throw new Error("Invalid password");
            }

            const token = jwt.sign(
                { userId: user.id, email: user.email },
                process.env.JWT_SECRET,
                { expiresIn: "24h" }
            );

            return { token, user: { id: user.id, email: user.email, name: user.name } };
        } catch (error) {
            logger.error("Error authenticating user:", error);
            throw error;
        }
    }

    async getUserById(userId) {
        try {
            const [users] = await pool.query(
                "SELECT id, email, name, phone, created_at FROM users WHERE id = ?",
                [userId]
            );
            return users[0];
        } catch (error) {
            logger.error("Error fetching user:", error);
            throw new Error("Failed to fetch user");
        }
    }

    async updateUser(userId, updateData) {
        try {
            const allowedUpdates = ["name", "phone"];
            const updates = Object.keys(updateData)
                .filter(key => allowedUpdates.includes(key))
                .map(key => `${key} = ?`);
            
            if (updates.length === 0) return null;

            const values = [...Object.keys(updateData)
                .filter(key => allowedUpdates.includes(key))
                .map(key => updateData[key]), userId];

            const [result] = await pool.query(
                `UPDATE users SET ${updates.join(", ")} WHERE id = ?`,
                values
            );
            return result.affectedRows > 0;
        } catch (error) {
            logger.error("Error updating user:", error);
            throw new Error("Failed to update user");
        }
    }
}

export default new UserService();
