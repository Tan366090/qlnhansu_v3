// Dark mode module
class DarkMode {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadDarkModePreference();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            const darkModeToggle = document.querySelector('.dark-mode-toggle');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', () => {
                    this.toggleDarkMode();
                });
            }
        });
    }

    loadDarkModePreference() {
        const darkMode = localStorage.getItem('darkMode') === 'true';
        if (darkMode) {
            document.body.classList.add('dark-mode');
        }
    }

    toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        const isDarkMode = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDarkMode);
    }
}

// Initialize dark mode functionality
const darkMode = new DarkMode(); 