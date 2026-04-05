<?php
/**
 * FormX Project Routes
 *
 * @package MMB\Projects\FormX
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/projects/formx#', '', $uri);
$uri = $uri ?: '/';

$segments = explode('/', trim($uri, '/'));
$method   = $_SERVER['REQUEST_METHOD'];

require_once PROJECT_PATH . '/controllers/DashboardController.php';

switch ($segments[0]) {
    case '':
    default:
        $ctrl = new \Projects\FormX\Controllers\DashboardController();
        $ctrl->index();
        break;
}
