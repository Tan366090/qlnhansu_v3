const express = require('express');
const router = express.Router();

// GET /api/leaves/remaining-days
router.get('/remaining-days', (req, res) => {
    res.json({
        annual: 12,
        sick: 5,
        unpaid: 0
    });
});

// GET /api/leaves
router.get('/', (req, res) => {
    res.json([
        {
            id: 1,
            employeeId: 1,
            startDate: '2024-04-24',
            endDate: '2024-04-25',
            type: 'annual',
            status: 'pending',
            reason: 'Nghỉ phép năm'
        }
    ]);
});

// GET /api/leaves/:id
router.get('/:id', (req, res) => {
    res.json({
        id: 1,
        employeeId: 1,
        startDate: '2024-04-24',
        endDate: '2024-04-25',
        type: 'annual',
        status: 'pending',
        reason: 'Nghỉ phép năm'
    });
});

// POST /api/leaves
router.post('/', (req, res) => {
    res.json({
        id: 2,
        employeeId: 1,
        startDate: '2024-04-26',
        endDate: '2024-04-27',
        type: 'sick',
        status: 'pending',
        reason: 'Nghỉ ốm'
    });
});

// PUT /api/leaves/:id/approve
router.put('/:id/approve', (req, res) => {
    res.json({
        id: 1,
        status: 'approved'
    });
});

// PUT /api/leaves/:id/reject
router.put('/:id/reject', (req, res) => {
    res.json({
        id: 1,
        status: 'rejected'
    });
});

// PUT /api/leaves/:id/cancel
router.put('/:id/cancel', (req, res) => {
    res.json({
        id: 1,
        status: 'cancelled'
    });
});

module.exports = router; 