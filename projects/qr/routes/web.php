<?php
/**
 * QR Generator Routes
 * 
 * @package MMB\Projects\QR
 */

use Core\Router;
use Core\View;
use Core\Auth;

// Simple project router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/projects/qr', '', $uri);
$uri = $uri ?: '/';

// Extract ID from URL if present (e.g., /view/123, /edit/123)
$segments = explode('/', trim($uri, '/'));

// Route handling
switch ($segments[0]) {
    case '':
    case 'dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        $controller = new \Projects\QR\Controllers\DashboardController();
        $controller->index();
        break;
        
    case 'generate':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->generate();
        } else {
            $controller->showForm();
        }
        break;
        
    case 'history':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->history();
        break;
        
    case 'view':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $id = (int) ($segments[1] ?? 0);
        if ($id) {
            $controller->view($id);
        } else {
            http_response_code(404);
            echo "Invalid QR code ID";
        }
        break;
        
    case 'edit':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $id = (int) ($segments[1] ?? 0);
        if ($id) {
            $controller->edit($id);
        } else {
            http_response_code(404);
            echo "Invalid QR code ID";
        }
        break;
        
    case 'update':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $id = (int) ($segments[1] ?? 0);
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update($id);
        } else {
            http_response_code(404);
            echo "Invalid request";
        }
        break;
        
    case 'download':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->download();
        break;
        
    case 'delete':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->delete();
        break;
        
    case 'bulk-delete':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->bulkDelete();
        break;
        
    case 'update-campaign':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->updateCampaign();
        break;
        
    case 'scan':
    case 'access':
        // Handle QR code scanning/access with password and expiry verification
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $code = $segments[1] ?? '';
        if ($code) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->verifyAccess($code);
            } else {
                $controller->showAccessForm($code);
            }
        } else {
            http_response_code(404);
            echo "Invalid QR code";
        }
        break;
        
    case 'analytics':
        require_once PROJECT_PATH . '/controllers/AnalyticsController.php';
        $controller = new \Projects\QR\Controllers\AnalyticsController();
        
        // Handle nested routes
        if (isset($pathParts[4])) {
            $action = $pathParts[4];
            switch ($action) {
                case 'export-csv':
                    $controller->exportCsv();
                    break;
                case 'scan-trends':
                    $controller->scanTrends();
                    break;
                case 'top-qrs':
                    $controller->topQRs();
                    break;
                default:
                    $controller->index();
                    break;
            }
        } else {
            $controller->index();
        }
        break;
        
    case 'campaigns':
        require_once PROJECT_PATH . '/controllers/CampaignsController.php';
        $controller = new \Projects\QR\Controllers\CampaignsController();
        
        // Handle nested routes
        if (isset($segments[1])) {
            switch ($segments[1]) {
                case 'create':
                    $controller->create();
                    break;
                case 'edit':
                    $controller->edit();
                    break;
                case 'view':
                    $controller->view();
                    break;
                case 'delete':
                    $controller->delete();
                    break;
                default:
                    http_response_code(404);
                    echo "Not found";
            }
        } else {
            $controller->index();
        }
        break;
        
    case 'bulk-print':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->bulkPrint();
        break;
        
    case 'bulk':
        require_once PROJECT_PATH . '/controllers/BulkController.php';
        $controller = new \Projects\QR\Controllers\BulkController();
        
        // Handle nested routes
        if (isset($segments[1])) {
            switch ($segments[1]) {
                case 'upload':
                    $controller->upload();
                    break;
                case 'generate':
                    $controller->generate();
                    break;
                case 'sample':
                    $controller->downloadSample();
                    break;
                default:
                    http_response_code(404);
                    echo "Not found";
            }
        } else {
            $controller->index();
        }
        break;
        
    case 'templates':
        require_once PROJECT_PATH . '/controllers/TemplatesController.php';
        $controller = new \Projects\QR\Controllers\TemplatesController();
        
        // Handle nested routes
        if (isset($segments[1])) {
            switch ($segments[1]) {
                case 'create':
                    $controller->create();
                    break;
                case 'get':
                    $controller->get();
                    break;
                case 'update':
                    $controller->update();
                    break;
                case 'delete':
                    $controller->delete();
                    break;
                default:
                    http_response_code(404);
                    echo "Not found";
            }
        } else {
            $controller->index();
        }
        break;
        
    case 'settings':
        require_once PROJECT_PATH . '/controllers/SettingsController.php';
        $controller = new \Projects\QR\Controllers\SettingsController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->index();
        }
        break;

    case 'plan':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        $controller = new \Projects\QR\Controllers\DashboardController();
        $controller->plan();
        break;

    case 'api':
        require_once PROJECT_PATH . '/controllers/QRApiUserController.php';
        $controller = new \Projects\QR\Controllers\QRApiUserController();
        if (isset($segments[1]) && $segments[1] === 'revoke') {
            $controller->revoke();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->generate();
        } else {
            $controller->index();
        }
        break;

    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
