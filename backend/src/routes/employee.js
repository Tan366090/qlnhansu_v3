const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
    res.json([{ id: 1, name: 'Nguyễn Văn A' }]);
});

module.exports = router; 