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

switch ($segments[0]) {

    // Dashboard
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        $ctrl = new \Projects\FormX\Controllers\DashboardController();
        $ctrl->index();
        break;

    // My Forms list
    case 'forms':
        require_once PROJECT_PATH . '/controllers/FormController.php';
        $ctrl = new \Projects\FormX\Controllers\FormController();
        $ctrl->index();
        break;

    // Create new form
    case 'create':
        require_once PROJECT_PATH . '/controllers/FormController.php';
        $ctrl = new \Projects\FormX\Controllers\FormController();
        if ($method === 'POST') {
            $ctrl->save();
        } else {
            $ctrl->create();
        }
        break;

    // Edit / update / delete / submissions for a specific form
    default:
        if (is_numeric($segments[0])) {
            $id     = (int) $segments[0];
            $action = $segments[1] ?? 'edit';

            require_once PROJECT_PATH . '/controllers/FormController.php';
            $ctrl = new \Projects\FormX\Controllers\FormController();

            switch ($action) {
                case 'edit':
                    if ($method === 'POST') {
                        $ctrl->update($id);
                    } else {
                        $ctrl->edit($id);
                    }
                    break;
                case 'delete':
                    $ctrl->delete($id);
                    break;
                case 'duplicate':
                    $ctrl->duplicate($id);
                    break;
                case 'submissions':
                    $ctrl->submissions($id);
                    break;
                default:
                    http_response_code(404);
                    echo '<h1>404 Not Found</h1>';
            }
        } else {
            http_response_code(404);
            echo '<h1>404 Not Found</h1>';
        }
        break;
}
