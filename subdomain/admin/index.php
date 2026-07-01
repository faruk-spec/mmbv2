<?php
/**
 * Admin Subdomain Bootstrap
 *
 * Provides a backup entry point to the admin dashboard via a dedicated subdomain
 * (e.g. admin.yourdomain.com).  This is useful when the main domain or its web
 * server config is broken — you can still reach the admin panel from here to
 * restore or fix things.
 *
 * Setup
 * ─────
 * 1. Configure your web server to serve admin.yourdomain.com from this directory
 *    (or from the main public/ directory directly — either works).
 * 2. Point the subdomain's DNS A/CNAME record to the same server IP.
 * 3. Ensure the subdomain shares the same database and session cookie domain
 *    as the main application.
 * 4. Set the BASE_PATH_OVERRIDE env variable (or edit the constant below) to
 *    the absolute path of the main platform installation when using standalone
 *    mode.
 * 5. Register the admin subdomain URL in Admin → Deployment → Subdomain so the
 *    dashboard always shows the correct link.
 *
 * How it works
 * ────────────
 * In redirect mode (default) this file simply forwards the browser to
 * /admin on the main domain.  Switch to standalone mode once the subdomain
 * DNS and web-server vhost are fully configured.
 */

// ── Redirect mode (active by default) ────────────────────────────────────────
// Change to standalone mode by commenting this block out and uncommenting the
// standalone section below.

$mainUrl = getenv('APP_URL') ?: 'https://yourdomain.com';
$mainUrl = rtrim($mainUrl, '/');

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$qs     = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';

// If the request is not already under /admin, prepend it.
if ($uri !== '/admin' && !str_starts_with($uri, '/admin/')) {
    $uri = '/admin';
}

header('Location: ' . $mainUrl . $uri . $qs, true, 302);
exit;

// ── Standalone mode (uncomment when subdomain DNS + vhost are ready) ─────────
/*
$mainRoot = getenv('MAIN_APP_PATH') ?: dirname(__DIR__, 2);
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $mainRoot);
}

// Bootstrap the main application
require_once BASE_PATH . '/core/Autoloader.php';
require_once BASE_PATH . '/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings — ensure the cookie works on the subdomain
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure',   isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');

    // Allow the session cookie to be shared across the apex domain and all subdomains
    // e.g. .yourdomain.com  (leading dot is required)
    // ini_set('session.cookie_domain', '.' . ltrim(defined('APP_DOMAIN') ? APP_DOMAIN : '', '.'));

    session_start();
}

use Core\Auth;

// Require admin authentication
if (!Auth::check()) {
    $returnUrl = rtrim(APP_URL, '/') . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/admin');
    header('Location: ' . $returnUrl);
    exit;
}

// Rewrite the request URI so the main router sees /admin/…
$subPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (!str_starts_with($subPath, '/admin')) {
    $subPath = '/admin' . $subPath;
}
$_SERVER['REQUEST_URI'] = $subPath . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');

// Dispatch via the main application router
require_once BASE_PATH . '/index.php';
*/
