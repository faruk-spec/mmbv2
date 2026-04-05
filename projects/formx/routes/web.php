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
                    if (isset($segments[2]) && is_numeric($segments[2])) {
                        $subId = (int) $segments[2];
                        $ctrl->viewSubmission($id, $subId);
                    } else {
                        $ctrl->submissions($id);
                    }
                    break;
                case 'analytics':
                    $ctrl->analytics($id);
                    break;
                case 'versions':
                    if (isset($segments[2]) && is_numeric($segments[2]) && isset($segments[3]) && $segments[3] === 'restore') {
                        $ctrl->restoreVersion($id, (int)$segments[2]);
                    } else {
                        $ctrl->versions($id);
                    }
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
