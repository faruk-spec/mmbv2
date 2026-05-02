<?php
namespace Core\Middleware;

use Core\Security;
use Core\Helpers;

class CsrfMiddleware
{
    /** Methods that must carry a valid CSRF token */
    private const PROTECTED_METHODS = ['POST', 'PUT', 'DELETE', 'PATCH'];

    /** URI prefixes that are exempt (e.g. webhook endpoints) */
    private const EXEMPT_PREFIXES = ['/api/'];

    public static function handle(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, self::PROTECTED_METHODS, true)) {
            return true;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        foreach (self::EXEMPT_PREFIXES as $prefix) {
            if (str_starts_with($uri, $prefix)) {
                return true;
            }
        }

        // Exempt project API sub-routes which are authenticated via X-Api-Key,
        // not CSRF tokens.  Matches paths like /projects/qr/api/generate and
        // the short-URL equivalents /qr/api/generate.
        $path = parse_url($uri, PHP_URL_PATH) ?? $uri;
        if (preg_match('#/api(?:/|$)#', $path)) {
            return true;
        }

        $token = $_POST['_csrf_token']
            ?? $_POST['_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        if (!Security::validateCsrfToken($token)) {
            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
                || str_starts_with($uri, '/api/')
            ) {
                Helpers::json(['error' => 'CSRF token mismatch'], 419);
            } else {
                Helpers::flash('error', 'Your session has expired or the request was invalid. Please try again.');
                // Validate referer is from the same origin to prevent open redirect
                $referer = $_SERVER['HTTP_REFERER'] ?? '/';
                $host = $_SERVER['HTTP_HOST'] ?? '';
                $refererHost = parse_url($referer, PHP_URL_HOST) ?? '';
                if (!empty($refererHost) && $refererHost !== $host) {
                    $referer = '/';
                }
                Helpers::redirect($referer);
            }
            return false;
        }

        return true;
    }
}
