<?php
/**
 * Security Helper Class
 * 
 * @package MMB\Core
 */

namespace Core;

class Security
{
    private static array $rateLimitCache = [];
    
    /**
     * Hash password using Argon2id
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1
        ]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token field
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Sanitize input for XSS
     */
    public static function sanitize($input): mixed
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Clean input preserving some HTML
     */
    public static function cleanHtml(string $input): string
    {
        $allowed = '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>';
        return strip_tags($input, $allowed);
    }
    
    /**
     * Generate session fingerprint
     */
    public static function generateSessionFingerprint(): string
    {
        $data = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $data .= $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $data .= $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
        
        return hash('sha256', $data);
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $decayMinutes = 15): bool
    {
        $cacheKey = 'rate_limit_' . md5($key);
        
        // Get from session or cache
        $attempts = $_SESSION[$cacheKey] ?? ['count' => 0, 'expires' => 0];
        
        // Check if expired
        if (time() > $attempts['expires']) {
            $attempts = ['count' => 0, 'expires' => time() + ($decayMinutes * 60)];
        }
        
        // Check limit
        if ($attempts['count'] >= $maxAttempts) {
            return false;
        }
        
        // Increment
        $attempts['count']++;
        $_SESSION[$cacheKey] = $attempts;
        
        return true;
    }
    
    /**
     * Clear rate limit
     */
    public static function clearRateLimit(string $key): void
    {
        $cacheKey = 'rate_limit_' . md5($key);
        unset($_SESSION[$cacheKey]);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Check if IP is blocked
     */
    public static function isIpBlocked(string $ip): bool
    {
        try {
            $db = Database::getInstance();
            $result = $db->fetch(
                "SELECT id FROM blocked_ips WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())",
                [$ip]
            );
            return $result !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Log failed login attempt
     */
    public static function logFailedLogin(string $username, string $ip): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('failed_logins', [
                'username' => $username,
                'ip_address' => $ip,
                'attempted_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to log failed login: ' . $e->getMessage());
        }
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Encrypt data
     */
    public static function encrypt(string $data, string $key = ''): string
    {
        $key = $key ?: (defined('APP_KEY') ? APP_KEY : 'default_key_change_me');
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     */
    public static function decrypt(string $data, string $key = ''): string|false
    {
        $key = $key ?: (defined('APP_KEY') ? APP_KEY : 'default_key_change_me');
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}
