class ErrorLogger {
    constructor() {
        this.errors = [];
        this.warnings = [];
        this.info = [];
        this.originalConsole = {
            log: console.log,
            error: console.error,
            warn: console.warn,
            info: console.info
        };
    }

    init() {
        // Override console methods
        console.log = (...args) => {
            this.info.push({ type: 'log', message: args, timestamp: new Date() });
            this.originalConsole.log(...args);
        };

        console.error = (...args) => {
            this.errors.push({ type: 'error', message: args, timestamp: new Date() });
            this.originalConsole.error(...args);
            this.updateErrorDisplay();
        };

        console.warn = (...args) => {
            this.warnings.push({ type: 'warn', message: args, timestamp: new Date() });
            this.originalConsole.warn(...args);
            this.updateErrorDisplay();
        };

        console.info = (...args) => {
            this.info.push({ type: 'info', message: args, timestamp: new Date() });
            this.originalConsole.info(...args);
        };

        // Create error display container
        this.createErrorDisplay();
        
        // Listen for unhandled errors
        window.onerror = (message, source, lineno, colno, error) => {
            this.errors.push({
                type: 'unhandled',
                message: message,
                source: source,
                lineno: lineno,
                colno: colno,
                error: error,
                timestamp: new Date()
            });
            this.updateErrorDisplay();
            return false;
        };

        // Listen for unhandled promise rejections
        window.onunhandledrejection = (event) => {
            this.errors.push({
                type: 'promise',
                message: event.reason,
                timestamp: new Date()
            });
            this.updateErrorDisplay();
        };
    }

    createErrorDisplay() {
        const container = document.createElement('div');
        container.id = 'error-logger-container';
        container.style.cssText = `
            position: fixed;
            bottom: 0;
            right: 0;
            width: 400px;
            max-height: 300px;
            background: rgba(0,0,0,0.9);
            color: white;
            padding: 10px;
            overflow-y: auto;
            z-index: 9999;
            font-family: monospace;
            font-size: 12px;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #444;
        `;

        const title = document.createElement('h3');
        title.textContent = 'Console Errors';
        title.style.margin = '0';

        const clearButton = document.createElement('button');
        clearButton.textContent = 'Clear';
        clearButton.onclick = () => this.clearErrors();
        clearButton.style.cssText = `
            background: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        `;

        header.appendChild(title);
        header.appendChild(clearButton);
        container.appendChild(header);

        const content = document.createElement('div');
        content.id = 'error-logger-content';
        container.appendChild(content);

        document.body.appendChild(container);
    }

    updateErrorDisplay() {
        const content = document.getElementById('error-logger-content');
        if (!content) return;

        content.innerHTML = '';

        // Display errors
        this.errors.forEach((error, index) => {
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `
                padding: 5px;
                margin-bottom: 5px;
                background: rgba(255,0,0,0.2);
                border-left: 3px solid red;
            `;

            const time = document.createElement('div');
            time.textContent = error.timestamp.toLocaleTimeString();
            time.style.color = '#aaa';
            time.style.fontSize = '10px';

            const message = document.createElement('div');
            message.textContent = this.formatError(error);
            message.style.whiteSpace = 'pre-wrap';
            message.style.wordBreak = 'break-word';

            errorDiv.appendChild(time);
            errorDiv.appendChild(message);
            content.appendChild(errorDiv);
        });

        // Auto scroll to bottom
        content.scrollTop = content.scrollHeight;
    }

    formatError(error) {
        if (typeof error.message === 'string') {
            return error.message;
        }
        
        if (Array.isArray(error.message)) {
            return error.message.map(msg => {
                if (typeof msg === 'object') {
                    return JSON.stringify(msg, null, 2);
                }
                return msg;
            }).join(' ');
        }

        return JSON.stringify(error, null, 2);
    }

    clearErrors() {
        this.errors = [];
        this.warnings = [];
        this.info = [];
        this.updateErrorDisplay();
    }

    getErrors() {
        return {
            errors: this.errors,
            warnings: this.warnings,
            info: this.info
        };
    }

    downloadLogs() {
        const logs = this.getErrors();
        const blob = new Blob([JSON.stringify(logs, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `console-logs-${new Date().toISOString()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

// Initialize error logger
const errorLogger = new ErrorLogger();
errorLogger.init();

// Add download button
const downloadButton = document.createElement('button');
downloadButton.textContent = 'Download Logs';
downloadButton.onclick = () => errorLogger.downloadLogs();
downloadButton.style.cssText = `
    position: fixed;
    bottom: 310px;
    right: 0;
    background: #4CAF50;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    z-index: 9999;
`;
document.body.appendChild(downloadButton); 