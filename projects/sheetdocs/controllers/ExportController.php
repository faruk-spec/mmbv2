<?php
/**
 * SheetDocs Export Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Helpers;

class ExportController
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
     * Export document
     */
    public function export(int $id, string $format): void
    {
        $userId = Auth::id();
        
        // Get document with access check
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   (SELECT permission FROM sheet_document_shares WHERE document_id = d.id AND shared_with_user_id = :user_id) as share_permission
            FROM sheet_documents d
            WHERE d.id = :id 
            AND (d.user_id = :user_id OR d.visibility = 'public' OR EXISTS (
                SELECT 1 FROM sheet_document_shares WHERE document_id = d.id AND shared_with_user_id = :user_id
            ))
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$document) {
            Helpers::flash('error', 'Document not found or you do not have access.');
            Helpers::redirect('/projects/sheetdocs');
            exit;
        }
        
        // Check if user has access to this export format
        $stmt = $this->db->prepare("SELECT plan FROM sheet_user_subscriptions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);
        $plan = $subscription['plan'] ?? 'free';
        
        $allowedFormats = $this->projectConfig['features'][$plan]['export_formats'];
        
        if (!in_array($format, $allowedFormats)) {
            Helpers::flash('error', 'This export format requires a premium subscription.');
            Helpers::redirect('/projects/sheetdocs/pricing');
            exit;
        }
        
        // Export based on format
        switch ($format) {
            case 'pdf':
                $this->exportPDF($document);
                break;
            case 'docx':
                $this->exportDOCX($document);
                break;
            case 'xlsx':
                $this->exportXLSX($document);
                break;
            case 'csv':
                $this->exportCSV($document);
                break;
            default:
                Helpers::flash('error', 'Invalid export format.');
                Helpers::redirect('/projects/sheetdocs/documents/' . $id);
        }
    }
    
    /**
     * Export as PDF (simple HTML to text)
     */
    private function exportPDF(array $document): void
    {
        $filename = Security::sanitizeFilename($document['title']) . '.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // For production, use a library like TCPDF or Dompdf
        // For now, just output plain text
        echo "PDF Export - " . $document['title'] . "\n\n";
        echo strip_tags($document['content']);
        exit;
    }
    
    /**
     * Export as DOCX (plain text for now)
     */
    private function exportDOCX(array $document): void
    {
        $filename = Security::sanitizeFilename($document['title']) . '.docx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // For production, use PHPWord library
        // For now, just output plain text (browser will handle as text file)
        echo strip_tags($document['content']);
        exit;
    }
    
    /**
     * Export as XLSX (CSV for now)
     */
    private function exportXLSX(array $document): void
    {
        $this->exportCSV($document, 'xlsx');
    }
    
    /**
     * Export as CSV
     */
    private function exportCSV(array $document, string $extension = 'csv'): void
    {
        $filename = Security::sanitizeFilename($document['title']) . '.' . $extension;
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Get sheets if it's a spreadsheet
        if ($document['type'] === 'sheet') {
            $stmt = $this->db->prepare("SELECT id FROM sheet_sheets WHERE document_id = :document_id ORDER BY order_index LIMIT 1");
            $stmt->execute(['document_id' => $document['id']]);
            $sheet = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($sheet) {
                $stmt = $this->db->prepare("
                    SELECT row_index, col_index, value 
                    FROM sheet_cells 
                    WHERE sheet_id = :sheet_id 
                    ORDER BY row_index, col_index
                ");
                $stmt->execute(['sheet_id' => $sheet['id']]);
                $cells = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Convert to CSV
                $output = fopen('php://output', 'w');
                $currentRow = -1;
                $rowData = [];
                
                foreach ($cells as $cell) {
                    if ($cell['row_index'] != $currentRow) {
                        if (!empty($rowData)) {
                            fputcsv($output, $rowData);
                        }
                        $currentRow = $cell['row_index'];
                        $rowData = [];
                    }
                    $rowData[$cell['col_index']] = $cell['value'];
                }
                
                if (!empty($rowData)) {
                    fputcsv($output, $rowData);
                }
                
                fclose($output);
            }
        }
        
        exit;
    }
}
