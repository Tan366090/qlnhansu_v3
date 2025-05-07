const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
    res.json([{ id: 1, name: 'Phòng IT' }]);
});

module.exports = router; 