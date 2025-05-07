const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
    res.json([{ id: 1, employeeId: 1, amount: 10000000, month: '2024-04' }]);
});

module.exports = router; 