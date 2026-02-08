<?php
/**
 * Template Model
 * Handles database operations for QR templates
 * 
 * @package MMB\Projects\QR\Models
 */

namespace Projects\QR\Models;

use Core\Database;

class TemplateModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new template
     * 
     * @param int $userId User ID
     * @param array $data Template data
     * @return int|false Template ID or false on failure
     */
    public function create(int $userId, array $data)
    {
        $sql = "INSERT INTO qr_templates (user_id, name, settings, is_public, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $settings = json_encode($data['settings']);
        
        $params = [
            $userId,
            $data['name'],
            $settings,
            $data['is_public'] ?? 0
        ];
        
        try {
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to create template: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all templates for a user
     * 
     * @param int $userId User ID
     * @return array Templates
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT * FROM qr_templates 
                WHERE user_id = ? OR is_public = 1
                ORDER BY created_at DESC";
        
        try {
            $results = $this->db->query($sql, [$userId])->fetchAll();
            
            // Decode JSON settings
            foreach ($results as &$template) {
                $template['settings'] = json_decode($template['settings'], true);
            }
            
            return $results;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get templates: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get template by ID
     * 
     * @param int $id Template ID
     * @param int $userId User ID for verification
     * @return array|null Template data
     */
    public function getById(int $id, int $userId): ?array
    {
        $sql = "SELECT * FROM qr_templates 
                WHERE id = ? AND (user_id = ? OR is_public = 1)";
        
        try {
            $result = $this->db->query($sql, [$id, $userId])->fetch();
            
            if ($result) {
                $result['settings'] = json_decode($result['settings'], true);
                return $result;
            }
            
            return null;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get template: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update template
     * 
     * @param int $id Template ID
     * @param int $userId User ID for verification
     * @param array $data Update data
     * @return bool Success status
     */
    public function update(int $id, int $userId, array $data): bool
    {
        $sql = "UPDATE qr_templates 
                SET name = ?, settings = ?, is_public = ?
                WHERE id = ? AND user_id = ?";
        
        $settings = json_encode($data['settings']);
        
        $params = [
            $data['name'],
            $settings,
            $data['is_public'] ?? 0,
            $id,
            $userId
        ];
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to update template: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete template
     * 
     * @param int $id Template ID
     * @param int $userId User ID for verification
     * @return bool Success status
     */
    public function delete(int $id, int $userId): bool
    {
        $sql = "DELETE FROM qr_templates WHERE id = ? AND user_id = ?";
        
        try {
            $this->db->query($sql, [$id, $userId]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to delete template: ' . $e->getMessage());
            return false;
        }
    }
}
