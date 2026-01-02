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

class DashboardController
{
    /**
     * Show project dashboard
     */
    public function index(): void
    {
        $user = Auth::user();
        
        // Get user's QR code stats (placeholder - would need project DB)
        $stats = [
            'total_generated' => 0,
            'total_scans' => 0,
            'active_codes' => 0
        ];
        
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
