<?php
/**
 * Router Class
 * 
 * @package MMB\Core
 */

namespace Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    
    public function __construct(bool $loadRoutes = true)
    {
        if ($loadRoutes) {
            $this->loadRoutes();
        }
    }
    
    /**
     * Load all route definitions
     */
    private function loadRoutes(): void
    {
        $routeFiles = glob(BASE_PATH . '/routes/*.php');
        foreach ($routeFiles as $file) {
            $router = $this;
            require $file;
        }
    }
    
    /**
     * Add a GET route
     */
    public function get(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $handler, $middleware);
    }
    
    /**
     * Add a POST route
     */
    public function post(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $handler, $middleware);
    }
    
    /**
     * Add a PUT route
     */
    public function put(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $uri, $handler, $middleware);
    }
    
    /**
     * Add a DELETE route
     */
    public function delete(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $handler, $middleware);
    }
    
    /**
     * Add a PATCH route
     */
    public function patch(string $uri, $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $uri, $handler, $middleware);
    }
    
    /**
     * Add a route
     */
    private function addRoute(string $method, string $uri, $handler, array $middleware = []): self
    {
        $this->routes[$method][$uri] = [
            'handler' => $handler,
            'middleware' => $middleware
        ];
        return $this;
    }
    
    /**
     * Dispatch the request
     */
    public function dispatch(string $method, string $uri): void
    {
        // Run global middleware (maintenance mode check)
        // This runs before all routes
        if (class_exists('Core\\Middleware\\MaintenanceMiddleware')) {
            \Core\Middleware\MaintenanceMiddleware::handle();
        }
        
        // Remove trailing slash
        $uri = rtrim($uri, '/') ?: '/';
        
        // Remove query string from URI
        if (strpos($uri, '?') !== false) {
            $uri = strstr($uri, '?', true);
        }
        
        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            $this->handleRoute($this->routes[$method][$uri], []);
            return;
        }
        
        // Check for pattern match
        foreach ($this->routes[$method] ?? [] as $pattern => $route) {
            $params = $this->matchRoute($pattern, $uri);
            if ($params !== false) {
                $this->handleRoute($route, $params);
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        View::render('errors/404');
    }
    
    /**
     * Match route pattern
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert pattern to regex
        // Support {param:wildcard} for wildcard paths (matches multiple segments including slashes)
        $regex = preg_replace('/\{([a-zA-Z_]+):wildcard\}/', '(?P<$1>.*)', $pattern);
        // Support {param} for single segment paths (matches everything except slashes)
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $regex);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }
        
        return false;
    }
    
    /**
     * Handle matched route
     */
    private function handleRoute(array $route, array $params): void
    {
        // Middleware name mapping
        $middlewareMap = [
            'auth' => 'AuthMiddleware',
            'admin' => 'AdminMiddleware'
        ];
        
        // Run middleware
        foreach ($route['middleware'] as $middleware) {
            $middlewareName = $middlewareMap[$middleware] ?? ucfirst($middleware) . 'Middleware';
            $middlewareClass = "Core\\Middleware\\{$middlewareName}";
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                if (!$middlewareInstance->handle()) {
                    return;
                }
            }
        }
        
        // Handle the route
        $handler = $route['handler'];
        
        if (is_string($handler)) {
            // Controller@method format
            [$controller, $method] = explode('@', $handler);
            
            // Determine the full controller class name
            // - If starts with "Projects\", it's a fully-qualified project namespace (use as-is)
            // - Otherwise, prepend "Controllers\" for main app and admin controllers
            if (strpos($controller, 'Projects\\') === 0) {
                // Project controller with full namespace
                $controllerClass = $controller;
            } else {
                // Main app or admin controller (prepend Controllers\)
                $controllerClass = "Controllers\\{$controller}";
            }
            
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                call_user_func_array([$controllerInstance, $method], $params);
            } else {
                throw new \RuntimeException("Controller {$controllerClass} not found");
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        }
    }
    
    /**
     * Generate URL for named route
     */
    public function url(string $name, array $params = []): string
    {
        // TODO: Implement named routes
        return '/';
    }
}
