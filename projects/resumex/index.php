<?php
/**
 * ResumeX Project - Entry Point
 *
 * @package MMB\Projects\ResumeX
 */

// Define project path
define('PROJECT_PATH', __DIR__);
define('BASE_PATH', dirname(dirname(__DIR__)));

// Load core autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Load main app config
require_once BASE_PATH . '/config/app.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_lifetime', '86400');

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

if (!SSO::validateProjectRequest('resumex')) {
    SSO::redirectToLogin($_SERVER['REQUEST_URI']);
}

// Block if project is disabled
if (!\Core\Helpers::isProjectEnabled('resumex')) {
    http_response_code(503);
    \Core\View::render('errors/project-disabled', ['project' => 'resumex']);
    exit;
}

// Load project config
$projectConfig = require PROJECT_PATH . '/config.php';

// Load project routes
require_once PROJECT_PATH . '/routes/web.php';
