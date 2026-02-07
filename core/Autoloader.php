<?php
/**
 * PSR-4 Autoloader
 * 
 * @package MMB\Core
 */

spl_autoload_register(function ($class) {
    // Base directory for the namespace prefix
    $baseDir = dirname(__DIR__) . '/';
    
    // Namespace to directory mapping
    $namespaceMap = [
        'Core\\' => 'core/',
        'Controllers\\' => 'controllers/',
        'Projects\\' => 'projects/',
    ];
    
    // Check each namespace mapping
    foreach ($namespaceMap as $namespace => $dir) {
        $len = strlen($namespace);
        if (strncmp($namespace, $class, $len) === 0) {
            // Get the relative class name
            $relativeClass = substr($class, $len);
            
            // Replace namespace separator with directory separator
            $relativeClass = str_replace('\\', '/', $relativeClass);
            
            // Special handling for Projects namespace - convert to lowercase for directory structure
            if ($namespace === 'Projects\\') {
                // Split path into parts
                $parts = explode('/', $relativeClass);
                // Convert first part (project name) to lowercase
                if (count($parts) > 0) {
                    $parts[0] = strtolower($parts[0]);
                }
                // Convert 'Controllers' directory to lowercase
                if (count($parts) > 1 && $parts[1] === 'Controllers') {
                    $parts[1] = 'controllers';
                }
                // Convert 'Models' directory to lowercase
                if (count($parts) > 1 && $parts[1] === 'Models') {
                    $parts[1] = 'models';
                }
                $relativeClass = implode('/', $parts);
            }
            
            // Build the file path
            $file = $baseDir . $dir . $relativeClass . '.php';
            
            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }
    
    // Fallback: Try direct path mapping
    $relativeClass = str_replace('\\', '/', $class);
    
    // Try exact case
    $file = $baseDir . $relativeClass . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    // Try lowercase
    $file = $baseDir . strtolower($relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});
