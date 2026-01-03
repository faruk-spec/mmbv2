<?php
/**
 * Main Application Class
 * 
 * @package MMB\Core
 */

namespace Core;

class App
{
    protected Router $router;
    protected Database $db;
    
    public function __construct()
    {
        // Initialize error handling
        $this->initErrorHandling();
        
        // Initialize session
        $this->initSession();
        
        // Initialize database
        $this->db = Database::getInstance();
        
        // Load system timezone from database
        $this->loadSystemTimezone();
        
        // Initialize router
        $this->router = new Router();
    }
    
    /**
     * Load system timezone from database settings
     */
    protected function loadSystemTimezone(): void
    {
        try {
            $timezone = $this->db->fetch("SELECT value FROM settings WHERE `key` = 'system_timezone'");
            if ($timezone && !empty($timezone['value'])) {
                date_default_timezone_set($timezone['value']);
            }
        } catch (\Exception $e) {
            // If database query fails, keep default timezone
            // This can happen during installation
        }
    }
    
    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            // Get request method
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            
            // Determine the URI using multiple fallback methods
            $uri = $this->resolveUri();
            
            // Debug logging for troubleshooting
            if (defined('APP_DEBUG') && APP_DEBUG && !isset($_GET['_debug'])) {
                Logger::info("Routing: $method $uri", [
                    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'not set',
                    'GET[url]' => $_GET['url'] ?? 'not set',
                    'REDIRECT_URL' => $_SERVER['REDIRECT_URL'] ?? 'not set'
                ]);
            }
            
            // Handle the request
            $this->router->dispatch($method, $uri);
        } catch (\Exception $e) {
            Logger::error('Application error: ' . $e->getMessage());
            $this->handleError($e);
        }
    }
    
    /**
     * Resolve the request URI from various server variables
     * Supports: mod_rewrite, PATH_INFO, Nginx try_files, FallbackResource, and query string fallback
     */
    protected function resolveUri(): string
    {
        // Method 1: Check for ?url= query parameter (explicit URL routing from mod_rewrite)
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            $uri = '/' . ltrim($_GET['url'], '/');
            return $this->sanitizeUri($uri);
        }
        
        // Method 2: Check PATH_INFO (for some server configurations)
        if (!empty($_SERVER['PATH_INFO'])) {
            return $this->sanitizeUri($_SERVER['PATH_INFO']);
        }
        
        // Method 3: Check REDIRECT_URL (set by Apache's FallbackResource/ErrorDocument)
        if (!empty($_SERVER['REDIRECT_URL'])) {
            $uri = $_SERVER['REDIRECT_URL'];
            // REDIRECT_URL contains the original requested path
            return $this->sanitizeUri($uri);
        }
        
        // Method 4: Parse REQUEST_URI - most reliable for Nginx and modern Apache
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string first
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        
        // Handle the case where URI path is exactly /index.php
        // This happens when the server serves index.php directly as the directory index
        // Note: By this point, query strings have been stripped by parse_url above,
        // and if ?url= was set, we already returned early in Method 1
        if ($uri === '/index.php') {
            return '/';
        }
        
        // Remove /index.php prefix if it exists in the middle of the path
        if (strpos($uri, '/index.php/') === 0) {
            $uri = substr($uri, strlen('/index.php')) ?: '/';
        }
        
        // Get the script directory (e.g., /public or /subfolder)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
        
        // Remove script directory prefix only if it's a real subdirectory (not just /)
        if ($scriptDir !== '/' && $scriptDir !== '\\' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir)) ?: '/';
        }
        
        // Remove /public prefix if present (for root DocumentRoot setups accessing public folder)
        if (strpos($uri, '/public') === 0) {
            $uri = substr($uri, strlen('/public')) ?: '/';
        }
        
        // Ensure the URI starts with /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        // If URI is empty or just a slash, return root
        if (empty($uri) || $uri === '/') {
            return '/';
        }
        
        return $this->sanitizeUri($uri);
    }
    
    /**
     * Sanitize URI to prevent directory traversal
     */
    protected function sanitizeUri(string $uri): string
    {
        // Ensure starts with /
        $uri = '/' . ltrim($uri, '/');
        
        // Remove any directory traversal attempts
        $uri = str_replace(['../', '..\\'], '', $uri);
        
        // Normalize slashes
        $uri = preg_replace('#/+#', '/', $uri);
        
        return $uri ?: '/';
    }
    
    /**
     * Initialize error handling
     */
    protected function initErrorHandling(): void
    {
        error_reporting(E_ALL);
        
        if (defined('APP_DEBUG') && APP_DEBUG) {
            ini_set('display_errors', '1');
        } else {
            ini_set('display_errors', '0');
        }
        
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
    }
    
    /**
     * Initialize session with security
     */
    protected function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session configuration
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.cookie_path', '/'); // Ensure cookie works for all paths
            ini_set('session.cookie_lifetime', '86400'); // 24 hours
            
            session_start();
            
            // Regenerate session ID periodically for security
            if (!isset($_SESSION['_created'])) {
                $_SESSION['_created'] = time();
            } elseif (time() - $_SESSION['_created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_created'] = time();
            }
            
            // Session fingerprinting - disabled to prevent session loss during navigation
            // Comment: Session fingerprinting was causing sessions to be destroyed
            // when users navigated between different parts of the application
            // TODO: Implement less strict fingerprinting if needed for security
            /*
            $fingerprint = Security::generateSessionFingerprint();
            if (isset($_SESSION['_fingerprint'])) {
                if ($_SESSION['_fingerprint'] !== $fingerprint) {
                    session_destroy();
                    session_start();
                    $_SESSION['_fingerprint'] = $fingerprint;
                }
            } else {
                $_SESSION['_fingerprint'] = $fingerprint;
            }
            */
        }
    }
    
    /**
     * Handle exceptions
     */
    public function handleException(\Throwable $e): void
    {
        Logger::error('Exception: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo '<h1>Error</h1>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            View::render('errors/500');
        }
    }
    
    /**
     * Handle errors
     */
    public function handleError($errno, $errstr = '', $errfile = '', $errline = 0): bool
    {
        if ($errno instanceof \Exception) {
            $this->handleException($errno);
            return true;
        }
        
        Logger::error("Error [$errno]: $errstr in $errfile on line $errline");
        return true;
    }
}
