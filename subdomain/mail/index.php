<?php
/**
 * Mail Subdomain Entry Point
 *
 * Deploy this entire /subdomain/mail/ directory to mail.yourdomain.in.
 * It reuses the main platform's core library, database, and session.
 *
 * Directory structure (relative to this file):
 *   index.php          ← this file (router + bootstrap)
 *   controllers/
 *     InboxController.php
 *   views/
 *     layout.php
 *     inbox.php
 *     view.php
 *     compose.php
 *     settings.php
 *
 * Apache / Nginx: point DocumentRoot (or Alias) to this directory.
 * All requests should be routed to index.php (see .htaccess below).
 */

// ---------------------------------------------------------------
// Bootstrap – locate the main platform root
// ---------------------------------------------------------------
$mainRoot = dirname(__DIR__, 2); // two levels up from subdomain/mail/
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $mainRoot);
}

// Load core autoloader & app config
require_once BASE_PATH . '/core/Autoloader.php';
require_once BASE_PATH . '/config/app.php';

// ---------------------------------------------------------------
// Session (reuse platform session)
// ---------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

use Core\Auth;
use Core\Security;

// ---------------------------------------------------------------
// Auth guard – redirect to main platform login
// ---------------------------------------------------------------
if (!Auth::check()) {
    $returnUrl = rtrim(APP_URL, '/') . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $returnUrl);
    exit;
}

// ---------------------------------------------------------------
// Minimal router
// ---------------------------------------------------------------
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri    = '/' . ltrim($uri, '/');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Strip base path prefix if deployed under a sub-path
// e.g. if served from /mail/* instead of root
$uriClean = preg_replace('#^/mail#', '', $uri) ?: '/';

require_once __DIR__ . '/controllers/InboxController.php';
$ctrl = new \Mail\InboxController();

// Route table
if ($uriClean === '/' || $uriClean === '/inbox') {
    if ($method === 'POST') {
        $ctrl->syncAndRedirect();
    } else {
        $ctrl->inbox();
    }
} elseif (preg_match('#^/view/(\d+)$#', $uriClean, $m)) {
    $ctrl->view((int)$m[1]);
} elseif ($uriClean === '/compose') {
    if ($method === 'POST') {
        $ctrl->send();
    } else {
        $ctrl->compose();
    }
} elseif ($uriClean === '/reply' && $method === 'POST') {
    $ctrl->reply();
} elseif ($uriClean === '/forward' && $method === 'POST') {
    $ctrl->forward();
} elseif ($uriClean === '/mark-read' && $method === 'POST') {
    $ctrl->markRead();
} elseif ($uriClean === '/delete' && $method === 'POST') {
    $ctrl->delete();
} elseif ($uriClean === '/archive' && $method === 'POST') {
    $ctrl->archive();
} elseif ($uriClean === '/star' && $method === 'POST') {
    $ctrl->star();
} elseif ($uriClean === '/search') {
    $ctrl->search();
} elseif ($uriClean === '/settings') {
    if ($method === 'POST') {
        $ctrl->saveSettings();
    } else {
        $ctrl->settings();
    }
} elseif ($uriClean === '/sync' && $method === 'POST') {
    $ctrl->syncAjax();
} else {
    http_response_code(404);
    echo '<h1>404 – Page not found</h1>';
}
