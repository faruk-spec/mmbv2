<?php
/**
 * SheetDocs Public Controller
 * For public shared documents/sheets
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Database;
use Core\View;
use Core\Helpers;

class PublicController
{
    private $db;
    private $projectConfig;
    
    public function __construct()
    {
        $this->projectConfig = require dirname(__DIR__) . '/config.php';
        $this->db = Database::projectConnection('sheetdocs');
    }
    
    /**
     * View publicly shared document
     */
    public function view(string $token): void
    {
        // Get share by token
        $stmt = $this->db->prepare("
            SELECT ds.*, d.*
            FROM sheet_document_shares ds
            INNER JOIN sheet_documents d ON ds.document_id = d.id
            WHERE ds.share_token = :token
            AND (ds.expires_at IS NULL OR ds.expires_at > NOW())
        ");
        $stmt->execute(['token' => $token]);
        $share = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$share) {
            Helpers::setFlash('error', 'This link is invalid or has expired.');
            Helpers::redirect('/');
            exit;
        }
        
        // Increment view count
        $stmt = $this->db->prepare("UPDATE sheet_documents SET views = views + 1 WHERE id = :id");
        $stmt->execute(['id' => $share['document_id']]);
        
        // Render based on type
        if ($share['type'] === 'document') {
            View::render('projects/sheetdocs/public/document', [
                'document' => $share,
                'permission' => $share['permission']
            ]);
        } else {
            // Get sheets
            $stmt = $this->db->prepare("
                SELECT * FROM sheet_sheets 
                WHERE document_id = :document_id 
                ORDER BY order_index
            ");
            $stmt->execute(['document_id' => $share['document_id']]);
            $sheets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            View::render('projects/sheetdocs/public/sheet', [
                'document' => $share,
                'sheets' => $sheets,
                'permission' => $share['permission']
            ]);
        }
    }
}
