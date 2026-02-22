<?php
/**
 * Admin Platform Plans Controller
 *
 * CRUD for universal/platform plans that bundle multiple applications.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;

class PlatformPlansController extends BaseController
{
    private Database $db;

    // All apps the platform knows about
    private const APPS = [
        'qr'       => 'QR Generator',
        'whatsapp' => 'WhatsApp API',
        'proshare' => 'ProShare',
        'codexpro' => 'CodeXPro',
        'imgtxt'   => 'ImgTxt',
        'resumex'  => 'ResumeX',
        'devzone'  => 'DevZone',
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
        $plans = $this->getPlans();

        // Subscriber counts
        foreach ($plans as &$plan) {
            try {
                $plan['subscriber_count'] = (int) $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM platform_user_subscriptions WHERE plan_id = ? AND status = 'active'",
                    [$plan['id']]
                );
            } catch (\Exception $e) {
                $plan['subscriber_count'] = 0;
            }
            $plan['included_apps'] = json_decode($plan['included_apps'] ?? '[]', true) ?: [];
        }

        $this->view('admin/platform-plans/index', [
            'title'    => 'Platform Plans',
            'plans'    => $plans,
            'appNames' => self::APPS,
        ]);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function createForm(): void
    {
        $this->view('admin/platform-plans/form', [
            'title'  => 'Create Platform Plan',
            'plan'   => null,
            'apps'   => self::APPS,
            'action' => '/admin/platform-plans/create',
        ]);
    }

    public function create(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/platform-plans/create');
            return;
        }

        $data = $this->sanitizePlanInput();

        if (empty($data['name']) || empty($data['slug'])) {
            $this->flash('error', 'Plan name and slug are required.');
            $this->redirect('/admin/platform-plans/create');
            return;
        }

        try {
            $this->db->query(
                "INSERT INTO platform_plans
                    (name, slug, description, price, billing_cycle, color,
                     included_apps, app_features, status, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $data['name'], $data['slug'], $data['description'],
                    $data['price'], $data['billing_cycle'], $data['color'],
                    $data['included_apps'], $data['app_features'],
                    $data['status'], $data['sort_order'],
                ]
            );
            $planId = $this->db->lastInsertId();
            Logger::activity(Auth::id(), 'platform_plan_created', ['plan_id' => $planId, 'name' => $data['name']]);
            $this->flash('success', 'Plan "' . $data['name'] . '" created successfully.');
        } catch (\Exception $e) {
            Logger::error('PlatformPlansController::create — ' . $e->getMessage());
            $this->flash('error', 'Failed to create plan: ' . $e->getMessage());
        }

        $this->redirect('/admin/platform-plans');
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function editForm(int $id): void
    {
        $plan = $this->db->fetch("SELECT * FROM platform_plans WHERE id = ?", [$id]);
        if (!$plan) {
            $this->flash('error', 'Plan not found.');
            $this->redirect('/admin/platform-plans');
            return;
        }

        $plan['included_apps_arr'] = json_decode($plan['included_apps'] ?? '[]', true) ?: [];
        $plan['app_features_arr']  = json_decode($plan['app_features']  ?? '{}', true) ?: [];

        $this->view('admin/platform-plans/form', [
            'title'  => 'Edit Plan: ' . htmlspecialchars($plan['name']),
            'plan'   => $plan,
            'apps'   => self::APPS,
            'action' => '/admin/platform-plans/' . $id . '/update',
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect("/admin/platform-plans/{$id}/edit");
            return;
        }

        $plan = $this->db->fetch("SELECT id, name FROM platform_plans WHERE id = ?", [$id]);
        if (!$plan) {
            $this->flash('error', 'Plan not found.');
            $this->redirect('/admin/platform-plans');
            return;
        }

        $data = $this->sanitizePlanInput();

        try {
            $this->db->query(
                "UPDATE platform_plans SET
                    name = ?, slug = ?, description = ?, price = ?,
                    billing_cycle = ?, color = ?,
                    included_apps = ?, app_features = ?,
                    status = ?, sort_order = ?, updated_at = NOW()
                 WHERE id = ?",
                [
                    $data['name'], $data['slug'], $data['description'],
                    $data['price'], $data['billing_cycle'], $data['color'],
                    $data['included_apps'], $data['app_features'],
                    $data['status'], $data['sort_order'], $id,
                ]
            );
            Logger::activity(Auth::id(), 'platform_plan_updated', ['plan_id' => $id, 'name' => $data['name']]);
            $this->flash('success', 'Plan "' . $data['name'] . '" updated successfully.');
        } catch (\Exception $e) {
            Logger::error('PlatformPlansController::update — ' . $e->getMessage());
            $this->flash('error', 'Failed to update plan.');
        }

        $this->redirect('/admin/platform-plans');
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function delete(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/platform-plans');
            return;
        }

        $plan = $this->db->fetch("SELECT id, name FROM platform_plans WHERE id = ?", [$id]);
        if (!$plan) {
            $this->flash('error', 'Plan not found.');
            $this->redirect('/admin/platform-plans');
            return;
        }

        try {
            // Cancel all user subscriptions first
            $this->db->query(
                "UPDATE platform_user_subscriptions SET status = 'cancelled', updated_at = NOW() WHERE plan_id = ?",
                [$id]
            );
            $this->db->query("DELETE FROM platform_plans WHERE id = ?", [$id]);
            Logger::activity(Auth::id(), 'platform_plan_deleted', ['plan_id' => $id, 'name' => $plan['name']]);
            $this->flash('success', 'Plan "' . $plan['name'] . '" deleted.');
        } catch (\Exception $e) {
            Logger::error('PlatformPlansController::delete — ' . $e->getMessage());
            $this->flash('error', 'Failed to delete plan.');
        }

        $this->redirect('/admin/platform-plans');
    }

    // -------------------------------------------------------------------------
    // Assign plan to user
    // -------------------------------------------------------------------------

    public function assignUser(): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request token.');
            return;
        }

        $userId = (int) $this->input('user_id');
        $planId = (int) $this->input('plan_id');
        $notes  = Security::sanitize($this->input('notes', ''));

        if (!$userId || !$planId) {
            $this->jsonError('User ID and Plan ID are required.');
            return;
        }

        try {
            // Cancel any existing active subscription for this user+plan
            $this->db->query(
                "UPDATE platform_user_subscriptions
                 SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
                 WHERE user_id = ? AND plan_id = ? AND status = 'active'",
                [$userId, $planId]
            );

            $this->db->query(
                "INSERT INTO platform_user_subscriptions
                    (user_id, plan_id, status, started_at, assigned_by, notes)
                 VALUES (?, ?, 'active', NOW(), ?, ?)",
                [$userId, $planId, Auth::id(), $notes]
            );

            $plan = $this->db->fetch("SELECT name FROM platform_plans WHERE id = ?", [$planId]);
            Logger::activity(Auth::id(), 'platform_plan_assigned', [
                'plan_id' => $planId,
                'plan_name' => $plan['name'] ?? '',
                'target_user_id' => $userId,
            ]);

            $this->json(['success' => true, 'message' => 'Plan assigned successfully.']);
        } catch (\Exception $e) {
            Logger::error('PlatformPlansController::assignUser — ' . $e->getMessage());
            $this->jsonError('Failed to assign plan.');
        }
    }

    // -------------------------------------------------------------------------
    // Revoke plan from user
    // -------------------------------------------------------------------------

    public function revokeUser(): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request token.');
            return;
        }

        $subId = (int) $this->input('subscription_id');
        if (!$subId) {
            $this->jsonError('Subscription ID required.');
            return;
        }

        try {
            $sub = $this->db->fetch("SELECT * FROM platform_user_subscriptions WHERE id = ?", [$subId]);
            if (!$sub) {
                $this->jsonError('Subscription not found.');
                return;
            }

            $this->db->query(
                "UPDATE platform_user_subscriptions
                 SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
                 WHERE id = ?",
                [$subId]
            );

            Logger::activity(Auth::id(), 'platform_plan_revoked', [
                'subscription_id' => $subId,
                'user_id' => $sub['user_id'],
                'plan_id' => $sub['plan_id'],
            ]);

            $this->json(['success' => true, 'message' => 'Plan revoked.']);
        } catch (\Exception $e) {
            Logger::error('PlatformPlansController::revokeUser — ' . $e->getMessage());
            $this->jsonError('Failed to revoke plan.');
        }
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function getPlans(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM platform_plans ORDER BY sort_order ASC, price ASC"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Sanitise and build plan data array from POST */
    private function sanitizePlanInput(): array
    {
        $name        = Security::sanitize($this->input('name', ''));
        $rawSlug     = Security::sanitize($this->input('slug', ''));
        $slug        = strtolower(preg_replace('/[^a-z0-9-]/', '-', $rawSlug));
        $description = Security::sanitize($this->input('description', ''));
        $price       = max(0, (float) $this->input('price', 0));
        $billing     = in_array($this->input('billing_cycle'), ['monthly', 'yearly', 'lifetime'])
                        ? $this->input('billing_cycle')
                        : 'monthly';
        $color       = preg_match('/^#[0-9a-fA-F]{6}$/', $this->input('color', '#9945ff'))
                        ? $this->input('color')
                        : '#9945ff';
        $status      = $this->input('status') === 'inactive' ? 'inactive' : 'active';
        $sortOrder   = max(0, (int) $this->input('sort_order', 0));

        // Included apps — array of known keys
        $includedApps = array_values(array_intersect(
            (array) ($_POST['included_apps'] ?? []),
            array_keys(self::APPS)
        ));

        // App features — built from structured app_feat inputs + optional raw JSON override
        $appFeatures = [];

        // 1. Read structured per-app feature inputs (app_feat[appKey][featureKey])
        $structuredInput = $_POST['app_feat'] ?? [];
        if (is_array($structuredInput)) {
            foreach ($structuredInput as $appKey => $featureMap) {
                if (!is_array($featureMap)) {
                    continue;
                }
                $appKey = $this->sanitizeKey($appKey);
                if (empty($appKey)) {
                    continue;
                }
                $appData = [];
                foreach ($featureMap as $fk => $fv) {
                    $fk = $this->sanitizeKey($fk);
                    if (empty($fk)) {
                        continue;
                    }
                    // Numeric fields: empty string = not set, otherwise cast to int
                    if (is_numeric($fv) || $fv === '' || $fv === '-1') {
                        if ($fv !== '') {
                            $appData[$fk] = (int)$fv;
                        }
                    } else {
                        // Checkbox value="1" → true; absent = false (but we only have present ones)
                        $appData[$fk] = (bool)$fv;
                    }
                }
                // Merge: for boolean keys not posted (unchecked checkboxes), set false explicitly
                // We detect boolean keys by whether the existing saved value was bool
                if (!empty($appData)) {
                    $appFeatures[$appKey] = $appData;
                }
            }
        }

        // 2. Merge raw JSON override for other/unlisted apps
        $rawJson = trim($this->input('app_features_raw', ''));
        if ($rawJson !== '' && $rawJson !== '{}') {
            $decoded = json_decode($rawJson, true);
            if (is_array($decoded)) {
                foreach ($decoded as $appKey => $featureMap) {
                    if (!isset($appFeatures[$appKey])) {
                        $appFeatures[$appKey] = $featureMap;
                    } else {
                        $appFeatures[$appKey] = array_merge($featureMap, $appFeatures[$appKey]);
                    }
                }
            }
        }

        return [
            'name'          => $name,
            'slug'          => $slug,
            'description'   => $description,
            'price'         => $price,
            'billing_cycle' => $billing,
            'color'         => $color,
            'included_apps' => json_encode($includedApps),
            'app_features'  => json_encode($appFeatures),
            'status'        => $status,
            'sort_order'    => $sortOrder,
        ];
    }

    private function ensureTables(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `platform_plans` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `slug` VARCHAR(100) UNIQUE NOT NULL,
                    `description` TEXT NULL,
                    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `billing_cycle` ENUM('monthly','yearly','lifetime') DEFAULT 'monthly',
                    `color` VARCHAR(7) DEFAULT '#9945ff',
                    `included_apps` JSON NULL,
                    `app_features` JSON NULL,
                    `status` ENUM('active','inactive') DEFAULT 'active',
                    `sort_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `platform_user_subscriptions` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT UNSIGNED NOT NULL,
                    `plan_id` INT UNSIGNED NOT NULL,
                    `status` ENUM('active','cancelled','expired','trial') DEFAULT 'active',
                    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `expires_at` TIMESTAMP NULL,
                    `cancelled_at` TIMESTAMP NULL,
                    `assigned_by` INT UNSIGNED NULL,
                    `notes` VARCHAR(500) NULL,
                    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`plan_id`) REFERENCES `platform_plans`(`id`) ON DELETE CASCADE,
                    INDEX `idx_user_id` (`user_id`),
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            Logger::error('PlatformPlansController::ensureTables — ' . $e->getMessage());
        }
    }

    // ── JSON helpers ─────────────────────────────────────────────────────────

    protected function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function jsonError(string $message, int $code = 400): void
    {
        $this->json(['success' => false, 'error' => $message], $code);
    }

    /** Sanitise a key to lowercase alphanumeric + underscore only */
    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-z0-9_]/', '', strtolower($key));
    }
}
