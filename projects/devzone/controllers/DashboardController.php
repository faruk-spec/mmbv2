<?php
/**
 * DevZone – Dashboard Controller
 *
 * @package MMB\Projects\DevZone\Controllers
 */

namespace Projects\DevZone\Controllers;

use Core\Auth;
use Core\Database;

class DashboardController
{
    public function index(): void
    {
        $user   = Auth::user();
        $userId = Auth::id();
        $db     = Database::getInstance();

        $boards = [];
        $stats  = ['boards' => 0, 'tasks' => 0, 'members' => 0];

        try {
            $boards = $db->fetchAll(
                "SELECT b.*, 
                    (SELECT COUNT(*) FROM devzone_tasks t WHERE t.board_id = b.id AND t.is_archived = 0) AS task_count
                 FROM devzone_boards b
                 LEFT JOIN devzone_members m ON m.board_id = b.id AND m.user_id = ?
                 WHERE b.user_id = ? OR m.user_id = ?
                 GROUP BY b.id
                 ORDER BY b.updated_at DESC, b.created_at DESC
                 LIMIT 12",
                [$userId, $userId, $userId]
            );

            $stats['boards']  = (int) ($db->fetchColumn(
                "SELECT COUNT(DISTINCT b.id) FROM devzone_boards b
                  LEFT JOIN devzone_members m ON m.board_id = b.id AND m.user_id = ?
                  WHERE b.user_id = ? OR m.user_id = ?",
                [$userId, $userId, $userId]
            ) ?: 0);
            $stats['tasks'] = (int) ($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE (b.user_id = ? OR t.assignee_id = ?) AND t.is_archived = 0",
                [$userId, $userId]
            ) ?: 0);
            $stats['due_soon'] = (int) ($db->fetchColumn(
                "SELECT COUNT(*) FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE (b.user_id = ? OR t.assignee_id = ?)
                    AND t.is_archived = 0
                    AND t.due_date IS NOT NULL
                    AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
                [$userId, $userId]
            ) ?: 0);
        } catch (\Exception $e) {
            // Tables may not exist yet
        }

        $this->render('dashboard', [
            'title'   => 'DevZone Dashboard',
            'user'    => $user,
            'boards'  => $boards,
            'stats'   => $stats,
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }
}
