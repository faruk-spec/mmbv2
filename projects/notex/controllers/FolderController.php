<?php
/**
 * NoteX Folder Controller
 *
 * @package MMB\Projects\NoteX\Controllers
 */

namespace Projects\NoteX\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;

class FolderController
{
    public function index(): void
    {
        $user    = Auth::user();
        $db      = Database::projectConnection('notex');
        $folders = $db->fetchAll(
            "SELECT nf.*, COUNT(n.id) as note_count FROM notex_folders nf LEFT JOIN notex_notes n ON n.folder_id = nf.id AND n.status = 'active' WHERE nf.user_id = ? GROUP BY nf.id ORDER BY nf.sort_order ASC",
            [$user['id']]
        );

        View::render('projects/notex/folders', [
            'title'   => 'Folders',
            'subtitle'=> 'Organise your notes',
            'folders' => $folders,
        ]);
    }

    public function create(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/notex/folders');
            exit;
        }

        $user  = Auth::user();
        $db    = Database::projectConnection('notex');
        $name  = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? '#ffd700';

        if (empty($name)) {
            $_SESSION['error'] = 'Folder name is required.';
            header('Location: /projects/notex/folders');
            exit;
        }

        $db->query("INSERT INTO notex_folders (user_id, name, color) VALUES (?, ?, ?)", [$user['id'], $name, $color]);

        $_SESSION['success'] = "Folder '{$name}' created.";
        header('Location: /projects/notex/folders');
        exit;
    }

    public function delete(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/notex/folders');
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('notex');
        // Move notes out of this folder before deleting
        $db->query("UPDATE notex_notes SET folder_id = NULL WHERE folder_id = ? AND user_id = ?", [$id, $user['id']]);
        $db->query("DELETE FROM notex_folders WHERE id = ? AND user_id = ?", [$id, $user['id']]);

        $_SESSION['success'] = 'Folder deleted.';
        header('Location: /projects/notex/folders');
        exit;
    }
}
