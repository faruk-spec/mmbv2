<?php
/**
 * NoteX Admin Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Core\Database;
use Core\View;

class NoteXAdminController extends BaseController
{
    public function overview(): void
    {
        $db = Database::projectConnection('notex');

        $stats = [
            'total_notes'   => (int) $db->fetchColumn("SELECT COUNT(*) FROM notes WHERE status = 'active'"),
            'total_users'   => (int) $db->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM notes"),
            'total_folders' => (int) $db->fetchColumn("SELECT COUNT(*) FROM note_folders"),
            'notes_today'   => (int) $db->fetchColumn("SELECT COUNT(*) FROM notes WHERE DATE(created_at) = CURDATE()"),
        ];

        $recentNotes = $db->fetchAll("SELECT * FROM notes WHERE status = 'active' ORDER BY created_at DESC LIMIT 15");

        View::render('admin/notex/overview', [
            'title'       => 'NoteX Overview',
            'stats'       => $stats,
            'recentNotes' => $recentNotes,
        ]);
    }

    public function notes(): void
    {
        $db     = Database::projectConnection('notex');
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;

        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM notes");
        $notes = $db->fetchAll("SELECT * FROM notes ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);

        View::render('admin/notex/notes', [
            'title'      => 'All Notes',
            'notes'      => $notes,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    public function deleteNote(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/projects/notex/notes');
            exit;
        }

        $db = Database::projectConnection('notex');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->query("DELETE FROM notes WHERE id = ?", [$id]);
        }

        $_SESSION['success'] = 'Note deleted.';
        header('Location: /admin/projects/notex/notes');
        exit;
    }

    public function users(): void
    {
        $db    = Database::projectConnection('notex');
        $users = $db->fetchAll(
            "SELECT user_id, COUNT(*) as note_count, MAX(created_at) as last_note_at FROM notes GROUP BY user_id ORDER BY note_count DESC"
        );

        View::render('admin/notex/users', [
            'title' => 'Users',
            'users' => $users,
        ]);
    }

    public function settings(): void
    {
        View::render('admin/notex/settings', [
            'title' => 'Settings',
        ]);
    }
}
