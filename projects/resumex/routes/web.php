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

    case 'import':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ResumeController();
        if ($method === 'POST') {
            $ctrl->storeImport();
        } else {
            $ctrl->importForm();
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
        $sub = $segments[1] ?? '';
        if ($sub === 'upload') {
            require_once PROJECT_PATH . '/controllers/TemplateUploadController.php';
            $ctrl = new \Projects\ResumeX\Controllers\TemplateUploadController();
            if ($method === 'POST') {
                $ctrl->upload();
            } else {
                $ctrl->index();
            }
        } elseif ($sub === 'delete' && $method === 'POST') {
            require_once PROJECT_PATH . '/controllers/TemplateUploadController.php';
            $ctrl = new \Projects\ResumeX\Controllers\TemplateUploadController();
            $ctrl->delete();
        } elseif ($sub === 'sample-download') {
            // Serve the sample template PHP file as a download
            $samplePath = PROJECT_PATH . '/templates/sample-template.php';
            if (file_exists($samplePath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="sample-template.php"');
                header('Content-Length: ' . filesize($samplePath));
                readfile($samplePath);
            } else {
                http_response_code(404);
                echo 'Sample template not found.';
            }
            exit;
        } else {
            require_once PROJECT_PATH . '/controllers/TemplateController.php';
            $ctrl = new \Projects\ResumeX\Controllers\TemplateController();
            $ctrl->index();
        }
        break;

    case 'upload-image':
        require_once PROJECT_PATH . '/controllers/ImageUploadController.php';
        $ctrl = new \Projects\ResumeX\Controllers\ImageUploadController();
        if ($method === 'POST') {
            $ctrl->upload();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case 'ai':
        require_once PROJECT_PATH . '/controllers/AIController.php';
        $ctrl = new \Projects\ResumeX\Controllers\AIController();
        $action = $segments[1] ?? '';
        switch ($action) {
            case 'suggest-all':
                $ctrl->suggestAll();
                break;
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

    case 'share':
        require_once PROJECT_PATH . '/controllers/ResumeController.php';
        $ctrl  = new \Projects\ResumeX\Controllers\ResumeController();
        $token = $segments[1] ?? '';
        if ($method === 'POST' && $token === 'generate') {
            $ctrl->generateShareLink();
        } else {
            $ctrl->publicView($token);
        }
        break;

    case 'plans':
        require_once PROJECT_PATH . '/controllers/PlansController.php';
        $ctrl = new \Projects\ResumeX\Controllers\PlansController();
        $sub1 = $segments[1] ?? '';
        $sub2 = $segments[2] ?? '';
        if ($sub1 === 'invoice' && $sub2) {
            $ctrl->invoice((int)$sub2);
        } elseif ($method === 'POST') {
            $ctrl->subscribe($sub1);
        } elseif ($sub1) {
            $ctrl->subscribePage($sub1);
        } else {
            $ctrl->index();
        }
        break;

    default:
        http_response_code(404);
        echo '<h1>404 - Page not found</h1>';
        break;
}
