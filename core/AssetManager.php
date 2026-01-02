<?php
/**
 * Asset Manager for CSS/JS Optimization
 * 
 * Provides asset minification, bundling, and versioning
 * Part of Phase 8: Performance Optimization
 * 
 * @package MMB\Core
 */

namespace Core;

class AssetManager
{
    private static $manifest = [];
    private static $manifestFile;
    private static $assetDir;
    private static $publicDir;
    
    /**
     * Initialize asset manager
     */
    private static function init(): void
    {
        if (!self::$assetDir) {
            self::$assetDir = BASE_PATH . '/public/assets';
            self::$publicDir = BASE_PATH . '/public';
            self::$manifestFile = self::$assetDir . '/manifest.json';
            
            // Load manifest if exists
            if (file_exists(self::$manifestFile)) {
                $content = file_get_contents(self::$manifestFile);
                self::$manifest = json_decode($content, true) ?? [];
            }
        }
    }
    
    /**
     * Get asset URL with versioning
     * 
     * @param string $path Asset path relative to public directory
     * @return string Versioned asset URL
     */
    public static function url(string $path): string
    {
        self::init();
        
        // Check if we have a versioned file in manifest
        if (isset(self::$manifest[$path])) {
            return '/' . self::$manifest[$path];
        }
        
        // Generate version based on file modification time
        $fullPath = self::$publicDir . '/' . ltrim($path, '/');
        if (file_exists($fullPath)) {
            $mtime = filemtime($fullPath);
            return '/' . ltrim($path, '/') . '?v=' . $mtime;
        }
        
        return '/' . ltrim($path, '/');
    }
    
    /**
     * Minify CSS content
     * 
     * @param string $css CSS content
     * @return string Minified CSS
     */
    public static function minifyCSS(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        
        // Remove spaces around special characters
        $css = preg_replace('/\s*([{}|:;,>])\s*/', '$1', $css);
        
        // Remove trailing semicolons
        $css = str_replace(';}', '}', $css);
        
        return trim($css);
    }
    
    /**
     * Minify JavaScript content
     * 
     * @param string $js JavaScript content
     * @return string Minified JavaScript
     */
    public static function minifyJS(string $js): string
    {
        // Remove single-line comments (but not URLs)
        $js = preg_replace('~//[^\n]*~', '', $js);
        
        // Remove multi-line comments
        $js = preg_replace('~/\*.*?\*/~s', '', $js);
        
        // Remove whitespace
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Remove spaces around operators and punctuation
        $js = preg_replace('/\s*([{}|:;,()=<>!+\-*\/])\s*/', '$1', $js);
        
        return trim($js);
    }
    
    /**
     * Bundle multiple CSS files into one
     * 
     * @param array $files Array of file paths
     * @param string $outputName Output file name
     * @param bool $minify Whether to minify the output
     * @return string URL to bundled file
     */
    public static function bundleCSS(array $files, string $outputName, bool $minify = true): string
    {
        self::init();
        
        $content = '';
        $hash = md5(implode('|', $files));
        
        foreach ($files as $file) {
            $filePath = self::$publicDir . '/' . ltrim($file, '/');
            if (file_exists($filePath)) {
                $fileContent = file_get_contents($filePath);
                $content .= "\n/* File: {$file} */\n" . $fileContent . "\n";
            }
        }
        
        if ($minify) {
            $content = self::minifyCSS($content);
        }
        
        // Generate output filename with hash
        $ext = '.css';
        $outputPath = self::$assetDir . '/css/' . pathinfo($outputName, PATHINFO_FILENAME) . '.' . substr($hash, 0, 8) . $ext;
        
        // Create directory if needed
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outputPath, $content);
        
        // Update manifest
        $relativePath = 'assets/css/' . basename($outputPath);
        self::$manifest[$outputName] = $relativePath;
        self::saveManifest();
        
        return '/' . $relativePath;
    }
    
    /**
     * Bundle multiple JS files into one
     * 
     * @param array $files Array of file paths
     * @param string $outputName Output file name
     * @param bool $minify Whether to minify the output
     * @return string URL to bundled file
     */
    public static function bundleJS(array $files, string $outputName, bool $minify = true): string
    {
        self::init();
        
        $content = '';
        $hash = md5(implode('|', $files));
        
        foreach ($files as $file) {
            $filePath = self::$publicDir . '/' . ltrim($file, '/');
            if (file_exists($filePath)) {
                $fileContent = file_get_contents($filePath);
                $content .= "\n/* File: {$file} */\n" . $fileContent . ";\n";
            }
        }
        
        if ($minify) {
            $content = self::minifyJS($content);
        }
        
        // Generate output filename with hash
        $ext = '.js';
        $outputPath = self::$assetDir . '/js/' . pathinfo($outputName, PATHINFO_FILENAME) . '.' . substr($hash, 0, 8) . $ext;
        
        // Create directory if needed
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($outputPath, $content);
        
        // Update manifest
        $relativePath = 'assets/js/' . basename($outputPath);
        self::$manifest[$outputName] = $relativePath;
        self::saveManifest();
        
        return '/' . $relativePath;
    }
    
    /**
     * Save manifest file
     */
    private static function saveManifest(): void
    {
        $dir = dirname(self::$manifestFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(
            self::$manifestFile,
            json_encode(self::$manifest, JSON_PRETTY_PRINT)
        );
    }
    
    /**
     * Clear cached bundles
     * 
     * @return int Number of files removed
     */
    public static function clearBundles(): int
    {
        self::init();
        
        $count = 0;
        
        // Clear CSS bundles
        $cssDir = self::$assetDir . '/css';
        if (is_dir($cssDir)) {
            $files = glob($cssDir . '/*.css');
            foreach ($files as $file) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        
        // Clear JS bundles
        $jsDir = self::$assetDir . '/js';
        if (is_dir($jsDir)) {
            $files = glob($jsDir . '/*.js');
            foreach ($files as $file) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        
        // Clear manifest
        self::$manifest = [];
        self::saveManifest();
        
        return $count;
    }
    
    /**
     * Generate inline CSS from file
     * 
     * @param string $path CSS file path
     * @param bool $minify Whether to minify
     * @return string CSS content wrapped in style tags
     */
    public static function inlineCSS(string $path, bool $minify = true): string
    {
        $filePath = self::$publicDir . '/' . ltrim($path, '/');
        if (!file_exists($filePath)) {
            return '';
        }
        
        $content = file_get_contents($filePath);
        if ($minify) {
            $content = self::minifyCSS($content);
        }
        
        return "<style>{$content}</style>";
    }
    
    /**
     * Generate inline JS from file
     * 
     * @param string $path JS file path
     * @param bool $minify Whether to minify
     * @return string JS content wrapped in script tags
     */
    public static function inlineJS(string $path, bool $minify = true): string
    {
        $filePath = self::$publicDir . '/' . ltrim($path, '/');
        if (!file_exists($filePath)) {
            return '';
        }
        
        $content = file_get_contents($filePath);
        if ($minify) {
            $content = self::minifyJS($content);
        }
        
        return "<script>{$content}</script>";
    }
}
