<?php
/**
 * Settings Controller
 * Handles user settings for QR codes
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;

class SettingsController
{
    public function __construct()
    {
    }
    
    /**
     * Show settings page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $this->render('settings', [
            'title' => 'Settings',
            'user' => Auth::user()
        ]);
    }
    
    /**
     * Update settings
     */
    public function update(): void
    {
        // TODO: Implement settings update logic
        header('Location: /projects/qr/settings');
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
