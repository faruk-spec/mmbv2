<?php
/**
 * Email & Notifications Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Email;
use Core\Notification;

class EmailController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Email Queue Management
     */
    public function queue(): void
    {
        $db = Database::getInstance();
        
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if ($status !== 'all') {
            $where = "WHERE status = ?";
            $params[] = $status;
        }
        
        // Get queued emails
        $emails = $db->fetchAll(
            "SELECT * FROM email_queue 
             $where 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM email_queue $where",
            $params
        )['count'];
        
        // Get queue statistics
        $stats = Email::getQueueStats();
        
        $this->view('admin/email/queue', [
            'title' => 'Email Queue',
            'emails' => $emails,
            'stats' => $stats,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'status' => $status
        ]);
    }
    
    /**
     * Email Templates Management
     */
    public function templates(): void
    {
        // Get all email template files
        $templateDir = __DIR__ . '/../../views/emails/';
        $templates = [];
        
        if (is_dir($templateDir)) {
            $files = scandir($templateDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'layout.php') {
                    $templates[] = [
                        'name' => str_replace('.php', '', $file),
                        'file' => $file,
                        'path' => $templateDir . $file,
                        'size' => filesize($templateDir . $file),
                        'modified' => filemtime($templateDir . $file)
                    ];
                }
            }
        }
        
        $this->view('admin/email/templates', [
            'title' => 'Email Templates',
            'templates' => $templates
        ]);
    }
    
    /**
     * View/Edit Email Template
     */
    public function viewTemplate(): void
    {
        $templateName = $_GET['template'] ?? '';
        
        if (!$templateName) {
            header('Location: /admin/email/templates');
            exit;
        }
        
        $templatePath = __DIR__ . '/../../views/emails/' . $templateName . '.php';
        
        if (!file_exists($templatePath)) {
            $_SESSION['error'] = 'Template not found';
            header('Location: /admin/email/templates');
            exit;
        }
        
        $content = file_get_contents($templatePath);
        
        $this->view('admin/email/view-template', [
            'title' => 'Email Template: ' . $templateName,
            'templateName' => $templateName,
            'content' => $content
        ]);
    }
    
    /**
     * Process Email Queue (AJAX)
     */
    public function processQueue(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $limit = $_POST['limit'] ?? 50;
        
        try {
            $processed = Email::processQueue($limit);
            $this->jsonResponse([
                'success' => true,
                'message' => "Processed $processed emails",
                'processed' => $processed
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Delete Failed Emails (AJAX)
     */
    public function deleteFailed(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $db = Database::getInstance();
        
        try {
            $deleted = $db->execute(
                "DELETE FROM email_queue WHERE status = 'failed'"
            );
            
            $this->jsonResponse([
                'success' => true,
                'message' => "Deleted failed emails",
                'deleted' => $deleted
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
