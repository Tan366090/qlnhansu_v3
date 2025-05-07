// Mobile stats module
const MobileStats = {
    init() {
        console.log('Mobile stats module initialized');
        this.checkMobileView();
        window.addEventListener('resize', () => this.checkMobileView());
    },

    checkMobileView() {
        const isMobile = window.innerWidth <= 768;
        const mobileStats = document.getElementById('mobile-stats');
        const desktopStats = document.getElementById('desktop-stats');

        if (mobileStats && desktopStats) {
            if (isMobile) {
                mobileStats.style.display = 'block';
                desktopStats.style.display = 'none';
                this.loadMobileStats();
            } else {
                mobileStats.style.display = 'none';
                desktopStats.style.display = 'block';
            }
        }
    },

    async loadMobileStats() {
        try {
            const response = await fetch('/api/mobile-stats');
            const data = await response.json();
            this.updateMobileStats(data);
        } catch (error) {
            console.error('Error loading mobile stats:', error);
            NotificationHandler.error('Có lỗi xảy ra khi tải thống kê');
        }
    },

    updateMobileStats(data) {
        const container = document.getElementById('mobile-stats');
        if (!container) return;

        container.innerHTML = `
            <div class="mobile-stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-value">${data.totalEmployees || 0}</div>
                    <div class="stat-label">Tổng nhân viên</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <div class="stat-value">${data.activeEmployees || 0}</div>
                    <div class="stat-label">Đang hoạt động</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div class="stat-value">${data.todayAttendance || '0%'}</div>
                    <div class="stat-label">Chấm công hôm nay</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <div class="stat-value">${data.onLeave || 0}</div>
                    <div class="stat-label">Đang nghỉ phép</div>
                </div>
            </div>
        `;
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    MobileStats.init();
}); 