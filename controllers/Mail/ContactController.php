<?php

namespace Controllers\Mail;

use Core\View;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

class ContactController extends BaseController
{
    private $subscriberId;
    private $mailboxId;

    public function __construct()
    {
        parent::__construct();
        $this->subscriberId = Auth::user()->subscriber_id;
        $this->mailboxId = Auth::user()->mailbox_id;
    }

    /**
     * List all contacts for the mailbox
     */
    public function index()
    {
        $search = $_GET['search'] ?? '';
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $query = "SELECT * FROM mail_contacts 
                  WHERE mailbox_id = ?";
        $params = [$this->mailboxId];

        if ($search) {
            $query .= " AND (name LIKE ? OR email LIKE ? OR company LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $query .= " ORDER BY name ASC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $contacts = $this->db->fetchAll($query, $params);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM mail_contacts WHERE mailbox_id = ?";
        $countParams = [$this->mailboxId];
        if ($search) {
            $countQuery .= " AND (name LIKE ? OR email LIKE ? OR company LIKE ?)";
            $countParams[] = $searchParam;
            $countParams[] = $searchParam;
            $countParams[] = $searchParam;
        }
        $total = $this->db->fetch($countQuery, $countParams)['total'];

        View::render('contacts/index', [
            'contacts' => $contacts,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search
        ]);
    }

    /**
     * Show add contact form
     */
    public function create()
    {
        View::render('contacts/add');
    }

    /**
     * Store new contact
     */
    public function store()
    {
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validation
        if (empty($name)) {
            $this->error('Name is required');
            return;
        }

        if (empty($email)) {
            $this->error('Email is required');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address');
            return;
        }

        // Check if email already exists
        $existing = $this->db->fetch(
            "SELECT id FROM mail_contacts WHERE mailbox_id = ? AND email = ?",
            [$this->mailboxId, $email]
        );

        if ($existing) {
            $this->error('Contact with this email already exists');
            return;
        }

        // Insert contact
        $this->db->query(
            "INSERT INTO mail_contacts (mailbox_id, name, email, company, phone, notes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$this->mailboxId, $name, $email, $company, $phone, $notes]
        );

        $this->success('Contact added successfully');
        $this->redirect('/projects/mail/contacts');
    }

    /**
     * Show edit contact form
     */
    public function edit($id)
    {
        $contact = $this->db->fetch(
            "SELECT * FROM mail_contacts WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$contact) {
            $this->error('Contact not found');
            $this->redirect('/projects/mail/contacts');
            return;
        }

        View::render('contacts/edit', ['contact' => $contact]);
    }

    /**
     * Update contact
     */
    public function update($id)
    {
        $this->validateCsrf();

        $contact = $this->db->fetch(
            "SELECT * FROM mail_contacts WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$contact) {
            $this->error('Contact not found');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Validation
        if (empty($name)) {
            $this->error('Name is required');
            return;
        }

        if (empty($email)) {
            $this->error('Email is required');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address');
            return;
        }

        // Check if email already exists (exclude current contact)
        $existing = $this->db->fetch(
            "SELECT id FROM mail_contacts WHERE mailbox_id = ? AND email = ? AND id != ?",
            [$this->mailboxId, $email, $id]
        );

        if ($existing) {
            $this->error('Another contact with this email already exists');
            return;
        }

        // Update contact
        $this->db->query(
            "UPDATE mail_contacts 
             SET name = ?, email = ?, company = ?, phone = ?, notes = ?, updated_at = NOW()
             WHERE id = ? AND mailbox_id = ?",
            [$name, $email, $company, $phone, $notes, $id, $this->mailboxId]
        );

        $this->success('Contact updated successfully');
        $this->redirect('/projects/mail/contacts');
    }

    /**
     * Delete contact
     */
    public function delete($id)
    {
        $this->validateCsrf();

        $contact = $this->db->fetch(
            "SELECT * FROM mail_contacts WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        if (!$contact) {
            $this->error('Contact not found');
            return;
        }

        $this->db->query(
            "DELETE FROM mail_contacts WHERE id = ? AND mailbox_id = ?",
            [$id, $this->mailboxId]
        );

        $this->success('Contact deleted successfully');
        $this->redirect('/projects/mail/contacts');
    }

    /**
     * Import contacts from CSV
     */
    public function import()
    {
        $this->validateCsrf();

        if (!isset($_FILES['csv_file'])) {
            $this->error('No file uploaded');
            return;
        }

        $file = $_FILES['csv_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error('File upload failed');
            return;
        }

        // Validate file type
        $allowedTypes = ['text/csv', 'text/plain', 'application/csv'];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->error('Invalid file type. Please upload a CSV file');
            return;
        }

        // Parse CSV
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->error('Failed to read CSV file');
            return;
        }

        $imported = 0;
        $skipped = 0;
        $header = fgetcsv($handle); // Skip header row

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue; // Need at least name and email

            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');
            $company = trim($row[2] ?? '');
            $phone = trim($row[3] ?? '');
            $notes = trim($row[4] ?? '');

            if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            // Check if contact already exists
            $existing = $this->db->fetch(
                "SELECT id FROM mail_contacts WHERE mailbox_id = ? AND email = ?",
                [$this->mailboxId, $email]
            );

            if ($existing) {
                $skipped++;
                continue;
            }

            // Insert contact
            $this->db->query(
                "INSERT INTO mail_contacts (mailbox_id, name, email, company, phone, notes, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$this->mailboxId, $name, $email, $company, $phone, $notes]
            );

            $imported++;
        }

        fclose($handle);

        $this->success("Imported {$imported} contacts. Skipped {$skipped} duplicates/invalid entries.");
        $this->redirect('/projects/mail/contacts');
    }

    /**
     * Export contacts to CSV
     */
    public function export()
    {
        $contacts = $this->db->fetchAll(
            "SELECT name, email, company, phone, notes FROM mail_contacts 
             WHERE mailbox_id = ? ORDER BY name ASC",
            [$this->mailboxId]
        );

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="contacts_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Company', 'Phone', 'Notes']);

        foreach ($contacts as $contact) {
            fputcsv($output, [
                $contact['name'],
                $contact['email'],
                $contact['company'],
                $contact['phone'],
                $contact['notes']
            ]);
        }

        fclose($output);
        exit;
    }
}
