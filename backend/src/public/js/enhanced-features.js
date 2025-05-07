// Enhanced Menu Search
class EnhancedMenuSearch {
    constructor() {
        this.searchInput = document.querySelector('.menu-search input');
        this.searchTimeout = null;
        this.setupSearch();
    }

    setupSearch() {
        if (!this.searchInput) return;

        // Add loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'search-loading';
        loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        loadingIndicator.style.display = 'none';
        this.searchInput.parentNode.appendChild(loadingIndicator);

        // Add debounce
        this.searchInput.addEventListener('input', (e) => {
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
            
            loadingIndicator.style.display = 'block';
            
            this.searchTimeout = setTimeout(() => {
                this.performSearch(e.target.value);
                loadingIndicator.style.display = 'none';
            }, 300);
        });
    }

    performSearch(query) {
        const menuItems = document.querySelectorAll('.nav-item');
        menuItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            item.style.display = matches ? '' : 'none';
        });
    }
}

// Enhanced Data Tables
class EnhancedDataTable {
    constructor(tableId) {
        this.table = document.getElementById(tableId);
        if (!this.table) return;

        this.setupSorting();
        this.setupColumnResizing();
        this.setupRowSelection();
    }

    setupSorting() {
        const headers = this.table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(header));
        });
    }

    sortTable(header) {
        const column = header.cellIndex;
        const rows = Array.from(this.table.querySelectorAll('tbody tr'));
        const direction = header.dataset.direction === 'asc' ? -1 : 1;
        
        rows.sort((a, b) => {
            const aValue = a.cells[column].textContent;
            const bValue = b.cells[column].textContent;
            return direction * aValue.localeCompare(bValue);
        });

        header.dataset.direction = direction === 1 ? 'asc' : 'desc';
        
        const tbody = this.table.querySelector('tbody');
        rows.forEach(row => tbody.appendChild(row));
    }

    setupColumnResizing() {
        const headers = this.table.querySelectorAll('th');
        headers.forEach(header => {
            const resizer = document.createElement('div');
            resizer.className = 'resizer';
            header.appendChild(resizer);

            let x = 0;
            let w = 0;

            const mouseDownHandler = (e) => {
                x = e.clientX;
                w = header.offsetWidth;

                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);
            };

            const mouseMoveHandler = (e) => {
                const dx = e.clientX - x;
                header.style.width = `${w + dx}px`;
            };

            const mouseUpHandler = () => {
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
            };

            resizer.addEventListener('mousedown', mouseDownHandler);
        });
    }

    setupRowSelection() {
        const rows = this.table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', () => {
                row.classList.toggle('selected');
            });
        });
    }
}

// Enhanced Responsive Design
class EnhancedResponsive {
    constructor() {
        this.setupTouchGestures();
        this.setupResponsiveImages();
        this.setupResponsiveTypography();
    }

    setupTouchGestures() {
        const touchElements = document.querySelectorAll('.touch-enabled');
        touchElements.forEach(element => {
            let startX = 0;
            let startY = 0;

            element.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            });

            element.addEventListener('touchmove', (e) => {
                const diffX = e.touches[0].clientX - startX;
                const diffY = e.touches[0].clientY - startY;

                if (Math.abs(diffX) > Math.abs(diffY)) {
                    // Horizontal swipe
                    if (diffX > 0) {
                        element.dispatchEvent(new CustomEvent('swipeRight'));
                    } else {
                        element.dispatchEvent(new CustomEvent('swipeLeft'));
                    }
                } else {
                    // Vertical swipe
                    if (diffY > 0) {
                        element.dispatchEvent(new CustomEvent('swipeDown'));
                    } else {
                        element.dispatchEvent(new CustomEvent('swipeUp'));
                    }
                }
            });
        });
    }

    setupResponsiveImages() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    setupResponsiveTypography() {
        const typography = document.querySelectorAll('.responsive-text');
        typography.forEach(text => {
            const resizeObserver = new ResizeObserver(entries => {
                entries.forEach(entry => {
                    const width = entry.contentRect.width;
                    if (width < 400) {
                        text.style.fontSize = '14px';
                    } else if (width < 600) {
                        text.style.fontSize = '16px';
                    } else {
                        text.style.fontSize = '18px';
                    }
                });
            });

            resizeObserver.observe(text);
        });
    }
}

