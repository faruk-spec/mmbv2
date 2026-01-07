<?php
/**
 * SheetDocs Dashboard Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class DashboardController
{
    private $db;
    private $projectConfig;
    
    public function __construct()
    {
        // Check authentication
        if (!Auth::check()) {
            Helpers::redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        // Load project database
        $this->projectConfig = require dirname(__DIR__) . '/config.php';
        $this->db = Database::projectConnection('sheetdocs');
    }
    
    /**
     * Show dashboard
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        // Get user subscription
        $subscription = $this->getUserSubscription($userId);
        
        // Get usage statistics
        $stats = $this->getUsageStats($userId);
        
        // Get recent documents
        $recentDocuments = $this->getRecentDocuments($userId);
        
        // Get recent sheets
        $recentSheets = $this->getRecentSheets($userId);
        
        // Get shared documents
        $sharedWithMe = $this->getSharedDocuments($userId);
        
        // Check limits
        $limits = $this->checkLimits($userId, $subscription);
        
        View::render('projects/sheetdocs/dashboard', [
            'subscription' => $subscription,
            'stats' => $stats,
            'recentDocuments' => $recentDocuments,
            'recentSheets' => $recentSheets,
            'sharedWithMe' => $sharedWithMe,
            'limits' => $limits,
            'config' => $this->projectConfig
        ]);
    }
    
    /**
     * Get user subscription
     */
    private function getUserSubscription(int $userId): array
    {
        $subscription = $this->db->fetch("
            SELECT * FROM sheet_user_subscriptions 
            WHERE user_id = ?
        ", [$userId]);
        
        if (!$subscription) {
            // Create default free subscription
            $this->db->insert('sheet_user_subscriptions', [
                'user_id' => $userId,
                'plan' => 'free',
                'status' => 'active'
            ]);
            
            return [
                'user_id' => $userId,
                'plan' => 'free',
                'status' => 'active',
                'billing_cycle' => null,
                'trial_ends_at' => null,
                'current_period_end' => null
            ];
        }
        
        return $subscription;
    }
    
    /**
     * Get usage statistics
     */
    private function getUsageStats(int $userId): array
    {
        $stats = $this->db->fetch("
            SELECT * FROM sheet_usage_stats 
            WHERE user_id = ?
        ", [$userId]);
        
        if (!$stats) {
            // Create initial stats
            $this->db->insert('sheet_usage_stats', [
                'user_id' => $userId,
                'document_count' => 0,
                'sheet_count' => 0,
                'storage_used' => 0
            ]);
            
            return [
                'user_id' => $userId,
                'document_count' => 0,
                'sheet_count' => 0,
                'storage_used' => 0,
                'api_calls_today' => 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get recent documents
     */
    private function getRecentDocuments(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll("
            SELECT id, title, type, visibility, views, updated_at, created_at
            FROM sheet_documents 
            WHERE user_id = ? AND type = 'document'
            ORDER BY updated_at DESC 
            LIMIT " . (int)$limit, [$userId]);
    }
    
    /**
     * Get recent sheets
     */
    private function getRecentSheets(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll("
            SELECT id, title, type, visibility, views, updated_at, created_at
            FROM sheet_documents 
            WHERE user_id = ? AND type = 'sheet'
            ORDER BY updated_at DESC 
            LIMIT " . (int)$limit, [$userId]);
    }
    
    /**
     * Get documents shared with user
     */
    private function getSharedDocuments(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll("
            SELECT d.id, d.title, d.type, d.visibility, ds.permission, ds.shared_by_user_id, ds.created_at
            FROM sheet_documents d
            INNER JOIN sheet_document_shares ds ON d.id = ds.document_id
            WHERE ds.shared_with_user_id = ?
            AND (ds.expires_at IS NULL OR ds.expires_at > NOW())
            ORDER BY ds.created_at DESC 
            LIMIT " . (int)$limit, [$userId]);
    }
    
    /**
     * Check user limits
     */
    private function checkLimits(int $userId, array $subscription): array
    {
        $plan = $subscription['plan'];
        $features = $this->projectConfig['features'][$plan];
        
        // Get current usage
        $usage = $this->db->fetch("
            SELECT 
                COUNT(CASE WHEN type = 'document' THEN 1 END) as document_count,
                COUNT(CASE WHEN type = 'sheet' THEN 1 END) as sheet_count
            FROM sheet_documents 
            WHERE user_id = ?
        ", [$userId]);
        
        $canCreateDocument = ($features['max_documents'] == -1 || 
                             $usage['document_count'] < $features['max_documents']);
        $canCreateSheet = ($features['max_sheets'] == -1 || 
                          $usage['sheet_count'] < $features['max_sheets']);
        
        return [
            'can_create_document' => $canCreateDocument,
            'can_create_sheet' => $canCreateSheet,
            'documents_remaining' => $features['max_documents'] == -1 ? 
                'unlimited' : $features['max_documents'] - $usage['document_count'],
            'sheets_remaining' => $features['max_sheets'] == -1 ? 
                'unlimited' : $features['max_sheets'] - $usage['sheet_count'],
            'features' => $features,
            'usage' => $usage
        ];
    }
}
