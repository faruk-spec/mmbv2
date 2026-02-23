<?php
/**
 * Web Routes
 * 
 * @package MMB\Routes
 */

use Core\View;
use Core\Auth;
use Core\Helpers;

// Home page
$router->get('/', function() {
    // Don't redirect logged-in users, let them view the home page
    View::render('home');
});

// Authentication routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->post('/logout', 'AuthController@logout');

// Password reset
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// Email verification
$router->get('/verify-email/{token}', 'AuthController@verifyEmail');

// Google OAuth routes
$router->get('/auth/google', 'GoogleOAuthController@redirectToGoogle');
$router->get('/auth/google/callback', 'GoogleOAuthController@callback');
$router->get('/auth/google/link', 'GoogleOAuthController@link', ['auth']);
$router->post('/auth/google/unlink', 'GoogleOAuthController@unlink', ['auth']);

// User Dashboard
$router->get('/dashboard', 'DashboardController@index', ['auth']);
$router->get('/profile', 'DashboardController@profile', ['auth']);
$router->post('/profile', 'DashboardController@updateProfile', ['auth']);
$router->get('/security', 'DashboardController@security', ['auth']);
$router->post('/security/set-password', 'DashboardController@setPassword', ['auth']);
$router->post('/security/password', 'DashboardController@updatePassword', ['auth']);
$router->post('/security/revoke-session', 'DashboardController@revokeSession', ['auth']);
$router->get('/activity', 'DashboardController@activity', ['auth']);
$router->get('/settings', 'DashboardController@settings', ['auth']);
$router->post('/settings', 'DashboardController@updateSettings', ['auth']);

// Plans & Subscriptions
$router->get('/plans', 'PlansController@index', ['auth']);
$router->get('/plans/subscribe/{slug}', 'PlansController@subscribe', ['auth']);
$router->post('/plans/subscribe/{slug}', 'PlansController@processSubscribe', ['auth']);

// Notification API
$router->get('/api/notifications', 'NotificationController@getList', ['auth']);
$router->post('/api/notifications/mark-read', 'NotificationController@markRead', ['auth']);
$router->post('/api/notifications/mark-all-read', 'NotificationController@markAllRead', ['auth']);
$router->get('/notifications', 'NotificationController@viewAll', ['auth']);

// 2FA routes
// Two-Factor Authentication routes
$router->get('/2fa/setup', 'TwoFactorController@setup', ['auth']);
$router->post('/2fa/enable', 'TwoFactorController@enable', ['auth']);
$router->post('/2fa/disable', 'TwoFactorController@disable', ['auth']);
$router->get('/2fa/backup-codes', 'TwoFactorController@showBackupCodes', ['auth']);
$router->get('/2fa/verify', 'TwoFactorController@showVerify');
$router->post('/2fa/verify', 'TwoFactorController@verify');

// Project routes - handled by project index.php files
$router->get('/projects/{project}', function($project) {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\View::render('errors/project-disabled', ['project' => $project]);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->get('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\View::render('errors/project-disabled', ['project' => $project]);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->post('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\Helpers::json(['error' => 'Project is disabled'], 503);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->put('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\Helpers::json(['error' => 'Project is disabled'], 503);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->delete('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\Helpers::json(['error' => 'Project is disabled'], 503);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

// ProShare anonymous short URLs (must be defined here for global access)
$router->get('/s/{shortcode}', 'Projects\ProShare\Controllers\DownloadController@download');
$router->get('/t/{shortcode}', 'Projects\ProShare\Controllers\TextShareController@view');
