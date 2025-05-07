class WebSocketManager {
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000;
        this.handlers = new Map();
    }

    connect() {
        this.ws = new WebSocket('ws://localhost:8080');

        this.ws.onopen = () => {
            console.log('WebSocket connected');
            this.reconnectAttempts = 0;
        };

        this.ws.onclose = () => {
            console.log('WebSocket disconnected');
            this.reconnect();
        };

        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };

        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            } catch (error) {
                console.error('Error parsing WebSocket message:', error);
            }
        };
    }

    reconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
            setTimeout(() => this.connect(), this.reconnectDelay);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }

    handleMessage(data) {
        const handler = this.handlers.get(data.type);
        if (handler) {
            handler(data);
        }
    }

    on(type, handler) {
        this.handlers.set(type, handler);
    }

    send(type, data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({ type, data }));
        } else {
            console.error('WebSocket is not connected');
        }
    }

    // Notification methods
    showNotification(message, type = 'info') {
        this.send('notification', { message, type });
    }

    // Dashboard update methods
    updateDashboard(data) {
        this.send('dashboard_update', data);
    }

    // Equipment update methods
    updateEquipment(data) {
        this.send('equipment_update', data);
    }

    // Performance update methods
    updatePerformance(data) {
        this.send('performance_update', data);
    }

    // Recruitment update methods
    updateRecruitment(data) {
        this.send('recruitment_update', data);
    }

    // Chat methods
    sendChatMessage(message, sender) {
        this.send('chat', { message, sender });
    }
}

// Create singleton instance
const wsManager = new WebSocketManager();
wsManager.connect();

// Export the instance
window.wsManager = wsManager; 