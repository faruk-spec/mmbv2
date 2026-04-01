<?php
/**
 * CardX Routes
 *
 * @package MMB\Projects\IDCard
 */

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = str_replace('/projects/idcard', '', $uri);
$uri      = $uri ?: '/';
$segments = explode('/', trim($uri, '/'));

switch ($segments[0]) {
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        $controller = new \Projects\IDCard\Controllers\DashboardController();
        $controller->index();
        break;

    case 'generate':
        require_once PROJECT_PATH . '/controllers/IDCardController.php';
        $controller = new \Projects\IDCard\Controllers\IDCardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->generate();
        } else {
            $controller->showForm();
        }
        break;

    case 'view':
        require_once PROJECT_PATH . '/controllers/IDCardController.php';
        $controller = new \Projects\IDCard\Controllers\IDCardController();
        $id = (int) ($segments[1] ?? 0);
        if ($id) {
            $controller->view($id);
        } else {
            http_response_code(404);
            echo "Invalid ID card";
        }
        break;

    case 'history':
        require_once PROJECT_PATH . '/controllers/IDCardController.php';
        $controller = new \Projects\IDCard\Controllers\IDCardController();
        $controller->history();
        break;

    case 'download':
        require_once PROJECT_PATH . '/controllers/IDCardController.php';
        $controller = new \Projects\IDCard\Controllers\IDCardController();
        $id = (int) ($segments[1] ?? 0);
        if ($id) {
            $controller->download($id);
        } else {
            http_response_code(404);
            echo "Invalid ID card";
        }
        break;

    case 'edit':
        require_once PROJECT_PATH . '/controllers/IDCardController.php';
        $controller = new \Projects\IDCard\Controllers\IDCardController();
        $id = (int) ($segments[1] ?? 0);
        if ($id) {
            $controller->edit($id);
        } else {
            http_response_code(404);
            echo "Invalid ID card";
        }
        break;

    case 'delete':
        require_once PROJECT_PATH . '/controllers/IDCardController.php';
        $controller = new \Projects\IDCard\Controllers\IDCardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->delete();
        } else {
            http_response_code(405);
            echo "Method not allowed";
        }
        break;

    case 'bulk':
        require_once PROJECT_PATH . '/controllers/BulkController.php';
        $controller = new \Projects\IDCard\Controllers\BulkController();
        if ($segments[1] === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->upload();
        } elseif ($segments[1] === 'sample-csv') {
            $controller->sampleCsv();
        } elseif ($segments[1] === 'cards') {
            $controller->viewCards();
        } else {
            $controller->index();
        }
        break;

    case 'ai-suggest':
        require_once PROJECT_PATH . '/controllers/IDCardController.php';
        $controller = new \Projects\IDCard\Controllers\IDCardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->aiSuggest();
        } else {
            http_response_code(405);
            echo "Method not allowed";
        }
        break;

    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
