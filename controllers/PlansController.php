<?php
/**
 * Plans Controller (User-facing)
 *
 * Shows all per-app subscriptions and universal platform plans.
 * Handles upgrade requests (contact admin / CTA).
 *
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;
use Core\Helpers;

class PlansController extends BaseController
{
    private Database $db;

    // Registered apps with display metadata
    private const APP_META = [
        'qr'        => ['name' => 'QR Generator',   'color' => '#9945ff', 'icon' => 'qr_code',      'url' => '/projects/qr'],
        'whatsapp'  => ['name' => 'WhatsApp API',    'color' => '#25D366', 'icon' => 'chat',         'url' => '/projects/whatsapp'],
        'proshare'  => ['name' => 'ProShare',        'color' => '#ffaa00', 'icon' => 'share',        'url' => '/projects/proshare'],
        'codexpro'  => ['name' => 'CodeXPro',        'color' => '#00f0ff', 'icon' => 'code',         'url' => '/projects/codexpro'],
        'imgtxt'    => ['name' => 'ImgTxt',          'color' => '#00ff88', 'icon' => 'image',        'url' => '/projects/imgtxt'],
        'resumex'   => ['name' => 'ResumeX',         'color' => '#ff6b6b', 'icon' => 'description', 'url' => '/projects/resumex'],
        'devzone'   => ['name' => 'DevZone',         'color' => '#ff2ec4', 'icon' => 'groups',      'url' => '/projects/devzone'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------------------------------------------------------
    // User Plan Page
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $userId = Auth::id();

        // ── Per-app active subscriptions ──────────────────────────────────────
        $appSubscriptions = $this->getAppSubscriptions($userId);

        // ── Platform (universal) plans — all active ones ──────────────────────
        $platformPlans = $this->getPlatformPlans();

        // ── User's active platform subscription(s) ────────────────────────────
        $userPlatformSubs = $this->getUserPlatformSubscriptions($userId);

        // Map plan_id → subscription so the view can check if a plan is active
        $activePlatformPlanIds = array_column($userPlatformSubs, 'plan_id');

        Logger::activity($userId, 'plans_viewed');

        // Contact email for upgrade CTAs — read from settings with fallback
        $contactEmail = 'support@mmbtech.online';
        try {
            $row = $this->db->fetch(
                "SELECT value FROM settings WHERE `key` = 'maintenance_contact_email' LIMIT 1"
            );
            if ($row && !empty($row['value'])) {
                $contactEmail = $row['value'];
            }
        } catch (\Exception $e) {
            // Use fallback
        }

        $this->view('dashboard/plans', [
            'title'                 => 'My Plans',
            'appSubscriptions'      => $appSubscriptions,
            'platformPlans'         => $platformPlans,
            'userPlatformSubs'      => $userPlatformSubs,
            'activePlatformPlanIds' => $activePlatformPlanIds,
            'appMeta'               => self::APP_META,
            'contactEmail'          => $contactEmail,
        ]);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Returns per-app subscriptions for the user.
     * Currently reads from qr_user_subscriptions; extensible for other apps.
     */
    private function getAppSubscriptions(int $userId): array
    {
        $subs = [];

        // QR Generator
        try {
            $row = $this->db->fetch(
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.price, p.billing_cycle,
                        p.max_static_qr, p.max_dynamic_qr, p.max_scans_per_month, p.features
                 FROM qr_user_subscriptions s
                 JOIN qr_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC LIMIT 1",
                [$userId]
            );
            if ($row) {
                $row['app_key']  = 'qr';
                $row['features'] = $this->decodeFeatures($row['features'] ?? '');
                $subs['qr']      = $row;
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // WhatsApp
        try {
            $row = $this->db->fetch(
                "SELECT s.*, p.name plan_name, p.plan_type plan_slug, p.price, p.billing_cycle,
                        p.max_sessions, p.max_messages_per_day
                 FROM whatsapp_subscriptions s
                 JOIN whatsapp_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.created_at DESC LIMIT 1",
                [$userId]
            );
            if ($row) {
                $row['app_key']  = 'whatsapp';
                $row['features'] = [];
                $subs['whatsapp'] = $row;
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        return $subs;
    }

    /** Returns all active platform plans ordered by sort_order */
    private function getPlatformPlans(): array
    {
        try {
            $this->ensurePlatformTables();
            $plans = $this->db->fetchAll(
                "SELECT * FROM platform_plans WHERE status = 'active' ORDER BY sort_order ASC, price ASC"
            );
            foreach ($plans as &$plan) {
                $plan['included_apps'] = json_decode($plan['included_apps'] ?? '[]', true) ?: [];
                $plan['app_features']  = json_decode($plan['app_features']  ?? '{}', true) ?: [];
            }
            return $plans;
        } catch (\Exception $e) {
            Logger::error('PlansController::getPlatformPlans — ' . $e->getMessage());
            return [];
        }
    }

    /** Returns all active platform subscriptions for this user */
    private function getUserPlatformSubscriptions(int $userId): array
    {
        try {
            $this->ensurePlatformTables();
            return $this->db->fetchAll(
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.color, p.price, p.billing_cycle, p.included_apps
                 FROM platform_user_subscriptions s
                 JOIN platform_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC",
                [$userId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Ensure platform tables exist (idempotent) */
    private function ensurePlatformTables(): void
    {
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
    }

    private function decodeFeatures(?string $json): array
    {
        if (empty($json)) {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
