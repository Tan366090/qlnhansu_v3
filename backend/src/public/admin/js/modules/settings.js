// Settings Module
class Settings {
    constructor() {
        this.baseUrl = 'http://localhost/qlnhansu_V2/backend/src/public/api/settings';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadGeneralSettings();
        this.loadUserSettings();
        this.loadSecuritySettings();
        this.loadThemeSettings();
        this.loadBackupSettings();
        this.loadAccessControlSettings();
        this.loadNotificationSettings();
    }

    setupEventListeners() {
        // General settings
        document.getElementById('saveGeneralSettingsBtn')?.addEventListener('click', () => this.saveGeneralSettings());
        document.getElementById('resetGeneralSettingsBtn')?.addEventListener('click', () => this.resetGeneralSettings());

        // User settings
        document.getElementById('saveUserSettingsBtn')?.addEventListener('click', () => this.saveUserSettings());
        document.getElementById('resetUserSettingsBtn')?.addEventListener('click', () => this.resetUserSettings());

        // Security settings
        document.getElementById('saveSecuritySettingsBtn')?.addEventListener('click', () => this.saveSecuritySettings());
        document.getElementById('resetSecuritySettingsBtn')?.addEventListener('click', () => this.resetSecuritySettings());

        // Theme settings
        document.getElementById('saveThemeSettingsBtn')?.addEventListener('click', () => this.saveThemeSettings());
        document.getElementById('resetThemeSettingsBtn')?.addEventListener('click', () => this.resetThemeSettings());

        // Backup settings
        document.getElementById('createBackupBtn')?.addEventListener('click', () => this.createBackup());
        document.getElementById('restoreBackupBtn')?.addEventListener('click', () => this.restoreBackup());
        document.getElementById('deleteBackupBtn')?.addEventListener('click', () => this.deleteBackup());

        // Access control settings
        document.getElementById('saveAccessControlBtn')?.addEventListener('click', () => this.saveAccessControl());
        document.getElementById('resetAccessControlBtn')?.addEventListener('click', () => this.resetAccessControl());

        // Notification settings
        document.getElementById('saveNotificationBtn')?.addEventListener('click', () => this.saveNotificationSettings());
    }

    // General settings
    async loadGeneralSettings() {
        try {
            const response = await fetch(`${this.baseUrl}/general`);
            const data = await response.json();
            this.renderGeneralSettings(data);
        } catch (error) {
            console.error('Error loading general settings:', error);
            NotificationUtils.show('Lỗi khi tải cài đặt chung', 'error');
        }
    }

    async saveGeneralSettings(settingsData) {
        try {
            const response = await fetch(`${this.baseUrl}/general`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(settingsData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Lưu cài đặt chung thành công', 'success');
                this.loadGeneralSettings();
                
                // Lưu lịch sử thay đổi
                await this.saveSettingsHistory('general', settingsData);
            }
        } catch (error) {
            console.error('Error saving general settings:', error);
            NotificationUtils.show('Lỗi khi lưu cài đặt chung', 'error');
        }
    }

    async saveSettingsHistory(category, settingsData) {
        try {
            const historyData = {
                category: category,
                settings: settingsData,
                changed_by: this.getCurrentUser(),
                changed_at: new Date().toISOString()
            };

            const response = await fetch(`${this.baseUrl}/history`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(historyData)
            });

            const data = await response.json();
            if (data.success) {
                this.loadSettingsHistory();
            }
        } catch (error) {
            console.error('Error saving settings history:', error);
        }
    }

    async loadSettingsHistory() {
        try {
            const response = await fetch(`${this.baseUrl}/history`);
            const data = await response.json();
            this.renderSettingsHistory(data);
        } catch (error) {
            console.error('Error loading settings history:', error);
        }
    }

