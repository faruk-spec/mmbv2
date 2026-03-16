<?php
/**
 * Admin Middleware
 * 
 * @package MMB\Core\Middleware
 */

namespace Core\Middleware;

use Core\Auth;
use Core\View;

class AdminMiddleware
{
    public function handle(): bool
    {
        if (!Auth::hasAnyAdminPermission()) {
            // Silently log the user out so the admin panel's existence
            // is not revealed — the requester sees a plain 404.
            if (Auth::check()) {
                Auth::logout();
            }
            http_response_code(404);
            View::render('errors/404');
            return false;
        }

        return true;
    }
}
