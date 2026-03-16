<?php
/**
 * ImgTxt History Controller
 * 
 * @package MMB\Projects\ImgTxt\Controllers
 */

namespace Projects\ImgTxt\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\ActivityLogger;

class HistoryController
{
    /**
     * Show history page
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $jobs = $db->fetchAll(
            "SELECT * FROM ocr_jobs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100",
            [$user['id']]
        );
        
        View::render('projects/imgtxt/history', [
            'jobs' => $jobs,
            'title' => 'OCR History',
            'subtitle' => 'View all past OCR jobs',
            'currentPage' => 'history',
            'user' => $user,
        ]);
    }
    
    /**
     * Delete job
     */
    public function delete(int $id): void
    {
        // Clear any output that may have been generated
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            $db = Database::projectConnection('imgtxt');
            
            $job = $db->fetch(
                "SELECT * FROM ocr_jobs WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );
            
            if (!$job) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Job not found']);
                return;
            }
            
            // Delete file
            if (!empty($job['file_path']) && file_exists($job['file_path'])) {
                @unlink($job['file_path']);
            }
            
            // Delete from database
            $deleted = $db->delete('ocr_jobs', 'id = ? AND user_id = ?', [$id, $user['id']]);
            
            if ($deleted) {
                try { ActivityLogger::logDelete($user['id'], 'imgtxt', 'ocr_job', $id, ['filename' => $job['original_filename'] ?? null]); } catch (\Throwable $_) {}
                echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete job']);
            }
        } catch (\Exception $e) {
            try { ActivityLogger::logFailure($user['id'] ?? null, 'ocr_job_delete', $e->getMessage()); } catch (\Throwable $_) {}
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Clear all history
     */
    public function clear(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        // Get all user jobs
        $jobs = $db->fetchAll(
            "SELECT * FROM ocr_jobs WHERE user_id = ?",
            [$user['id']]
        );
        
        // Delete files
        foreach ($jobs as $job) {
            if (file_exists($job['file_path'])) {
                @unlink($job['file_path']);
            }
        }
        
        // Delete from database
        $db->query("DELETE FROM ocr_jobs WHERE user_id = ?", [$user['id']]);
        
        try { ActivityLogger::log($user['id'], 'history_cleared', ['module' => 'imgtxt', 'resource_type' => 'ocr_job', 'new_values' => ['cleared_count' => count($jobs)]]); } catch (\Throwable $_) {}
        
        echo json_encode(['success' => true]);
    }
}
