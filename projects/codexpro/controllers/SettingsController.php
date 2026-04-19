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
                'user_id'          => $user['id'],
                'theme'            => 'dark',
                'font_size'        => 14,
                'font_family'      => 'JetBrains Mono',
                'tab_size'         => 2,
                'auto_save'        => 1,
                'auto_preview'     => 1,
                'key_bindings'     => 'default',
                'word_wrap'        => 0,
                'line_numbers'     => 1,
                'bracket_matching' => 1,
                'auto_indent'      => 1,
                'indent_guides'    => 1,
                'highlight_line'   => 1,
                'show_minimap'     => 0,
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
            $fontSize    = max(10, min(28, (int)($_POST['font_size'] ?? 14)));
            $tabSize     = max(2, min(8, (int)($_POST['tab_size'] ?? 2)));
            $autoSave    = isset($_POST['auto_save']) ? 1 : 0;
            $autoPreview = isset($_POST['auto_preview']) ? 1 : 0;
            $keyBindings = Security::sanitize($_POST['key_bindings'] ?? 'default');

            // New settings
            $fontFamily      = Security::sanitize($_POST['font_family'] ?? 'JetBrains Mono');
            $wordWrap        = isset($_POST['word_wrap']) ? 1 : 0;
            $lineNumbers     = isset($_POST['line_numbers']) ? 1 : 0;
            $bracketMatching = isset($_POST['bracket_matching']) ? 1 : 0;
            $autoIndent      = isset($_POST['auto_indent']) ? 1 : 0;
            $indentGuides    = isset($_POST['indent_guides']) ? 1 : 0;
            $highlightLine   = isset($_POST['highlight_line']) ? 1 : 0;
            $showMinimap     = isset($_POST['show_minimap']) ? 1 : 0;

            $allowedThemes = ['dark', 'light', 'monokai', 'dracula', 'nord', 'material', 'github'];
            if (!in_array($theme, $allowedThemes, true)) {
                $theme = 'dark';
            }

            $allowedKeyBindings = ['default', 'vim', 'emacs'];
            if (!in_array($keyBindings, $allowedKeyBindings, true)) {
                $keyBindings = 'default';
            }

            $allowedFonts = ['JetBrains Mono', 'Fira Code', 'Cascadia Code', 'Source Code Pro', 'Consolas', 'Courier New', 'monospace'];
            if (!in_array($fontFamily, $allowedFonts, true)) {
                $fontFamily = 'JetBrains Mono';
            }

            $existing = $db->fetch(
                "SELECT id FROM codexpro_user_settings WHERE user_id = ?",
                [$user['id']]
            );

            $data = [
                'theme'            => $theme,
                'font_size'        => $fontSize,
                'font_family'      => $fontFamily,
                'tab_size'         => $tabSize,
                'auto_save'        => $autoSave,
                'auto_preview'     => $autoPreview,
                'key_bindings'     => $keyBindings,
                'word_wrap'        => $wordWrap,
                'line_numbers'     => $lineNumbers,
                'bracket_matching' => $bracketMatching,
                'auto_indent'      => $autoIndent,
                'indent_guides'    => $indentGuides,
                'highlight_line'   => $highlightLine,
                'show_minimap'     => $showMinimap,
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
                    'font_family' => $fontFamily,
                    'tab_size' => $tabSize,
                    'key_bindings' => $keyBindings,
                    'auto_save' => $autoSave,
                    'auto_preview' => $autoPreview,
                    'word_wrap' => $wordWrap,
                    'line_numbers' => $lineNumbers,
                    'bracket_matching' => $bracketMatching,
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
