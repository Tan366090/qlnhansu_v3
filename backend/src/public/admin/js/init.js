// Load required libraries
document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>');
document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>');
document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>');

// Load our modules
// document.write('<script src="js/modules/menu-handler.js"></script>');
document.write('<script src="js/modules/dashboard.js"></script>');

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Menu handler is already initialized in its own file
    // Dashboard is already initialized in its own file
    console.log('All modules loaded and initialized');
}); 