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
    public function index(): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        $settings = $db->fetch(
            "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
            [$user['id']]
        );

        if (!$settings) {
            $db->insert('codexpro_user_settings', [
                'user_id' => $user['id'],
                'theme' => 'dark',
                'font_size' => 14,
                'tab_size' => 2,
                'auto_save' => 1,
                'auto_preview' => 1,
                'key_bindings' => 'default',
            ]);

            $settings = $db->fetch(
                "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
                [$user['id']]
            );
        }

        View::render('projects/codexpro/settings', [
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $theme       = Security::sanitize($_POST['theme'] ?? 'dark');
            $fontSize    = max(10, min(24, (int)($_POST['font_size'] ?? 14)));
            $tabSize     = max(2, min(8, (int)($_POST['tab_size'] ?? 2)));
            $autoSave    = isset($_POST['auto_save']) ? 1 : 0;
            $autoPreview = isset($_POST['auto_preview']) ? 1 : 0;
            $keyBindings = Security::sanitize($_POST['key_bindings'] ?? 'default');

            $allowedThemes = ['dark', 'light', 'monokai', 'dracula'];
            if (!in_array($theme, $allowedThemes, true)) {
                $theme = 'dark';
            }

            $allowedKeyBindings = ['default', 'vim', 'emacs'];
            if (!in_array($keyBindings, $allowedKeyBindings, true)) {
                $keyBindings = 'default';
            }

            $existing = $db->fetch(
                "SELECT id FROM codexpro_user_settings WHERE user_id = ?",
                [$user['id']]
            );

            $data = [
                'theme' => $theme,
                'font_size' => $fontSize,
                'tab_size' => $tabSize,
                'auto_save' => $autoSave,
                'auto_preview' => $autoPreview,
                'key_bindings' => $keyBindings,
            ];

            if ($existing) {
                $db->update('codexpro_user_settings', $data, 'user_id = ?', [$user['id']]);
            } else {
                $data['user_id'] = $user['id'];
                $db->insert('codexpro_user_settings', $data);
            }

            try {
                ActivityLogger::logUpdate($user['id'], 'codexpro', 'settings', $user['id'], [], [
                    'theme' => $theme,
                    'font_size' => $fontSize,
                    'tab_size' => $tabSize,
                    'key_bindings' => $keyBindings,
                    'auto_save' => $autoSave,
                    'auto_preview' => $autoPreview,
                ]);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            try {
                ActivityLogger::logFailure($user['id'] ?? 0, 'update_codexpro_settings', $e->getMessage());
            } catch (\Throwable $_) {
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
