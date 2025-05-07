class Gamification {
    constructor() {
        this.points = 0;
        this.level = 1;
        this.achievements = [];
        this.leaderboardContainer = document.querySelector('.leaderboard');
        this.achievementsContainer = document.querySelector('.achievements');
        this.progressContainer = document.querySelector('.progress-container');
        this.setupEventListeners();
        // Không tự động load data để tránh lỗi
        // this.loadData();
        // this.loadUserProgress();
    }

    setupEventListeners() {
        // Lắng nghe các sự kiện để cập nhật điểm
        document.addEventListener('taskCompleted', (e) => this.addPoints(10));
        document.addEventListener('reportSubmitted', (e) => this.addPoints(20));
        document.addEventListener('goalAchieved', (e) => this.addPoints(50));

        // Lắng nghe sự kiện khi người dùng thay đổi khoảng thời gian bảng xếp hạng
        document.querySelectorAll('[data-leaderboard-range]').forEach(button => {
            button.addEventListener('click', (e) => this.handleLeaderboardRangeChange(e));
        });
    }

    async loadData() {
        try {
            // Lấy dữ liệu bảng xếp hạng
            const leaderboardResponse = await fetch('/api/gamification/leaderboard');
            if (!leaderboardResponse.ok) {
                console.warn('Leaderboard API not available');
                return;
            }
            const leaderboardData = await leaderboardResponse.json();

            // Lấy dữ liệu thành tích
            const achievementsResponse = await fetch('/api/gamification/achievements');
            if (!achievementsResponse.ok) {
                console.warn('Achievements API not available');
                return;
            }
            const achievementsData = await achievementsResponse.json();

            // Lấy dữ liệu tiến độ
            const progressResponse = await fetch('/api/gamification/progress');
            if (!progressResponse.ok) {
                console.warn('Progress API not available');
                return;
            }
            const progressData = await progressResponse.json();

            // Hiển thị dữ liệu
            this.displayLeaderboard(leaderboardData);
            this.displayAchievements(achievementsData);
            this.displayProgress(progressData);
        } catch (error) {
            console.warn('Gamification features are not available:', error);
            // Không hiển thị lỗi cho người dùng
        }
    }

    async loadUserProgress() {
        try {
            const response = await fetch('/api/gamification/progress');
            if (response.ok) {
                const data = await response.json();
                this.points = data.points;
                this.level = data.level;
                this.achievements = data.achievements;
                this.updateUI();
            } else {
                console.warn('Progress API not available');
            }
        } catch (error) {
            console.warn('Gamification progress features are not available:', error);
        }
    }

    addPoints(amount) {
        this.points += amount;
        this.checkLevelUp();
        this.checkAchievements();
        this.updateUI();
        this.saveProgress();
    }

    checkLevelUp() {
        const pointsNeeded = this.level * 100;
        if (this.points >= pointsNeeded) {
            this.level++;
            this.showLevelUpNotification();
        }
    }

    checkAchievements() {
        const newAchievements = this.calculateAchievements();
        if (newAchievements.length > 0) {
            this.achievements.push(...newAchievements);
            this.showAchievementNotification(newAchievements);
        }
    }

    calculateAchievements() {
        const newAchievements = [];
        // Logic để tính toán thành tựu mới
        return newAchievements;
    }

    updateUI() {
        const pointsElement = document.querySelector('.gamification-points');
        const levelElement = document.querySelector('.gamification-level');
        const achievementsElement = document.querySelector('.gamification-achievements');

        if (pointsElement) pointsElement.textContent = this.points;
        if (levelElement) levelElement.textContent = this.level;
        if (achievementsElement) {
            achievementsElement.innerHTML = this.achievements.map(achievement => `
                <div class="achievement-item">
                    <i class="fas fa-trophy"></i>
                    <span>${achievement.name}</span>
                </div>
            `).join('');
        }
    }

    async saveProgress() {
        try {
            await fetch('/api/gamification/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    points: this.points,
                    level: this.level,
                    achievements: this.achievements
                })
            });
        } catch (error) {
            console.error('Error saving gamification progress:', error);
        }
    }

    showLevelUpNotification() {
        const notification = document.createElement('div');
        notification.className = 'gamification-notification level-up';
        notification.innerHTML = `
            <i class="fas fa-star"></i>
            <span>Chúc mừng! Bạn đã lên cấp ${this.level}</span>
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    showAchievementNotification(achievements) {
        achievements.forEach(achievement => {
            const notification = document.createElement('div');
            notification.className = 'gamification-notification achievement';
            notification.innerHTML = `
                <i class="fas fa-trophy"></i>
                <span>Thành tựu mới: ${achievement.name}</span>
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        });
    }

    displayLeaderboard(data) {
        if (!this.leaderboardContainer) return;

        const leaderboardHtml = `
            <div class="leaderboard-header">
                <h3>Bảng xếp hạng</h3>
                <div class="time-range">
                    <button class="btn btn-sm ${data.timeRange === 'week' ? 'btn-primary' : 'btn-outline-primary'}" 
                            data-leaderboard-range="week">Tuần</button>
                    <button class="btn btn-sm ${data.timeRange === 'month' ? 'btn-primary' : 'btn-outline-primary'}" 
                            data-leaderboard-range="month">Tháng</button>
                    <button class="btn btn-sm ${data.timeRange === 'all' ? 'btn-primary' : 'btn-outline-primary'}" 
                            data-leaderboard-range="all">Tất cả</button>
                </div>
            </div>
            <div class="leaderboard-list">
                ${data.players.map((player, index) => `
                    <div class="leaderboard-item ${index < 3 ? 'top-' + (index + 1) : ''}">
                        <div class="rank">${index + 1}</div>
                        <div class="avatar">
                            <img src="${player.avatar || 'default-avatar.png'}" alt="${player.name}">
                        </div>
                        <div class="info">
                            <div class="name">${player.name}</div>
                            <div class="department">${player.department}</div>
                        </div>
                        <div class="score">
                            <span class="points">${player.points}</span>
                            <span class="label">điểm</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        this.leaderboardContainer.innerHTML = leaderboardHtml;
    }

    displayAchievements(data) {
        if (!this.achievementsContainer) return;

        const achievementsHtml = `
            <div class="achievements-header">
                <h3>Thành tích</h3>
                <div class="stats">
                    <span class="total">${data.total}</span>
                    <span class="completed">${data.completed}</span>
                </div>
            </div>
            <div class="achievements-list">
                ${data.achievements.map(achievement => `
                    <div class="achievement-item ${achievement.completed ? 'completed' : ''}">
                        <div class="achievement-icon">
                            <i class="fas ${achievement.completed ? 'fa-check-circle' : 'fa-circle'}"></i>
                        </div>
                        <div class="achievement-info">
                            <h4>${achievement.title}</h4>
                            <p>${achievement.description}</p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: ${achievement.progress}%" 
                                     aria-valuenow="${achievement.progress}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <div class="achievement-reward">
                            <span class="points">+${achievement.points}</span>
                            <span class="label">điểm</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        this.achievementsContainer.innerHTML = achievementsHtml;
    }

    displayProgress(data) {
        if (!this.progressContainer) return;

        const progressHtml = `
            <div class="progress-header">
                <h3>Tiến độ của bạn</h3>
                <div class="level">Cấp độ ${data.level}</div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" role="progressbar" 
                     style="width: ${data.progress}%" 
                     aria-valuenow="${data.progress}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    <span class="progress-text">${data.progress}%</span>
                </div>
            </div>
            <div class="progress-details">
                <div class="detail-item">
                    <span class="label">Điểm hiện tại:</span>
                    <span class="value">${data.currentPoints}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Điểm cần thiết:</span>
                    <span class="value">${data.requiredPoints}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Thành tích đã đạt:</span>
                    <span class="value">${data.completedAchievements}/${data.totalAchievements}</span>
                </div>
            </div>
        `;

        this.progressContainer.innerHTML = progressHtml;
    }

    async handleLeaderboardRangeChange(e) {
        const button = e.currentTarget;
        const timeRange = button.dataset.leaderboardRange;
        
        try {
            // Cập nhật trạng thái nút
            document.querySelectorAll('[data-leaderboard-range]').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-primary');

            // Lấy dữ liệu mới
            const response = await fetch(`/api/gamification/leaderboard?timeRange=${timeRange}`);
            const data = await response.json();

            // Cập nhật bảng xếp hạng
            this.displayLeaderboard(data);
        } catch (error) {
            console.error('Leaderboard range change error:', error);
            this.showError('Có lỗi xảy ra khi cập nhật bảng xếp hạng');
        }
    }

    showError(message) {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-danger border-0';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}

// Khởi tạo khi DOM đã load
document.addEventListener('DOMContentLoaded', () => {
    new Gamification();
}); 