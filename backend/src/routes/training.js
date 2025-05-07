const express = require('express');
const router = express.Router();

// GET /api/trainings/courses
router.get('/courses', (req, res) => {
    res.json([
        {
            id: 1,
            name: 'Khóa học Node.js cơ bản',
            type: 'Technical',
            duration: '2 tuần',
            startDate: '2024-05-01',
            endDate: '2024-05-15',
            instructor: 'Nguyễn Văn A',
            location: 'Phòng 101',
            status: 'planned'
        }
    ]);
});

// GET /api/trainings/registrations
router.get('/registrations', (req, res) => {
    res.json([
        {
            id: 1,
            employeeId: 1,
            courseId: 1,
            registrationDate: '2024-04-20',
            status: 'registered'
        }
    ]);
});

// GET /api/trainings/evaluations
router.get('/evaluations', (req, res) => {
    res.json([
        {
            id: 1,
            registrationId: 1,
            result: 'excellent',
            score: 95,
            evaluator: 'Nguyễn Văn A',
            comments: 'Học viên xuất sắc'
        }
    ]);
});

// GET /api/trainings/reports
router.get('/reports', (req, res) => {
    res.json({
        totalCourses: 1,
        totalEmployees: 1,
        completionRate: 100,
        averageScore: 95
    });
});

module.exports = router; 