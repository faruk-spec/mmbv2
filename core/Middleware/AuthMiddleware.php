<?php
/**
 * Authentication Middleware
 * 
 * @package MMB\Core\Middleware
 */

namespace Core\Middleware;

use Core\Auth as AuthCore;
use Core\Helpers;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!AuthCore::check()) {
            $returnUrl = $_SERVER['REQUEST_URI'];
            Helpers::redirect('/login?return=' . urlencode($returnUrl));
            return false;
        }
        
        return true;
    }
}
