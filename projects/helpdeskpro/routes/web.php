<?php
/**
 * Helpdesk Pro Routes
 *
 * @package MMB\Projects\HelpdeskPro
 */

require_once PROJECT_PATH . '/controllers/HelpdeskController.php';
require_once PROJECT_PATH . '/controllers/TemplateController.php';
require_once PROJECT_PATH . '/controllers/AnalyticsController.php';
require_once PROJECT_PATH . '/controllers/AgentController.php';
require_once PROJECT_PATH . '/controllers/CustomerController.php';
require_once PROJECT_PATH . '/controllers/WorkflowController.php';
require_once PROJECT_PATH . '/controllers/IntegrationController.php';
require_once PROJECT_PATH . '/controllers/SettingsController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/projects/helpdeskpro', '', $uri);
$uri = $uri ?: '/';
$segments = explode('/', trim($uri, '/'));

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$first  = $segments[0] ?? '';
$second = $segments[1] ?? '';
$third  = $segments[2] ?? '';
$fourth = $segments[3] ?? '';

// -----------------------------------------------------------------------
// Dashboard
// -----------------------------------------------------------------------
if ($first === '' || $first === 'dashboard') {
    (new \Projects\HelpdeskPro\Controllers\HelpdeskController())->dashboard();
    return;
}

// -----------------------------------------------------------------------
// Tickets
// -----------------------------------------------------------------------
if ($first === 'tickets') {
    $c = new \Projects\HelpdeskPro\Controllers\HelpdeskController();
    if ($method === 'GET' && $second === '') { $c->tickets(); return; }
    if ($second === 'create' && $method === 'GET') { $c->createTicketForm(); return; }
    if ($second === 'create' && $method === 'POST') { $c->storeTicket(); return; }
    if ($second === 'view' && ctype_digit((string) $third)) { $c->viewTicket((int) $third); return; }
    if ($second === 'reply' && $method === 'POST' && ctype_digit((string) $third)) { $c->replyTicket((int) $third); return; }
    if ($second === 'status' && $method === 'POST' && ctype_digit((string) $third)) { $c->updateTicketStatus((int) $third); return; }
}

// -----------------------------------------------------------------------
// Live Support
// -----------------------------------------------------------------------
if ($first === 'live-support') {
    $c = new \Projects\HelpdeskPro\Controllers\HelpdeskController();
    if ($method === 'GET' && $second === '') { $c->liveSupport(); return; }
    if ($second === 'start' && $method === 'POST') { $c->startLiveSupport(); return; }
    if ($second === 'send' && $method === 'POST') { $c->sendLiveMessage(); return; }
}

// -----------------------------------------------------------------------
// Agent Console
// -----------------------------------------------------------------------
if ($first === 'agent') {
    $c = new \Projects\HelpdeskPro\Controllers\HelpdeskController();
    if ($second === 'live-support' && $method === 'GET') { $c->agentLiveSupport(); return; }
    if ($second === 'live-support' && $third === 'reply' && $method === 'POST' && ctype_digit((string) $fourth)) {
        $c->agentReplyLive((int) $fourth);
        return;
    }
}

