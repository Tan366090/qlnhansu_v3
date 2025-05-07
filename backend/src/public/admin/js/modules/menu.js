document.addEventListener('DOMContentLoaded', function() {
    // Basic menu toggle functionality
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }

    // Xử lý submenu
    const menuItems = document.querySelectorAll('.nav-item.has-submenu');
    
    menuItems.forEach(item => {
        const link = item.querySelector('.nav-link');
        const submenu = item.querySelector('.submenu');
        
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Đóng các submenu khác
            menuItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('open');
                }
            });
            
            // Mở/đóng submenu hiện tại
            item.classList.toggle('open');
        });
    });

    // Đóng submenu khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item.has-submenu')) {
            menuItems.forEach(item => {
                item.classList.remove('open');
            });
        }
    });
}); 