<?php
/**
 * SheetDocs Settings Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class SettingsController
{
    private $db;
    private $projectConfig;
    
    public function __construct()
    {
        if (!Auth::check()) {
            Helpers::redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        $this->projectConfig = require dirname(__DIR__) . '/config.php';
        $this->db = Database::getProjectConnection('sheetdocs', $this->projectConfig['database']);
    }
    
    /**
     * Show settings page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        // Get user subscription
        $stmt = $this->db->prepare("SELECT * FROM sheet_user_subscriptions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Get usage stats
        $stmt = $this->db->prepare("SELECT * FROM sheet_usage_stats WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        View::render('projects/sheetdocs/settings', [
            'subscription' => $subscription,
            'stats' => $stats
        ]);
    }
    
    /**
     * Update settings
     */
    public function update(): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        // Settings can be added here as needed
        // For now, just placeholder
        
        Helpers::setFlash('success', 'Settings updated successfully!');
        Helpers::redirect('/projects/sheetdocs/settings');
    }
}
