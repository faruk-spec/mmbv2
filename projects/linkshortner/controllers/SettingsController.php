<?php
/**
 * LinkShortner Settings Controller
 *
 * @package MMB\Projects\LinkShortner\Controllers
 */

namespace Projects\LinkShortner\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;

class SettingsController
{
    public function index(): void
    {
        $user     = Auth::user();
        $db       = Database::projectConnection('linkshortner');
        $settings = $db->fetch("SELECT * FROM linkshortner_settings WHERE user_id = ?", [$user['id']]);

        View::render('projects/linkshortner/settings', [
            'title'    => 'Settings',
            'subtitle' => 'Manage your preferences',
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/linkshortner/settings');
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');

        $defaultExpiry         = !empty($_POST['default_expiry_days']) ? (int) $_POST['default_expiry_days'] : null;
        $notificationsEnabled  = isset($_POST['notifications_enabled']) ? 1 : 0;

        $existing = $db->fetchColumn("SELECT id FROM linkshortner_settings WHERE user_id = ?", [$user['id']]);
        if ($existing) {
            $db->query(
                "UPDATE linkshortner_settings SET default_expiry_days = ?, notifications_enabled = ?, updated_at = NOW() WHERE user_id = ?",
                [$defaultExpiry, $notificationsEnabled, $user['id']]
            );
        } else {
            $db->query(
                "INSERT INTO linkshortner_settings (user_id, default_expiry_days, notifications_enabled) VALUES (?, ?, ?)",
                [$user['id'], $defaultExpiry, $notificationsEnabled]
            );
        }

        $_SESSION['success'] = 'Settings saved.';
        header('Location: /projects/linkshortner/settings');
        exit;
    }
}
