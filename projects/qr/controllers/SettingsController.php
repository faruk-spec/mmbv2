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
                // Design defaults
                'default_corner_style' => $_POST['default_corner_style'] ?? 'square',
                'default_dot_style' => $_POST['default_dot_style'] ?? 'square',
                'default_marker_border_style' => $_POST['default_marker_border_style'] ?? 'square',
                'default_marker_center_style' => $_POST['default_marker_center_style'] ?? 'square',
                // Logo defaults
                'default_logo_color' => $_POST['default_logo_color'] ?? '#9945ff',
                'default_logo_size' => floatval($_POST['default_logo_size'] ?? 0.30),
                'default_logo_remove_bg' => isset($_POST['default_logo_remove_bg']) ? 1 : 0,
                // Advanced defaults
                'default_gradient_enabled' => isset($_POST['default_gradient_enabled']) ? 1 : 0,
                'default_gradient_color' => $_POST['default_gradient_color'] ?? '#9945ff',
                'default_transparent_bg' => isset($_POST['default_transparent_bg']) ? 1 : 0,
                'default_custom_marker_color' => isset($_POST['default_custom_marker_color']) ? 1 : 0,
                'default_marker_color' => $_POST['default_marker_color'] ?? '#9945ff',
                // Preferences
                'auto_save' => isset($_POST['auto_save']) ? 1 : 0,
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'scan_notification_threshold' => intval($_POST['scan_notification_threshold'] ?? 10)
            ];
            
            try {
                if ($this->model->save($userId, $data)) {
                    $_SESSION['success'] = 'Settings updated successfully';
                    \Core\Logger::info('User ' . $userId . ' successfully updated settings');
                } else {
                    $_SESSION['error'] = 'Failed to update settings. Please check the logs or try again.';
                    \Core\Logger::error('Settings save returned false for user ' . $userId);
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
                \Core\Logger::error('Exception during settings save for user ' . $userId . ': ' . $e->getMessage());
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