    renderSettingsHistory(history) {
        const container = document.getElementById('settingsHistoryTable');
        if (!container) return;

        container.innerHTML = history.map(record => `
            <tr>
                <td>${record.id}</td>
                <td>${record.category}</td>
                <td>${record.changed_by}</td>
                <td>${record.changed_at}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="settings.viewHistoryDetails(${record.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="settings.restoreFromHistory(${record.id})">
                        <i class="fas fa-undo"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    getCurrentUser() {
        // Lấy thông tin người dùng hiện tại từ session hoặc local storage
        return localStorage.getItem('currentUser') || 'Unknown';
    }

    // User settings
    async loadUserSettings() {
        try {
            const response = await fetch(`${this.baseUrl}/user`);
            const data = await response.json();
            this.renderUserSettings(data);
        } catch (error) {
            console.error('Error loading user settings:', error);
            NotificationUtils.show('Lỗi khi tải cài đặt người dùng', 'error');
        }
    }

    async saveUserSettings(settingsData) {
        try {
            const response = await fetch(`${this.baseUrl}/user`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(settingsData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Lưu cài đặt người dùng thành công', 'success');
            }
        } catch (error) {
            console.error('Error saving user settings:', error);
            NotificationUtils.show('Lỗi khi lưu cài đặt người dùng', 'error');
        }
    }

    // Security settings
    async loadSecuritySettings() {
        try {
            const response = await fetch(`${this.baseUrl}/security`);
            const data = await response.json();
            this.renderSecuritySettings(data);
        } catch (error) {
            console.error('Error loading security settings:', error);
            NotificationUtils.show('Lỗi khi tải cài đặt bảo mật', 'error');
        }
    }

    async saveSecuritySettings(settingsData) {
        try {
            const response = await fetch(`${this.baseUrl}/security`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(settingsData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Lưu cài đặt bảo mật thành công', 'success');
            }
        } catch (error) {
            console.error('Error saving security settings:', error);
            NotificationUtils.show('Lỗi khi lưu cài đặt bảo mật', 'error');
        }
    }

    // Theme settings
    async loadThemeSettings() {
        try {
            const response = await fetch(`${this.baseUrl}/theme`);
            const data = await response.json();
            this.renderThemeSettings(data);
        } catch (error) {
            console.error('Error loading theme settings:', error);
            NotificationUtils.show('Lỗi khi tải cài đặt giao diện', 'error');
        }
    }

    async saveThemeSettings(settingsData) {
        try {
            const response = await fetch(`${this.baseUrl}/theme`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(settingsData)
            });
            const data = await response.json();
            if (data.success) {
                NotificationUtils.show('Lưu cài đặt giao diện thành công', 'success');
                this.applyThemeSettings(settingsData);
            }
        } catch (error) {
            console.error('Error saving theme settings:', error);
            NotificationUtils.show('Lỗi khi lưu cài đặt giao diện', 'error');
        }
    }

    // Rendering methods
    renderGeneralSettings(settings) {
        const container = document.getElementById('generalSettingsForm');
        if (!container) return;

        container.innerHTML = `
            <div class="form-group">
                <label for="companyName">Tên công ty</label>
                <input type="text" class="form-control" id="companyName" value="${settings.company_name}">
            </div>
            <div class="form-group">
                <label for="companyAddress">Địa chỉ</label>
                <input type="text" class="form-control" id="companyAddress" value="${settings.company_address}">
            </div>
            <div class="form-group">
                <label for="companyPhone">Số điện thoại</label>
                <input type="text" class="form-control" id="companyPhone" value="${settings.company_phone}">
            </div>
            <div class="form-group">
                <label for="companyEmail">Email</label>
                <input type="email" class="form-control" id="companyEmail" value="${settings.company_email}">
            </div>
        `;
    }

    renderUserSettings(settings) {
        const container = document.getElementById('userSettingsForm');
        if (!container) return;

        container.innerHTML = `
            <div class="form-group">
                <label for="defaultLanguage">Ngôn ngữ mặc định</label>
                <select class="form-control" id="defaultLanguage">
                    <option value="vi" ${settings.default_language === 'vi' ? 'selected' : ''}>Tiếng Việt</option>
                    <option value="en" ${settings.default_language === 'en' ? 'selected' : ''}>English</option>
                </select>
            </div>
            <div class="form-group">
                <label for="timezone">Múi giờ</label>
                <select class="form-control" id="timezone">
                    <option value="Asia/Ho_Chi_Minh" ${settings.timezone === 'Asia/Ho_Chi_Minh' ? 'selected' : ''}>UTC+7 (Hà Nội)</option>
                    <option value="UTC" ${settings.timezone === 'UTC' ? 'selected' : ''}>UTC</option>
                </select>
            </div>
            <div class="form-group">
                <label for="dateFormat">Định dạng ngày tháng</label>
                <select class="form-control" id="dateFormat">
                    <option value="dd/mm/yyyy" ${settings.date_format === 'dd/mm/yyyy' ? 'selected' : ''}>dd/mm/yyyy</option>
                    <option value="mm/dd/yyyy" ${settings.date_format === 'mm/dd/yyyy' ? 'selected' : ''}>mm/dd/yyyy</option>
                </select>
            </div>
        `;
    }

    renderSecuritySettings(settings) {
        const container = document.getElementById('securitySettingsForm');
        if (!container) return;

        container.innerHTML = `
            <div class="form-group">
                <label for="passwordPolicy">Chính sách mật khẩu</label>
                <select class="form-control" id="passwordPolicy">
                    <option value="basic" ${settings.password_policy === 'basic' ? 'selected' : ''}>Cơ bản</option>
                    <option value="medium" ${settings.password_policy === 'medium' ? 'selected' : ''}>Trung bình</option>
                    <option value="strong" ${settings.password_policy === 'strong' ? 'selected' : ''}>Mạnh</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sessionTimeout">Thời gian hết hạn phiên (phút)</label>
                <input type="number" class="form-control" id="sessionTimeout" value="${settings.session_timeout}">
            </div>
            <div class="form-group">
                <label for="twoFactorAuth">Xác thực hai yếu tố</label>
                <select class="form-control" id="twoFactorAuth">
                    <option value="enabled" ${settings.two_factor_auth === 'enabled' ? 'selected' : ''}>Bật</option>
                    <option value="disabled" ${settings.two_factor_auth === 'disabled' ? 'selected' : ''}>Tắt</option>
                </select>
            </div>
        `;
    }

    renderThemeSettings(settings) {
        const container = document.getElementById('themeSettingsForm');
        if (!container) return;

        container.innerHTML = `
            <div class="form-group">
                <label for="themeMode">Chế độ giao diện</label>
                <select class="form-control" id="themeMode">
                    <option value="light" ${settings.theme_mode === 'light' ? 'selected' : ''}>Sáng</option>
                    <option value="dark" ${settings.theme_mode === 'dark' ? 'selected' : ''}>Tối</option>
                    <option value="auto" ${settings.theme_mode === 'auto' ? 'selected' : ''}>Tự động</option>
                </select>
            </div>
            <div class="form-group">
                <label for="primaryColor">Màu chủ đạo</label>
                <input type="color" class="form-control" id="primaryColor" value="${settings.primary_color}">
            </div>
            <div class="form-group">
                <label for="fontFamily">Font chữ</label>
                <select class="form-control" id="fontFamily">
                    <option value="Arial" ${settings.font_family === 'Arial' ? 'selected' : ''}>Arial</option>
                    <option value="Roboto" ${settings.font_family === 'Roboto' ? 'selected' : ''}>Roboto</option>
                    <option value="Open Sans" ${settings.font_family === 'Open Sans' ? 'selected' : ''}>Open Sans</option>
                </select>
            </div>
        `;
    }

    applyThemeSettings(settings) {
        // Comment out dark mode class removal
        /*
        document.body.classList.remove('light-mode', 'dark-mode');
        */

        // Apply theme mode
        document.body.classList.remove('light-mode', 'dark-mode');
        document.body.classList.add(`${settings.theme_mode}-mode`);

        // Apply primary color
        document.documentElement.style.setProperty('--primary-color', settings.primary_color);

        // Apply font family
        document.body.style.fontFamily = settings.font_family;
    }
}

// Initialize settings module
const settings = new Settings(); 