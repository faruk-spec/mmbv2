<?php
/**
 * FormX Admin Controller
 *
 * Provides a drag-and-drop Form Builder for the admin panel.
 * Features: create/edit forms, manage submissions, public form rendering.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\ActivityLogger;
use Core\Security;

class FormXController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('formx');
        $this->db = Database::getInstance();
        $this->ensureTables();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function ensureTables(): void
    {
        try {
            $this->db->query("SELECT 1 FROM formx_forms LIMIT 1");
        } catch (\Exception $e) {
            $sql = file_get_contents(__DIR__ . '/../../migrations/formx.sql');
            if ($sql) {
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    try {
                        $this->db->query($stmt);
                    } catch (\Exception $ex) {
                        // ignore individual failures (e.g. already exists)
                    }
                }
            }
        }
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    private function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $this->slugify($base);
        $original = $slug;
        $i = 1;
        while (true) {
            $sql = "SELECT id FROM formx_forms WHERE slug = ?";
            $params = [$slug];
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            if (!$this->db->fetch($sql, $params)) {
                break;
            }
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    // -------------------------------------------------------------------------
    // Index – list all forms
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $this->requirePermission('formx');

        $search = trim($this->input('search', ''));
        $status = $this->input('status', '');
        $page   = max(1, (int) $this->input('page', 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $where  = '1=1';
        $params = [];
        if ($search) {
            $where .= " AND (title LIKE ? OR slug LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $forms = $this->db->fetchAll(
            "SELECT * FROM formx_forms WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM formx_forms WHERE {$where}",
            $params
        );

        $this->view('admin/formx/index', [
            'title'      => 'FormX – Form Builder',
            'forms'      => $forms,
            'search'     => $search,
            'status'     => $status,
            'pagination' => [
                'current' => $page,
                'total'   => (int) ceil($total / $perPage),
                'perPage' => $perPage,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Create – show blank builder
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $this->requirePermission('formx.create');
        $this->view('admin/formx/builder', [
            'title'  => 'Create New Form',
            'form'   => null,
            'action' => '/admin/formx/save',
        ]);
    }

    // -------------------------------------------------------------------------
    // Edit – show populated builder
    // -------------------------------------------------------------------------

    public function edit(string $id = ''): void
    {
        $this->requirePermission('formx.edit');
        $id   = (int) $id ?: (int) ($this->input('id') ?? 0);
        $form = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$id]);

        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/admin/formx');
            return;
        }

        $form['fields']   = json_decode($form['fields']   ?? '[]', true) ?: [];
        $form['settings'] = json_decode($form['settings'] ?? '{}', true) ?: [];

        $this->view('admin/formx/builder', [
            'title'  => 'Edit Form: ' . htmlspecialchars($form['title']),
            'form'   => $form,
            'action' => '/admin/formx/' . $id . '/update',
        ]);
    }

    // -------------------------------------------------------------------------
    // Save (create POST)
    // -------------------------------------------------------------------------

    public function save(): void
    {
        $this->requirePermission('formx.create');
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/formx/create');
            return;
        }

        $title    = trim($this->input('title', ''));
        $description = trim($this->input('description', ''));
        $fieldsJson  = $this->input('fields_json', '[]');
        $settingsJson = $this->input('settings_json', '{}');
        $status   = $this->input('status', 'draft');

        if (!$title) {
            $this->flash('error', 'Form title is required.');
            $this->redirect('/admin/formx/create');
            return;
        }

        // Validate JSON
        $fields   = json_decode($fieldsJson, true);
        $settings = json_decode($settingsJson, true);
        if (!is_array($fields))   $fields   = [];
        if (!is_array($settings)) $settings = [];

        $slug = $this->uniqueSlug($title);

        $this->db->query(
            "INSERT INTO formx_forms (user_id, title, slug, description, fields, settings, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                Auth::id(),
                $title,
                $slug,
                $description ?: null,
                json_encode($fields),
                json_encode($settings),
                in_array($status, ['active','inactive','draft']) ? $status : 'draft',
            ]
        );

        $newId = $this->db->lastInsertId();

        ActivityLogger::log(Auth::id(), 'formx.create', [
            'module'        => 'formx',
            'resource_type' => 'formx_form',
            'resource_id'   => $newId,
            'entity_name'   => $title,
            'new_values'    => ['title' => $title, 'slug' => $slug],
        ]);

        $this->flash('success', 'Form created successfully.');
        $this->redirect('/admin/formx/' . $newId . '/edit');
    }

    // -------------------------------------------------------------------------
    // Update (edit POST)
    // -------------------------------------------------------------------------

    public function update(string $id = ''): void
    {
        $this->requirePermission('formx.edit');
        $id = (int) $id ?: (int) ($this->input('id') ?? 0);
        $form = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$id]);

        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/admin/formx');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/formx/' . $id . '/edit');
            return;
        }

        $title       = trim($this->input('title', ''));
        $description = trim($this->input('description', ''));
        $fieldsJson  = $this->input('fields_json', '[]');
        $settingsJson = $this->input('settings_json', '{}');
        $status      = $this->input('status', 'draft');

        if (!$title) {
            $this->flash('error', 'Form title is required.');
            $this->redirect('/admin/formx/' . $id . '/edit');
            return;
        }

        $fields   = json_decode($fieldsJson, true);
        $settings = json_decode($settingsJson, true);
        if (!is_array($fields))   $fields   = [];
        if (!is_array($settings)) $settings = [];

        // Handle expires_at from settings payload
        $expiresAt = null;
        if (!empty($settings['expires_at'])) {
            $parsed = strtotime($settings['expires_at']);
            if ($parsed) {
                $expiresAt = date('Y-m-d H:i:s', $parsed);
            }
        }
        unset($settings['expires_at']);

        // Handle access password: new value takes priority; keep existing if flagged
        if (!empty($settings['access_password_new']) && ($settings['access_mode'] ?? '') === 'password') {
            $settings['access_password'] = password_hash($settings['access_password_new'], PASSWORD_BCRYPT);
        } elseif (!empty($settings['access_password_keep'])) {
            $current = $this->db->fetch("SELECT settings FROM formx_forms WHERE id = ?", [$id]);
            $existingSettings = is_string($current['settings'] ?? '') ? json_decode($current['settings'] ?? '{}', true) : ($current['settings'] ?? []);
            $settings['access_password'] = $existingSettings['access_password'] ?? '';
        } else {
            $settings['access_password'] = '';
        }
        unset($settings['access_password_new'], $settings['access_password_keep']);

        $this->db->query(
            "UPDATE formx_forms SET title=?, description=?, fields=?, settings=?, status=?, expires_at=?, updated_at=NOW() WHERE id=?",
            [
                $title,
                $description ?: null,
                json_encode($fields),
                json_encode($settings),
                in_array($status, ['active','inactive','draft']) ? $status : 'draft',
                $expiresAt,
                $id,
            ]
        );

        ActivityLogger::log(Auth::id(), 'formx.update', [
            'module'        => 'formx',
            'resource_type' => 'formx_form',
            'resource_id'   => $id,
            'entity_name'   => $title,
        ]);

        $this->flash('success', 'Form saved successfully.');
        $this->redirect('/admin/formx/' . $id . '/edit');
    }

    // -------------------------------------------------------------------------
    // Duplicate a form
    // -------------------------------------------------------------------------

    public function duplicate(string $id = ''): void
    {
        $this->requirePermission('formx.create');
        $id   = (int) $id ?: (int) ($this->input('id') ?? 0);
        $form = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$id]);

        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/admin/formx');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/formx');
            return;
        }

        $newTitle = 'Copy of ' . $form['title'];
        $newSlug  = $this->uniqueSlug($newTitle);

        $this->db->query(
            "INSERT INTO formx_forms (user_id, title, slug, description, fields, settings, status)
             VALUES (?, ?, ?, ?, ?, ?, 'draft')",
            [Auth::id(), $newTitle, $newSlug, $form['description'], $form['fields'], $form['settings']]
        );

        $newId = $this->db->lastInsertId();
        $this->flash('success', 'Form duplicated. You are now editing the copy.');
        $this->redirect('/admin/formx/' . $newId . '/edit');
    }

    // -------------------------------------------------------------------------
    // Toggle status (active ↔ inactive)
    // -------------------------------------------------------------------------

    public function toggle(string $id = ''): void
    {
        $this->requirePermission('formx.edit');
        $id   = (int) $id ?: (int) ($this->input('id') ?? 0);
        $form = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$id]);

        if (!$form || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/formx');
            return;
        }

        $newStatus = $form['status'] === 'active' ? 'inactive' : 'active';
        $this->db->query("UPDATE formx_forms SET status=?, updated_at=NOW() WHERE id=?", [$newStatus, $id]);

        $this->flash('success', 'Form status updated to ' . $newStatus . '.');
        $this->redirect('/admin/formx');
    }

    // -------------------------------------------------------------------------
    // Delete a form
    // -------------------------------------------------------------------------

    public function delete(string $id = ''): void
    {
        $this->requirePermission('formx.delete');
        $id   = (int) $id ?: (int) ($this->input('id') ?? 0);
        $form = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$id]);

        if (!$form || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/formx');
            return;
        }

        $this->db->query("DELETE FROM formx_forms WHERE id = ?", [$id]);

        ActivityLogger::log(Auth::id(), 'formx.delete', [
            'module'        => 'formx',
            'resource_type' => 'formx_form',
            'resource_id'   => $id,
            'entity_name'   => $form['title'],
        ]);

        $this->flash('success', 'Form deleted.');
        $this->redirect('/admin/formx');
    }

    // -------------------------------------------------------------------------
    // Submissions list
    // -------------------------------------------------------------------------

    public function submissions(string $id = ''): void
    {
        $this->requirePermission('formx');
        $id   = (int) $id ?: (int) ($this->input('id') ?? 0);
        $form = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$id]);

        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/admin/formx');
            return;
        }

        $page    = max(1, (int) $this->input('page', 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $submissions = $this->db->fetchAll(
            "SELECT * FROM formx_submissions WHERE form_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$id, $perPage, $offset]
        );

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM formx_submissions WHERE form_id = ?",
            [$id]
        );

        $form['fields'] = json_decode($form['fields'] ?? '[]', true) ?: [];

        foreach ($submissions as &$sub) {
            $sub['data'] = json_decode($sub['data'] ?? '{}', true) ?: [];
        }
        unset($sub);

        $this->view('admin/formx/submissions', [
            'title'       => 'Submissions: ' . htmlspecialchars($form['title']),
            'form'        => $form,
            'submissions' => $submissions,
            'pagination'  => [
                'current' => $page,
                'total'   => (int) ceil($total / $perPage),
                'perPage' => $perPage,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Single submission detail
    // -------------------------------------------------------------------------

    public function submissionDetail(string $form_id = '', string $submission_id = ''): void
    {
        $this->requirePermission('formx');
        $formId = (int) $form_id ?: (int) ($this->input('form_id') ?? 0);
        $subId  = (int) $submission_id ?: (int) ($this->input('submission_id') ?? 0);

        $form       = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$formId]);
        $submission = $this->db->fetch(
            "SELECT * FROM formx_submissions WHERE id = ? AND form_id = ?",
            [$subId, $formId]
        );

        if (!$form || !$submission) {
            $this->flash('error', 'Submission not found.');
            $this->redirect('/admin/formx/' . $formId . '/submissions');
            return;
        }

        $form['fields']    = json_decode($form['fields']    ?? '[]', true) ?: [];
        $submission['data'] = json_decode($submission['data'] ?? '{}', true) ?: [];

        $this->view('admin/formx/submission-detail', [
            'title'      => 'Submission #' . $subId,
            'form'       => $form,
            'submission' => $submission,
        ]);
    }

    // -------------------------------------------------------------------------
    // Delete a single submission
    // -------------------------------------------------------------------------

    public function deleteSubmission(string $form_id = '', string $submission_id = ''): void
    {
        $this->requirePermission('formx.delete');
        $formId = (int) $form_id ?: (int) ($this->input('form_id') ?? 0);
        $subId  = (int) $submission_id ?: (int) ($this->input('submission_id') ?? 0);

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/formx/' . $formId . '/submissions');
            return;
        }

        $this->db->query(
            "DELETE FROM formx_submissions WHERE id = ? AND form_id = ?",
            [$subId, $formId]
        );

        // Update counter
        $this->db->query(
            "UPDATE formx_forms SET submissions_count = (SELECT COUNT(*) FROM formx_submissions WHERE form_id = ?) WHERE id = ?",
            [$formId, $formId]
        );

        $this->flash('success', 'Submission deleted.');
        $this->redirect('/admin/formx/' . $formId . '/submissions');
    }

    // -------------------------------------------------------------------------
    // Export submissions as CSV
    // -------------------------------------------------------------------------

    public function exportSubmissions(string $id = ''): void
    {
        $this->requirePermission('formx');
        $id   = (int) $id ?: (int) ($this->input('id') ?? 0);
        $form = $this->db->fetch("SELECT * FROM formx_forms WHERE id = ?", [$id]);

        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/admin/formx');
            return;
        }

        $fields = json_decode($form['fields'] ?? '[]', true) ?: [];
        $submissions = $this->db->fetchAll(
            "SELECT * FROM formx_submissions WHERE form_id = ? ORDER BY created_at DESC",
            [$id]
        );

        $filename = 'formx-' . $form['slug'] . '-submissions-' . date('Ymd') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        // Header row
        $headers = ['#', 'Submitted At', 'IP Address'];
        foreach ($fields as $field) {
            $headers[] = $field['label'] ?? $field['name'] ?? 'Field';
        }
        fputcsv($out, $headers);

        // Data rows
        foreach ($submissions as $i => $sub) {
            $data = json_decode($sub['data'] ?? '{}', true) ?: [];
            $row = [$i + 1, $sub['created_at'], $sub['ip_address']];
            foreach ($fields as $field) {
                $key = $field['name'] ?? '';
                $val = $data[$key] ?? '';
                if (is_array($val)) $val = implode(', ', $val);
                $row[] = $val;
            }
            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    // -------------------------------------------------------------------------
    // Global admin overview / analytics
    // -------------------------------------------------------------------------

    public function overview(): void
    {
        $this->requirePermission('formx');

        // Summary stats
        $totalForms       = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM formx_forms");
        $activeForms      = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM formx_forms WHERE status = 'active'");
        $totalSubmissions = (int)$this->db->fetchColumn("SELECT COALESCE(SUM(submissions_count),0) FROM formx_forms");
        $totalUsers       = (int)$this->db->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM formx_forms WHERE user_id IS NOT NULL");

        // Submissions per day – last 30 days
        $daily = $this->db->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
             FROM formx_submissions
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
             GROUP BY DATE(created_at) ORDER BY day ASC"
        );

        // Submissions per week – last 8 weeks
        $weekly = $this->db->fetchAll(
            "SELECT YEARWEEK(created_at, 1) AS yw, COUNT(*) AS cnt
             FROM formx_submissions
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 56 DAY)
             GROUP BY YEARWEEK(created_at, 1) ORDER BY yw ASC"
        );

        // Top 10 forms by submission count
        $topForms = $this->db->fetchAll(
            "SELECT id, title, slug, status, submissions_count, user_id
             FROM formx_forms ORDER BY submissions_count DESC LIMIT 10"
        );

        // Device breakdown (all forms)
        $devices = $this->db->fetchAll(
            "SELECT
                CASE
                    WHEN device IS NOT NULL THEN device
                    WHEN user_agent LIKE '%Mobile%' OR user_agent LIKE '%Android%' OR user_agent LIKE '%iPhone%' THEN 'Mobile'
                    WHEN user_agent LIKE '%Tablet%' OR user_agent LIKE '%iPad%' THEN 'Tablet'
                    ELSE 'Desktop'
                END AS device_type,
                COUNT(*) AS cnt
             FROM formx_submissions
             GROUP BY device_type ORDER BY cnt DESC"
        );

        // Recent submissions (last 10)
        $recentSubmissions = $this->db->fetchAll(
            "SELECT fs.id, fs.form_id, fs.ip_address, fs.created_at, ff.title AS form_title
             FROM formx_submissions fs
             JOIN formx_forms ff ON ff.id = fs.form_id
             ORDER BY fs.created_at DESC LIMIT 10"
        );

        // This month vs last month
        $thisMonth = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM formx_submissions
             WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())"
        );
        $lastMonthCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM formx_submissions
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
               AND created_at <  DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );

        \Core\View::render('admin/formx/overview', [
            'title'             => 'FormX – Overview',
            'totalForms'        => $totalForms,
            'activeForms'       => $activeForms,
            'totalSubmissions'  => $totalSubmissions,
            'totalUsers'        => $totalUsers,
            'daily'             => $daily,
            'weekly'            => $weekly,
            'topForms'          => $topForms,
            'devices'           => $devices,
            'recentSubmissions' => $recentSubmissions,
            'thisMonth'         => $thisMonth,
            'lastMonthCount'    => $lastMonthCount,
        ]);
    }
}
