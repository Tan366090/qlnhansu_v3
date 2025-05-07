import express from 'express';
const router = express.Router();

// Leave management routes
router.post('/request', (req, res) => {
    res.json({ message: 'Request leave route' });
});

router.get('/history', (req, res) => {
    res.json({ message: 'Get leave history route' });
});

router.put('/approve/:id', (req, res) => {
    res.json({ message: 'Approve leave request route' });
});

router.put('/reject/:id', (req, res) => {
    res.json({ message: 'Reject leave request route' });
});

export default router; 