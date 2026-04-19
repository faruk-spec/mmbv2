<?php
/**
 * DevZone – Board Controller
 *
 * @package MMB\Projects\DevZone\Controllers
 */

namespace Projects\DevZone\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;

class BoardController
{
    private const DEFAULT_COLUMNS = [
        ['name' => 'To Do',       'color' => '#8892a6', 'sort_order' => 1],
        ['name' => 'In Progress', 'color' => '#ff2ec4', 'sort_order' => 2],
        ['name' => 'Done',        'color' => '#00ff88', 'sort_order' => 3],
    ];

    // ── List all boards ─────────────────────────────────────────── //

    public function index(): void
    {
        $userId = Auth::id();
        $db     = Database::getInstance();
        $boards = [];

        try {
            $boards = $db->fetchAll(
                "SELECT b.*,
                    (SELECT COUNT(*) FROM devzone_tasks t WHERE t.board_id = b.id AND t.is_archived = 0) AS task_count
                 FROM devzone_boards b
                 LEFT JOIN devzone_members m ON m.board_id = b.id AND m.user_id = ?
                 WHERE b.user_id = ? OR m.user_id = ?
                 GROUP BY b.id
                 ORDER BY b.updated_at DESC, b.created_at DESC",
                [$userId, $userId, $userId]
            );
        } catch (\Exception $e) { /* graceful */ }

        $this->render('boards', [
            'title'  => 'My Boards – DevZone',
            'boards' => $boards,
        ]);
    }

    // ── Show single board (Kanban) ──────────────────────────────── //

    public function show(int $id): void
    {
        $userId = Auth::id();
        $db     = Database::getInstance();

        $board   = null;
        $columns = [];

        try {
            $board = $db->fetch(
                "SELECT b.* FROM devzone_boards b
                  LEFT JOIN devzone_members m ON m.board_id = b.id AND m.user_id = ?
                  WHERE b.id = ? AND (b.user_id = ? OR m.user_id = ?)
                  LIMIT 1",
                [$userId, $id, $userId, $userId]
            );

            if (!$board) {
                $_SESSION['_flash']['error'] = 'Board not found.';
                header('Location: /projects/devzone/boards');
                exit;
            }

            $columns = $db->fetchAll(
                "SELECT * FROM devzone_columns WHERE board_id = ? ORDER BY sort_order ASC, id ASC",
                [$id]
            );

            foreach ($columns as &$col) {
                $col['tasks'] = $db->fetchAll(
                    "SELECT * FROM devzone_tasks WHERE board_id = ? AND column_id = ? AND is_archived = 0 ORDER BY sort_order ASC, created_at ASC",
                    [$id, $col['id']]
                );
            }
            unset($col);
        } catch (\Exception $e) {
            $board   = $board ?? ['id' => $id, 'name' => 'Board', 'color' => '#ff2ec4'];
            $columns = [];
        }

        $this->render('board', [
            'title'   => htmlspecialchars($board['name']) . ' – DevZone',
            'board'   => $board,
            'columns' => $columns,
        ]);
    }

    // ── Show create form ────────────────────────────────────────── //

    public function create(): void
    {
        $this->render('board-form', [
            'title' => 'New Board – DevZone',
            'board' => [],
        ]);
    }

    // ── Store new board ─────────────────────────────────────────── //

    public function store(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            header('Location: /projects/devzone/boards/create');
            exit;
        }

        $userId = Auth::id();
        $db     = Database::getInstance();

        $name  = trim(Security::sanitize($_POST['name'] ?? ''));
        $desc  = trim(Security::sanitize($_POST['description'] ?? ''));
        $color = (string)($_POST['color'] ?? '#ff2ec4');

        if (trim($name) === '') {
            $_SESSION['_flash']['error'] = 'Board name is required.';
            header('Location: /projects/devzone/boards/create');
            exit;
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $color = '#ff2ec4';
        }

        try {
            $boardId = $db->insert('devzone_boards', [
                'user_id'     => $userId,
                'name'        => $name,
                'description' => $desc,
                'color'       => $color,
            ]);

            foreach (self::DEFAULT_COLUMNS as $col) {
                $db->insert('devzone_columns', [
                    'board_id'   => $boardId,
                    'name'       => $col['name'],
                    'color'      => $col['color'],
                    'sort_order' => $col['sort_order'],
                ]);
            }

            $_SESSION['_flash']['success'] = 'Board "' . $name . '" created.';
            header('Location: /projects/devzone/boards/' . $boardId);
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to create board.';
            header('Location: /projects/devzone/boards/create');
        }
        exit;
    }

    // ── Show edit form ───────────────────────────────────────────── //

    public function edit(int $id): void
    {
        $userId = Auth::id();
        $db     = Database::getInstance();

        try {
            $board = $db->fetch(
                "SELECT * FROM devzone_boards WHERE id = ? AND user_id = ? LIMIT 1",
                [$id, $userId]
            );
        } catch (\Exception $e) {
            $board = null;
        }

        if (!$board) {
            $_SESSION['_flash']['error'] = 'Board not found.';
            header('Location: /projects/devzone/boards');
            exit;
        }

        $this->render('board-form', [
            'title' => 'Edit Board – DevZone',
            'board' => $board,
        ]);
    }

    // ── Update board ─────────────────────────────────────────────── //

    public function update(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            header('Location: /projects/devzone/boards/' . $id . '/edit');
            exit;
        }

        $userId = Auth::id();
        $db     = Database::getInstance();

        $name  = trim(Security::sanitize($_POST['name'] ?? ''));
        $desc  = trim(Security::sanitize($_POST['description'] ?? ''));
        $color = (string)($_POST['color'] ?? '#ff2ec4');

        if (trim($name) === '') {
            $_SESSION['_flash']['error'] = 'Board name is required.';
            header('Location: /projects/devzone/boards/' . $id . '/edit');
            exit;
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $color = '#ff2ec4';
        }

        try {
            $existing = $db->fetch("SELECT id FROM devzone_boards WHERE id = ? AND user_id = ?", [$id, $userId]);
            if (!$existing) {
                $_SESSION['_flash']['error'] = 'Board not found.';
                header('Location: /projects/devzone/boards');
                exit;
            }
            $db->update('devzone_boards', [
                'name'        => $name,
                'description' => $desc,
                'color'       => $color,
                'updated_at'  => date('Y-m-d H:i:s'),
            ], 'id = ? AND user_id = ?', [$id, $userId]);

            $_SESSION['_flash']['success'] = 'Board updated.';
            header('Location: /projects/devzone/boards/' . $id);
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to update board.';
            header('Location: /projects/devzone/boards/' . $id . '/edit');
        }
        exit;
    }

    // ── Delete board ─────────────────────────────────────────────── //

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
            $existing = $db->fetch("SELECT id FROM devzone_boards WHERE id = ? AND user_id = ?", [$id, $userId]);
            if (!$existing) {
                $_SESSION['_flash']['error'] = 'Board not found.';
                header('Location: /projects/devzone/boards');
                exit;
            }
            // Cascade delete tasks and columns
            $db->query("DELETE FROM devzone_tasks   WHERE board_id = ?", [$id]);
            $db->query("DELETE FROM devzone_columns WHERE board_id = ?", [$id]);
            $db->query("DELETE FROM devzone_members WHERE board_id = ?", [$id]);
            $db->query("DELETE FROM devzone_boards  WHERE id = ? AND user_id = ?", [$id, $userId]);

            $_SESSION['_flash']['success'] = 'Board deleted.';
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to delete board.';
        }

        header('Location: /projects/devzone/boards');
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