// -----------------------------------------------------------------------
// Templates
// -----------------------------------------------------------------------
if ($first === 'templates') {
    $c = new \Projects\HelpdeskPro\Controllers\TemplateController();

    if ($second === '' && $method === 'GET') { $c->index(); return; }

    // Category routes
    if ($second === 'categories') {
        if ($third === 'create' && $method === 'POST') { $c->createCategory(); return; }
        if ($third === 'delete' && ctype_digit((string) $fourth) && $method === 'POST') { $c->deleteCategory((int) $fourth); return; }
        if (ctype_digit((string) $third) && $fourth === 'update' && $method === 'POST') { $c->updateCategory((int) $third); return; }
    }
    if ($second === 'category' && ctype_digit((string) $third) && $method === 'GET') { $c->viewCategory((int) $third); return; }

    // Subcategory routes
    if ($second === 'subcategories') {
        if ($third === 'create' && $method === 'POST') { $c->createSubcategory(); return; }
        if ($third === 'delete' && ctype_digit((string) $fourth) && $method === 'POST') { $c->deleteSubcategory((int) $fourth); return; }
    }
    if ($second === 'subcategory' && ctype_digit((string) $third) && $method === 'GET') { $c->viewSubcategory((int) $third); return; }

    // Item routes
    if ($second === 'items') {
        if ($third === 'create' && $method === 'POST') { $c->createItem(); return; }
        if ($third === 'delete' && ctype_digit((string) $fourth) && $method === 'POST') { $c->deleteItem((int) $fourth); return; }
    }
    if ($second === 'item' && ctype_digit((string) $third) && $method === 'GET') { $c->viewItem((int) $third); return; }

    // Field routes
    if ($second === 'fields') {
        if ($third === 'create' && $method === 'POST') { $c->createField(); return; }
        if ($third === 'delete' && ctype_digit((string) $fourth) && $method === 'POST') { $c->deleteField((int) $fourth); return; }
    }
}

// -----------------------------------------------------------------------
// Analytics
// -----------------------------------------------------------------------
if ($first === 'analytics' && $method === 'GET') {
    (new \Projects\HelpdeskPro\Controllers\AnalyticsController())->index();
    return;
}

// -----------------------------------------------------------------------
// Agents
// -----------------------------------------------------------------------
if ($first === 'agents' && $method === 'GET') {
    (new \Projects\HelpdeskPro\Controllers\AgentController())->index();
    return;
}

// -----------------------------------------------------------------------
// Customers
// -----------------------------------------------------------------------
if ($first === 'customers') {
    $c = new \Projects\HelpdeskPro\Controllers\CustomerController();
    if ($second === '' && $method === 'GET') { $c->index(); return; }
    if ($second === 'view' && ctype_digit((string) $third) && $method === 'GET') { $c->view((int) $third); return; }
}

// -----------------------------------------------------------------------
// Workflows
// -----------------------------------------------------------------------
if ($first === 'workflows') {
    $c = new \Projects\HelpdeskPro\Controllers\WorkflowController();
    if ($second === '' && $method === 'GET') { $c->index(); return; }
    if ($second === 'create' && $method === 'POST') { $c->create(); return; }
    if ($second === 'toggle' && ctype_digit((string) $third) && $method === 'POST') { $c->toggle((int) $third); return; }
    if ($second === 'delete' && ctype_digit((string) $third) && $method === 'POST') { $c->delete((int) $third); return; }
}

// -----------------------------------------------------------------------
// Integrations
// -----------------------------------------------------------------------
if ($first === 'integrations') {
    $c = new \Projects\HelpdeskPro\Controllers\IntegrationController();
    if ($second === '' && $method === 'GET') { $c->index(); return; }
    if ($second === 'api-keys' && $third === 'create' && $method === 'POST') { $c->createApiKey(); return; }
    if ($second === 'api-keys' && $third === 'revoke' && ctype_digit((string) $fourth) && $method === 'POST') { $c->revokeApiKey((int) $fourth); return; }
    if ($second === 'widget' && $third === 'save' && $method === 'POST') { $c->saveWidgetSettings(); return; }
    if ($second === 'webhooks' && $third === 'create' && $method === 'POST') { $c->createWebhook(); return; }
    if ($second === 'webhooks' && $third === 'delete' && ctype_digit((string) $fourth) && $method === 'POST') { $c->deleteWebhook((int) $fourth); return; }
}

// -----------------------------------------------------------------------
// Settings
// -----------------------------------------------------------------------
if ($first === 'settings') {
    $c = new \Projects\HelpdeskPro\Controllers\SettingsController();
    if ($second === '' && $method === 'GET') { $c->index(); return; }
    if ($second === 'sla' && $method === 'POST') { $c->saveSla(); return; }
}

http_response_code(404);
echo '<p style="font-family:system-ui;padding:2rem;color:#6b7280;">Helpdesk Pro page not found.</p>';

