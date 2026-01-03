<?php
/**
 * Authentication Middleware
 * 
 * @package MMB\Core\Middleware
 */

namespace Core\Middleware;

use Core\Auth as AuthCore;
use Core\SessionManager;
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
        
        // Check session expiration
        if (!SessionManager::checkExpiration()) {
            // Session expired - redirect to login with message
            $_SESSION['session_expired'] = true;
            Helpers::redirect('/login?expired=1&return=' . urlencode($_SERVER['REQUEST_URI']));
            return false;
        }
        
        return true;
    }
}
