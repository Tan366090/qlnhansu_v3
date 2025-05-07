const express = require('express');
const router = express.Router();
const db = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

// Phân tích hiệu suất nhân viên
router.get('/performance', authenticateToken, async (req, res) => {
    try {
        // Lấy dữ liệu hiệu suất từ bảng evaluations và kpi
        const [evaluations] = await db.query(`
            SELECT 
                e.employee_id,
                AVG(e.performance_score) as avg_score,
                COUNT(e.id) as evaluation_count,
                k.metric_name,
                AVG(k.actual_value) as avg_kpi
            FROM evaluations e
            LEFT JOIN kpi k ON e.employee_id = k.employee_id
            GROUP BY e.employee_id, k.metric_name
            ORDER BY avg_score DESC
        `);

        // Tính toán xu hướng hiệu suất
        const performanceTrends = evaluations.reduce((acc, curr) => {
            if (!acc[curr.employee_id]) {
                acc[curr.employee_id] = {
                    employee_id: curr.employee_id,
                    avg_score: curr.avg_score,
                    evaluation_count: curr.evaluation_count,
                    kpis: []
                };
            }
            if (curr.metric_name) {
                acc[curr.employee_id].kpis.push({
                    metric_name: curr.metric_name,
                    avg_value: curr.avg_kpi
                });
            }
            return acc;
        }, {});

        // Tạo insights
        const insights = [];
        Object.values(performanceTrends).forEach(employee => {
            if (employee.avg_score >= 4) {
                insights.push(`Nhân viên ${employee.employee_id} có hiệu suất xuất sắc`);
            } else if (employee.avg_score >= 3) {
                insights.push(`Nhân viên ${employee.employee_id} có hiệu suất tốt`);
            } else {
                insights.push(`Nhân viên ${employee.employee_id} cần cải thiện hiệu suất`);
            }
        });

        res.json({
            labels: Object.keys(performanceTrends),
            values: Object.values(performanceTrends).map(e => e.avg_score),
            insights
        });
    } catch (error) {
        console.error('Lỗi phân tích hiệu suất:', error);
        res.status(500).json({ error: 'Lỗi phân tích hiệu suất' });
    }
});

// Phân tích xu hướng nghỉ việc
router.get('/turnover', authenticateToken, async (req, res) => {
    try {
        // Lấy dữ liệu nghỉ việc từ bảng employees
        const [turnoverData] = await db.query(`
            SELECT 
                DATE_FORMAT(updated_at, '%Y-%m') as month,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as turnover_count,
                COUNT(*) as total_employees
            FROM employees
            GROUP BY DATE_FORMAT(updated_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        `);

        // Tính toán tỷ lệ nghỉ việc
        const turnoverRates = turnoverData.map(data => 
            (data.turnover_count / data.total_employees) * 100
        );

        // Tạo insights
        const insights = [];
        const avgTurnoverRate = turnoverRates.reduce((a, b) => a + b, 0) / turnoverRates.length;
        if (avgTurnoverRate > 10) {
            insights.push('Tỷ lệ nghỉ việc cao, cần xem xét nguyên nhân');
        } else if (avgTurnoverRate > 5) {
            insights.push('Tỷ lệ nghỉ việc ở mức trung bình');
        } else {
            insights.push('Tỷ lệ nghỉ việc thấp, nhân viên ổn định');
        }

        res.json({
            turnoverRates,
            insights
        });
    } catch (error) {
        console.error('Lỗi phân tích xu hướng nghỉ việc:', error);
        res.status(500).json({ error: 'Lỗi phân tích xu hướng nghỉ việc' });
    }
});

