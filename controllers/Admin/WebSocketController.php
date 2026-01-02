<?php
/**
 * WebSocket Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;

class WebSocketController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * WebSocket Status
     */
    public function status(): void
    {
        $db = Database::getInstance();
        
        // Get WebSocket settings
        $settings = [];
        $settingsData = $db->fetchAll(
            "SELECT * FROM system_settings 
             WHERE setting_key LIKE 'websocket_%' 
             ORDER BY setting_key"
        );
        
        foreach ($settingsData as $row) {
            $settings[$row['setting_key']] = json_decode($row['setting_value'], true);
        }
        
        // Check if WebSocket server is running
        $host = $settings['websocket_host'] ?? 'localhost';
        $port = $settings['websocket_port'] ?? 8080;
        
        $isRunning = @fsockopen($host, $port, $errno, $errstr, 1);
        $serverStatus = $isRunning ? 'online' : 'offline';
        if ($isRunning) {
            fclose($isRunning);
        }
        
        $this->view('admin/websocket/status', [
            'title' => 'WebSocket Status',
            'serverStatus' => $serverStatus,
            'settings' => $settings,
            'host' => $host,
            'port' => $port
        ]);
    }
    
    /**
     * Active Connections
     */
    public function connections(): void
    {
        // In a real implementation, this would connect to the WebSocket server
        // to get active connections. For now, we'll show a placeholder.
        
        $this->view('admin/websocket/connections', [
            'title' => 'Active WebSocket Connections',
            'connections' => [] // Would be populated from WebSocket server
        ]);
    }
    
    /**
     * Rooms Management
     */
    public function rooms(): void
    {
        // In a real implementation, this would show active rooms
        // For now, we'll show a placeholder.
        
        $this->view('admin/websocket/rooms', [
            'title' => 'WebSocket Rooms',
            'rooms' => [] // Would be populated from WebSocket server
        ]);
    }
    
    /**
     * WebSocket Settings
     */
    public function settings(): void
    {
        $db = Database::getInstance();
        
        // Get WebSocket settings
        $settings = $db->fetchAll(
            "SELECT * FROM system_settings 
             WHERE setting_key LIKE 'websocket_%' 
             ORDER BY setting_key"
        );
        
        $this->view('admin/websocket/settings', [
            'title' => 'WebSocket Settings',
            'settings' => $settings
        ]);
    }
    
    /**
     * Update WebSocket Settings (AJAX)
     */
    public function updateSettings(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $db = Database::getInstance();
        
        try {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'websocket_') === 0) {
                    $db->execute(
                        "UPDATE system_settings 
                         SET setting_value = ? 
                         WHERE setting_key = ?",
                        [json_encode($value), $key]
                    );
                }
            }
            
            $this->jsonResponse(['success' => true, 'message' => 'Settings updated successfully']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
