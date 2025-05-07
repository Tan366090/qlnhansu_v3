// Dark mode module
class DarkMode {
    constructor() {
        this.themeToggle = document.getElementById('darkModeToggle');
        this.html = document.documentElement;
        this.isDarkMode = localStorage.getItem('theme') === 'dark';
        
        // Initialize immediately
        this.initialize();
    }

    initialize() {
        // Apply initial theme
        this.applyTheme();

        // Add event listener for toggle button
        if (this.themeToggle) {
            this.themeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });
        }

        // Check system theme preference
        this.checkSystemTheme();

        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                if (!localStorage.getItem('theme')) {
                    this.isDarkMode = e.matches;
                    this.applyTheme();
                }
            });
        }
    }

    checkSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            if (!localStorage.getItem('theme')) {
                this.isDarkMode = true;
                this.applyTheme();
            }
        }
    }

    toggle() {
        this.isDarkMode = !this.isDarkMode;
        localStorage.setItem('theme', this.isDarkMode ? 'dark' : 'light');
        this.applyTheme();
    }

    applyTheme() {
        if (this.isDarkMode) {
            this.html.setAttribute('data-theme', 'dark');
            if (this.themeToggle) {
                const icon = this.themeToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-sun';
                }
            }
        } else {
            this.html.removeAttribute('data-theme');
            if (this.themeToggle) {
                const icon = this.themeToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-moon';
                }
            }
        }

        // Update charts if they exist
        this.updateCharts();
    }

    updateCharts() {
        if (typeof Chart !== 'undefined' && Chart.instances) {
            Object.values(Chart.instances).forEach(chart => {
                // Update chart colors based on theme
                const isDark = this.isDarkMode;
                chart.options.scales.x.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                chart.options.scales.y.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                chart.options.scales.x.ticks.color = isDark ? '#fff' : '#666';
                chart.options.scales.y.ticks.color = isDark ? '#fff' : '#666';
                chart.update();
            });
        }
    }
}

// Export the class
export { DarkMode }; 