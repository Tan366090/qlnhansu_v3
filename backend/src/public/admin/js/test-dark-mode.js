import { DarkMode } from './modules/dark-mode.js';

// Khởi tạo dark mode
const darkMode = new DarkMode();

// Debug functions
function updateDebugInfo() {
    const isDarkMode = document.documentElement.classList.contains('dark-mode');
    const localStorageValue = localStorage.getItem('darkMode');
    
    document.getElementById('darkModeStatus').textContent = isDarkMode ? 'Active' : 'Not Active';
    document.getElementById('localStorageValue').textContent = localStorageValue || 'Not Set';
}

// Update debug info when status button is clicked
document.getElementById('checkStatus').addEventListener('click', updateDebugInfo);

// Update debug info when theme is toggled
document.getElementById('themeToggle').addEventListener('click', () => {
    setTimeout(updateDebugInfo, 100);
});

// Initial debug info update
updateDebugInfo(); 