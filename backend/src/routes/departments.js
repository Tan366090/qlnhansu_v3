const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');

// Lấy danh sách phòng ban
router.get('/', async (req, res) => {
    try {
        const [departments] = await pool.query(`
            SELECT d.*, COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            GROUP BY d.id
            ORDER BY d.name
        `);
        res.json(departments);
    } catch (error) {
        console.error('Get departments error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy thông tin phòng ban
router.get('/:id', async (req, res) => {
    try {
        const [departments] = await pool.query(`
            SELECT d.*, COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            WHERE d.id = ?
            GROUP BY d.id
        `, [req.params.id]);
        
        if (departments.length === 0) {
            return res.status(404).json({ error: 'Department not found' });
        }
        
        res.json(departments[0]);
    } catch (error) {
        console.error('Get department error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thêm phòng ban mới
router.post('/', async (req, res) => {
    try {
        const { name, description, manager_id } = req.body;
        
        const [result] = await pool.query(`
            INSERT INTO departments (name, description, manager_id)
            VALUES (?, ?, ?)
        `, [name, description, manager_id]);
        
        res.status(201).json({
            id: result.insertId,
            message: 'Department created successfully'
        });
    } catch (error) {
        console.error('Create department error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Cập nhật thông tin phòng ban
router.put('/:id', async (req, res) => {
    try {
        const { name, description, manager_id } = req.body;
        
        await pool.query(`
            UPDATE departments 
            SET name = ?, description = ?, manager_id = ?
            WHERE id = ?
        `, [name, description, manager_id, req.params.id]);
        
        res.json({ message: 'Department updated successfully' });
    } catch (error) {
        console.error('Update department error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Xóa phòng ban
router.delete('/:id', async (req, res) => {
    try {
        // Kiểm tra xem phòng ban có nhân viên không
        const [employees] = await pool.query(
            'SELECT COUNT(*) as count FROM employees WHERE department_id = ?',
            [req.params.id]
        );
        
        if (employees[0].count > 0) {
            return res.status(400).json({ 
                error: 'Cannot delete department with employees' 
            });
        }
        
        await pool.query('DELETE FROM departments WHERE id = ?', [req.params.id]);
        res.json({ message: 'Department deleted successfully' });
    } catch (error) {
        console.error('Delete department error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy danh sách nhân viên trong phòng ban
router.get('/:id/employees', async (req, res) => {
    try {
        const [employees] = await pool.query(`
            SELECT e.*, p.name as position_name
            FROM employees e
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.department_id = ?
            ORDER BY e.name
        `, [req.params.id]);
        
        res.json(employees);
    } catch (error) {
        console.error('Get department employees error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router; 