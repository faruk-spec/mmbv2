<?php
/**
 * Mail Subdomain Bootstrap (Future Use)
 *
 * Currently the mail system is served at the main domain under /mail.
 * When you are ready to point mail.yourdomain.in to its own server:
 *   1. Copy this entire subdomain/mail/ directory to the subdomain web root.
 *   2. Set BASE_PATH_OVERRIDE below (or environment variable MAIN_APP_PATH)
 *      to the absolute path of the main platform installation.
 *   3. Ensure the subdomain shares the same database and session cookie domain.
 *
 * Until then, this file simply redirects to the main domain's /mail path.
 */

// ── Redirect to main domain /mail while subdomain is not yet active ──────────
// Remove this block once the subdomain DNS and web root are configured.
$mainUrl = getenv('APP_URL') ?: 'https://yourdomain.in';
$mainUrl = rtrim($mainUrl, '/');

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
// Strip leading /mail prefix if the subdomain still receives those paths
$subPath = preg_replace('#^/mail#', '', $uri ?: '/') ?: '/';
// Reconstruct the target URL on the main domain
$qs       = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
$redirect = $mainUrl . '/mail' . ($subPath === '/' ? '' : $subPath) . $qs;

header('Location: ' . $redirect, true, 302);
exit;

// ── Standalone mode (uncomment when subdomain is ready) ──────────────────────
/*
$mainRoot = getenv('MAIN_APP_PATH') ?: dirname(__DIR__, 2);
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $mainRoot);
}

require_once BASE_PATH . '/core/Autoloader.php';
require_once BASE_PATH . '/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

use Core\Auth;

if (!Auth::check()) {
    $returnUrl = rtrim(APP_URL, '/') . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $returnUrl);
    exit;
}

// Route: re-use the main platform's bootstrap and dispatch via the same router.
// All /mail/* routes are already defined in routes/web.php; run them via index.php.
$_SERVER['REQUEST_URI'] = '/mail' . (preg_replace('#^/mail#', '', parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)) ?: '/');
require_once BASE_PATH . '/index.php';
*/
