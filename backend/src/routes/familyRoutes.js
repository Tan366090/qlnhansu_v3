import express from 'express';
const router = express.Router();

// Family information routes
router.get('/:employeeId', (req, res) => {
    res.json({ message: 'Get family information route' });
});

router.post('/add', (req, res) => {
    res.json({ message: 'Add family member route' });
});

router.put('/:id', (req, res) => {
    res.json({ message: 'Update family member route' });
});

router.delete('/:id', (req, res) => {
    res.json({ message: 'Delete family member route' });
});

export default router; 