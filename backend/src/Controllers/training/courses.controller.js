const Course = require('../../models/training/course.model');
const CourseMaterial = require('../../models/training/course-material.model');
const { uploadFile } = require('../../utils/file-upload');
const { Op } = require('sequelize');

// Get all courses with pagination and filtering
exports.getCourses = async (req, res) => {
    try {
        const { 
            page = 1, 
            limit = 10, 
            status, 
            instructor, 
            start_date, 
            end_date 
        } = req.query;

        const where = {};
        if (status) where.status = status;
        if (instructor) where.instructor = { [Op.like]: `%${instructor}%` };
        if (start_date && end_date) {
            where.start_date = { [Op.gte]: new Date(start_date) };
            where.end_date = { [Op.lte]: new Date(end_date) };
        }

        const { count, rows } = await Course.findAndCountAll({
            where,
            limit: parseInt(limit),
            offset: (page - 1) * limit,
            order: [['created_at', 'DESC']],
            include: [{
                model: CourseMaterial,
                as: 'materials'
            }]
        });

        res.json({
            success: true,
            data: rows,
            total: count,
            currentPage: parseInt(page),
            totalPages: Math.ceil(count / limit)
        });
    } catch (error) {
        console.error('Error getting courses:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi lấy danh sách khóa học'
        });
    }
};

// Get course by ID
exports.getCourseById = async (req, res) => {
    try {
        const course = await Course.findByPk(req.params.id, {
            include: [{
                model: CourseMaterial,
                as: 'materials'
            }]
        });

        if (!course) {
            return res.status(404).json({
                success: false,
                message: 'Không tìm thấy khóa học'
            });
        }

        res.json({
            success: true,
            data: course
        });
    } catch (error) {
        console.error('Error getting course:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi lấy thông tin khóa học'
        });
    }
};

// Create new course
exports.createCourse = async (req, res) => {
    try {
        const {
            course_code,
            course_name,
            instructor,
            location,
            start_date,
            end_date,
            max_students,
            course_fee,
            description
        } = req.body;

        // Check if course code already exists
        const existingCourse = await Course.findOne({ where: { course_code } });
        if (existingCourse) {
            return res.status(400).json({
                success: false,
                message: 'Mã khóa học đã tồn tại'
            });
        }

        const course = await Course.create({
            course_code,
            course_name,
            instructor,
            location,
            start_date,
            end_date,
            max_students,
            course_fee,
            description,
            status: 'upcoming',
            current_students: 0
        });

        // Handle file uploads
        if (req.files && req.files.materials) {
            const materials = Array.isArray(req.files.materials) 
                ? req.files.materials 
                : [req.files.materials];

            for (const file of materials) {
                const filePath = await uploadFile(file, 'training-materials');
                await CourseMaterial.create({
                    course_id: course.id,
                    file_name: file.name,
                    file_path: filePath,
                    file_size: file.size,
                    file_type: file.mimetype
                });
            }
        }

        res.json({
            success: true,
            message: 'Đã thêm khóa học thành công',
            data: course
        });
    } catch (error) {
        console.error('Error creating course:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi thêm khóa học'
        });
    }
};

// Update course
exports.updateCourse = async (req, res) => {
    try {
        const course = await Course.findByPk(req.params.id);
        if (!course) {
            return res.status(404).json({
                success: false,
                message: 'Không tìm thấy khóa học'
            });
        }

        const {
            course_code,
            course_name,
            instructor,
            location,
            start_date,
            end_date,
            max_students,
            course_fee,
            description
        } = req.body;

        // Check if course code already exists for another course
        if (course_code !== course.course_code) {
            const existingCourse = await Course.findOne({ 
                where: { 
                    course_code,
                    id: { [Op.ne]: course.id }
                }
            });
            if (existingCourse) {
                return res.status(400).json({
                    success: false,
                    message: 'Mã khóa học đã tồn tại'
                });
            }
        }

        await course.update({
            course_code,
            course_name,
            instructor,
            location,
            start_date,
            end_date,
            max_students,
            course_fee,
            description
        });

        // Handle file uploads
        if (req.files && req.files.materials) {
            const materials = Array.isArray(req.files.materials) 
                ? req.files.materials 
                : [req.files.materials];

            for (const file of materials) {
                const filePath = await uploadFile(file, 'training-materials');
                await CourseMaterial.create({
                    course_id: course.id,
                    file_name: file.name,
                    file_path: filePath,
                    file_size: file.size,
                    file_type: file.mimetype
                });
            }
        }

        res.json({
            success: true,
            message: 'Đã cập nhật khóa học thành công',
            data: course
        });
    } catch (error) {
        console.error('Error updating course:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi cập nhật khóa học'
        });
    }
};

// Delete course
exports.deleteCourse = async (req, res) => {
    try {
        const course = await Course.findByPk(req.params.id);
        if (!course) {
            return res.status(404).json({
                success: false,
                message: 'Không tìm thấy khóa học'
            });
        }

        // Check if course has students
        if (course.current_students > 0) {
            return res.status(400).json({
                success: false,
                message: 'Không thể xóa khóa học đã có học viên đăng ký'
            });
        }

        await course.destroy();
        res.json({
            success: true,
            message: 'Đã xóa khóa học thành công'
        });
    } catch (error) {
        console.error('Error deleting course:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi xóa khóa học'
        });
    }
}; 