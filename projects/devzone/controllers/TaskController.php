<?php
/**
 * DevZone – Task Controller
 *
 * @package MMB\Projects\DevZone\Controllers
 */

namespace Projects\DevZone\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;

class TaskController
{
    // ── My Tasks list ─────────────────────────────────────────────── //

    public function index(): void
    {
        $userId         = Auth::id();
        $db             = Database::getInstance();
        $filterStatus   = Security::sanitize($_GET['status']   ?? '');
        $filterPriority = Security::sanitize($_GET['priority'] ?? '');
        $tasks          = [];

        try {
            $sql    = "SELECT t.*, c.name AS col_name, b.name AS board_name, b.color AS board_color
                       FROM devzone_tasks t
                       JOIN devzone_boards b  ON b.id = t.board_id
                       JOIN devzone_columns c ON c.id = t.column_id
                       WHERE (b.user_id = ? OR t.user_id = ?) AND t.is_archived = 0";
            $params = [$userId, $userId];

            if ($filterPriority !== '') {
                $sql    .= ' AND t.priority = ?';
                $params[] = $filterPriority;
            }

            $sql .= ' ORDER BY t.due_date ASC, t.created_at DESC LIMIT 200';

            $tasks = $db->fetchAll($sql, $params);

            // Filter by column name after fetch (status maps to col_name)
            if ($filterStatus !== '') {
                $tasks = array_filter($tasks, static function ($t) use ($filterStatus) {
                    $col = strtolower($t['col_name'] ?? '');
                    return match($filterStatus) {
                        'todo'        => in_array($col, ['to do','todo','backlog'], true),
                        'in-progress' => str_contains($col, 'progress'),
                        'done'        => in_array($col, ['done','complete','completed','closed'], true),
                        'blocked'     => str_contains($col, 'block'),
                        default       => true,
                    };
                });
                $tasks = array_values($tasks);
            }
        } catch (\Exception $e) { /* graceful */ }

        $this->render('tasks', [
            'title' => 'My Tasks – DevZone',
            'tasks' => $tasks,
        ]);
    }

    // ── Store new task ─────────────────────────────────────────────── //

    public function store(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            $this->redirectBack('/projects/devzone/boards');
        }

        $userId   = Auth::id();
        $db       = Database::getInstance();
        $boardId  = (int)($_POST['board_id']  ?? 0);
        $columnId = (int)($_POST['column_id'] ?? 0);
        $title    = trim(Security::sanitize($_POST['title'] ?? ''));
        $desc     = trim(Security::sanitize($_POST['description'] ?? ''));
        $priority = Security::sanitize($_POST['priority'] ?? 'medium');
        $dueDate  = trim($_POST['due_date'] ?? '');
        $redirect = '/projects/devzone/boards/' . $boardId;

        if (trim($title) === '' || $boardId < 1 || $columnId < 1) {
            $_SESSION['_flash']['error'] = 'Title and column are required.';
            header('Location: ' . $redirect);
            exit;
        }

        if (!in_array($priority, ['low','medium','high','urgent'], true)) {
            $priority = 'medium';
        }
        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $dueDate = '';
        }

        try {
            // Verify board access
            $board = $db->fetch(
                "SELECT id FROM devzone_boards WHERE id = ? AND user_id = ?",
                [$boardId, $userId]
            );
            if (!$board) {
                $_SESSION['_flash']['error'] = 'Access denied.';
                header('Location: /projects/devzone/boards');
                exit;
            }

            $db->insert('devzone_tasks', [
                'board_id'    => $boardId,
                'column_id'   => $columnId,
                'user_id'     => $userId,
                'title'       => $title,
                'description' => $desc,
                'priority'    => $priority,
                'due_date'    => $dueDate ?: null,
                'is_archived' => 0,
            ]);

            $_SESSION['_flash']['success'] = 'Task added.';
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to save task.';
        }

        header('Location: ' . $redirect);
        exit;
    }

    // ── Update task ───────────────────────────────────────────────── //

    public function update(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            header('Location: /projects/devzone/boards');
            exit;
        }

        $userId   = Auth::id();
        $db       = Database::getInstance();
        $boardId  = (int)($_POST['board_id']  ?? 0);
        $columnId = (int)($_POST['column_id'] ?? 0);
        $title    = trim(Security::sanitize($_POST['title'] ?? ''));
        $desc     = trim(Security::sanitize($_POST['description'] ?? ''));
        $priority = Security::sanitize($_POST['priority'] ?? 'medium');
        $dueDate  = trim($_POST['due_date'] ?? '');
        $redirect = $boardId > 0 ? '/projects/devzone/boards/' . $boardId : '/projects/devzone/tasks';

        if (trim($title) === '') {
            $_SESSION['_flash']['error'] = 'Task title is required.';
            header('Location: ' . $redirect);
            exit;
        }

        if (!in_array($priority, ['low','medium','high','urgent'], true)) {
            $priority = 'medium';
        }
        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $dueDate = '';
        }

        try {
            $existing = $db->fetch(
                "SELECT t.id, t.board_id FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE t.id = ? AND (b.user_id = ? OR t.user_id = ?)",
                [$id, $userId, $userId]
            );
            if (!$existing) {
                $_SESSION['_flash']['error'] = 'Task not found.';
                header('Location: ' . $redirect);
                exit;
            }

            $updateData = [
                'title'       => $title,
                'description' => $desc,
                'priority'    => $priority,
                'due_date'    => $dueDate ?: null,
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
            if ($columnId > 0) {
                $updateData['column_id'] = $columnId;
            }

            $db->update('devzone_tasks', $updateData, 'id = ?', [$id]);
            $_SESSION['_flash']['success'] = 'Task updated.';
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to update task.';
        }

        header('Location: ' . $redirect);
        exit;
    }

    // ── Delete task ───────────────────────────────────────────────── //

    public function delete(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            header('Location: /projects/devzone/boards');
            exit;
        }

        $userId = Auth::id();
        $db     = Database::getInstance();

        try {
            $task = $db->fetch(
                "SELECT t.id, t.board_id FROM devzone_tasks t
                  JOIN devzone_boards b ON b.id = t.board_id
                  WHERE t.id = ? AND (b.user_id = ? OR t.user_id = ?)",
                [$id, $userId, $userId]
            );
            if (!$task) {
                $_SESSION['_flash']['error'] = 'Task not found.';
                header('Location: /projects/devzone/boards');
                exit;
            }
            $boardId = (int)$task['board_id'];
            $db->query("DELETE FROM devzone_tasks WHERE id = ?", [$id]);
            $_SESSION['_flash']['success'] = 'Task deleted.';
            header('Location: /projects/devzone/boards/' . $boardId);
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to delete task.';
            header('Location: /projects/devzone/boards');
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────────── //

    private function redirectBack(string $fallback = '/'): never
    {
        header('Location: ' . ($fallback));
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        $currentView = $view;
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }
}
