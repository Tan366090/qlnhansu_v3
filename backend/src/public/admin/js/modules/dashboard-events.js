class DashboardEvents {
    constructor() {
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Handle period change
        const attendancePeriodSelect = document.getElementById('attendancePeriod');
        if (attendancePeriodSelect) {
            attendancePeriodSelect.addEventListener('change', (e) => {
                loadAttendanceTrends(e.target.value);
            });
        }

        // Configuration Dropdown Handling
        const configDropdown = document.getElementById('configDropdown');
        const configDropdownToggle = document.getElementById('configDropdownToggle');
        const closeBtn = document.getElementById('closeConfigDropdown');
        const saveBtn = document.getElementById('saveConfigBtn');

        if (configDropdownToggle && configDropdown) {
            configDropdownToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                configDropdown.classList.toggle('show');
            });
        }

        if (configDropdown) {
            configDropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        if (closeBtn && configDropdown) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                configDropdown.classList.remove('show');
            });
        }

        if (saveBtn && configDropdown) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveConfig();
                configDropdown.classList.remove('show');
            });
        }

        // Search Toggle
        const searchToggle = document.getElementById('searchToggle');
        const searchContainer = document.getElementById('searchContainer');
        const closeSearch = document.querySelector('.close-search');

        if (searchToggle && searchContainer) {
            searchToggle.addEventListener('click', () => {
                searchContainer.classList.add('show');
            });
        }

        if (closeSearch && searchContainer) {
            closeSearch.addEventListener('click', () => {
                searchContainer.classList.remove('show');
            });
        }

        if (searchContainer) {
            document.addEventListener('click', (e) => {
                if (!searchContainer.contains(e.target) && e.target !== searchToggle) {
                    searchContainer.classList.remove('show');
                }
            });
        }
    }

    saveConfig() {
        const systemNameInput = document.querySelector('.config-item input');
        const languageSelect = document.querySelector('.config-item select');
        
        if (systemNameInput && languageSelect) {
            const systemName = systemNameInput.value;
            const language = languageSelect.value;
            console.log('Saving configuration:', { systemName, language });
            alert('Cấu hình đã được lưu thành công!');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize dashboard events
    const dashboardEvents = new DashboardEvents();

    // Load initial data
    loadAttendanceTrends();
    loadDepartmentDistribution();

    // Initialize modules
    const recentMenu = new RecentMenu();
    recentMenu.init();

    const darkMode = new DarkMode();
    darkMode.initialize();

    const globalSearch = new GlobalSearch();
    globalSearch.initialize();

    const menuSearch = new MenuSearch();
    menuSearch.initialize();

    const userProfile = new UserProfile();
    userProfile.loadProfile();

    const mobileStats = new MobileStats();
    mobileStats.initialize();

    const activityFilter = new ActivityFilter();
    activityFilter.initialize();

    const notificationHandler = new NotificationHandler();
    notificationHandler.initialize();
}); 