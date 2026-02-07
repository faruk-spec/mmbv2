<?php
/**
 * Templates Controller
 * Handles QR code templates
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;

class TemplatesController
{
    public function __construct()
    {
    }
    
    /**
     * Show templates page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $this->render('templates', [
            'title' => 'Templates',
            'user' => Auth::user()
        ]);
    }
    
    /**
     * Save template
     */
    public function save(): void
    {
        // TODO: Implement template save logic
        header('Location: /projects/qr/templates');
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
