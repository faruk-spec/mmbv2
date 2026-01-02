<?php
/**
 * CodeXPro Template Controller
 * 
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\View;
use TemplateManager;

// Load TemplateManager for starter templates
require_once __DIR__ . '/../../../core/CodeXPro/TemplateManager.php';

class TemplateController
{
    /**
     * List all templates
     */
    public function index(): void
    {
        $db = Database::projectConnection('codexpro');
        
        // Get database templates
        $dbTemplates = $db->fetchAll(
            "SELECT * FROM templates WHERE is_active = 1 ORDER BY category, name"
        );
        
        // Get starter templates from TemplateManager
        $starterTemplates = TemplateManager::getStarterTemplates();
        
        // Convert starter templates to array format similar to DB templates
        $templates = [];
        foreach ($starterTemplates as $key => $template) {
            $templates[] = [
                'id' => $key,
                'name' => $template['name'],
                'description' => $template['description'],
                'category' => $template['category'],
                'is_active' => 1,
                'is_starter' => true // Flag to differentiate
            ];
        }
        
        // Merge with DB templates
        $templates = array_merge($templates, $dbTemplates);
        
        View::render('projects/codexpro/templates', [
            'templates' => $templates,
        ]);
    }
    
    /**
     * Load template data
     */
    public function load(int $id): void
    {
        header('Content-Type: application/json');
        
        $db = Database::projectConnection('codexpro');
        
        $template = $db->fetch(
            "SELECT * FROM templates WHERE id = ? AND is_active = 1",
            [$id]
        );
        
        if ($template) {
            echo json_encode([
                'success' => true,
                'template' => $template
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Template not found'
            ]);
        }
    }
}
