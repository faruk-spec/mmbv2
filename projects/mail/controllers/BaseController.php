<?php
/**
 * Base Controller for Mail Project
 * 
 * @package Projects\Mail\Controllers
 */

namespace Projects\Mail\Controllers;

use Core\View;
use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Helpers;

abstract class BaseController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
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
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Return success JSON response
     */
    protected function success($message = 'Success', $data = []): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Return error JSON response
     */
    protected function error($message = 'Error', $code = 400, $data = []): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $code = 302): void
    {
        header("Location: $url", true, $code);
        exit;
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
    protected function input(string $key, $default = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }
    
    /**
     * Get all input
     */
    protected function all(): array
    {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * Validate input
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rules_array = explode('|', $rule);
            
            foreach ($rules_array as $single_rule) {
                if ($single_rule === 'required' && empty($data[$field])) {
                    $errors[$field] = ucfirst($field) . ' is required';
                    break;
                }
                
                if (str_starts_with($single_rule, 'min:')) {
                    $min = (int)substr($single_rule, 4);
                    if (strlen($data[$field] ?? '') < $min) {
                        $errors[$field] = ucfirst($field) . " must be at least $min characters";
                        break;
                    }
                }
                
                if (str_starts_with($single_rule, 'max:')) {
                    $max = (int)substr($single_rule, 4);
                    if (strlen($data[$field] ?? '') > $max) {
                        $errors[$field] = ucfirst($field) . " must not exceed $max characters";
                        break;
                    }
                }
                
                if ($single_rule === 'email' && !empty($data[$field])) {
                    if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = ucfirst($field) . ' must be a valid email address';
                        break;
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Flash message
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
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
            $this->view('errors/403');
            exit;
        }
    }
    
    /**
     * Check if user has mail access
     */
    protected function requireMailAccess(): void
    {
        $this->requireAuth();
        
        $user = Auth::user();
        if (!isset($user->subscriber_id) || !isset($user->mailbox_id)) {
            http_response_code(403);
            $this->error('Mail access required', 403);
        }
    }
}
