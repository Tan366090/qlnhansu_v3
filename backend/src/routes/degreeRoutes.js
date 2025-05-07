import express from 'express';
const router = express.Router();

// Degree management routes
router.get('/', (req, res) => {
    res.json({ message: 'Get all degrees route' });
});

router.post('/add', (req, res) => {
    res.json({ message: 'Add degree route' });
});

router.put('/:id', (req, res) => {
    res.json({ message: 'Update degree route' });
});

router.delete('/:id', (req, res) => {
    res.json({ message: 'Delete degree route' });
});

export default router; 