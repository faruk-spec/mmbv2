<?php
/**
 * Bulk Generator Controller
 * Handles bulk QR code generation
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;

class BulkController
{
    public function __construct()
    {
    }
    
    /**
     * Show bulk generation page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $this->render('bulk', [
            'title' => 'Bulk Generate',
            'user' => Auth::user()
        ]);
    }
    
    /**
     * Generate bulk QR codes
     */
    public function generate(): void
    {
        // TODO: Implement bulk generation logic
        header('Location: /projects/qr/bulk');
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
