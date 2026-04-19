<?php
/**
 * Admin Project Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Logger;
use Core\ActivityLogger;

class ProjectController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('projects');
    }

    public function index(): void
    {
        $this->requirePermission('projects.list');

        $projects = $this->getProjectsWithFallback();

        $this->view('admin/projects/index', [
            'title' => 'Project Management',
            'projects' => $projects,
        ]);
    }

    public function show(string $name): void
    {
        $this->requirePermission('projects');

        $projects = $this->getProjectsWithFallback();
        if (!isset($projects[$name])) {
            $this->flash('error', 'Project not found.');
            $this->redirect('/admin/projects');
            return;
        }

        $project = $projects[$name];

        $this->view('admin/projects/show', [
            'title' => $project['name'],
            'project' => $project,
        ]);
    }

    public function toggle(string $name): void
    {
        $this->requirePermission('projects');

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects');
            return;
        }

        try {
            $db = Database::getInstance();
            $row = $db->fetch('SELECT id, is_enabled FROM home_projects WHERE project_key = ?', [$name]);

            if (!$row) {
                $this->flash('error', 'Project not found in database.');
                $this->redirect('/admin/projects');
                return;
            }

            $newStatus = ((int)$row['is_enabled'] === 1) ? 0 : 1;
            $db->update('home_projects', ['is_enabled' => $newStatus], 'project_key = ?', [$name]);

            Logger::activity(Auth::id(), 'project_toggled', ['project' => $name, 'enabled' => $newStatus]);
            ActivityLogger::log(Auth::id(), 'project_toggled', [
                'module'        => 'admin',
                'resource_type' => 'project',
                'resource_id'   => $name,
                'entity_name'   => $name,
                'enabled'       => $newStatus,
            ]);

            $this->flash('success', 'Project status updated.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update project status.');
        }

        $this->redirect('/admin/projects');
    }

    public function settings(string $name): void
    {
        $this->requirePermission('projects');

        $projects = $this->getProjectsWithFallback();
        if (!isset($projects[$name])) {
            $this->flash('error', 'Project not found.');
            $this->redirect('/admin/projects');
            return;
        }

        $project = $projects[$name];

        $this->view('admin/projects/settings', [
            'title' => $project['name'] . ' Settings',
            'project' => $project,
        ]);
    }

    public function updateSettings(string $name): void
    {
        $this->requirePermission('projects');

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/' . $name . '/settings');
            return;
        }

        try {
            $db = Database::getInstance();
            $row = $db->fetch('SELECT id FROM home_projects WHERE project_key = ?', [$name]);

            if (!$row) {
                $this->flash('error', 'Project not found in database.');
                $this->redirect('/admin/projects');
                return;
            }

            $description = trim((string)$this->input('description', ''));
            $color = trim((string)$this->input('color', '#00f0ff'));
            $isEnabled = $this->input('enabled', '0') === '1' ? 1 : 0;

            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                $color = '#00f0ff';
            }

            $db->update('home_projects', [
                'description' => $description,
                'color' => $color,
                'is_enabled' => $isEnabled,
            ], 'project_key = ?', [$name]);

            Logger::activity(Auth::id(), 'project_settings_updated', ['project' => $name]);
            ActivityLogger::log(Auth::id(), 'project_settings_updated', [
                'module'        => 'admin',
                'resource_type' => 'project',
                'resource_id'   => $name,
                'entity_name'   => $name,
            ]);

            $this->flash('success', 'Project settings updated.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update project settings.');
        }

        $this->redirect('/admin/projects/' . $name);
    }

    private function getProjectsWithFallback(): array
    {
        $configProjects = require BASE_PATH . '/config/projects.php';
        $normalized = [];

        foreach ($configProjects as $key => $cfg) {
            $normalized[$key] = [
                'key' => $key,
                'name' => $cfg['name'] ?? ucfirst($key),
                'description' => $cfg['description'] ?? '',
                'icon' => $cfg['icon'] ?? 'box',
                'color' => $cfg['color'] ?? '#00f0ff',
                'enabled' => (bool)($cfg['enabled'] ?? true),
                'database' => $cfg['database'] ?? ($cfg['database_name'] ?? ''),
                'url' => $cfg['url'] ?? ('/projects/' . $key),
            ];
        }

        try {
            $db = Database::getInstance();

            $existingRows = $db->fetchAll('SELECT project_key FROM home_projects');
            $existingKeys = array_column($existingRows, 'project_key');
            $maxOrder = (int)($db->fetch('SELECT MAX(sort_order) m FROM home_projects')['m'] ?? 0);

            foreach ($normalized as $key => $project) {
                if (!in_array($key, $existingKeys, true)) {
                    $maxOrder++;
                    $db->insert('home_projects', [
                        'project_key' => $key,
                        'name' => $project['name'],
                        'description' => $project['description'],
                        'icon' => $project['icon'],
                        'color' => $project['color'],
                        'is_enabled' => $project['enabled'] ? 1 : 0,
                        'sort_order' => $maxOrder,
                        'database_name' => $project['database'],
                        'url' => $project['url'],
                    ]);
                }
            }

            $rows = $db->fetchAll('SELECT * FROM home_projects ORDER BY sort_order ASC, name ASC');
            foreach ($rows as $row) {
                $key = $row['project_key'];
                $normalized[$key] = [
                    'key' => $key,
                    'name' => $row['name'] ?? ($normalized[$key]['name'] ?? ucfirst($key)),
                    'description' => $row['description'] ?? ($normalized[$key]['description'] ?? ''),
                    'icon' => $row['icon'] ?? ($normalized[$key]['icon'] ?? 'box'),
                    'color' => $row['color'] ?? ($normalized[$key]['color'] ?? '#00f0ff'),
                    'enabled' => (bool)($row['is_enabled'] ?? 0),
                    'database' => $row['database_name'] ?? ($normalized[$key]['database'] ?? ''),
                    'url' => $row['url'] ?? ($normalized[$key]['url'] ?? ('/projects/' . $key)),
                ];
            }
        } catch (\Throwable $e) {
            // Fallback to config data only
        }

        return $normalized;
    }
}
