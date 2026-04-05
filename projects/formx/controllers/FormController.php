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

        if (!$title) {
            $this->flash('error', 'Form title is required.');
            $this->redirect('/projects/formx/create');
            return;
        }

        $fields   = json_decode($fieldsJson, true);
        $settings = json_decode($settingsJson, true);
        if (!is_array($fields))   $fields   = [];
        if (!is_array($settings)) $settings = [];

        // Hash access_password if provided and not already a bcrypt hash
        if (!empty($settings['access_password']) && $settings['access_mode'] === 'password') {
            if (strlen($settings['access_password']) < 60 || !str_starts_with($settings['access_password'], '$2')) {
                $settings['access_password'] = password_hash($settings['access_password'], PASSWORD_BCRYPT);
            }
        }

        $slug = $this->uniqueSlug($title);

        $this->db->query(
            "INSERT INTO formx_forms (user_id, title, slug, description, fields, settings, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $this->userId(),
                $title,
                $slug,
                $description ?: null,
                json_encode($fields),
                json_encode($settings),
                in_array($status, ['active', 'inactive', 'draft']) ? $status : 'draft',
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

        if (!$title) {
            $this->flash('error', 'Form title is required.');
            $this->redirect('/projects/formx/' . $id . '/edit');
            return;
        }

        $fields   = json_decode($fieldsJson, true);
        $settings = json_decode($settingsJson, true);
        if (!is_array($fields))   $fields   = [];
        if (!is_array($settings)) $settings = [];

        // Hash access_password if provided and not already a bcrypt hash
        if (!empty($settings['access_password']) && ($settings['access_mode'] ?? '') === 'password') {
            if (strlen($settings['access_password']) < 60 || !str_starts_with($settings['access_password'], '$2')) {
                $settings['access_password'] = password_hash($settings['access_password'], PASSWORD_BCRYPT);
            }
        }

        $this->db->query(
            "UPDATE formx_forms SET title=?, description=?, fields=?, settings=?, status=?, updated_at=NOW() WHERE id=? AND user_id=?",
            [
                $title,
                $description ?: null,
                json_encode($fields),
                json_encode($settings),
                in_array($status, ['active', 'inactive', 'draft']) ? $status : 'draft',
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
}
