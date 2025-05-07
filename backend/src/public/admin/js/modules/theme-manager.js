export class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.darkModeClass = 'dark-mode';
    }

    initialize() {
        this.applyTheme();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const themeToggle = document.getElementById('darkModeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }

    applyTheme() {
        if (this.theme === 'dark') {
            document.body.classList.add(this.darkModeClass);
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.body.classList.remove(this.darkModeClass);
            document.documentElement.setAttribute('data-theme', 'light');
        }
    }

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.theme);
        this.applyTheme();
    }

    getCurrentTheme() {
        return this.theme;
    }
} 