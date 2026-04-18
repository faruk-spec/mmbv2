<?php
/**
 * ProShare Dashboard Controller
 * 
 * @package MMB\Projects\ProShare\Controllers
 */

namespace Projects\ProShare\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class DashboardController
{
    /**
     * Show project dashboard
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        
        // Get statistics
        $stats = [
            'total_files' => $db->fetchColumn("SELECT COUNT(*) FROM proshare_files WHERE user_id = ?", [$user['id']]),
            'total_texts' => $db->fetchColumn("SELECT COUNT(*) FROM proshare_text_shares WHERE user_id = ?", [$user['id']]),
            'total_downloads' => $db->fetchColumn(
                "SELECT SUM(downloads) FROM proshare_files WHERE user_id = ?", 
                [$user['id']]
            ) ?: 0,
            'active_shares' => $db->fetchColumn(
                "SELECT COUNT(*) FROM proshare_files WHERE user_id = ? AND status = 'active'", 
                [$user['id']]
            ),
        ];
        
        // Get recent files
        $recentFiles = $db->fetchAll(
            "SELECT * FROM proshare_files WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        );
        
        // Get recent text shares
        $recentTexts = $db->fetchAll(
            "SELECT * FROM proshare_text_shares WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        );
        
        // Get unread notifications
        $notifications = $db->fetchAll(
            "SELECT * FROM proshare_notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        );
        
        View::render('projects/proshare/dashboard', [
            'title' => 'Dashboard',
            'subtitle' => 'Welcome to ProShare',
            'stats' => $stats,
            'recentFiles' => $recentFiles,
            'recentTexts' => $recentTexts,
            'notifications' => $notifications,
        ]);
    }
    
    /**
     * Show my files
     */
    public function myFiles(): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        
        $files = $db->fetchAll(
            "SELECT * FROM proshare_files WHERE user_id = ? ORDER BY created_at DESC",
            [$user['id']]
        );
        
        $texts = $db->fetchAll(
            "SELECT * FROM proshare_text_shares WHERE user_id = ? ORDER BY created_at DESC",
            [$user['id']]
        );
        
        View::render('projects/proshare/files-list', [
            'title' => 'My Files',
            'subtitle' => 'Manage your shared files and text shares',
            'files' => $files,
            'texts' => $texts,
        ]);
    }
}

