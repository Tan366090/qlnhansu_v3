import express from 'express';
const router = express.Router();

// Performance management routes
router.get('/evaluations', (req, res) => {
    res.json({ message: 'Get all performance evaluations route' });
});

router.post('/evaluate', (req, res) => {
    res.json({ message: 'Evaluate performance route' });
});

router.get('/metrics', (req, res) => {
    res.json({ message: 'Get performance metrics route' });
});

export default router; 