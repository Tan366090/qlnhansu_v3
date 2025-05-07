import express from 'express';
const router = express.Router();

// HR management routes
router.get('/employees', (req, res) => {
    res.json({ message: 'Get all employees route' });
});

router.get('/departments', (req, res) => {
    res.json({ message: 'Get all departments route' });
});

router.get('/positions', (req, res) => {
    res.json({ message: 'Get all positions route' });
});

export default router; 