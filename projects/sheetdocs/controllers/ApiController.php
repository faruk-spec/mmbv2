<?php
/**
 * SheetDocs API Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Helpers;

class ApiController
{
    private $db;
    private $projectConfig;
    
    public function __construct()
    {
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
        
        $this->projectConfig = require dirname(__DIR__) . '/config.php';
        $this->db = Database::projectConnection('sheetdocs');
    }
    
    /**
     * Update a cell value (for spreadsheets)
     */
    public function updateCell(): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $sheetId = (int)($_POST['sheet_id'] ?? 0);
        $rowIndex = (int)($_POST['row_index'] ?? 0);
        $colIndex = (int)($_POST['col_index'] ?? 0);
        $value = $_POST['value'] ?? '';
        $formula = $_POST['formula'] ?? null;
        
        // Verify access to sheet
        $stmt = $this->db->prepare("
            SELECT s.*, d.user_id, d.id as document_id,
                   (SELECT permission FROM sheet_document_shares WHERE document_id = d.id AND shared_with_user_id = :user_id) as share_permission
            FROM sheet_sheets s
            INNER JOIN sheet_documents d ON s.document_id = d.id
            WHERE s.id = :sheet_id
        ");
        $stmt->execute(['sheet_id' => $sheetId, 'user_id' => $userId]);
        $sheet = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$sheet) {
            $this->jsonResponse(['error' => 'Sheet not found'], 404);
            exit;
        }
        
        // Check edit permission
        $hasEditAccess = ($sheet['user_id'] == $userId) || ($sheet['share_permission'] === 'edit');
        if (!$hasEditAccess) {
            $this->jsonResponse(['error' => 'No edit permission'], 403);
            exit;
        }
        
        // Update or insert cell
        $stmt = $this->db->prepare("
            INSERT INTO sheet_cells (sheet_id, row_index, col_index, value, formula)
            VALUES (:sheet_id, :row_index, :col_index, :value, :formula)
            ON DUPLICATE KEY UPDATE 
                value = :value,
                formula = :formula,
                updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([
            'sheet_id' => $sheetId,
            'row_index' => $rowIndex,
            'col_index' => $colIndex,
            'value' => $value,
            'formula' => $formula
        ]);
        
        // Update document timestamp
        $stmt = $this->db->prepare("
            UPDATE sheet_documents 
            SET last_edited_by = :user_id 
            WHERE id = :document_id
        ");
        $stmt->execute([
            'user_id' => $userId,
            'document_id' => $sheet['document_id']
        ]);
        
        $this->jsonResponse(['success' => true, 'cell_updated' => true]);
    }
    
    /**
     * Auto-save document content
     */
    public function autosave(): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $documentId = (int)($_POST['document_id'] ?? 0);
        $content = $_POST['content'] ?? '';
        
        // Verify access
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   (SELECT permission FROM sheet_document_shares WHERE document_id = d.id AND shared_with_user_id = :user_id) as share_permission
            FROM sheet_documents d
            WHERE d.id = :document_id
        ");
        $stmt->execute(['document_id' => $documentId, 'user_id' => $userId]);
        $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$document) {
            $this->jsonResponse(['error' => 'Document not found'], 404);
            exit;
        }
        
        // Check edit permission
        $hasEditAccess = ($document['user_id'] == $userId) || ($document['share_permission'] === 'edit');
        if (!$hasEditAccess) {
            $this->jsonResponse(['error' => 'No edit permission'], 403);
            exit;
        }
        
        // Update document
        $stmt = $this->db->prepare("
            UPDATE sheet_documents 
            SET content = :content, last_edited_by = :user_id 
            WHERE id = :document_id
        ");
        
        $stmt->execute([
            'content' => $content,
            'user_id' => $userId,
            'document_id' => $documentId
        ]);
        
        $this->jsonResponse([
            'success' => true,
            'saved_at' => date('Y-m-d H:i:s'),
            'message' => 'Auto-saved'
        ]);
    }
    
    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
