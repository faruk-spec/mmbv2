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
            foreground_color, background_color, 
            error_correction, frame_style, logo_path,
            is_dynamic, redirect_url, password_hash, expires_at,
            campaign_id, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        
        $params = [
            $userId,
            $data['content'],
            $data['type'] ?? 'text',
            $data['size'] ?? 300,
            $data['foreground_color'] ?? '#000000',
            $data['background_color'] ?? '#ffffff',
            $data['error_correction'] ?? 'H',
            $data['frame_style'] ?? 'none',
            $data['logo_path'] ?? null,
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
     * Get total count of QR codes for a user (including deleted)
     * This shows total generated over time
     * 
     * @param int $userId User ID
     * @return int Total count
     */
    public function countByUser(int $userId): int
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
}
