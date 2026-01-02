<?php
/**
 * Logger Class
 * 
 * @package MMB\Core
 */

namespace Core;

class Logger
{
    private static string $logPath = '';
    
    /**
     * Get log path
     */
    private static function getLogPath(): string
    {
        if (empty(self::$logPath)) {
            self::$logPath = BASE_PATH . '/storage/logs';
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0755, true);
            }
        }
        return self::$logPath;
    }
    
    /**
     * Write log entry
     */
    private static function write(string $level, string $message, array $context = []): void
    {
        $logFile = self::getLogPath() . '/' . date('Y-m-d') . '.log';
        
        $entry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            self::write('debug', $message, $context);
        }
    }
    
    /**
     * Log activity
     */
    public static function activity(int $userId, string $action, array $data = []): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('activity_logs', [
                'user_id' => $userId,
                'action' => $action,
                'ip_address' => Security::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'data' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            self::error('Failed to log activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Get log files
     */
    public static function getLogFiles(): array
    {
        $files = glob(self::getLogPath() . '/*.log');
        return array_map('basename', $files);
    }
    
    /**
     * Read log file
     */
    public static function readLog(string $filename, int $lines = 100): array
    {
        $filepath = self::getLogPath() . '/' . basename($filename);
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $content = file($filepath);
        return array_slice($content, -$lines);
    }
}
