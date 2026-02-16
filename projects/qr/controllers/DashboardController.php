<?php
/**
 * QR Generator Dashboard Controller
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Projects\QR\Models\QRModel;

class DashboardController
{
    private QRModel $qrModel;
    
    public function __construct()
    {
        $this->qrModel = new QRModel();
    }
    
    /**
     * Show project dashboard
     */
    public function index(): void
    {
        $user = Auth::user();
        $userId = Auth::id();
        
        // Get user's QR code stats from database
        $stats = [
            'total_generated' => 0,
            'total_scans' => 0,
            'active_codes' => 0,
            'scans_today' => 0,
            'scans_this_week' => 0,
            'average_scans' => 0
        ];
        
        $recentQRs = [];
        $topQRs = [];
        
        if ($userId) {
            try {
                // Get total QR codes count (including deleted) - this never decreases
                $stats['total_generated'] = $this->qrModel->countAllByUser($userId);
                
                // Get active (non-deleted) QR codes
                $stats['active_codes'] = $this->qrModel->countActiveByUser($userId);
                
                // Get scan statistics
                $scanStats = $this->qrModel->getScanStats($userId);
                $stats['total_scans'] = $scanStats['total'];
                $stats['scans_today'] = $scanStats['today'];
                $stats['scans_this_week'] = $scanStats['this_week'];
                
                // Calculate average scans per QR code
                if ($stats['active_codes'] > 0) {
                    $stats['average_scans'] = round($stats['total_scans'] / $stats['active_codes'], 1);
                }
                
                // Get recent activity (last 10 QR codes)
                $recentQRs = $this->qrModel->getRecentByUser($userId, 10);
                
                // Get top performing QR codes (top 5 by scans)
                $topQRs = $this->qrModel->getTopByScans($userId, 5);
                
            } catch (\Exception $e) {
                \Core\Logger::error('Failed to fetch QR stats: ' . $e->getMessage());
            }
        }
        
        $this->render('dashboard', [
            'title' => 'QR Generator Dashboard',
            'user' => $user,
            'stats' => $stats,
            'recentQRs' => $recentQRs,
            'topQRs' => $topQRs
        ]);
    }
    
    /**
     * Render a project view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        // Start output buffering
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        // Include layout
        include PROJECT_PATH . '/views/layout.php';
    }
}
