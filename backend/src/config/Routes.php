// API Routes
$routes->group('api', ['filter' => 'auth'], function($routes) {
    // Search
    $routes->get('search', 'ApiController::search');
    
    // User Profile & Settings
    $routes->get('user/profile', 'ApiController::userProfile');
    $routes->match(['get', 'post'], 'user/settings', 'ApiController::userSettings');
    
    // Export Data
    $routes->get('export/(:segment)', 'ApiController::export/$1');
    
    // AI Analysis
    $routes->get('ai/hr-trends', 'ApiController::hrTrends');
    $routes->get('ai/sentiment', 'ApiController::sentiment');
    
    // Gamification
    $routes->get('gamification/leaderboard', 'ApiController::leaderboard');
    $routes->get('gamification/achievements', 'ApiController::achievements');
    $routes->get('gamification/progress', 'ApiController::progress');
    
    // Mobile Stats
    $routes->get('mobile/stats', 'ApiController::mobileStats');
    $routes->get('mobile/stats/versions', 'ApiController::mobileVersions');
    
    // Activities
    $routes->get('activities', 'ApiController::activities');
    
    // Notifications
    $routes->get('notifications', 'ApiController::notifications');
    $routes->post('notifications/(:num)/read', 'ApiController::markNotificationAsRead/$1');
    $routes->delete('notifications/(:num)', 'ApiController::deleteNotification/$1');
}); 