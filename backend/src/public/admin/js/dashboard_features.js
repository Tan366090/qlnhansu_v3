// Task Management
const TaskManager = {
    tasks: [],
    loadTasks: async () => {
        try {
            UIUtils.showLoading();
            const response = await APIUtils.get('tasks');
            this.tasks = response.data;
            this.renderTasks();
        } catch (error) {
            APIUtils.handleError(error, 'loadTasks');
        } finally {
            UIUtils.hideLoading();
        }
    },
    renderTasks: () => {
        const taskContainer = document.getElementById('taskContainer');
        if (!taskContainer) return;
        
        taskContainer.innerHTML = this.tasks.map(task => `
            <div class="task-item ${task.status}">
                <div class="task-title">${task.title}</div>
                <div class="task-description">${task.description}</div>
                <div class="task-meta">
                    <span class="task-due">${CommonUtils.formatDate(task.dueDate)}</span>
                    <span class="task-priority ${task.priority}">${task.priority}</span>
                </div>
            </div>
        `).join('');
    }
};

// Weather Widget
const WeatherWidget = {
    weatherData: null,
    updateWeather: async () => {
        try {
            UIUtils.showLoading();
            const response = await APIUtils.get('weather');
            this.weatherData = response.data;
            this.renderWeather();
        } catch (error) {
            APIUtils.handleError(error, 'updateWeather');
        } finally {
            UIUtils.hideLoading();
        }
    },
    renderWeather: () => {
        const weatherContainer = document.getElementById('weatherContainer');
        if (!weatherContainer || !this.weatherData) return;
        
        weatherContainer.innerHTML = `
            <div class="weather-widget">
                <div class="weather-icon">
                    <i class="fas fa-${this.getWeatherIcon(this.weatherData.condition)}"></i>
                </div>
                <div class="weather-info">
                    <div class="temperature">${this.weatherData.temperature}°C</div>
                    <div class="condition">${this.weatherData.condition}</div>
                    <div class="location">${this.weatherData.location}</div>
                </div>
            </div>
        `;
    },
    getWeatherIcon: (condition) => {
        const icons = {
            'sunny': 'sun',
            'cloudy': 'cloud',
            'rainy': 'cloud-rain',
            'stormy': 'bolt',
            'snowy': 'snowflake'
        };
        return icons[condition.toLowerCase()] || 'cloud';
    }
};

// Chat Functions
const ChatManager = {
    messages: [],
    loadChats: async () => {
        try {
            UIUtils.showLoading();
            const response = await APIUtils.get('chats');
            this.messages = response.data;
            this.renderChats();
        } catch (error) {
            APIUtils.handleError(error, 'loadChats');
        } finally {
            UIUtils.hideLoading();
        }
    },
    renderChats: () => {
        const chatContainer = document.getElementById('chatContainer');
        if (!chatContainer) return;
        
        chatContainer.innerHTML = this.messages.map(message => `
            <div class="chat-message ${message.type}">
                <div class="message-header">
                    <span class="sender">${message.sender}</span>
                    <span class="time">${CommonUtils.formatDate(message.timestamp)}</span>
                </div>
                <div class="message-content">${message.content}</div>
            </div>
        `).join('');
    },
    sendMessage: async (content) => {
        try {
            await APIUtils.post('chats', { content });
            await this.loadChats();
        } catch (error) {
            APIUtils.handleError(error, 'sendMessage');
        }
    }
};

// Backup Functions
const BackupManager = {
    backupInfo: null,
    loadBackupInfo: async () => {
        try {
            UIUtils.showLoading();
            const response = await APIUtils.get('backup');
            this.backupInfo = response.data;
            this.renderBackupInfo();
        } catch (error) {
            APIUtils.handleError(error, 'loadBackupInfo');
        } finally {
            UIUtils.hideLoading();
        }
    },
    renderBackupInfo: () => {
        const backupContainer = document.getElementById('backupContainer');
        if (!backupContainer || !this.backupInfo) return;
        
        backupContainer.innerHTML = `
            <div class="backup-info">
                <div class="backup-status">
                    <i class="fas fa-${this.backupInfo.status === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>Trạng thái: ${this.backupInfo.status}</span>
                </div>
                <div class="backup-details">
                    <div>Lần backup cuối: ${CommonUtils.formatDate(this.backupInfo.lastBackup)}</div>
                    <div>Kích thước: ${this.formatSize(this.backupInfo.size)}</div>
                    <div>Vị trí: ${this.backupInfo.location}</div>
                </div>
            </div>
        `;
    },
    formatSize: (bytes) => {
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;
        
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        
        return `${size.toFixed(2)} ${units[unitIndex]}`;
    },
    createBackup: async () => {
        try {
            await APIUtils.post('backup/create');
            NotificationUtils.show('Backup đã được tạo thành công', 'success');
            await this.loadBackupInfo();
        } catch (error) {
            APIUtils.handleError(error, 'createBackup');
        }
    }
};

// Initialize all managers when DOM is loaded
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Show loading state
        UIUtils.showLoading();

        // Initialize all features
        await Promise.allSettled([
            TaskManager.loadTasks(),
            WeatherWidget.updateWeather(),
            ChatManager.loadChats(),
            BackupManager.loadBackupInfo()
        ]);

    } catch (error) {
        console.error('Initialization error:', error);
        NotificationUtils.show('Có lỗi khi khởi tạo ứng dụng', 'error');
    } finally {
        // Always hide loading regardless of success or failure
        UIUtils.hideLoading();
    }
}); 