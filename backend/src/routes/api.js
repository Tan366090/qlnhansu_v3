const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const XLSX = require('xlsx');
const Papa = require('papaparse');
const AI = require('../Libraries/AI');

// AI Analysis Endpoints
router.get('/ai/hr-trends', async (req, res) => {
    try {
        const trends = await AI.getHRTrends();
        res.json({
            success: true,
            data: trends
        });
    } catch (error) {
        console.error('Error fetching HR trends:', error);
        res.status(500).json({
            success: false,
            error: 'Internal server error'
        });
    }
});

router.get('/ai/sentiment', async (req, res) => {
    try {
        const sentiment = await AI.getSentimentAnalysis();
        res.json({
            success: true,
            data: sentiment
        });
    } catch (error) {
        console.error('Error fetching sentiment analysis:', error);
        res.status(500).json({
            success: false,
            error: 'Internal server error'
        });
    }
});

// Export Endpoints
router.get('/export/hr-trends', async (req, res) => {
    try {
        const format = req.query.format || 'excel';
        const data = await getHRTrendsData();
        
        if (format === 'excel') {
            const workbook = XLSX.utils.book_new();
            const worksheet = XLSX.utils.json_to_sheet(data);
            XLSX.utils.book_append_sheet(workbook, worksheet, 'HR Trends');
            const buffer = XLSX.write(workbook, { type: 'buffer', bookType: 'xlsx' });
            
            res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            res.setHeader('Content-Disposition', 'attachment; filename=hr-trends.xlsx');
            res.send(buffer);
        } else if (format === 'csv') {
            const csv = Papa.unparse(data);
            res.setHeader('Content-Type', 'text/csv');
            res.setHeader('Content-Disposition', 'attachment; filename=hr-trends.csv');
            res.send(csv);
        }
    } catch (error) {
        res.status(500).json({ error: 'Failed to export HR trends' });
    }
});

// Gamification Endpoints
router.get('/gamification/progress', async (req, res) => {
    try {
        const progress = await getUserProgress(req.user.id);
        res.json(progress);
    } catch (error) {
        res.status(500).json({ error: 'Failed to fetch user progress' });
    }
});

router.post('/gamification/save', async (req, res) => {
    try {
        await saveUserProgress(req.user.id, req.body);
        res.json({ success: true });
    } catch (error) {
        res.status(500).json({ error: 'Failed to save user progress' });
    }
});

// Activity Endpoints
router.get('/activities', async (req, res) => {
    try {
        const activities = await getActivities(req.query);
        res.json(activities);
    } catch (error) {
        res.status(500).json({ error: 'Failed to fetch activities' });
    }
});

router.post('/activities/filter', async (req, res) => {
    try {
        const activities = await filterActivities(req.body);
        res.json(activities);
    } catch (error) {
        res.status(500).json({ error: 'Failed to filter activities' });
    }
});

// Notification Endpoints
router.get('/notifications', async (req, res) => {
    try {
        const notifications = await getNotifications(req.user.id);
        res.json(notifications);
    } catch (error) {
        res.status(500).json({ error: 'Failed to fetch notifications' });
    }
});

// Helper functions
async function getHRTrendsData() {
    // Implement actual data fetching logic
    return {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        totalEmployees: [100, 105, 110, 115, 120, 125],
        newEmployees: [5, 8, 6, 7, 9, 8],
        leftEmployees: [2, 3, 4, 2, 3, 4],
        insights: [
            'Tổng số nhân viên tăng 25% trong 6 tháng',
            'Tỷ lệ nghỉ việc ổn định ở mức 3-4%',
            'Số nhân viên mới tăng đều hàng tháng'
        ]
    };
}

async function getSentimentData() {
    // Implement actual data fetching logic
    return {
        positive: 60,
        neutral: 30,
        negative: 10,
        insights: [
            'Tâm lý nhân viên tích cực chiếm đa số',
            'Cần quan tâm đến 10% nhân viên có tâm lý tiêu cực',
            'Tỷ lệ trung tính đang giảm dần'
        ]
    };
}

async function getUserProgress(userId) {
    // Implement actual data fetching logic
    return {
        points: 1000,
        level: 5,
        achievements: [
            { name: 'Hoàn thành 10 nhiệm vụ', date: '2024-01-01' },
            { name: 'Đạt cấp 5', date: '2024-02-01' }
        ]
    };
}

async function saveUserProgress(userId, progress) {
    // Implement actual saving logic
    return true;
}

async function getActivities(filters) {
    // Implement actual data fetching logic
    return [
        {
            type: 'login',
            title: 'Đăng nhập',
            description: 'User đã đăng nhập vào hệ thống',
            timestamp: new Date(),
            user: 'Admin'
        }
    ];
}

async function filterActivities(filters) {
    // Implement actual filtering logic
    return getActivities(filters);
}

async function getNotifications(userId) {
    // Implement actual data fetching logic
    return [
        {
            type: 'info',
            title: 'Thông báo mới',
            message: 'Có 5 nhiệm vụ mới cần xử lý',
            timestamp: new Date()
        }
    ];
}

module.exports = router; 