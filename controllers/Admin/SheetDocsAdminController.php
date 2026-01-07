<?php
/**
 * SheetDocs Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class SheetDocsAdminController
{
    private $projectDb;
    
    public function __construct()
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::isAdmin()) {
            Helpers::redirect('/login');
            exit;
        }
        
        // Get SheetDocs database connection
        $projectConfig = require dirname(dirname(__DIR__)) . '/projects/sheetdocs/config.php';
        $this->projectDb = Database::getProjectConnection('sheetdocs', $projectConfig['database']);
    }
    
    /**
     * Show admin dashboard for SheetDocs
     */
    public function index(): void
    {
        // Get statistics
        $stats = $this->getStatistics();
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity();
        
        // Get subscription overview
        $subscriptionStats = $this->getSubscriptionStats();
        
        View::render('admin/sheetdocs/index', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'subscriptionStats' => $subscriptionStats
        ]);
    }
    
    /**
     * List all documents
     */
    public function documents(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get documents with user info
        $stmt = $this->projectDb->prepare("
            SELECT d.*, 
                   (SELECT COUNT(*) FROM document_shares WHERE document_id = d.id) as share_count
            FROM documents d
            WHERE d.type = 'document'
            ORDER BY d.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get total count
        $totalStmt = $this->projectDb->query("SELECT COUNT(*) as count FROM documents WHERE type = 'document'");
        $total = $totalStmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        View::render('admin/sheetdocs/documents', [
            'documents' => $documents,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    /**
     * List all users and their subscriptions
     */
    public function subscriptions(): void
    {
        $stmt = $this->projectDb->query("
            SELECT us.*, 
                   (SELECT document_count FROM usage_stats WHERE user_id = us.user_id) as document_count,
                   (SELECT sheet_count FROM usage_stats WHERE user_id = us.user_id) as sheet_count
            FROM user_subscriptions us
            ORDER BY us.created_at DESC
        ");
        $subscriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        View::render('admin/sheetdocs/subscriptions', [
            'subscriptions' => $subscriptions
        ]);
    }
    
    /**
     * View activity logs
     */
    public function activityLogs(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->projectDb->prepare("
            SELECT al.*
            FROM activity_logs al
            ORDER BY al.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get total count
        $totalStmt = $this->projectDb->query("SELECT COUNT(*) as count FROM activity_logs");
        $total = $totalStmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        View::render('admin/sheetdocs/activity', [
            'logs' => $logs,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    /**
     * Delete a document
     */
    public function deleteDocument(int $id): void
    {
        Security::validateCsrfToken();
        
        $stmt = $this->projectDb->prepare("DELETE FROM documents WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        Helpers::setFlash('success', 'Document deleted successfully.');
        Helpers::redirect('/admin/sheetdocs/documents');
    }
    
    /**
     * Get statistics
     */
    private function getStatistics(): array
    {
        $stats = [];
        
        // Total documents
        $stmt = $this->projectDb->query("SELECT COUNT(*) as count FROM documents WHERE type = 'document'");
        $stats['total_documents'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        // Total sheets
        $stmt = $this->projectDb->query("SELECT COUNT(*) as count FROM documents WHERE type = 'sheet'");
        $stats['total_sheets'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        // Total users
        $stmt = $this->projectDb->query("SELECT COUNT(DISTINCT user_id) as count FROM documents");
        $stats['total_users'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        // Paid subscribers
        $stmt = $this->projectDb->query("SELECT COUNT(*) as count FROM user_subscriptions WHERE plan = 'paid' AND status IN ('active', 'trial')");
        $stats['paid_subscribers'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        // Total shares
        $stmt = $this->projectDb->query("SELECT COUNT(*) as count FROM document_shares");
        $stats['total_shares'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        
        // Total storage used (approximate from content length)
        $stmt = $this->projectDb->query("SELECT SUM(LENGTH(content)) as total FROM documents");
        $stats['storage_used'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity(int $limit = 10): array
    {
        $stmt = $this->projectDb->prepare("
            SELECT * FROM activity_logs 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get subscription statistics
     */
    private function getSubscriptionStats(): array
    {
        $stmt = $this->projectDb->query("
            SELECT 
                plan,
                status,
                COUNT(*) as count
            FROM user_subscriptions
            GROUP BY plan, status
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
