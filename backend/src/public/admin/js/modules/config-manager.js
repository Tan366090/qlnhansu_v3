export class ConfigManager {
    constructor() {
        this.config = {
            systemName: 'HR System',
            language: 'vi',
            theme: 'light'
        };
        this.translations = {
            vi: {
                // Header
                'Admin Dashboard': 'Bảng điều khiển Admin',
                'Search...': 'Tìm kiếm...',
                'Toggle Dark Mode': 'Chuyển chế độ tối',
                'Profile': 'Hồ sơ',
                'Settings': 'Cài đặt',
                'Logout': 'Đăng xuất',
                'Configuration': 'Cấu hình',
                'System Name': 'Tên hệ thống',
                'Language': 'Ngôn ngữ',
                'Save Configuration': 'Lưu cấu hình',
                'Close': 'Đóng',
                
                // Statistics
                'Total Employees': 'Tổng nhân viên',
                'Active Employees': 'Nhân viên đang làm việc',
                'Pending Leaves': 'Đơn nghỉ phép chờ duyệt',
                'Monthly Salary': 'Tổng lương tháng',
                
                // Charts
                'Attendance Trend': 'Xu hướng chấm công',
                'Week': 'Tuần',
                'Month': 'Tháng',
                'Quarter': 'Quý',
                'Update': 'Cập nhật',
                'Export': 'Xuất',
                'Employee Distribution by Department': 'Phân bố nhân viên theo phòng ban',
                
                // Activities
                'Recent Activities': 'Hoạt động gần đây',
                'Notifications': 'Thông báo',
                'Refresh': 'Làm mới',
                
                // Quick Actions
                'Quick Actions': 'Thao tác nhanh',
                'Add Employee': 'Thêm nhân viên',
                'Check Attendance': 'Chấm công',
                'Register Leave': 'Đăng ký nghỉ phép',
                'Calculate Salary': 'Tính lương',
                
                // Work Schedule
                'Work Schedule': 'Lịch làm việc',
                'Date': 'Ngày',
                'Employee': 'Nhân viên',
                'Department': 'Phòng ban',
                'Time': 'Thời gian',
                'Type': 'Loại',
                'Previous': 'Trước',
                'Next': 'Sau',
                
                // Tasks
                'Tasks': 'Công việc',
                'Deadline': 'Hạn chót',
                
                // Weather
                'Hanoi Weather': 'Thời tiết Hà Nội',
                'Loading weather data...': 'Đang tải dữ liệu thời tiết...',
                
                // Chat
                'Team Chat': 'Trò chuyện nhóm',
                'Enter message...': 'Nhập tin nhắn...',
                'Send': 'Gửi',
                
                // Backup
                'Data Backup': 'Sao lưu dữ liệu',
                'Type': 'Loại',
                'Status': 'Trạng thái',
                'Time': 'Thời gian',
                'Performed By': 'Người thực hiện'
            },
            en: {
                // Header
                'Admin Dashboard': 'Admin Dashboard',
                'Search...': 'Search...',
                'Toggle Dark Mode': 'Toggle Dark Mode',
                'Profile': 'Profile',
                'Settings': 'Settings',
                'Logout': 'Logout',
                'Configuration': 'Configuration',
                'System Name': 'System Name',
                'Language': 'Language',
                'Save Configuration': 'Save Configuration',
                'Close': 'Close',
                
                // Statistics
                'Total Employees': 'Total Employees',
                'Active Employees': 'Active Employees',
                'Pending Leaves': 'Pending Leaves',
                'Monthly Salary': 'Monthly Salary',
                
                // Charts
                'Attendance Trend': 'Attendance Trend',
                'Week': 'Week',
                'Month': 'Month',
                'Quarter': 'Quarter',
                'Update': 'Update',
                'Export': 'Export',
                'Employee Distribution by Department': 'Employee Distribution by Department',
                
                // Activities
                'Recent Activities': 'Recent Activities',
                'Notifications': 'Notifications',
                'Refresh': 'Refresh',
                
                // Quick Actions
                'Quick Actions': 'Quick Actions',
                'Add Employee': 'Add Employee',
                'Check Attendance': 'Check Attendance',
                'Register Leave': 'Register Leave',
                'Calculate Salary': 'Calculate Salary',
                
                // Work Schedule
                'Work Schedule': 'Work Schedule',
                'Date': 'Date',
                'Employee': 'Employee',
                'Department': 'Department',
                'Time': 'Time',
                'Type': 'Type',
                'Previous': 'Previous',
                'Next': 'Next',
                
                // Tasks
                'Tasks': 'Tasks',
                'Deadline': 'Deadline',
                
                // Weather
                'Hanoi Weather': 'Hanoi Weather',
                'Loading weather data...': 'Loading weather data...',
                
                // Chat
                'Team Chat': 'Team Chat',
                'Enter message...': 'Enter message...',
                'Send': 'Send',
                
                // Backup
                'Data Backup': 'Data Backup',
                'Type': 'Type',
                'Status': 'Status',
                'Time': 'Time',
                'Performed By': 'Performed By'
            },
            zh: {
                // Header
                'Admin Dashboard': '管理面板',
                'Search...': '搜索...',
                'Toggle Dark Mode': '切换暗黑模式',
                'Profile': '个人资料',
                'Settings': '设置',
                'Logout': '退出',
                'Configuration': '配置',
                'System Name': '系统名称',
                'Language': '语言',
                'Save Configuration': '保存配置',
                'Close': '关闭',
                
                // Statistics
                'Total Employees': '员工总数',
                'Active Employees': '在职员工',
                'Pending Leaves': '待处理请假',
                'Monthly Salary': '月薪总额',
                
                // Charts
                'Attendance Trend': '考勤趋势',
                'Week': '周',
                'Month': '月',
                'Quarter': '季度',
                'Update': '更新',
                'Export': '导出',
                'Employee Distribution by Department': '部门员工分布',
                
                // Activities
                'Recent Activities': '最近活动',
                'Notifications': '通知',
                'Refresh': '刷新',
                
                // Quick Actions
                'Quick Actions': '快捷操作',
                'Add Employee': '添加员工',
                'Check Attendance': '考勤',
                'Register Leave': '请假登记',
                'Calculate Salary': '计算工资',
                
                // Work Schedule
                'Work Schedule': '工作日程',
                'Date': '日期',
                'Employee': '员工',
                'Department': '部门',
                'Time': '时间',
                'Type': '类型',
                'Previous': '上一页',
                'Next': '下一页',
                
                // Tasks
                'Tasks': '任务',
                'Deadline': '截止日期',
                
                // Weather
                'Hanoi Weather': '河内天气',
                'Loading weather data...': '正在加载天气数据...',
                
                // Chat
                'Team Chat': '团队聊天',
                'Enter message...': '输入消息...',
                'Send': '发送',
                
                // Backup
                'Data Backup': '数据备份',
                'Type': '类型',
                'Status': '状态',
                'Time': '时间',
                'Performed By': '执行人'
            }
        };
        this.loadConfig();
    }

    // Load config from localStorage
    loadConfig() {
        const savedConfig = localStorage.getItem('systemConfig');
        if (savedConfig) {
            this.config = JSON.parse(savedConfig);
            this.updateUI();
        }
    }

    // Save config to localStorage
    saveConfig() {
        localStorage.setItem('systemConfig', JSON.stringify(this.config));
        this.updateUI();
        this.showNotification('Cấu hình đã được lưu thành công', 'success');
    }

    // Update UI based on current config
    updateUI() {
        // Update system name
        const systemNameInput = document.querySelector('.config-item input[type="text"]');
        if (systemNameInput) {
            systemNameInput.value = this.config.systemName;
        }

        // Update language select
        const languageSelect = document.querySelector('.config-item select');
        if (languageSelect) {
            languageSelect.value = this.config.language;
        }

        // Update page title
        document.title = `${this.config.systemName} - Admin Dashboard`;

        // Update all translatable elements
        this.updateLanguageContent();
    }

    // Update content based on selected language
    updateLanguageContent() {
        const currentLang = this.config.language;
        const langData = this.translations[currentLang];

        // Update all elements with data-translate attribute
        document.querySelectorAll('[data-translate]').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (langData[key]) {
                element.textContent = langData[key];
            }
        });

        // Update placeholders
        document.querySelectorAll('[data-translate-placeholder]').forEach(element => {
            const key = element.getAttribute('data-translate-placeholder');
            if (langData[key]) {
                element.placeholder = langData[key];
            }
        });

        // Update titles and tooltips
        document.querySelectorAll('[data-translate-title]').forEach(element => {
            const key = element.getAttribute('data-translate-title');
            if (langData[key]) {
                element.title = langData[key];
            }
        });

        // Update aria-labels
        document.querySelectorAll('[data-translate-aria-label]').forEach(element => {
            const key = element.getAttribute('data-translate-aria-label');
            if (langData[key]) {
                element.setAttribute('aria-label', langData[key]);
            }
        });
    }

    // Show notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

        const container = document.getElementById('notificationContainer');
        container.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Handle input changes
    handleInputChange(input, value) {
        if (input.type === 'text') {
            this.config.systemName = value;
        } else if (input.tagName === 'SELECT') {
            this.config.language = value;
            this.updateLanguageContent();
        }
    }

    // Initialize event listeners
    initEventListeners() {
        // System name input
        const systemNameInput = document.querySelector('.config-item input[type="text"]');
        if (systemNameInput) {
            systemNameInput.addEventListener('change', (e) => {
                this.handleInputChange(e.target, e.target.value);
            });
        }

        // Language select
        const languageSelect = document.querySelector('.config-item select');
        if (languageSelect) {
            languageSelect.addEventListener('change', (e) => {
                this.handleInputChange(e.target, e.target.value);
            });
        }

        // Save button
        const saveButton = document.querySelector('.dropdown-item a[onclick="saveConfig()"]');
        if (saveButton) {
            saveButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.saveConfig();
            });
        }

        // Close button
        const closeButton = document.querySelector('.dropdown-item button[onclick="closeConfig()"]');
        if (closeButton) {
            closeButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeConfig();
            });
        }

        // Handle dropdown toggle
        const dropdownToggle = document.querySelector('.dropdown button[data-bs-toggle="dropdown"]');
        if (dropdownToggle) {
            dropdownToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const dropdown = e.target.closest('.dropdown');
                if (dropdown) {
                    const isOpen = dropdown.classList.contains('show');
                    if (isOpen) {
                        dropdown.classList.remove('show');
                    } else {
                        dropdown.classList.add('show');
                    }
                }
            });
        }

        // Ngăn chặn đóng dropdown khi click vào bên trong
        const dropdownMenu = document.querySelector('.dropdown-menu');
        if (dropdownMenu) {
            dropdownMenu.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // Chỉ đóng dropdown khi click bên ngoài
        document.addEventListener('click', (e) => {
            const dropdown = document.querySelector('.dropdown');
            const dropdownToggle = document.querySelector('.dropdown button[data-bs-toggle="dropdown"]');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (dropdown && dropdownToggle && dropdownMenu) {
                const isClickInside = dropdownMenu.contains(e.target) || dropdownToggle.contains(e.target);
                if (!isClickInside) {
                    dropdown.classList.remove('show');
                }
            }
        });
    }

    // Close config dropdown
    closeConfig() {
        const dropdown = document.querySelector('.dropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
        }
    }
} 