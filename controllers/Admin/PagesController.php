<?php
namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\ActivityLogger;

class PagesController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('pages');
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $pages = $db->fetchAll("SELECT * FROM pages ORDER BY sort_order ASC, created_at DESC");

        $this->view('admin/pages/index', [
            'title' => 'Pages',
            'pages' => $pages,
        ]);
    }

    public function create(): void
    {
        $this->view('admin/pages/create', ['title' => 'Create Page']);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages/create');
            return;
        }

        $errors = $this->validate([
            'title' => 'required|max:255',
            'slug'  => 'required|max:200',
        ]);

        if (!empty($errors)) {
            $this->redirect('/admin/pages/create');
            return;
        }

        try {
            $db = Database::getInstance();
            $slug = $this->sanitizeSlug($this->input('slug'));

            $existing = $db->fetch("SELECT id FROM pages WHERE slug = ?", [$slug]);
            if ($existing) {
                $this->flash('error', 'A page with that slug already exists.');
                $this->redirect('/admin/pages/create');
                return;
            }

            $id = $db->insert('pages', [
                'title'            => Security::sanitize($this->input('title')),
                'slug'             => $slug,
                'content'          => $this->input('content', ''),
                'meta_title'       => Security::sanitize($this->input('meta_title', '')),
                'meta_description' => Security::sanitize($this->input('meta_description', '')),
                'show_navbar'      => $this->input('show_navbar') ? 1 : 0,
                'show_footer'      => $this->input('show_footer') ? 1 : 0,
                'status'           => in_array($this->input('status'), ['published','draft']) ? $this->input('status') : 'draft',
                'sort_order'       => (int)$this->input('sort_order', 0),
                'created_by'       => Auth::id(),
            ]);

            ActivityLogger::log(Auth::id(), 'page_created', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => $id, 'entity_name' => $this->input('title'), 'new_values' => ['title' => $this->input('title')]]);
            $this->flash('success', 'Page created successfully.');
            $this->redirect('/admin/pages');
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to create page: ' . $e->getMessage());
            $this->redirect('/admin/pages/create');
        }
    }

    public function edit(string $id): void
    {
        $db = Database::getInstance();
        $page = $db->fetch("SELECT * FROM pages WHERE id = ?", [(int)$id]);

        if (!$page) {
            $this->flash('error', 'Page not found.');
            $this->redirect('/admin/pages');
            return;
        }

        $this->view('admin/pages/edit', ['title' => 'Edit Page', 'page' => $page]);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages/' . $id . '/edit');
            return;
        }

        $errors = $this->validate([
            'title' => 'required|max:255',
            'slug'  => 'required|max:200',
        ]);

        if (!empty($errors)) {
            $this->redirect('/admin/pages/' . $id . '/edit');
            return;
        }

        try {
            $db = Database::getInstance();
            $slug = $this->sanitizeSlug($this->input('slug'));

            $existing = $db->fetch("SELECT id FROM pages WHERE slug = ? AND id != ?", [$slug, (int)$id]);
            if ($existing) {
                $this->flash('error', 'A page with that slug already exists.');
                $this->redirect('/admin/pages/' . $id . '/edit');
                return;
            }

            $db->update('pages', [
                'title'            => Security::sanitize($this->input('title')),
                'slug'             => $slug,
                'content'          => $this->input('content', ''),
                'meta_title'       => Security::sanitize($this->input('meta_title', '')),
                'meta_description' => Security::sanitize($this->input('meta_description', '')),
                'show_navbar'      => $this->input('show_navbar') ? 1 : 0,
                'show_footer'      => $this->input('show_footer') ? 1 : 0,
                'status'           => in_array($this->input('status'), ['published','draft']) ? $this->input('status') : 'draft',
                'sort_order'       => (int)$this->input('sort_order', 0),
            ], 'id = ?', [(int)$id]);

            ActivityLogger::log(Auth::id(), 'page_updated', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => (int)$id, 'entity_name' => $this->input('title')]);
            $this->flash('success', 'Page updated successfully.');
            $this->redirect('/admin/pages');
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to update page: ' . $e->getMessage());
            $this->redirect('/admin/pages/' . $id . '/edit');
        }
    }

    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages');
            return;
        }

        try {
            $db = Database::getInstance();
            $page = $db->fetch("SELECT * FROM pages WHERE id = ?", [(int)$id]);
            if ($page) {
                $db->delete('pages', 'id = ?', [(int)$id]);
                ActivityLogger::log(Auth::id(), 'page_deleted', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => (int)$id, 'entity_name' => $page['title']]);
                $this->flash('success', 'Page deleted.');
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to delete page: ' . $e->getMessage());
        }

        $this->redirect('/admin/pages');
    }

    public function toggleStatus(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages');
            return;
        }

        try {
            $db = Database::getInstance();
            $page = $db->fetch("SELECT * FROM pages WHERE id = ?", [(int)$id]);
            if (!$page) {
                $this->flash('error', 'Page not found.');
                $this->redirect('/admin/pages');
                return;
            }
            $newStatus = $page['status'] === 'published' ? 'draft' : 'published';
            $db->update('pages', ['status' => $newStatus], 'id = ?', [(int)$id]);
            ActivityLogger::log(Auth::id(), 'page_status_changed', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => (int)$id, 'entity_name' => $page['title'] ?? '', 'new_values' => ['status' => $newStatus]]);
            $this->flash('success', 'Page status changed to ' . $newStatus . '.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/pages');
    }

    private function sanitizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9\-_\/]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
