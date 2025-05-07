const fs = require('fs');
const path = require('path');

const uploadFile = async (file, folder) => {
    try {
        // Create folder if it doesn't exist
        const uploadDir = path.join(__dirname, '../../uploads', folder);
        if (!fs.existsSync(uploadDir)) {
            fs.mkdirSync(uploadDir, { recursive: true });
        }

        // Generate unique filename
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        const filename = uniqueSuffix + '-' + file.originalname;
        const filePath = path.join(uploadDir, filename);

        // Move file to destination
        await fs.promises.rename(file.path, filePath);

        // Return relative path for database storage
        return path.join('uploads', folder, filename).replace(/\\/g, '/');
    } catch (error) {
        console.error('Error uploading file:', error);
        throw new Error('Có lỗi xảy ra khi tải lên file');
    }
};

module.exports = {
    uploadFile
}; 