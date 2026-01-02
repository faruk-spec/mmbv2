<?php
/**
 * View/Template Engine
 * 
 * @package MMB\Core
 */

namespace Core;

class View
{
    private static array $sections = [];
    private static ?string $currentSection = null;
    private static ?string $layout = null;
    private static array $data = [];
    
    /**
     * Render a view
     */
    public static function render(string $view, array $data = []): void
    {
        self::$data = array_merge(self::$data, $data);
        
        // Check if this is a project view (starts with "projects/")
        if (strpos($view, 'projects/') === 0) {
            // For project views, look in the project directory
            // projects/codexpro/dashboard -> projects/codexpro/views/dashboard.php
            $parts = explode('/', $view);
            if (count($parts) >= 3) {
                $projectName = $parts[1];
                $viewName = implode('/', array_slice($parts, 2));
                $viewPath = BASE_PATH . '/projects/' . $projectName . '/views/' . str_replace('.', '/', $viewName) . '.php';
            } else {
                $viewPath = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
            }
        } else {
            // Regular views in views/ directory
            $viewPath = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        }
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        
        // Extract data to variables
        extract(self::$data);
        
        // Start output buffering
        ob_start();
        
        try {
            // Include the view
            include $viewPath;
            
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            // Clear buffer on error
            ob_end_clean();
            
            // Log the error
            Logger::error("View rendering error in {$view}: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Re-throw for main error handler
            throw $e;
        }
        
        // If layout is set, render within layout
        if (self::$layout) {
            // Check if layout is in project-specific format (e.g., "proshare:app")
            if (strpos(self::$layout, ':') !== false) {
                list($projectName, $layoutName) = explode(':', self::$layout, 2);
                $layoutPath = BASE_PATH . '/projects/' . $projectName . '/views/layouts/' . $layoutName . '.php';
            } else {
                $layoutPath = BASE_PATH . '/views/layouts/' . self::$layout . '.php';
            }
            $layoutName = self::$layout;  // Store for error logging
            
            // Only set content section if it wasn't already set by View::section()
            if (!isset(self::$sections['content']) && !empty($content)) {
                self::$sections['content'] = $content;
            }
            
            self::$layout = null;
            
            if (file_exists($layoutPath)) {
                try {
                    extract(self::$data);
                    include $layoutPath;
                } catch (\Throwable $e) {
                    Logger::error("Layout rendering error: " . $e->getMessage(), [
                        'layout' => $layoutName,
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    throw $e;
                }
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
        
        // Reset for next render
        self::$sections = [];
    }
    
    /**
     * Extend a layout
     */
    public static function extend(string $layout): void
    {
        self::$layout = $layout;
    }
    
    /**
     * Start a section
     */
    public static function section(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }
    
    /**
     * End current section
     */
    public static function endSection(): void
    {
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }
    
    /**
     * Yield a section
     */
    public static function yield(string $name, string $default = ''): void
    {
        echo self::$sections[$name] ?? $default;
    }
    
    /**
     * Include a partial
     */
    public static function include(string $partial, array $data = []): void
    {
        $partialPath = BASE_PATH . '/views/' . str_replace('.', '/', $partial) . '.php';
        
        if (file_exists($partialPath)) {
            extract(array_merge(self::$data, $data));
            include $partialPath;
        }
    }
    
    /**
     * Escape HTML
     */
    public static function e($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Share data globally
     */
    public static function share(string $key, $value): void
    {
        self::$data[$key] = $value;
    }
    
    /**
     * Generate asset URL
     */
    public static function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
    
    /**
     * Generate URL
     */
    public static function url(string $path = ''): string
    {
        $base = defined('APP_URL') ? APP_URL : '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Get old input value
     */
    public static function old(string $key, string $default = ''): string
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
    
    /**
     * Flash old input
     */
    public static function flashOldInput(array $data): void
    {
        $_SESSION['_old_input'] = $data;
    }
    
    /**
     * Clear old input
     */
    public static function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }
    
    /**
     * Check if there are errors
     */
    public static function hasError(string $key): bool
    {
        return isset($_SESSION['_errors'][$key]);
    }
    
    /**
     * Get error message
     */
    public static function error(string $key): string
    {
        return $_SESSION['_errors'][$key] ?? '';
    }
    
    /**
     * Set error message
     */
    public static function setError(string $key, string $message): void
    {
        $_SESSION['_errors'][$key] = $message;
    }
    
    /**
     * Set multiple errors
     */
    public static function setErrors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }
    
    /**
     * Clear errors
     */
    public static function clearErrors(): void
    {
        unset($_SESSION['_errors']);
    }
}
