// API utilities
export const APIUtils = {
    baseUrl: 'http://localhost/qlnhansu_V2/backend/src/public/api',
    maxRetries: 3,
    retryDelay: 1000,
    cache: new Map(),
    cacheTimeout: 5 * 60 * 1000, // 5 minutes

    // Authentication handling
    getAuthHeaders() {
        const token = localStorage.getItem('token');
        return {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
    },

    // Enhanced error handling
    handleError(error, endpoint) {
        const errorTypes = {
            'NetworkError': 'Lỗi kết nối mạng',
            'SyntaxError': 'Lỗi định dạng dữ liệu',
            'TypeError': 'Lỗi xử lý dữ liệu',
            'default': 'Lỗi không xác định'
        };

        const errorMessage = errorTypes[error.name] || errorTypes.default;
        console.error(`Error in ${endpoint}:`, error);
        NotificationUtils.show(`${errorMessage}: ${error.message}`, 'error');
    },

    // Cache management
    setCache(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    },

    getCache(key) {
        const cached = this.cache.get(key);
        if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
            return cached.data;
        }
        this.cache.delete(key);
        return null;
    },

    // Enhanced fetch with retry and error handling
    async fetchWithRetry(endpoint, options = {}, retryCount = 0) {
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}`, {
                ...options,
                headers: {
                    ...this.getAuthHeaders(),
                    ...options.headers
                },
                credentials: 'include'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            if (retryCount < this.maxRetries) {
                await new Promise(resolve => setTimeout(resolve, this.retryDelay * (retryCount + 1)));
                return this.fetchWithRetry(endpoint, options, retryCount + 1);
            }
            this.handleError(error, endpoint);
            return null;
        }
    },

    // GET request with caching
    async get(endpoint, options = {}) {
        const cacheKey = `GET:${endpoint}:${JSON.stringify(options)}`;
        const cachedData = this.getCache(cacheKey);
        if (cachedData) {
            return cachedData;
        }

        const data = await this.fetchWithRetry(endpoint, {
            method: 'GET',
            ...options
        });

        if (data) {
            this.setCache(cacheKey, data);
        }
        return data;
    },

    // POST request
    async post(endpoint, body, options = {}) {
        return this.fetchWithRetry(endpoint, {
            method: 'POST',
            body: JSON.stringify(body),
            ...options
        });
    },

    // PUT request
    async put(endpoint, body, options = {}) {
        return this.fetchWithRetry(endpoint, {
            method: 'PUT',
            body: JSON.stringify(body),
            ...options
        });
    },

    // DELETE request
    async delete(endpoint, options = {}) {
        return this.fetchWithRetry(endpoint, {
            method: 'DELETE',
            ...options
        });
    },

    // PATCH request
    async patch(endpoint, body, options = {}) {
        return this.fetchWithRetry(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(body),
            ...options
        });
    },

    // Query builder for GET requests
    buildQuery(params) {
        const query = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null) {
                query.append(key, value);
            }
        });
        return query.toString();
    },

    // Enhanced data fetching with query parameters
    async fetchData(endpoint, params = {}) {
        const queryString = this.buildQuery(params);
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.get(url);
    },

    // Clear cache
    clearCache() {
        this.cache.clear();
    },

    // Clear specific cache
    clearCacheForEndpoint(endpoint) {
        for (const [key] of this.cache) {
            if (key.startsWith(`GET:${endpoint}`)) {
                this.cache.delete(key);
            }
        }
    }
}; 