<?php
/**
 * SheetDocs Document Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class DocumentController
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
        $this->db = Database::projectConnection('sheetdocs');
    }
    
    /**
     * List all documents
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        $stmt = $this->db->prepare("
            SELECT * FROM sheet_documents 
            WHERE user_id = :user_id AND type = 'document'
            ORDER BY updated_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        View::render('projects/sheetdocs/documents/index', [
            'documents' => $documents
        ]);
    }
    
    /**
     * Show create form
     */
    public function create(): void
    {
        $userId = Auth::id();
        
        // Check if user can create more documents
        if (!$this->canCreateDocument($userId)) {
            Helpers::setFlash('error', 'You have reached your document limit. Please upgrade to create more documents.');
            Helpers::redirect('/projects/sheetdocs/pricing');
            exit;
        }
        
        // Get templates
        $subscription = $this->getUserSubscription($userId);
        $tier = $subscription['plan'];
        
        $stmt = $this->db->prepare("
            SELECT * FROM sheet_templates 
            WHERE type = 'document' AND (tier = 'free' OR tier = :tier)
            ORDER BY category, title
        ");
        $stmt->execute(['tier' => $tier]);
        $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        View::render('projects/sheetdocs/documents/create', [
            'templates' => $templates
        ]);
    }
    
    /**
     * Store new document
     */
    public function store(): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        if (!$this->canCreateDocument($userId)) {
            Helpers::setFlash('error', 'You have reached your document limit. Please upgrade to create more documents.');
            Helpers::redirect('/projects/sheetdocs/pricing');
            exit;
        }
        
        $title = Security::sanitize($_POST['title'] ?? 'Untitled Document');
        $content = $_POST['content'] ?? '';
        $visibility = in_array($_POST['visibility'] ?? 'private', ['private', 'shared', 'public']) 
            ? $_POST['visibility'] : 'private';
        
        $stmt = $this->db->prepare("
            INSERT INTO sheet_documents (user_id, title, content, type, visibility, last_edited_by)
            VALUES (:user_id, :title, :content, 'document', :visibility, :user_id)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'visibility' => $visibility
        ]);
        
        $documentId = $this->db->lastInsertId();
        
        // Update usage stats
        $this->updateUsageStats($userId);
        
        // Log activity
        $this->logActivity($userId, $documentId, 'create', ['title' => $title]);
        
        Helpers::setFlash('success', 'Document created successfully!');
        Helpers::redirect('/projects/sheetdocs/documents/' . $documentId . '/edit');
    }
    
    /**
     * Show document
     */
    public function show(int $id): void
    {
        $userId = Auth::id();
        $document = $this->getDocument($id, $userId);
        
        if (!$document) {
            Helpers::setFlash('error', 'Document not found or you do not have access.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        // Increment views
        $stmt = $this->db->prepare("UPDATE sheet_documents SET views = views + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        View::render('projects/sheetdocs/documents/show', [
            'document' => $document
        ]);
    }
    
    /**
     * Edit document
     */
    public function edit(int $id): void
    {
        $userId = Auth::id();
        $document = $this->getDocument($id, $userId, true);
        
        if (!$document) {
            Helpers::setFlash('error', 'Document not found or you do not have edit access.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        View::render('projects/sheetdocs/documents/edit', [
            'document' => $document
        ]);
    }
    
    /**
     * Update document
     */
    public function update(int $id): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $document = $this->getDocument($id, $userId, true);
        if (!$document) {
            Helpers::setFlash('error', 'Document not found or you do not have edit access.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        $title = Security::sanitize($_POST['title'] ?? $document['title']);
        $content = $_POST['content'] ?? $document['content'];
        
        $stmt = $this->db->prepare("
            UPDATE sheet_documents 
            SET title = :title, content = :content, last_edited_by = :user_id
            WHERE id = :id
        ");
        
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'user_id' => $userId,
            'id' => $id
        ]);
        
        // Log activity
        $this->logActivity($userId, $id, 'edit', ['title' => $title]);
        
        Helpers::setFlash('success', 'Document updated successfully!');
        Helpers::redirect('/projects/sheetdocs/documents/' . $id . '/edit');
    }
    
    /**
     * Delete document
     */
    public function delete(int $id): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $document = $this->getDocument($id, $userId);
        if (!$document || $document['user_id'] != $userId) {
            Helpers::setFlash('error', 'Document not found or you do not have permission to delete it.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        $stmt = $this->db->prepare("DELETE FROM sheet_documents WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        
        // Update usage stats
        $this->updateUsageStats($userId);
        
        // Log activity
        $this->logActivity($userId, null, 'delete', ['document_id' => $id, 'title' => $document['title']]);
        
        Helpers::setFlash('success', 'Document deleted successfully!');
        Helpers::redirect('/projects/sheetdocs/documents');
    }
    
    /**
     * Get document with access check
     */
    private function getDocument(int $id, int $userId, bool $requireEdit = false): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   ds.permission as share_permission,
                   (d.user_id = :user_id) as is_owner
            FROM sheet_documents d
            LEFT JOIN document_shares ds ON d.id = ds.document_id AND ds.shared_with_user_id = :user_id
            WHERE d.id = :id 
            AND (d.user_id = :user_id OR ds.shared_with_user_id = :user_id OR d.visibility = 'public')
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$document) {
            return null;
        }
        
        // Check edit permission if required
        if ($requireEdit) {
            $hasEditAccess = ($document['is_owner'] == 1) || 
                           ($document['share_permission'] === 'edit');
            if (!$hasEditAccess) {
                return null;
            }
        }
        
        return $document;
    }
    
    /**
     * Check if user can create more documents
     */
    private function canCreateDocument(int $userId): bool
    {
        $subscription = $this->getUserSubscription($userId);
        $features = $this->projectConfig['features'][$subscription['plan']];
        
        if ($features['max_documents'] == -1) {
            return true;
        }
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM sheet_documents 
            WHERE user_id = :user_id AND type = 'document'
        ");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['count'] < $features['max_documents'];
    }
    
    /**
     * Get user subscription
     */
    private function getUserSubscription(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM sheet_user_subscriptions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $subscription ?: ['user_id' => $userId, 'plan' => 'free', 'status' => 'active'];
    }
    
    /**
     * Update usage statistics
     */
    private function updateUsageStats(int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO sheet_usage_stats (user_id, document_count, sheet_count)
            SELECT :user_id,
                   COUNT(CASE WHEN type = 'document' THEN 1 END),
                   COUNT(CASE WHEN type = 'sheet' THEN 1 END)
            FROM sheet_documents WHERE user_id = :user_id
            ON DUPLICATE KEY UPDATE
                document_count = VALUES(document_count),
                sheet_count = VALUES(sheet_count)
        ");
        $stmt->execute(['user_id' => $userId]);
    }
    
    /**
     * Log activity
     */
    private function logActivity(int $userId, ?int $documentId, string $action, array $details = []): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO sheet_activity_logs (user_id, document_id, action, details, ip_address, user_agent)
            VALUES (:user_id, :document_id, :action, :details, :ip, :user_agent)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'document_id' => $documentId,
            'action' => $action,
            'details' => json_encode($details),
            'ip' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
