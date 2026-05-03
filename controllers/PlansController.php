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
use Core\Notification;

class PlansController extends BaseController
{
    private Database $db;

    // Registered apps with display metadata
    private const APP_META = [
        'qr'        => ['name' => 'QR Generator',   'color' => '#9945ff', 'icon' => 'qr_code',      'url' => '/projects/qr'],
        'whatsapp'  => ['name' => 'WhatsApp API',    'color' => '#25D366', 'icon' => 'chat',         'url' => '/projects/whatsapp'],
        'proshare'  => ['name' => 'ProShare',        'color' => '#ffaa00', 'icon' => 'share',        'url' => '/projects/proshare'],
        'codexpro'  => ['name' => 'CodeXPro',        'color' => '#00f0ff', 'icon' => 'code',         'url' => '/projects/codexpro'],
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
    // Subscription Request
    // -------------------------------------------------------------------------

    /** Show subscription confirmation/request form */
    public function subscribe(string $slug): void
    {
        $userId = Auth::id();
        $plan   = $this->findPlanBySlug($slug);

        if (!$plan) {
            $this->flash('error', 'Plan not found.');
            $this->redirect('/plans');
            return;
        }

        $plan['included_apps'] = json_decode($plan['included_apps'] ?? '[]', true) ?: [];
        $plan['app_features']  = json_decode($plan['app_features']  ?? '{}', true) ?: [];

        // Check if user already has this plan
        $existing = null;
        try {
            $existing = $this->db->fetch(
                "SELECT * FROM platform_user_subscriptions WHERE user_id = ? AND plan_id = ? AND status = 'active'",
                [$userId, $plan['id']]
            );
        } catch (\Exception $e) {}

        Logger::activity($userId, 'subscription_page_viewed', ['plan_slug' => $slug]);

        $this->view('dashboard/plans-subscribe', [
            'title'    => 'Subscribe to Plan',
            'plan'     => $plan,
            'appMeta'  => self::APP_META,
            'existing' => $existing,
        ]);
    }

    /** Process subscription request — stores pending request and notifies admin */
    public function processSubscribe(string $slug): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/plans/subscribe/' . urlencode($slug));
            return;
        }

        $userId  = Auth::id();
        $plan    = $this->findPlanBySlug($slug);

        if (!$plan) {
            $this->flash('error', 'Plan not found.');
            $this->redirect('/plans');
            return;
        }

        $message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

        // Log the request so admin can see it in activity logs
        Logger::activity($userId, 'subscription_requested', [
            'plan_id'   => $plan['id'],
            'plan_name' => $plan['name'],
            'plan_slug' => $plan['slug'],
            'message'   => $message,
        ]);

        // If plan is free (price=0), auto-assign immediately
        if ((float)$plan['price'] === 0.0) {
            try {
                $this->ensurePlatformTables();
                // Cancel any existing
                $this->db->query(
                    "UPDATE platform_user_subscriptions SET status='cancelled', cancelled_at=NOW() WHERE user_id=? AND plan_id=? AND status='active'",
                    [$userId, $plan['id']]
                );
                $this->db->query(
                    "INSERT INTO platform_user_subscriptions (user_id, plan_id, status, started_at) VALUES (?,?,'active',NOW())",
                    [$userId, $plan['id']]
                );
                Logger::activity($userId, 'subscription_auto_activated', ['plan_id' => $plan['id']]);
                try { Notification::send($userId, 'plan_subscribed', 'You have been subscribed to the "' . $plan['name'] . '" plan.', ['plan_id' => $plan['id'], 'plan_name' => $plan['name']]); } catch (\Exception $e) {}
                $this->flash('success', 'You have been subscribed to "' . $plan['name'] . '" successfully!');
            } catch (\Exception $e) {
                Logger::error('PlansController::processSubscribe auto-assign — ' . $e->getMessage());
                $this->flash('error', 'Failed to activate plan. Please contact support.');
            }
            $this->redirect('/plans');
            return;
        }

