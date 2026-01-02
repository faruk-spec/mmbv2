<?php
/**
 * ImgTxt Batch Controller
 * 
 * @package MMB\Projects\ImgTxt\Controllers
 */

namespace Projects\ImgTxt\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class BatchController
{
    /**
     * Show batch processing page
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $batches = $db->fetchAll(
            "SELECT * FROM batch_jobs WHERE user_id = ? ORDER BY created_at DESC",
            [$user['id']]
        );
        
        View::render('projects/imgtxt/batch', [
            'batches' => $batches,
            'title' => 'Batch Processing',
            'subtitle' => 'Manage bulk OCR jobs',
            'currentPage' => 'batch',
            'user' => $user,
        ]);
    }
    
    /**
     * Create batch job
     */
    public function create(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $name = $_POST['name'] ?? 'Batch ' . date('Y-m-d H:i:s');
        
        // Create batch job
        $batchId = $db->insert('batch_jobs', [
            'user_id' => $user['id'],
            'name' => $name,
            'status' => 'pending',
        ]);
        
        if ($batchId) {
            echo json_encode([
                'success' => true,
                'batch_id' => $batchId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to create batch job'
            ]);
        }
    }
    
    /**
     * Show batch details
     */
    public function show(int $id): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $batch = $db->fetch(
            "SELECT * FROM batch_jobs WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );
        
        if (!$batch) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        
        // Get batch files
        $files = $db->fetchAll(
            "SELECT o.* FROM ocr_jobs o
             JOIN batch_job_files bjf ON o.id = bjf.ocr_job_id
             WHERE bjf.batch_id = ?
             ORDER BY o.created_at",
            [$id]
        );
        
        View::render('projects/imgtxt/batch-detail', [
            'batch' => $batch,
            'files' => $files,
            'title' => 'Batch #' . $id,
            'subtitle' => 'View batch details',
            'currentPage' => 'batch',
            'user' => $user,
        ]);
    }
    
    /**
     * Download batch results
     */
    public function download(int $id): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $batch = $db->fetch(
            "SELECT * FROM batch_jobs WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );
        
        if (!$batch) {
            http_response_code(404);
            echo "Batch not found";
            return;
        }
        
        // Get all completed files
        $files = $db->fetchAll(
            "SELECT o.* FROM ocr_jobs o
             JOIN batch_job_files bjf ON o.id = bjf.ocr_job_id
             WHERE bjf.batch_id = ? AND o.status = 'completed'",
            [$id]
        );
        
        if (empty($files)) {
            echo "No completed files in this batch";
            return;
        }
        
        // Create ZIP file
        $zipFilename = tempnam(sys_get_temp_dir(), 'batch_') . '.zip';
        $zip = new \ZipArchive();
        
        if ($zip->open($zipFilename, \ZipArchive::CREATE) !== true) {
            echo "Failed to create ZIP file";
            return;
        }
        
        foreach ($files as $file) {
            $filename = pathinfo($file['original_filename'], PATHINFO_FILENAME) . '.txt';
            $zip->addFromString($filename, $file['extracted_text']);
        }
        
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="batch_' . $id . '_results.zip"');
        header('Content-Length: ' . filesize($zipFilename));
        
        readfile($zipFilename);
        unlink($zipFilename);
    }
}
