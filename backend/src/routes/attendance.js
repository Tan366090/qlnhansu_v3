const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
    res.json([{ id: 1, employeeId: 1, date: '2024-04-24', status: 'present' }]);
});

module.exports = router; 