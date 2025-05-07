// API Test functionality
const APITest = {
    async testConnection() {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);

            const response = await fetch(`${window.location.origin}/qlnhansu_V2/backend/src/public/api/test/connection`, {
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
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
            const response = await fetch(`${window.location.origin}/qlnhansu_V2/backend/src/public/api/test/database`, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            
            if (!response.ok) {
                throw new Error(`Database test failed: ${response.status}`);
            }
            
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error("Database Test Error:", error);
            return false;
        }
    },

    async testSession() {
        try {
            const response = await fetch(`${window.location.origin}/qlnhansu_V2/backend/src/public/api/test/session`, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            
            if (!response.ok) {
                throw new Error(`Session test failed: ${response.status}`);
            }
            
            const data = await response.json();
            return data.active;
        } catch (error) {
            console.error("Session Test Error:", error);
            return false;
        }
    },

    async runAllTests() {
        console.log('Running API tests...');
        
        const results = {
            connection: await this.testConnection(),
            database: await this.testDatabase(),
            session: await this.testSession()
        };

        console.log('Test Results:', results);
        
        // Show results in UI
        const container = document.getElementById('apiTestResults') || document.createElement('div');
        container.id = 'apiTestResults';
        container.className = 'alert alert-info';
        container.innerHTML = `
            <h4>API Test Results</h4>
            <ul>
                <li>Connection: ${results.connection ? '✅' : '❌'}</li>
                <li>Database: ${results.database ? '✅' : '❌'}</li>
                <li>Session: ${results.session ? '✅' : '❌'}</li>
            </ul>
        `;
        
        if (!document.getElementById('apiTestResults')) {
            document.body.appendChild(container);
        }

        return results;
    }
};

// Add test button to header
document.addEventListener('DOMContentLoaded', () => {
    const headerControls = document.querySelector('.header-controls');
    if (headerControls) {
        const testButton = document.createElement('button');
        testButton.className = 'btn btn-info';
        testButton.innerHTML = '<i class="fas fa-vial"></i> Test API';
        testButton.onclick = () => APITest.runAllTests();
        headerControls.appendChild(testButton);
    }
}); 