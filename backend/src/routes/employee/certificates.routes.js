const express = require('express');
const router = express.Router();
const multer = require('multer');
const certificatesController = require('../../controllers/employee/certificates.controller');
const { isAdmin } = require('../../middleware/auth');

// Configure multer for file uploads
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/certificates/');
    },
    filename: function (req, file, cb) {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + '-' + file.originalname);
    }
});

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 5 * 1024 * 1024 // 5MB limit
    },
    fileFilter: function (req, file, cb) {
        // Accept only PDF, JPG, PNG files
        if (!file.originalname.match(/\.(pdf|jpg|jpeg|png)$/)) {
            return cb(new Error('Chỉ chấp nhận file PDF, JPG, PNG'));
        }
        cb(null, true);
    }
});

// Get all certificates with pagination and filtering
router.get('/', certificatesController.getCertificates);

// Get certificate by ID
router.get('/:id', certificatesController.getCertificateById);

// Create new certificate (admin only)
router.post('/', isAdmin, upload.single('file'), certificatesController.createCertificate);

// Update certificate (admin only)
router.put('/:id', isAdmin, upload.single('file'), certificatesController.updateCertificate);

// Delete certificate (admin only)
router.delete('/:id', isAdmin, certificatesController.deleteCertificate);

module.exports = router; 