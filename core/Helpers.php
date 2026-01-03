<?php
/**
 * Helper Functions
 * 
 * @package MMB\Core
 */

namespace Core;

class Helpers
{
    /**
     * Redirect to URL
     */
    public static function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Return JSON response
     */
    public static function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get request input
     */
    public static function input(string $key, $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }
    
    /**
     * Get all request input
     */
    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * Check request method
     */
    public static function isMethod(string $method): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }
    
    /**
     * Check if request is AJAX
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Flash message to session
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }
    
    /**
     * Get flash message
     */
    public static function getFlash(string $type): ?string
    {
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $message;
    }
    
    /**
     * Check for flash message
     */
    public static function hasFlash(string $type): bool
    {
        return isset($_SESSION['_flash'][$type]);
    }
    
    /**
     * Generate slug
     */
    public static function slug(string $text): string
    {
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return preg_replace('/-+/', '-', $text);
    }
    
    /**
     * Format date
     */
    public static function formatDate(string $date, string $format = 'M d, Y'): string
    {
        return date($format, strtotime($date));
    }
    
    /**
     * Format time ago
     */
    public static function timeAgo($datetime): string
    {
        // Handle both timestamp integers and datetime strings
        if (is_numeric($datetime)) {
            $timestamp = (int)$datetime;
        } else {
            $timestamp = strtotime($datetime);
        }
        
        // If conversion failed, return original value
        if ($timestamp === false) {
            return is_string($datetime) ? $datetime : 'unknown';
        }
        
        $time = time() - $timestamp;
        
        // Handle future dates (shouldn't happen but just in case)
        if ($time < 0) {
            return 'just now';
        }
        
        $units = [
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        ];
        
        foreach ($units as $unit => $text) {
            if ($time >= $unit) {
                $count = floor($time / $unit);
                return $count . ' ' . $text . ($count > 1 ? 's' : '') . ' ago';
            }
        }
        
        return 'just now';
    }
    
    /**
     * Format file size
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Truncate text
     */
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
    
    /**
     * Get config value
     */
    public static function config(string $key, $default = null): mixed
    {
        static $configs = [];
        
        $parts = explode('.', $key);
        $file = array_shift($parts);
        
        if (!isset($configs[$file])) {
            $path = BASE_PATH . "/config/{$file}.php";
            if (file_exists($path)) {
                $configs[$file] = require $path;
            } else {
                return $default;
            }
        }
        
        $value = $configs[$file];
        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return $default;
            }
            $value = $value[$part];
        }
        
        return $value;
    }
    
    /**
     * Upload file
     */
    public static function uploadFile(array $file, string $destination, array $allowed = []): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file type
        if (!empty($allowed)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                return false;
            }
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $path = rtrim($destination, '/') . '/' . $filename;
        
        // Create directory if needed
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $filename;
        }
        
        return false;
    }
    
    /**
     * Validate request data
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            
            foreach ($fieldRules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                
                $error = self::validateRule($field, $value, $rule, $params);
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate single rule
     */
    private static function validateRule(string $field, $value, string $rule, array $params): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));
        
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    return "{$label} is required.";
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "{$label} must be a valid email address.";
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < (int) $params[0]) {
                    return "{$label} must be at least {$params[0]} characters.";
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > (int) $params[0]) {
                    return "{$label} must not exceed {$params[0]} characters.";
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (($_POST[$confirmField] ?? '') !== $value) {
                    return "{$label} confirmation does not match.";
                }
                break;
                
            case 'unique':
                if (!empty($value)) {
                    $table = $params[0];
                    $column = $params[1] ?? $field;
                    try {
                        $db = Database::getInstance();
                        $existing = $db->fetch(
                            "SELECT id FROM {$table} WHERE {$column} = ?",
                            [$value]
                        );
                        if ($existing) {
                            return "{$label} already exists.";
                        }
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "{$label} must be a number.";
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    return "{$label} must contain only letters.";
                }
                break;
                
            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum($value)) {
                    return "{$label} must contain only letters and numbers.";
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Check if a project is enabled
     */
    public static function isProjectEnabled(string $projectKey): bool
    {
        try {
            $db = Database::getInstance();
            $project = $db->fetch("SELECT is_enabled FROM home_projects WHERE project_key = ?", [$projectKey]);
            
            if ($project) {
                return (bool) $project['is_enabled'];
            }
            
            // Fallback to config
            $config = self::config("projects.{$projectKey}.enabled", true);
            return $config;
        } catch (\Exception $e) {
            // If database fails, fallback to config
            return self::config("projects.{$projectKey}.enabled", true);
        }
    }
    
    /**
     * Get project info
     */
    public static function getProject(string $projectKey): ?array
    {
        try {
            $db = Database::getInstance();
            $project = $db->fetch("SELECT * FROM home_projects WHERE project_key = ?", [$projectKey]);
            
            if ($project) {
                return $project;
            }
            
            // Fallback to config
            $config = self::config("projects.{$projectKey}");
            if ($config) {
                $config['project_key'] = $projectKey;
                return $config;
            }
        } catch (\Exception $e) {
            // If database fails, fallback to config
            $config = self::config("projects.{$projectKey}");
            if ($config) {
                $config['project_key'] = $projectKey;
                return $config;
            }
        }
        
        return null;
    }
    
    /**
     * Build pagination URL with preserved query parameters
     * 
     * @param int $page Page number
     * @param array $params Additional parameters to include
     * @return string URL with query parameters
     */
    public static function paginationUrl(int $page, array $params = []): string
    {
        // Merge with existing GET parameters
        $queryParams = array_merge($_GET, $params, ['page' => $page]);
        
        // Remove empty values
        $queryParams = array_filter($queryParams, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Build query string
        $queryString = http_build_query($queryParams);
        
        // Get current path without query string
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        return $path . ($queryString ? '?' . $queryString : '');
    }
}
