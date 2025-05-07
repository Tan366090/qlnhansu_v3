const express = require('express');
const cors = require('cors');
const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Routes
const analysisRoutes = require('./routes/analysis');
const userRoutes = require('./routes/user');

app.use('/api/analysis', analysisRoutes);
app.use('/api/user', userRoutes);

// ... existing code ... 