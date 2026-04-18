<?php
/**
 * DevZone – Dashboard Controller (ConvertX-style render pattern)
 *
 * @package MMB\Projects\DevZone\Controllers
 */

namespace Projects\DevZone\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;

class DashboardController
{
    // ─────────────────────────────────────────────────────────────── //

    public function index(): void
    {
        $userId = Auth::id();
        $db     = Database::getInstance();

        $boards = [];
        $stats  = ['boards' => 0, 'tasks' => 0, 'members' => 0, 'due_soon' => 0, 'done_this_week' => 0];

        try {
            $boards = $db->fetchAll(
                "SELECT b.*,
                    (SELECT COUNT(*) FROM devzone_tasks t WHERE t.board_id = b.id AND t.is_archived = 0) AS task_count
                 FROM devzone_boards b
                 LEFT JOIN devzone_members m ON m.board_id = b.id AND m.user_id = ?
                 WHERE b.user_id = ? OR m.user_id = ?
                 GROUP BY b.id
                 ORDER BY b.updated_at DESC, b.created_at DESC
                 LIMIT 10",
                [$userId, $userId, $userId]
            );

            $stats['boards'] = (int)($db->fetchColumn(
                "SELECT COUNT(DISTINCT b.id) FROM devzone_boards b
                  LEFT JOIN devzone_members m ON m.board_id = b.id AND m.user_id = ?
                  WHERE b.user_id = ? OR m.user_id = ?",
                [$userId, $userId, $userId]
            ) ?: 0);

            $stats['tasks'] = (int)($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE (b.user_id = ? OR t.user_id = ?) AND t.is_archived = 0",
                [$userId, $userId]
            ) ?: 0);

            $stats['due_soon'] = (int)($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE (b.user_id = ? OR t.user_id = ?)
                    AND t.is_archived = 0
                    AND t.due_date IS NOT NULL
                    AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
                [$userId, $userId]
            ) ?: 0);

            $stats['done_this_week'] = (int)($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  JOIN devzone_columns c ON c.id = t.column_id
                  WHERE (b.user_id = ? OR t.user_id = ?)
                    AND t.is_archived = 0
                    AND LOWER(c.name) IN ('done','complete','completed','closed')
                    AND t.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
                [$userId, $userId]
            ) ?: 0);
        } catch (\Exception $e) {
            // Tables may not exist yet — fail gracefully
        }

        $this->render('dashboard', [
            'title'  => 'DevZone Dashboard',
            'user'   => Auth::user(),
            'boards' => $boards,
            'stats'  => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────── //

    public function settings(): void
    {
        $userId   = Auth::id();
        $db       = Database::getInstance();
        $settings = [];
        $stats    = ['boards' => 0, 'tasks' => 0, 'members' => 0, 'due_soon' => 0];

        try {
            $settings = $db->fetch("SELECT * FROM devzone_settings WHERE user_id = ?", [$userId]) ?: [];

            if (empty($settings)) {
                $db->insert('devzone_settings', [
                    'user_id'             => $userId,
                    'default_board_color' => '#ff2ec4',
                    'email_notifications' => 1,
                    'task_reminders'      => 1,
                ]);
                $settings = $db->fetch("SELECT * FROM devzone_settings WHERE user_id = ?", [$userId]) ?: [];
            }

            $stats['boards'] = (int)($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_boards WHERE user_id = ?", [$userId]
            ) ?: 0);

            $stats['tasks'] = (int)($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE (b.user_id = ? OR t.user_id = ?) AND t.is_archived = 0",
                [$userId, $userId]
            ) ?: 0);

            $stats['members'] = (int)($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_members m
                  JOIN devzone_boards b ON b.id = m.board_id
                  WHERE b.user_id = ?", [$userId]
            ) ?: 0);

            $stats['due_soon'] = (int)($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE (b.user_id = ? OR t.user_id = ?)
                    AND t.is_archived = 0
                    AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
                [$userId, $userId]
            ) ?: 0);
        } catch (\Exception $e) { /* graceful */ }

        $this->render('settings', [
            'title'    => 'DevZone Settings',
            'user'     => Auth::user(),
            'settings' => $settings,
            'stats'    => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────── //

    public function updateSettings(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            header('Location: /projects/devzone/settings');
            exit;
        }

        $userId = Auth::id();
        $db     = Database::getInstance();

        try {
            $color = (string)($_POST['default_board_color'] ?? '#ff2ec4');
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                $color = '#ff2ec4';
            }

            $data = [
                'default_board_color' => $color,
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'task_reminders'      => isset($_POST['task_reminders']) ? 1 : 0,
            ];

            $existing = $db->fetch("SELECT id FROM devzone_settings WHERE user_id = ?", [$userId]);
            if ($existing) {
                $db->update('devzone_settings', $data, 'user_id = ?', [$userId]);
            } else {
                $data['user_id'] = $userId;
                $db->insert('devzone_settings', $data);
            }
            $_SESSION['_flash']['success'] = 'Settings saved.';
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to save settings.';
        }

        header('Location: /projects/devzone/settings');
        exit;
    }

    // ─────────────────────────────────────────────────────────────── //

    private function render(string $view, array $data = []): void
    {
        $currentView = $view;
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }
}
