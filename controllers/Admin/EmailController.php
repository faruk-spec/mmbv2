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
use Core\Logger;

class EmailController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('email');
    }
    
    /**
     * Ensure the email_queue table exists. Safe to call multiple times.
     */
    private function ensureQueueTable(Database $db): void
    {
        try {
            $db->query(
                "CREATE TABLE IF NOT EXISTS `email_queue` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `to_email` VARCHAR(255) NOT NULL,
                    `subject` VARCHAR(255) NOT NULL,
                    `body` LONGTEXT NOT NULL,
                    `cc` VARCHAR(512) NULL,
                    `bcc` VARCHAR(512) NULL,
                    `reply_to` VARCHAR(255) NULL,
                    `attachments` JSON NULL,
                    `priority` TINYINT DEFAULT 5,
                    `attempts` INT DEFAULT 0,
                    `max_attempts` INT DEFAULT 3,
                    `status` ENUM('pending','processing','sent','failed') DEFAULT 'pending',
                    `error_message` TEXT NULL,
                    `scheduled_at` TIMESTAMP NULL,
                    `sent_at` TIMESTAMP NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_status` (`status`),
                    INDEX `idx_scheduled` (`scheduled_at`),
                    INDEX `idx_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                []
            );
        } catch (\Throwable $e) {
            // Log the error but continue — the table may already exist or the DB user
            // may lack CREATE permission; the subsequent queries will surface the real error.
            Logger::warning('EmailController: could not ensure email_queue table: ' . $e->getMessage());
        }
    }

    /**
     * Email Queue Management
     */
    public function queue(): void
    {
        $this->requirePermission('email.queue');
        $db = Database::getInstance();

        // Auto-create email_queue table if it doesn't exist yet
        $this->ensureQueueTable($db);

        $status  = $_GET['status'] ?? 'all';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $where  = '';
        $params = [];

        if ($status !== 'all') {
            $where    = "WHERE status = ?";
            $params[] = $status;
        }

        $emails = [];
        $total  = 0;

        try {
            $emails = $db->fetchAll(
                "SELECT * FROM email_queue
                 $where
                 ORDER BY created_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );

            $total = (int)($db->fetch(
                "SELECT COUNT(*) AS count FROM email_queue $where",
                $params
            )['count'] ?? 0);
        } catch (\Throwable $e) {
            // email_queue table may not exist yet — show empty list
            \Core\Logger::warning('EmailController::queue DB error: ' . $e->getMessage());
        }

        // Queue statistics from the DB
        $stats = ['pending' => 0, 'sent' => 0, 'failed' => 0];
        try {
            $statRows = $db->fetchAll("SELECT status, COUNT(*) AS c FROM email_queue GROUP BY status");
            foreach ($statRows as $r) {
                $stats[$r['status']] = (int)$r['c'];
            }
        } catch (\Throwable $e) {
            // email_queue may not exist; keep zeroes
        }

        $this->view('admin/email/queue', [
            'title'      => 'Email Queue',
            'emails'     => $emails,
            'stats'      => $stats,
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => $total,
            'totalPages' => $total > 0 ? (int)ceil($total / $perPage) : 1,
            'status'     => $status,
        ]);
    }
    
    /**
     * Email Templates Management
     */
    public function templates(): void
    {
        $this->requirePermission('email.templates');
        // Get all email template files
        $templateDir = __DIR__ . '/../../views/emails/';
        $templates = [];

        // These are handled via admin/mail/templates (DB-backed notification templates)
        // and should not appear in the file-based template list.
        $excludedNames = [
            'login-alert',
            'login_alert',
            'password-changed',
            'password_changed',
            'password-reset',
            'password_reset',
            'verify',
            'email-verify',
            'email_verify',
            'welcome',
        ];

        // Load disabled templates from settings
        $disabledTemplates = $this->getDisabledFileTemplates();

        if (is_dir($templateDir)) {
            $files = scandir($templateDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'layout.php') {
                    $name = str_replace('.php', '', $file);
                    if (in_array($name, $excludedNames, true)) {
                        continue;
                    }
                    $templates[] = [
                        'name'     => $name,
                        'file'     => $file,
                        'path'     => $templateDir . $file,
                        'size'     => filesize($templateDir . $file),
                        'modified' => filemtime($templateDir . $file),
                        'enabled'  => !in_array($name, $disabledTemplates, true),
                    ];
                }
            }
        }

        $this->view('admin/email/templates', [
            'title'     => 'Email Templates',
            'templates' => $templates,
        ]);
    }

    /**
     * Toggle enable/disable for a file-based email template (AJAX POST).
     */
    public function toggleTemplate(): void
    {
        $this->requirePermission('email.templates');
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $name    = preg_replace('/[^a-zA-Z0-9\-_]/', '', $this->input('name', ''));
        $enabled = (int)$this->input('enabled', 1);

        if (!$name) {
            $this->json(['success' => false, 'message' => 'Invalid template name.']);
            return;
        }

        $disabled = $this->getDisabledFileTemplates();

        if ($enabled) {
            $disabled = array_values(array_diff($disabled, [$name]));
        } else {
            if (!in_array($name, $disabled, true)) {
                $disabled[] = $name;
            }
        }

        $this->saveDisabledFileTemplates($disabled);
        Logger::activity(Auth::id(), 'email_template_toggled', ['name' => $name, 'enabled' => $enabled]);
        $this->json(['success' => true, 'enabled' => $enabled, 'message' => 'Template ' . ($enabled ? 'enabled' : 'disabled') . '.']);
    }

    private function getDisabledFileTemplates(): array
    {
        try {
            $db  = Database::getInstance();
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = 'disabled_file_templates' LIMIT 1");
            if ($row && $row['value']) {
                return json_decode($row['value'], true) ?: [];
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return [];
    }

    private function saveDisabledFileTemplates(array $disabled): void
    {
        try {
            $db  = Database::getInstance();
            $val = json_encode(array_values($disabled));
            $row = $db->fetch("SELECT id FROM settings WHERE `key` = 'disabled_file_templates' LIMIT 1");
            if ($row) {
                $db->update('settings', ['value' => $val], '`key` = ?', ['disabled_file_templates']);
            } else {
                $db->insert('settings', ['key' => 'disabled_file_templates', 'value' => $val]);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
    
    /**
     * View/Edit Email Template
     */
    public function viewTemplate(): void
    {
        $this->requirePermission('email.templates');
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

    public function editTemplate(): void
    {
        $this->requirePermission('email.templates');
        $templateName = $_GET['template'] ?? '';

        if (!$templateName) {
            header('Location: /admin/email/templates');
            exit;
        }

        $templatePath = $this->getSafeTemplatePath($templateName);
        if (!$templatePath || !file_exists($templatePath)) {
            $_SESSION['error'] = 'Template not found';
            header('Location: /admin/email/templates');
            exit;
        }

        $content = file_get_contents($templatePath);

        $this->view('admin/email/view-template', [
            'title' => 'Edit Email Template: ' . $templateName,
            'templateName' => $templateName,
            'content' => $content,
            'isEditable' => true
        ]);
    }

    public function updateTemplate(): void
    {
        $this->requirePermission('email.templates');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/email/templates');
            return;
        }

        $templateName = $_POST['template'] ?? '';
        $content = $_POST['content'] ?? '';
        $templatePath = $this->getSafeTemplatePath($templateName);

        if (!$templatePath || !file_exists($templatePath)) {
            $this->flash('error', 'Template not found.');
            $this->redirect('/admin/email/templates');
            return;
        }

        $written = file_put_contents($templatePath, $content);
        if ($written === false) {
            $lastError = error_get_last();
            \Core\Logger::error('Failed to write email template file', [
                'template' => $templateName,
                'error' => $lastError['message'] ?? 'unknown'
            ]);
            $this->flash('error', 'Failed to save template.');
            $this->redirect('/admin/email/templates/edit?template=' . urlencode($templateName));
            return;
        }

        \Core\Logger::activity(Auth::id(), 'email_template_file_updated', ['template' => $templateName]);
        $this->flash('success', 'Template updated successfully.');
        $this->redirect('/admin/email/templates/edit?template=' . urlencode($templateName));
    }

    private function getSafeTemplatePath(string $templateName): ?string
    {
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $templateName)) {
            \Core\Logger::warning('Email template name validation failed', ['template' => $templateName]);
            return null;
        }

        $templateDir = realpath(__DIR__ . '/../../views/emails');
        if ($templateDir === false) {
            \Core\Logger::warning('Email template directory does not exist');
            return null;
        }

        $templatePath = realpath($templateDir . '/' . $templateName . '.php');
        if ($templatePath === false) {
            \Core\Logger::warning('Email template path not found', ['template' => $templateName]);
            return null;
        }
        if (!str_starts_with($templatePath, $templateDir . DIRECTORY_SEPARATOR)) {
            \Core\Logger::warning('Email template path traversal blocked', ['template' => $templateName]);
            return null;
        }

        return $templatePath;
    }
    
    /**
     * Process Email Queue (AJAX)
     */
    public function processQueue(): void
    {
        $this->requirePermission('email.queue');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $limit = (int)($_POST['limit'] ?? 50);

        // Note: this endpoint marks pending emails as 'processing' so that external
        // queue workers or cron jobs can pick them up.  The actual SMTP sending is
        // performed by MailService::sendNow() called from those workers, not here.
        $processed = 0;
        try {
            $db = Database::getInstance();
            $this->ensureQueueTable($db);
            $rows = $db->fetchAll(
                "SELECT id FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT ?",
                [$limit]
            );
            foreach ($rows as $row) {
                try {
                    $db->query(
                        "UPDATE email_queue SET status = 'processing', attempts = attempts + 1 WHERE id = ?",
                        [(int)$row['id']]
                    );
                    $processed++;
                } catch (\Throwable $innerE) {
                    // skip individual row errors
                }
            }
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Queue unavailable: ' . $e->getMessage()]);
            return;
        }
        $this->json([
            'success' => true,
            'message' => "Processed $processed emails",
            'processed' => $processed,
        ]);
    }

    /**
     * Delete Failed Emails (AJAX)
     */
    public function deleteFailed(): void
    {
        $this->requirePermission('email.queue');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $db = Database::getInstance();

        try {
            $stmt = $db->query("DELETE FROM email_queue WHERE status = 'failed'");
            $deleted = $stmt->rowCount();

            $this->json([
                'success' => true,
                'message' => "Deleted {$deleted} failed email" . ($deleted !== 1 ? 's' : ''),
                'deleted' => $deleted
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
