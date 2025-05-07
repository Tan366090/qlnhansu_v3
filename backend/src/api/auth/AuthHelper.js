import jwt from "jsonwebtoken";
import dotenv from "dotenv";
import bcrypt from "bcryptjs";
import db from "../../config/database.js";
import { time } from "../../utils/time.js";

dotenv.config();

export class AuthHelper {
    constructor() {
        this.JWT_SECRET = process.env.JWT_SECRET || "your-secret-key";
        this.JWT_ALGO = "HS256";
    }

    async authenticateUser(username, password) {
        try {
            const [users] = await db.query(
                "SELECT id, username, password, role FROM users WHERE username = ?",
                [username]
            );

            if (users.length === 0) {
                return false;
            }

            const user = users[0];
            const isValidPassword = await bcrypt.compare(password, user.password);

            if (!isValidPassword) {
                return false;
            }

            return this.generateJWT(user);
        } catch (error) {
            console.error("Authentication error:", error);
            return false;
        }
    }

    generateJWT(user) {
        const issuedAt = time();
        const expire = issuedAt + 3600; // 1 hour expiration

        const payload = {
            iat: issuedAt,
            exp: expire,
            user_id: user.id,
            username: user.username,
            role: user.role
        };

        return jwt.sign(payload, this.JWT_SECRET, { algorithm: this.JWT_ALGO });
    }

    verifyJWT(token) {
        try {
            return jwt.verify(token, this.JWT_SECRET, { algorithms: [this.JWT_ALGO] });
        } catch (error) {
            console.error("JWT verification error:", error);
            return false;
        }
    }

    async hashPassword(password) {
        return await bcrypt.hash(password, 10);
    }

    async updatePassword(userId, newPassword) {
        try {
            const hashedPassword = await this.hashPassword(newPassword);
            await db.query(
                "UPDATE users SET password_hash = ? WHERE user_id = ?",
                [hashedPassword, userId]
            );
            return true;
        } catch (error) {
            console.error("Password update error:", error);
            return false;
        }
    }
} 