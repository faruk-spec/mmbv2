<?php
/**
 * SheetDocs Share Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class ShareController
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
     * Show share management for document
     */
    public function show(int $id): void
    {
        $userId = Auth::id();
        
        // Verify ownership
        $stmt = $this->db->prepare("SELECT * FROM sheet_documents WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$document) {
            Helpers::setFlash('error', 'Document not found or you do not have permission.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        // Get existing shares
        $stmt = $this->db->prepare("
            SELECT * FROM sheet_document_shares 
            WHERE document_id = :document_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['document_id' => $id]);
        $shares = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        View::render('projects/sheetdocs/share', [
            'document' => $document,
            'shares' => $shares
        ]);
    }
    
    /**
     * Create a share
     */
    public function create(int $id): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        // Verify ownership
        $stmt = $this->db->prepare("SELECT * FROM sheet_documents WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$document) {
            Helpers::setFlash('error', 'Document not found or you do not have permission.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        $shareType = $_POST['share_type'] ?? 'user';
        $permission = in_array($_POST['permission'] ?? 'view', ['view', 'comment', 'edit']) 
            ? $_POST['permission'] : 'view';
        
        if ($shareType === 'public') {
            // Create public share link
            $token = bin2hex(random_bytes(32));
            
            $stmt = $this->db->prepare("
                INSERT INTO sheet_document_shares 
                (document_id, shared_by_user_id, permission, share_token)
                VALUES (:document_id, :user_id, :permission, :token)
            ");
            
            $stmt->execute([
                'document_id' => $id,
                'user_id' => $userId,
                'permission' => $permission,
                'token' => $token
            ]);
            
            $shareUrl = Helpers::url('/sd/' . $token);
            Helpers::setFlash('success', 'Public share link created: ' . $shareUrl);
        } else {
            // Share with specific user (would need user lookup)
            $sharedWithUserId = (int)($_POST['shared_with_user_id'] ?? 0);
            
            if ($sharedWithUserId > 0) {
                $stmt = $this->db->prepare("
                    INSERT INTO sheet_document_shares 
                    (document_id, shared_with_user_id, shared_by_user_id, permission)
                    VALUES (:document_id, :shared_with, :shared_by, :permission)
                    ON DUPLICATE KEY UPDATE permission = :permission
                ");
                
                $stmt->execute([
                    'document_id' => $id,
                    'shared_with' => $sharedWithUserId,
                    'shared_by' => $userId,
                    'permission' => $permission
                ]);
                
                Helpers::setFlash('success', 'Document shared successfully!');
            }
        }
        
        Helpers::redirect('/projects/sheetdocs/share/' . $id);
    }
    
    /**
     * Revoke a share
     */
    public function revoke(int $id): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $shareId = (int)($_POST['share_id'] ?? 0);
        
        // Verify ownership of the document
        $stmt = $this->db->prepare("
            SELECT ds.* FROM sheet_document_shares ds
            INNER JOIN sheet_documents d ON ds.document_id = d.id
            WHERE ds.id = :share_id AND d.user_id = :user_id
        ");
        $stmt->execute(['share_id' => $shareId, 'user_id' => $userId]);
        $share = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($share) {
            $stmt = $this->db->prepare("DELETE FROM sheet_document_shares WHERE id = :id");
            $stmt->execute(['id' => $shareId]);
            
            Helpers::setFlash('success', 'Share revoked successfully!');
        } else {
            Helpers::setFlash('error', 'Share not found or you do not have permission.');
        }
        
        Helpers::redirect('/projects/sheetdocs/share/' . $id);
    }
}
