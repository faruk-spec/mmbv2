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
              WHERE k.api_key LIKE 'cx_%'
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
    //  Generate API Key for User                                          //
    // ------------------------------------------------------------------ //

    public function generateApiKeyForUser(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $userId = (int) ($_POST['user_id'] ?? 0);
        if (!$userId) {
            $_SESSION['_flash']['error'] = 'Invalid user ID.';
            $this->redirect('/admin/projects/convertx/api-keys');
            return;
        }
        // Check user exists
        $user = $this->db->fetch("SELECT id, name, email FROM users WHERE id=:id", ['id' => $userId]);
        if (!$user) {
            $_SESSION['_flash']['error'] = 'User not found.';
            $this->redirect('/admin/projects/convertx/api-keys');
            return;
        }
        $key = 'cx_' . bin2hex(random_bytes(20));
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS api_keys (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    api_key VARCHAR(64) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL,
                    INDEX idx_user (user_id),
                    UNIQUE KEY uniq_key (api_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $this->db->query(
                "UPDATE api_keys SET is_active=0 WHERE user_id=:uid AND api_key LIKE 'cx_%'",
                ['uid' => $userId]
            );
            $this->db->query(
                "INSERT INTO api_keys (user_id, api_key, is_active, created_at) VALUES (:uid, :key, 1, NOW())",
                ['uid' => $userId, 'key' => $key]
            );
            $_SESSION['_flash']['success'] = 'API key generated for ' . $user['name'] . ': ' . $key;
            $_SESSION['_flash']['new_key'] = $key;
            $_SESSION['_flash']['new_key_user'] = $user['name'];
        } catch (\Exception $e) {
            Logger::error('ConvertX Admin generateApiKeyForUser: ' . $e->getMessage());
            $_SESSION['_flash']['error'] = 'Failed to generate API key.';
        }
        $this->redirect('/admin/projects/convertx/api-keys');
    }

    // ------------------------------------------------------------------ //
    //  AI Provider CRUD                                                    //
    // ------------------------------------------------------------------ //

    public function createProvider(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        $name     = trim($_POST['name'] ?? '');
        $slug     = strtolower(trim(preg_replace('/[^a-z0-9_\-]/i', '-', $_POST['slug'] ?? '')));
        $baseUrl  = trim($_POST['base_url'] ?? '');
        $apiKey   = trim($_POST['api_key'] ?? '');
        $model    = trim($_POST['model'] ?? '');
        $priority = max(1, min(100, (int) ($_POST['priority'] ?? 10)));
        $cost     = max(0, (float) ($_POST['cost_per_1k_tokens'] ?? 0));
        $caps     = array_filter(array_map('trim', explode(',', $_POST['capabilities'] ?? '')));
        $tiers    = array_filter(array_map('trim', explode(',', $_POST['allowed_tiers'] ?? 'free,pro,enterprise')));

        if (!$name || !$slug) {
            $_SESSION['_flash']['error'] = 'Name and slug are required.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        try {
            $this->db->query(
                "INSERT INTO convertx_ai_providers
                    (name, slug, base_url, api_key, model, capabilities, allowed_tiers, priority, cost_per_1k_tokens, is_active, created_at)
                 VALUES (:name, :slug, :url, :key, :model, :caps, :tiers, :pri, :cost, 1, NOW())",
                [
                    'name'  => $name,
                    'slug'  => $slug,
                    'url'   => $baseUrl ?: null,
                    'key'   => $apiKey ?: null,
                    'model' => $model ?: null,
                    'caps'  => json_encode(array_values($caps)),
                    'tiers' => json_encode(array_values($tiers)),
                    'pri'   => $priority,
                    'cost'  => $cost,
                ]
            );
            $_SESSION['_flash']['success'] = 'Provider created.';
        } catch (\Exception $e) {
            Logger::error('ConvertX createProvider: ' . $e->getMessage());
            $_SESSION['_flash']['error'] = 'Failed to create provider: ' . $e->getMessage();
        }
        $this->redirect('/admin/projects/convertx/settings');
    }

    public function editProvider(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        $id       = (int) ($_POST['provider_id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $baseUrl  = trim($_POST['base_url'] ?? '');
        $apiKey   = trim($_POST['api_key'] ?? '');
        $model    = trim($_POST['model'] ?? '');
        $priority = max(1, min(100, (int) ($_POST['priority'] ?? 10)));
        $cost     = max(0, (float) ($_POST['cost_per_1k_tokens'] ?? 0));
        $caps     = array_filter(array_map('trim', explode(',', $_POST['capabilities'] ?? '')));
        $tiers    = array_filter(array_map('trim', explode(',', $_POST['allowed_tiers'] ?? 'free,pro,enterprise')));

        if (!$id || !$name) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        try {
            $this->db->query(
                "UPDATE convertx_ai_providers SET
                    name=:name, base_url=:url, api_key=:key, model=:model,
                    capabilities=:caps, allowed_tiers=:tiers, priority=:pri,
                    cost_per_1k_tokens=:cost
                 WHERE id=:id",
                [
                    'name'  => $name,
                    'url'   => $baseUrl ?: null,
                    'key'   => $apiKey ?: null,
                    'model' => $model ?: null,
                    'caps'  => json_encode(array_values($caps)),
                    'tiers' => json_encode(array_values($tiers)),
                    'pri'   => $priority,
                    'cost'  => $cost,
                    'id'    => $id,
                ]
            );
            $_SESSION['_flash']['success'] = 'Provider updated.';
        } catch (\Exception $e) {
            Logger::error('ConvertX editProvider: ' . $e->getMessage());
            $_SESSION['_flash']['error'] = 'Failed to update provider.';
        }
        $this->redirect('/admin/projects/convertx/settings');
    }

    public function deleteProvider(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        $id = (int) ($_POST['provider_id'] ?? 0);
        if ($id) {
            try {
                $this->db->query("DELETE FROM convertx_ai_providers WHERE id=:id", ['id' => $id]);
                $_SESSION['_flash']['success'] = 'Provider deleted.';
            } catch (\Exception $e) {
                $_SESSION['_flash']['error'] = 'Failed to delete provider.';
            }
        }
        $this->redirect('/admin/projects/convertx/settings');
    }

    // ------------------------------------------------------------------ //
    //  Storage Monitor                                                     //
    // ------------------------------------------------------------------ //

    public function storage(): void
    {
        $uploadDir = BASE_PATH . '/storage/uploads/convertx';
        $diskUsed  = 0;
        $fileCount = 0;
        if (is_dir($uploadDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadDir, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $diskUsed  += $file->getSize();
                    $fileCount++;
                }
            }
        }

        $outputDir  = BASE_PATH . '/storage/converted';
        $outputUsed  = 0;
        $outputCount = 0;
        if (is_dir($outputDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($outputDir, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $outputUsed  += $file->getSize();
                    $outputCount++;
                }
            }
        }

        $userStats = [];
        try {
            $userStats = $this->db->fetchAll(
                "SELECT u.id, u.name, u.email,
                        COUNT(j.id) AS total_jobs,
                        SUM(CASE WHEN j.status='completed' THEN 1 ELSE 0 END) AS completed
                   FROM users u
                   LEFT JOIN convertx_jobs j ON j.user_id = u.id
                  GROUP BY u.id
                 HAVING total_jobs > 0
                  ORDER BY total_jobs DESC
                  LIMIT 50"
            );
        } catch (\Exception $e) {}

        $this->view('admin/projects/convertx/storage', [
            'title'       => 'ConvertX — Storage Monitor',
            'diskUsed'    => $diskUsed,
            'fileCount'   => $fileCount,
            'outputUsed'  => $outputUsed,
            'outputCount' => $outputCount,
            'userStats'   => $userStats,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Subscription Plans CRUD                                            //
    // ------------------------------------------------------------------ //

    public function plans(): void
    {
        $this->ensurePlansTables();
        $plans = [];
        try {
            $plans = $this->db->fetchAll(
                "SELECT p.*, COUNT(s.id) AS subscriber_count
                   FROM convertx_subscription_plans p
                   LEFT JOIN convertx_user_subscriptions s ON s.plan_id=p.id AND s.status='active'
                  GROUP BY p.id
                  ORDER BY p.sort_order ASC, p.price ASC"
            );
        } catch (\Exception $e) {}

        $this->view('admin/projects/convertx/plans', [
            'title' => 'ConvertX — Subscription Plans',
            'plans' => $plans,
        ]);
    }

    public function createPlan(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        $name       = trim($_POST['name'] ?? '');
        $slug       = strtolower(trim(preg_replace('/[^a-z0-9_\-]/', '-', $_POST['slug'] ?? '')));
        $desc       = trim($_POST['description'] ?? '');
        $price      = max(0, (float) ($_POST['price'] ?? 0));
        $billing    = in_array($_POST['billing_cycle'] ?? '', ['monthly','yearly','lifetime']) ? $_POST['billing_cycle'] : 'monthly';
        $maxJobs    = (int) ($_POST['max_jobs_per_month'] ?? 50);
        $maxFile    = (int) ($_POST['max_file_size_mb'] ?? 10);
        $maxBatch   = (int) ($_POST['max_batch_size'] ?? 5);
        $aiAccess   = !empty($_POST['ai_access']) ? 1 : 0;
        $apiAccess  = !empty($_POST['api_access']) ? 1 : 0;
        $batchConv  = !empty($_POST['batch_convert']) ? 1 : 0;
        $priority   = !empty($_POST['priority_processing']) ? 1 : 0;
        $status     = $_POST['status'] ?? 'active';
        $sortOrder  = (int) ($_POST['sort_order'] ?? 0);

        if (!$name || !$slug) {
            $_SESSION['_flash']['error'] = 'Name and slug are required.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        try {
            $this->db->query(
                "INSERT INTO convertx_subscription_plans
                    (name,slug,description,price,billing_cycle,max_jobs_per_month,max_file_size_mb,max_batch_size,ai_access,api_access,batch_convert,priority_processing,status,sort_order)
                 VALUES (:name,:slug,:desc,:price,:billing,:jobs,:file,:batch,:ai,:api,:bconv,:prio,:status,:sort)",
                [
                    'name'=>$name,'slug'=>$slug,'desc'=>$desc,'price'=>$price,'billing'=>$billing,
                    'jobs'=>$maxJobs,'file'=>$maxFile,'batch'=>$maxBatch,'ai'=>$aiAccess,
                    'api'=>$apiAccess,'bconv'=>$batchConv,'prio'=>$priority,'status'=>$status,'sort'=>$sortOrder
                ]
            );
            $_SESSION['_flash']['success'] = 'Plan created.';
        } catch (\Exception $e) {
            Logger::error('ConvertX createPlan: ' . $e->getMessage());
            $_SESSION['_flash']['error'] = 'Failed to create plan: ' . $e->getMessage();
        }
        $this->redirect('/admin/projects/convertx/plans');
    }

    public function updatePlan(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        $id         = (int) ($_POST['plan_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $desc       = trim($_POST['description'] ?? '');
        $price      = max(0, (float) ($_POST['price'] ?? 0));
        $billing    = in_array($_POST['billing_cycle'] ?? '', ['monthly','yearly','lifetime']) ? $_POST['billing_cycle'] : 'monthly';
        $maxJobs    = (int) ($_POST['max_jobs_per_month'] ?? 50);
        $maxFile    = (int) ($_POST['max_file_size_mb'] ?? 10);
        $maxBatch   = (int) ($_POST['max_batch_size'] ?? 5);
        $aiAccess   = !empty($_POST['ai_access']) ? 1 : 0;
        $apiAccess  = !empty($_POST['api_access']) ? 1 : 0;
        $batchConv  = !empty($_POST['batch_convert']) ? 1 : 0;
        $priority   = !empty($_POST['priority_processing']) ? 1 : 0;
        $status     = $_POST['status'] ?? 'active';

        if (!$id || !$name) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        try {
            $this->db->query(
                "UPDATE convertx_subscription_plans SET
                    name=:name, description=:desc, price=:price, billing_cycle=:billing,
                    max_jobs_per_month=:jobs, max_file_size_mb=:file, max_batch_size=:batch,
                    ai_access=:ai, api_access=:api, batch_convert=:bconv, priority_processing=:prio, status=:status
                 WHERE id=:id",
                [
                    'name'=>$name,'desc'=>$desc,'price'=>$price,'billing'=>$billing,
                    'jobs'=>$maxJobs,'file'=>$maxFile,'batch'=>$maxBatch,'ai'=>$aiAccess,
                    'api'=>$apiAccess,'bconv'=>$batchConv,'prio'=>$priority,'status'=>$status,'id'=>$id
                ]
            );
            $_SESSION['_flash']['success'] = 'Plan updated.';
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to update plan.';
        }
        $this->redirect('/admin/projects/convertx/plans');
    }

    public function deletePlan(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        $id = (int) ($_POST['plan_id'] ?? 0);
        if ($id) {
            try {
                $this->db->query("DELETE FROM convertx_subscription_plans WHERE id=:id", ['id' => $id]);
                $_SESSION['_flash']['success'] = 'Plan deleted.';
            } catch (\Exception $e) {
                $_SESSION['_flash']['error'] = 'Failed to delete plan.';
            }
        }
        $this->redirect('/admin/projects/convertx/plans');
    }

    private function ensurePlansTables(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS convertx_subscription_plans (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(50) NOT NULL UNIQUE,
                description TEXT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                billing_cycle ENUM('monthly','yearly','lifetime') DEFAULT 'monthly',
                max_jobs_per_month INT NOT NULL DEFAULT 50,
                max_file_size_mb INT NOT NULL DEFAULT 10,
                max_batch_size INT NOT NULL DEFAULT 5,
                ai_access TINYINT(1) NOT NULL DEFAULT 0,
                api_access TINYINT(1) NOT NULL DEFAULT 0,
                batch_convert TINYINT(1) NOT NULL DEFAULT 1,
                priority_processing TINYINT(1) NOT NULL DEFAULT 0,
                status ENUM('active','inactive') DEFAULT 'active',
                sort_order INT DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->query("CREATE TABLE IF NOT EXISTS convertx_user_subscriptions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                plan_id INT UNSIGNED NOT NULL,
                status ENUM('active','cancelled','expired','trial') DEFAULT 'active',
                started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NULL,
                assigned_by INT UNSIGNED NULL,
                notes VARCHAR(500) NULL,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_plan_id (plan_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->query("INSERT IGNORE INTO convertx_subscription_plans
                (name,slug,description,price,billing_cycle,max_jobs_per_month,max_file_size_mb,max_batch_size,ai_access,api_access,batch_convert,priority_processing,status,sort_order)
                VALUES
                ('Free','free','Basic conversion, limited monthly jobs.',0.00,'monthly',50,10,5,0,0,1,0,'active',1),
                ('Pro','pro','Unlimited conversions with AI and API access.',9.99,'monthly',-1,100,50,1,1,1,1,'active',2),
                ('Enterprise','enterprise','Full access, custom limits, priority support.',29.99,'monthly',-1,500,100,1,1,1,1,'active',3)");
        } catch (\Exception $e) {}
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
