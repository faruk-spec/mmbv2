<?php
/**
 * BillX Routes
 *
 * @package MMB\Projects\BillX
 */

use Core\Router;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/projects/billx', '', $uri);
$uri = $uri ?: '/';

$segments = explode('/', trim($uri, '/'));

switch ($segments[0]) {
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        $controller = new \Projects\BillX\Controllers\DashboardController();
        $controller->index();
        break;

    case 'generate':
        require_once PROJECT_PATH . '/controllers/BillController.php';
        $controller = new \Projects\BillX\Controllers\BillController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->generate();
        } else {
            $controller->showForm();
        }
        break;

    case 'history':
        require_once PROJECT_PATH . '/controllers/BillController.php';
        $controller = new \Projects\BillX\Controllers\BillController();
        $controller->history();
        break;

    case 'view':
        require_once PROJECT_PATH . '/controllers/BillController.php';
        $controller = new \Projects\BillX\Controllers\BillController();
        $id = (int)($segments[1] ?? 0);
        if ($id) {
            $controller->view($id);
        } else {
            http_response_code(404);
            echo "Invalid bill ID";
        }
        break;

    case 'download':
        require_once PROJECT_PATH . '/controllers/BillController.php';
        $controller = new \Projects\BillX\Controllers\BillController();
        $id = (int)($segments[1] ?? 0);
        if ($id) {
            $controller->download($id);
        } else {
            http_response_code(404);
            echo "Invalid bill ID";
        }
        break;

    case 'delete':
        require_once PROJECT_PATH . '/controllers/BillController.php';
        $controller = new \Projects\BillX\Controllers\BillController();
        $controller->delete();
        break;

    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
