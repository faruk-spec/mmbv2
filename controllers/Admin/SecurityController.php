<?php
/**
 * Admin Security Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\Logger;

class SecurityController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Security center dashboard
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        // Get comprehensive security stats
        $stats = [
            'blocked_ips' => $db->fetch("SELECT COUNT(*) as count FROM blocked_ips WHERE expires_at IS NULL OR expires_at > NOW()")['count'] ?? 0,
            'failed_logins_today' => $db->fetch(
                "SELECT COUNT(*) as count FROM failed_logins WHERE DATE(attempted_at) = CURDATE()"
            )['count'] ?? 0,
            'failed_logins_hour' => $db->fetch(
                "SELECT COUNT(*) as count FROM failed_logins WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            )['count'] ?? 0,
            'active_sessions' => $db->fetch("SELECT COUNT(*) as count FROM user_remember_tokens WHERE expires_at > NOW()")['count'] ?? 0,
            'suspicious_ips' => 0 // Will be calculated below
        ];
        
        // Detect suspicious IPs (5+ failed attempts in last hour)
        $suspiciousIps = $db->fetchAll(
            "SELECT ip_address, COUNT(*) as attempts 
             FROM failed_logins 
             WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
             GROUP BY ip_address 
             HAVING attempts >= 5
             ORDER BY attempts DESC"
        );
        $stats['suspicious_ips'] = count($suspiciousIps);
        
        // Get recent failed logins
        $recentFailedLogins = $db->fetchAll(
            "SELECT * FROM failed_logins ORDER BY attempted_at DESC LIMIT 10"
        );
        
        // Get failed login trend (last 24 hours by hour)
        $failedLoginTrend = $db->fetchAll(
            "SELECT HOUR(attempted_at) as hour, COUNT(*) as count 
             FROM failed_logins 
             WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
             GROUP BY HOUR(attempted_at)
             ORDER BY hour"
        );
        
        // Get top targeted usernames
        $topTargetedUsers = $db->fetchAll(
            "SELECT username, COUNT(*) as attempts 
             FROM failed_logins 
             WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY username 
             ORDER BY attempts DESC 
             LIMIT 5"
        );
        
        // Get blocked IPs by reason
        $blockedByReason = $db->fetchAll(
            "SELECT reason, COUNT(*) as count 
             FROM blocked_ips 
             WHERE expires_at IS NULL OR expires_at > NOW()
             GROUP BY reason 
             ORDER BY count DESC"
        );
        
        $this->view('admin/security/index', [
            'title' => 'Security Center',
            'stats' => $stats,
            'recentFailedLogins' => $recentFailedLogins,
            'suspiciousIps' => $suspiciousIps,
            'failedLoginTrend' => $failedLoginTrend,
            'topTargetedUsers' => $topTargetedUsers,
            'blockedByReason' => $blockedByReason
        ]);
    }
    
    /**
     * Blocked IPs list
     */
    public function blockedIps(): void
    {
        $db = Database::getInstance();
        
        $blockedIps = $db->fetchAll(
            "SELECT * FROM blocked_ips ORDER BY created_at DESC"
        );
        
        $this->view('admin/security/blocked-ips', [
            'title' => 'Blocked IPs',
            'blockedIps' => $blockedIps
        ]);
    }
    
    /**
     * Block an IP
     */
    public function blockIp(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/security/blocked-ips');
            return;
        }
        
        $ip = $this->input('ip_address');
        $reason = $this->input('reason', '');
        $duration = $this->input('duration', 'permanent');
        
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->flash('error', 'Invalid IP address.');
            $this->redirect('/admin/security/blocked-ips');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $expiresAt = null;
            if ($duration !== 'permanent') {
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration}"));
            }
            
            $db->insert('blocked_ips', [
                'ip_address' => $ip,
                'reason' => Security::sanitize($reason),
                'blocked_by' => Auth::id(),
                'expires_at' => $expiresAt,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            Logger::activity(Auth::id(), 'ip_blocked', ['ip' => $ip]);
            
            $this->flash('success', 'IP address blocked successfully.');
            
        } catch (\Exception $e) {
            Logger::error('IP blocking error: ' . $e->getMessage());
            $this->flash('error', 'Failed to block IP.');
        }
        
        $this->redirect('/admin/security/blocked-ips');
    }
    
    /**
     * Unblock an IP
     */
    public function unblockIp(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/security/blocked-ips');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $blocked = $db->fetch("SELECT ip_address FROM blocked_ips WHERE id = ?", [(int) $id]);
            
            if ($blocked) {
                $db->delete('blocked_ips', 'id = ?', [(int) $id]);
                Logger::activity(Auth::id(), 'ip_unblocked', ['ip' => $blocked['ip_address']]);
                $this->flash('success', 'IP address unblocked.');
            }
            
        } catch (\Exception $e) {
            Logger::error('IP unblocking error: ' . $e->getMessage());
            $this->flash('error', 'Failed to unblock IP.');
        }
        
        $this->redirect('/admin/security/blocked-ips');
    }
    
    /**
     * Failed logins list
     */
    public function failedLogins(): void
    {
        $db = Database::getInstance();
        
        $page = max(1, (int) $this->input('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $failedLogins = $db->fetchAll(
            "SELECT * FROM failed_logins ORDER BY attempted_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $total = $db->fetch("SELECT COUNT(*) as count FROM failed_logins");
        
        $this->view('admin/security/failed-logins', [
            'title' => 'Failed Logins',
            'failedLogins' => $failedLogins,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total['count'] / $perPage),
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * Auto-block suspicious IPs (AJAX)
     */
    public function autoBlock(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $threshold = (int) ($this->input('threshold', 5));
        $duration = $this->input('duration', '24 hours');
        
        // Validate duration input
        $allowedDurations = ['1 hour', '24 hours', '7 days', '30 days', 'permanent'];
        if (!in_array($duration, $allowedDurations)) {
            $this->json(['success' => false, 'message' => 'Invalid duration']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // Find IPs with excessive failed attempts
            $suspiciousIps = $db->fetchAll(
                "SELECT ip_address, COUNT(*) as attempts 
                 FROM failed_logins 
                 WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY ip_address 
                 HAVING attempts >= ?
                 ORDER BY attempts DESC",
                [$threshold]
            );
            
            $blocked = 0;
            foreach ($suspiciousIps as $ip) {
                // Check if already blocked
                $existing = $db->fetch(
                    "SELECT id FROM blocked_ips WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())",
                    [$ip['ip_address']]
                );
                
                if (!$existing) {
                    $expiresAt = $duration === 'permanent' ? null : date('Y-m-d H:i:s', strtotime("+{$duration}"));
                    
                    $db->insert('blocked_ips', [
                        'ip_address' => $ip['ip_address'],
                        'reason' => "Auto-blocked: {$ip['attempts']} failed login attempts",
                        'blocked_by' => Auth::id(),
                        'expires_at' => $expiresAt,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    Logger::activity(Auth::id(), 'ip_auto_blocked', [
                        'ip' => $ip['ip_address'],
                        'attempts' => $ip['attempts']
                    ]);
                    
                    $blocked++;
                }
            }
            
            $this->json([
                'success' => true,
                'message' => "Auto-blocked {$blocked} suspicious IP(s)",
                'blocked' => $blocked
            ]);
            
        } catch (\Exception $e) {
            Logger::error('Auto-block error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Get security stats (AJAX for live updates)
     */
    public function getStats(): void
    {
        $db = Database::getInstance();
        
        try {
            $stats = [
                'blocked_ips' => $db->fetch("SELECT COUNT(*) as count FROM blocked_ips WHERE expires_at IS NULL OR expires_at > NOW()")['count'] ?? 0,
                'failed_logins_today' => $db->fetch(
                    "SELECT COUNT(*) as count FROM failed_logins WHERE DATE(attempted_at) = CURDATE()"
                )['count'] ?? 0,
                'failed_logins_hour' => $db->fetch(
                    "SELECT COUNT(*) as count FROM failed_logins WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
                )['count'] ?? 0,
                'active_sessions' => $db->fetch("SELECT COUNT(*) as count FROM user_remember_tokens WHERE expires_at > NOW()")['count'] ?? 0,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $this->json(['success' => true, 'stats' => $stats]);
            
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
