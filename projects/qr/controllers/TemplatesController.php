<?php
/**
 * Templates Controller
 * Handles QR code templates
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Projects\QR\Models\TemplateModel;

class TemplatesController
{
    private TemplateModel $model;
    
    public function __construct()
    {
        $this->model = new TemplateModel();
    }
    
    /**
     * Show templates page
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Get all templates for the user
        $templates = $this->model->getByUser($userId);
        
        $this->render('templates', [
            'title' => 'Templates',
            'user' => Auth::user(),
            'templates' => $templates
        ]);
    }
    
    /**
     * Create new template
     */
    public function create(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? 'Untitled Template',
                'settings' => json_decode($_POST['settings'] ?? '{}', true),
                'is_public' => isset($_POST['is_public']) ? 1 : 0
            ];
            
            $templateId = $this->model->create($userId, $data);
            
            if ($templateId) {
                echo json_encode(['success' => true, 'id' => $templateId, 'message' => 'Template saved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save template']);
            }
            exit;
        }
    }
    
    /**
     * Get template by ID (for applying)
     */
    public function get(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $templateId = $_GET['id'] ?? null;
        
        if (!$templateId) {
            echo json_encode(['success' => false, 'message' => 'Template ID required']);
            exit;
        }
        
        $template = $this->model->getById($templateId, $userId);
        
        if ($template) {
            echo json_encode(['success' => true, 'template' => $template]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Template not found']);
        }
        exit;
    }
    
    /**
     * Update template
     */
    public function update(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $templateId = $_POST['id'] ?? null;
        
        if (!$templateId) {
            echo json_encode(['success' => false, 'message' => 'Template ID required']);
            exit;
        }
        
        $data = [
            'name' => $_POST['name'] ?? 'Untitled Template',
            'settings' => json_decode($_POST['settings'] ?? '{}', true),
            'is_public' => isset($_POST['is_public']) ? 1 : 0
        ];
        
        if ($this->model->update($templateId, $userId, $data)) {
            echo json_encode(['success' => true, 'message' => 'Template updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update template']);
        }
        exit;
    }
    
    /**
     * Delete template
     */
    public function delete(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $templateId = $_POST['id'] ?? null;
        
        if (!$templateId) {
            echo json_encode(['success' => false, 'message' => 'Template ID required']);
            exit;
        }
        
        if ($this->model->delete($templateId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'Template deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete template']);
        }
        exit;
    }
    
    /**
     * Render a project view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        include PROJECT_PATH . '/views/layout.php';
    }
}
