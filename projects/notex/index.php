<?php
/**
 * NoteX Project - Entry Point
 *
 * @package MMB\Projects\NoteX
 */

define('PROJECT_PATH', __DIR__);
define('BASE_PATH', dirname(dirname(__DIR__)));

require_once BASE_PATH . '/core/Autoloader.php';
require_once BASE_PATH . '/config/app.php';

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

if (!SSO::validateProjectRequest('notex')) {
    SSO::redirectToLogin($_SERVER['REQUEST_URI']);
}

if (!\Core\Helpers::isProjectEnabled('notex')) {
    http_response_code(503);
    \Core\View::render('errors/project-disabled', ['project' => 'notex']);
    exit;
}

$projectConfig = require PROJECT_PATH . '/config.php';

require_once PROJECT_PATH . '/routes/web.php';
