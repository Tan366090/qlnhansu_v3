import express from 'express';
const router = express.Router();

// Document management routes
router.get('/', (req, res) => {
    res.json({ message: 'Get all documents route' });
});

router.post('/upload', (req, res) => {
    res.json({ message: 'Upload document route' });
});

router.get('/:id', (req, res) => {
    res.json({ message: 'Get document by id route' });
});

router.delete('/:id', (req, res) => {
    res.json({ message: 'Delete document route' });
});

export default router; 