<?php
/**
 * Helpdesk Pro Routes
 *
 * @package MMB\Projects\HelpdeskPro
 */

require_once PROJECT_PATH . '/controllers/HelpdeskController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/projects/helpdeskpro', '', $uri);
$uri = $uri ?: '/';
$segments = explode('/', trim($uri, '/'));

$controller = new \Projects\HelpdeskPro\Controllers\HelpdeskController();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$first = $segments[0] ?? '';
$second = $segments[1] ?? '';
$third = $segments[2] ?? '';
$fourth = $segments[3] ?? '';

if ($first === '' || $first === 'dashboard') {
    $controller->dashboard();
    return;
}

if ($first === 'tickets' && $method === 'GET' && $second === '') {
    $controller->tickets();
    return;
}

if ($first === 'tickets' && $second === 'create' && $method === 'GET') {
    $controller->createTicketForm();
    return;
}

if ($first === 'tickets' && $second === 'create' && $method === 'POST') {
    $controller->storeTicket();
    return;
}

if ($first === 'tickets' && $second === 'view' && ctype_digit((string) $third)) {
    $controller->viewTicket((int) $third);
    return;
}

if ($first === 'tickets' && $second === 'reply' && $method === 'POST' && ctype_digit((string) $third)) {
    $controller->replyTicket((int) $third);
    return;
}

if ($first === 'tickets' && $second === 'status' && $method === 'POST' && ctype_digit((string) $third)) {
    $controller->updateTicketStatus((int) $third);
    return;
}

if ($first === 'live-support' && $method === 'GET') {
    $controller->liveSupport();
    return;
}

if ($first === 'live-support' && $second === 'start' && $method === 'POST') {
    $controller->startLiveSupport();
    return;
}

if ($first === 'live-support' && $second === 'send' && $method === 'POST') {
    $controller->sendLiveMessage();
    return;
}

if ($first === 'agent' && $second === 'live-support' && $method === 'GET') {
    $controller->agentLiveSupport();
    return;
}

if ($first === 'agent' && $second === 'live-support' && $third === 'reply' && $method === 'POST' && ctype_digit((string) $fourth)) {
    $controller->agentReplyLive((int) $fourth);
    return;
}

http_response_code(404);
echo '<p style="font-family:system-ui;padding:2rem;color:#6b7280;">Helpdesk Pro page not found.</p>';
