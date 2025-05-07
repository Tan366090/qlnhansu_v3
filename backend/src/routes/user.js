const express = require('express');
const router = express.Router();
const db = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

// Lấy thông tin profile của user
router.get('/profile', authenticateToken, async (req, res) => {
    try {
        const userId = req.user.id;
        
        // Lấy thông tin user từ bảng employees
        const [user] = await db.query(`
            SELECT 
                e.id,
                e.employee_code,
                e.full_name,
                e.email,
                e.phone,
                e.address,
                e.birth_date,
                e.gender,
                e.status,
                d.name as department_name,
                p.name as position_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.id = ?
        `, [userId]);

        if (!user) {
            return res.status(404).json({ error: 'User not found' });
        }

        res.json({
            id: user.id,
            employeeCode: user.employee_code,
            fullName: user.full_name,
            email: user.email,
            phone: user.phone,
            address: user.address,
            birthDate: user.birth_date,
            gender: user.gender,
            status: user.status,
            department: user.department_name,
            position: user.position_name
        });
    } catch (error) {
        console.error('Error fetching user profile:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router; 