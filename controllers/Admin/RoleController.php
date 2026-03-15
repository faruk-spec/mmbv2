<?php
/**
 * Admin Role Controller
 *
 * CRUD for user roles. System roles (super_admin, admin, project_admin, user)
 * are auto-seeded and protected from deletion; custom roles can be freely
 * created, edited and removed.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;

class RoleController extends BaseController
{
    private Database $db;

    /** System roles that mirror the users.role ENUM values. */
    private const SYSTEM_ROLES = [
        'super_admin'   => ['name' => 'Super Admin',   'description' => 'Full system access with all privileges.',                     'color' => '#ff4444', 'sort_order' => 1],
        'admin'         => ['name' => 'Admin',          'description' => 'Administrative access to platform management.',               'color' => '#ff8800', 'sort_order' => 2],
        'project_admin' => ['name' => 'Project Admin',  'description' => 'Administrative access to assigned projects only.',            'color' => '#00bbff', 'sort_order' => 3],
        'user'          => ['name' => 'User',            'description' => 'Standard user with access to subscribed services.',          'color' => '#44cc44', 'sort_order' => 4],
    ];

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->db = Database::getInstance();
        $this->ensureTables();
    }

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $roles = $this->db->fetchAll(
            "SELECT * FROM user_roles ORDER BY sort_order ASC, name ASC"
        );

        foreach ($roles as &$role) {
            if ($role['is_system']) {
                try {
                    $role['user_count'] = (int) $this->db->fetchColumn(
                        "SELECT COUNT(*) FROM users WHERE role = ?",
                        [$role['slug']]
                    );
                } catch (\Exception $e) {
                    $role['user_count'] = 0;
                }
            } else {
                $role['user_count'] = 0;
            }
        }
        unset($role);

        $this->view('admin/roles/index', [
            'title' => 'User Roles',
            'roles' => $roles,
        ]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function createForm(): void
    {
        $this->view('admin/roles/form', [
            'title'  => 'Create Role',
            'role'   => null,
            'action' => '/admin/roles/create',
        ]);
    }

    public function create(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/roles/create');
            return;
        }

        $name      = Security::sanitize($this->input('name', ''));
        $slug      = strtolower(preg_replace('/[^a-z0-9_]/', '_', $this->input('slug', '')));
        $slug      = trim($slug, '_');
        $desc      = Security::sanitize($this->input('description', ''));
        $color     = preg_match('/^#[0-9a-fA-F]{6}$/', $this->input('color', '#9945ff'))
                        ? $this->input('color') : '#9945ff';
        $status    = $this->input('status') === 'inactive' ? 'inactive' : 'active';
        $sortOrder = max(0, (int) $this->input('sort_order', 0));

        if (empty($name) || empty($slug)) {
            $this->flash('error', 'Role name and slug are required.');
            $this->redirect('/admin/roles/create');
            return;
        }

        $existing = $this->db->fetch("SELECT id FROM user_roles WHERE slug = ?", [$slug]);
        if ($existing) {
            $this->flash('error', 'A role with this slug already exists.');
            $this->redirect('/admin/roles/create');
            return;
        }

        try {
            $this->db->query(
                "INSERT INTO user_roles (name, slug, description, color, is_system, status, sort_order, created_at)
                 VALUES (?, ?, ?, ?, 0, ?, ?, NOW())",
                [$name, $slug, $desc, $color, $status, $sortOrder]
            );
            Logger::activity(Auth::id(), 'role_created', ['slug' => $slug, 'name' => $name]);
            $this->flash('success', 'Role "' . $name . '" created successfully.');
        } catch (\Exception $e) {
            Logger::error('RoleController::create — ' . $e->getMessage());
            $this->flash('error', 'Failed to create role: ' . $e->getMessage());
        }

        $this->redirect('/admin/roles');
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function editForm(int $id): void
    {
        $role = $this->db->fetch("SELECT * FROM user_roles WHERE id = ?", [$id]);
        if (!$role) {
            $this->flash('error', 'Role not found.');
            $this->redirect('/admin/roles');
            return;
        }

        $this->view('admin/roles/form', [
            'title'  => 'Edit Role: ' . htmlspecialchars($role['name']),
            'role'   => $role,
            'action' => '/admin/roles/' . $id . '/update',
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect("/admin/roles/{$id}/edit");
            return;
        }

        $role = $this->db->fetch("SELECT * FROM user_roles WHERE id = ?", [$id]);
        if (!$role) {
            $this->flash('error', 'Role not found.');
            $this->redirect('/admin/roles');
            return;
        }

        $name      = Security::sanitize($this->input('name', ''));
        $desc      = Security::sanitize($this->input('description', ''));
        $color     = preg_match('/^#[0-9a-fA-F]{6}$/', $this->input('color', '#9945ff'))
                        ? $this->input('color') : '#9945ff';
        $status    = $this->input('status') === 'inactive' ? 'inactive' : 'active';
        $sortOrder = max(0, (int) $this->input('sort_order', 0));

        if (empty($name)) {
            $this->flash('error', 'Role name is required.');
            $this->redirect("/admin/roles/{$id}/edit");
            return;
        }

        // Slug is read-only for system roles; editable for custom ones
        $slug = $role['slug'];
        if (!$role['is_system']) {
            $newSlug = strtolower(preg_replace('/[^a-z0-9_]/', '_', $this->input('slug', $slug)));
            $newSlug = trim($newSlug, '_');
            if ($newSlug !== $slug && !empty($newSlug)) {
                $conflict = $this->db->fetch(
                    "SELECT id FROM user_roles WHERE slug = ? AND id != ?",
                    [$newSlug, $id]
                );
                if ($conflict) {
                    $this->flash('error', 'A role with this slug already exists.');
                    $this->redirect("/admin/roles/{$id}/edit");
                    return;
                }
                $slug = $newSlug;
            }
        }

        try {
            $this->db->query(
                "UPDATE user_roles
                 SET name = ?, slug = ?, description = ?, color = ?, status = ?, sort_order = ?, updated_at = NOW()
                 WHERE id = ?",
                [$name, $slug, $desc, $color, $status, $sortOrder, $id]
            );
            Logger::activity(Auth::id(), 'role_updated', ['role_id' => $id, 'name' => $name]);
            $this->flash('success', 'Role "' . $name . '" updated successfully.');
        } catch (\Exception $e) {
            Logger::error('RoleController::update — ' . $e->getMessage());
            $this->flash('error', 'Failed to update role.');
        }

        $this->redirect('/admin/roles');
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function delete(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/roles');
            return;
        }

        $role = $this->db->fetch("SELECT * FROM user_roles WHERE id = ?", [$id]);
        if (!$role) {
            $this->flash('error', 'Role not found.');
            $this->redirect('/admin/roles');
            return;
        }

        if ($role['is_system']) {
            $this->flash('error', 'System roles cannot be deleted.');
            $this->redirect('/admin/roles');
            return;
        }

        try {
            $this->db->query("DELETE FROM user_roles WHERE id = ?", [$id]);
            Logger::activity(Auth::id(), 'role_deleted', ['role_id' => $id, 'name' => $role['name']]);
            $this->flash('success', 'Role "' . $role['name'] . '" deleted.');
        } catch (\Exception $e) {
            Logger::error('RoleController::delete — ' . $e->getMessage());
            $this->flash('error', 'Failed to delete role.');
        }

        $this->redirect('/admin/roles');
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function ensureTables(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `user_roles` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `slug` VARCHAR(100) NOT NULL UNIQUE,
                    `description` TEXT NULL,
                    `color` VARCHAR(7) DEFAULT '#9945ff',
                    `is_system` TINYINT(1) DEFAULT 0,
                    `status` ENUM('active','inactive') DEFAULT 'active',
                    `sort_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_slug` (`slug`),
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Seed system roles once if they are missing
            $count = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM user_roles WHERE is_system = 1"
            );
            if ($count === 0) {
                foreach (self::SYSTEM_ROLES as $slug => $meta) {
                    $this->db->query(
                        "INSERT IGNORE INTO user_roles
                             (name, slug, description, color, is_system, status, sort_order, created_at)
                         VALUES (?, ?, ?, ?, 1, 'active', ?, NOW())",
                        [$meta['name'], $slug, $meta['description'], $meta['color'], $meta['sort_order']]
                    );
                }
            }
        } catch (\Exception $e) {
            Logger::error('RoleController::ensureTables — ' . $e->getMessage());
        }
    }
}
