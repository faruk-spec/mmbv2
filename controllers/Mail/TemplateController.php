<?php

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class TemplateController extends BaseController
{
    private $db;
    private $subscriberId;
    private $mailboxId;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->subscriberId = Auth::user()->subscriber_id;
        $this->mailboxId = Auth::user()->mailbox_id;
    }

    /**
     * List all email templates
     */
    public function index()
    {
        $search = $_GET['search'] ?? '';
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $query = "SELECT * FROM mail_templates 
                  WHERE mailbox_id = ?";
        $params = [$this->mailboxId];

        if ($search) {
            $query .= " AND (name LIKE ? OR subject LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $query .= " ORDER BY name ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $templates = $this->db->fetchAll($query, $params);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM mail_templates WHERE mailbox_id = ?";
        $countParams = [$this->mailboxId];
        if ($search) {
            $countQuery .= " AND (name LIKE ? OR subject LIKE ?)";
            $countParams[] = $searchParam;
            $countParams[] = $searchParam;
        }
        $total = $this->db->fetch($countQuery, $countParams)['total'];

        View::render('templates/index', [
            'templates' => $templates,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search
        ]);
    }

    /**
     * Show create template form
     */
    public function create()
    {
        View::render('templates/add');
    }

    /**
     * Store new template
     */
    public function store()
    {
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = $_POST['body'] ?? '';
        $isHtml = isset($_POST['is_html']) ? 1 : 0;

        // Validation
        if (empty($name)) {
            $this->error('Template name is required');
            return;
        }

        if (empty($subject)) {
            $this->error('Subject is required');
            return;
        }

        if (empty($body)) {
            $this->error('Body is required');
            return;
        }

        // Check if template name already exists
        $existing = $this->db->fetch(
            "SELECT id FROM mail_templates WHERE mailbox_id = ? AND name = ?",
            [$this->mailboxId, $name]
        );

        if ($existing) {
            $this->error('Template with this name already exists');
            return;
        }

        // Insert template
        $this->db->query(
            "INSERT INTO mail_templates (mailbox_id, name, subject, body, is_html, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$this->mailboxId, $name, $subject, $body, $isHtml]
        );

        $this->success('Template created successfully');
        $this->redirect('/projects/mail/templates');
    }

    /**
     * Show edit template form
     */
    public function edit($id)
    {
        $template = $this->db->fetch(
            "SELECT * FROM mail_templates WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$template) {
            $this->error('Template not found');
            $this->redirect('/projects/mail/templates');
            return;
        }

        View::render('templates/edit', ['template' => $template]);
    }

    /**
     * Update template
     */
    public function update($id)
    {
        $this->validateCsrf();

        $template = $this->db->fetch(
            "SELECT * FROM mail_templates WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$template) {
            $this->error('Template not found');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = $_POST['body'] ?? '';
        $isHtml = isset($_POST['is_html']) ? 1 : 0;

        // Validation
        if (empty($name)) {
            $this->error('Template name is required');
            return;
        }

        if (empty($subject)) {
            $this->error('Subject is required');
            return;
        }

        if (empty($body)) {
            $this->error('Body is required');
            return;
        }

        // Check if template name already exists (exclude current template)
        $existing = $this->db->fetch(
            "SELECT id FROM mail_templates WHERE mailbox_id = ? AND name = ? AND id != ?",
            [$this->mailboxId, $name, $id]
        );

        if ($existing) {
            $this->error('Another template with this name already exists');
            return;
        }

        // Update template
        $this->db->query(
            "UPDATE mail_templates 
             SET name = ?, subject = ?, body = ?, is_html = ?, updated_at = NOW()
             WHERE id = ? AND mailbox_id = ?",
            [$name, $subject, $body, $isHtml, $id, $this->mailboxId]
        );

        $this->success('Template updated successfully');
        $this->redirect('/projects/mail/templates');
    }

    /**
     * Delete template
     */
    public function delete($id)
    {
        $this->validateCsrf();

        $template = $this->db->fetch(
            "SELECT * FROM mail_templates WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$template) {
            $this->error('Template not found');
            return;
        }

        $this->db->query(
            "DELETE FROM mail_templates WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        $this->success('Template deleted successfully');
        $this->redirect('/projects/mail/templates');
    }

    /**
     * Duplicate template
     */
    public function duplicate($id)
    {
        $this->validateCsrf();

        $template = $this->db->fetch(
            "SELECT * FROM mail_templates WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$template) {
            $this->error('Template not found');
            return;
        }

        // Create new name
        $newName = $template['name'] . ' (Copy)';
        $counter = 1;
        
        while ($this->db->fetch(
            "SELECT id FROM mail_templates WHERE mailbox_id = ? AND name = ?",
            [$this->mailboxId, $newName]
        )) {
            $counter++;
            $newName = $template['name'] . ' (Copy ' . $counter . ')';
        }

        // Insert duplicate
        $this->db->query(
            "INSERT INTO mail_templates (mailbox_id, name, subject, body, is_html, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$this->mailboxId, $newName, $template['subject'], $template['body'], $template['is_html']]
        );

        $this->success('Template duplicated successfully');
        $this->redirect('/projects/mail/templates');
    }

    /**
     * Get template by ID (AJAX)
     */
    public function get($id)
    {
        $template = $this->db->fetch(
            "SELECT * FROM mail_templates WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$template) {
            http_response_code(404);
            echo json_encode(['error' => 'Template not found']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode($template);
        exit;
    }
}