// Phân tích nhu cầu đào tạo
router.get('/training', authenticateToken, async (req, res) => {
    try {
        // Lấy dữ liệu đào tạo từ bảng employee_trainings
        const [trainingData] = await db.query(`
            SELECT 
                t.training_id,
                t.name as training_name,
                COUNT(et.id) as registration_count,
                AVG(CASE WHEN et.status = 'completed' THEN 1 ELSE 0 END) as completion_rate
            FROM trainings t
            LEFT JOIN employee_trainings et ON t.id = et.training_id
            GROUP BY t.id
            ORDER BY registration_count DESC
        `);

        // Tính toán nhu cầu đào tạo
        const trainingNeeds = trainingData.map(data => ({
            training_name: data.training_name,
            demand: data.registration_count,
            effectiveness: data.completion_rate * 100
        }));

        // Tạo insights
        const insights = [];
        trainingNeeds.forEach(need => {
            if (need.demand > 10) {
                insights.push(`Nhu cầu đào tạo ${need.training_name} cao`);
            }
            if (need.effectiveness < 50) {
                insights.push(`Hiệu quả đào tạo ${need.training_name} thấp, cần cải thiện`);
            }
        });

        res.json({
            trainingNeeds: trainingNeeds.map(n => n.demand),
            insights
        });
    } catch (error) {
        console.error('Lỗi phân tích nhu cầu đào tạo:', error);
        res.status(500).json({ error: 'Lỗi phân tích nhu cầu đào tạo' });
    }
});

// Phân tích tỷ lệ nghỉ phép
router.get('/leave', authenticateToken, async (req, res) => {
    try {
        // Lấy dữ liệu nghỉ phép từ bảng leaves
        const [leaveData] = await db.query(`
            SELECT 
                DATE_FORMAT(start_date, '%Y-%m') as month,
                COUNT(*) as leave_count,
                SUM(DATEDIFF(end_date, start_date) + 1) as total_days
            FROM leaves
            WHERE status = 'approved'
            GROUP BY DATE_FORMAT(start_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        `);

        // Tính toán tỷ lệ nghỉ phép
        const leaveRates = leaveData.map(data => 
            data.total_days / 30 // Giả sử mỗi tháng có 30 ngày làm việc
        );

        // Tạo insights
        const insights = [];
        const avgLeaveRate = leaveRates.reduce((a, b) => a + b, 0) / leaveRates.length;
        if (avgLeaveRate > 0.1) {
            insights.push('Tỷ lệ nghỉ phép cao, cần xem xét nguyên nhân');
        } else if (avgLeaveRate > 0.05) {
            insights.push('Tỷ lệ nghỉ phép ở mức trung bình');
        } else {
            insights.push('Tỷ lệ nghỉ phép thấp, nhân viên làm việc ổn định');
        }

        res.json({
            leaveRates,
            insights
        });
    } catch (error) {
        console.error('Lỗi phân tích tỷ lệ nghỉ phép:', error);
        res.status(500).json({ error: 'Lỗi phân tích tỷ lệ nghỉ phép' });
    }
});

// Phân tích tâm lý nhân viên
router.get('/sentiment', authenticateToken, async (req, res) => {
    try {
        // Lấy dữ liệu phản hồi từ bảng employee_feedback
        const [feedbackData] = await db.query(`
            SELECT 
                sentiment,
                COUNT(*) as count
            FROM employee_feedback
            GROUP BY sentiment
        `);

        // Tính toán tỷ lệ tâm lý
        const total = feedbackData.reduce((sum, item) => sum + item.count, 0);
        const sentimentData = {
            positive: 0,
            neutral: 0,
            negative: 0
        };

        feedbackData.forEach(item => {
            switch(item.sentiment) {
                case 'positive':
                    sentimentData.positive = (item.count / total) * 100;
                    break;
                case 'neutral':
                    sentimentData.neutral = (item.count / total) * 100;
                    break;
                case 'negative':
                    sentimentData.negative = (item.count / total) * 100;
                    break;
            }
        });

        // Tạo insights
        const insights = [];
        if (sentimentData.positive > 70) {
            insights.push('Tâm lý nhân viên tích cực');
        } else if (sentimentData.negative > 30) {
            insights.push('Cần cải thiện tâm lý nhân viên');
        }

        res.json({
            ...sentimentData,
            insights
        });
    } catch (error) {
        console.error('Lỗi phân tích tâm lý:', error);
        res.status(500).json({ error: 'Lỗi phân tích tâm lý' });
    }
});

module.exports = router; 