<?php
/**
 * DevZone Routes
 *
 * @package MMB\Projects\DevZone
 */

use Core\Auth;

$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = rtrim(str_replace('/projects/devzone', '', $uri), '/') ?: '/';
$segments = explode('/', trim($uri, '/'));
$method   = $_SERVER['REQUEST_METHOD'];

// ── Require authenticated user ───────────────────────────────────────────
if (!Auth::check()) {
    \Core\SSO::redirectToLogin($_SERVER['REQUEST_URI']);
}

// ── Autoload controllers ─────────────────────────────────────────────────
require_once PROJECT_PATH . '/controllers/DashboardController.php';
require_once PROJECT_PATH . '/controllers/BoardController.php';
require_once PROJECT_PATH . '/controllers/TaskController.php';

$dashCtrl  = new \Projects\DevZone\Controllers\DashboardController();
$boardCtrl = new \Projects\DevZone\Controllers\BoardController();
$taskCtrl  = new \Projects\DevZone\Controllers\TaskController();

// ── Route matching ───────────────────────────────────────────────────────

switch ($segments[0]) {

    // ── Dashboard ────────────────────────────────────────────────────────
    case '':
    case 'dashboard':
        $dashCtrl->index();
        break;

    // ── Settings ─────────────────────────────────────────────────────────
    case 'settings':
        $method === 'POST' ? $dashCtrl->updateSettings() : $dashCtrl->settings();
        break;

    // ── Boards ───────────────────────────────────────────────────────────
    case 'boards':
        $seg1 = $segments[1] ?? '';
        $seg2 = $segments[2] ?? '';

        if ($seg1 === '') {
            // GET /boards
            $boardCtrl->index();
        } elseif ($seg1 === 'create') {
            // GET /boards/create
            $boardCtrl->create();
        } elseif ($seg1 === 'store') {
            // POST /boards/store
            $boardCtrl->store();
        } elseif (ctype_digit($seg1) && $seg2 === '') {
            // GET /boards/{id}
            $boardCtrl->show((int)$seg1);
        } elseif (ctype_digit($seg1) && $seg2 === 'edit') {
            // GET /boards/{id}/edit
            $boardCtrl->edit((int)$seg1);
        } elseif (ctype_digit($seg1) && $seg2 === 'update') {
            // POST /boards/{id}/update
            $boardCtrl->update((int)$seg1);
        } elseif (ctype_digit($seg1) && $seg2 === 'delete') {
            // POST /boards/{id}/delete
            $boardCtrl->delete((int)$seg1);
        } else {
            http_response_code(404);
            echo '<p style="font-family:sans-serif;padding:2rem;color:#888;">Page not found.</p>';
        }
        break;

    // ── Tasks ────────────────────────────────────────────────────────────
    case 'tasks':
        $seg1 = $segments[1] ?? '';
        $seg2 = $segments[2] ?? '';

        if ($seg1 === '') {
            // GET /tasks
            $taskCtrl->index();
        } elseif ($seg1 === 'store') {
            // POST /tasks/store
            $taskCtrl->store();
        } elseif (ctype_digit($seg1) && $seg2 === 'update') {
            // POST /tasks/{id}/update
            $taskCtrl->update((int)$seg1);
        } elseif (ctype_digit($seg1) && $seg2 === 'delete') {
            // POST /tasks/{id}/delete
            $taskCtrl->delete((int)$seg1);
        } else {
            http_response_code(404);
            echo '<p style="font-family:sans-serif;padding:2rem;color:#888;">Page not found.</p>';
        }
        break;

    default:
        http_response_code(404);
        echo '<p style="font-family:sans-serif;padding:2rem;color:#888;">DevZone page not found.</p>';
        break;
}
