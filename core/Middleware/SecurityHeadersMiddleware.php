<?php
namespace Core\Middleware;

class SecurityHeadersMiddleware
{
    public static function handle(): void
    {
        if (headers_sent()) return;

        // Remove server-fingerprinting headers
        header_remove('X-Powered-By');

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
    }
}
