<?php
/**
 * SheetDocs Template Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class TemplateController
{
    private $db;
    private $projectConfig;
    
    public function __construct()
    {
        if (!Auth::check()) {
            Helpers::redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        $this->projectConfig = require dirname(__DIR__) . '/config.php';
        $this->db = Database::projectConnection('sheetdocs');
    }
    
    /**
     * List all templates
     */
    public function index(): void
    {
        $userId = Auth::id();
        
        // Get user subscription
        $stmt = $this->db->prepare("SELECT plan FROM sheet_user_subscriptions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);
        $plan = ($subscription && isset($subscription['plan'])) ? $subscription['plan'] : 'free';
        
        // Get templates based on plan
        $stmt = $this->db->prepare("
            SELECT * FROM sheet_templates 
            WHERE tier = 'free' OR (tier = 'paid' AND :plan = 'paid')
            ORDER BY category, title
        ");
        $stmt->execute(['plan' => $plan]);
        $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Group by category
        $groupedTemplates = [];
        foreach ($templates as $template) {
            $category = $template['category'];
            if (!isset($groupedTemplates[$category])) {
                $groupedTemplates[$category] = [];
            }
            $groupedTemplates[$category][] = $template;
        }
        
        View::render('projects/sheetdocs/templates', [
            'templates' => $groupedTemplates,
            'plan' => $plan
        ]);
    }
    
    /**
     * Use a template to create new document
     */
    public function use(int $id): void
    {
        $userId = Auth::id();
        
        // Get template
        $stmt = $this->db->prepare("SELECT * FROM sheet_templates WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $template = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$template) {
            Helpers::setFlash('error', 'Template not found.');
            Helpers::redirect('/projects/sheetdocs/templates');
            exit;
        }
        
        // Check if user has access to this template
        $stmt = $this->db->prepare("SELECT plan FROM sheet_user_subscriptions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $subscription = $stmt->fetch(\PDO::FETCH_ASSOC);
        $plan = ($subscription && isset($subscription['plan'])) ? $subscription['plan'] : 'free';
        
        if ($template['tier'] === 'paid' && $plan !== 'paid') {
            Helpers::setFlash('error', 'This template requires a premium subscription.');
            Helpers::redirect('/projects/sheetdocs/pricing');
            exit;
        }
        
        // Check limits
        $features = $this->projectConfig['features'][$plan];
        $type = $template['type'];
        $maxKey = $type === 'document' ? 'max_documents' : 'max_sheets';
        
        if ($features[$maxKey] != -1) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM sheet_documents 
                WHERE user_id = :user_id AND type = :type
            ");
            $stmt->execute(['user_id' => $userId, 'type' => $type]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result['count'] >= $features[$maxKey]) {
                Helpers::setFlash('error', 'You have reached your ' . $type . ' limit. Please upgrade to create more.');
                Helpers::redirect('/projects/sheetdocs/pricing');
                exit;
            }
        }
        
        // Create document from template
        $stmt = $this->db->prepare("
            INSERT INTO sheet_documents (user_id, title, content, type, visibility, last_edited_by)
            VALUES (:user_id, :title, :content, :type, 'private', :user_id)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'title' => $template['title'] . ' (Copy)',
            'content' => $template['content'],
            'type' => $template['type']
        ]);
        
        $documentId = $this->db->lastInsertId();
        
        // If it's a sheet, create default sheet tab
        if ($template['type'] === 'sheet') {
            $stmt = $this->db->prepare("
                INSERT INTO sheet_sheets (document_id, name, order_index, row_count, col_count)
                VALUES (:document_id, 'Sheet1', 0, 100, 26)
            ");
            $stmt->execute(['document_id' => $documentId]);
        }
        
        // Increment template usage
        $stmt = $this->db->prepare("UPDATE sheet_templates SET usage_count = usage_count + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        Helpers::setFlash('success', 'Document created from template!');
        
        if ($template['type'] === 'document') {
            Helpers::redirect('/projects/sheetdocs/documents/' . $documentId . '/edit');
        } else {
            Helpers::redirect('/projects/sheetdocs/sheets/' . $documentId . '/edit');
        }
    }
}
