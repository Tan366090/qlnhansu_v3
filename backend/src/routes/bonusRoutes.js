import express from 'express';
const router = express.Router();

// Bonus management routes
router.get('/', (req, res) => {
    res.json({ message: 'Get all bonuses route' });
});

router.post('/add', (req, res) => {
    res.json({ message: 'Add bonus route' });
});

router.put('/:id', (req, res) => {
    res.json({ message: 'Update bonus route' });
});

router.delete('/:id', (req, res) => {
    res.json({ message: 'Delete bonus route' });
});

export default router; 