const Certificate = require('../../models/employee/certificate.model');
const Employee = require('../../models/employee/employee.model');
const { uploadFile } = require('../../utils/file-upload');
const { Op } = require('sequelize');

// Get all certificates with pagination and filtering
exports.getCertificates = async (req, res) => {
    try {
        const { 
            page = 1, 
            limit = 10, 
            employee_id,
            certificate_type,
            status,
            start_date,
            end_date
        } = req.query;

        const where = {};
        if (employee_id) where.employee_id = employee_id;
        if (certificate_type) where.certificate_type = certificate_type;
        if (status) where.status = status;
        if (start_date && end_date) {
            where.issue_date = { 
                [Op.between]: [new Date(start_date), new Date(end_date)]
            };
        }

        const { count, rows } = await Certificate.findAndCountAll({
            where,
            limit: parseInt(limit),
            offset: (page - 1) * limit,
            order: [['issue_date', 'DESC']],
            include: [{
                model: Employee,
                as: 'employee',
                attributes: ['id', 'employee_code', 'full_name']
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
        console.error('Error getting certificates:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi lấy danh sách bằng cấp'
        });
    }
};

// Get certificate by ID
exports.getCertificateById = async (req, res) => {
    try {
        const certificate = await Certificate.findByPk(req.params.id, {
            include: [{
                model: Employee,
                as: 'employee',
                attributes: ['id', 'employee_code', 'full_name']
            }]
        });

        if (!certificate) {
            return res.status(404).json({
                success: false,
                message: 'Không tìm thấy bằng cấp'
            });
        }

        res.json({
            success: true,
            data: certificate
        });
    } catch (error) {
        console.error('Error getting certificate:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi lấy thông tin bằng cấp'
        });
    }
};

// Create new certificate
exports.createCertificate = async (req, res) => {
    try {
        const {
            employee_id,
            certificate_type,
            issue_date,
            expiry_date,
            issuing_organization,
            certificate_number,
            notes
        } = req.body;

        // Check if employee exists
        const employee = await Employee.findByPk(employee_id);
        if (!employee) {
            return res.status(404).json({
                success: false,
                message: 'Không tìm thấy nhân viên'
            });
        }

        const certificate = await Certificate.create({
            employee_id,
            certificate_type,
            issue_date,
            expiry_date,
            issuing_organization,
            certificate_number,
            notes,
            status: 'active'
        });

        // Handle file upload
        if (req.file) {
            const filePath = await uploadFile(req.file, 'certificates');
            await certificate.update({
                file_path: filePath,
                file_name: req.file.originalname,
                file_size: req.file.size,
                file_type: req.file.mimetype
            });
        }

        res.json({
            success: true,
            message: 'Đã thêm bằng cấp thành công',
            data: certificate
        });
    } catch (error) {
        console.error('Error creating certificate:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi thêm bằng cấp'
        });
    }
};

// Update certificate
exports.updateCertificate = async (req, res) => {
    try {
        const certificate = await Certificate.findByPk(req.params.id);
        if (!certificate) {
            return res.status(404).json({
                success: false,
                message: 'Không tìm thấy bằng cấp'
            });
        }

        const {
            certificate_type,
            issue_date,
            expiry_date,
            issuing_organization,
            certificate_number,
            notes,
            status
        } = req.body;

        await certificate.update({
            certificate_type,
            issue_date,
            expiry_date,
            issuing_organization,
            certificate_number,
            notes,
            status
        });

        // Handle file upload
        if (req.file) {
            const filePath = await uploadFile(req.file, 'certificates');
            await certificate.update({
                file_path: filePath,
                file_name: req.file.originalname,
                file_size: req.file.size,
                file_type: req.file.mimetype
            });
        }

        res.json({
            success: true,
            message: 'Đã cập nhật bằng cấp thành công',
            data: certificate
        });
    } catch (error) {
        console.error('Error updating certificate:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi cập nhật bằng cấp'
        });
    }
};

// Delete certificate
exports.deleteCertificate = async (req, res) => {
    try {
        const certificate = await Certificate.findByPk(req.params.id);
        if (!certificate) {
            return res.status(404).json({
                success: false,
                message: 'Không tìm thấy bằng cấp'
            });
        }

        await certificate.destroy();
        res.json({
            success: true,
            message: 'Đã xóa bằng cấp thành công'
        });
    } catch (error) {
        console.error('Error deleting certificate:', error);
        res.status(500).json({
            success: false,
            message: 'Có lỗi xảy ra khi xóa bằng cấp'
        });
    }
}; 