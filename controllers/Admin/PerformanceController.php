<?php
/**
 * Performance & Cache Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Cache;
use Core\AssetManager;

class PerformanceController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Cache Management
     */
    public function cache(): void
    {
        // Get cache statistics
        $stats = Cache::stats();
        
        // Get cache directory size
        $cacheDir = __DIR__ . '/../../storage/cache/';
        $cacheSize = 0;
        $fileCount = 0;
        
        if (is_dir($cacheDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $cacheSize += $file->getSize();
                    $fileCount++;
                }
            }
        }
        
        $this->view('admin/performance/cache', [
            'title' => 'Cache Management',
            'stats' => $stats,
            'cacheSize' => $cacheSize,
            'cacheSizeFormatted' => $this->formatBytes($cacheSize),
            'fileCount' => $fileCount
        ]);
    }
    
    /**
     * Asset Optimization
     */
    public function assets(): void
    {
        // Get assets directory info
        $assetsDir = __DIR__ . '/../../assets/';
        $assets = [
            'css' => [],
            'js' => []
        ];
        
        if (is_dir($assetsDir . 'css')) {
            $cssFiles = glob($assetsDir . 'css/*.css');
            foreach ($cssFiles as $file) {
                $assets['css'][] = [
                    'name' => basename($file),
                    'size' => filesize($file),
                    'sizeFormatted' => $this->formatBytes(filesize($file)),
                    'modified' => filemtime($file)
                ];
            }
        }
        
        if (is_dir($assetsDir . 'js')) {
            $jsFiles = glob($assetsDir . 'js/*.js');
            foreach ($jsFiles as $file) {
                $assets['js'][] = [
                    'name' => basename($file),
                    'size' => filesize($file),
                    'sizeFormatted' => $this->formatBytes(filesize($file)),
                    'modified' => filemtime($file)
                ];
            }
        }
        
        $this->view('admin/performance/assets', [
            'title' => 'Asset Optimization',
            'assets' => $assets
        ]);
    }
    
    /**
     * Database Optimization
     */
    public function database(): void
    {
        $db = Database::getInstance();
        
        // Get database size
        $dbName = $db->fetch("SELECT DATABASE() as name")['name'];
        $tables = $db->fetchAll(
            "SELECT 
                table_name as name,
                table_rows as `rows`,
                data_length as data_size,
                index_length as index_size,
                (data_length + index_length) as total_size
             FROM information_schema.TABLES 
             WHERE table_schema = ? 
             ORDER BY total_size DESC",
            [$dbName]
        );
        
        $totalSize = 0;
        $totalRows = 0;
        foreach ($tables as &$table) {
            $totalSize += $table['total_size'];
            $totalRows += $table['rows'];
            $table['data_size_formatted'] = $this->formatBytes($table['data_size']);
            $table['index_size_formatted'] = $this->formatBytes($table['index_size']);
            $table['total_size_formatted'] = $this->formatBytes($table['total_size']);
        }
        
        $this->view('admin/performance/database', [
            'title' => 'Database Optimization',
            'tables' => $tables,
            'totalSize' => $totalSize,
            'totalSizeFormatted' => $this->formatBytes($totalSize),
            'totalRows' => $totalRows,
            'dbName' => $dbName
        ]);
    }
    
    /**
     * Performance Monitoring
     */
    public function monitoring(): void
    {
        $db = Database::getInstance();
        
        // Get server info
        $serverInfo = [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_formatted' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_peak_formatted' => $this->formatBytes(memory_get_peak_usage(true))
        ];
        
        // Get system load (Linux only)
        $load = sys_getloadavg();
        $serverInfo['load_average'] = $load ? sprintf('%.2f, %.2f, %.2f', $load[0], $load[1], $load[2]) : 'N/A';
        $serverInfo['load_1min'] = $load[0] ?? 0;
        $serverInfo['load_5min'] = $load[1] ?? 0;
        $serverInfo['load_15min'] = $load[2] ?? 0;
        
        // Get disk space
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $serverInfo['disk_free'] = $this->formatBytes($diskFree);
        $serverInfo['disk_total'] = $this->formatBytes($diskTotal);
        $serverInfo['disk_used'] = $this->formatBytes($diskTotal - $diskFree);
        $serverInfo['disk_used_percent'] = $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 2) : 0;
        
        // Get database performance metrics
        $dbStats = [
            'total_queries' => 0,
            'slow_queries' => 0,
            'connections' => 0
        ];
        
        try {
            $status = $db->fetchAll("SHOW GLOBAL STATUS WHERE Variable_name IN ('Queries', 'Slow_queries', 'Threads_connected')");
            foreach ($status as $stat) {
                if ($stat['Variable_name'] === 'Queries') {
                    $dbStats['total_queries'] = $stat['Value'];
                } elseif ($stat['Variable_name'] === 'Slow_queries') {
                    $dbStats['slow_queries'] = $stat['Value'];
                } elseif ($stat['Variable_name'] === 'Threads_connected') {
                    $dbStats['connections'] = $stat['Value'];
                }
            }
        } catch (\Exception $e) {
            // Fallback if status query fails
        }
        
        // Get average response time (placeholder - would need response_time column)
        $avgResponseTime = 0;
        // TODO: Implement proper response time tracking with a response_time column in analytics_events table
        
        $this->view('admin/performance/monitoring', [
            'title' => 'Performance Monitoring',
            'serverInfo' => $serverInfo,
            'dbStats' => $dbStats,
            'avgResponseTime' => round($avgResponseTime, 3)
        ]);
    }
    
    /**
     * Clear Cache (AJAX)
     */
    public function clearCache(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            Cache::flush();
            $this->json(['success' => true, 'message' => 'Cache cleared successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Optimize Database Table (AJAX)
     */
    public function optimizeTable(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $tableName = $_POST['table'] ?? '';
        
        if (!$tableName) {
            $this->json(['success' => false, 'message' => 'Table name is required']);
            return;
        }
        
        $db = Database::getInstance();
        
        try {
            $db->execute("OPTIMIZE TABLE `$tableName`");
            $this->json(['success' => true, 'message' => "Table $tableName optimized successfully"]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Minify Asset (AJAX)
     */
    public function minifyAsset(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $file = $_POST['file'] ?? '';
        $type = $_POST['type'] ?? '';
        
        if (!$file || !$type) {
            $this->json(['success' => false, 'message' => 'File and type are required']);
            return;
        }
        
        try {
            $inputPath = __DIR__ . '/../../assets/' . $type . '/' . $file;
            $outputPath = __DIR__ . '/../../assets/' . $type . '/min/' . $file;
            
            if (!file_exists($inputPath)) {
                $this->json(['success' => false, 'message' => 'File not found']);
                return;
            }
            
            $content = file_get_contents($inputPath);
            
            if ($type === 'css') {
                $minified = AssetManager::minifyCSS($content);
            } elseif ($type === 'js') {
                $minified = AssetManager::minifyJS($content);
            } else {
                $this->json(['success' => false, 'message' => 'Unsupported file type']);
                return;
            }
            
            // Create output directory if it doesn't exist
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            file_put_contents($outputPath, $minified);
            
            $originalSize = strlen($content);
            $minifiedSize = strlen($minified);
            $savings = round((($originalSize - $minifiedSize) / $originalSize) * 100, 2);
            
            $this->json([
                'success' => true,
                'message' => "Asset minified successfully. Saved $savings%",
                'originalSize' => $this->formatBytes($originalSize),
                'minifiedSize' => $this->formatBytes($minifiedSize),
                'savings' => $savings
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
