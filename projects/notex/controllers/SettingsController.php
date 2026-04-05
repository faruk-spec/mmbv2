<?php
/**
 * NoteX Settings Controller
 *
 * @package MMB\Projects\NoteX\Controllers
 */

namespace Projects\NoteX\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;

class SettingsController
{
    public function index(): void
    {
        $user     = Auth::user();
        $db       = Database::projectConnection('notex');
        $settings = $db->fetch("SELECT * FROM notex_settings WHERE user_id = ?", [$user['id']]);
        $tags     = $db->fetchAll("SELECT * FROM note_tags WHERE user_id = ? ORDER BY name ASC", [$user['id']]);

        View::render('projects/notex/settings', [
            'title'    => 'Settings',
            'subtitle' => 'Preferences & Tags',
            'settings' => $settings,
            'tags'     => $tags,
        ]);
    }

    public function update(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/notex/settings');
            exit;
        }

        $user      = Auth::user();
        $db        = Database::projectConnection('notex');
        $color     = $_POST['default_color'] ?? '#ffd700';
        $autoSave  = isset($_POST['auto_save']) ? 1 : 0;
        $theme     = in_array($_POST['theme'] ?? '', ['dark', 'light']) ? $_POST['theme'] : 'dark';

        $existing  = $db->fetchColumn("SELECT id FROM notex_settings WHERE user_id = ?", [$user['id']]);
        if ($existing) {
            $db->query(
                "UPDATE notex_settings SET default_color = ?, auto_save = ?, theme = ?, updated_at = NOW() WHERE user_id = ?",
                [$color, $autoSave, $theme, $user['id']]
            );
        } else {
            $db->query(
                "INSERT INTO notex_settings (user_id, default_color, auto_save, theme) VALUES (?, ?, ?, ?)",
                [$user['id'], $color, $autoSave, $theme]
            );
        }

        // Handle new tag creation
        $newTag = trim($_POST['new_tag'] ?? '');
        if ($newTag !== '') {
            $tagColor = $_POST['new_tag_color'] ?? '#ffd700';
            $db->query(
                "INSERT IGNORE INTO note_tags (user_id, name, color) VALUES (?, ?, ?)",
                [$user['id'], $newTag, $tagColor]
            );
        }

        // Delete tags
        $deleteTagId = !empty($_POST['delete_tag']) ? (int) $_POST['delete_tag'] : null;
        if ($deleteTagId) {
            $db->query("DELETE FROM note_tags WHERE id = ? AND user_id = ?", [$deleteTagId, $user['id']]);
        }

        $_SESSION['success'] = 'Settings saved.';
        header('Location: /projects/notex/settings');
        exit;
    }
}
