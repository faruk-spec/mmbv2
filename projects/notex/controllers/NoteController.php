<?php
/**
 * NoteX Note Controller
 *
 * @package MMB\Projects\NoteX\Controllers
 */

namespace Projects\NoteX\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;

class NoteController
{
    public function index(): void
    {
        $user   = Auth::user();
        $db     = Database::projectConnection('notex');
        $search = trim($_GET['q'] ?? '');
        $folder = isset($_GET['folder']) ? (int) $_GET['folder'] : null;
        $tag    = trim($_GET['tag'] ?? '');

        $params = [$user['id']];
        $where  = "n.user_id = ? AND n.status = 'active'";

        if ($search !== '') {
            $where   .= " AND (n.title LIKE ? OR n.content LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($folder !== null) {
            $where   .= " AND n.folder_id = ?";
            $params[] = $folder;
        }

        $notes = $db->fetchAll(
            "SELECT n.*, nf.name as folder_name FROM notex_notes n LEFT JOIN notex_folders nf ON n.folder_id = nf.id WHERE {$where} ORDER BY n.is_pinned DESC, n.updated_at DESC",
            $params
        );

        $folders = $db->fetchAll("SELECT * FROM notex_folders WHERE user_id = ? ORDER BY sort_order ASC", [$user['id']]);
        $tags    = $db->fetchAll("SELECT * FROM notex_tags WHERE user_id = ? ORDER BY name ASC", [$user['id']]);

        View::render('projects/notex/notes', [
            'title'   => 'My Notes',
            'subtitle'=> 'All your notes',
            'notes'   => $notes,
            'folders' => $folders,
            'tags'    => $tags,
            'search'  => $search,
            'currentFolder' => $folder,
        ]);
    }

    public function create(): void
    {
        $user    = Auth::user();
        $db      = Database::projectConnection('notex');
        $folders = $db->fetchAll("SELECT * FROM notex_folders WHERE user_id = ? ORDER BY sort_order ASC", [$user['id']]);
        $tags    = $db->fetchAll("SELECT * FROM notex_tags WHERE user_id = ? ORDER BY name ASC", [$user['id']]);

        View::render('projects/notex/create', [
            'title'   => 'New Note',
            'subtitle'=> 'Create a note',
            'folders' => $folders,
            'tags'    => $tags,
        ]);
    }

    public function store(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/notex/create');
            exit;
        }

        $user    = Auth::user();
        $db      = Database::projectConnection('notex');
        $title   = trim($_POST['title'] ?? '') ?: 'Untitled Note';
        $content = $_POST['content'] ?? '';
        $folder  = !empty($_POST['folder_id']) ? (int) $_POST['folder_id'] : null;
        $color   = $_POST['color'] ?? '#ffd700';
        $isPinned = isset($_POST['is_pinned']) ? 1 : 0;

        $db->query(
            "INSERT INTO notex_notes (user_id, title, content, folder_id, color, is_pinned) VALUES (?, ?, ?, ?, ?, ?)",
            [$user['id'], $title, $content, $folder, $color, $isPinned]
        );

        $noteId = $db->lastInsertId();

        // Save version
        $db->query("INSERT INTO notex_versions (note_id, content) VALUES (?, ?)", [$noteId, $content]);

        // Attach tags
        $tagIds = $_POST['tag_ids'] ?? [];
        foreach ($tagIds as $tagId) {
            $tagId = (int) $tagId;
            $tag   = $db->fetch("SELECT id FROM notex_tags WHERE id = ? AND user_id = ?", [$tagId, $user['id']]);
            if ($tag) {
                $db->query("INSERT IGNORE INTO notex_tag_map (note_id, tag_id) VALUES (?, ?)", [$noteId, $tagId]);
            }
        }

