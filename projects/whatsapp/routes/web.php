<?php
/**
 * WhatsApp API Automation - Web Routes
 * 
 * @package MMB\Projects\WhatsApp
 */

use Core\Router;
use Core\Auth;
use Projects\WhatsApp\Helpers\SubscriptionHelper;

// Load the controllers
require_once BASE_PATH . '/projects/whatsapp/controllers/DashboardController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/SessionController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/MessageController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/ContactController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/SettingsController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/ApiDocsController.php';
require_once BASE_PATH . '/projects/whatsapp/helpers/SubscriptionHelper.php';

$waUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';
$waPath = trim((string) preg_replace('#^/projects/whatsapp/?#', '', $waUriPath), '/');
$waFirst = explode('/', $waPath)[0] ?: 'dashboard';
$waMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$waUserId = (int) (Auth::id() ?? 0);

try {
    // Web/API-keys dashboard pages can be viewed without active subscription,
    // but feature actions are enforced below.
    $alwaysAllowedPages = ['dashboard', 'subscription', 'plans'];
    $requiresActivePlan = !in_array($waFirst, $alwaysAllowedPages, true) && $waFirst !== 'api-docs' && $waFirst !== 'api';

    if ($waUserId > 0 && $requiresActivePlan) {
        $sub = SubscriptionHelper::getUserSubscription($waUserId);
        $isActive = $sub && (!empty($sub['end_date']) ? strtotime((string) $sub['end_date']) >= time() : true);
        if (!$isActive) {
            if ($waMethod !== 'GET') {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Active WhatsApp subscription required.']);
                exit;
            }
            $_SESSION['_flash']['error'] = 'Active WhatsApp subscription required.';
            header('Location: /projects/whatsapp/plans');
            exit;
        }
    }

    if ($waUserId > 0 && $waFirst === 'sessions' && $waMethod === 'POST' && str_contains($waPath, 'create')) {
        $check = SubscriptionHelper::canCreateSession($waUserId);
        if (empty($check['allowed'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $check['reason'] ?? 'Session creation is not available on your current plan.']);
            exit;
        }
    }

    if ($waUserId > 0 && $waFirst === 'messages' && $waMethod === 'POST' && str_contains($waPath, 'send')) {
        $check = SubscriptionHelper::canSendMessage($waUserId);
        if (empty($check['allowed'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $check['reason'] ?? 'Message sending is not available on your current plan.']);
            exit;
        }
    }

    if ($waUserId > 0 && $waFirst === 'api' && in_array($waMethod, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $check = SubscriptionHelper::canMakeApiCall($waUserId);
        if (empty($check['allowed'])) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => $check['reason'] ?? 'API access is not available on your current plan.']);
            exit;
        }
    }
} catch (\Throwable $e) {
    // Fail closed for mutating actions.
    if (in_array($waMethod, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'This action is not available on your current plan.']);
        exit;
    }
}

$router = new Router(false);

// Dashboard routes
$router->get('/projects/whatsapp', 'Projects\WhatsApp\Controllers\DashboardController@index');
$router->get('/projects/whatsapp/dashboard', 'Projects\WhatsApp\Controllers\DashboardController@index');

// Subscription routes
$router->get('/projects/whatsapp/subscription', 'Projects\WhatsApp\Controllers\DashboardController@subscription');
$router->get('/projects/whatsapp/plans', 'Projects\WhatsApp\Controllers\DashboardController@subscription');

// Session management routes
$router->get('/projects/whatsapp/sessions', 'Projects\WhatsApp\Controllers\SessionController@index');
$router->post('/projects/whatsapp/sessions/create', 'Projects\WhatsApp\Controllers\SessionController@create');
$router->post('/projects/whatsapp/sessions/disconnect', 'Projects\WhatsApp\Controllers\SessionController@disconnect');
$router->post('/projects/whatsapp/sessions/delete', 'Projects\WhatsApp\Controllers\SessionController@delete');
$router->get('/projects/whatsapp/sessions/qr', 'Projects\WhatsApp\Controllers\SessionController@getQRCode');
$router->get('/projects/whatsapp/sessions/status', 'Projects\WhatsApp\Controllers\SessionController@status');

// Message routes
$router->get('/projects/whatsapp/messages', 'Projects\WhatsApp\Controllers\MessageController@index');
$router->post('/projects/whatsapp/messages/send', 'Projects\WhatsApp\Controllers\MessageController@send');
$router->get('/projects/whatsapp/messages/history', 'Projects\WhatsApp\Controllers\MessageController@history');

// Contact routes
$router->get('/projects/whatsapp/contacts', 'Projects\WhatsApp\Controllers\ContactController@index');
$router->get('/projects/whatsapp/contacts/sync', 'Projects\WhatsApp\Controllers\ContactController@sync');

// Settings routes
$router->get('/projects/whatsapp/settings', 'Projects\WhatsApp\Controllers\SettingsController@index');
$router->post('/projects/whatsapp/settings/update', 'Projects\WhatsApp\Controllers\SettingsController@update');

// API keys & analytics
require_once BASE_PATH . '/projects/whatsapp/controllers/ApiKeysController.php';
$router->get('/projects/whatsapp/api',  'Projects\WhatsApp\Controllers\ApiKeysController@index');
$router->get('/projects/whatsapp/api/', 'Projects\WhatsApp\Controllers\ApiKeysController@index');
$router->post('/projects/whatsapp/api/generate', 'Projects\WhatsApp\Controllers\ApiKeysController@generate');
$router->post('/projects/whatsapp/api/revoke', 'Projects\WhatsApp\Controllers\ApiKeysController@revoke');

// API documentation
$router->get('/projects/whatsapp/api-docs', 'Projects\WhatsApp\Controllers\ApiDocsController@index');

// ── External REST API (authenticated via X-Api-Key, no session required) ───
require_once PROJECT_PATH . '/api/ApiHandler.php';
$router->post('/projects/whatsapp/api/send-message', function () {
    (new \WhatsApp\API\ApiHandler())->handle('send-message', 'POST');
});
$router->post('/projects/whatsapp/api/send-media', function () {
    (new \WhatsApp\API\ApiHandler())->handle('send-media', 'POST');
});
$router->get('/projects/whatsapp/api/messages', function () {
    (new \WhatsApp\API\ApiHandler())->handle('messages', 'GET');
});
$router->get('/projects/whatsapp/api/contacts', function () {
    (new \WhatsApp\API\ApiHandler())->handle('contacts', 'GET');
});
$router->get('/projects/whatsapp/api/status', function () {
    (new \WhatsApp\API\ApiHandler())->handle('status', 'GET');
});

// Dispatch the route
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
