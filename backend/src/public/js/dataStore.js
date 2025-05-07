class DataStore {
    constructor() {
        this.baseUrl = '/api/sync.php';
        this.cache = new Map();
    }

    async syncData() {
        try {
            const response = await fetch(this.baseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.error);
            }
            
            return result;
        } catch (error) {
            console.error('Data sync failed:', error);
            throw error;
        }
    }

    async getTableData(table) {
        // Check cache first
        if (this.cache.has(table)) {
            return this.cache.get(table);
        }

        try {
            const response = await fetch(`${this.baseUrl}?table=${table}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.error);
            }
            
            // Cache the result
            this.cache.set(table, result.data);
            
            return result.data;
        } catch (error) {
            console.error(`Failed to get data for table ${table}:`, error);
            throw error;
        }
    }

    async clearCache() {
        try {
            const response = await fetch(this.baseUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.error);
            }
            
            // Clear local cache
            this.cache.clear();
            
            return result;
        } catch (error) {
            console.error('Cache clear failed:', error);
            throw error;
        }
    }
}

// Create singleton instance
const dataStore = new DataStore();
export default dataStore; 