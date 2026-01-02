<?php
/**
 * CodeXPro Project Controller
 * 
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\Helpers;

class ProjectController
{
    /**
     * List all projects
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        $projects = $db->fetchAll(
            "SELECT * FROM projects WHERE user_id = ? ORDER BY updated_at DESC",
            [$user['id']]
        );
        
        View::render('projects/codexpro/projects', [
            'projects' => $projects,
        ]);
    }
    
    /**
     * Store new project
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        $name = Security::sanitize($_POST['name'] ?? '');
        $description = Security::sanitize($_POST['description'] ?? '');
        $language = Security::sanitize($_POST['language'] ?? 'html');
        $visibility = Security::sanitize($_POST['visibility'] ?? 'private');
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Project name is required']);
            return;
        }
        
        $projectId = $db->insert('projects', [
            'user_id' => $user['id'],
            'name' => $name,
            'description' => $description,
            'language' => $language,
            'visibility' => $visibility,
        ]);
        
        if ($projectId) {
            echo json_encode(['success' => true, 'project_id' => $projectId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create project']);
        }
    }
    
    /**
     * Show project details
     */
    public function show(int $id): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        $project = $db->fetch(
            "SELECT * FROM projects WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );
        
        if (!$project) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        
        // Redirect to editor
        Helpers::redirect('/projects/codexpro/editor/' . $id);
    }
    
    /**
     * Update project
     */
    public function update(int $id): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        $project = $db->fetch(
            "SELECT * FROM projects WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );
        
        if (!$project) {
            echo json_encode(['success' => false, 'error' => 'Project not found']);
            return;
        }
        
        $name = Security::sanitize($_POST['name'] ?? $project['name']);
        $description = Security::sanitize($_POST['description'] ?? $project['description']);
        $visibility = Security::sanitize($_POST['visibility'] ?? $project['visibility']);
        
        $updated = $db->update('projects', [
            'name' => $name,
            'description' => $description,
            'visibility' => $visibility,
        ], ['id' => $id]);
        
        echo json_encode(['success' => $updated]);
    }
    
    /**
     * Delete project
     */
    public function delete(int $id): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            $db = Database::projectConnection('codexpro');
            
            // Verify ownership
            $project = $db->fetch(
                "SELECT * FROM projects WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );
            
            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found or you do not have permission to delete it']);
                return;
            }
            
            $deleted = $db->delete('projects', [
                'id' => $id,
                'user_id' => $user['id'],
            ]);
            
            if ($deleted !== false && $deleted > 0) {
                echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete project']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Quick update project config (inline editing)
     */
    public function quickUpdate(int $id): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            $db = Database::projectConnection('codexpro');
            
            // Verify ownership
            $project = $db->fetch(
                "SELECT * FROM projects WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );
            
            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found or you do not have permission to edit it']);
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
            if (isset($input['name'])) {
                $name = Security::sanitize($input['name']);
                if (empty($name)) {
                    echo json_encode(['success' => false, 'error' => 'Project name cannot be empty']);
                    return;
                }
                $updateData['name'] = $name;
            }
            if (isset($input['description'])) {
                $updateData['description'] = Security::sanitize($input['description']);
            }
            if (isset($input['visibility'])) {
                $updateData['visibility'] = Security::sanitize($input['visibility']);
            }
            
            if (empty($updateData)) {
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                return;
            }
            
            $updated = $db->update('projects', $updateData, [
                'id' => $id,
                'user_id' => $user['id'],
            ]);
            
            if ($updated !== false) {
                echo json_encode(['success' => true, 'data' => $updateData, 'message' => 'Project updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update project']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
