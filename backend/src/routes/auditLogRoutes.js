import express from 'express';
const router = express.Router();

// Audit log routes
router.get('/', (req, res) => {
    res.json({ message: 'Get all audit logs route' });
});

router.get('/:id', (req, res) => {
    res.json({ message: 'Get audit log by id route' });
});

router.get('/user/:userId', (req, res) => {
    res.json({ message: 'Get audit logs by user route' });
});

export default router; 