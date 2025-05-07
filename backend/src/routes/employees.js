const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const multer = require('multer');
const path = require('path');

// Cấu hình multer cho upload file
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, 'uploads/');
    },
    filename: (req, file, cb) => {
        cb(null, Date.now() + path.extname(file.originalname));
    }
});

const upload = multer({ storage });

// Lấy danh sách nhân viên
router.get('/', async (req, res) => {
    try {
        const [employees] = await pool.query(`
            SELECT e.*, d.name as department_name, p.name as position_name 
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            ORDER BY e.id DESC
        `);
        res.json(employees);
    } catch (error) {
        console.error('Get employees error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy thông tin nhân viên
router.get('/:id', async (req, res) => {
    try {
        const [employees] = await pool.query(`
            SELECT e.*, d.name as department_name, p.name as position_name 
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.id = ?
        `, [req.params.id]);
        
        if (employees.length === 0) {
            return res.status(404).json({ error: 'Employee not found' });
        }
        
        res.json(employees[0]);
    } catch (error) {
        console.error('Get employee error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thêm nhân viên mới
router.post('/', upload.single('avatar'), async (req, res) => {
    try {
        const {
            name,
            email,
            phone,
            address,
            department_id,
            position_id,
            salary,
            join_date
        } = req.body;
        
        const avatar = req.file ? req.file.filename : null;
        
        const [result] = await pool.query(`
            INSERT INTO employees (
                name, email, phone, address, department_id, 
                position_id, salary, join_date, avatar
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        `, [
            name, email, phone, address, department_id,
            position_id, salary, join_date, avatar
        ]);
        
        res.status(201).json({
            id: result.insertId,
            message: 'Employee created successfully'
        });
    } catch (error) {
        console.error('Create employee error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Cập nhật thông tin nhân viên
router.put('/:id', upload.single('avatar'), async (req, res) => {
    try {
        const {
            name,
            email,
            phone,
            address,
            department_id,
            position_id,
            salary,
            status
        } = req.body;
        
        const avatar = req.file ? req.file.filename : null;
        
        await pool.query(`
            UPDATE employees 
            SET name = ?, email = ?, phone = ?, address = ?,
                department_id = ?, position_id = ?, salary = ?,
                status = ?, avatar = COALESCE(?, avatar)
            WHERE id = ?
        `, [
            name, email, phone, address,
            department_id, position_id, salary,
            status, avatar, req.params.id
        ]);
        
        res.json({ message: 'Employee updated successfully' });
    } catch (error) {
        console.error('Update employee error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Xóa nhân viên
router.delete('/:id', async (req, res) => {
    try {
        await pool.query('DELETE FROM employees WHERE id = ?', [req.params.id]);
        res.json({ message: 'Employee deleted successfully' });
    } catch (error) {
        console.error('Delete employee error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Tìm kiếm nhân viên
router.get('/search/:keyword', async (req, res) => {
    try {
        const keyword = `%${req.params.keyword}%`;
        const [employees] = await pool.query(`
            SELECT e.*, d.name as department_name, p.name as position_name 
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.name LIKE ? OR e.email LIKE ? OR e.phone LIKE ?
        `, [keyword, keyword, keyword]);
        
        res.json(employees);
    } catch (error) {
        console.error('Search employees error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router; 