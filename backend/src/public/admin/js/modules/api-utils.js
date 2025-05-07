class APIUtils {
    static async fetchData(endpoint, params = {}) {
        try {
            console.log(`Fetching data from ${endpoint} with params:`, params);
            
            // Update base URL to match your project structure
            const baseUrl = 'http://localhost/qlnhansu_V3/backend/src/public/api';
            const url = new URL(`${baseUrl}/${endpoint}`, window.location.origin);
            
            // Add params to URL
            Object.keys(params).forEach(key => {
                url.searchParams.append(key, params[key]);
            });

            console.log('Full API URL:', url.toString());

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error(`API Error Response:`, errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log(`Response from ${endpoint}:`, data);
            
            if (!data.success) {
                throw new Error(data.message || 'API call failed');
            }

            return data.data;
        } catch (error) {
            console.error(`Error in fetchData for ${endpoint}:`, error);
            this.showError(`Lỗi khi tải dữ liệu từ ${endpoint}: ${error.message}`);
            throw error;
        }
    }

    static showError(message) {
        // You can implement your own error display logic here
        console.error('Error:', message);
        
        // Show error in UI
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.container-fluid') || document.body;
        container.insertBefore(errorDiv, container.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
} 