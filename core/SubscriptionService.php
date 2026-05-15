<?php

namespace Core;

class SubscriptionService
{
    private Database $db;
    private const APP_CONFIG = [
        'platform' => [
            'label' => 'MMB Platform',
            'plan_table' => 'platform_plans',
            'plan_id_column' => 'id',
            'plan_slug_column' => 'slug',
            'plan_name_column' => 'name',
            'plan_status_column' => 'status',
            'plan_active_value' => 'active',
            'plan_currency_column' => 'currency',
            'plan_price_column' => 'price',
            'plan_billing_column' => 'billing_cycle',
            'plan_cancel_days_column' => 'cancel_days',
            'plan_refund_days_column' => 'refund_days',
            'subscription_table' => 'platform_user_subscriptions',
            'subscription_user_column' => 'user_id',
            'subscription_plan_column' => 'plan_id',
            'subscription_status_column' => 'status',
            'subscription_active_value' => 'active',
            'subscription_started_column' => 'started_at',
            'subscription_expires_column' => 'expires_at',
            'subscription_cancelled_column' => 'cancelled_at',
        ],
        'resumex' => [
            'label' => 'ResumeX',
            'plan_table' => 'resumex_subscription_plans',
            'plan_id_column' => 'id',
            'plan_slug_column' => 'slug',
            'plan_name_column' => 'name',
            'plan_status_column' => 'status',
            'plan_active_value' => 'active',
            'plan_currency_column' => 'currency',
            'plan_price_column' => 'price',
            'plan_billing_column' => 'billing_cycle',
            'plan_cancel_days_column' => 'cancel_days',
            'plan_refund_days_column' => 'refund_days',
            'subscription_table' => 'resumex_user_subscriptions',
            'subscription_user_column' => 'user_id',
            'subscription_plan_column' => 'plan_id',
            'subscription_status_column' => 'status',
            'subscription_active_value' => 'active',
            'subscription_started_column' => 'started_at',
            'subscription_expires_column' => 'expires_at',
        ],
        'qr' => [
            'label' => 'QR Generator',
            'plan_table' => 'qr_subscription_plans',
            'plan_id_column' => 'id',
            'plan_slug_column' => 'slug',
            'plan_name_column' => 'name',
            'plan_status_column' => 'status',
            'plan_active_value' => 'active',
            'plan_currency_column' => 'currency',
            'plan_price_column' => 'price',
            'plan_billing_column' => 'billing_cycle',
            'plan_cancel_days_column' => 'cancel_days',
            'plan_refund_days_column' => 'refund_days',
            'subscription_table' => 'qr_user_subscriptions',
            'subscription_user_column' => 'user_id',
            'subscription_plan_column' => 'plan_id',
            'subscription_status_column' => 'status',
            'subscription_active_value' => 'active',
            'subscription_started_column' => 'started_at',
            'subscription_expires_column' => 'expires_at',
        ],
        'convertx' => [
            'label' => 'ConvertX',
            'plan_table' => 'convertx_subscription_plans',
            'plan_id_column' => 'id',
            'plan_slug_column' => 'slug',
            'plan_name_column' => 'name',
            'plan_status_column' => 'status',
            'plan_active_value' => 'active',
            'plan_currency_column' => 'currency',
            'plan_price_column' => 'price',
            'plan_billing_column' => 'billing_cycle',
            'plan_cancel_days_column' => 'cancel_days',
            'plan_refund_days_column' => 'refund_days',
            'subscription_table' => 'convertx_user_subscriptions',
            'subscription_user_column' => 'user_id',
            'subscription_plan_column' => 'plan_id',
            'subscription_status_column' => 'status',
            'subscription_active_value' => 'active',
            'subscription_started_column' => 'started_at',
            'subscription_expires_column' => 'expires_at',
        ],
        'whatsapp' => [
            'label' => 'WhatsApp API',
            'plan_table' => 'whatsapp_subscription_plans',
            'plan_id_column' => 'id',
            'plan_slug_column' => 'slug',
            'plan_name_column' => 'name',
            'plan_status_column' => 'is_active',
            'plan_active_value' => 1,
            'plan_currency_column' => 'currency',
            'plan_price_column' => 'price',
            'plan_billing_column' => 'duration_days',
            'plan_cancel_days_column' => 'cancel_days',
            'plan_refund_days_column' => 'refund_days',
            'subscription_table' => 'whatsapp_subscriptions',
            'subscription_user_column' => 'user_id',
            'subscription_plan_column' => 'plan_id',
            'subscription_status_column' => 'status',
            'subscription_active_value' => 'active',
            'subscription_started_column' => 'start_date',
            'subscription_expires_column' => 'end_date',
        ],
    ];

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function ensureInfrastructure(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `subscription_payments` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `app_key` VARCHAR(32) NOT NULL,
                `plan_id` INT UNSIGNED NOT NULL,
                `subscription_id` INT UNSIGNED NULL,
                `plan_name` VARCHAR(120) NOT NULL,
                `billing_cycle` VARCHAR(20) NULL,
                `gateway` VARCHAR(20) NOT NULL DEFAULT 'manual',
                `status` ENUM('pending','verification_pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
                `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
                `invoice_no` VARCHAR(40) NOT NULL,
                `reference` VARCHAR(80) NOT NULL,
                `provider_order_id` VARCHAR(120) NULL,
                `provider_payment_session_id` VARCHAR(255) NULL,
                `payment_url` TEXT NULL,
                `payment_payload` TEXT NULL,
                `metadata_json` LONGTEXT NULL,
                `admin_notes` TEXT NULL,
                `refund_status` ENUM('none','requested','approved','rejected','refunded') NOT NULL DEFAULT 'none',
                `refund_requested_at` TIMESTAMP NULL,
                `refund_decided_at` TIMESTAMP NULL,
                `cancel_requested_at` TIMESTAMP NULL,
                `paid_at` TIMESTAMP NULL,
                `expires_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_invoice_no` (`invoice_no`),
                UNIQUE KEY `uniq_reference` (`reference`),
                KEY `idx_user_app` (`user_id`, `app_key`),
                KEY `idx_status` (`status`),
                KEY `idx_provider_order_id` (`provider_order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        try {
            $cols = array_column($this->db->fetchAll("SHOW COLUMNS FROM platform_plans"), 'Field');
            if (!in_array('currency', $cols, true)) {
                $this->db->query("ALTER TABLE platform_plans ADD COLUMN `currency` VARCHAR(3) NOT NULL DEFAULT 'USD' AFTER `price`");
            }
        } catch (\Throwable $e) {
        }

        try {
            $cols = array_column($this->db->fetchAll("SHOW COLUMNS FROM subscription_payments"), 'Field');
            if (!in_array('refund_status', $cols, true)) {
                $this->db->query("ALTER TABLE subscription_payments ADD COLUMN `refund_status` ENUM('none','requested','approved','rejected','refunded') NOT NULL DEFAULT 'none' AFTER `admin_notes`");
            }
            if (!in_array('refund_requested_at', $cols, true)) {
                $this->db->query("ALTER TABLE subscription_payments ADD COLUMN `refund_requested_at` TIMESTAMP NULL AFTER `refund_status`");
            }
            if (!in_array('refund_decided_at', $cols, true)) {
                $this->db->query("ALTER TABLE subscription_payments ADD COLUMN `refund_decided_at` TIMESTAMP NULL AFTER `refund_requested_at`");
            }
            if (!in_array('cancel_requested_at', $cols, true)) {
                $this->db->query("ALTER TABLE subscription_payments ADD COLUMN `cancel_requested_at` TIMESTAMP NULL AFTER `refund_decided_at`");
            }
            if (!in_array('payment_session_expires_at', $cols, true)) {
                $this->db->query("ALTER TABLE subscription_payments ADD COLUMN `payment_session_expires_at` DATETIME NULL DEFAULT NULL AFTER `payment_url`");
            }
        } catch (\Throwable $e) {
        }

        $this->ensureProjectPlanPolicyColumns();

        // Billing details table + phone verification columns
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `user_billing_details` (
              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `user_id` INT UNSIGNED NOT NULL,
              `full_name` VARCHAR(100) NOT NULL DEFAULT '',
              `email` VARCHAR(255) NOT NULL DEFAULT '',
              `phone` VARCHAR(20) NOT NULL DEFAULT '',
              `address_line1` VARCHAR(255) NOT NULL DEFAULT '',
              `address_line2` VARCHAR(255) NULL DEFAULT NULL,
              `city` VARCHAR(100) NOT NULL DEFAULT '',
              `state` VARCHAR(100) NOT NULL DEFAULT '',
              `postal_code` VARCHAR(20) NOT NULL DEFAULT '',
              `country` VARCHAR(100) NOT NULL DEFAULT '',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY `uniq_user` (`user_id`),
              FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
        }

        try {
            $profCols = array_column($this->db->fetchAll("SHOW COLUMNS FROM user_profiles"), 'Field');
            if (!in_array('phone_verified_at', $profCols, true)) {
                $this->db->query("ALTER TABLE user_profiles ADD COLUMN `phone_verified_at` TIMESTAMP NULL DEFAULT NULL");
            }
            if (!in_array('phone_otp', $profCols, true)) {
                $this->db->query("ALTER TABLE user_profiles ADD COLUMN `phone_otp` VARCHAR(10) NULL DEFAULT NULL");
            }
            if (!in_array('phone_otp_expires_at', $profCols, true)) {
                $this->db->query("ALTER TABLE user_profiles ADD COLUMN `phone_otp_expires_at` TIMESTAMP NULL DEFAULT NULL");
            }
        } catch (\Throwable $e) {
        }
    }

    public function ensureNotificationTemplates(): void
    {
        try {
            $this->db->query("
                INSERT IGNORE INTO `mail_notification_templates`
                    (`slug`, `name`, `subject`, `body`, `variables`, `is_enabled`)
                VALUES
                    (
                        'subscription-confirmed',
                        'Subscription Confirmed',
                        'Your {{plan_name}} subscription is active',
                        '<h2>Hi {{user_name}},</h2>
<p>Your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong> is now active.</p>
<table style=\"border-collapse:collapse;width:100%;margin:16px 0;\">
<tr><td style=\"padding:8px;border:1px solid #ddd;\"><strong>Amount</strong></td><td style=\"padding:8px;border:1px solid #ddd;\">{{currency}} {{amount}}</td></tr>
<tr><td style=\"padding:8px;border:1px solid #ddd;\"><strong>Billing</strong></td><td style=\"padding:8px;border:1px solid #ddd;\">{{billing_cycle}}</td></tr>
<tr><td style=\"padding:8px;border:1px solid #ddd;\"><strong>Starts</strong></td><td style=\"padding:8px;border:1px solid #ddd;\">{{started_at}}</td></tr>
<tr><td style=\"padding:8px;border:1px solid #ddd;\"><strong>Expires</strong></td><td style=\"padding:8px;border:1px solid #ddd;\">{{expires_at}}</td></tr>
</table>
<p><a href=\"{{invoice_url}}\" style=\"background:#667eea;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;\">View Invoice</a></p>
<p><a href=\"{{dashboard_url}}\">Manage your subscription</a></p>',
                        '[\"user_name\",\"plan_name\",\"app_name\",\"currency\",\"amount\",\"billing_cycle\",\"started_at\",\"expires_at\",\"invoice_url\",\"dashboard_url\"]',
                        1
                    ),
                    (
                        'subscription-expiring',
                        'Subscription Expiring Soon',
                        'Your {{plan_name}} subscription expires in {{days_left}} day(s)',
                        '<h2>Hi {{user_name}},</h2>
<p>Your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong> will expire on <strong>{{expires_at}}</strong>.</p>
<p>You have {{days_left}} day(s) remaining.</p>
<p><a href=\"{{renew_url}}\" style=\"background:#f59e0b;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;\">Renew Now</a></p>',
                        '[\"user_name\",\"plan_name\",\"app_name\",\"expires_at\",\"days_left\",\"renew_url\"]',
                        1
                    ),
                    (
                        'subscription-expired',
                        'Subscription Expired',
                        'Your {{plan_name}} subscription has expired',
                        '<h2>Hi {{user_name}},</h2>
<p>Your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong> expired on <strong>{{expired_at}}</strong>.</p>
<p><a href=\"{{renew_url}}\" style=\"background:#e74c3c;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;\">Subscribe Again</a></p>',
                        '[\"user_name\",\"plan_name\",\"app_name\",\"expired_at\",\"renew_url\"]',
                        1
                    ),
                    (
                        'subscription-renewal',
                        'Subscription Renewal Reminder',
                        'Renew your {{plan_name}} subscription',
                        '<h2>Hi {{user_name}},</h2>
<p>This is a reminder to renew your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong>.</p>
<p>Plan amount: <strong>{{currency}} {{amount}}</strong></p>
<p><a href=\"{{renew_url}}\" style=\"background:#667eea;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;\">Renew Subscription</a></p>',
                        '[\"user_name\",\"plan_name\",\"app_name\",\"currency\",\"amount\",\"renew_url\"]',
                        1
                    ),
                    (
                        'subscription-cancel-otp',
                        'Subscription Cancellation Verification',
                        'Your cancellation code for {{plan_name}}',
                        '<h2>Hi {{user_name}},</h2>
<p>You requested to cancel your <strong>{{plan_name}}</strong> subscription.</p>
<p>Use the code below to confirm cancellation. This code expires in <strong>{{expiry_minutes}} minutes</strong>.</p>
<div style=\"text-align:center;margin:24px 0;\">
  <span style=\"display:inline-block;font-size:36px;font-weight:700;letter-spacing:10px;background:#f4f7fb;border:2px dashed #e74c3c;border-radius:10px;padding:16px 32px;color:#333;\">{{otp}}</span>
</div>
<p style=\"color:#888;font-size:.85em;\">If you did not request this, you can ignore this email — your subscription will remain active.</p>',
                        '[\"user_name\",\"plan_name\",\"otp\",\"expiry_minutes\"]',
                        1
                    ),
                    (
                        'subscription-cancelled',
                        'Subscription Cancelled',
                        'Your {{plan_name}} subscription has been cancelled',
                        '<h2>Hi {{user_name}},</h2>
<p>Your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong> has been cancelled.</p>
<table style=\"border-collapse:collapse;width:100%;margin:16px 0;\">
<tr><td style=\"padding:8px;border:1px solid #ddd;\"><strong>Refund Requested</strong></td><td style=\"padding:8px;border:1px solid #ddd;\">{{refund_requested}}</td></tr>
</table>
<p style=\"color:#555;\">If a refund was requested, an admin will review it and you will be notified by email once a decision is made.</p>
<p><a href=\"{{dashboard_url}}\">Go to your dashboard</a></p>',
                        '[\"user_name\",\"plan_name\",\"app_name\",\"refund_requested\",\"dashboard_url\"]',
                        1
                    ),
                    (
                        'phone_otp',
                        'Phone Number Verification OTP',
                        'Your phone verification code',
                        '<h2>Hi {{name}},</h2>
<p>You requested to verify your phone number <strong>{{phone}}</strong>.</p>
<p>Use the code below to complete verification. This code expires in <strong>{{expires_minutes}} minutes</strong>.</p>
<div style=\"text-align:center;margin:28px 0;\">
  <span style=\"display:inline-block;font-size:40px;font-weight:800;letter-spacing:12px;background:#f4f7fb;border:2px dashed #0077cc;border-radius:12px;padding:18px 36px;color:#1a1a2e;\">{{otp}}</span>
</div>
<p style=\"color:#888;font-size:.85em;\">If you did not request this, you can safely ignore this email.</p>',
                        '[\"name\",\"otp\",\"phone\",\"expires_minutes\"]',
                        1
                    )
            ");
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::ensureNotificationTemplates - ' . $e->getMessage());
        }
    }

    public function getInvoiceSettings(bool $forDisplay = false): array
    {
        $defaults = [
            'invoice_company_name' => defined('APP_NAME') ? APP_NAME : 'MMB Platform',
            'invoice_company_email' => '',
            'invoice_company_phone' => '',
            'invoice_company_address' => '',
            'invoice_logo' => '',
            'invoice_prefix' => 'INV',
            'invoice_accent_color' => '#0077cc',
            'invoice_footer_note' => 'Thank you for using our platform.',
            'invoice_terms' => 'This is a computer-generated invoice. No signature required.',
            'invoice_tax_enabled' => '0',
            'invoice_tax_label' => 'Tax',
            'invoice_tax_rate' => '0',
            'invoice_title' => 'Subscription Invoice',
            'invoice_subtitle' => 'Secure payment receipt',
            'invoice_item_label' => 'Subscription',
            'invoice_total_label' => 'Total',
            'invoice_layout_blocks' => '["bill_to","subscription_details","line_items","footer_notes"]',
        ];

        try {
            $rows = $this->db->fetchAll("SELECT `key`, value FROM settings WHERE `key` LIKE 'invoice_%'");
            foreach ($rows as $row) {
                $defaults[$row['key']] = $row['value'];
            }
        } catch (\Throwable $e) {
        }

        if ($forDisplay && !empty($defaults['invoice_logo'])) {
            $defaults['invoice_logo_url'] = $defaults['invoice_logo'];
        }

        return $defaults;
    }

    public function saveInvoiceSettings(array $data): void
    {
        $settings = [
            'invoice_company_name' => trim((string) ($data['invoice_company_name'] ?? '')),
            'invoice_company_email' => trim((string) ($data['invoice_company_email'] ?? '')),
            'invoice_company_phone' => trim((string) ($data['invoice_company_phone'] ?? '')),
            'invoice_company_address' => trim((string) ($data['invoice_company_address'] ?? '')),
            'invoice_logo' => trim((string) ($data['invoice_logo'] ?? '')),
            'invoice_prefix' => strtoupper(trim((string) ($data['invoice_prefix'] ?? 'INV'))) ?: 'INV',
            'invoice_accent_color' => preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($data['invoice_accent_color'] ?? ''))
                ? (string) $data['invoice_accent_color']
                : '#0077cc',
            'invoice_footer_note' => trim((string) ($data['invoice_footer_note'] ?? '')),
            'invoice_terms' => trim((string) ($data['invoice_terms'] ?? '')),
            'invoice_tax_enabled' => isset($data['invoice_tax_enabled']) && $data['invoice_tax_enabled'] ? '1' : '0',
            'invoice_tax_label' => trim((string) ($data['invoice_tax_label'] ?? 'Tax')) ?: 'Tax',
            'invoice_tax_rate' => (string) max(0, min(100, (float) ($data['invoice_tax_rate'] ?? 0))),
            'invoice_title' => trim((string) ($data['invoice_title'] ?? 'Subscription Invoice')) ?: 'Subscription Invoice',
            'invoice_subtitle' => trim((string) ($data['invoice_subtitle'] ?? 'Secure payment receipt')) ?: 'Secure payment receipt',
            'invoice_item_label' => trim((string) ($data['invoice_item_label'] ?? 'Subscription')) ?: 'Subscription',
            'invoice_total_label' => trim((string) ($data['invoice_total_label'] ?? 'Total')) ?: 'Total',
            'invoice_layout_blocks' => trim((string) ($data['invoice_layout_blocks'] ?? '["bill_to","subscription_details","line_items","footer_notes"]')),
        ];

        foreach ($settings as $key => $value) {
            $row = $this->db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($row) {
                $this->db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
            } else {
                $this->db->insert('settings', ['key' => $key, 'value' => $value, 'type' => 'string', 'created_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function getAppPlan(string $appKey, string $slugOrId): ?array
    {
        $config = $this->getAppConfig($appKey);
        if ($config === null) {
            return null;
        }

        try {
            if ($appKey === 'whatsapp') {
                $plan = ctype_digit($slugOrId)
                    ? $this->db->fetch(
                        "SELECT * FROM {$config['plan_table']} WHERE {$config['plan_id_column']} = ? AND {$config['plan_status_column']} = ? LIMIT 1",
                        [(int) $slugOrId, $config['plan_active_value']]
                    )
                    : $this->db->fetch(
                        "SELECT * FROM {$config['plan_table']}
                         WHERE (LOWER(REPLACE(REPLACE({$config['plan_name_column']}, ' Plan', ''), ' ', '-')) = ? OR {$config['plan_slug_column']} = ?)
                           AND {$config['plan_status_column']} = ?
                         LIMIT 1",
                        [strtolower($slugOrId), strtolower($slugOrId), $config['plan_active_value']]
                    );
            } else {
                $plan = $this->db->fetch(
                    "SELECT * FROM {$config['plan_table']}
                     WHERE {$config['plan_slug_column']} = ? AND {$config['plan_status_column']} = ?
                     LIMIT 1",
                    [$slugOrId, $config['plan_active_value']]
                );
            }
        } catch (\Throwable $e) {
            return null;
        }

        if (!$plan) {
            return null;
        }

        return $this->normalizePlanRow($appKey, $plan);
    }

    public function getActivePlans(string $appKey): array
    {
        $config = $this->getAppConfig($appKey);
        if ($config === null) {
            return [];
        }

        try {
            $orderBy = $appKey === 'whatsapp' ? "{$config['plan_price_column']} ASC, {$config['plan_id_column']} ASC" : "sort_order ASC, {$config['plan_price_column']} ASC";
            $plans = $this->db->fetchAll(
                "SELECT * FROM {$config['plan_table']} WHERE {$config['plan_status_column']} = ? ORDER BY {$orderBy}",
                [$config['plan_active_value']]
            );
            return array_map(fn(array $row) => $this->normalizePlanRow($appKey, $row), $plans);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getCurrentSubscription(string $appKey, int $userId): ?array
    {
        $config = $this->getAppConfig($appKey);
        if ($config === null) {
            return null;
        }

        try {
            if ($appKey === 'whatsapp') {
                $row = $this->db->fetch(
                    "SELECT s.*, p.id AS plan_id, p.name AS plan_name, LOWER(REPLACE(REPLACE(p.name, ' Plan', ''), ' ', '-')) AS plan_slug,
                            p.price, p.currency, p.duration_days, p.messages_limit, p.sessions_limit, p.api_calls_limit,
                            s.start_date AS started_at, s.end_date AS expires_at
                     FROM whatsapp_subscriptions s
                     LEFT JOIN whatsapp_subscription_plans p
                       ON p.id = COALESCE(s.plan_id, (SELECT id FROM whatsapp_subscription_plans WHERE LOWER(REPLACE(REPLACE(name, ' Plan', ''), ' ', '-')) = s.plan_type LIMIT 1))
                     WHERE s.user_id = ? AND s.status = 'active'
                     ORDER BY s.created_at DESC
                     LIMIT 1",
                    [$userId]
                );
            } else {
                $row = $this->db->fetch(
                    "SELECT s.*, p.*,
                            s.{$config['subscription_started_column']} AS started_at,
                            s.{$config['subscription_expires_column']} AS expires_at,
                            p.{$config['plan_name_column']} AS plan_name,
                            p.{$config['plan_slug_column']} AS plan_slug
                     FROM {$config['subscription_table']} s
                     JOIN {$config['plan_table']} p ON p.{$config['plan_id_column']} = s.{$config['subscription_plan_column']}
                     WHERE s.{$config['subscription_user_column']} = ? AND s.{$config['subscription_status_column']} = ?
                     ORDER BY s.{$config['subscription_started_column']} DESC
                     LIMIT 1",
                    [$userId, $config['subscription_active_value']]
                );
            }
        } catch (\Throwable $e) {
            return null;
        }

        return $row ? $this->normalizeSubscriptionRow($appKey, $row) : null;
    }

    public function ensureUserHasDefaultPlatformPlan(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        try {
            $this->ensureInfrastructure();
            $this->ensurePlatformPlanTables();

            $active = $this->db->fetch(
                "SELECT s.id
                 FROM platform_user_subscriptions s
                 JOIN platform_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                   AND (s.expires_at IS NULL OR s.expires_at > NOW())
                 LIMIT 1",
                [$userId]
            );
            if ($active) {
                return true;
            }

            $freePlan = $this->db->fetch(
                "SELECT *
                 FROM platform_plans
                 WHERE status = 'active' AND (slug = 'free' OR price <= 0)
                 ORDER BY (slug = 'free') DESC, sort_order ASC, id ASC
                 LIMIT 1"
            );

            if (!$freePlan) {
                $freePlanId = $this->db->insert('platform_plans', [
                    'name' => 'Free Plan',
                    'slug' => 'free',
                    'description' => 'Default free access plan available until upgrade.',
                    'price' => 0.00,
                    'currency' => 'USD',
                    'billing_cycle' => 'lifetime',
                    'cancel_days' => 0,
                    'refund_days' => 0,
                    'color' => '#00f0ff',
                    'included_apps' => json_encode(['qr', 'whatsapp', 'convertx', 'resumex']),
                    'app_features' => json_encode((object) []),
                    'status' => 'active',
                    'sort_order' => -1000,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $freePlan = $this->db->fetch("SELECT * FROM platform_plans WHERE id = ? LIMIT 1", [(int) $freePlanId]);
            }

            if (!$freePlan) {
                return false;
            }

            $this->db->insert('platform_user_subscriptions', [
                'user_id' => $userId,
                'plan_id' => (int) $freePlan['id'],
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => null,
                'assigned_by' => null,
                'notes' => 'Auto-assigned default free plan',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::ensureUserHasDefaultPlatformPlan - ' . $e->getMessage());
            return false;
        }
    }

    public function getSubscriptionHistory(string $appKey, int $userId, int $limit = 20): array
    {
        $config = $this->getAppConfig($appKey);
        if ($config === null) {
            return [];
        }

        try {
            if ($appKey === 'whatsapp') {
                $rows = $this->db->fetchAll(
                    "SELECT s.*, p.id AS plan_id, p.name AS plan_name, LOWER(REPLACE(REPLACE(p.name, ' Plan', ''), ' ', '-')) AS plan_slug,
                            p.price, p.currency, p.duration_days,
                            s.start_date AS started_at, s.end_date AS expires_at
                     FROM whatsapp_subscriptions s
                     LEFT JOIN whatsapp_subscription_plans p
                       ON p.id = COALESCE(s.plan_id, (SELECT id FROM whatsapp_subscription_plans WHERE LOWER(REPLACE(REPLACE(name, ' Plan', ''), ' ', '-')) = s.plan_type LIMIT 1))
                     WHERE s.user_id = ?
                     ORDER BY s.created_at DESC
                     LIMIT {$limit}",
                    [$userId]
                );
            } else {
                $rows = $this->db->fetchAll(
                    "SELECT s.*, p.*,
                            s.{$config['subscription_started_column']} AS started_at,
                            s.{$config['subscription_expires_column']} AS expires_at,
                            p.{$config['plan_name_column']} AS plan_name,
                            p.{$config['plan_slug_column']} AS plan_slug
                     FROM {$config['subscription_table']} s
                     JOIN {$config['plan_table']} p ON p.{$config['plan_id_column']} = s.{$config['subscription_plan_column']}
                     WHERE s.{$config['subscription_user_column']} = ?
                     ORDER BY s.{$config['subscription_started_column']} DESC
                     LIMIT {$limit}",
                    [$userId]
                );
            }
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(fn(array $row) => $this->normalizeSubscriptionRow($appKey, $row), $rows);
    }

    public function getPaymentSettings(bool $forDisplay = false): array
    {
        $defaults = [
            'payment_method' => 'request',
            'payment_upi_id' => '',
            'payment_cashfree_enabled' => '0',
            'payment_cashfree_app_id' => '',
            'payment_cashfree_secret' => '',
            'payment_cashfree_sandbox' => '1',
            'payment_currency' => 'INR',
            'payment_manual_review_enabled' => '1',
            'require_mobile_verification' => '0',
        ];

        try {
            $rows = $this->db->fetchAll("SELECT `key`, value FROM settings WHERE `key` LIKE 'payment_%'");
            foreach ($rows as $row) {
                $defaults[$row['key']] = $row['value'];
            }
            $rmvRow = $this->db->fetch("SELECT value FROM settings WHERE `key` = 'require_mobile_verification'");
            if ($rmvRow) {
                $defaults['require_mobile_verification'] = $rmvRow['value'];
            }
        } catch (\Throwable $e) {
        }

        $defaults['payment_cashfree_secret'] = $this->decryptStoredSecret((string) ($defaults['payment_cashfree_secret'] ?? ''));
        if ($forDisplay) {
            $plain = $defaults['payment_cashfree_secret'];
            $defaults['payment_cashfree_secret'] = $plain === '' ? '' : '••••••••';
            $defaults['payment_cashfree_secret_set'] = $plain !== '';
        }

        return $defaults;
    }

    public function savePaymentSettings(array $input): void
    {
        $existing = $this->getPaymentSettings();
        $secret = trim((string) ($input['payment_cashfree_secret'] ?? ''));
        $storeSecret = $secret === '' ? ($existing['payment_cashfree_secret'] ?? '') : $secret;

        $data = [
            'payment_method' => $input['payment_method'] ?? 'request',
            'payment_upi_id' => trim((string) ($input['payment_upi_id'] ?? '')),
            'payment_cashfree_enabled' => !empty($input['payment_cashfree_enabled']) ? '1' : '0',
            'payment_cashfree_app_id' => trim((string) ($input['payment_cashfree_app_id'] ?? '')),
            'payment_cashfree_secret' => $storeSecret === '' ? '' : Security::encrypt($storeSecret),
            'payment_cashfree_sandbox' => !empty($input['payment_cashfree_sandbox']) ? '1' : '0',
            'payment_currency' => trim((string) ($input['payment_currency'] ?? 'INR')),
            'payment_manual_review_enabled' => !empty($input['payment_manual_review_enabled']) ? '1' : '0',
        ];

        foreach ($data as $key => $value) {
            $row = $this->db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($row) {
                $this->db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
            } else {
                $this->db->insert('settings', ['key' => $key, 'value' => $value, 'type' => 'string', 'created_at' => date('Y-m-d H:i:s')]);
            }
        }

        // Save require_mobile_verification separately (not a payment_ prefix key)
        $rmvValue = !empty($input['require_mobile_verification']) ? '1' : '0';
        $rmvRow = $this->db->fetch("SELECT id FROM settings WHERE `key` = 'require_mobile_verification'");
        if ($rmvRow) {
            $this->db->update('settings', ['value' => $rmvValue, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', ['require_mobile_verification']);
        } else {
            $this->db->insert('settings', ['key' => 'require_mobile_verification', 'value' => $rmvValue, 'type' => 'string', 'created_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function createOrReusePayment(array $data): array
    {
        $this->ensureInfrastructure();

        $existing = $this->db->fetch(
            "SELECT * FROM subscription_payments
             WHERE user_id = ? AND app_key = ? AND plan_id = ? AND gateway = ?
               AND status IN ('pending','verification_pending')
             ORDER BY id DESC LIMIT 1",
            [
                (int) $data['user_id'],
                (string) $data['app_key'],
                (int) $data['plan_id'],
                (string) $data['gateway'],
            ]
        );

        if ($existing) {
            // If a fresh UPI payload is supplied but the existing record has none, update it now.
            if (!empty($data['payment_payload']) && empty($existing['payment_payload'])) {
                $this->db->query(
                    "UPDATE subscription_payments SET payment_payload = ?, updated_at = NOW() WHERE id = ?",
                    [$data['payment_payload'], (int) $existing['id']]
                );
                $existing['payment_payload'] = $data['payment_payload'];
            }
            return $existing;
        }

        $invoiceNo = $this->generateUniqueToken('INV-');
        $reference = $this->generateUniqueToken('PAY-');
        $expiresAt = (array_key_exists('expires_at', $data) && $data['expires_at'] !== null)
            ? $data['expires_at']
            : $this->calculateExpiry((string) ($data['billing_cycle'] ?? ''));

        $paymentId = $this->db->insert('subscription_payments', [
            'user_id' => (int) $data['user_id'],
            'app_key' => (string) $data['app_key'],
            'plan_id' => (int) $data['plan_id'],
            'plan_name' => (string) $data['plan_name'],
            'billing_cycle' => $data['billing_cycle'] ?? null,
            'gateway' => (string) $data['gateway'],
            'status' => (string) ($data['status'] ?? 'pending'),
            'amount' => (float) $data['amount'],
            'currency' => (string) ($data['currency'] ?? 'USD'),
            'invoice_no' => $invoiceNo,
            'reference' => $reference,
            'provider_order_id' => $data['provider_order_id'] ?? null,
            'provider_payment_session_id' => $data['provider_payment_session_id'] ?? null,
            'payment_url' => $data['payment_url'] ?? null,
            'payment_payload' => $data['payment_payload'] ?? null,
            'metadata_json' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->fetch("SELECT * FROM subscription_payments WHERE id = ?", [$paymentId]) ?? [];
    }

    public function getUserPayment(int $paymentId, int $userId): ?array
    {
        $payment = $this->db->fetch(
            "SELECT * FROM subscription_payments WHERE id = ? AND user_id = ? LIMIT 1",
            [$paymentId, $userId]
        ) ?: null;

        if ($payment !== null) {
            $payment['currency'] = $this->normalizeCurrencyValue($payment['currency'] ?? null);
        }

        return $payment;
    }

    public function getUserPayments(int $userId, ?string $appKey = null): array
    {
        $rows = [];
        if ($appKey !== null) {
            $rows = $this->db->fetchAll(
                "SELECT * FROM subscription_payments WHERE user_id = ? AND app_key = ? ORDER BY id DESC",
                [$userId, $appKey]
            );
        } else {
            $rows = $this->db->fetchAll(
                "SELECT * FROM subscription_payments WHERE user_id = ? ORDER BY id DESC",
                [$userId]
            );
        }

        return array_map(function (array $row): array {
            $row['currency'] = $this->normalizeCurrencyValue($row['currency'] ?? null);
            return $row;
        }, $rows);
    }

    public function findPaymentByProviderOrderId(string $orderId): ?array
    {
        $payment = $this->db->fetch(
            "SELECT * FROM subscription_payments WHERE provider_order_id = ? LIMIT 1",
            [$orderId]
        ) ?: null;

        if ($payment !== null) {
            $payment['currency'] = $this->normalizeCurrencyValue($payment['currency'] ?? null);
        }

        return $payment;
    }

    public function confirmCashfreePayment(array $payment, array $settings, int $actorId = 0): array
    {
        $result = $this->verifyCashfreePayment($payment, $settings);
        if (empty($result['success'])) {
            return $result;
        }

        if (empty($result['paid'])) {
            return $result + ['approved' => false];
        }

        $approved = $this->approvePayment((int) $payment['id'], $actorId);

        return $result + [
            'approved' => $approved,
            'payment_id' => (int) $payment['id'],
        ];
    }

    public function getSubscriptionInvoicePayment(string $appKey, int $subscriptionId, int $userId): ?array
    {
        $config = $this->getAppConfig($appKey);
        if ($config === null) {
            return null;
        }

        $payment = $this->db->fetch(
            "SELECT *
             FROM subscription_payments
             WHERE user_id = ? AND app_key = ? AND subscription_id = ?
             ORDER BY id DESC
             LIMIT 1",
            [$userId, $appKey, $subscriptionId]
        );

        if ($payment) {
            $payment['currency'] = $this->normalizeCurrencyValue($payment['currency'] ?? null);
            return $payment;
        }

        try {
            if ($appKey === 'whatsapp') {
                $subscription = $this->db->fetch(
                    "SELECT s.id, s.status, s.start_date AS started_at, s.end_date AS expires_at,
                            p.id AS plan_id, p.name AS plan_name, p.price, p.currency, p.duration_days
                     FROM whatsapp_subscriptions s
                     LEFT JOIN whatsapp_subscription_plans p
                       ON p.id = COALESCE(
                           s.plan_id,
                           (SELECT id FROM whatsapp_subscription_plans WHERE LOWER(REPLACE(REPLACE(name, ' Plan', ''), ' ', '-')) = s.plan_type LIMIT 1)
                       )
                     WHERE s.id = ? AND s.user_id = ?
                     LIMIT 1",
                    [$subscriptionId, $userId]
                );
            } else {
                $subscription = $this->db->fetch(
                    "SELECT s.id,
                            s.{$config['subscription_status_column']} AS status,
                            s.{$config['subscription_started_column']} AS started_at,
                            s.{$config['subscription_expires_column']} AS expires_at,
                            p.{$config['plan_id_column']} AS plan_id,
                            p.{$config['plan_name_column']} AS plan_name,
                            p.{$config['plan_price_column']} AS price,
                            p.{$config['plan_currency_column']} AS currency,
                            p.{$config['plan_billing_column']} AS billing_cycle
                     FROM {$config['subscription_table']} s
                     JOIN {$config['plan_table']} p ON p.{$config['plan_id_column']} = s.{$config['subscription_plan_column']}
                     WHERE s.id = ? AND s.{$config['subscription_user_column']} = ?
                     LIMIT 1",
                    [$subscriptionId, $userId]
                );
            }
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::getSubscriptionInvoicePayment - ' . $e->getMessage());
            return null;
        }

        if (!$subscription) {
            return null;
        }

        $billingCycle = $subscription['billing_cycle'] ?? null;
        if ($appKey === 'whatsapp') {
            $billingCycle = ((int) ($subscription['duration_days'] ?? 30)) . ' days';
        }

        return [
            'id' => (int) $subscription['id'],
            'user_id' => $userId,
            'app_key' => $appKey,
            'plan_id' => (int) ($subscription['plan_id'] ?? 0),
            'subscription_id' => (int) $subscription['id'],
            'plan_name' => (string) ($subscription['plan_name'] ?? 'Subscription'),
            'billing_cycle' => $billingCycle ?: 'one-time',
            'amount' => (float) ($subscription['price'] ?? 0),
            'currency' => $this->normalizeCurrencyValue($subscription['currency'] ?? null),
            'invoice_no' => 'INV-' . strtoupper(substr(md5($appKey . '-' . $subscription['id'] . '-' . ($subscription['started_at'] ?? '')), 0, 8)),
            'paid_at' => $subscription['started_at'] ?? null,
            'created_at' => $subscription['started_at'] ?? date('Y-m-d H:i:s'),
            'expires_at' => $subscription['expires_at'] ?? null,
            'status' => $subscription['status'] ?? 'active',
        ];
    }

    // -------------------------------------------------------------------------
    // Cancel subscription with OTP verification + 7-day refund policy
    // -------------------------------------------------------------------------

    /**
     * Generate and store a one-time cancel-confirmation code for a payment,
     * then send it via email to the user. Returns false if anything fails.
     */
    public function sendCancelOtp(int $paymentId, int $userId): bool
    {
        $payment = $this->getUserPayment($paymentId, $userId);
        if (!$payment || ($payment['status'] ?? '') !== 'paid' || empty($payment['subscription_id'])) {
            return false;
        }

        // Fetch user email
        try {
            $user = $this->db->fetch("SELECT email, name FROM users WHERE id = ? LIMIT 1", [$userId]);
        } catch (\Throwable $e) {
            return false;
        }
        if (!$user || empty($user['email'])) {
            return false;
        }

        $code = (string) random_int(100000, 999999);
        $expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes

        // Persist code in metadata
        $meta = $this->decodePaymentMetadata($payment);
        $meta['cancel_otp'] = $code;
        $meta['cancel_otp_expires'] = $expires;
        $this->db->query(
            "UPDATE subscription_payments SET metadata_json = ?, updated_at = NOW() WHERE id = ?",
            [json_encode($meta), $paymentId]
        );

        // Send email
        $this->ensureNotificationTemplates();
        MailService::sendNotification(
            $user['email'],
            'subscription-cancel-otp',
            [
                'user_name' => $user['name'] ?? 'User',
                'plan_name' => $payment['plan_name'] ?? 'Subscription',
                'otp' => $code,
                'expiry_minutes' => '10',
            ],
            false
        );

        return true;
    }

    /**
     * Verify the OTP and, if valid, cancel the subscription.
     *  - Within 7 days (or plan refund_days): cancel + auto-request refund + email.
     *  - After 7 days: cancel but subscription stays active until expires_at.
     * Returns ['success'=>bool, 'refund_eligible'=>bool, 'message'=>string].
     */
    public function cancelSubscriptionWithOtp(int $paymentId, int $userId, string $otp): array
    {
        $payment = $this->getUserPayment($paymentId, $userId);
        if (!$payment || ($payment['status'] ?? '') !== 'paid' || empty($payment['subscription_id'])) {
            return ['success' => false, 'refund_eligible' => false, 'message' => 'Invalid payment or subscription not active.'];
        }

        $meta = $this->decodePaymentMetadata($payment);
        $storedOtp = $meta['cancel_otp'] ?? null;
        $otpExpires = $meta['cancel_otp_expires'] ?? null;

        if (!$storedOtp || $otp !== $storedOtp) {
            return ['success' => false, 'refund_eligible' => false, 'message' => 'Invalid verification code.'];
        }
        if ($otpExpires && time() > strtotime($otpExpires)) {
            return ['success' => false, 'refund_eligible' => false, 'message' => 'Verification code has expired. Please request a new one.'];
        }

        // Clear OTP
        unset($meta['cancel_otp'], $meta['cancel_otp_expires']);

        // Determine refund eligibility (within refund_days, default 7)
        $refundDays = (int) ($meta['refund_days'] ?? 7);
        $start = $payment['paid_at'] ?? $payment['created_at'] ?? null;
        $withinRefundWindow = $start && (time() <= strtotime($start . ' +' . $refundDays . ' days'));

        $config = $this->getAppConfig((string) $payment['app_key']);

        try {
            if ($withinRefundWindow) {
                // Cancel immediately + auto-request refund; subscription row is cancelled
                $this->doCancelSubscriptionRow($payment, $config, $userId);
                $meta['cancel_otp_verified'] = true;
                $this->db->query(
                    "UPDATE subscription_payments
                     SET status = 'cancelled', cancel_requested_at = NOW(),
                         refund_status = 'requested', refund_requested_at = NOW(),
                         metadata_json = ?, updated_at = NOW()
                     WHERE id = ?",
                    [json_encode($meta), $paymentId]
                );
                Logger::activity($userId, 'subscription_cancelled_with_refund_request', ['payment_id' => $paymentId]);
                $this->sendCancellationEmail($paymentId, true);
            } else {
                // Cancel after window: subscription stays active until expires_at, no refund
                $meta['cancel_otp_verified'] = true;
                $this->db->query(
                    "UPDATE subscription_payments
                     SET status = 'cancelled', cancel_requested_at = NOW(),
                         metadata_json = ?, updated_at = NOW()
                     WHERE id = ?",
                    [json_encode($meta), $paymentId]
                );
                // Mark subscription as "cancelling at period end" — still active until expires_at
                // We set a cancel marker but don't change the app subscription status yet
                Logger::activity($userId, 'subscription_cancelled_end_of_period', ['payment_id' => $paymentId]);
                $this->sendCancellationEmail($paymentId, false);
            }
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::cancelSubscriptionWithOtp - ' . $e->getMessage());
            return ['success' => false, 'refund_eligible' => false, 'message' => 'An error occurred. Please try again.'];
        }

        return ['success' => true, 'refund_eligible' => $withinRefundWindow, 'message' => ''];
    }

    /**
     * Immediately cancel the subscription row in the project's own table.
     */
    private function doCancelSubscriptionRow(array $payment, ?array $config, int $userId): void
    {
        if ($payment['app_key'] === 'whatsapp') {
            $this->db->query(
                "UPDATE whatsapp_subscriptions SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND user_id = ?",
                [(int) $payment['subscription_id'], $userId]
            );
            return;
        }
        if ($config === null) {
            return;
        }
        $cancelledColumn = $config['subscription_cancelled_column'] ?? null;
        $sql = "UPDATE {$config['subscription_table']}
                SET {$config['subscription_status_column']} = 'cancelled', updated_at = NOW()";
        if ($cancelledColumn) {
            $sql .= ", {$cancelledColumn} = NOW()";
        }
        $sql .= " WHERE id = ? AND {$config['subscription_user_column']} = ?";
        $this->db->query($sql, [(int) $payment['subscription_id'], $userId]);
    }

    /**
     * Send cancellation confirmation email to the user.
     * $refundRequested = true when within refund window.
     */
    private function sendCancellationEmail(int $paymentId, bool $refundRequested): void
    {
        try {
            $payment = $this->db->fetch(
                "SELECT sp.*, u.email AS user_email, u.name AS user_name
                 FROM subscription_payments sp
                 JOIN users u ON u.id = sp.user_id
                 WHERE sp.id = ? LIMIT 1",
                [$paymentId]
            );
            if (!$payment || empty($payment['user_email'])) {
                return;
            }

            $dashboardUrl = match ($payment['app_key']) {
                'resumex' => '/projects/resumex/plans',
                'qr' => '/projects/qr/plans',
                'convertx' => '/projects/convertx/plans',
                'whatsapp' => '/projects/whatsapp/plans',
                default => '/plans',
            };

            MailService::sendNotification(
                (string) $payment['user_email'],
                'subscription-cancelled',
                [
                    'user_name' => $payment['user_name'] ?? 'User',
                    'plan_name' => $payment['plan_name'] ?? 'Subscription',
                    'app_name' => self::APP_CONFIG[$payment['app_key']]['label'] ?? 'MMB Platform',
                    'refund_requested' => $refundRequested ? 'Yes — pending admin confirmation' : 'No',
                    'dashboard_url' => $this->absoluteUrl($dashboardUrl),
                ],
                false
            );
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::sendCancellationEmail - ' . $e->getMessage());
        }
    }

    /**
     * Admin-initiated subscription cancellation. Cancels the subscription regardless of
     * cancel-day restrictions; marks payment as cancelled and notifies the user.
     */
    public function adminCancelSubscription(int $paymentId, int $adminId): bool
    {
        $payment = $this->db->fetch("SELECT * FROM subscription_payments WHERE id = ?", [$paymentId]);
        if (!$payment) {
            return false;
        }

        $config = $this->getAppConfig((string) $payment['app_key']);

        try {
            // Cancel the subscription row in the project table (if linked)
            if (!empty($payment['subscription_id'])) {
                if ($payment['app_key'] === 'whatsapp') {
                    $this->db->query(
                        "UPDATE whatsapp_subscriptions SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
                        [(int) $payment['subscription_id']]
                    );
                } elseif ($config !== null) {
                    $cancelledColumn = $config['subscription_cancelled_column'] ?? null;
                    $sql = "UPDATE {$config['subscription_table']}
                            SET {$config['subscription_status_column']} = 'cancelled', updated_at = NOW()";
                    if ($cancelledColumn) {
                        $sql .= ", {$cancelledColumn} = NOW()";
                    }
                    $sql .= " WHERE id = ?";
                    $this->db->query($sql, [(int) $payment['subscription_id']]);
                }
            }

            // Cancel the payment record
            $this->db->query(
                "UPDATE subscription_payments
                 SET status = 'cancelled', cancel_requested_at = NOW(), updated_at = NOW()
                 WHERE id = ?",
                [$paymentId]
            );

            Logger::activity($adminId, 'admin_subscription_cancelled', ['payment_id' => $paymentId, 'user_id' => $payment['user_id']]);
            $this->sendCancellationEmail($paymentId, false);
            return true;
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::adminCancelSubscription - ' . $e->getMessage());
            return false;
        }
    }

    public function cancelSubscriptionByPayment(int $paymentId, int $userId): bool
    {
        $payment = $this->db->fetch("SELECT * FROM subscription_payments WHERE id = ?", [$paymentId]);
        if (!$payment || empty($payment['subscription_id'])) {
            return false;
        }

        $config = $this->getAppConfig((string) $payment['app_key']);
        if ($config === null) {
            return false;
        }

        $allowed = $this->canCancelPayment($payment);
        if (!$allowed['allowed']) {
            return false;
        }

        try {
            if ($payment['app_key'] === 'whatsapp') {
                $this->db->query(
                    "UPDATE whatsapp_subscriptions
                     SET status = 'cancelled', updated_at = NOW()
                     WHERE id = ? AND user_id = ?",
                    [(int) $payment['subscription_id'], $userId]
                );
            } else {
                $cancelledColumn = $config['subscription_cancelled_column'] ?? null;
                $sql = "UPDATE {$config['subscription_table']}
                        SET {$config['subscription_status_column']} = 'cancelled', updated_at = NOW()";
                if ($cancelledColumn) {
                    $sql .= ", {$cancelledColumn} = NOW()";
                }
                $sql .= " WHERE id = ? AND {$config['subscription_user_column']} = ?";
                $this->db->query($sql, [(int) $payment['subscription_id'], $userId]);
            }

            $this->db->query(
                "UPDATE subscription_payments SET cancel_requested_at = NOW(), updated_at = NOW() WHERE id = ?",
                [$paymentId]
            );
            return true;
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::cancelSubscriptionByPayment - ' . $e->getMessage());
            return false;
        }
    }

    public function requestRefund(int $paymentId, int $userId): bool
    {
        $payment = $this->getUserPayment($paymentId, $userId);
        if (!$payment || ($payment['status'] ?? '') !== 'paid' || ($payment['refund_status'] ?? 'none') !== 'none') {
            return false;
        }

        $allowed = $this->canRequestRefund($payment);
        if (!$allowed['allowed']) {
            return false;
        }

        $this->db->query(
            "UPDATE subscription_payments
             SET refund_status = 'requested', refund_requested_at = NOW(), updated_at = NOW()
             WHERE id = ? AND user_id = ?",
            [$paymentId, $userId]
        );

        return true;
    }

    public function reviewRefundRequest(int $paymentId, string $decision, int $adminId, string $note = ''): bool
    {
        if (!in_array($decision, ['approved', 'rejected', 'refunded'], true)) {
            return false;
        }
        $payment = $this->db->fetch("SELECT * FROM subscription_payments WHERE id = ? LIMIT 1", [$paymentId]);
        if (!$payment || ($payment['refund_status'] ?? 'none') !== 'requested') {
            return false;
        }

        $finalStatus = $decision;

        // When admin approves a Cashfree payment, attempt gateway refund automatically.
        if ($decision === 'approved' && ($payment['gateway'] ?? '') === 'cashfree' && !empty($payment['provider_order_id'])) {
            $settings = $this->getPaymentSettings();
            $gatewayResult = $this->triggerCashfreeRefund($payment, $settings, $note);
            if ($gatewayResult['success']) {
                $finalStatus = 'refunded';
                $note = trim($note . ' [Gateway refund initiated: ' . ($gatewayResult['refund_id'] ?? 'OK') . ']');
            } else {
                // Log gateway failure but still mark as approved so admin can follow up manually.
                Logger::warning('SubscriptionService::reviewRefundRequest - Cashfree refund API failed: ' . ($gatewayResult['message'] ?? 'unknown'), [
                    'payment_id' => $paymentId,
                ]);
                $note = trim($note . ' [Gateway error: ' . ($gatewayResult['message'] ?? 'unknown') . ' — process manually]');
            }
        }

        $this->db->query(
            "UPDATE subscription_payments
             SET refund_status = ?, refund_decided_at = NOW(), admin_notes = ?, updated_at = NOW()
             WHERE id = ?",
            [$finalStatus, $note, $paymentId]
        );
        Logger::activity($adminId, 'subscription_refund_reviewed', ['payment_id' => $paymentId, 'decision' => $finalStatus]);
        return true;
    }

    /**
     * Call the Cashfree refunds API to initiate a gateway-level refund.
     *
     * @param array  $payment  The subscription_payments row (must have provider_order_id and amount).
     * @param array  $settings Payment settings (cashfree credentials).
     * @param string $note     Optional reason / note for the refund.
     * @return array ['success' => bool, 'refund_id' => string|null, 'message' => string|null]
     */
    private function triggerCashfreeRefund(array $payment, array $settings, string $note = ''): array
    {
        $orderId = (string) ($payment['provider_order_id'] ?? '');
        if ($orderId === '') {
            return ['success' => false, 'message' => 'Missing Cashfree order ID.'];
        }

        $isSandbox = ($settings['payment_cashfree_sandbox'] ?? '1') === '1';
        $baseUrl = $isSandbox
            ? 'https://sandbox.cashfree.com/pg/orders/'
            : 'https://api.cashfree.com/pg/orders/';

        $refundId = 'refund_' . $payment['id'] . '_' . time();
        $payload  = [
            'refund_amount' => (float) $payment['amount'],
            'refund_id'     => $refundId,
            'refund_note'   => $note ?: 'Admin-approved refund',
        ];

        $url = $baseUrl . rawurlencode($orderId) . '/refunds';
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-version: 2023-08-01',
                'x-client-id: '     . ($settings['payment_cashfree_app_id'] ?? ''),
                'x-client-secret: ' . ($settings['payment_cashfree_secret'] ?? ''),
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpStatus = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            return ['success' => false, 'message' => $curlError ?: 'cURL error contacting Cashfree.'];
        }

        $decoded = json_decode($response, true);
        // Cashfree returns 200 on success; refund_status will be PENDING or SUCCESS.
        if ($httpStatus >= 200 && $httpStatus < 300 && is_array($decoded)) {
            return [
                'success'   => true,
                'refund_id' => $decoded['refund_id'] ?? $refundId,
                'message'   => $decoded['refund_message'] ?? null,
            ];
        }

        $errMsg = is_array($decoded) ? ($decoded['message'] ?? 'Cashfree refund failed.') : 'Cashfree refund failed.';
        return ['success' => false, 'message' => $errMsg];
    }

    /**
     * Return the configured refund window (in days) for a payment.
     * Falls back to 7 days when not configured.
     */
    public function getCancelRefundWindowDays(array $payment): int
    {
        $meta = $this->decodePaymentMetadata($payment);
        $refundDays = (int) ($meta['refund_days'] ?? 7);
        return $refundDays > 0 ? $refundDays : 7;
    }

    public function canCancelPayment(array $payment): array
    {
        $metadata = $this->decodePaymentMetadata($payment);
        $cancelDays = (int) ($metadata['cancel_days'] ?? 0);
        if ($cancelDays <= 0) {
            return ['allowed' => true, 'reason' => null];
        }

        $start = $payment['paid_at'] ?? $payment['created_at'] ?? null;
        if (!$start) {
            return ['allowed' => false, 'reason' => 'Missing payment date.'];
        }

        $deadline = strtotime($start . ' +' . $cancelDays . ' days');
        return time() <= $deadline
            ? ['allowed' => true, 'reason' => null, 'deadline' => $deadline]
            : ['allowed' => false, 'reason' => 'Cancellation window has ended.', 'deadline' => $deadline];
    }

    public function canRequestRefund(array $payment): array
    {
        $metadata = $this->decodePaymentMetadata($payment);
        $refundDays = (int) ($metadata['refund_days'] ?? 0);
        if ($refundDays <= 0) {
            return ['allowed' => false, 'reason' => 'Refunds are not available for this plan.'];
        }

        $start = $payment['paid_at'] ?? $payment['created_at'] ?? null;
        if (!$start) {
            return ['allowed' => false, 'reason' => 'Missing payment date.'];
        }

        $deadline = strtotime($start . ' +' . $refundDays . ' days');
        return time() <= $deadline
            ? ['allowed' => true, 'reason' => null, 'deadline' => $deadline]
            : ['allowed' => false, 'reason' => 'Refund window has ended.', 'deadline' => $deadline];
    }

    public function getAdminPayments(?string $appKey = null): array
    {
        if ($appKey !== null) {
            return $this->db->fetchAll(
                "SELECT sp.*, u.name AS user_name, u.email AS user_email
                 FROM subscription_payments sp
                 JOIN users u ON u.id = sp.user_id
                 WHERE sp.app_key = ?
                 ORDER BY FIELD(sp.status,'verification_pending','pending','paid','failed','cancelled'), sp.id DESC",
                [$appKey]
            );
        }

        return $this->db->fetchAll(
            "SELECT sp.*, u.name AS user_name, u.email AS user_email
             FROM subscription_payments sp
             JOIN users u ON u.id = sp.user_id
             ORDER BY FIELD(sp.status,'verification_pending','pending','paid','failed','cancelled'), sp.id DESC"
        );
    }

    public function getAdminRefundPayments(): array
    {
        return $this->db->fetchAll(
            "SELECT sp.*, u.name AS user_name, u.email AS user_email
             FROM subscription_payments sp
             JOIN users u ON u.id = sp.user_id
             WHERE sp.refund_status IN ('requested','approved','refunded','rejected')
             ORDER BY FIELD(sp.refund_status,'requested','approved','refunded','rejected'), sp.refund_requested_at DESC, sp.id DESC"
        );
    }

    public function markUserPaymentSubmitted(int $paymentId, int $userId): bool
    {
        $payment = $this->getUserPayment($paymentId, $userId);
        if (!$payment || !in_array($payment['status'], ['pending', 'verification_pending'], true)) {
            return false;
        }

        $this->db->query(
            "UPDATE subscription_payments
             SET status = 'verification_pending', updated_at = NOW()
             WHERE id = ? AND user_id = ?",
            [$paymentId, $userId]
        );

        return true;
    }

    public function approvePayment(int $paymentId, int $adminId): bool
    {
        $payment = $this->db->fetch("SELECT * FROM subscription_payments WHERE id = ? LIMIT 1", [$paymentId]);
        if (!$payment) {
            return false;
        }

        if ($payment['status'] === 'paid' && !empty($payment['subscription_id'])) {
            return true;
        }

        $subscriptionId = match ($payment['app_key']) {
            'resumex' => $this->activateResumeXSubscription((int) $payment['user_id'], (int) $payment['plan_id'], $adminId),
            'qr' => $this->activateQrSubscription((int) $payment['user_id'], (int) $payment['plan_id'], $adminId),
            'convertx' => $this->activateConvertXSubscription((int) $payment['user_id'], (int) $payment['plan_id'], $adminId),
            'whatsapp' => $this->activateWhatsAppSubscription((int) $payment['user_id'], (int) $payment['plan_id'], $adminId),
            default => $this->activatePlatformSubscription((int) $payment['user_id'], (int) $payment['plan_id'], $adminId),
        };

        if ($subscriptionId <= 0) {
            return false;
        }

        $subscriptionExpiresAt = $this->resolveSubscriptionExpiryRaw((string) $payment['app_key'], $subscriptionId);

        $this->db->query(
            "UPDATE subscription_payments
             SET status = 'paid', subscription_id = ?, paid_at = NOW(), expires_at = ?, updated_at = NOW()
             WHERE id = ?",
            [$subscriptionId, $subscriptionExpiresAt, $paymentId]
        );

        $this->sendConfirmationEmail($paymentId);

        return true;
    }

    public function rejectPayment(int $paymentId, int $adminId, string $reason = ''): bool
    {
        $payment = $this->db->fetch("SELECT id FROM subscription_payments WHERE id = ? LIMIT 1", [$paymentId]);
        if (!$payment) {
            return false;
        }

        $this->db->query(
            "UPDATE subscription_payments
             SET status = 'failed', admin_notes = ?, updated_at = NOW()
             WHERE id = ?",
            [$reason, $paymentId]
        );

        Logger::activity($adminId, 'subscription_payment_rejected', ['payment_id' => $paymentId]);
        return true;
    }

    public function createCashfreeOrder(array $payment, array $settings, array $customer, string $returnUrl): array
    {
        $orderAmount = (float) ($payment['amount'] ?? 0);
        if ($orderAmount <= 0) {
            return ['success' => false, 'message' => 'Cannot create a Cashfree order for a zero or negative amount. Please use a paid plan.'];
        }

        $validatedReturnUrl = $this->validateReturnUrl($returnUrl);
        if ($validatedReturnUrl === null) {
            return ['success' => false, 'message' => 'Invalid Cashfree return URL.'];
        }

        $baseUrl = ($settings['payment_cashfree_sandbox'] ?? '1') === '1'
            ? 'https://sandbox.cashfree.com/pg/orders'
            : 'https://api.cashfree.com/pg/orders';

        // Cashfree payment sessions expire after 60 minutes.  When a payment
        // already has a provider_order_id (session regeneration after expiry),
        // append a timestamp suffix to produce a unique order_id and avoid a
        // 409 conflict from Cashfree's duplicate-order check.
        $baseOrderId = 'order_' . strtolower((string)$payment['reference']);
        $orderId = empty($payment['provider_order_id'])
            ? $baseOrderId
            : substr($baseOrderId, 0, 30) . '_' . time();

        // Set a 60-minute session expiry for security (prevents stale sessions
        // from being usable beyond the checkout window).
        $sessionExpirySeconds = 3600; // 60 minutes
        $expiryAt = gmdate('Y-m-d\TH:i:s', time() + $sessionExpirySeconds) . '+00:00';
        $expiryDbAt = gmdate('Y-m-d H:i:s', time() + $sessionExpirySeconds);

        // Cashfree requires exactly 10 digits for customer_phone.
        $rawPhone = preg_replace('/\D/', '', (string) ($customer['phone'] ?? ''));
        // Strip leading country code (e.g. 91 prefix for India) if longer than 10 digits.
        if (strlen($rawPhone) > 10) {
            $rawPhone = substr($rawPhone, -10);
        }
        // Cashfree requires exactly 10 digits; use a placeholder when the user's phone is missing/invalid.
        // '9999999999' is a generic 10-digit placeholder accepted by Cashfree for missing phone numbers.
        $customerPhone = strlen($rawPhone) === 10 ? $rawPhone : '9999999999';

        $payload = [
            'order_id' => $orderId,
            'order_amount' => $orderAmount,
            'order_currency' => (string) $payment['currency'],
            'order_expiry_time' => $expiryAt,
            'customer_details' => [
                'customer_id' => 'user_' . (int) $payment['user_id'],
                // Use ?: so that an empty string also falls back to 'Customer' (Cashfree rejects empty names).
                'customer_name' => (string) ($customer['name'] ?: 'Customer'),
                'customer_email' => (string) ($customer['email'] ?? ''),
                'customer_phone' => $customerPhone,
            ],
            'order_meta' => [
                'return_url' => $this->appendQueryParams($validatedReturnUrl, ['order_id' => '{order_id}']),
            ],
        ];

        $ch = curl_init($baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-version: 2023-08-01',
                'x-client-id: ' . ($settings['payment_cashfree_app_id'] ?? ''),
                'x-client-secret: ' . ($settings['payment_cashfree_secret'] ?? ''),
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            return ['success' => false, 'message' => $curlError ?: 'Unable to contact Cashfree.'];
        }

        $decoded = json_decode($response, true);
        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            return ['success' => false, 'message' => is_array($decoded) ? ($decoded['message'] ?? 'Cashfree order creation failed.') : 'Cashfree order creation failed.'];
        }

        $paymentUrl = $decoded['payment_link'] ?? $decoded['payment_url'] ?? null;
        $sessionId = $decoded['payment_session_id'] ?? null;

        // Persist the session ID, order ID, expiry, and the full Cashfree response.
        try {
            $this->db->query(
                "UPDATE subscription_payments
                 SET provider_order_id = ?, provider_payment_session_id = ?,
                     payment_url = ?, payment_payload = ?,
                     payment_session_expires_at = ?, updated_at = NOW()
                 WHERE id = ?",
                [$orderId, $sessionId, $paymentUrl, $response, $expiryDbAt, (int) $payment['id']]
            );
        } catch (\Throwable $e) {
            // Column may not exist on older installs; fall back without expiry column.
            $this->db->query(
                "UPDATE subscription_payments
                 SET provider_order_id = ?, provider_payment_session_id = ?,
                     payment_url = ?, payment_payload = ?, updated_at = NOW()
                 WHERE id = ?",
                [$orderId, $sessionId, $paymentUrl, $response, (int) $payment['id']]
            );
        }

        return [
            'success' => true,
            'order_id' => $orderId,
            'payment_session_id' => $sessionId,
            'payment_url' => $paymentUrl,
            'expires_at' => $expiryDbAt,
            'payload' => $decoded,
        ];
    }

    public function verifyCashfreePayment(array $payment, array $settings): array
    {
        if (empty($payment['provider_order_id'])) {
            return ['success' => false, 'message' => 'Missing Cashfree order id.'];
        }

        $baseUrl = ($settings['payment_cashfree_sandbox'] ?? '1') === '1'
            ? 'https://sandbox.cashfree.com/pg/orders/'
            : 'https://api.cashfree.com/pg/orders/';

        $ch = curl_init($baseUrl . rawurlencode((string) $payment['provider_order_id']));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-version: 2023-08-01',
                'x-client-id: ' . ($settings['payment_cashfree_app_id'] ?? ''),
                'x-client-secret: ' . ($settings['payment_cashfree_secret'] ?? ''),
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            return ['success' => false, 'message' => $curlError ?: 'Unable to verify Cashfree payment.'];
        }

        $decoded = json_decode($response, true);
        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            return ['success' => false, 'message' => 'Unable to verify Cashfree payment.'];
        }

        $orderStatus = strtoupper((string) ($decoded['order_status'] ?? ''));
        if ($orderStatus === 'PAID') {
            return ['success' => true, 'paid' => true, 'payload' => $decoded];
        }

        return ['success' => true, 'paid' => false, 'status' => $orderStatus, 'payload' => $decoded];
    }

    public function sendConfirmationEmail(int $paymentId): void
    {
        try {
            $payment = $this->db->fetch(
                "SELECT sp.*, u.name AS user_name, u.email AS user_email
                 FROM subscription_payments sp
                 JOIN users u ON u.id = sp.user_id
                 WHERE sp.id = ? LIMIT 1",
                [$paymentId]
            );

            if (!$payment || empty($payment['user_email'])) {
                Logger::warning('SubscriptionService::sendConfirmationEmail - Payment not found or missing email', [
                    'payment_id' => $paymentId,
                ]);
                return;
            }

            $invoiceUrl = match ($payment['app_key']) {
                'resumex' => '/projects/resumex/plans/invoice/' . (int) $payment['subscription_id'],
                'platform' => '/plans/invoice/' . (int) $payment['subscription_id'],
                default => '/plans/payment/' . (int) $payment['id'] . '/invoice',
            };
            $dashboardUrl = match ($payment['app_key']) {
                'resumex' => '/projects/resumex/plans',
                'qr' => '/projects/qr/plans',
                'convertx' => '/projects/convertx/plans',
                'whatsapp' => '/projects/whatsapp/plans',
                default => '/plans',
            };
            $expiresAt = $this->resolveSubscriptionExpiry((string) $payment['app_key'], (int) $payment['subscription_id']);

            MailService::sendNotification(
                (string) $payment['user_email'],
                'subscription-confirmed',
                [
                    'user_name' => $payment['user_name'] ?? 'User',
                    'plan_name' => $payment['plan_name'],
                    'app_name' => self::APP_CONFIG[$payment['app_key']]['label'] ?? 'MMB Platform',
                    'currency' => $payment['currency'],
                    'amount' => number_format((float) $payment['amount'], 2, '.', ''),
                    'billing_cycle' => $payment['billing_cycle'] ?: 'one-time',
                    'started_at' => date('F j, Y'),
                    'expires_at' => $expiresAt ?: 'Lifetime',
                    'invoice_url' => $this->absoluteUrl($invoiceUrl),
                    'dashboard_url' => $this->absoluteUrl($dashboardUrl),
                ],
                false
            );
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::sendConfirmationEmail - ' . $e->getMessage());
        }
    }

    private function activatePlatformSubscription(int $userId, int $planId, int $adminId): int
    {
        $plan = $this->db->fetch("SELECT * FROM platform_plans WHERE id = ? LIMIT 1", [$planId]);
        if (!$plan) {
            return 0;
        }

        $expiresAt = $this->calculateExpiry((string) ($plan['billing_cycle'] ?? 'monthly'));
        $this->db->beginTransaction();
        try {
            $this->db->query(
                "UPDATE platform_user_subscriptions
                 SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
                 WHERE user_id = ? AND status = 'active'",
                [$userId]
            );
            $subId = $this->db->insert('platform_user_subscriptions', [
                'user_id' => $userId,
                'plan_id' => $planId,
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'assigned_by' => $adminId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            Logger::error('activatePlatformSubscription transaction failed: ' . $e->getMessage());
            return 0;
        }

        Notification::send($userId, 'plan_subscribed', 'Your "' . $plan['name'] . '" subscription is now active.', ['plan_id' => $planId, 'plan_name' => $plan['name']]);
        Logger::activity($adminId, 'platform_subscription_activated', ['user_id' => $userId, 'plan_id' => $planId, 'subscription_id' => $subId]);
        return $subId;
    }

    private function activateResumeXSubscription(int $userId, int $planId, int $adminId): int
    {
        $plan = $this->db->fetch("SELECT * FROM resumex_subscription_plans WHERE id = ? LIMIT 1", [$planId]);
        if (!$plan) {
            return 0;
        }

        $expiresAt = $this->calculateExpiry((string) ($plan['billing_cycle'] ?? 'monthly'));
        $this->db->beginTransaction();
        try {
            $this->db->query(
                "UPDATE resumex_user_subscriptions
                 SET status = 'cancelled', updated_at = NOW()
                 WHERE user_id = ? AND status IN ('active','trial')",
                [$userId]
            );
            $subId = $this->db->insert('resumex_user_subscriptions', [
                'user_id' => $userId,
                'plan_id' => $planId,
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            Logger::error('activateResumeXSubscription transaction failed: ' . $e->getMessage());
            return 0;
        }

        Notification::send($userId, 'plan_subscribed', 'Your ResumeX "' . $plan['name'] . '" subscription is now active.', ['plan_id' => $planId, 'plan_name' => $plan['name']]);
        Logger::activity($adminId, 'resumex_subscription_activated', ['user_id' => $userId, 'plan_id' => $planId, 'subscription_id' => $subId]);
        return $subId;
    }

    private function activateQrSubscription(int $userId, int $planId, int $adminId): int
    {
        $plan = $this->db->fetch("SELECT * FROM qr_subscription_plans WHERE id = ? LIMIT 1", [$planId]);
        if (!$plan) {
            return 0;
        }
        $expiresAt = $this->calculateExpiry((string) ($plan['billing_cycle'] ?? 'monthly'));
        $this->db->beginTransaction();
        try {
            $this->db->query(
                "UPDATE qr_user_subscriptions
                 SET status = 'cancelled', updated_at = NOW()
                 WHERE user_id = ? AND status = 'active'",
                [$userId]
            );
            $subId = $this->db->insert('qr_user_subscriptions', [
                'user_id' => $userId,
                'plan_id' => $planId,
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            Logger::error('activateQrSubscription transaction failed: ' . $e->getMessage());
            return 0;
        }
        Notification::send($userId, 'plan_subscribed', 'Your QR Generator "' . $plan['name'] . '" subscription is now active.', ['plan_id' => $planId, 'plan_name' => $plan['name']]);
        Logger::activity($adminId, 'qr_subscription_activated', ['user_id' => $userId, 'plan_id' => $planId, 'subscription_id' => $subId]);
        return $subId;
    }

    private function activateConvertXSubscription(int $userId, int $planId, int $adminId): int
    {
        $plan = $this->db->fetch("SELECT * FROM convertx_subscription_plans WHERE id = ? LIMIT 1", [$planId]);
        if (!$plan) {
            return 0;
        }
        $expiresAt = $this->calculateExpiry((string) ($plan['billing_cycle'] ?? 'monthly'));
        $this->db->beginTransaction();
        try {
            $this->db->query(
                "UPDATE convertx_user_subscriptions
                 SET status = 'cancelled', updated_at = NOW()
                 WHERE user_id = ? AND status = 'active'",
                [$userId]
            );
            $subId = $this->db->insert('convertx_user_subscriptions', [
                'user_id' => $userId,
                'plan_id' => $planId,
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'assigned_by' => $adminId,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            Logger::error('activateConvertXSubscription transaction failed: ' . $e->getMessage());
            return 0;
        }
        Notification::send($userId, 'plan_subscribed', 'Your ConvertX "' . $plan['name'] . '" subscription is now active.', ['plan_id' => $planId, 'plan_name' => $plan['name']]);
        Logger::activity($adminId, 'convertx_subscription_activated', ['user_id' => $userId, 'plan_id' => $planId, 'subscription_id' => $subId]);
        return $subId;
    }

    private function activateWhatsAppSubscription(int $userId, int $planId, int $adminId): int
    {
        $plan = $this->db->fetch("SELECT * FROM whatsapp_subscription_plans WHERE id = ? LIMIT 1", [$planId]);
        if (!$plan) {
            return 0;
        }
        $durationDays = max(1, (int) ($plan['duration_days'] ?? 30));
        $start = date('Y-m-d H:i:s');
        $end = date('Y-m-d H:i:s', strtotime('+' . $durationDays . ' days'));
        $planType = strtolower(trim(str_replace(' plan', '', strtolower((string) $plan['name']))));

        try {
            $cols = array_column($this->db->fetchAll("SHOW COLUMNS FROM whatsapp_subscriptions"), 'Field');
            if (!in_array('plan_id', $cols, true)) {
                $this->db->query("ALTER TABLE whatsapp_subscriptions ADD COLUMN `plan_id` INT NULL AFTER `user_id`");
            }
        } catch (\Throwable $e) {
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "UPDATE whatsapp_subscriptions
                 SET status = 'cancelled', updated_at = NOW()
                 WHERE user_id = ? AND status = 'active'",
                [$userId]
            );
            $subId = $this->db->insert('whatsapp_subscriptions', [
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_type' => $planType ?: 'free',
                'status' => 'active',
                'start_date' => $start,
                'end_date' => $end,
                'messages_limit' => (int) ($plan['messages_limit'] ?? 0),
                'sessions_limit' => (int) ($plan['sessions_limit'] ?? 0),
                'api_calls_limit' => (int) ($plan['api_calls_limit'] ?? 0),
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            Logger::error('activateWhatsAppSubscription transaction failed: ' . $e->getMessage());
            return 0;
        }
        Notification::send($userId, 'plan_subscribed', 'Your WhatsApp API "' . $plan['name'] . '" subscription is now active.', ['plan_id' => $planId, 'plan_name' => $plan['name']]);
        Logger::activity($adminId, 'whatsapp_subscription_activated', ['user_id' => $userId, 'plan_id' => $planId, 'subscription_id' => $subId]);
        return $subId;
    }

    public function renderInvoiceHtmlForPayment(array $payment, array $user): string
    {
        $settings  = $this->getInvoiceSettings(true);
        $accent    = htmlspecialchars($settings['invoice_accent_color'] ?? '#0077cc');
        $brandName = htmlspecialchars($settings['invoice_company_name'] ?: (defined('APP_NAME') ? APP_NAME : 'MMB Platform'));
        $brandLogo = $settings['invoice_logo_url'] ?? $settings['invoice_logo'] ?? '';
        $cur       = htmlspecialchars($payment['currency'] ?? 'USD');
        $basePrice = (float) ($payment['amount'] ?? 0);

        // Tax calculation
        $taxEnabled = ($settings['invoice_tax_enabled'] ?? '0') === '1';
        $taxRate    = max(0, (float) ($settings['invoice_tax_rate'] ?? 0));
        $taxLabel   = htmlspecialchars($settings['invoice_tax_label'] ?? 'Tax');
        $taxAmount  = $taxEnabled && $taxRate > 0 ? round($basePrice * $taxRate / 100, 2) : 0.0;
        $totalPrice = $basePrice + $taxAmount;

        $price     = number_format($basePrice, 2);
        $taxAmtFmt = number_format($taxAmount, 2);
        $totalFmt  = number_format($totalPrice, 2);

        $invoiceNo   = htmlspecialchars($payment['invoice_no'] ?? ('INV-' . strtoupper(substr(md5((string) ($payment['id'] ?? 0)), 0, 8))));
        $paidAt      = (string) ($payment['paid_at'] ?: $payment['created_at'] ?: 'now');
        $dateFormatted = date('F j, Y', strtotime($paidAt));
        $paidAtTimeFormatted = date('g:i A', strtotime($paidAt));
        $createdAtTimeFormatted = date('g:i A', strtotime((string) ($payment['created_at'] ?: 'now')));
        $createdDate = date('F j, Y \a\t g:i A', strtotime((string) ($payment['created_at'] ?: 'now')));
        $expiry    = !empty($payment['expires_at']) ? date('F j, Y', strtotime((string) $payment['expires_at'])) : ($this->resolveSubscriptionExpiry((string) ($payment['app_key'] ?? 'platform'), (int) ($payment['subscription_id'] ?? 0)) ?: 'Lifetime');
        $userName  = htmlspecialchars($user['name'] ?? $user['username'] ?? 'User');
        $userEmail = htmlspecialchars($user['email'] ?? '');
        $userPhone = htmlspecialchars($user['phone'] ?? '');
        $planName  = htmlspecialchars($payment['plan_name'] ?? 'Subscription');
        $billing   = htmlspecialchars((string) ($payment['billing_cycle'] ?? 'one-time'));
        $status    = htmlspecialchars((string) ($payment['status'] ?? 'paid'));
        $footer    = nl2br(htmlspecialchars($settings['invoice_footer_note'] ?? ''));
        $terms     = nl2br(htmlspecialchars($settings['invoice_terms'] ?? ''));
        $gateway   = htmlspecialchars(strtoupper((string) ($payment['gateway'] ?? '')));
        $reference = htmlspecialchars((string) ($payment['reference'] ?? ''));
        $invoiceTitle = htmlspecialchars((string) ($settings['invoice_title'] ?? 'Subscription Invoice'));
        $invoiceSubtitle = htmlspecialchars((string) ($settings['invoice_subtitle'] ?? 'Secure payment receipt'));
        $invoiceItemLabel = htmlspecialchars((string) ($settings['invoice_item_label'] ?? 'Subscription'));
        $invoiceTotalLabel = htmlspecialchars((string) ($settings['invoice_total_label'] ?? 'Total'));

        // User billing address (from user_billing_details if available)
        $billAddress = '';
        try {
            $bd = $this->db->fetch(
                "SELECT full_name, email, phone, address_line1, address_line2, city, state, postal_code, country
                 FROM user_billing_details
                 WHERE user_id = ? LIMIT 1",
                [(int) ($user['id'] ?? 0)]
            );
            if ($bd) {
                $parts = array_filter([
                    htmlspecialchars($bd['address_line1'] ?? ''),
                    htmlspecialchars($bd['address_line2'] ?? ''),
                    implode(', ', array_filter([
                        htmlspecialchars($bd['city'] ?? ''),
                        htmlspecialchars($bd['state'] ?? ''),
                        htmlspecialchars($bd['postal_code'] ?? ''),
                    ])),
                    htmlspecialchars($bd['country'] ?? ''),
                ]);
                $billAddress = implode('<br>', $parts);
                if (!empty($bd['full_name'])) {
                    $userName = htmlspecialchars($bd['full_name']);
                }
                if (!empty($bd['email'])) {
                    $userEmail = htmlspecialchars($bd['email']);
                }
                if (!empty($bd['phone'])) {
                    $userPhone = htmlspecialchars($bd['phone']);
                }
            }
        } catch (\Throwable $e) {}

        $companyInfo = implode('<br>', array_filter([
            htmlspecialchars($settings['invoice_company_email'] ?? ''),
            htmlspecialchars($settings['invoice_company_phone'] ?? ''),
            nl2br(htmlspecialchars($settings['invoice_company_address'] ?? '')),
        ]));
        $logoHtml = $brandLogo ? '<img src="' . htmlspecialchars($brandLogo) . '" alt="Invoice Logo" style="max-width:160px;max-height:64px;margin-bottom:12px;">' : '';

        $taxRow = '';
        if ($taxEnabled && $taxRate > 0) {
            $taxRow = "<tr class=\"tax-row\"><td colspan=\"2\">{$taxLabel} ({$taxRate}%)</td><td style=\"text-align:right;\">{$cur} {$taxAmtFmt}</td></tr>";
        }

        $userPhoneRow = $userPhone ? "<p><strong>Phone</strong>{$userPhone}</p>" : '';
        $billAddressRow = $billAddress ? "<p><strong>Address</strong><span style=\"display:block;margin-top:2px;\">{$billAddress}</span></p>" : '';

        $refRow = $reference !== '' ? "<div><strong style=\"color:#aaa;font-size:.72rem;font-weight:700;\">REF</strong> {$reference}</div>" : '';
        $statusClass = $status === 'paid' ? 'status-paid' : (in_array($status, ['pending', 'verification_pending'], true) ? 'status-pending' : 'status-other');
        $footerContent = ($footer || $terms)
            ? '<div class="footer-text">'
                . ($footer ? "<div>{$footer}</div>" : '')
                . ($terms ? "<div style=\"margin-top:8px;\">{$terms}</div>" : '')
              . '</div>'
            : '';
        $appLabel = htmlspecialchars($this->getAppLabel((string) ($payment['app_key'] ?? 'platform')));
        $layoutOrder = json_decode((string) ($settings['invoice_layout_blocks'] ?? ''), true);
        $allowedLayoutKeys = ['bill_to', 'subscription_details', 'line_items', 'footer_notes'];
        if (!is_array($layoutOrder)) {
            $layoutOrder = $allowedLayoutKeys;
        }
        $normalizedLayoutOrder = [];
        foreach ($layoutOrder as $item) {
            $key = (string) $item;
            if (in_array($key, $allowedLayoutKeys, true)) {
                $normalizedLayoutOrder[] = $key;
            }
        }
        $layoutOrder = array_values(array_unique($normalizedLayoutOrder));
        foreach ($allowedLayoutKeys as $layoutKey) {
            if (!in_array($layoutKey, $layoutOrder, true)) {
                $layoutOrder[] = $layoutKey;
            }
        }
        $layoutBlocks = [
            'bill_to' => <<<HTML
<div class="invoice-section">
  <div class="block-title">Bill To</div>
  <p><strong>Name</strong>{$userName}</p>
  <p><strong>Email</strong>{$userEmail}</p>
  {$userPhoneRow}
  {$billAddressRow}
</div>
HTML,
            'subscription_details' => <<<HTML
<div class="invoice-section">
  <div class="block-title">Subscription Details</div>
  <p><strong>Application</strong>{$appLabel}</p>
  <p><strong>Plan</strong>{$planName}</p>
  <p><strong>Billing Cycle</strong>{$billing}</p>
  <p><strong>Started</strong>{$dateFormatted}</p>
  <p><strong>Expires</strong>{$expiry}</p>
  <p><strong>Ordered</strong>{$createdDate}</p>
</div>
HTML,
            'line_items' => <<<HTML
<div class="invoice-section invoice-section--wide">
  <table>
    <thead>
      <tr><th>Description</th><th>Billing</th><th style="text-align:right;">Amount</th></tr>
    </thead>
    <tbody>
      <tr><td>{$planName} {$invoiceItemLabel}</td><td>{$billing}</td><td style="text-align:right;">{$cur} {$price}</td></tr>
    </tbody>
    <tfoot>
      {$taxRow}
      <tr class="total-row"><td colspan="2">{$invoiceTotalLabel}</td><td style="text-align:right;">{$cur} {$totalFmt}</td></tr>
    </tfoot>
  </table>
</div>
HTML,
            'footer_notes' => $footerContent !== ''
                ? '<div class="invoice-section invoice-section--wide">' . $footerContent . '</div>'
                : '',
        ];
        $renderedLayoutBlocks = '';
        foreach ($layoutOrder as $layoutKey) {
            if (!empty($layoutBlocks[$layoutKey])) {
                $renderedLayoutBlocks .= $layoutBlocks[$layoutKey];
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Invoice {$invoiceNo}</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Segoe UI',Helvetica,Arial,sans-serif; background:#f5f7fc; color:#1a1a2e; min-height:100vh; }
.page-wrap { max-width:800px; margin:0 auto; padding:32px 20px 60px; }
.invoice-card { background:#fff; border-radius:16px; box-shadow:0 4px 32px rgba(0,0,0,.10); overflow:hidden; }
.inv-top { background:linear-gradient(135deg,{$accent}18,{$accent}06); border-bottom:2px solid {$accent}22; padding:32px 36px; display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap; }
.brand-col { display:flex; flex-direction:column; gap:6px; }
.brand-name { font-size:1.5rem; font-weight:800; color:{$accent}; }
.brand-sub  { font-size:.8rem; color:#888; }
.company-info { font-size:.8rem; color:#666; line-height:1.6; margin-top:8px; }
.inv-meta-col { text-align:right; }
.inv-number { font-size:1.2rem; font-weight:800; color:{$accent}; }
.inv-dates { font-size:.82rem; color:#666; margin-top:6px; line-height:1.7; }
.status-paid   { display:inline-block; background:#e6ffed; color:#1a7a3e; padding:3px 14px; border-radius:20px; font-size:.78rem; font-weight:700; margin-top:8px; }
.status-pending { display:inline-block; background:#fff3cd; color:#856404; padding:3px 14px; border-radius:20px; font-size:.78rem; font-weight:700; margin-top:8px; }
.status-other  { display:inline-block; background:#f0f4ff; color:#444; padding:3px 14px; border-radius:20px; font-size:.78rem; font-weight:700; margin-top:8px; }
.inv-body { padding:32px 36px; }
.inv-layout { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
.invoice-section { border:1px solid #edf0f7; border-radius:12px; padding:16px; background:#fff; }
.invoice-section--wide { grid-column:1 / -1; }
.invoice-section .block-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#aaa; margin-bottom:10px; }
.invoice-section p { font-size:.87rem; line-height:1.7; color:#333; }
.invoice-section strong { display:block; font-size:.72rem; color:#aaa; font-weight:600; margin-top:6px; }
table { width:100%; border-collapse:collapse; font-size:.88rem; }
thead tr { background:linear-gradient(135deg,{$accent}12,{$accent}04); }
th { padding:11px 16px; text-align:left; font-size:.76rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#666; border-bottom:2px solid {$accent}22; }
td { padding:12px 16px; border-bottom:1px solid #f0f3fa; color:#333; }
tbody tr:last-child td { border-bottom:none; }
.tax-row td { background:#fafbff; color:#666; font-size:.85rem; }
.total-row { }
.total-row td { font-weight:800; font-size:1rem; color:{$accent}; padding:14px 16px; border-top:2px solid {$accent}22; background:linear-gradient(135deg,{$accent}08,{$accent}03); }
.inv-footer { padding:22px 36px; border-top:1px solid #edf0f7; background:#fafbff; display:flex; justify-content:flex-end; }
.footer-text { font-size:.78rem; color:#777; line-height:1.6; }
.print-btn { margin-top:12px; padding:8px 20px; background:{$accent}; color:#fff; border:none; border-radius:7px; font-size:.82rem; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.print-btn:hover { opacity:.88; }
@media (max-width:580px) {
  .inv-top, .inv-body, .inv-footer { padding:20px; }
  .inv-layout { grid-template-columns:1fr; }
  .inv-meta-col { text-align:left; }
}
@media print {
  body { background:#fff; }
  .page-wrap { padding:0; }
  .invoice-card { box-shadow:none; border-radius:0; }
  .print-btn { display:none; }
}
</style>
</head>
<body>
<div class="page-wrap">
<div class="invoice-card">
  <!-- Header -->
  <div class="inv-top">
    <div class="brand-col">
      {$logoHtml}
      <div class="brand-name">{$brandName}</div>
      <div class="brand-sub">{$invoiceSubtitle}</div>
      <div class="company-info">{$companyInfo}</div>
    </div>
    <div class="inv-meta-col">
      <div class="inv-number">{$invoiceNo}</div>
        <div class="inv-dates">
          <div><strong style="color:#aaa;font-size:.72rem;font-weight:700;">ISSUED</strong> {$dateFormatted}</div>
          <div><strong style="color:#aaa;font-size:.72rem;font-weight:700;">PAYMENT TIME</strong> {$paidAtTimeFormatted}</div>
          <div><strong style="color:#aaa;font-size:.72rem;font-weight:700;">INVOICE TIME</strong> {$createdAtTimeFormatted}</div>
          <div><strong style="color:#aaa;font-size:.72rem;font-weight:700;">GATEWAY</strong> {$gateway}</div>
          {$refRow}
        </div>
      <div class="{$statusClass}">{$status}</div>
    </div>
  </div>

  <div class="inv-body">
    <h2 style="font-size:1.05rem;margin-bottom:14px;color:{$accent};">{$invoiceTitle}</h2>
    <div class="inv-layout">
      {$renderedLayoutBlocks}
    </div>
  </div>

  <div class="inv-footer">
    <button class="print-btn" onclick="window.print()">&#x1F5A8; Print / Save as PDF</button>
  </div>
</div>
</div>
</body>
</html>
HTML;
    }

    private function resolveSubscriptionExpiry(string $appKey, int $subscriptionId): ?string
    {
        try {
            $config = $this->getAppConfig($appKey);
            if ($config === null || empty($config['subscription_expires_column'])) {
                return null;
            }
            $row = $this->db->fetch(
                "SELECT {$config['subscription_expires_column']} AS expires_at FROM {$config['subscription_table']} WHERE id = ? LIMIT 1",
                [$subscriptionId]
            );
            if (!empty($row['expires_at'])) {
                return date('F j, Y', strtotime((string) $row['expires_at']));
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    private function resolveSubscriptionExpiryRaw(string $appKey, int $subscriptionId): ?string
    {
        try {
            $config = $this->getAppConfig($appKey);
            if ($config === null || empty($config['subscription_expires_column'])) {
                return null;
            }
            $row = $this->db->fetch(
                "SELECT {$config['subscription_expires_column']} AS expires_at FROM {$config['subscription_table']} WHERE id = ? LIMIT 1",
                [$subscriptionId]
            );
            if (!empty($row['expires_at'])) {
                return (string) $row['expires_at'];
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    private function ensurePlatformPlanTables(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `platform_plans` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) UNIQUE NOT NULL,
                `description` TEXT NULL,
                `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
                `billing_cycle` ENUM('monthly','yearly','lifetime') DEFAULT 'monthly',
                `cancel_days` INT NOT NULL DEFAULT 0,
                `refund_days` INT NOT NULL DEFAULT 0,
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
                INDEX `idx_plan_id` (`plan_id`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        try {
            $cols = array_column($this->db->fetchAll("SHOW COLUMNS FROM platform_plans"), 'Field');
            if (!in_array('currency', $cols, true)) {
                $this->db->query("ALTER TABLE platform_plans ADD COLUMN `currency` VARCHAR(3) NOT NULL DEFAULT 'USD' AFTER `price`");
            }
            if (!in_array('cancel_days', $cols, true)) {
                $this->db->query("ALTER TABLE platform_plans ADD COLUMN `cancel_days` INT NOT NULL DEFAULT 0 AFTER `billing_cycle`");
            }
            if (!in_array('refund_days', $cols, true)) {
                $this->db->query("ALTER TABLE platform_plans ADD COLUMN `refund_days` INT NOT NULL DEFAULT 0 AFTER `cancel_days`");
            }
        } catch (\Throwable $e) {
        }
    }

    private function calculateExpiry(string $billingCycle): ?string
    {
        return match ($billingCycle) {
            'monthly' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'yearly' => date('Y-m-d H:i:s', strtotime('+1 year')),
            default => null,
        };
    }

    private function decryptStoredSecret(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $decrypted = Security::decrypt($value);
        return ($decrypted === false || $decrypted === '') ? $value : $decrypted;
    }

    private function generateUniqueToken(string $prefix): string
    {
        do {
            $value = $prefix . strtoupper(bin2hex(random_bytes(4)));
            $exists = $this->db->fetch(
                "SELECT id FROM subscription_payments WHERE invoice_no = ? OR reference = ? LIMIT 1",
                [$value, $value]
            );
        } while ($exists);

        return $value;
    }

    private function absoluteUrl(string $path): string
    {
        $base = defined('APP_URL') && APP_URL ? rtrim(APP_URL, '/') : '';
        if ($base !== '') {
            return $base . $path;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $path;
    }

    private function validateReturnUrl(string $returnUrl): ?string
    {
        if (!filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $urlHost = parse_url($returnUrl, PHP_URL_HOST);
        $appUrl = defined('APP_URL') && APP_URL ? APP_URL : null;
        $allowedHost = $appUrl ? parse_url($appUrl, PHP_URL_HOST) : ($_SERVER['HTTP_HOST'] ?? null);

        if (!$urlHost || !$allowedHost || strcasecmp((string) $urlHost, (string) $allowedHost) !== 0) {
            return null;
        }

        return $returnUrl;
    }

    private function appendQueryParams(string $url, array $params): string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $existing = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $existing);
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':' . $parts['pass']  : '';
        $pass = ($user !== '' || $pass !== '') ? $pass . '@' : '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        // Build query string: encode existing params normally, but preserve
        // Cashfree-style template placeholders ({order_id}, etc.) verbatim so that
        // http_build_query / urlencode does not turn {order_id} into %7Border_id%7D.
        // Cashfree requires the literal string {order_id} in the return_url for its
        // server-side substitution to work; URL-encoding the braces breaks the mechanism.
        $allParams = array_merge($existing, $params);
        $queryParts = [];
        foreach ($allParams as $key => $value) {
            $encodedKey = urlencode((string) $key);
            // Preserve any value that is exactly a {placeholder} token.
            if (preg_match('/^\{[^{}]+\}$/', (string) $value)) {
                $queryParts[] = $encodedKey . '=' . $value;
            } else {
                $queryParts[] = $encodedKey . '=' . urlencode((string) $value);
            }
        }
        $query = implode('&', $queryParts);

        return $scheme . $user . $pass . $host . $port . $path . ($query !== '' ? '?' . $query : '') . $fragment;
    }

    private function ensureProjectPlanPolicyColumns(): void
    {
        foreach (self::APP_CONFIG as $appKey => $config) {
            if (empty($config['plan_table']) || empty($config['plan_cancel_days_column']) || empty($config['plan_refund_days_column'])) {
                continue;
            }
            try {
                $cols = array_column($this->db->fetchAll("SHOW COLUMNS FROM {$config['plan_table']}"), 'Field');
                if (!in_array($config['plan_cancel_days_column'], $cols, true)) {
                    $this->db->query("ALTER TABLE {$config['plan_table']} ADD COLUMN `{$config['plan_cancel_days_column']}` INT NOT NULL DEFAULT 0");
                }
                if (!in_array($config['plan_refund_days_column'], $cols, true)) {
                    $this->db->query("ALTER TABLE {$config['plan_table']} ADD COLUMN `{$config['plan_refund_days_column']}` INT NOT NULL DEFAULT 0");
                }
                if ($appKey === 'whatsapp' && !in_array('plan_id', array_column($this->db->fetchAll("SHOW COLUMNS FROM whatsapp_subscriptions"), 'Field'), true)) {
                    $this->db->query("ALTER TABLE whatsapp_subscriptions ADD COLUMN `plan_id` INT NULL AFTER `user_id`");
                }
                if ($appKey === 'whatsapp' && !in_array('slug', $cols, true)) {
                    $this->db->query("ALTER TABLE whatsapp_subscription_plans ADD COLUMN `slug` VARCHAR(80) NULL AFTER `name`");
                    $plans = $this->db->fetchAll("SELECT id, name FROM whatsapp_subscription_plans");
                    foreach ($plans as $plan) {
                        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', str_replace(' plan', '', strtolower((string) $plan['name']))), '-'));
                        $this->db->query("UPDATE whatsapp_subscription_plans SET slug = ? WHERE id = ?", [$slug ?: ('plan-' . $plan['id']), $plan['id']]);
                    }
                }
            } catch (\Throwable $e) {
            }
        }
    }

    private function getAppConfig(string $appKey): ?array
    {
        return self::APP_CONFIG[$appKey] ?? null;
    }

    private function getAppLabel(string $appKey): string
    {
        return htmlspecialchars((string) (self::APP_CONFIG[$appKey]['label'] ?? strtoupper($appKey)));
    }

    private function normalizePlanRow(string $appKey, array $row): array
    {
        $row['app_key'] = $appKey;
        $row['currency'] = $this->normalizeCurrencyValue($row['currency'] ?? null);
        $row['cancel_days'] = (int) ($row['cancel_days'] ?? 0);
        $row['refund_days'] = (int) ($row['refund_days'] ?? 0);
        if ($appKey === 'whatsapp') {
            $row['slug'] = $row['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', str_replace(' plan', '', strtolower((string) ($row['name'] ?? '')))), '-'));
            $row['billing_cycle'] = ($row['duration_days'] ?? 30) . ' days';
        }
        return $row;
    }

    private function normalizeSubscriptionRow(string $appKey, array $row): array
    {
        $row['app_key'] = $appKey;
        $row['currency'] = $this->normalizeCurrencyValue($row['currency'] ?? null);
        return $row;
    }

    private function decodePaymentMetadata(array $payment): array
    {
        $decoded = json_decode((string) ($payment['metadata_json'] ?? '{}'), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeCurrencyValue(mixed $currency): string
    {
        $value = strtoupper(trim((string) $currency));
        return $value !== '' ? $value : 'USD';
    }
}