        // Paid plan — request stored in logs, redirect back with confirmation
        try { Notification::send($userId, 'plan_request_submitted', 'Your subscription request for the "' . $plan['name'] . '" plan has been submitted. An admin will activate it shortly.', ['plan_id' => $plan['id'], 'plan_name' => $plan['name']]); } catch (\Exception $e) {}
        $this->flash('success', 'Your subscription request for "' . $plan['name'] . '" has been submitted. An admin will activate it for you shortly.');
        $this->redirect('/plans');
    }

    // -------------------------------------------------------------------------

    /** Find a platform plan by slug */
    private function findPlanBySlug(string $slug): ?array
    {
        try {
            $this->ensurePlatformTables();
            $plan = $this->db->fetch(
                "SELECT * FROM platform_plans WHERE slug = ? AND status = 'active' LIMIT 1",
                [$slug]
            );
            return $plan ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }



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
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.price, p.currency, p.billing_cycle,
                        p.max_static_qr, p.max_dynamic_qr, p.max_scans_per_month, p.features,
                        s.expires_at
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

        // ResumeX
        try {
            $row = $this->db->fetch(
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.price, p.currency, p.billing_cycle,
                        p.max_resumes, p.features, s.expires_at
                 FROM resumex_user_subscriptions s
                 JOIN resumex_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC LIMIT 1",
                [$userId]
            );
            if ($row) {
                $row['app_key']  = 'resumex';
                $row['features'] = $this->decodeFeatures($row['features'] ?? '');
                $subs['resumex'] = $row;
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
        // Ensure currency column on platform_plans
        try {
            $cols = array_column($this->db->fetchAll("SHOW COLUMNS FROM platform_plans"), 'Field');
            if (!in_array('currency', $cols, true)) {
                $this->db->query("ALTER TABLE platform_plans ADD COLUMN `currency` VARCHAR(3) NOT NULL DEFAULT 'USD' AFTER `price`");
            }
        } catch (\Exception $e) {}
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

    /** Show/download invoice for a platform subscription */
    public function invoice(string $id): void
    {
        $userId = Auth::id();
        $subId  = (int) $id;

        try {
            $sub = $this->db->fetch(
                "SELECT s.*, p.name plan_name, p.price, p.currency, p.billing_cycle, p.description
                 FROM platform_user_subscriptions s
                 JOIN platform_plans p ON p.id = s.plan_id
                 WHERE s.id = ? AND s.user_id = ?",
                [$subId, $userId]
            );
        } catch (\Exception $e) {
            $sub = null;
        }

        if (!$sub) {
            $this->flash('error', 'Invoice not found.');
            $this->redirect('/plans');
            return;
        }

        $user = Auth::user();

        // Generate simple HTML invoice
        $invoiceHtml = $this->renderInvoiceHtml($sub, $user);
        header('Content-Type: text/html; charset=utf-8');
        echo $invoiceHtml;
        exit;
    }

    private function renderInvoiceHtml(array $sub, array $user): string
    {
        $cur      = htmlspecialchars($sub['currency'] ?? 'USD');
        $price    = number_format((float)$sub['price'], 2);
        $isFree   = (float)$sub['price'] === 0.0;
        $invoiceNo = 'INV-' . strtoupper(substr(md5($sub['id'] . $sub['started_at']), 0, 8));
        $date     = date('F j, Y', strtotime($sub['started_at']));
        $expiry   = $sub['expires_at'] ? date('F j, Y', strtotime($sub['expires_at'])) : 'Lifetime';
        $userName = htmlspecialchars($user['name'] ?? $user['username'] ?? 'User');
        $userEmail= htmlspecialchars($user['email'] ?? '');
        $planName = htmlspecialchars($sub['plan_name']);
        $billing  = htmlspecialchars($sub['billing_cycle']);
        $status   = htmlspecialchars($sub['status']);
        $noPayment = $isFree ? 'This is a complimentary plan — no payment required.' : 'Payment is processed by your administrator.';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {$invoiceNo}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1a1a2e; padding: 40px; max-width: 700px; margin: 0 auto; }
.invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #0077cc; padding-bottom: 24px; }
.brand { font-size: 1.5rem; font-weight: 800; color: #0077cc; }
.inv-meta { text-align: right; }
.inv-meta h2 { font-size: 1.1rem; color: #0077cc; }
.inv-meta p { font-size: .85rem; color: #666; margin-top: 4px; }
.section { margin-bottom: 28px; }
.section-title { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #888; margin-bottom: 8px; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.info-block p { font-size: .9rem; line-height: 1.6; }
.info-block strong { display: block; font-size: .78rem; color: #888; margin-bottom: 2px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th { background: #f0f4ff; padding: 10px 14px; text-align: left; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #444; }
td { padding: 12px 14px; border-bottom: 1px solid #e8edf5; font-size: .9rem; }
.total-row td { font-weight: 700; font-size: 1rem; background: #f8faff; }
.status-badge { background: #e6ffed; color: #1a7a3e; padding: 3px 12px; border-radius: 20px; font-size: .78rem; font-weight: 700; }
.footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e8edf5; font-size: .78rem; color: #999; text-align: center; }
@media print { body { padding: 20px; } }
</style>
</head>
<body>
<div class="invoice-header">
    <div>
        <div class="brand">MMB Platform</div>
        <div style="font-size:.82rem;color:#666;margin-top:4px;">Subscription Invoice</div>
    </div>
    <div class="inv-meta">
        <h2>{$invoiceNo}</h2>
        <p>Issued: {$date}</p>
    </div>
</div>

<div class="section info-grid">
    <div class="info-block">
        <div class="section-title">Bill To</div>
        <p><strong>Name</strong>{$userName}</p>
        <p><strong>Email</strong>{$userEmail}</p>
    </div>
    <div class="info-block">
        <div class="section-title">Subscription Details</div>
        <p><strong>Plan</strong>{$planName}</p>
        <p><strong>Started</strong>{$date}</p>
        <p><strong>Expires</strong>{$expiry}</p>
        <p><strong>Status</strong><span class="status-badge">{$status}</span></p>
    </div>
</div>

<div class="section">
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Billing</th>
                <th style="text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$planName} Subscription</td>
                <td>{$billing}</td>
                <td style="text-align:right;">{$cur} {$price}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2">Total</td>
                <td style="text-align:right;">{$cur} {$price}</td>
            </tr>
        </tfoot>
    </table>
    <p style="font-size:.8rem;color:#666;">{$noPayment}</p>
</div>

<div class="footer">
    Thank you for using MMB Platform &bull; This is a computer-generated invoice &bull; No signature required<br>
    <a href="javascript:window.print()" style="color:#0077cc;text-decoration:none;">🖨 Print / Save as PDF</a>
</div>
</body>
</html>
HTML;
    }
}
