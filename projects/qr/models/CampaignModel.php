<?php
/**
 * Campaign Model
 * Handles database operations for campaigns
 * 
 * @package MMB\Projects\QR\Models
 */

namespace Projects\QR\Models;

use Core\Database;

class CampaignModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new campaign
     * 
     * @param int $userId User ID
     * @param array $data Campaign data
     * @return int|false Campaign ID or false on failure
     */
    public function create(int $userId, array $data)
    {
        $sql = "INSERT INTO qr_campaigns (user_id, name, description, status, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $params = [
            $userId,
            $data['name'],
            $data['description'] ?? '',
            $data['status'] ?? 'active'
        ];
        
        try {
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to create campaign: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all campaigns for a user
     * 
     * @param int $userId User ID
     * @return array Campaigns with QR count
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT c.*, 
                COUNT(q.id) as qr_count,
                SUM(q.scan_count) as total_scans
                FROM qr_campaigns c
                LEFT JOIN qr_codes q ON c.id = q.campaign_id AND q.deleted_at IS NULL
                WHERE c.user_id = ?
                GROUP BY c.id
                ORDER BY c.created_at DESC";
        
        try {
            return $this->db->query($sql, [$userId])->fetchAll();
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get campaigns: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get campaign by ID
     * 
     * @param int $id Campaign ID
     * @param int $userId User ID for verification
     * @return array|null Campaign data
     */
    public function getById(int $id, int $userId): ?array
    {
        $sql = "SELECT c.*, 
                COUNT(q.id) as qr_count,
                SUM(q.scan_count) as total_scans
                FROM qr_campaigns c
                LEFT JOIN qr_codes q ON c.id = q.campaign_id AND q.deleted_at IS NULL
                WHERE c.id = ? AND c.user_id = ?
                GROUP BY c.id";
        
        try {
            $result = $this->db->query($sql, [$id, $userId])->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get campaign: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update campaign
     * 
     * @param int $id Campaign ID
     * @param int $userId User ID for verification
     * @param array $data Update data
     * @return bool Success status
     */
    public function update(int $id, int $userId, array $data): bool
    {
        $sql = "UPDATE qr_campaigns 
                SET name = ?, description = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?";
        
        $params = [
            $data['name'],
            $data['description'] ?? '',
            $data['status'] ?? 'active',
            $id,
            $userId
        ];
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to update campaign: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete campaign
     * 
     * @param int $id Campaign ID
     * @param int $userId User ID for verification
     * @return bool Success status
     */
    public function delete(int $id, int $userId): bool
    {
        // Note: This will set campaign_id to NULL in qr_codes table
        $sql = "DELETE FROM qr_campaigns WHERE id = ? AND user_id = ?";
        
        try {
            // First, unlink QR codes from this campaign
            $unlinkSql = "UPDATE qr_codes SET campaign_id = NULL WHERE campaign_id = ?";
            $this->db->query($unlinkSql, [$id]);
            
            // Then delete the campaign
            $this->db->query($sql, [$id, $userId]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to delete campaign: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get QR codes for a campaign
     * 
     * @param int $campaignId Campaign ID
     * @param int $userId User ID for verification
     * @return array QR codes
     */
    public function getQRCodes(int $campaignId, int $userId): array
    {
        $sql = "SELECT q.* 
                FROM qr_codes q
                INNER JOIN qr_campaigns c ON q.campaign_id = c.id
                WHERE q.campaign_id = ? AND c.user_id = ? AND q.deleted_at IS NULL
                ORDER BY q.created_at DESC";
        
        try {
            return $this->db->query($sql, [$campaignId, $userId])->fetchAll();
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get campaign QR codes: ' . $e->getMessage());
            return [];
        }
    }
}
