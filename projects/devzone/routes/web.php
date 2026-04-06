<?php
/**
 * DevZone Routes
 *
 * @package MMB\Projects\DevZone
 */

use Core\Auth;

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = str_replace('/projects/devzone', '', $uri);
$uri      = $uri ?: '/';
$segments = explode('/', trim($uri, '/'));

switch ($segments[0]) {
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        (new \Projects\DevZone\Controllers\DashboardController())->index();
        break;

    default:
        http_response_code(404);
        echo '<p style="font-family:sans-serif;padding:2rem;color:#888;">DevZone page not found.</p>';
        break;
}
