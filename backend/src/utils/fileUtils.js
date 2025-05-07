import multer from "multer";
import path from "path";
import { fileURLToPath } from "url";
import logger from "../config/logger.js";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Cấu hình multer để lưu file
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, path.join(__dirname, "../uploads"));
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + "-" + Math.round(Math.random() * 1E9);
        cb(null, file.fieldname + "-" + uniqueSuffix + path.extname(file.originalname));
    }
});

// Kiểm tra loại file
const fileFilter = (req, file, cb) => {
    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "application/pdf"];
    if (allowedTypes.includes(file.mimetype)) {
        cb(null, true);
    } else {
        cb(new Error("Loại file không được hỗ trợ"), false);
    }
};

// Cấu hình multer
export const upload = multer({
    storage: storage,
    fileFilter: fileFilter,
    limits: {
        fileSize: 5 * 1024 * 1024 // Giới hạn 5MB
    }
});

// Hàm xử lý upload file
export const uploadFile = (req, res, next) => {
    try {
        if (!req.file) {
            return res.status(400).json({ message: "Vui lòng chọn file để upload" });
        }

        const fileUrl = `/uploads/${req.file.filename}`;
        req.fileUrl = fileUrl;
        next();
    } catch (error) {
        logger.error("Error uploading file:", error);
        res.status(500).json({ message: "Lỗi khi upload file" });
    }
};

// Hàm xóa file
export const deleteFile = async (filePath) => {
    try {
        const fs = await import("fs/promises");
        const fullPath = path.join(__dirname, "../", filePath);
        await fs.unlink(fullPath);
    } catch (error) {
        logger.error("Error deleting file:", error);
        throw error;
    }
};

// Hàm kiểm tra file tồn tại
export const fileExists = async (filePath) => {
    try {
        const fs = await import("fs/promises");
        const fullPath = path.join(__dirname, "../", filePath);
        await fs.access(fullPath);
        return true;
    } catch (error) {
        return false;
    }
};
