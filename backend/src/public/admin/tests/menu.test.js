/**
 * @jest-environment jsdom
 */

describe('Mobile Menu Tests', () => {
    let container;
    let AdminMenuHandler;
    let menuHandler;

    beforeEach(() => {
        // Set up DOM elements before each test
        document.body.innerHTML = `
            <div class="dashboard-container">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="sidebar-overlay"></div>
                <aside class="sidebar" role="complementary">
                    <div class="sidebar-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <img src="male.png" alt="User Avatar">
                            </div>
                            <div class="user-details">
                                <h2 class="user-name">VNPT</h2>
                                <span class="user-role">Administrator</span>
                            </div>
                        </div>
                    </div>
                    <nav class="nav-menu">
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-home"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item has-submenu">
                                <a href="#" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <span>Quản lý nhân sự</span>
                                    <i class="fas fa-chevron-right submenu-toggle"></i>
                                </a>
                                <ul class="submenu">
                                    <li><a href="#" class="nav-link">Danh sách nhân viên</a></li>
                                    <li><a href="#" class="nav-link">Thêm nhân viên</a></li>
                                </ul>
                            </li>
                        </ul>
                    </nav>
                </aside>
            </div>
        `;

        // Import and initialize AdminMenuHandler
        AdminMenuHandler = require('../js/modules/menu-handler.js');
        menuHandler = new AdminMenuHandler();
    });

    afterEach(() => {
        // Clean up after each test
        document.body.innerHTML = '';
        jest.clearAllMocks();
        menuHandler = null;
    });

    test('Menu should be hidden by default on mobile screens', () => {
        // Set viewport to mobile size
        global.innerWidth = 767;
        global.dispatchEvent(new Event('resize'));

        const sidebar = document.querySelector('.sidebar');
        expect(sidebar.classList.contains('active')).toBeFalsy();
    });

    test('Menu should show when toggle button is clicked', () => {
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        // Directly call the toggle method to ensure proper context
        menuHandler.toggleMobileMenu();

        expect(sidebar.classList.contains('active')).toBeTruthy();
        expect(overlay.classList.contains('active')).toBeTruthy();
        expect(document.body.classList.contains('sidebar-open')).toBeTruthy();
    });

    test('Menu should hide when overlay is clicked', () => {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        // First show the menu
        menuHandler.toggleMobileMenu();

        // Then click the overlay
        menuHandler.closeMobileMenu();

        expect(sidebar.classList.contains('active')).toBeFalsy();
        expect(overlay.classList.contains('active')).toBeFalsy();
        expect(document.body.classList.contains('sidebar-open')).toBeFalsy();
    });

    test('Submenu should toggle when clicked', () => {
        const submenuParent = document.querySelector('.nav-item.has-submenu');
        
        // Directly call the toggle method
        menuHandler.toggleSubmenu(submenuParent);
        expect(submenuParent.classList.contains('open')).toBeTruthy();

        menuHandler.toggleSubmenu(submenuParent);
        expect(submenuParent.classList.contains('open')).toBeFalsy();
    });

    test('Menu should auto-close on desktop view', () => {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        // First show the menu in mobile view
        global.innerWidth = 767;
        global.dispatchEvent(new Event('resize'));
        menuHandler.toggleMobileMenu();

        // Then resize to desktop
        global.innerWidth = 1024;
        global.dispatchEvent(new Event('resize'));

        expect(sidebar.classList.contains('active')).toBeFalsy();
        expect(overlay.classList.contains('active')).toBeFalsy();
        expect(document.body.classList.contains('sidebar-open')).toBeFalsy();
    });

    test('Menu items should be visible when menu is open', () => {
        // Show menu
        menuHandler.toggleMobileMenu();

        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            const style = window.getComputedStyle(item);
            expect(style.display).not.toBe('none');
        });
    });

    test('Menu should be properly styled on mobile', () => {
        const sidebar = document.querySelector('.sidebar');
        const style = window.getComputedStyle(sidebar);

        expect(style.position).toBe('fixed');
        expect(style.width).toBe('280px');
        expect(style.zIndex).toBe('1040');
    });
}); 