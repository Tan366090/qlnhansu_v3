const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');

// Lấy danh sách đánh giá hiệu suất
router.get('/', async (req, res) => {
    try {
        const [evaluations] = await pool.query(`
            SELECT e.*, emp.name as employee_name, d.name as department_name,
                   evaluator.full_name as evaluator_name
            FROM performance_evaluations e
            LEFT JOIN employees emp ON e.employee_id = emp.id
            LEFT JOIN departments d ON emp.department_id = d.id
            LEFT JOIN users evaluator ON e.evaluator_id = evaluator.id
            ORDER BY e.evaluation_date DESC
        `);
        res.json(evaluations);
    } catch (error) {
        console.error('Get performance evaluations error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy đánh giá hiệu suất của nhân viên
router.get('/employee/:id', async (req, res) => {
    try {
        const [evaluations] = await pool.query(`
            SELECT e.*, evaluator.full_name as evaluator_name
            FROM performance_evaluations e
            LEFT JOIN users evaluator ON e.evaluator_id = evaluator.id
            WHERE e.employee_id = ?
            ORDER BY e.evaluation_date DESC
        `, [req.params.id]);
        
        res.json(evaluations);
    } catch (error) {
        console.error('Get employee evaluations error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thêm đánh giá hiệu suất
router.post('/', async (req, res) => {
    try {
        const {
            employee_id,
            evaluator_id,
            evaluation_date,
            kpi_score,
            quality_score,
            efficiency_score,
            teamwork_score,
            comments
        } = req.body;
        
        const [result] = await pool.query(`
            INSERT INTO performance_evaluations (
                employee_id, evaluator_id, evaluation_date,
                kpi_score, quality_score, efficiency_score,
                teamwork_score, comments
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        `, [
            employee_id, evaluator_id, evaluation_date,
            kpi_score, quality_score, efficiency_score,
            teamwork_score, comments
        ]);
        
        res.status(201).json({
            id: result.insertId,
            message: 'Performance evaluation created successfully'
        });
    } catch (error) {
        console.error('Create performance evaluation error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Cập nhật đánh giá hiệu suất
router.put('/:id', async (req, res) => {
    try {
        const {
            kpi_score,
            quality_score,
            efficiency_score,
            teamwork_score,
            comments
        } = req.body;
        
        await pool.query(`
            UPDATE performance_evaluations 
            SET kpi_score = ?, quality_score = ?,
                efficiency_score = ?, teamwork_score = ?,
                comments = ?
            WHERE id = ?
        `, [
            kpi_score, quality_score,
            efficiency_score, teamwork_score,
            comments, req.params.id
        ]);
        
        res.json({ message: 'Performance evaluation updated successfully' });
    } catch (error) {
        console.error('Update performance evaluation error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy báo cáo hiệu suất theo phòng ban
router.get('/department/:id', async (req, res) => {
    try {
        const [report] = await pool.query(`
            SELECT 
                d.name as department_name,
                COUNT(DISTINCT e.id) as total_employees,
                AVG(pe.kpi_score) as avg_kpi_score,
                AVG(pe.quality_score) as avg_quality_score,
                AVG(pe.efficiency_score) as avg_efficiency_score,
                AVG(pe.teamwork_score) as avg_teamwork_score,
                COUNT(CASE WHEN pe.kpi_score >= 8 THEN 1 END) as high_performers,
                COUNT(CASE WHEN pe.kpi_score < 5 THEN 1 END) as low_performers
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            LEFT JOIN performance_evaluations pe ON e.id = pe.employee_id
            WHERE d.id = ?
            GROUP BY d.id
        `, [req.params.id]);
        
        res.json(report[0] || {});
    } catch (error) {
        console.error('Get department performance report error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy báo cáo hiệu suất tổng thể
router.get('/overall', async (req, res) => {
    try {
        const [report] = await pool.query(`
            SELECT 
                COUNT(DISTINCT e.id) as total_employees,
                AVG(pe.kpi_score) as avg_kpi_score,
                AVG(pe.quality_score) as avg_quality_score,
                AVG(pe.efficiency_score) as avg_efficiency_score,
                AVG(pe.teamwork_score) as avg_teamwork_score,
                COUNT(CASE WHEN pe.kpi_score >= 8 THEN 1 END) as high_performers,
                COUNT(CASE WHEN pe.kpi_score < 5 THEN 1 END) as low_performers,
                COUNT(DISTINCT d.id) as total_departments
            FROM employees e
            LEFT JOIN performance_evaluations pe ON e.id = pe.employee_id
            LEFT JOIN departments d ON e.department_id = d.id
        `);
        
        res.json(report[0] || {});
    } catch (error) {
        console.error('Get overall performance report error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy biểu đồ hiệu suất theo thời gian
router.get('/trends', async (req, res) => {
    try {
        const [trends] = await pool.query(`
            SELECT 
                DATE_FORMAT(evaluation_date, '%Y-%m') as month,
                AVG(kpi_score) as avg_kpi_score,
                AVG(quality_score) as avg_quality_score,
                AVG(efficiency_score) as avg_efficiency_score,
                AVG(teamwork_score) as avg_teamwork_score
            FROM performance_evaluations
            GROUP BY DATE_FORMAT(evaluation_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        `);
        
        res.json(trends);
    } catch (error) {
        console.error('Get performance trends error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router; 