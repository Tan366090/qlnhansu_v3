import express from 'express';
const router = express.Router();

// Training management routes
router.get('/courses', (req, res) => {
    res.json({ message: 'Get all training courses route' });
});

router.post('/register', (req, res) => {
    res.json({ message: 'Register for training route' });
});

router.get('/history', (req, res) => {
    res.json({ message: 'Training history route' });
});

export default router; 