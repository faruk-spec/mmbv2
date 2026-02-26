<?php
/**
 * QR Generator Project - Entry Point
 * 
 * @package MMB\Projects\QR
 */

// Define project path
define('PROJECT_PATH', __DIR__);
define('BASE_PATH', dirname(dirname(__DIR__)));

// Load core autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Load main app config
require_once BASE_PATH . '/config/app.php';

// Initialize session if not already started
// This is critical for OAuth users - session must be active before SSO validation
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_lifetime', '86400');
    
    // Set cookie domain to ensure session works across all paths
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = explode(':', $_SERVER['HTTP_HOST'])[0];
        ini_set('session.cookie_domain', $host);
    }
    
    session_start();
}

// Validate SSO access
use Core\SSO;
use Core\Auth;
use Core\Helpers;

// Check if user has access to this project
if (!SSO::validateProjectRequest('qr')) {
    SSO::redirectToLogin($_SERVER['REQUEST_URI']);
}

// Defensive: block access if project has been disabled in admin
if (!\Core\Helpers::isProjectEnabled('qr')) {
    http_response_code(503);
    \Core\View::render('errors/project-disabled', ['project' => 'qr']);
    exit;
}

// Load project config
$projectConfig = require PROJECT_PATH . '/config.php';

// Load project routes
require_once PROJECT_PATH . '/routes/web.php';
