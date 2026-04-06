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
use Core\ActivityLogger;

class SettingsController
{
    /**
     * Show settings page
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        
        $settings = $db->fetch(
            "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        if (!$settings) {
            $db->insert('user_settings', ['user_id' => $user['id']]);
            $settings = $db->fetch(
                "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
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
            $db = Database::getInstance();
            
            $theme = Security::sanitize($_POST['theme'] ?? 'dark');
            $fontSize = (int)($_POST['font_size'] ?? 14);
            $tabSize = (int)($_POST['tab_size'] ?? 2);
            $autoSave = isset($_POST['auto_save']) ? 1 : 0;
            $autoPreview = isset($_POST['auto_preview']) ? 1 : 0;
            $keyBindings = Security::sanitize($_POST['key_bindings'] ?? 'default');
            
            // Ensure settings record exists
            $existing = $db->fetch(
                "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
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
                try { ActivityLogger::logUpdate($user['id'], 'codexpro', 'settings', $user['id'], [], ['theme' => $theme, 'font_size' => $fontSize, 'tab_size' => $tabSize]); } catch (\Throwable $_) {}
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
                try { ActivityLogger::logUpdate($user['id'], 'codexpro', 'settings', $user['id'], [], ['theme' => $theme, 'font_size' => $fontSize, 'tab_size' => $tabSize]); } catch (\Throwable $_) {}
            }
            
            echo json_encode(['success' => true, 'updated' => $updated]);
        } catch (\Exception $e) {
            try { ActivityLogger::logFailure($user['id'] ?? 0, 'update_codexpro_settings', $e->getMessage()); } catch (\Throwable $_) {}
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
