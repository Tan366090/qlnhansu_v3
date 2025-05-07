import nodemailer from "nodemailer";
import dotenv from "dotenv";
import config from "../config/config.js";
import logger from "../config/logger.js";

dotenv.config();

// Tạo transporter
const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: process.env.SMTP_PORT,
    secure: false, // true for 465, false for other ports
    auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS
    }
});

// Hàm gửi email
export const sendEmail = async ({ to, subject, text, html }) => {
    try {
        const info = await transporter.sendMail({
            from: `"HR System" <${process.env.SMTP_USER}>`,
            to,
            subject,
            text,
            html
        });

        logger.info("Email sent:", info.messageId);
        return info;
    } catch (error) {
        logger.error("Error sending email:", error);
        throw error;
    }
}; 