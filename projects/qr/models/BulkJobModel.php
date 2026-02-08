<?php
/**
 * Bulk Job Model
 * Handles database operations for bulk QR generation jobs
 * 
 * @package MMB\Projects\QR\Models
 */

namespace Projects\QR\Models;

use Core\Database;

class BulkJobModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new bulk job
     * 
     * @param int $userId User ID
     * @param array $data Job data
     * @return int|false Job ID or false on failure
     */
    public function create(int $userId, array $data)
    {
        $sql = "INSERT INTO qr_bulk_jobs (
            user_id, campaign_id, total_count, status, created_at
        ) VALUES (?, ?, ?, 'pending', NOW())";
        
        $params = [
            $userId,
            $data['campaign_id'] ?? null,
            $data['total_count']
        ];
        
        try {
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to create bulk job: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all bulk jobs for a user
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to fetch
     * @return array Jobs
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT j.*, c.name as campaign_name
                FROM qr_bulk_jobs j
                LEFT JOIN qr_campaigns c ON j.campaign_id = c.id
                WHERE j.user_id = ?
                ORDER BY j.created_at DESC
                LIMIT ?";
        
        try {
            return $this->db->query($sql, [$userId, $limit])->fetchAll();
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get bulk jobs: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get job by ID
     * 
     * @param int $id Job ID
     * @param int $userId User ID for verification
     * @return array|null Job data
     */
    public function getById(int $id, int $userId): ?array
    {
        $sql = "SELECT j.*, c.name as campaign_name
                FROM qr_bulk_jobs j
                LEFT JOIN qr_campaigns c ON j.campaign_id = c.id
                WHERE j.id = ? AND j.user_id = ?";
        
        try {
            $result = $this->db->query($sql, [$id, $userId])->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get bulk job: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update job progress
     * 
     * @param int $id Job ID
     * @param int $completed Completed count
     * @param int $failed Failed count
     * @return bool Success status
     */
    public function updateProgress(int $id, int $completed, int $failed = 0): bool
    {
        $sql = "UPDATE qr_bulk_jobs 
                SET completed_count = ?, failed_count = ?,
                    status = CASE 
                        WHEN completed_count + failed_count >= total_count THEN 'completed'
                        ELSE 'processing'
                    END
                WHERE id = ?";
        
        try {
            $this->db->query($sql, [$completed, $failed, $id]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to update job progress: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark job as completed
     * 
     * @param int $id Job ID
     * @param string $filePath Path to generated ZIP file
     * @return bool Success status
     */
    public function markCompleted(int $id, string $filePath): bool
    {
        $sql = "UPDATE qr_bulk_jobs 
                SET status = 'completed', file_path = ?, completed_at = NOW()
                WHERE id = ?";
        
        try {
            $this->db->query($sql, [$filePath, $id]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to mark job as completed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark job as failed
     * 
     * @param int $id Job ID
     * @param string $errorLog Error message
     * @return bool Success status
     */
    public function markFailed(int $id, string $errorLog): bool
    {
        $sql = "UPDATE qr_bulk_jobs 
                SET status = 'failed', error_log = ?, completed_at = NOW()
                WHERE id = ?";
        
        try {
            $this->db->query($sql, [$errorLog, $id]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to mark job as failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete old completed jobs
     * 
     * @param int $days Days to keep
     * @return bool Success status
     */
    public function deleteOldJobs(int $days = 30): bool
    {
        $sql = "DELETE FROM qr_bulk_jobs 
                WHERE status IN ('completed', 'failed') 
                AND completed_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        try {
            $this->db->query($sql, [$days]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to delete old jobs: ' . $e->getMessage());
            return false;
        }
    }
}
