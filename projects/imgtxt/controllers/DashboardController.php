<?php
/**
 * ImgTxt Dashboard Controller
 * 
 * @package MMB\Projects\ImgTxt\Controllers
 */

namespace Projects\ImgTxt\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class DashboardController
{
    /**
     * Show dashboard
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        // Get recent OCR jobs
        $recentJobs = $db->fetchAll(
            "SELECT * FROM ocr_jobs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$user['id']]
        );
        
        // Get statistics
        $stats = [
            'total_jobs' => $db->fetchColumn("SELECT COUNT(*) FROM ocr_jobs WHERE user_id = ?", [$user['id']]),
            'completed_jobs' => $db->fetchColumn("SELECT COUNT(*) FROM ocr_jobs WHERE user_id = ? AND status = 'completed'", [$user['id']]),
            'failed_jobs' => $db->fetchColumn("SELECT COUNT(*) FROM ocr_jobs WHERE user_id = ? AND status = 'failed'", [$user['id']]),
            'processing_jobs' => $db->fetchColumn("SELECT COUNT(*) FROM ocr_jobs WHERE user_id = ? AND status IN ('pending', 'processing')", [$user['id']]),
        ];
        
        // Get today's usage
        $today = date('Y-m-d');
        $todayStats = $db->fetch(
            "SELECT * FROM usage_stats WHERE user_id = ? AND date = ?",
            [$user['id'], $today]
        );
        
        View::render('projects/imgtxt/dashboard', [
            'recentJobs' => $recentJobs,
            'stats' => $stats,
            'todayStats' => $todayStats,
            'title' => 'Dashboard',
            'subtitle' => 'OCR Management Center',
            'currentPage' => 'dashboard',
            'user' => $user,
        ]);
    }
}
