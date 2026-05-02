<?php
/**
 * ConvertX Project - Entry Point
 *
 * AI-powered document conversion and automation platform.
 *
 * @package MMB\Projects\ConvertX
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

use Core\SSO;

// API requests (/projects/convertx/api/*) authenticate via X-Api-Key and must
// never be redirected to the web login page.  The ApiController handles its own
// authentication, so we bypass session/SSO checks entirely for these routes.
$_cx_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_cx_is_api = (bool) preg_match('#^/projects/convertx/api(/|$)#', $_cx_uri);
unset($_cx_uri);

if (!$_cx_is_api) {
    // Check if user has access to this project (session/SSO required)
    if (!SSO::validateProjectRequest('convertx')) {
        SSO::redirectToLogin($_SERVER['REQUEST_URI']);
    }

    // Defensive: block access if project has been disabled in admin
    if (!\Core\Helpers::isProjectEnabled('convertx')) {
        http_response_code(503);
        \Core\View::render('errors/project-disabled', ['project' => 'convertx']);
        exit;
    }
} else {
    // For API routes: if the project is disabled, return a JSON error instead
    // of an HTML page or redirect, so API clients always receive JSON.
    if (!\Core\Helpers::isProjectEnabled('convertx')) {
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'This project is currently disabled.']);
        exit;
    }
}

// Load project config
$projectConfig = require PROJECT_PATH . '/config.php';

// Load project routes
require_once PROJECT_PATH . '/routes/web.php';
