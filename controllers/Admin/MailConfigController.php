<?php
/**
 * Admin Mail Configuration Controller
 *
 * Manages SMTP/IMAP provider configs, notification templates,
 * email queue monitoring, and send-log viewing.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Security;
use Core\View;
use Core\MailService;
use Core\Logger;

class MailConfigController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }

    // ------------------------------------------------------------------
    // Provider list
    // ------------------------------------------------------------------

    public function index(): void
    {
        $db        = Database::getInstance();
        $providers = $db->fetchAll("SELECT * FROM mail_provider_configs ORDER BY is_active DESC, id ASC");
        $queueStats = $this->getQueueStats($db);
        $logCount   = $db->fetch("SELECT COUNT(*) AS c FROM mail_send_log")['c'] ?? 0;

        $this->view('admin/mail/config', [
            'title'      => 'Mail Configuration',
            'providers'  => $providers,
            'stats'      => $queueStats,
            'logCount'   => $logCount,
        ]);
    }

    // ------------------------------------------------------------------
    // Create / Edit provider form
    // ------------------------------------------------------------------

    public function create(): void
    {
        $this->view('admin/mail/config-form', [
            'title'    => 'Add Mail Provider',
            'provider' => null,
        ]);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();
        $provider = $db->fetch("SELECT * FROM mail_provider_configs WHERE id = ? LIMIT 1", [$id]);

        if (!$provider) {
            $this->flash('error', 'Provider not found.');
            $this->redirect('/admin/mail/config');
            return;
        }

        // Mask passwords in form (show placeholder if set)
        $provider['smtp_password_set'] = !empty($provider['smtp_password']);
        $provider['imap_password_set'] = !empty($provider['imap_password']);
        $provider['smtp_password']     = '';
        $provider['imap_password']     = '';

        $this->view('admin/mail/config-form', [
            'title'    => 'Edit Mail Provider',
            'provider' => $provider,
        ]);
    }

    // ------------------------------------------------------------------
    // Save provider
    // ------------------------------------------------------------------

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/mail/config/create');
            return;
        }

        $data = $this->buildProviderData();
        $db   = Database::getInstance();
        $db->insert('mail_provider_configs', $data);

        Logger::activity(Auth::id(), 'mail_provider_created', ['name' => $data['name']]);
        MailService::clearProviderCache();

        $this->flash('success', 'Mail provider added successfully.');
        $this->redirect('/admin/mail/config');
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/mail/config');
            return;
        }

        $id = (int)$this->input('id', 0);
        if (!$id) {
            $this->redirect('/admin/mail/config');
            return;
        }

        $data = $this->buildProviderData();
        $db   = Database::getInstance();

        // If new passwords are empty strings, don't overwrite stored passwords
        $existing = $db->fetch("SELECT smtp_password, imap_password FROM mail_provider_configs WHERE id = ?", [$id]);
        if ($existing) {
            if (empty($data['smtp_password'])) {
                $data['smtp_password'] = $existing['smtp_password'];
            }
            if (empty($data['imap_password'])) {
                $data['imap_password'] = $existing['imap_password'];
            }
        }

        $db->update('mail_provider_configs', $data, 'id = ?', [$id]);

        Logger::activity(Auth::id(), 'mail_provider_updated', ['id' => $id, 'name' => $data['name']]);
        MailService::clearProviderCache();

        $this->flash('success', 'Mail provider updated.');
        $this->redirect('/admin/mail/config');
    }

    // ------------------------------------------------------------------
    // Activate / delete
    // ------------------------------------------------------------------

    public function activate(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $id = (int)$this->input('id', 0);
        $db = Database::getInstance();

        $db->query("UPDATE mail_provider_configs SET is_active = 0");
        $db->update('mail_provider_configs', ['is_active' => 1], 'id = ?', [$id]);

        MailService::clearProviderCache();
        Logger::activity(Auth::id(), 'mail_provider_activated', ['id' => $id]);

        $this->json(['success' => true, 'message' => 'Provider activated.']);
    }

    public function delete(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $id = (int)$this->input('id', 0);
        $db = Database::getInstance();
        $db->delete('mail_provider_configs', 'id = ?', [$id]);

        MailService::clearProviderCache();
        Logger::activity(Auth::id(), 'mail_provider_deleted', ['id' => $id]);

        $this->json(['success' => true, 'message' => 'Provider deleted.']);
    }

    // ------------------------------------------------------------------
    // Test connection
    // ------------------------------------------------------------------

    public function testSmtp(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $cfg = [
            'smtp_host'       => $this->input('smtp_host', ''),
            'smtp_port'       => (int)$this->input('smtp_port', 587),
            'smtp_encryption' => $this->input('smtp_encryption', 'tls'),
            'smtp_username'   => $this->input('smtp_username', ''),
            'smtp_password'   => $this->input('smtp_password', ''),
        ];

        $result = MailService::testSmtp($cfg);
        $this->json($result);
    }

    public function testImap(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $cfg = [
            'imap_host'       => $this->input('imap_host', ''),
            'imap_port'       => (int)$this->input('imap_port', 993),
            'imap_encryption' => $this->input('imap_encryption', 'ssl'),
            'imap_username'   => $this->input('imap_username', ''),
            'imap_password'   => $this->input('imap_password', ''),
        ];

        $result = MailService::testImap($cfg);
        $this->json($result);
    }

    /**
     * Send a real test email to a given address using the active provider.
     */
    public function sendTestEmail(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $to = trim($this->input('to', ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid recipient email address.']);
            return;
        }

        $result = MailService::sendTestEmail($to);
        Logger::activity(Auth::id(), 'mail_test_sent', ['to' => $to, 'success' => $result['success']]);
        $this->json($result);
    }

    // ------------------------------------------------------------------
    // Notification templates
    // ------------------------------------------------------------------

    public function templates(): void
    {
        $db        = Database::getInstance();
        $templates = $db->fetchAll("SELECT * FROM mail_notification_templates ORDER BY slug ASC");

        $this->view('admin/mail/templates', [
            'title'     => 'Notification Templates',
            'templates' => $templates,
        ]);
    }

    public function editTemplate(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();
        $template = $db->fetch("SELECT * FROM mail_notification_templates WHERE id = ? LIMIT 1", [$id]);

        if (!$template) {
            $this->flash('error', 'Template not found.');
            $this->redirect('/admin/mail/templates');
            return;
        }

        $this->view('admin/mail/edit-template', [
            'title'    => 'Edit Template: ' . $template['name'],
            'template' => $template,
        ]);
    }

    public function updateTemplate(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/mail/templates');
            return;
        }

        $id      = (int)$this->input('id', 0);
        $subject = Security::sanitize($this->input('subject', ''));
        $body    = $this->input('body', ''); // Allow HTML
        $enabled = $this->input('is_enabled', '0') === '1' ? 1 : 0;

        if (!$id || !$subject) {
            $this->flash('error', 'Subject is required.');
            $this->redirect('/admin/mail/templates/edit?id=' . $id);
            return;
        }

        $db = Database::getInstance();
        $db->update('mail_notification_templates', [
            'subject'    => $subject,
            'body'       => $body,
            'is_enabled' => $enabled,
        ], 'id = ?', [$id]);

        Logger::activity(Auth::id(), 'mail_template_updated', ['id' => $id]);
        $this->flash('success', 'Template saved.');
        $this->redirect('/admin/mail/templates');
    }

    public function toggleTemplate(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false]);
            return;
        }

        $id = (int)$this->input('id', 0);
        $db = Database::getInstance();
        $tpl = $db->fetch("SELECT is_enabled FROM mail_notification_templates WHERE id = ?", [$id]);
        if (!$tpl) {
            $this->json(['success' => false, 'message' => 'Not found.']);
            return;
        }

        $newVal = $tpl['is_enabled'] ? 0 : 1;
        $db->update('mail_notification_templates', ['is_enabled' => $newVal], 'id = ?', [$id]);

        $this->json(['success' => true, 'enabled' => (bool)$newVal]);
    }

    // ------------------------------------------------------------------
    // Send log
    // ------------------------------------------------------------------

    public function logs(): void
    {
        $db      = Database::getInstance();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $logs  = $db->fetchAll("SELECT l.*, u.name AS user_name FROM mail_send_log l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.sent_at DESC LIMIT ? OFFSET ?", [$perPage, $offset]);
        $total = $db->fetch("SELECT COUNT(*) AS c FROM mail_send_log")['c'] ?? 0;

        $this->view('admin/mail/logs', [
            'title'      => 'Mail Send Log',
            'logs'       => $logs,
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => (int)$total,
            'totalPages' => (int)ceil($total / $perPage),
        ]);
    }

    // ------------------------------------------------------------------
    // Queue management (reuse existing)
    // ------------------------------------------------------------------

    public function processQueue(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $limit     = min(200, max(1, (int)$this->input('limit', 50)));
        $processed = MailService::processQueue($limit);

        $this->json(['success' => true, 'message' => "Processed $processed emails.", 'processed' => $processed]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function buildProviderData(): array
    {
        $smtpPass = $this->input('smtp_password', '');
        $imapPass = $this->input('imap_password', '');

        return [
            'name'            => Security::sanitize($this->input('name', 'Provider')),
            'provider_type'   => $this->input('provider_type', 'smtp'),
            'smtp_host'       => Security::sanitize($this->input('smtp_host', '')),
            'smtp_port'       => (int)$this->input('smtp_port', 587),
            'smtp_username'   => Security::sanitize($this->input('smtp_username', '')),
            'smtp_password'   => $smtpPass !== '' ? MailService::encryptPassword($smtpPass) : '',
            'smtp_encryption' => $this->input('smtp_encryption', 'tls'),
            'imap_host'       => Security::sanitize($this->input('imap_host', '')),
            'imap_port'       => (int)$this->input('imap_port', 993),
            'imap_username'   => Security::sanitize($this->input('imap_username', '')),
            'imap_password'   => $imapPass !== '' ? MailService::encryptPassword($imapPass) : '',
            'imap_encryption' => $this->input('imap_encryption', 'ssl'),
            'from_name'       => Security::sanitize($this->input('from_name', '')),
            'from_email'      => Security::sanitize($this->input('from_email', '')),
            'reply_to'        => Security::sanitize($this->input('reply_to', '')),
            'is_imap_enabled' => $this->input('is_imap_enabled') === '1' ? 1 : 0,
            'is_active'       => 0, // use /activate endpoint to set active
        ];
    }

    private function getQueueStats(Database $db): array
    {
        try {
            $row = $db->fetchAll(
                "SELECT status, COUNT(*) AS c FROM email_queue GROUP BY status"
            );
            $stats = ['pending' => 0, 'sent' => 0, 'failed' => 0, 'processing' => 0];
            foreach ($row as $r) {
                $stats[$r['status']] = (int)$r['c'];
            }
            return $stats;
        } catch (\Exception $e) {
            return ['pending' => 0, 'sent' => 0, 'failed' => 0, 'processing' => 0];
        }
    }
}
