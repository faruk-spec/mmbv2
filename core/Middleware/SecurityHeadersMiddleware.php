<?php
namespace Core\Middleware;

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

        // Content Security Policy — allows same-origin scripts/styles, CDN fonts,
        // and inline styles/scripts used by the existing front-end.
        // unsafe-inline is needed because the current codebase embeds <script>/<style> blocks.
        header(
            "Content-Security-Policy: " .
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; " .
            "img-src 'self' data: blob: https:; " .
            "connect-src 'self'; " .
            "frame-ancestors 'self';"
        );

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
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
}
