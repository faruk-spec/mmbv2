<?php
/**
 * SheetDocs Sheet Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class SheetController
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
     * List all sheets
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        $stmt = $this->db->prepare("
            SELECT * FROM sheet_documents 
            WHERE user_id = :user_id AND type = 'sheet'
            ORDER BY updated_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $sheets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        View::render('projects/sheetdocs/sheets/index', [
            'sheets' => $sheets
        ]);
    }
    
    /**
     * Show create form
     */
    public function create(): void
    {
        $userId = Auth::id();
        
        if (!$this->canCreateSheet($userId)) {
            Helpers::setFlash('error', 'You have reached your sheet limit. Please upgrade to create more sheets.');
            Helpers::redirect('/projects/sheetdocs/pricing');
            exit;
        }
        
        View::render('projects/sheetdocs/sheets/create');
    }
    
    /**
     * Store new sheet
     */
    public function store(): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        if (!$this->canCreateSheet($userId)) {
            Helpers::setFlash('error', 'You have reached your sheet limit.');
            Helpers::redirect('/projects/sheetdocs/pricing');
            exit;
        }
        
        $title = Security::sanitize($_POST['title'] ?? 'Untitled Spreadsheet');
        $visibility = in_array($_POST['visibility'] ?? 'private', ['private', 'shared', 'public']) 
            ? $_POST['visibility'] : 'private';
        
        // Create document
        $stmt = $this->db->prepare("
            INSERT INTO sheet_documents (user_id, title, type, visibility, last_edited_by)
            VALUES (:user_id, :title, 'sheet', :visibility, :user_id)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'visibility' => $visibility
        ]);
        
        $documentId = $this->db->lastInsertId();
        
        // Create default sheet
        $stmt = $this->db->prepare("
            INSERT INTO sheet_sheets (document_id, name, order_index, row_count, col_count)
            VALUES (:document_id, 'Sheet1', 0, 100, 26)
        ");
        $stmt->execute(['document_id' => $documentId]);
        
        // Update usage stats
        $this->updateUsageStats($userId);
        
        // Log activity
        $this->logActivity($userId, $documentId, 'create', ['title' => $title, 'type' => 'sheet']);
        
        Helpers::setFlash('success', 'Spreadsheet created successfully!');
        Helpers::redirect('/projects/sheetdocs/sheets/' . $documentId . '/edit');
    }
    
    /**
     * Show sheet
     */
    public function show(int $id): void
    {
        $userId = Auth::id();
        $sheet = $this->getSheet($id, $userId);
        
        if (!$sheet) {
            Helpers::setFlash('error', 'Sheet not found or you do not have access.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        // Get sheet tabs
        $sheetTabs = $this->getSheetTabs($id);
        
        // Increment views
        $stmt = $this->db->prepare("UPDATE sheet_documents SET views = views + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        View::render('projects/sheetdocs/sheets/show', [
            'sheet' => $sheet,
            'sheetTabs' => $sheetTabs
        ]);
    }
    
    /**
     * Edit sheet
     */
    public function edit(int $id): void
    {
        $userId = Auth::id();
        $sheet = $this->getSheet($id, $userId, true);
        
        if (!$sheet) {
            Helpers::setFlash('error', 'Sheet not found or you do not have edit access.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        // Get sheet tabs
        $sheetTabs = $this->getSheetTabs($id);
        
        // Get cells for active sheet
        $activeSheetId = $sheetTabs[0]['id'] ?? null;
        $cells = $activeSheetId ? $this->getSheetCells($activeSheetId) : [];
        
        View::render('projects/sheetdocs/sheets/edit', [
            'sheet' => $sheet,
            'sheetTabs' => $sheetTabs,
            'cells' => $cells
        ]);
    }
    
    /**
     * Update sheet
     */
    public function update(int $id): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $sheet = $this->getSheet($id, $userId, true);
        if (!$sheet) {
            Helpers::setFlash('error', 'Sheet not found or you do not have edit access.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        $title = Security::sanitize($_POST['title'] ?? $sheet['title']);
        
        $stmt = $this->db->prepare("
            UPDATE sheet_documents 
            SET title = :title, last_edited_by = :user_id
            WHERE id = :id
        ");
        
        $stmt->execute([
            'title' => $title,
            'user_id' => $userId,
            'id' => $id
        ]);
        
        // Log activity
        $this->logActivity($userId, $id, 'edit', ['title' => $title]);
        
        Helpers::setFlash('success', 'Spreadsheet updated successfully!');
        Helpers::redirect('/projects/sheetdocs/sheets/' . $id . '/edit');
    }
    
    /**
     * Delete sheet
     */
    public function delete(int $id): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $sheet = $this->getSheet($id, $userId);
        if (!$sheet || $sheet['user_id'] != $userId) {
            Helpers::setFlash('error', 'Sheet not found or you do not have permission to delete it.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        $stmt = $this->db->prepare("DELETE FROM sheet_documents WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        
        // Update usage stats
        $this->updateUsageStats($userId);
        
        // Log activity
        $this->logActivity($userId, null, 'delete', ['document_id' => $id, 'title' => $sheet['title']]);
        
        Helpers::setFlash('success', 'Spreadsheet deleted successfully!');
        Helpers::redirect('/projects/sheetdocs/sheets');
    }
    
    /**
     * Get sheet with access check
     */
    private function getSheet(int $id, int $userId, bool $requireEdit = false): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   ds.permission as share_permission,
                   (d.user_id = :user_id) as is_owner
            FROM sheet_documents d
            LEFT JOIN document_shares ds ON d.id = ds.document_id AND ds.shared_with_user_id = :user_id
            WHERE d.id = :id AND d.type = 'sheet'
            AND (d.user_id = :user_id OR ds.shared_with_user_id = :user_id OR d.visibility = 'public')
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $sheet = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$sheet) {
            return null;
        }
        
        if ($requireEdit) {
            $hasEditAccess = ($sheet['is_owner'] == 1) || ($sheet['share_permission'] === 'edit');
            if (!$hasEditAccess) {
                return null;
            }
        }
        
        return $sheet;
    }
    
    /**
     * Get sheet tabs
     */
    private function getSheetTabs(int $documentId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM sheet_sheets 
            WHERE document_id = :document_id 
            ORDER BY order_index
        ");
        $stmt->execute(['document_id' => $documentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get sheet cells
     */
    private function getSheetCells(int $sheetId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM sheet_cells 
            WHERE sheet_id = :sheet_id
        ");
        $stmt->execute(['sheet_id' => $sheetId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if user can create more sheets
     */
    private function canCreateSheet(int $userId): bool
    {
        $subscription = $this->getUserSubscription($userId);
        $features = $this->projectConfig['features'][$subscription['plan']];
        
        if ($features['max_sheets'] == -1) {
            return true;
        }
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM sheet_documents 
            WHERE user_id = :user_id AND type = 'sheet'
        ");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['count'] < $features['max_sheets'];
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
