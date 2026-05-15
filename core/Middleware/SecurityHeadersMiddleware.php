<?php
namespace Core\Middleware;

use Core\Security;

class SecurityHeadersMiddleware
{
    public static function handle(): void
    {
        if (headers_sent()) return;

        // Remove server-fingerprinting headers
        header_remove('X-Powered-By');
        header_remove('Server');

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        $nonce = Security::getCspNonce();
        if (self::usesStrictAuthCsp($requestPath)) {
            header(
                "Content-Security-Policy: " .
                "default-src 'self'; " .
                "script-src 'self' 'nonce-{$nonce}'; " .
                "script-src-attr 'none'; " .
                "style-src 'self' 'nonce-{$nonce}'; " .
                "style-src-attr 'none'; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' data:; " .
                "connect-src 'self'; " .
                "frame-src 'none'; " .
                "frame-ancestors 'self'; " .
                "form-action 'self'; " .
                "base-uri 'self'; " .
                "object-src 'none'; " .
                "upgrade-insecure-requests;"
            );
        } else {
            header(
                "Content-Security-Policy: " .
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://sdk.cashfree.com https://*.cashfree.com; " .
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
                "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; " .
                "img-src 'self' data: blob: https:; " .
                "connect-src 'self' https://sdk.cashfree.com https://*.cashfree.com; " .
                "frame-src 'self' https://sdk.cashfree.com https://*.cashfree.com; " .
                "frame-ancestors 'self'; " .
                "form-action 'self' https://*.cashfree.com; " .
                "base-uri 'self'; " .
                "object-src 'none'; " .
                "upgrade-insecure-requests;"
            );
        }

        $isHttps = self::isHttpsRequest();
        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // For authenticated sessions: add identity headers visible in DevTools Network tab
        // and prevent sensitive responses from being stored in intermediary caches.
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
            // Expose the user's unique identifier and role in response headers so the
            // browser DevTools Network inspector shows who the request belongs to.
            $uid = $_SESSION['user_unique_id'] ?? (string) $_SESSION['user_id'];
            header('X-Auth-User: ' . $uid);

            $role = $_SESSION['user_role'] ?? 'user';
            header('X-Auth-Role: ' . $role);

            // Prevent authenticated page responses from being stored in shared caches.
            header('Cache-Control: private, no-store, must-revalidate');
            header('Pragma: no-cache');

            // Prevent search engines and crawlers from indexing authenticated pages
            // or caching snapshots of them.
            header('X-Robots-Tag: noindex, nofollow, nosnippet, noarchive');
        }
    }

    public static function isHttpsRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        $forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
        if ($forwardedProto !== '') {
            $parts = array_map('trim', explode(',', $forwardedProto));
            if (in_array('https', $parts, true)) {
                return true;
            }
        }

        if (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')) === 'on') {
            return true;
        }

        if (strtolower((string) ($_SERVER['REQUEST_SCHEME'] ?? '')) === 'https') {
            return true;
        }

        $cfVisitor = (string) ($_SERVER['HTTP_CF_VISITOR'] ?? '');
        if ($cfVisitor !== '' && str_contains($cfVisitor, '"scheme":"https"')) {
            return true;
        }

        return false;
    }

    private static function usesStrictAuthCsp(string $path): bool
    {
        return in_array($path, [
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/verify-otp',
            '/2fa-verify',
        ], true);
    }
}
