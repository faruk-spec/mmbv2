<?php
/**
 * Analytics Controller
 * Handles QR code analytics and statistics
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Projects\QR\Models\QRModel;

class AnalyticsController
{
    private QRModel $qrModel;
    
    public function __construct()
    {
        $this->qrModel = new QRModel();
    }
    
    /**
     * Show analytics dashboard
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Get analytics data
        $totalQRs = $this->qrModel->countByUser($userId);
        $activeQRs = $this->qrModel->countActiveByUser($userId);
        $recentQRs = $this->qrModel->getByUser($userId, 10);
        
        $this->render('analytics', [
            'title' => 'Analytics',
            'user' => Auth::user(),
            'totalQRs' => $totalQRs,
            'activeQRs' => $activeQRs,
            'recentQRs' => $recentQRs
        ]);
    }
    
    /**
     * Render a project view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        include PROJECT_PATH . '/views/layout.php';
    }
}
