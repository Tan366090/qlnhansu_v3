const express = require('express');
const router = express.Router();

// Import các route modules
const authRoutes = require('./auth');
const employeeRoutes = require('./employee');
const departmentRoutes = require('./department');
const trainingRoutes = require('./training');
const attendanceRoutes = require('./attendance');
const leaveRoutes = require('./leave');
const salaryRoutes = require('./salary');
const performanceRoutes = require('./performance');

// Sử dụng các routes
router.use('/auth', authRoutes);
router.use('/employees', employeeRoutes);
router.use('/departments', departmentRoutes);
router.use('/trainings', trainingRoutes);
router.use('/attendances', attendanceRoutes);
router.use('/leaves', leaveRoutes);
router.use('/salaries', salaryRoutes);
router.use('/performances', performanceRoutes);

module.exports = router; 