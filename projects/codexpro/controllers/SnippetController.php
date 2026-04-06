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
    /**
     * List all snippets
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        
        $snippets = $db->fetchAll(
            "SELECT * FROM codexpro_snippets WHERE user_id = ? OR is_public = 1 
             ORDER BY created_at DESC",
            [$user['id']]
        );
        
        View::render('projects/codexpro/snippets', [
            'snippets' => $snippets,
        ]);
    }
    
    /**
     * Store new snippet
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::getInstance();
        
        $title = Security::sanitize($_POST['title'] ?? '');
        $description = Security::sanitize($_POST['description'] ?? '');
        $code = $_POST['code'] ?? '';
        $language = Security::sanitize($_POST['language'] ?? 'javascript');
        $tags = Security::sanitize($_POST['tags'] ?? '');
        $isPublic = isset($_POST['is_public']) ? 1 : 0;
        
        if (empty($title) || empty($code)) {
            echo json_encode(['success' => false, 'error' => 'Title and code are required']);
            return;
        }
        
        $snippetId = $db->insert('codexpro_snippets', [
        
        if ($snippetId) {
            try { ActivityLogger::logCreate($user['id'], 'codexpro', 'snippet', $snippetId, ['title' => $title, 'language' => $language]); } catch (\Throwable $_) {}
            echo json_encode(['success' => true, 'snippet_id' => $snippetId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create snippet']);
        }
    }
    
    /**
     * Show snippet
     */
    public function show(int $id): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        
        $snippet = $db->fetch(
            "SELECT * FROM codexpro_snippets WHERE id = ? AND (user_id = ? OR is_public = 1)",
            [$id, $user['id']]
        );
        
        if (!$snippet) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        
        // Increment views
        $db->query("UPDATE codexpro_snippets SET views = views + 1 WHERE id = ?", [$id]);
        
        View::render('projects/codexpro/snippet', [
            'snippet' => $snippet,
        ]);
    }
    
    /**
     * Show edit form for snippet
     */
    public function edit(int $id): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        
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
    
    /**
     * Update snippet
     */
    public function update(int $id): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::getInstance();
        
        // Verify ownership
        $snippet = $db->fetch(
            "SELECT * FROM codexpro_snippets WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );
        
        if (!$snippet) {
            echo json_encode(['success' => false, 'error' => 'Snippet not found']);
            return;
        }
        
        // Handle both POST data and JSON input
        $input = [];
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            if ($jsonInput) {
                $input = $jsonInput;
            }
        }
        
        // Merge POST and JSON data (POST takes priority)
        $title = Security::sanitize($_POST['title'] ?? $input['title'] ?? '');
        $description = Security::sanitize($_POST['description'] ?? $input['description'] ?? '');
        $code = $_POST['code'] ?? $input['code'] ?? '';
        $language = Security::sanitize($_POST['language'] ?? $input['language'] ?? 'javascript');
        $tags = Security::sanitize($_POST['tags'] ?? $input['tags'] ?? '');
        $isPublic = isset($_POST['is_public']) || (isset($input['is_public']) && $input['is_public']) ? 1 : 0;
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'error' => 'Title is required']);
            return;
        }
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'error' => 'Code is required']);
            return;
        }
        
        $updated = $db->update('codexpro_snippets', [
            'id' => $id,
            'user_id' => $user['id'],
        ]);
        
        if ($updated !== false) {
            try { ActivityLogger::logUpdate($user['id'], 'codexpro', 'snippet', $id, [], ['title' => $title, 'language' => $language]); } catch (\Throwable $_) {}
            echo json_encode(['success' => true, 'message' => 'Snippet updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update snippet']);
        }
    }
    
    /**
     * Delete snippet
     */
    public function delete(int $id): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            $db = Database::getInstance();
            
            // Verify ownership
            $snippet = $db->fetch(
                "SELECT * FROM codexpro_snippets WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );
            
            if (!$snippet) {
                echo json_encode(['success' => false, 'error' => 'Snippet not found or you do not have permission to delete it']);
                return;
            }
            
            $deleted = $db->delete('codexpro_snippets', [
            
            if ($deleted !== false && $deleted > 0) {
                try { ActivityLogger::logDelete($user['id'], 'codexpro', 'snippet', $id, ['id' => $id]); } catch (\Throwable $_) {}
                echo json_encode(['success' => true, 'message' => 'Snippet deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete snippet']);
            }
        } catch (\Exception $e) {
            try { ActivityLogger::logFailure($user['id'] ?? 0, 'delete_snippet', $e->getMessage()); } catch (\Throwable $_) {}
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Quick update snippet config (inline editing)
     */
    public function quickUpdate(int $id): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            $db = Database::getInstance();
            
            // Verify ownership
            $snippet = $db->fetch(
                "SELECT * FROM codexpro_snippets WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );
            
            if (!$snippet) {
                echo json_encode(['success' => false, 'error' => 'Snippet not found or you do not have permission to edit it']);
                return;
            }
            
            // Parse JSON body for PATCH requests
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode(['success' => false, 'error' => 'Invalid request data']);
                return;
            }
            
            $updateData = [];
            
            // Only update fields that are provided
            if (isset($input['title'])) {
                $title = Security::sanitize($input['title']);
                if (empty($title)) {
                    echo json_encode(['success' => false, 'error' => 'Title cannot be empty']);
                    return;
                }
                $updateData['title'] = $title;
            }
            if (isset($input['description'])) {
                $updateData['description'] = Security::sanitize($input['description']);
            }
            if (isset($input['is_public'])) {
                $updateData['is_public'] = $input['is_public'] ? 1 : 0;
            }
            
            if (empty($updateData)) {
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                return;
            }
            
            $updated = $db->update('codexpro_snippets', $updateData, [
            
            if ($updated !== false) {
                try { ActivityLogger::logUpdate($user['id'], 'codexpro', 'snippet', $id, [], $updateData); } catch (\Throwable $_) {}
                echo json_encode(['success' => true, 'data' => $updateData, 'message' => 'Snippet updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update snippet']);
            }
        } catch (\Exception $e) {
            try { ActivityLogger::logFailure($user['id'] ?? 0, 'quick_update_snippet', $e->getMessage()); } catch (\Throwable $_) {}
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