        $_SESSION['success'] = 'Note created.';
        header("Location: /projects/notex/notes/{$noteId}/edit");
        exit;
    }

    public function show(int $id): void
    {
        $user = Auth::user();
        $db   = Database::projectConnection('notex');
        $note = $db->fetch("SELECT n.*, nf.name as folder_name FROM notex_notes n LEFT JOIN notex_folders nf ON n.folder_id = nf.id WHERE n.id = ? AND n.user_id = ?", [$id, $user['id']]);
        if (!$note) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        $tags = $db->fetchAll("SELECT nt.* FROM notex_tags nt JOIN notex_tag_map ntm ON nt.id = ntm.tag_id WHERE ntm.note_id = ?", [$id]);

        View::render('projects/notex/view', [
            'title'   => $note['title'],
            'subtitle'=> 'View Note',
            'note'    => $note,
            'tags'    => $tags,
        ]);
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        $db   = Database::projectConnection('notex');
        $note = $db->fetch("SELECT * FROM notex_notes WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$note) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $folders    = $db->fetchAll("SELECT * FROM notex_folders WHERE user_id = ? ORDER BY sort_order ASC", [$user['id']]);
        $allTags    = $db->fetchAll("SELECT * FROM notex_tags WHERE user_id = ? ORDER BY name ASC", [$user['id']]);
        $noteTags   = $db->fetchAll("SELECT tag_id FROM notex_tag_map WHERE note_id = ?", [$id]);
        $noteTagIds = array_column($noteTags, 'tag_id');
        $versions   = $db->fetchAll("SELECT id, created_at FROM notex_versions WHERE note_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);

        View::render('projects/notex/edit', [
            'title'      => 'Edit Note',
            'subtitle'   => $note['title'],
            'note'       => $note,
            'folders'    => $folders,
            'allTags'    => $allTags,
            'noteTagIds' => $noteTagIds,
            'versions'   => $versions,
        ]);
    }

    public function update(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header("Location: /projects/notex/notes/{$id}/edit");
            exit;
        }

        $user    = Auth::user();
        $db      = Database::projectConnection('notex');
        $note    = $db->fetch("SELECT id, content FROM notex_notes WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$note) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $title    = trim($_POST['title'] ?? '') ?: 'Untitled Note';
        $content  = $_POST['content'] ?? '';
        $folder   = !empty($_POST['folder_id']) ? (int) $_POST['folder_id'] : null;
        $color    = $_POST['color'] ?? '#ffd700';
        $isPinned = isset($_POST['is_pinned']) ? 1 : 0;

        // Save version if content changed
        if ($content !== $note['content']) {
            $db->query("INSERT INTO notex_versions (note_id, content) VALUES (?, ?)", [$id, $note['content']]);
        }

        $db->query(
            "UPDATE notex_notes SET title = ?, content = ?, folder_id = ?, color = ?, is_pinned = ?, updated_at = NOW() WHERE id = ? AND user_id = ?",
            [$title, $content, $folder, $color, $isPinned, $id, $user['id']]
        );

        // Update tags
        $db->query("DELETE FROM notex_tag_map WHERE note_id = ?", [$id]);
        $tagIds = $_POST['tag_ids'] ?? [];
        foreach ($tagIds as $tagId) {
            $tagId = (int) $tagId;
            $tag   = $db->fetch("SELECT id FROM notex_tags WHERE id = ? AND user_id = ?", [$tagId, $user['id']]);
            if ($tag) {
                $db->query("INSERT IGNORE INTO notex_tag_map (note_id, tag_id) VALUES (?, ?)", [$id, $tagId]);
            }
        }

        $_SESSION['success'] = 'Note saved.';
        header("Location: /projects/notex/notes/{$id}/edit");
        exit;
    }

    public function delete(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/notex/notes');
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('notex');
        $db->query("UPDATE notex_notes SET status = 'trashed', updated_at = NOW() WHERE id = ? AND user_id = ?", [$id, $user['id']]);

        $_SESSION['success'] = 'Note moved to trash.';
        header('Location: /projects/notex/notes');
        exit;
    }

    public function togglePin(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('notex');
        $note = $db->fetch("SELECT id, is_pinned FROM notex_notes WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
            exit;
        }

        $newPin = $note['is_pinned'] ? 0 : 1;
        $db->query("UPDATE notex_notes SET is_pinned = ?, updated_at = NOW() WHERE id = ?", [$newPin, $id]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'pinned' => (bool) $newPin]);
        exit;
    }

    public function toggleArchive(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('notex');
        $note = $db->fetch("SELECT id, is_archived FROM notex_notes WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$note) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
            exit;
        }

        $newArchive = $note['is_archived'] ? 0 : 1;
        $db->query("UPDATE notex_notes SET is_archived = ?, updated_at = NOW() WHERE id = ?", [$newArchive, $id]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'archived' => (bool) $newArchive]);
        exit;
    }

    public function share(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header("Location: /projects/notex/notes/{$id}/edit");
            exit;
        }

        $user   = Auth::user();
        $db     = Database::projectConnection('notex');
        $note   = $db->fetch("SELECT id FROM notex_notes WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$note) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        // Generate a unique share token
        do {
            $token = bin2hex(random_bytes(32));
        } while ($db->fetchColumn("SELECT COUNT(*) FROM notex_notes WHERE share_token = ?", [$token]) > 0);
        $access     = in_array($_POST['access'] ?? '', ['view', 'edit']) ? $_POST['access'] : 'view';
        $expiresAt  = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

        $db->query(
            "UPDATE notex_notes SET share_token = ?, share_access = ?, share_expires_at = ?, updated_at = NOW() WHERE id = ?",
            [$token, $access, $expiresAt, $id]
        );

        $_SESSION['success'] = 'Share link generated.';
        $_SESSION['share_url'] = (defined('APP_URL') ? APP_URL : '') . '/projects/notex/shared/' . $token;
        header("Location: /projects/notex/notes/{$id}/edit");
        exit;
    }

    public function viewShared(string $token): void
    {
        $db   = Database::projectConnection('notex');
        $note = $db->fetch("SELECT * FROM notex_notes WHERE share_token = ? AND status = 'active'", [$token]);
        if (!$note) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        if ($note['share_expires_at'] && strtotime($note['share_expires_at']) < time()) {
            http_response_code(410);
            echo '<!DOCTYPE html><html><body><h1>This shared note has expired.</h1></body></html>';
            exit;
        }

        View::render('projects/notex/shared', [
            'title'   => $note['title'],
            'subtitle'=> 'Shared Note',
            'note'    => $note,
        ]);
    }
}
