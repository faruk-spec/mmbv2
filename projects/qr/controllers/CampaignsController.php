<?php
/**
 * Campaigns Controller
 * Handles QR code campaigns
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;

class CampaignsController
{
    public function __construct()
    {
    }
    
    /**
     * Show campaigns page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $this->render('campaigns', [
            'title' => 'Campaigns',
            'user' => Auth::user()
        ]);
    }
    
    /**
     * Save campaign
     */
    public function save(): void
    {
        // TODO: Implement campaign save logic
        header('Location: /projects/qr/campaigns');
        exit;
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
