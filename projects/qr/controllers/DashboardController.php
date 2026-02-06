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
            'active_codes' => 0
        ];
        
        if ($userId) {
            try {
                // Get total QR codes count
                $stats['total_generated'] = $this->qrModel->countByUser($userId);
                
                // Get active QR codes and total scans
                $qrCodes = $this->qrModel->getByUser($userId);
                $totalScans = 0;
                $activeCodes = 0;
                
                foreach ($qrCodes as $qr) {
                    $totalScans += (int)($qr['scan_count'] ?? 0);
                    if ($qr['status'] === 'active') {
                        $activeCodes++;
                    }
                }
                
                $stats['total_scans'] = $totalScans;
                $stats['active_codes'] = $activeCodes;
            } catch (\Exception $e) {
                \Core\Logger::error('Failed to fetch QR stats: ' . $e->getMessage());
            }
        }
        
        $this->render('dashboard', [
            'title' => 'QR Generator Dashboard',
            'user' => $user,
            'stats' => $stats
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
