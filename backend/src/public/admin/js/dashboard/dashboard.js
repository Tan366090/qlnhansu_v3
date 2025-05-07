import { CommonUtils } from '../utils/common.js';
import { APIUtils } from '../utils/api.js';

export class Dashboard {
    constructor() {
        this.charts = {};
        this.data = {
            employees: [],
            departments: [],
            performances: [],
            payroll: [],
            leaves: []
        };
    }

    async init() {
        try {
            await this.loadData();
            this.initCharts();
            this.updateUI();
            this.setupEventListeners();
        } catch (error) {
            console.error('Dashboard initialization error:', error);
            CommonUtils.showNotification('Không thể khởi tạo dashboard', 'error');
        }
    }

    async loadData() {
        try {
            CommonUtils.showLoading();
            
            // Load all data in parallel
            const [
                employees,
                departments,
                performances,
                payroll,
                leaves
            ] = await Promise.all([
                APIUtils.get('/api/employees'),
                APIUtils.get('/api/departments'),
                APIUtils.get('/api/performances'),
                APIUtils.get('/api/payroll'),
                APIUtils.get('/api/leaves')
            ]);

            this.data = {
                employees,
                departments,
                performances,
                payroll,
                leaves
            };

            CommonUtils.hideLoading();
        } catch (error) {
            CommonUtils.hideLoading();
            console.error('Error loading dashboard data:', error);
            CommonUtils.showNotification('Không thể tải dữ liệu dashboard', 'error');
        }
    }

    updateUI() {
        this.updateMetrics();
        this.updateCharts();
        this.updateRecentEmployees();
    }

    updateMetrics() {
        const { employees, performances, payroll, leaves } = this.data;

        // Update employee metrics
        document.getElementById('totalEmployees').textContent = employees.length;
        document.getElementById('activeEmployees').textContent = 
            employees.filter(e => e.status === 'active').length;
        document.getElementById('inactiveEmployees').textContent = 
            employees.filter(e => e.status === 'inactive').length;

        // Update performance metrics
        const avgPerformance = performances.reduce((acc, curr) => acc + curr.rating, 0) / performances.length;
        document.getElementById('avgPerformance').textContent = avgPerformance.toFixed(1);

        // Update payroll metrics
        const totalSalary = payroll.reduce((acc, curr) => acc + curr.amount, 0);
        document.getElementById('totalSalary').textContent = CommonUtils.formatCurrency(totalSalary);

        // Update leave metrics
        document.getElementById('pendingLeaves').textContent = 
            leaves.filter(l => l.status === 'pending').length;
    }

    updateCharts() {
        this.updateAttendanceChart();
        this.updateDepartmentChart();
    }

    processAttendanceData() {
        const { employees, performances } = this.data;
        const attendanceData = {
            present: 0,
            absent: 0,
            late: 0
        };

        performances.forEach(perf => {
            if (perf.attendance === 'present') attendanceData.present++;
            else if (perf.attendance === 'absent') attendanceData.absent++;
            else if (perf.attendance === 'late') attendanceData.late++;
        });

        return attendanceData;
    }

    processDepartmentData() {
        const { employees, departments } = this.data;
        const departmentData = {};

        departments.forEach(dept => {
            departmentData[dept.name] = employees.filter(e => e.departmentId === dept.id).length;
        });

        return departmentData;
    }

    updateAttendanceChart() {
        const attendanceData = this.processAttendanceData();
        const ctx = document.getElementById('attendanceChart').getContext('2d');

        if (this.charts.attendance) {
            this.charts.attendance.destroy();
        }

        this.charts.attendance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Có mặt', 'Vắng mặt', 'Đi muộn'],
                datasets: [{
                    data: [attendanceData.present, attendanceData.absent, attendanceData.late],
                    backgroundColor: ['#4CAF50', '#f44336', '#FFC107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    updateDepartmentChart() {
        const departmentData = this.processDepartmentData();
        const ctx = document.getElementById('departmentChart').getContext('2d');

        if (this.charts.department) {
            this.charts.department.destroy();
        }

        this.charts.department = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(departmentData),
                datasets: [{
                    label: 'Số nhân viên',
                    data: Object.values(departmentData),
                    backgroundColor: '#2196F3'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    updateRecentEmployees() {
        const { employees } = this.data;
        const recentEmployees = employees
            .sort((a, b) => new Date(b.joinDate) - new Date(a.joinDate))
            .slice(0, 5);

        const container = document.getElementById('recentEmployees');
        container.innerHTML = '';

        recentEmployees.forEach(employee => {
            const div = document.createElement('div');
            div.className = 'recent-employee';
            div.innerHTML = `
                <img src="${employee.avatar || '/images/default-avatar.png'}" alt="${employee.name}">
                <div>
                    <h4>${employee.name}</h4>
                    <p>${employee.position}</p>
                </div>
            `;
            container.appendChild(div);
        });
    }

    initCharts() {
        // Initialize empty charts
        this.charts = {
            attendance: null,
            department: null
        };
    }

    setupEventListeners() {
        // Comment out dark mode toggle
        /*
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
            });
        }
        */

        // Attendance period change
        const attendancePeriod = document.getElementById('attendancePeriod');
        if (attendancePeriod) {
            attendancePeriod.addEventListener('change', () => {
                this.updateAttendanceChart();
            });
        }

        // Auto refresh data every 5 minutes
        setInterval(() => {
            this.loadData();
        }, 5 * 60 * 1000);
    }
} 