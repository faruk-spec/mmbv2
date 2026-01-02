<?php
/**
 * API Base Controller
 * 
 * Base controller for all API endpoints
 * Part of Phase 11: API Development
 * 
 * @package MMB\Core\API
 */

namespace Core\API;

use Core\Auth;
use Core\Logger;

abstract class ApiController
{
    protected $version = 'v1';
    protected $requestMethod;
    protected $requestData;
    protected $headers;
    
    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->headers = $this->getRequestHeaders();
        $this->requestData = $this->parseRequestData();
        
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Enable CORS if configured
        $this->setCorsHeaders();
    }
    
    /**
     * Handle API request
     */
    public function handle(): void
    {
        try {
            // Authenticate request
            if (!$this->authenticate()) {
                $this->respondUnauthorized('Invalid or missing API credentials');
                return;
            }
            
            // Check rate limit
            if (!$this->checkRateLimit()) {
                $this->respondTooManyRequests('Rate limit exceeded');
                return;
            }
            
            // Route to appropriate method
            $this->route();
            
        } catch (\Exception $e) {
            Logger::error('API Error: ' . $e->getMessage());
            $this->respondError('Internal server error', 500);
        }
    }
    
    /**
     * Route request to appropriate method
     */
    abstract protected function route(): void;
    
    /**
     * Authenticate API request
     */
    protected function authenticate(): bool
    {
        // Check for API key in header
        $apiKey = $this->headers['X-Api-Key'] ?? $this->headers['Authorization'] ?? null;
        
        if (!$apiKey) {
            return false;
        }
        
        // Remove 'Bearer ' prefix if present
        $apiKey = str_replace('Bearer ', '', $apiKey);
        
        // Validate API key
        return ApiAuth::validateKey($apiKey);
    }
    
    /**
     * Check rate limit
     */
    protected function checkRateLimit(): bool
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return false;
        }
        
        return RateLimiter::check($apiKey);
    }
    
    /**
     * Get API key from request
     */
    protected function getApiKey(): ?string
    {
        $apiKey = $this->headers['X-Api-Key'] ?? $this->headers['Authorization'] ?? null;
        return $apiKey ? str_replace('Bearer ', '', $apiKey) : null;
    }
    
    /**
     * Get request headers
     */
    protected function getRequestHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Parse request data
     */
    protected function parseRequestData(): array
    {
        $data = [];
        
        // Parse JSON body
        if ($this->requestMethod !== 'GET') {
            $input = file_get_contents('php://input');
            $json = json_decode($input, true);
            if ($json) {
                $data = $json;
            }
        }
        
        // Merge with query parameters
        $data = array_merge($_GET, $data);
        
        return $data;
    }
    
    /**
     * Set CORS headers
     */
    protected function setCorsHeaders(): void
    {
        $allowedOrigins = ['*']; // Configure in production
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        header('Access-Control-Max-Age: 3600');
        
        // Handle preflight requests
        if ($this->requestMethod === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Send JSON response
     */
    protected function respond($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send success response
     */
    protected function respondSuccess($data = null, string $message = 'Success'): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        $this->respond($response, 200);
    }
    
    /**
     * Send created response
     */
    protected function respondCreated($data = null, string $message = 'Resource created'): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        $this->respond($response, 201);
    }
    
    /**
     * Send error response
     */
    protected function respondError(string $message, int $statusCode = 400, $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        $this->respond($response, $statusCode);
    }
    
    /**
     * Send unauthorized response
     */
    protected function respondUnauthorized(string $message = 'Unauthorized'): void
    {
        $this->respondError($message, 401);
    }
    
    /**
     * Send forbidden response
     */
    protected function respondForbidden(string $message = 'Forbidden'): void
    {
        $this->respondError($message, 403);
    }
    
    /**
     * Send not found response
     */
    protected function respondNotFound(string $message = 'Resource not found'): void
    {
        $this->respondError($message, 404);
    }
    
    /**
     * Send too many requests response
     */
    protected function respondTooManyRequests(string $message = 'Too many requests'): void
    {
        $this->respondError($message, 429);
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired(array $required): bool
    {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($this->requestData[$field]) || empty($this->requestData[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->respondError('Missing required fields', 400, [
                'missing_fields' => $missing
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get paginated results
     */
    protected function paginate(array $items, int $page = 1, int $perPage = 20): array
    {
        $total = count($items);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        return [
            'items' => array_slice($items, $offset, $perPage),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ]
        ];
    }
}
