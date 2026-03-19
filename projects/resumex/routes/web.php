<?php
/**
 * ResumeX Project Routes
 *
 * @package MMB\Projects\ResumeX
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/projects/resumex', '', $uri);
$uri = $uri ?: '/';

$segments = explode('/', trim($uri, '/'));
$method   = $_SERVER['REQUEST_METHOD'];

switch ($segments[0]) {
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        $ctrl = new \Projects\ResumeX\Controllers\DashboardController();
        $ctrl->index();
        break;

    case 'create':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ResumeController();
        if ($method === 'POST') {
            $ctrl->store();
        } else {
            $ctrl->create();
        }
        break;

    case 'edit':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ResumeController();
        $id = (int) ($segments[1] ?? 0);
        if ($method === 'POST') {
            $ctrl->save($id);
        } else {
            $ctrl->edit($id);
        }
        break;

    case 'preview':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ResumeController();
        $id = (int) ($segments[1] ?? 0);
        $ctrl->preview($id);
        break;

    case 'delete':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ResumeController();
        $ctrl->delete();
        break;

    case 'duplicate':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ResumeController();
        $ctrl->duplicate();
        break;

    case 'download':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ResumeController();
        $id = (int) ($segments[1] ?? 0);
        $ctrl->download($id);
        break;

    case 'templates':
        require_once PROJECT_PATH . '/controllers/TemplateController.php';
        $ctrl = new \Projects\ResumeX\Controllers\TemplateController();
        $ctrl->index();
        break;

    case 'ai':
        require_once PROJECT_PATH . '/controllers/AIController.php';
        $ctrl = new \Projects\ResumeX\Controllers\AIController();
        $action = $segments[1] ?? '';
        switch ($action) {
            case 'suggest-summary':
                $ctrl->suggestSummary();
                break;
            case 'suggest-skills':
                $ctrl->suggestSkills();
                break;
            case 'suggest-bullets':
                $ctrl->suggestBullets();
                break;
            case 'score':
                $ctrl->score();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Not found']);
        }
        break;

    default:
        http_response_code(404);
        echo '<h1>404 - Page not found</h1>';
        break;
}
