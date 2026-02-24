<?php
/**
 * ConvertX Admin Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Security;
use Core\Logger;

class ConvertXAdminController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------ //
    //  Overview                                                            //
    // ------------------------------------------------------------------ //

    public function overview(): void
    {
        $stats = $this->getStats();
        $recentJobs = $this->getRecentJobs(15);

        $this->view('admin/projects/convertx/overview', [
            'title'       => 'ConvertX Admin — Overview',
            'stats'       => $stats,
            'recentJobs'  => $recentJobs,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  All Jobs                                                            //
    // ------------------------------------------------------------------ //

    public function jobs(): void
    {
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;
        $status  = $_GET['status'] ?? '';
        $search  = trim($_GET['search'] ?? '');

        $where  = '1=1';
        $params = [];

        if ($status && in_array($status, ['pending','processing','completed','failed','cancelled'], true)) {
            $where    .= ' AND j.status = :status';
            $params['status'] = $status;
        }
        if ($search) {
            $where    .= ' AND (j.input_filename LIKE :search OR u.email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as cnt
               FROM convertx_jobs j
               LEFT JOIN users u ON u.id = j.user_id
              WHERE $where",
            $params
        )['cnt'] ?? 0;

        $jobs = $this->db->fetchAll(
            "SELECT j.*, u.name AS user_name, u.email AS user_email
               FROM convertx_jobs j
               LEFT JOIN users u ON u.id = j.user_id
              WHERE $where
              ORDER BY j.created_at DESC
              LIMIT $perPage OFFSET $offset",
            $params
        );

        $this->view('admin/projects/convertx/jobs', [
            'title'   => 'ConvertX Admin — All Jobs',
            'jobs'    => $jobs,
            'total'   => (int) $total,
            'page'    => $page,
            'perPage' => $perPage,
            'status'  => $status,
            'search'  => $search,
        ]);
    }

    public function cancelJob(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $id = (int) ($_POST['job_id'] ?? 0);
        if ($id) {
            $this->db->query(
                "UPDATE convertx_jobs SET status='cancelled' WHERE id=:id AND status IN ('pending','processing')",
                ['id' => $id]
            );
        }
        $this->redirect('/admin/projects/convertx/jobs');
    }

    public function deleteJob(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $id = (int) ($_POST['job_id'] ?? 0);
        if ($id) {
            $this->db->query("DELETE FROM convertx_jobs WHERE id=:id", ['id' => $id]);
        }
        $this->redirect('/admin/projects/convertx/jobs');
    }

    // ------------------------------------------------------------------ //
    //  Users                                                               //
    // ------------------------------------------------------------------ //

    public function users(): void
    {
        $users = $this->db->fetchAll(
            "SELECT u.id, u.name, u.email,
                    COUNT(j.id) AS total_jobs,
                    SUM(CASE WHEN j.status='completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN j.status='failed'    THEN 1 ELSE 0 END) AS failed,
                    MAX(j.created_at) AS last_job_at,
                    k.api_key, k.is_active AS key_active
               FROM users u
               LEFT JOIN convertx_jobs j ON j.user_id = u.id
               LEFT JOIN api_keys k ON k.user_id = u.id AND k.is_active = 1
              GROUP BY u.id, u.name, u.email, k.api_key, k.is_active
              ORDER BY total_jobs DESC"
        );

        $this->view('admin/projects/convertx/users', [
            'title' => 'ConvertX Admin — Users',
            'users' => $users,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  API Keys                                                            //
    // ------------------------------------------------------------------ //

    public function apiKeys(): void
    {
        $keys = $this->db->fetchAll(
            "SELECT k.*, u.name AS user_name, u.email AS user_email
               FROM api_keys k
               LEFT JOIN users u ON u.id = k.user_id
              ORDER BY k.created_at DESC"
        );

        $this->view('admin/projects/convertx/api-keys', [
            'title' => 'ConvertX Admin — API Keys',
            'keys'  => $keys,
        ]);
    }

    public function revokeApiKey(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $id = (int) ($_POST['key_id'] ?? 0);
        if ($id) {
            $this->db->query("UPDATE api_keys SET is_active=0 WHERE id=:id", ['id' => $id]);
        }
        $this->redirect('/admin/projects/convertx/api-keys');
    }

    // ------------------------------------------------------------------ //
    //  Settings                                                            //
    // ------------------------------------------------------------------ //

    public function settings(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings();
            return;
        }

        $providers = $this->db->fetchAll(
            "SELECT * FROM convertx_ai_providers ORDER BY priority ASC"
        );

        $this->view('admin/projects/convertx/settings', [
            'title'     => 'ConvertX Admin — Settings',
            'providers' => $providers,
        ]);
    }

    public function updateSettings(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }

        $id       = (int) ($_POST['provider_id'] ?? 0);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        if ($id) {
            $this->db->query(
                "UPDATE convertx_ai_providers SET is_active=:a WHERE id=:id",
                ['a' => $isActive, 'id' => $id]
            );
        }

        $_SESSION['_flash']['success'] = 'Settings updated.';
        $this->redirect('/admin/projects/convertx/settings');
    }

    // ------------------------------------------------------------------ //
    //  SQL Schema                                                          //
    // ------------------------------------------------------------------ //

    public function schema(): void
    {
        $schemaFile = BASE_PATH . '/projects/convertx/schema.sql';
        $schema = file_exists($schemaFile) ? file_get_contents($schemaFile) : '-- schema.sql not found';

        $this->view('admin/projects/convertx/schema', [
            'title'  => 'ConvertX Admin — SQL Schema',
            'schema' => $schema,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers                                                     //
    // ------------------------------------------------------------------ //

    private function getStats(): array
    {
        $stats = [
            'total_jobs'     => 0,
            'completed_jobs' => 0,
            'failed_jobs'    => 0,
            'pending_jobs'   => 0,
            'active_users'   => 0,
            'tokens_used'    => 0,
        ];

        try {
            $row = $this->db->fetch(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='completed'  THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status='failed'     THEN 1 ELSE 0 END) AS failed,
                    SUM(CASE WHEN status='pending'    THEN 1 ELSE 0 END) AS pending,
                    COUNT(DISTINCT user_id) AS active_users,
                    COALESCE(SUM(tokens_used), 0) AS tokens
                   FROM convertx_jobs"
            );
            if ($row) {
                $stats['total_jobs']     = (int) $row['total'];
                $stats['completed_jobs'] = (int) $row['completed'];
                $stats['failed_jobs']    = (int) $row['failed'];
                $stats['pending_jobs']   = (int) $row['pending'];
                $stats['active_users']   = (int) $row['active_users'];
                $stats['tokens_used']    = (int) $row['tokens'];
            }
        } catch (\Exception $e) {
            // table may not exist yet
        }

        return $stats;
    }

    private function getRecentJobs(int $limit): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT j.*, u.name AS user_name, u.email AS user_email
                   FROM convertx_jobs j
                   LEFT JOIN users u ON u.id = j.user_id
                  ORDER BY j.created_at DESC
                  LIMIT $limit"
            );
        } catch (\Exception $e) {
            return [];
        }
    }
}
