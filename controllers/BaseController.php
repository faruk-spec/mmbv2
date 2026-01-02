<?php
/**
 * Base Controller
 * 
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\View;
use Core\Auth;
use Core\Security;
use Core\Helpers;

abstract class BaseController
{
    /**
     * Render a view
     */
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        // Add common data
        $data['user'] = Auth::user();
        $data['isLoggedIn'] = Auth::check();
        $data['csrf_token'] = Security::generateCsrfToken();
        
        // Set layout if provided
        if ($layout) {
            View::extend($layout);
        }
        
        View::render($view, $data);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, int $code = 200): void
    {
        Helpers::json($data, $code);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $code = 302): void
    {
        Helpers::redirect($url, $code);
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return Security::verifyCsrfToken($token);
    }
    
    /**
     * Get input value
     */
    protected function input(string $key, $default = null): mixed
    {
        return Helpers::input($key, $default);
    }
    
    /**
     * Get all input
     */
    protected function all(): array
    {
        return Helpers::all();
    }
    
    /**
     * Validate input
     */
    protected function validate(array $rules): array
    {
        $errors = Helpers::validate($_POST, $rules);
        if (!empty($errors)) {
            View::setErrors($errors);
            View::flashOldInput($_POST);
        }
        return $errors;
    }
    
    /**
     * Flash message
     */
    protected function flash(string $type, string $message): void
    {
        Helpers::flash($type, $message);
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login?return=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }
    
    /**
     * Check if user is admin
     */
    protected function requireAdmin(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }
}
