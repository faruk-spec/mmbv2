<?php
/**
 * CodeXPro Settings Controller
 * 
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

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
        $db = Database::projectConnection('codexpro');
        
        $settings = $db->fetch(
            "SELECT * FROM user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        if (!$settings) {
            $db->insert('user_settings', ['user_id' => $user['id']]);
            $settings = $db->fetch(
                "SELECT * FROM user_settings WHERE user_id = ?",
                [$user['id']]
            );
        }
        
        View::render('projects/codexpro/settings', [
            'settings' => $settings,
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
            $db = Database::projectConnection('codexpro');
            
            $theme = Security::sanitize($_POST['theme'] ?? 'dark');
            $fontSize = (int)($_POST['font_size'] ?? 14);
            $tabSize = (int)($_POST['tab_size'] ?? 2);
            $autoSave = isset($_POST['auto_save']) ? 1 : 0;
            $autoPreview = isset($_POST['auto_preview']) ? 1 : 0;
            $keyBindings = Security::sanitize($_POST['key_bindings'] ?? 'default');
            
            // Ensure settings record exists
            $existing = $db->fetch(
                "SELECT * FROM user_settings WHERE user_id = ?",
                [$user['id']]
            );
            
            if (!$existing) {
                // Insert new settings
                $db->insert('user_settings', [
                    'user_id' => $user['id'],
                    'theme' => $theme,
                    'font_size' => $fontSize,
                    'tab_size' => $tabSize,
                    'auto_save' => $autoSave,
                    'auto_preview' => $autoPreview,
                    'key_bindings' => $keyBindings,
                ]);
                $updated = true;
            } else {
                // Update existing settings
                $updated = $db->update('user_settings', [
                    'theme' => $theme,
                    'font_size' => $fontSize,
                    'tab_size' => $tabSize,
                    'auto_save' => $autoSave,
                    'auto_preview' => $autoPreview,
                    'key_bindings' => $keyBindings,
                ], ['user_id' => $user['id']]);
            }
            
            echo json_encode(['success' => true, 'updated' => $updated]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
