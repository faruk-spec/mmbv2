<?php
/**
 * Session Manager
 * Handles session lifecycle, timeout, and tracking
 * 
 * @package MMB\Core
 */

namespace Core;

class SessionManager
{
    /**
     * Initialize session tracking
     */
    public static function track(int $userId): void
    {
        try {
            $db = Database::getInstance();
            
            $sessionId = session_id();
            $ip = Security::getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Get user's custom timeout or use default
            $user = $db->fetch("SELECT session_timeout_minutes FROM users WHERE id = ?", [$userId]);
            $timeoutMinutes = $user['session_timeout_minutes'] ?? SESSION_LIFETIME;
            
            $expiresAt = date('Y-m-d H:i:s', time() + ($timeoutMinutes * 60));
            
            $deviceInfo = [
                'browser' => self::getBrowser($userAgent),
                'platform' => self::getPlatform($userAgent),
                'device' => self::getDevice($userAgent)
            ];
            
            // Check if session already tracked
            $existing = $db->fetch(
                "SELECT id FROM user_sessions WHERE session_id = ?",
                [$sessionId]
            );
            
            if ($existing) {
                // Update existing session
                $db->update('user_sessions', [
                    'last_activity_at' => date('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt,
                    'is_active' => 1
                ], 'id = ?', [$existing['id']]);
            } else {
                // Create new session record
                $db->insert('user_sessions', [
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'device_info' => json_encode($deviceInfo),
                    'last_activity_at' => date('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Store session metadata
            $_SESSION['_last_activity'] = time();
            $_SESSION['_expires_at'] = time() + ($timeoutMinutes * 60);
            $_SESSION['_timeout_minutes'] = $timeoutMinutes;
            
        } catch (\Exception $e) {
            Logger::error('Session tracking error: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if session is expired and handle auto-logout
     */
    public static function checkExpiration(): bool
    {
        if (!Auth::check()) {
            return true;
        }
        
        $lastActivity = $_SESSION['_last_activity'] ?? 0;
        $expiresAt = $_SESSION['_expires_at'] ?? 0;
        $timeoutMinutes = $_SESSION['_timeout_minutes'] ?? SESSION_LIFETIME;
        
        $now = time();
        
        // Check if session has expired
        if ($expiresAt > 0 && $now > $expiresAt) {
            self::terminateSession('expired');
            return false;
        }
        
        // Check absolute timeout (for security)
        if ($lastActivity > 0 && ($now - $lastActivity) > ($timeoutMinutes * 60)) {
            self::terminateSession('timeout');
            return false;
        }
        
        // Update activity timestamp
        self::updateActivity();
        
        return true;
    }
    
    /**
     * Update session activity timestamp
     */
    public static function updateActivity(): void
    {
        if (!Auth::check()) {
            return;
        }
        
        try {
            $userId = Auth::id();
            $sessionId = session_id();
            $timeoutMinutes = $_SESSION['_timeout_minutes'] ?? SESSION_LIFETIME;
            
            $_SESSION['_last_activity'] = time();
            $_SESSION['_expires_at'] = time() + ($timeoutMinutes * 60);
            
            $db = Database::getInstance();
            $db->update('user_sessions', [
                'last_activity_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', $_SESSION['_expires_at'])
            ], 'session_id = ? AND user_id = ?', [$sessionId, $userId]);
            
        } catch (\Exception $e) {
            Logger::error('Session update error: ' . $e->getMessage());
        }
    }
    
    /**
     * Terminate session
     */
    public static function terminateSession(string $reason = 'logout'): void
    {
        try {
            $sessionId = session_id();
            
            if ($sessionId) {
                $db = Database::getInstance();
                $db->update('user_sessions', [
                    'is_active' => 0,
                    'last_activity_at' => date('Y-m-d H:i:s')
                ], 'session_id = ?', [$sessionId]);
                
                if ($reason === 'logout' && Auth::check()) {
                    Logger::activity(Auth::id(), 'logout', ['reason' => $reason]);
                }
            }
        } catch (\Exception $e) {
            Logger::error('Session termination error: ' . $e->getMessage());
        }
        
        // Clear session data
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }
    
    /**
     * Clean up expired sessions
     */
    public static function cleanupExpiredSessions(): int
    {
        try {
            $db = Database::getInstance();
            
            // Mark expired sessions as inactive
            $result = $db->execute(
                "UPDATE user_sessions SET is_active = 0 
                 WHERE expires_at < NOW() AND is_active = 1"
            );
            
            // Delete old inactive sessions (older than 30 days)
            $db->execute(
                "DELETE FROM user_sessions 
                 WHERE is_active = 0 AND last_activity_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            
            return $result;
            
        } catch (\Exception $e) {
            Logger::error('Session cleanup error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get user's active sessions
     */
    public static function getUserSessions(int $userId): array
    {
        try {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT * FROM user_sessions 
                 WHERE user_id = ? AND is_active = 1 
                 ORDER BY last_activity_at DESC",
                [$userId]
            );
        } catch (\Exception $e) {
            Logger::error('Get user sessions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Revoke specific session
     */
    public static function revokeSession(int $sessionDbId): bool
    {
        try {
            $db = Database::getInstance();
            $db->update('user_sessions', [
                'is_active' => 0
            ], 'id = ?', [$sessionDbId]);
            
            return true;
        } catch (\Exception $e) {
            Logger::error('Revoke session error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Revoke all sessions for user except current
     */
    public static function revokeAllOtherSessions(int $userId): int
    {
        try {
            $currentSessionId = session_id();
            $db = Database::getInstance();
            
            return $db->execute(
                "UPDATE user_sessions SET is_active = 0 
                 WHERE user_id = ? AND session_id != ? AND is_active = 1",
                [$userId, $currentSessionId]
            );
        } catch (\Exception $e) {
            Logger::error('Revoke all sessions error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get remaining time before session expires (in seconds)
     */
    public static function getRemainingTime(): int
    {
        $expiresAt = $_SESSION['_expires_at'] ?? 0;
        $remaining = $expiresAt - time();
        return max(0, $remaining);
    }
    
    /**
     * Get browser name from user agent
     */
    private static function getBrowser(string $userAgent): string
    {
        if (preg_match('/MSIE|Trident/i', $userAgent)) return 'Internet Explorer';
        if (preg_match('/Edge/i', $userAgent)) return 'Edge';
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Opera|OPR/i', $userAgent)) return 'Opera';
        return 'Unknown';
    }
    
    /**
     * Get platform from user agent
     */
    private static function getPlatform(string $userAgent): string
    {
        if (preg_match('/Windows/i', $userAgent)) return 'Windows';
        if (preg_match('/Mac/i', $userAgent)) return 'MacOS';
        if (preg_match('/Linux/i', $userAgent)) return 'Linux';
        if (preg_match('/Android/i', $userAgent)) return 'Android';
        if (preg_match('/iOS|iPhone|iPad/i', $userAgent)) return 'iOS';
        return 'Unknown';
    }
    
    /**
     * Get device type from user agent
     */
    private static function getDevice(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent)) return 'Mobile';
        if (preg_match('/Tablet|iPad/i', $userAgent)) return 'Tablet';
        return 'Desktop';
    }
}