// Enhanced Accessibility
class EnhancedAccessibility {
    constructor() {
        this.setupSkipLinks();
        this.setupFocusManagement();
        this.setupARIALabels();
    }

    setupSkipLinks() {
        const skipLinks = document.createElement('div');
        skipLinks.className = 'skip-links';
        skipLinks.innerHTML = `
            <a href="#main-content" class="skip-link">Skip to main content</a>
            <a href="#main-menu" class="skip-link">Skip to main menu</a>
        `;
        document.body.insertBefore(skipLinks, document.body.firstChild);
    }

    setupFocusManagement() {
        const focusableElements = document.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
        });
    }

    setupARIALabels() {
        const elements = document.querySelectorAll('[data-aria-label]');
        elements.forEach(element => {
            const label = element.dataset.ariaLabel;
            element.setAttribute('aria-label', label);
        });
    }
}

// Enhanced Performance
class EnhancedPerformance {
    constructor() {
        this.setupCodeSplitting();
        this.setupServiceWorker();
        this.setupImageOptimization();
    }

    setupCodeSplitting() {
        // Dynamic imports for code splitting
        const loadModule = async (moduleName) => {
            try {
                const module = await import(`/qlnhansu_V2/backend/src/public/admin/js/modules/${moduleName}.js`);
                return module;
            } catch (error) {
                console.error(`Failed to load module ${moduleName}:`, error);
            }
        };

        // Example usage
        document.querySelectorAll('[data-module]').forEach(element => {
            element.addEventListener('click', async () => {
                const moduleName = element.dataset.module;
                const module = await loadModule(moduleName);
                if (module) module.init();
            });
        });
    }

    setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    }

    setupImageOptimization() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (!img.complete) {
                img.addEventListener('load', () => {
                    img.classList.add('loaded');
                });
            } else {
                img.classList.add('loaded');
            }
        });
    }
}

// Enhanced Security
class EnhancedSecurity {
    constructor() {
        this.setupRateLimiting();
        this.setupInputValidation();
        this.setupSecurityHeaders();
    }

    setupRateLimiting() {
        const rateLimitMap = new Map();
        const RATE_LIMIT = 100; // requests
        const TIME_WINDOW = 60000; // 1 minute

        document.addEventListener('submit', (e) => {
            const form = e.target;
            const now = Date.now();
            const key = `${form.id}-${now}`;

            if (!rateLimitMap.has(key)) {
                rateLimitMap.set(key, {
                    count: 0,
                    timestamp: now
                });
            }

            const data = rateLimitMap.get(key);
            data.count++;

            if (data.count > RATE_LIMIT) {
                e.preventDefault();
                alert('Rate limit exceeded. Please try again later.');
            }

            // Cleanup old entries
            for (const [k, v] of rateLimitMap.entries()) {
                if (now - v.timestamp > TIME_WINDOW) {
                    rateLimitMap.delete(k);
                }
            }
        });
    }

    setupInputValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const inputs = form.querySelectorAll('input, textarea, select');
                let isValid = true;

                inputs.forEach(input => {
                    if (input.hasAttribute('required') && !input.value.trim()) {
                        isValid = false;
                        input.classList.add('invalid');
                    } else {
                        input.classList.remove('invalid');
                    }

                    if (input.type === 'email' && !this.validateEmail(input.value)) {
                        isValid = false;
                        input.classList.add('invalid');
                    }

                    if (input.type === 'password' && !this.validatePassword(input.value)) {
                        isValid = false;
                        input.classList.add('invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields correctly.');
                }
            });
        });
    }

    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    validatePassword(password) {
        return password.length >= 8;
    }

    setupSecurityHeaders() {
        // Add security headers via meta tags
        const securityMeta = document.createElement('meta');
        securityMeta.setAttribute('http-equiv', 'Content-Security-Policy');
        securityMeta.setAttribute('content', `
            default-src 'self';
            script-src 'self' 'unsafe-inline' 'unsafe-eval';
            style-src 'self' 'unsafe-inline';
            img-src 'self' data:;
            font-src 'self';
            connect-src 'self';
        `);
        document.head.appendChild(securityMeta);
    }
}

// Initialize all enhanced features
document.addEventListener('DOMContentLoaded', () => {
    new EnhancedMenuSearch();
    new EnhancedDataTable('recentEmployees');
    new EnhancedResponsive();
    new EnhancedAccessibility();
    new EnhancedPerformance();
    new EnhancedSecurity();
}); 