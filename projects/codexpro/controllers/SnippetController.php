<?php
/**
 * CodeXPro Snippet Controller
 *
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\ActivityLogger;

class SnippetController
{
    public function index(): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        $snippets = $db->fetchAll(
            "SELECT * FROM codexpro_snippets WHERE user_id = ? OR is_public = 1 ORDER BY created_at DESC",
            [$user['id']]
        );

        View::render('projects/codexpro/snippets', [
            'snippets' => $snippets,
        ]);
    }

    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $title       = Security::sanitize($_POST['title'] ?? '');
            $description = Security::sanitize($_POST['description'] ?? '');
            $code        = $_POST['code'] ?? '';
            $language    = Security::sanitize($_POST['language'] ?? 'javascript');
            $tags        = Security::sanitize($_POST['tags'] ?? '');
            $isPublic    = isset($_POST['is_public']) ? 1 : 0;

            if ($title === '' || trim($code) === '') {
                echo json_encode(['success' => false, 'error' => 'Title and code are required']);
                return;
            }

            $snippetId = $db->insert('codexpro_snippets', [
                'user_id' => $user['id'],
                'title' => $title,
                'description' => $description,
                'code' => $code,
                'language' => $language,
                'tags' => $tags,
                'is_public' => $isPublic,
            ]);

            try {
                ActivityLogger::logCreate($user['id'], 'codexpro', 'snippet', $snippetId, ['title' => $title, 'language' => $language]);
            } catch (\Throwable $_) {
            }

            try {
                \Core\Notification::send($user['id'], 'codexpro_snippet_created', 'Snippet "' . $title . '" created in CodeXPro.', ['project' => 'codexpro', 'snippet_id' => $snippetId]);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true, 'snippet_id' => $snippetId]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        $snippet = $db->fetch(
            "SELECT * FROM codexpro_snippets WHERE id = ? AND (user_id = ? OR is_public = 1)",
            [$id, $user['id']]
        );

        if (!$snippet) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $db->query("UPDATE codexpro_snippets SET views = views + 1 WHERE id = ?", [$id]);

        View::render('projects/codexpro/snippet', [
            'snippet' => $snippet,
        ]);
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        $snippet = $db->fetch(
            "SELECT * FROM codexpro_snippets WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );

        if (!$snippet) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        View::render('projects/codexpro/snippet-edit', [
            'snippet' => $snippet,
        ]);
    }

    public function update(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $snippet = $db->fetch(
                "SELECT * FROM codexpro_snippets WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );

            if (!$snippet) {
                echo json_encode(['success' => false, 'error' => 'Snippet not found']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $title       = Security::sanitize($_POST['title'] ?? ($input['title'] ?? ''));
            $description = Security::sanitize($_POST['description'] ?? ($input['description'] ?? ''));
            $code        = $_POST['code'] ?? ($input['code'] ?? '');
            $language    = Security::sanitize($_POST['language'] ?? ($input['language'] ?? 'javascript'));
            $tags        = Security::sanitize($_POST['tags'] ?? ($input['tags'] ?? ''));
            $isPublic    = isset($_POST['is_public']) || (!empty($input['is_public'])) ? 1 : 0;

            if ($title === '') {
                echo json_encode(['success' => false, 'error' => 'Title is required']);
                return;
            }
            if (trim($code) === '') {
                echo json_encode(['success' => false, 'error' => 'Code is required']);
                return;
            }

            $db->update('codexpro_snippets', [
                'title' => $title,
                'description' => $description,
                'code' => $code,
                'language' => $language,
                'tags' => $tags,
                'is_public' => $isPublic,
            ], 'id = ? AND user_id = ?', [$id, $user['id']]);

            try {
                ActivityLogger::logUpdate($user['id'], 'codexpro', 'snippet', $id, [], ['title' => $title, 'language' => $language]);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true, 'message' => 'Snippet updated successfully']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $snippet = $db->fetch(
                "SELECT id FROM codexpro_snippets WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );

            if (!$snippet) {
                echo json_encode(['success' => false, 'error' => 'Snippet not found or you do not have permission to delete it']);
                return;
            }

            $deleted = $db->delete('codexpro_snippets', 'id = ? AND user_id = ?', [$id, $user['id']]);

            if ($deleted > 0) {
                try {
                    ActivityLogger::logDelete($user['id'], 'codexpro', 'snippet', $id, ['id' => $id]);
                } catch (\Throwable $_) {
                }

                try {
                    \Core\Notification::send($user['id'], 'codexpro_snippet_deleted', 'Snippet #' . $id . ' deleted in CodeXPro.', ['project' => 'codexpro', 'snippet_id' => $id]);
                } catch (\Throwable $_) {
                }

                echo json_encode(['success' => true, 'message' => 'Snippet deleted successfully']);
                return;
            }

            echo json_encode(['success' => false, 'error' => 'Failed to delete snippet']);
        } catch (\Throwable $e) {
            try {
                ActivityLogger::logFailure($user['id'] ?? 0, 'delete_snippet', $e->getMessage());
            } catch (\Throwable $_) {
            }
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function quickUpdate(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $snippet = $db->fetch(
                "SELECT id FROM codexpro_snippets WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );

            if (!$snippet) {
                echo json_encode(['success' => false, 'error' => 'Snippet not found or you do not have permission to edit it']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $updateData = [];

            if (array_key_exists('title', $input)) {
                $title = Security::sanitize((string)$input['title']);
                if ($title === '') {
                    echo json_encode(['success' => false, 'error' => 'Title cannot be empty']);
                    return;
                }
                $updateData['title'] = $title;
            }
            if (array_key_exists('description', $input)) {
                $updateData['description'] = Security::sanitize((string)$input['description']);
            }
            if (array_key_exists('is_public', $input)) {
                $updateData['is_public'] = !empty($input['is_public']) ? 1 : 0;
            }

            if (empty($updateData)) {
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                return;
            }

            $db->update('codexpro_snippets', $updateData, 'id = ? AND user_id = ?', [$id, $user['id']]);

            try {
                ActivityLogger::logUpdate($user['id'], 'codexpro', 'snippet', $id, [], $updateData);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true, 'data' => $updateData, 'message' => 'Snippet updated successfully']);
        } catch (\Throwable $e) {
            try {
                ActivityLogger::logFailure($user['id'] ?? 0, 'quick_update_snippet', $e->getMessage());
            } catch (\Throwable $_) {
            }
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
