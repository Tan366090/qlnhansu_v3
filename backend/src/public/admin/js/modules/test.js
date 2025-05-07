import { NotificationUtils } from './utils.js';

export const APITest = {
    async testConnection() {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);

            const response = await fetch("/qlnhansu_V2/backend/src/public/api/test/connection", {
                signal: controller.signal,
                headers: {
                    "Content-Type": "application/json",
                },
            });
            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error("API Test Error:", error);
            return false;
        }
    },

    async testDatabase() {
        try {
            const response = await fetch("/qlnhansu_V2/backend/src/public/api/test/database", {
                headers: {
                    "Content-Type": "application/json",
                },
            });
            if (!response.ok) throw new Error("Database test failed");
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error("Database Test Error:", error);
            return false;
        }
    },

    async testSession() {
        try {
            const response = await fetch("/qlnhansu_V2/backend/src/public/api/test/session", {
                headers: {
                    "Content-Type": "application/json",
                },
            });
            if (!response.ok) throw new Error("Session test failed");
            const data = await response.json();
            return data.active;
        } catch (error) {
            console.error("Session Test Error:", error);
            return false;
        }
    },

    async testFilePermissions() {
        try {
            const response = await fetch("/qlnhansu_V2/backend/src/public/api/test/permissions", {
                headers: {
                    "Content-Type": "application/json",
                },
            });
            if (!response.ok) throw new Error("Permissions test failed");
            const data = await response.json();
            return data.writable;
        } catch (error) {
            console.error("Permissions Test Error:", error);
            return false;
        }
    },

    async testWithRetry(fn, maxRetries = 3) {
        for (let i = 0; i < maxRetries; i++) {
            try {
                return await fn();
            } catch (error) {
                if (i === maxRetries - 1) throw error;
                await new Promise((resolve) => setTimeout(resolve, 1000 * (i + 1)));
            }
        }
    },

    async runAllTests() {
        try {
            const results = {
                connection: await this.testWithRetry(() => this.testConnection()),
                database: await this.testWithRetry(() => this.testDatabase()),
                session: await this.testWithRetry(() => this.testSession()),
                permissions: await this.testWithRetry(() => this.testFilePermissions()),
            };

            // Display results
            const container = document.createElement("div");
            container.className = "php-test";
            container.innerHTML = `
                <h2>System Test Results:</h2>
                <p>API Connection: ${results.connection ? "Success" : "Failed"}</p>
                <p>Database Connection: ${results.database ? "Success" : "Failed"}</p>
                <p>Session: ${results.session ? "Active" : "Inactive"}</p>
                <p>File Permissions: ${results.permissions ? "Writable" : "Read-only"}</p>
            `;

            document.body.insertBefore(container, document.body.firstChild);
        } catch (error) {
            console.error("Failed to run tests:", error);
            NotificationUtils.show("Failed to run system tests", "error");
        }
    }
}; 