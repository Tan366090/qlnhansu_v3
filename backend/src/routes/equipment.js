const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const multer = require('multer');
const path = require('path');

// Cấu hình multer cho upload file
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, 'uploads/equipment/');
    },
    filename: (req, file, cb) => {
        cb(null, Date.now() + path.extname(file.originalname));
    }
});

const upload = multer({ storage });

// Lấy danh sách thiết bị
router.get('/', async (req, res) => {
    try {
        const [equipment] = await pool.query(`
            SELECT e.*, d.name as department_name, u.full_name as assigned_to_name
            FROM equipment e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON e.assigned_to = u.id
            ORDER BY e.id DESC
        `);
        res.json(equipment);
    } catch (error) {
        console.error('Get equipment error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy thông tin thiết bị
router.get('/:id', async (req, res) => {
    try {
        const [equipment] = await pool.query(`
            SELECT e.*, d.name as department_name, u.full_name as assigned_to_name
            FROM equipment e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON e.assigned_to = u.id
            WHERE e.id = ?
        `, [req.params.id]);
        
        if (equipment.length === 0) {
            return res.status(404).json({ error: 'Equipment not found' });
        }
        
        res.json(equipment[0]);
    } catch (error) {
        console.error('Get equipment error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thêm thiết bị mới
router.post('/', upload.single('image'), async (req, res) => {
    try {
        const {
            name,
            description,
            serial_number,
            purchase_date,
            warranty_expiry,
            status,
            department_id,
            assigned_to
        } = req.body;
        
        const image = req.file ? req.file.filename : null;
        
        const [result] = await pool.query(`
            INSERT INTO equipment (
                name, description, serial_number, purchase_date,
                warranty_expiry, status, department_id, assigned_to, image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        `, [
            name, description, serial_number, purchase_date,
            warranty_expiry, status, department_id, assigned_to, image
        ]);
        
        res.status(201).json({
            id: result.insertId,
            message: 'Equipment created successfully'
        });
    } catch (error) {
        console.error('Create equipment error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Cập nhật thông tin thiết bị
router.put('/:id', upload.single('image'), async (req, res) => {
    try {
        const {
            name,
            description,
            serial_number,
            purchase_date,
            warranty_expiry,
            status,
            department_id,
            assigned_to
        } = req.body;
        
        const image = req.file ? req.file.filename : null;
        
        await pool.query(`
            UPDATE equipment 
            SET name = ?, description = ?, serial_number = ?,
                purchase_date = ?, warranty_expiry = ?, status = ?,
                department_id = ?, assigned_to = ?, image = COALESCE(?, image)
            WHERE id = ?
        `, [
            name, description, serial_number,
            purchase_date, warranty_expiry, status,
            department_id, assigned_to, image, req.params.id
        ]);
        
        res.json({ message: 'Equipment updated successfully' });
    } catch (error) {
        console.error('Update equipment error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Xóa thiết bị
router.delete('/:id', async (req, res) => {
    try {
        await pool.query('DELETE FROM equipment WHERE id = ?', [req.params.id]);
        res.json({ message: 'Equipment deleted successfully' });
    } catch (error) {
        console.error('Delete equipment error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy lịch sử cấp phát thiết bị
router.get('/:id/assignments', async (req, res) => {
    try {
        const [assignments] = await pool.query(`
            SELECT a.*, u.full_name as assigned_by_name
            FROM equipment_assignments a
            LEFT JOIN users u ON a.assigned_by = u.id
            WHERE a.equipment_id = ?
            ORDER BY a.assigned_date DESC
        `, [req.params.id]);
        
        res.json(assignments);
    } catch (error) {
        console.error('Get equipment assignments error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Cấp phát thiết bị
router.post('/:id/assign', async (req, res) => {
    try {
        const { assigned_to, assigned_by, notes } = req.body;
        
        // Cập nhật trạng thái thiết bị
        await pool.query(`
            UPDATE equipment 
            SET status = 'assigned', assigned_to = ?
            WHERE id = ?
        `, [assigned_to, req.params.id]);
        
        // Thêm vào lịch sử cấp phát
        await pool.query(`
            INSERT INTO equipment_assignments (
                equipment_id, assigned_to, assigned_by, notes
            ) VALUES (?, ?, ?, ?)
        `, [req.params.id, assigned_to, assigned_by, notes]);
        
        res.json({ message: 'Equipment assigned successfully' });
    } catch (error) {
        console.error('Assign equipment error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thu hồi thiết bị
router.post('/:id/return', async (req, res) => {
    try {
        const { returned_by, notes } = req.body;
        
        // Cập nhật trạng thái thiết bị
        await pool.query(`
            UPDATE equipment 
            SET status = 'available', assigned_to = NULL
            WHERE id = ?
        `, [req.params.id]);
        
        // Cập nhật lịch sử cấp phát
        await pool.query(`
            UPDATE equipment_assignments 
            SET returned_date = NOW(), returned_by = ?, return_notes = ?
            WHERE equipment_id = ? AND returned_date IS NULL
        `, [returned_by, notes, req.params.id]);
        
        res.json({ message: 'Equipment returned successfully' });
    } catch (error) {
        console.error('Return equipment error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router; 