class NotificationService {
    static #notifications = [];
    static #container = null;
    static #soundEnabled = true;
    static #position = 'top-right';
    static #maxNotifications = 5;
    static #notificationHistory = [];

    static init() {
        this.#createContainer();
        this.#loadSettings();
        this.#setupEventListeners();
    }

    static #createContainer() {
        this.#container = document.createElement('div');
        this.#container.className = 'notification-container';
        document.body.appendChild(this.#container);
    }

    static #loadSettings() {
        const settings = localStorage.getItem('notificationSettings');
        if (settings) {
            const { soundEnabled, position, maxNotifications } = JSON.parse(settings);
            this.#soundEnabled = soundEnabled;
            this.#position = position;
            this.#maxNotifications = maxNotifications;
        }
    }

    static #setupEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('notification-close')) {
                const notification = e.target.closest('.notification');
                this.#removeNotification(notification);
            }
        });
    }

    static show(message, type = 'info', options = {}) {
        const {
            duration = 5000,
            action = null,
            actionText = 'Xem',
            sound = true,
            persistent = false,
            group = null
        } = options;

        const notification = this.#createNotification(message, type, action, actionText, group);
        this.#container.appendChild(notification);

        if (sound && this.#soundEnabled) {
            this.#playSound(type);
        }

        if (!persistent) {
            setTimeout(() => {
                this.#removeNotification(notification);
            }, duration);
        }

        this.#notifications.push(notification);
        this.#notificationHistory.push({
            message,
            type,
            timestamp: new Date(),
            group
        });

        if (this.#notifications.length > this.#maxNotifications) {
            this.#removeNotification(this.#notifications[0]);
        }

        return notification;
    }

    static #createNotification(message, type, action, actionText, group) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        if (group) {
            notification.dataset.group = group;
        }

        const icon = this.#getIcon(type);
        const content = `
            <div class="notification-icon">${icon}</div>
            <div class="notification-content">
                <div class="notification-message">${message}</div>
                ${action ? `<button class="notification-action" onclick="${action}">${actionText}</button>` : ''}
            </div>
            <button class="notification-close">&times;</button>
        `;

        notification.innerHTML = content;
        return notification;
    }

    static #getIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    static #playSound(type) {
        const sounds = {
            success: 'success.mp3',
            error: 'error.mp3',
            warning: 'warning.mp3',
            info: 'info.mp3'
        };

        const audio = new Audio(`/assets/sounds/${sounds[type]}`);
        audio.play().catch(() => {}); // Ignore errors if sound can't play
    }

    static #removeNotification(notification) {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            notification.remove();
            this.#notifications = this.#notifications.filter(n => n !== notification);
        }, 300);
    }

    static success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    static error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    static warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    static info(message, options = {}) {
        return this.show(message, 'info', options);
    }

    static clearAll() {
        this.#notifications.forEach(notification => this.#removeNotification(notification));
    }

    static clearGroup(group) {
        const groupNotifications = this.#notifications.filter(n => n.dataset.group === group);
        groupNotifications.forEach(notification => this.#removeNotification(notification));
    }

    static getHistory() {
        return [...this.#notificationHistory];
    }

    static updateSettings(settings) {
        const { soundEnabled, position, maxNotifications } = settings;
        if (soundEnabled !== undefined) this.#soundEnabled = soundEnabled;
        if (position) this.#position = position;
        if (maxNotifications) this.#maxNotifications = maxNotifications;

        localStorage.setItem('notificationSettings', JSON.stringify({
            soundEnabled: this.#soundEnabled,
            position: this.#position,
            maxNotifications: this.#maxNotifications
        }));
    }
}

// Initialize the service when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NotificationService.init();
}); 