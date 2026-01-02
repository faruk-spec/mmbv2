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

// Route handling
switch ($uri) {
    case '/':
    case '/dashboard':
        require_once PROJECT_PATH . '/controllers/DashboardController.php';
        $controller = new \Projects\QR\Controllers\DashboardController();
        $controller->index();
        break;
        
    case '/generate':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->generate();
        } else {
            $controller->showForm();
        }
        break;
        
    case '/history':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->history();
        break;
        
    case '/download':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->download();
        break;
        
    case '/delete':
        require_once PROJECT_PATH . '/controllers/QRController.php';
        $controller = new \Projects\QR\Controllers\QRController();
        $controller->delete();
        break;
        
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
