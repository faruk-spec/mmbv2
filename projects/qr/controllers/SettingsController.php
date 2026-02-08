<?php
/**
 * Settings Controller
 * Handles user settings for QR codes
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Projects\QR\Models\SettingsModel;

class SettingsController
{
    private SettingsModel $model;
    
    public function __construct()
    {
        $this->model = new SettingsModel();
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
        
        // Get user settings
        $settings = $this->model->get($userId);
        
        $this->render('settings', [
            'title' => 'Settings',
            'user' => Auth::user(),
            'settings' => $settings
        ]);
    }
    
    /**
     * Update settings
     */
    public function update(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'default_size' => intval($_POST['default_size'] ?? 300),
                'default_foreground_color' => $_POST['default_foreground_color'] ?? '#000000',
                'default_background_color' => $_POST['default_background_color'] ?? '#ffffff',
                'default_error_correction' => $_POST['default_error_correction'] ?? 'H',
                'default_frame_style' => $_POST['default_frame_style'] ?? 'none',
                'default_download_format' => $_POST['default_download_format'] ?? 'png',
                'auto_save' => isset($_POST['auto_save']) ? 1 : 0,
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'scan_notification_threshold' => intval($_POST['scan_notification_threshold'] ?? 10)
            ];
            
            if ($this->model->save($userId, $data)) {
                $_SESSION['success'] = 'Settings updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update settings';
            }
            
            header('Location: /projects/qr/settings');
            exit;
        }
    }
    
    /**
     * Generate API key
     */
    public function generateApiKey(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $apiKey = $this->model->generateApiKey($userId);
        
        if ($apiKey) {
            echo json_encode(['success' => true, 'api_key' => $apiKey]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to generate API key']);
        }
        exit;
    }
    
    /**
     * Disable API
     */
    public function disableApi(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        if ($this->model->disableApi($userId)) {
            echo json_encode(['success' => true, 'message' => 'API access disabled']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disable API']);
        }
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
