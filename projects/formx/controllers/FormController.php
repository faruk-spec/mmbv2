<?php
/**
 * FormX Form Controller (user-facing)
 *
 * Handles form CRUD scoped to the current logged-in user.
 *
 * @package MMB\Projects\FormX\Controllers
 */

namespace Projects\FormX\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Core\Security;
use Core\Helpers;
use Core\ActivityLogger;

class FormController
{
    private Database $db;

    public function __construct()
    {
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
            $sql = file_get_contents(BASE_PATH . '/migrations/formx.sql');
            if ($sql) {
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    try { $this->db->query($stmt); } catch (\Exception $ex) {}
                }
            }
        }
        // Apply v2 migration (idempotent: uses ALTER … ADD COLUMN IF NOT EXISTS + CREATE TABLE IF NOT EXISTS)
        try {
            $v2 = file_get_contents(BASE_PATH . '/migrations/formx_v2.sql');
            if ($v2) {
                foreach (array_filter(array_map('trim', explode(';', $v2))) as $stmt) {
                    if ($stmt) { try { $this->db->query($stmt); } catch (\Exception $ex) {} }
                }
            }
        } catch (\Exception $e) {}
    }

    private function userId(): int
    {
        return (int) Auth::id();
    }

    private function validateCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_POST['_csrf_token'] ?? '';
        return Security::validateCsrfToken($token);
    }

    private function redirect(string $url): void
    {
        Helpers::redirect($url);
    }

    private function flash(string $type, string $msg): void
    {
        Helpers::flash($type, $msg);
    }

    private function input(string $key, $default = null)
    {
        return Helpers::input($key, $default);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    private function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug     = $this->slugify($base);
        $original = $slug;
        $i = 1;
        while (true) {
            $sql    = "SELECT id FROM formx_forms WHERE slug = ?";
            $params = [$slug];
            if ($excludeId) {
                $sql    .= " AND id != ?";
                $params[] = $excludeId;
            }
            if (!$this->db->fetch($sql, $params)) break;
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    private function ownsForm(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM formx_forms WHERE id = ? AND user_id = ?",
            [$id, $this->userId()]
        ) ?: null;
    }

    // -------------------------------------------------------------------------
    // Index – list user's forms
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $search  = trim($this->input('search', ''));
        $status  = $this->input('status', '');
        $page    = max(1, (int) $this->input('page', 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;

        $where  = "user_id = ?";
        $params = [$this->userId()];

        if ($search) {
            $where   .= " AND (title LIKE ? OR slug LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where   .= " AND status = ?";
            $params[] = $status;
        }

        $forms = $this->db->fetchAll(
            "SELECT * FROM formx_forms WHERE {$where} ORDER BY updated_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM formx_forms WHERE {$where}", $params
        );

        // Sidebar recent forms
        $sidebarForms = $this->db->fetchAll(
            "SELECT id, title FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 8",
            [$this->userId()]
        );

        View::render('projects/formx/forms', [
            'title'        => 'My Forms',
            'forms'        => $forms,
            'search'       => $search,
            'status'       => $status,
            'pagination'   => ['current' => $page, 'total' => (int) ceil($total / $perPage)],
            'sidebarForms' => $sidebarForms,
            'activePage'   => 'forms',
        ]);
    }

    // -------------------------------------------------------------------------
    // Create – show blank builder
    // -------------------------------------------------------------------------

    public function create(): void
    {
        $sidebarForms = $this->db->fetchAll(
            "SELECT id, title FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 8",
            [$this->userId()]
        );

        View::render('projects/formx/builder', [
            'title'        => 'Create New Form',
            'form'         => null,
            'action'       => '/projects/formx/create',
            'sidebarForms' => $sidebarForms,
            'activePage'   => 'create',
            'csrfToken'    => Security::generateCsrfToken(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Save – POST create
    // -------------------------------------------------------------------------

    public function save(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/projects/formx/create');
            return;
        }

        $title       = trim($this->input('title', ''));
        $description = trim($this->input('description', ''));
        $fieldsJson  = $this->input('fields_json', '[]');
        $settingsJson = $this->input('settings_json', '{}');
        $status      = $this->input('status', 'draft');
        $expiresAt   = trim($this->input('expires_at', '')) ?: null;
        // Convert local datetime-local value to MySQL datetime
        if ($expiresAt) {
            $expiresAt = date('Y-m-d H:i:s', strtotime($expiresAt)) ?: null;
        }

        if (!$title) {
            $this->flash('error', 'Form title is required.');
            $this->redirect('/projects/formx/create');
            return;
        }

        $fields   = json_decode($fieldsJson, true);
        $settings = json_decode($settingsJson, true);
        if (!is_array($fields))   $fields   = [];
        if (!is_array($settings)) $settings = [];

        // Hash access_password if provided (new form create — no existing hash to preserve)
        if (!empty($settings['access_password_new']) && ($settings['access_mode'] ?? '') === 'password') {
            $settings['access_password'] = password_hash($settings['access_password_new'], PASSWORD_BCRYPT);
        } else {
            $settings['access_password'] = '';
        }
        unset($settings['access_password_new'], $settings['access_password_keep']);

        $slug = $this->uniqueSlug($title);

        $this->db->query(
            "INSERT INTO formx_forms (user_id, title, slug, description, fields, settings, status, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $this->userId(),
                $title,
                $slug,
                $description ?: null,
                json_encode($fields),
                json_encode($settings),
                in_array($status, ['active', 'inactive', 'draft']) ? $status : 'draft',
                $expiresAt,
            ]
        );

        $newId = $this->db->lastInsertId();

        ActivityLogger::log($this->userId(), 'formx.create', [
            'module'        => 'formx',
            'resource_type' => 'formx_form',
            'resource_id'   => $newId,
            'entity_name'   => $title,
            'new_values'    => ['title' => $title, 'slug' => $slug],
        ]);

        $this->flash('success', 'Form created successfully!');
        try { \Core\Notification::send($this->userId(), 'formx_form_created', 'Form "' . $title . '" created in FormX.', ['project' => 'formx', 'form_id' => $newId]); } catch (\Exception $e) {}
        $this->redirect('/projects/formx/' . $newId . '/edit');
    }

    // -------------------------------------------------------------------------
    // Edit – show builder with existing form data
    // -------------------------------------------------------------------------

    public function edit(int $id): void
    {
        $form = $this->ownsForm($id);
        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/projects/formx/forms');
            return;
        }

        $form['fields']   = json_decode($form['fields']   ?? '[]', true) ?: [];
        $form['settings'] = json_decode($form['settings'] ?? '{}', true) ?: [];

        $sidebarForms = $this->db->fetchAll(
            "SELECT id, title FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 8",
            [$this->userId()]
        );

        View::render('projects/formx/builder', [
            'title'        => 'Edit: ' . htmlspecialchars($form['title']),
            'form'         => $form,
            'action'       => '/projects/formx/' . $id . '/edit',
            'sidebarForms' => $sidebarForms,
            'activePage'   => 'forms',
            'csrfToken'    => Security::generateCsrfToken(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Update – POST edit
    // -------------------------------------------------------------------------

    public function update(int $id): void
    {
        $form = $this->ownsForm($id);
        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/projects/formx/forms');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/projects/formx/' . $id . '/edit');
            return;
        }

        $title       = trim($this->input('title', ''));
        $description = trim($this->input('description', ''));
        $fieldsJson  = $this->input('fields_json', '[]');
        $settingsJson = $this->input('settings_json', '{}');
        $status      = $this->input('status', 'draft');
        $expiresAt   = trim($this->input('expires_at', '')) ?: null;
        if ($expiresAt) {
            $expiresAt = date('Y-m-d H:i:s', strtotime($expiresAt)) ?: null;
        }

        if (!$title) {
            $this->flash('error', 'Form title is required.');
            $this->redirect('/projects/formx/' . $id . '/edit');
            return;
        }

        $fields   = json_decode($fieldsJson, true);
        $settings = json_decode($settingsJson, true);
        if (!is_array($fields))   $fields   = [];
        if (!is_array($settings)) $settings = [];

        // Handle access password: new value takes priority; keep existing if flagged
        if (!empty($settings['access_password_new']) && ($settings['access_mode'] ?? '') === 'password') {
            // New plain-text password — hash it
            $settings['access_password'] = password_hash($settings['access_password_new'], PASSWORD_BCRYPT);
        } elseif (!empty($settings['access_password_keep'])) {
            // Keep the existing hash stored in DB
            $current = $this->ownsForm($id);
            $existingSettings = is_string($current['settings'] ?? '') ? json_decode($current['settings'] ?? '{}', true) : ($current['settings'] ?? []);
            $settings['access_password'] = $existingSettings['access_password'] ?? '';
        } else {
            $settings['access_password'] = '';
        }
        unset($settings['access_password_new'], $settings['access_password_keep']);

        // ── Save version snapshot before overwriting ──────────────────────────
        try {
            $current = $this->ownsForm($id);
            if ($current) {
                $this->db->query(
                    "INSERT INTO formx_form_versions (form_id, user_id, title, description, fields, settings, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $id,
                        $this->userId(),
                        $current['title'],
                        $current['description'],
                        is_string($current['fields']) ? $current['fields'] : json_encode($current['fields']),
                        is_string($current['settings']) ? $current['settings'] : json_encode($current['settings']),
                        $current['status'],
                    ]
                );
                // Keep only the 20 most recent versions per form to avoid unbounded growth
                $this->db->query(
                    "DELETE FROM formx_form_versions WHERE form_id = ? AND id NOT IN (
                        SELECT id FROM (SELECT id FROM formx_form_versions WHERE form_id = ? ORDER BY created_at DESC LIMIT 20) t
                    )",
                    [$id, $id]
                );
            }
        } catch (\Exception $e) { /* ignore if versions table not yet created */ }

        $this->db->query(
            "UPDATE formx_forms SET title=?, description=?, fields=?, settings=?, status=?, expires_at=?, updated_at=NOW() WHERE id=? AND user_id=?",
            [
                $title,
                $description ?: null,
                json_encode($fields),
                json_encode($settings),
                in_array($status, ['active', 'inactive', 'draft']) ? $status : 'draft',
                $expiresAt,
                $id,
                $this->userId(),
            ]
        );

        ActivityLogger::log($this->userId(), 'formx.update', [
            'module'        => 'formx',
            'resource_type' => 'formx_form',
            'resource_id'   => $id,
            'entity_name'   => $title,
        ]);

        $this->flash('success', 'Form saved!');
        try { \Core\Notification::send($this->userId(), 'formx_form_updated', 'Form "' . $title . '" updated in FormX.', ['project' => 'formx', 'form_id' => $id]); } catch (\Exception $e) {}
        $this->redirect('/projects/formx/' . $id . '/edit');
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function delete(int $id): void
    {
        $form = $this->ownsForm($id);
        if (!$form || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/projects/formx/forms');
            return;
        }

        $this->db->query("DELETE FROM formx_submissions WHERE form_id = ?", [$id]);
        $this->db->query("DELETE FROM formx_forms WHERE id = ? AND user_id = ?", [$id, $this->userId()]);

        ActivityLogger::log($this->userId(), 'formx.delete', [
            'module'        => 'formx',
            'resource_type' => 'formx_form',
            'resource_id'   => $id,
            'entity_name'   => $form['title'],
        ]);

        $this->flash('success', 'Form deleted.');
        try { \Core\Notification::send($this->userId(), 'formx_form_deleted', 'Form "' . $form['title'] . '" deleted in FormX.', ['project' => 'formx', 'form_id' => $id]); } catch (\Exception $e) {}
        $this->redirect('/projects/formx/forms');
    }

    // -------------------------------------------------------------------------
    // Duplicate
    // -------------------------------------------------------------------------

    public function duplicate(int $id): void
    {
        $form = $this->ownsForm($id);
        if (!$form || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/projects/formx/forms');
            return;
        }

        $newTitle = 'Copy of ' . $form['title'];
        $newSlug  = $this->uniqueSlug($newTitle);

        $this->db->query(
            "INSERT INTO formx_forms (user_id, title, slug, description, fields, settings, status)
             VALUES (?, ?, ?, ?, ?, ?, 'draft')",
            [$this->userId(), $newTitle, $newSlug, $form['description'], $form['fields'], $form['settings']]
        );

        $newId = $this->db->lastInsertId();
        $this->flash('success', 'Form duplicated.');
        $this->redirect('/projects/formx/' . $newId . '/edit');
    }

    // -------------------------------------------------------------------------
    // Submissions list
    // -------------------------------------------------------------------------

    public function submissions(int $id): void
    {
        $form = $this->ownsForm($id);
        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/projects/formx/forms');
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
            "SELECT COUNT(*) FROM formx_submissions WHERE form_id = ?", [$id]
        );

        $form['fields'] = json_decode($form['fields'] ?? '[]', true) ?: [];
        foreach ($submissions as &$sub) {
            $sub['data'] = json_decode($sub['data'] ?? '{}', true) ?: [];
        }
        unset($sub);

        $sidebarForms = $this->db->fetchAll(
            "SELECT id, title FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 8",
            [$this->userId()]
        );

        View::render('projects/formx/submissions', [
            'title'        => 'Submissions: ' . htmlspecialchars($form['title']),
            'form'         => $form,
            'submissions'  => $submissions,
            'pagination'   => ['current' => $page, 'total' => (int) ceil($total / $perPage)],
            'sidebarForms' => $sidebarForms,
            'activePage'   => 'forms',
        ]);
    }

    // -------------------------------------------------------------------------
    // View single submission
    // -------------------------------------------------------------------------

    public function viewSubmission(int $id, int $subId): void
    {
        $form = $this->ownsForm($id);
        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/projects/formx/forms');
            return;
        }

        $sub = $this->db->fetch(
            "SELECT * FROM formx_submissions WHERE id = ? AND form_id = ?",
            [$subId, $id]
        );
        if (!$sub) {
            $this->flash('error', 'Submission not found.');
            $this->redirect('/projects/formx/' . $id . '/submissions');
            return;
        }

        $form['fields']  = json_decode($form['fields']  ?? '[]', true) ?: [];
        $sub['data']     = json_decode($sub['data']     ?? '{}', true) ?: [];

        $sidebarForms = $this->db->fetchAll(
            "SELECT id, title FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 8",
            [$this->userId()]
        );

        View::render('projects/formx/submission-detail', [
            'title'        => 'Submission #' . $subId,
            'form'         => $form,
            'sub'          => $sub,
            'sidebarForms' => $sidebarForms,
            'activePage'   => 'forms',
        ]);
    }

    // -------------------------------------------------------------------------
    // Analytics
    // -------------------------------------------------------------------------

    public function analytics(int $id): void
    {
        $form = $this->ownsForm($id);
        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/projects/formx/forms');
            return;
        }

        // Submissions per day – last 30 days
        $daily = $this->db->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
             FROM formx_submissions WHERE form_id = ?
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
             GROUP BY DATE(created_at) ORDER BY day ASC",
            [$id]
        );

        // Submissions per week – last 8 weeks
        $weekly = $this->db->fetchAll(
            "SELECT YEARWEEK(created_at, 1) AS yw, COUNT(*) AS cnt
             FROM formx_submissions WHERE form_id = ?
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 56 DAY)
             GROUP BY YEARWEEK(created_at, 1) ORDER BY yw ASC",
            [$id]
        );

        // Device breakdown (derived from device column or user_agent)
        $devices = $this->db->fetchAll(
            "SELECT
                CASE
                    WHEN device IS NOT NULL THEN device
                    WHEN user_agent LIKE '%Mobile%' OR user_agent LIKE '%Android%' OR user_agent LIKE '%iPhone%' THEN 'Mobile'
                    WHEN user_agent LIKE '%Tablet%' OR user_agent LIKE '%iPad%' THEN 'Tablet'
                    ELSE 'Desktop'
                END AS device_type,
                COUNT(*) AS cnt
             FROM formx_submissions WHERE form_id = ?
             GROUP BY device_type ORDER BY cnt DESC",
            [$id]
        );

        // Browser breakdown
        $browsers = $this->db->fetchAll(
            "SELECT
                COALESCE(NULLIF(browser, ''), 'Unknown') AS browser_name,
                COUNT(*) AS cnt
             FROM formx_submissions WHERE form_id = ?
             GROUP BY browser_name ORDER BY cnt DESC LIMIT 6",
            [$id]
        );

        // Total & this month
        $total       = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM formx_submissions WHERE form_id=?", [$id]);
        $thisMonth   = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM formx_submissions WHERE form_id=? AND YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())",
            [$id]
        );
        $lastMonth   = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM formx_submissions WHERE form_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$id]
        );

        // Avg submissions per day (over lifetime)
        $firstSub = $this->db->fetchColumn(
            "SELECT MIN(created_at) FROM formx_submissions WHERE form_id=?", [$id]
        );
        $daysSinceFirst = 1;
        if ($firstSub) {
            $diff = (time() - strtotime($firstSub)) / 86400;
            $daysSinceFirst = max(1, $diff);
        }
        $avgPerDay = $total > 0 ? round($total / $daysSinceFirst, 1) : 0;

        // Recent submissions (last 5)
        $recentSubmissions = $this->db->fetchAll(
            "SELECT id, ip_address, created_at FROM formx_submissions
             WHERE form_id = ? ORDER BY created_at DESC LIMIT 5",
            [$id]
        );

        $sidebarForms = $this->db->fetchAll(
            "SELECT id, title FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 8",
            [$this->userId()]
        );

        View::render('projects/formx/analytics', [
            'title'             => 'Analytics: ' . htmlspecialchars($form['title']),
            'form'              => $form,
            'daily'             => $daily,
            'weekly'            => $weekly,
            'devices'           => $devices,
            'browsers'          => $browsers,
            'total'             => $total,
            'thisMonth'         => $thisMonth,
            'lastMonth'         => $lastMonth,
            'avgPerDay'         => $avgPerDay,
            'recentSubmissions' => $recentSubmissions,
            'sidebarForms'      => $sidebarForms,
            'activePage'        => 'forms',
        ]);
    }

    // -------------------------------------------------------------------------
    // Version History
    // -------------------------------------------------------------------------

    public function versions(int $id): void
    {
        $form = $this->ownsForm($id);
        if (!$form) {
            $this->flash('error', 'Form not found.');
            $this->redirect('/projects/formx/forms');
            return;
        }

        $versions = [];
        try {
            $versions = $this->db->fetchAll(
                "SELECT id, title, status, note, created_at FROM formx_form_versions
                 WHERE form_id = ? ORDER BY created_at DESC LIMIT 20",
                [$id]
            );
        } catch (\Exception $e) {}

        $sidebarForms = $this->db->fetchAll(
            "SELECT id, title FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 8",
            [$this->userId()]
        );

        View::render('projects/formx/versions', [
            'title'        => 'Version History: ' . htmlspecialchars($form['title']),
            'form'         => $form,
            'versions'     => $versions,
            'sidebarForms' => $sidebarForms,
            'activePage'   => 'forms',
        ]);
    }

    public function restoreVersion(int $id, int $vid): void
    {
        $form = $this->ownsForm($id);
        if (!$form || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/projects/formx/' . $id . '/versions');
            return;
        }

        $version = null;
        try {
            $version = $this->db->fetch(
                "SELECT * FROM formx_form_versions WHERE id = ? AND form_id = ?",
                [$vid, $id]
            );
        } catch (\Exception $e) {}

        if (!$version) {
            $this->flash('error', 'Version not found.');
            $this->redirect('/projects/formx/' . $id . '/versions');
            return;
        }

        // Snapshot current before restoring
        try {
            $this->db->query(
                "INSERT INTO formx_form_versions (form_id, user_id, title, description, fields, settings, status, note)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $id, $this->userId(), $form['title'], $form['description'],
                    is_string($form['fields']) ? $form['fields'] : json_encode($form['fields']),
                    is_string($form['settings']) ? $form['settings'] : json_encode($form['settings']),
                    $form['status'], 'Auto-snapshot before restore',
                ]
            );
        } catch (\Exception $e) {}

        $this->db->query(
            "UPDATE formx_forms SET title=?, description=?, fields=?, settings=?, status=?, updated_at=NOW() WHERE id=? AND user_id=?",
            [
                $version['title'],
                $version['description'],
                $version['fields'],
                $version['settings'],
                $version['status'],
                $id,
                $this->userId(),
            ]
        );

        $this->flash('success', 'Form restored to version from ' . date('M j Y H:i', strtotime($version['created_at'])) . '.');
        $this->redirect('/projects/formx/' . $id . '/edit');
    }
}
