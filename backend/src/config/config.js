import { config } from "dotenv";
import { fileURLToPath } from "url";
import { dirname, join } from "path";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Load environment variables
config();

export default {
    // Server configuration
    server: {
        port: process.env.PORT || 3000,
        env: process.env.NODE_ENV || "development"
    },

    // Database configuration
    database: {
        host: process.env.DB_HOST || "localhost",
        port: process.env.DB_PORT || 3306,
        name: process.env.DB_NAME || "qlnhansu",
        user: process.env.DB_USER || "root",
        password: process.env.DB_PASSWORD || "",
        dialect: "mysql",
        logging: process.env.NODE_ENV === "development" ? console.log : false
    },

    // JWT configuration
    jwt: {
        secret: process.env.JWT_SECRET || "your-secret-key",
        expiresIn: process.env.JWT_EXPIRES_IN || "24h"
    },

    // CORS configuration
    cors: {
        origin: process.env.CORS_ORIGIN || "*",
        methods: ["GET", "POST", "PUT", "DELETE", "PATCH"],
        allowedHeaders: ["Content-Type", "Authorization"]
    },

    // Rate limit configuration
    rateLimit: {
        windowMs: 15 * 60 * 1000, // 15 minutes
        max: 100 // limit each IP to 100 requests per windowMs
    },

    // File upload configuration
    upload: {
        maxFileSize: 5 * 1024 * 1024, // 5MB
        allowedTypes: ["image/jpeg", "image/png", "application/pdf"],
        uploadDir: "uploads"
    },

    app: {
        name: process.env.APP_NAME || "QLNhanSu",
        env: process.env.APP_ENV || "development",
        debug: process.env.APP_DEBUG === "true",
        url: process.env.APP_URL || "http://localhost",
        key: process.env.APP_KEY,
        port: process.env.PORT || 3000
    },

    mail: {
        host: process.env.SMTP_HOST || 'smtp.gmail.com',
        port: process.env.SMTP_PORT || 587,
        secure: false,
        auth: {
            user: process.env.SMTP_USERNAME,
            pass: process.env.SMTP_PASSWORD
        },
        from: process.env.SMTP_FROM_EMAIL,
        fromName: process.env.SMTP_FROM_NAME || 'HR System'
    },

    redis: {
        host: process.env.REDIS_HOST || "redis",
        password: process.env.REDIS_PASSWORD || null,
        port: process.env.REDIS_PORT || 6379
    },

    cache: {
        driver: process.env.CACHE_DRIVER || "redis"
    },

    session: {
        driver: process.env.SESSION_DRIVER || "redis",
        lifetime: process.env.SESSION_LIFETIME || 120,
        secure: process.env.SESSION_SECURE === "true",
        same_site: process.env.SESSION_SAME_SITE || "lax"
    },

    queue: {
        connection: process.env.QUEUE_CONNECTION || "redis"
    },

    logging: {
        channel: process.env.LOG_CHANNEL || "stack",
        level: process.env.LOG_LEVEL || "debug",
        path: join(__dirname, "../logs")
    }
}; 