const express = require('express');
const router = express.Router();
const multer = require('multer');
const coursesController = require('../../controllers/training/courses.controller');
const { isAdmin } = require('../../middleware/auth');

// Configure multer for file uploads
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/training-materials/');
    },
    filename: function (req, file, cb) {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, uniqueSuffix + '-' + file.originalname);
    }
});

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 10 * 1024 * 1024 // 10MB limit
    },
    fileFilter: function (req, file, cb) {
        // Accept only PDF, DOC, DOCX, PPT, PPTX files
        if (!file.originalname.match(/\.(pdf|doc|docx|ppt|pptx)$/)) {
            return cb(new Error('Chỉ chấp nhận file PDF, DOC, DOCX, PPT, PPTX'));
        }
        cb(null, true);
    }
});

// Get all courses with pagination and filtering
router.get('/', coursesController.getCourses);

// Get course by ID
router.get('/:id', coursesController.getCourseById);

// Create new course (admin only)
router.post('/', isAdmin, upload.array('materials'), coursesController.createCourse);

// Update course (admin only)
router.put('/:id', isAdmin, upload.array('materials'), coursesController.updateCourse);

// Delete course (admin only)
router.delete('/:id', isAdmin, coursesController.deleteCourse);

module.exports = router; 