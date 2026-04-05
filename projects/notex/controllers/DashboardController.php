<?php
/**
 * NoteX Dashboard Controller
 *
 * @package MMB\Projects\NoteX\Controllers
 */

namespace Projects\NoteX\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();
        $db   = Database::projectConnection('notex');

        $stats = [
            'total_notes'   => (int) $db->fetchColumn("SELECT COUNT(*) FROM notes WHERE user_id = ? AND status = 'active'", [$user['id']]),
            'pinned_notes'  => (int) $db->fetchColumn("SELECT COUNT(*) FROM notes WHERE user_id = ? AND is_pinned = 1 AND status = 'active'", [$user['id']]),
            'total_folders' => (int) $db->fetchColumn("SELECT COUNT(*) FROM note_folders WHERE user_id = ?", [$user['id']]),
            'total_tags'    => (int) $db->fetchColumn("SELECT COUNT(*) FROM note_tags WHERE user_id = ?", [$user['id']]),
        ];

        $pinnedNotes = $db->fetchAll(
            "SELECT * FROM notes WHERE user_id = ? AND is_pinned = 1 AND status = 'active' ORDER BY updated_at DESC LIMIT 4",
            [$user['id']]
        );

        $recentNotes = $db->fetchAll(
            "SELECT n.*, nf.name as folder_name FROM notes n LEFT JOIN note_folders nf ON n.folder_id = nf.id WHERE n.user_id = ? AND n.status = 'active' ORDER BY n.updated_at DESC LIMIT 8",
            [$user['id']]
        );

        $folders = $db->fetchAll(
            "SELECT nf.*, COUNT(n.id) as note_count FROM note_folders nf LEFT JOIN notes n ON n.folder_id = nf.id AND n.status = 'active' WHERE nf.user_id = ? GROUP BY nf.id ORDER BY nf.sort_order ASC",
            [$user['id']]
        );

        View::render('projects/notex/dashboard', [
            'title'        => 'Dashboard',
            'subtitle'     => 'Private Notes & Cloud Sync',
            'stats'        => $stats,
            'pinnedNotes'  => $pinnedNotes,
            'recentNotes'  => $recentNotes,
            'folders'      => $folders,
        ]);
    }
}
