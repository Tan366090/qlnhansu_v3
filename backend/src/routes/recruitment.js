const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const multer = require('multer');
const path = require('path');

// Cấu hình multer cho upload file
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, 'uploads/recruitment/');
    },
    filename: (req, file, cb) => {
        cb(null, Date.now() + path.extname(file.originalname));
    }
});

const upload = multer({ storage });

// Lấy danh sách vị trí tuyển dụng
router.get('/positions', async (req, res) => {
    try {
        const [positions] = await pool.query(`
            SELECT p.*, d.name as department_name,
                   COUNT(DISTINCT c.id) as total_candidates
            FROM recruitment_positions p
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN candidates c ON p.id = c.position_id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        `);
        res.json(positions);
    } catch (error) {
        console.error('Get recruitment positions error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thêm vị trí tuyển dụng mới
router.post('/positions', async (req, res) => {
    try {
        const {
            title,
            department_id,
            description,
            requirements,
            responsibilities,
            salary_range,
            deadline
        } = req.body;
        
        const [result] = await pool.query(`
            INSERT INTO recruitment_positions (
                title, department_id, description,
                requirements, responsibilities,
                salary_range, deadline
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        `, [
            title, department_id, description,
            requirements, responsibilities,
            salary_range, deadline
        ]);
        
        res.status(201).json({
            id: result.insertId,
            message: 'Recruitment position created successfully'
        });
    } catch (error) {
        console.error('Create recruitment position error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy danh sách ứng viên
router.get('/candidates', async (req, res) => {
    try {
        const [candidates] = await pool.query(`
            SELECT c.*, p.title as position_title,
                   d.name as department_name
            FROM candidates c
            LEFT JOIN recruitment_positions p ON c.position_id = p.id
            LEFT JOIN departments d ON p.department_id = d.id
            ORDER BY c.created_at DESC
        `);
        res.json(candidates);
    } catch (error) {
        console.error('Get candidates error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thêm ứng viên mới
router.post('/candidates', upload.single('resume'), async (req, res) => {
    try {
        const {
            name,
            email,
            phone,
            position_id,
            experience,
            education,
            skills
        } = req.body;
        
        const resume = req.file ? req.file.filename : null;
        
        const [result] = await pool.query(`
            INSERT INTO candidates (
                name, email, phone, position_id,
                experience, education, skills, resume
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        `, [
            name, email, phone, position_id,
            experience, education, skills, resume
        ]);
        
        res.status(201).json({
            id: result.insertId,
            message: 'Candidate added successfully'
        });
    } catch (error) {
        console.error('Add candidate error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy danh sách phỏng vấn
router.get('/interviews', async (req, res) => {
    try {
        const [interviews] = await pool.query(`
            SELECT i.*, c.name as candidate_name,
                   p.title as position_title,
                   interviewer.full_name as interviewer_name
            FROM interviews i
            LEFT JOIN candidates c ON i.candidate_id = c.id
            LEFT JOIN recruitment_positions p ON c.position_id = p.id
            LEFT JOIN users interviewer ON i.interviewer_id = interviewer.id
            ORDER BY i.interview_date DESC
        `);
        res.json(interviews);
    } catch (error) {
        console.error('Get interviews error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Thêm lịch phỏng vấn
router.post('/interviews', async (req, res) => {
    try {
        const {
            candidate_id,
            interviewer_id,
            interview_date,
            interview_type,
            notes
        } = req.body;
        
        const [result] = await pool.query(`
            INSERT INTO interviews (
                candidate_id, interviewer_id,
                interview_date, interview_type, notes
            ) VALUES (?, ?, ?, ?, ?)
        `, [
            candidate_id, interviewer_id,
            interview_date, interview_type, notes
        ]);
        
        res.status(201).json({
            id: result.insertId,
            message: 'Interview scheduled successfully'
        });
    } catch (error) {
        console.error('Schedule interview error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Cập nhật kết quả phỏng vấn
router.put('/interviews/:id', async (req, res) => {
    try {
        const {
            status,
            feedback,
            rating
        } = req.body;
        
        await pool.query(`
            UPDATE interviews 
            SET status = ?, feedback = ?, rating = ?
            WHERE id = ?
        `, [status, feedback, rating, req.params.id]);
        
        res.json({ message: 'Interview result updated successfully' });
    } catch (error) {
        console.error('Update interview result error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Lấy báo cáo tuyển dụng
router.get('/reports', async (req, res) => {
    try {
        const [report] = await pool.query(`
            SELECT 
                COUNT(DISTINCT p.id) as total_positions,
                COUNT(DISTINCT c.id) as total_candidates,
                COUNT(DISTINCT i.id) as total_interviews,
                COUNT(CASE WHEN c.status = 'hired' THEN 1 END) as total_hired,
                COUNT(CASE WHEN c.status = 'rejected' THEN 1 END) as total_rejected,
                COUNT(CASE WHEN c.status = 'pending' THEN 1 END) as total_pending,
                AVG(i.rating) as avg_interview_rating
            FROM recruitment_positions p
            LEFT JOIN candidates c ON p.id = c.position_id
            LEFT JOIN interviews i ON c.id = i.candidate_id
        `);
        
        res.json(report[0] || {});
    } catch (error) {
        console.error('Get recruitment report error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router; 