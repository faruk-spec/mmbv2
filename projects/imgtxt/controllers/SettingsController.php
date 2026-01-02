<?php
/**
 * ImgTxt Settings Controller
 * 
 * @package MMB\Projects\ImgTxt\Controllers
 */

namespace Projects\ImgTxt\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;

class SettingsController
{
    /**
     * Show settings page
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $settings = $db->fetch(
            "SELECT * FROM user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        if (!$settings) {
            $db->insert('user_settings', ['user_id' => $user['id']]);
            $settings = $db->fetch("SELECT * FROM user_settings WHERE user_id = ?", [$user['id']]);
        }
        
        View::render('projects/imgtxt/settings', [
            'settings' => $settings,
            'title' => 'Settings',
            'subtitle' => 'Configure OCR preferences',
            'currentPage' => 'settings',
            'user' => $user,
        ]);
    }
    
    /**
     * Update settings
     */
    public function update(): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            $db = Database::projectConnection('imgtxt');
            
            $defaultLanguage = Security::sanitize($_POST['default_language'] ?? 'eng');
            $autoDownload = isset($_POST['auto_download']) ? 1 : 0;
            $outputFormat = Security::sanitize($_POST['output_format'] ?? 'txt');
            $keepHistory = isset($_POST['keep_history']) ? 1 : 0;
            
            // Check if settings exist
            $existing = $db->fetch(
                "SELECT * FROM user_settings WHERE user_id = ?",
                [$user['id']]
            );
            
            if ($existing) {
                // Update existing settings
                $updated = $db->update('user_settings', [
                    'default_language' => $defaultLanguage,
                    'auto_download' => $autoDownload,
                    'output_format' => $outputFormat,
                    'keep_history' => $keepHistory,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'user_id = ?', [$user['id']]);
            } else {
                // Insert new settings
                $updated = $db->insert('user_settings', [
                    'user_id' => $user['id'],
                    'default_language' => $defaultLanguage,
                    'auto_download' => $autoDownload,
                    'output_format' => $outputFormat,
                    'keep_history' => $keepHistory,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Settings saved successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ]);
        }
    }
}
