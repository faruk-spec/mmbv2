<?php
/**
 * QR Code Model
 * Handles database operations for QR codes
 * 
 * @package MMB\Projects\QR\Models
 */

namespace Projects\QR\Models;

use Core\Database;

class QRModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Save QR code to database with enhanced features
     * 
     * @param int $userId User ID
     * @param array $data QR code data
     * @return int|false QR code ID or false on failure
     */
    public function save(int $userId, array $data)
    {
        $sql = "INSERT INTO qr_codes (
            user_id, content, type, size, 
            foreground_color, background_color, error_correction,
            gradient_enabled, gradient_color, transparent_bg,
            corner_style, dot_style, marker_border_style, marker_center_style,
            custom_marker_color, marker_color, 
            frame_style, frame_label, frame_font, frame_color,
            logo_path, logo_color, logo_size, logo_remove_bg,
            is_dynamic, redirect_url, password_hash, expires_at,
            campaign_id, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        
        $params = [
            $userId,
            $data['content'],
            $data['type'] ?? 'text',
            $data['size'] ?? 300,
            $data['foreground_color'] ?? '#000000',
            $data['background_color'] ?? '#ffffff',
            $data['error_correction'] ?? 'H',
            $data['gradient_enabled'] ?? 0,
            $data['gradient_color'] ?? '#9945ff',
            $data['transparent_bg'] ?? 0,
            $data['corner_style'] ?? 'square',
            $data['dot_style'] ?? 'dots',
            $data['marker_border_style'] ?? 'square',
            $data['marker_center_style'] ?? 'square',
            $data['custom_marker_color'] ?? 0,
            $data['marker_color'] ?? null,
            $data['frame_style'] ?? 'none',
            $data['frame_label'] ?? null,
            $data['frame_font'] ?? null,
            $data['frame_color'] ?? null,
            $data['logo_path'] ?? null,
            $data['logo_color'] ?? '#9945ff',
            $data['logo_size'] ?? 0.3,
            $data['logo_remove_bg'] ?? 0,
            $data['is_dynamic'] ?? 0,
            $data['redirect_url'] ?? null,
            $data['password_hash'] ?? null,
            $data['expires_at'] ?? null,
            $data['campaign_id'] ?? null
        ];
        
        try {
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to save QR code: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update short code for a QR code
     * 
     * @param int $qrId QR code ID
     * @param string $shortCode Short code to set
     * @return bool Success status
     */
    public function updateShortCode(int $qrId, string $shortCode): bool
    {
        $sql = "UPDATE qr_codes SET short_code = ? WHERE id = ?";
        
        try {
            $this->db->query($sql, [$shortCode, $qrId]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to update short code: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get QR codes for a user
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to fetch
     * @param int $offset Offset for pagination
     * @return array QR codes
     */
    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM qr_codes 
                WHERE user_id = ? AND deleted_at IS NULL
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        try {
            $results = $this->db->fetchAll($sql, [$userId, $limit, $offset]);
            return $results ?: [];
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to fetch QR codes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get ALL QR codes for a user (including deleted)
     * Used for analytics to show complete history
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to fetch
     * @param int $offset Offset for pagination
     * @return array QR codes
     */
    public function getAllByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM qr_codes 
                WHERE user_id = ?
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        try {
            $results = $this->db->fetchAll($sql, [$userId, $limit, $offset]);
            return $results ?: [];
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to fetch all QR codes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count QR codes by user
     */
    public function countByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM qr_codes 
                WHERE user_id = ? AND deleted_at IS NULL";
        
        try {
            $result = $this->db->fetch($sql, [$userId]);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to count QR codes: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get a single QR code by ID
     * 
     * @param int $id QR code ID
     * @param int $userId User ID (for security)
     * @return array|null QR code data or null if not found
     */
    public function getById(int $id, int $userId): ?array
    {
        $sql = "SELECT * FROM qr_codes WHERE id = ? AND user_id = ? AND deleted_at IS NULL";
        
        try {
            $result = $this->db->fetch($sql, [$id, $userId]);
            return $result ?: null;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to fetch QR code: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Delete a QR code (soft delete)
     * 
     * @param int $id QR code ID
     * @param int $userId User ID (for security)
     * @return bool Success status
     */
    public function delete(int $id, int $userId): bool
    {
        // Use soft delete to preserve total count
        $sql = "UPDATE qr_codes SET deleted_at = NOW() WHERE id = ? AND user_id = ? AND deleted_at IS NULL";
        
        try {
            $this->db->query($sql, [$id, $userId]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to delete QR code: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get count of active (non-deleted) QR codes for a user
     * 
     * @param int $userId User ID
     * @return int Active count
     */
    public function countActiveByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM qr_codes WHERE user_id = ? AND deleted_at IS NULL";
        
        try {
            $result = $this->db->fetch($sql, [$userId]);
            return (int) ($result['count'] ?? 0);
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to count active QR codes: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total count of QR codes for a user (including deleted)
     * This shows total generated over time
     * 
     * @param int $userId User ID
     * @return int Total count
     */
    public function countAllByUser(int $userId): int
    {
        // Count ALL QR codes including deleted ones
        $sql = "SELECT COUNT(*) as count FROM qr_codes WHERE user_id = ?";
        
        try {
            $result = $this->db->fetch($sql, [$userId]);
            return (int) ($result['count'] ?? 0);
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to count QR codes: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get scan statistics for dashboard
     * 
     * @param int $userId User ID
     * @return array Statistics array with today, this_week, this_month, total
     */
    public function getScanStats(int $userId): array
    {
        $sql = "SELECT 
                    SUM(scan_count) as total_scans,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN scan_count ELSE 0 END) as scans_today,
                    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN scan_count ELSE 0 END) as scans_this_week,
                    SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN scan_count ELSE 0 END) as scans_this_month
                FROM qr_codes 
                WHERE user_id = ? AND deleted_at IS NULL";
        
        try {
            $result = $this->db->fetch($sql, [$userId]);
            return [
                'total' => (int)($result['total_scans'] ?? 0),
                'today' => (int)($result['scans_today'] ?? 0),
                'this_week' => (int)($result['scans_this_week'] ?? 0),
                'this_month' => (int)($result['scans_this_month'] ?? 0)
            ];
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get scan stats: ' . $e->getMessage());
            return ['total' => 0, 'today' => 0, 'this_week' => 0, 'this_month' => 0];
        }
    }
    
    /**
     * Get recent QR codes for dashboard
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to fetch
     * @return array Recent QR codes
     */
    public function getRecentByUser(int $userId, int $limit = 10): array
    {
        $sql = "SELECT id, content, type, scan_count, created_at 
                FROM qr_codes 
                WHERE user_id = ? AND deleted_at IS NULL
                ORDER BY created_at DESC 
                LIMIT ?";
        
        try {
            $results = $this->db->fetchAll($sql, [$userId, $limit]);
            return $results ?: [];
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to fetch recent QR codes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top performing QR codes by scan count
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to fetch
     * @return array Top QR codes
     */
    public function getTopByScans(int $userId, int $limit = 5): array
    {
        $sql = "SELECT id, content, type, scan_count, created_at 
                FROM qr_codes 
                WHERE user_id = ? AND deleted_at IS NULL AND scan_count > 0
                ORDER BY scan_count DESC 
                LIMIT ?";
        
        try {
            $results = $this->db->fetchAll($sql, [$userId, $limit]);
            return $results ?: [];
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to fetch top QR codes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update scan count for a QR code
     * 
     * @param int $id QR code ID
     * @return bool Success status
     */
    public function incrementScanCount(int $id): bool
    {
        $sql = "UPDATE qr_codes 
                SET scan_count = scan_count + 1,
                    last_scanned_at = NOW()
                WHERE id = ?";
        
        try {
            $this->db->query($sql, [$id]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to update scan count: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update QR code data
     * 
     * @param int $id QR code ID
     * @param int $userId User ID (for security)
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update(int $id, int $userId, array $data): bool
    {
        // Build UPDATE query dynamically based on provided data
        $updates = [];
        $params = [];
        
        if (isset($data['redirect_url'])) {
            $updates[] = "redirect_url = ?";
            $params[] = $data['redirect_url'];
        }
        
        if (isset($data['password_hash'])) {
            $updates[] = "password_hash = ?";
            $params[] = $data['password_hash'];
        }
        
        if (isset($data['expires_at'])) {
            $updates[] = "expires_at = ?";
            $params[] = $data['expires_at'];
        }
        
        if (isset($data['status'])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (isset($data['content'])) {
            $updates[] = "content = ?";
            $params[] = $data['content'];
        }
        
        if (array_key_exists('campaign_id', $data)) {
            $updates[] = "campaign_id = ?";
            $params[] = $data['campaign_id'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        // Add WHERE conditions
        $params[] = $id;
        $params[] = $userId;
        
        $sql = "UPDATE qr_codes SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?";
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to update QR code: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get QR code by short code
     * 
     * @param string $shortCode Short code to search for
     * @return array|null QR code data or null if not found
     */
    public function getByShortCode(string $shortCode): ?array
    {
        $sql = "SELECT * FROM qr_codes WHERE short_code = ? AND deleted_at IS NULL";
        
        try {
            $result = $this->db->fetch($sql, [$shortCode]);
            if ($result === false) {
                return null;
            }
            return $result;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to fetch QR code by short code: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Track QR code scan
     * Records scan in qr_scans table and updates QR code scan count
     * 
     * @param int $qrId QR code ID
     * @param array $data Additional scan data (IP, user agent, location, etc.)
     * @return bool Success status
     */
    public function trackScan(int $qrId, array $data = []): bool
    {
        try {
            // Update scan count on QR code
            $this->incrementScanCount($qrId);
            
            // Record detailed scan info if qr_scans table exists
            $scanData = [
                'qr_code_id' => $qrId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'referer' => $_SERVER['HTTP_REFERER'] ?? null,
                'scanned_at' => date('Y-m-d H:i:s')
            ];
            
            // Merge with any additional data provided
            $scanData = array_merge($scanData, $data);
            
            $sql = "INSERT INTO qr_scans (qr_code_id, ip_address, user_agent, referer, scanned_at) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $this->db->query($sql, [
                $scanData['qr_code_id'],
                $scanData['ip_address'],
                $scanData['user_agent'],
                $scanData['referer'],
                $scanData['scanned_at']
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to track QR code scan: ' . $e->getMessage());
            // Don't fail the entire request if tracking fails
            return false;
        }
    }
    
    /**
     * Get all QR codes for a user with date filter
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to fetch
     * @param int $offset Offset for pagination
     * @param string|null $startDate Start date filter (Y-m-d)
     * @param string|null $endDate End date filter (Y-m-d)
     * @return array QR codes
     */
    public function getAllByUserWithDateFilter(int $userId, int $limit = 50, int $offset = 0, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT * FROM qr_codes WHERE user_id = ?";
        $params = [$userId];
        
        if ($startDate) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        try {
            $results = $this->db->fetchAll($sql, $params);
            return $results ?: [];
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to fetch QR codes with date filter: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count QR codes for a user with date filter
     * 
     * @param int $userId User ID
     * @param string|null $startDate Start date filter (Y-m-d)
     * @param string|null $endDate End date filter (Y-m-d)
     * @return int Count
     */
    public function countAllByUserWithDateFilter(int $userId, ?string $startDate = null, ?string $endDate = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM qr_codes WHERE user_id = ?";
        $params = [$userId];
        
        if ($startDate) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $endDate;
        }
        
        try {
            $result = $this->db->fetch($sql, $params);
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to count QR codes with date filter: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get scan trends for chart (last N days)
     * 
     * @param int $userId User ID
     * @param int $days Number of days to include
     * @return array Scan trends data
     */
    public function getScanTrends(int $userId, int $days = 30): array
    {
        // Generate date range
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = date('Y-m-d', strtotime("-$i days"));
        }
        
        // Get scan counts by date
        $sql = "SELECT DATE(created_at) as date, SUM(scan_count) as scans
                FROM qr_codes
                WHERE user_id = ? 
                AND deleted_at IS NULL
                AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date";
        
        try {
            $results = $this->db->fetchAll($sql, [$userId, $days]);
            
            // Create a map of date => scans
            $scanMap = [];
            foreach ($results as $row) {
                $scanMap[$row['date']] = (int)$row['scans'];
            }
            
            // Fill in all dates with 0 for missing days
            $data = [
                'labels' => [],
                'values' => []
            ];
            
            foreach ($dates as $date) {
                $data['labels'][] = date('M j', strtotime($date));
                $data['values'][] = isset($scanMap[$date]) ? $scanMap[$date] : 0;
            }
            
            return $data;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get scan trends: ' . $e->getMessage());
            return ['labels' => [], 'values' => []];
        }
    }
}
