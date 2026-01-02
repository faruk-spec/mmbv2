<?php
/**
 * CodeXPro Editor Controller - Live Preview
 * 
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\Helpers;

class EditorController
{
    /**
     * Show editor (new project)
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        // Get user settings
        $settings = $db->fetch(
            "SELECT * FROM user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        if (!$settings) {
            // Create default settings
            $db->insert('user_settings', ['user_id' => $user['id']]);
            $settings = $db->fetch(
                "SELECT * FROM user_settings WHERE user_id = ?",
                [$user['id']]
            );
        }
        
        View::render('projects/codexpro/editor', [
            'project' => null,
            'settings' => $settings,
        ]);
    }
    
    /**
     * Create new project
     */
    public function create(): void
    {
        $this->index();
    }
    
    /**
     * Edit existing project
     */
    public function edit(int $id): void
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
        
        // Get user settings
        $settings = $db->fetch(
            "SELECT * FROM user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        View::render('projects/codexpro/editor', [
            'project' => $project,
            'settings' => $settings,
        ]);
    }
    
    /**
     * Save project
     */
    public function save(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        $projectId = (int)($_POST['project_id'] ?? 0);
        $name = Security::sanitize($_POST['name'] ?? 'Untitled Project');
        $description = Security::sanitize($_POST['description'] ?? '');
        $htmlContent = $_POST['html_content'] ?? '';
        $cssContent = $_POST['css_content'] ?? '';
        $jsContent = $_POST['js_content'] ?? '';
        $visibility = Security::sanitize($_POST['visibility'] ?? 'private');
        
        if ($projectId) {
            // Update existing project
            $project = $db->fetch(
                "SELECT * FROM projects WHERE id = ? AND user_id = ?",
                [$projectId, $user['id']]
            );
            
            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found']);
                return;
            }
            
            $updated = $db->update('projects', [
                'name' => $name,
                'description' => $description,
                'html_content' => $htmlContent,
                'css_content' => $cssContent,
                'js_content' => $jsContent,
                'visibility' => $visibility,
            ], ['id' => $projectId]);
            
            echo json_encode(['success' => $updated, 'project_id' => $projectId]);
        } else {
            // Create new project
            $projectId = $db->insert('projects', [
                'user_id' => $user['id'],
                'name' => $name,
                'description' => $description,
                'html_content' => $htmlContent,
                'css_content' => $cssContent,
                'js_content' => $jsContent,
                'visibility' => $visibility,
            ]);
            
            if ($projectId) {
                echo json_encode(['success' => true, 'project_id' => $projectId]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create project']);
            }
        }
    }
    
    /**
     * Autosave project (for auto-save feature)
     */
    public function autosave(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        $projectId = (int)($_POST['project_id'] ?? 0);
        
        if (!$projectId) {
            echo json_encode(['success' => false, 'error' => 'Project ID required']);
            return;
        }
        
        $project = $db->fetch(
            "SELECT * FROM projects WHERE id = ? AND user_id = ?",
            [$projectId, $user['id']]
        );
        
        if (!$project) {
            echo json_encode(['success' => false, 'error' => 'Project not found']);
            return;
        }
        
        $htmlContent = $_POST['html_content'] ?? '';
        $cssContent = $_POST['css_content'] ?? '';
        $jsContent = $_POST['js_content'] ?? '';
        
        $updated = $db->update('projects', [
            'html_content' => $htmlContent,
            'css_content' => $cssContent,
            'js_content' => $jsContent,
        ], ['id' => $projectId]);
        
        echo json_encode(['success' => $updated]);
    }
}
