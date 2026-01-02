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
        if (!Auth::isAdmin()) {
            http_response_code(403);
            View::render('errors/403');
            return false;
        }
        
        return true;
    }
}
