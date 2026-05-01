<?php
/**
 * Projects API Controller
 *
 * Endpoints:
 *   GET /api/projects        – list all enabled projects for the authenticated user
 *   GET /api/projects/{name} – get details of a specific project
 *
 * @package MMB\Controllers\Api
 */

namespace Controllers\Api;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

class ProjectController extends BaseController
{
    /**
     * GET /api/projects
     * Returns all enabled projects, merging DB rows with config.
     */
    public function list(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            $this->json(['error' => 'Unauthorized', 'message' => 'Authentication required.']);
            return;
        }

        $projects = $this->getEnabledProjects();

        $this->json([
            'success'  => true,
            'projects' => array_values($projects),
            'total'    => count($projects),
        ]);
    }

    /**
     * GET /api/projects/{name}
     * Returns details of a single project by key.
     */
    public function show(string $name): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            $this->json(['error' => 'Unauthorized', 'message' => 'Authentication required.']);
            return;
        }

        $name = strtolower(trim($name));
        $projects = $this->getEnabledProjects();

        if (!isset($projects[$name])) {
            http_response_code(404);
            $this->json(['error' => 'Not Found', 'message' => "Project '{$name}' not found or not enabled."]);
            return;
        }

        $this->json([
            'success' => true,
            'project' => $projects[$name],
        ]);
    }

    /**
     * Build the merged list of enabled projects (DB + config).
     */
    private function getEnabledProjects(): array
    {
        $projects = [];

        try {
            $db      = Database::getInstance();
            $dbRows  = $db->fetchAll("SELECT * FROM home_projects ORDER BY sort_order ASC");
            $dbKeys  = [];

            foreach ($dbRows as $row) {
                $dbKeys[] = $row['project_key'];
                if ((int) $row['is_enabled'] === 1) {
                    $projects[$row['project_key']] = [
                        'key'         => $row['project_key'],
                        'name'        => $row['name'],
                        'description' => $row['description'] ?? '',
                        'url'         => $row['url'] ?? '/projects/' . $row['project_key'],
                        'color'       => $row['color'] ?? '#00f0ff',
                        'icon'        => $row['icon'] ?? '',
                        'logo_url'    => $row['logo_url'] ?? '',
                        'sort_order'  => (int) $row['sort_order'],
                        'source'      => 'database',
                    ];
                }
            }

            // Merge config-only projects not yet in DB
            $configProjects = require BASE_PATH . '/config/projects.php';
            foreach ($configProjects as $key => $cfg) {
                if (!empty($cfg['enabled']) && !in_array($key, $dbKeys, true)) {
                    $projects[$key] = [
                        'key'         => $key,
                        'name'        => $cfg['name'] ?? $key,
                        'description' => $cfg['description'] ?? '',
                        'url'         => $cfg['url'] ?? '/projects/' . $key,
                        'color'       => $cfg['color'] ?? '#00f0ff',
                        'icon'        => $cfg['icon'] ?? '',
                        'logo_url'    => '',
                        'sort_order'  => 0,
                        'source'      => 'config',
                    ];
                }
            }
        } catch (\Exception $e) {
            // If DB unavailable, fall back to config only
            try {
                $configProjects = require BASE_PATH . '/config/projects.php';
                foreach ($configProjects as $key => $cfg) {
                    if (!empty($cfg['enabled'])) {
                        $projects[$key] = [
                            'key'         => $key,
                            'name'        => $cfg['name'] ?? $key,
                            'description' => $cfg['description'] ?? '',
                            'url'         => $cfg['url'] ?? '/projects/' . $key,
                            'color'       => $cfg['color'] ?? '#00f0ff',
                            'icon'        => $cfg['icon'] ?? '',
                            'logo_url'    => '',
                            'sort_order'  => 0,
                            'source'      => 'config',
                        ];
                    }
                }
            } catch (\Exception $e2) {
                // Nothing to return
            }
        }

        return $projects;
    }
}
