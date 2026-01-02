<?php
/**
 * CodeXPro API Controller - Advanced IDE Features
 * 
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\Auth;
use Core\Security;
use CodeFormatter;
use TemplateManager;

// Load Phase 5 core classes
require_once __DIR__ . '/../../../core/CodeXPro/TemplateManager.php';
require_once __DIR__ . '/../../../core/CodeXPro/CodeFormatter.php';
require_once __DIR__ . '/../../../core/CodeXPro/FileTreeManager.php';

class ApiController
{
    /**
     * Format code (HTML, CSS, JavaScript)
     */
    public function format(): void
    {
        header('Content-Type: application/json');
        
        $code = $_POST['code'] ?? '';
        $language = $_POST['language'] ?? 'html';
        
        try {
            $formatted = '';
            
            switch (strtolower($language)) {
                case 'html':
                    $formatted = CodeFormatter::formatHTML($code);
                    break;
                case 'css':
                    $formatted = CodeFormatter::formatCSS($code);
                    break;
                case 'javascript':
                case 'js':
                    $formatted = CodeFormatter::formatJavaScript($code);
                    break;
                default:
                    throw new \Exception('Unsupported language');
            }
            
            echo json_encode([
                'success' => true,
                'formatted' => $formatted
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Validate code
     */
    public function validate(): void
    {
        header('Content-Type: application/json');
        
        $code = $_POST['code'] ?? '';
        $language = $_POST['language'] ?? 'html';
        
        try {
            $validation = [];
            
            switch (strtolower($language)) {
                case 'html':
                    $validation = CodeFormatter::validateHTML($code);
                    break;
                case 'css':
                    $validation = CodeFormatter::validateCSS($code);
                    break;
                case 'javascript':
                case 'js':
                    $validation = CodeFormatter::validateJavaScript($code);
                    break;
                default:
                    throw new \Exception('Unsupported language');
            }
            
            echo json_encode([
                'success' => true,
                'valid' => $validation['valid'] ?? true,
                'errors' => $validation['errors'] ?? []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Minify code
     */
    public function minify(): void
    {
        header('Content-Type: application/json');
        
        $code = $_POST['code'] ?? '';
        $language = $_POST['language'] ?? 'html';
        
        try {
            $minified = '';
            
            switch (strtolower($language)) {
                case 'html':
                    $minified = CodeFormatter::minifyHTML($code);
                    break;
                case 'css':
                    $minified = CodeFormatter::minifyCSS($code);
                    break;
                case 'javascript':
                case 'js':
                    $minified = CodeFormatter::minifyJavaScript($code);
                    break;
                default:
                    throw new \Exception('Unsupported language');
            }
            
            echo json_encode([
                'success' => true,
                'minified' => $minified
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get starter templates
     */
    public function getStarterTemplates(): void
    {
        header('Content-Type: application/json');
        
        try {
            $templates = TemplateManager::getStarterTemplates();
            
            echo json_encode([
                'success' => true,
                'templates' => $templates
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get snippets by language
     */
    public function getSnippets(): void
    {
        header('Content-Type: application/json');
        
        $language = $_GET['language'] ?? null;
        
        try {
            $snippets = $language 
                ? TemplateManager::getSnippets($language)
                : TemplateManager::getAllSnippets();
            
            echo json_encode([
                'success' => true,
                'snippets' => $snippets
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Search snippets
     */
    public function searchSnippets(): void
    {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        
        try {
            $results = TemplateManager::searchSnippets($query);
            
            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Export project as ZIP
     */
    public function exportProject(int $projectId): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        try {
            // Get project
            $project = $db->fetch(
                "SELECT * FROM projects WHERE id = ? AND user_id = ?",
                [$projectId, $user['id']]
            );
            
            if (!$project) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Project not found']);
                return;
            }
            
            // Prepare files
            $files = [
                'index.html' => $project['html_content'] ?? '',
                'style.css' => $project['css_content'] ?? '',
                'script.js' => $project['js_content'] ?? ''
            ];
            
            // Create ZIP
            $zipFile = TemplateManager::exportAsZip($project['name'], $files);
            
            // Download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . Security::sanitize($project['name']) . '.zip"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            unlink($zipFile);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create project from template
     */
    public function createFromTemplate(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        $templateKey = $_POST['template'] ?? '';
        
        try {
            // Get starter template
            $template = TemplateManager::getStarterTemplate($templateKey);
            
            if (!$template) {
                throw new \Exception('Template not found');
            }
            
            // Create new project
            $projectData = [
                'user_id' => $user['id'],
                'name' => $template['name'],
                'description' => $template['description'],
                'language' => 'html',
                'html_content' => $template['files']['index.html'] ?? '',
                'css_content' => $template['files']['style.css'] ?? $template['files']['app.css'] ?? '',
                'js_content' => $template['files']['script.js'] ?? $template['files']['app.js'] ?? '',
                'visibility' => 'private'
            ];
            
            $projectId = $db->insert('projects', $projectData);
            
            echo json_encode([
                'success' => true,
                'project_id' => $projectId,
                'message' => 'Project created from template'
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
