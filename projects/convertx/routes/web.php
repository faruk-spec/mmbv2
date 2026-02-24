<?php
/**
 * ConvertX Routes
 *
 * @package MMB\Projects\ConvertX
 */

use Core\Auth;

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = str_replace('/projects/convertx', '', $uri);
$uri      = $uri ?: '/';
$segments = explode('/', trim($uri, '/'));

switch ($segments[0]) {
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        (new \Projects\ConvertX\Controllers\DashboardController())->index();
        break;

    case 'convert':
        require_once PROJECT_PATH . '/controllers/ConversionController.php';
        $ctrl = new \Projects\ConvertX\Controllers\ConversionController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submit();
        } else {
            $ctrl->showForm();
        }
        break;

    case 'job':
        require_once PROJECT_PATH . '/controllers/ConversionController.php';
        $ctrl = new \Projects\ConvertX\Controllers\ConversionController();
        $id   = (int) ($segments[1] ?? 0);
        $action = $segments[2] ?? 'status';
        if ($id) {
            match ($action) {
                'download' => $ctrl->download($id),
                'cancel'   => $ctrl->cancel($id),
                default    => $ctrl->status($id),
            };
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Job not found']);
        }
        break;

    case 'history':
        require_once PROJECT_PATH . '/controllers/ConversionController.php';
        (new \Projects\ConvertX\Controllers\ConversionController())->history();
        break;

    case 'batch':
        require_once PROJECT_PATH . '/controllers/BatchController.php';
        $ctrl = new \Projects\ConvertX\Controllers\BatchController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->submit();
        } else {
            $ctrl->showForm();
        }
        break;

    case 'api':
        require_once PROJECT_PATH . '/controllers/ApiController.php';
        $ctrl   = new \Projects\ConvertX\Controllers\ApiController();
        $action = $segments[1] ?? '';
        match ($action) {
            'convert'  => $ctrl->convert(),
            'status'   => $ctrl->jobStatus($segments[2] ?? ''),
            'download' => $ctrl->download($segments[2] ?? ''),
            'history'  => $ctrl->history(),
            'usage'    => $ctrl->usage(),
            default    => $ctrl->index(),
        };
        break;

    case 'docs':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        (new \Projects\ConvertX\Controllers\DashboardController())->docs();
        break;

    case 'plan':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        (new \Projects\ConvertX\Controllers\DashboardController())->plan();
        break;

    case 'settings':
        require_once PROJECT_PATH . '/controllers/SettingsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\SettingsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->update();
        } else {
            $ctrl->index();
        }
        break;

    case 'apikeys':
        require_once PROJECT_PATH . '/controllers/SettingsController.php';
        $ctrl = new \Projects\ConvertX\Controllers\SettingsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->update();
        } else {
            $ctrl->apikeys();
        }
        break;

    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}
