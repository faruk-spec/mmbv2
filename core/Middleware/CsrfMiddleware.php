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

        $token = $_POST['_csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        if (!Security::validateCsrfToken($token)) {
            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
                || str_starts_with($uri, '/api/')
            ) {
                Helpers::json(['error' => 'CSRF token mismatch'], 419);
            } else {
                Helpers::flash('error', 'Your session has expired or the request was invalid. Please try again.');
                $referer = $_SERVER['HTTP_REFERER'] ?? '/';
                Helpers::redirect($referer);
            }
            return false;
        }

        return true;
    }
}
