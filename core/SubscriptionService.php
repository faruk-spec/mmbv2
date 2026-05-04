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
        } catch (\Throwable $e) {
        }

        $this->ensureProjectPlanPolicyColumns();
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
        ];

        try {
            $rows = $this->db->fetchAll("SELECT `key`, value FROM settings WHERE `key` LIKE 'payment_%'");
            foreach ($rows as $row) {
                $defaults[$row['key']] = $row['value'];
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
        $expiresAt = $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+1 day'));

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

    public function cancelSubscriptionByPayment(int $paymentId, int $userId): bool
    {
        $payment = $this->getUserPayment($paymentId, $userId);
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

        $this->db->query(
            "UPDATE subscription_payments
             SET refund_status = ?, refund_decided_at = NOW(), admin_notes = ?, updated_at = NOW()
             WHERE id = ?",
            [$decision, $note, $paymentId]
        );
        Logger::activity($adminId, 'subscription_refund_reviewed', ['payment_id' => $paymentId, 'decision' => $decision]);
        return true;
    }

    /**
     * Return the configured refund window (in days) for a payment.
     * Falls back to 7 days when not configured.
     */
    public function getCancelRefundWindowDays(array $payment): int
    {
        $meta = $this->decodePaymentMetadata($payment);
        return (int) ($meta['refund_days'] ?? 7);
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

        $this->db->query(
            "UPDATE subscription_payments
             SET status = 'paid', subscription_id = ?, paid_at = NOW(), updated_at = NOW()
             WHERE id = ?",
            [$subscriptionId, $paymentId]
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
        $validatedReturnUrl = $this->validateReturnUrl($returnUrl);
        if ($validatedReturnUrl === null) {
            return ['success' => false, 'message' => 'Invalid Cashfree return URL.'];
        }

        $baseUrl = ($settings['payment_cashfree_sandbox'] ?? '1') === '1'
            ? 'https://sandbox.cashfree.com/pg/orders'
            : 'https://api.cashfree.com/pg/orders';

        $orderId = 'order_' . strtolower($payment['reference']);

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
            'order_amount' => (float) $payment['amount'],
            'order_currency' => (string) $payment['currency'],
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

        $this->db->query(
            "UPDATE subscription_payments
             SET provider_order_id = ?, provider_payment_session_id = ?, payment_url = ?, payment_payload = ?, updated_at = NOW()
             WHERE id = ?",
            [$orderId, $sessionId, $paymentUrl, $response, (int) $payment['id']]
        );

        return [
            'success' => true,
            'order_id' => $orderId,
            'payment_session_id' => $sessionId,
            'payment_url' => $paymentUrl,
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
        $price     = number_format((float) ($payment['amount'] ?? 0), 2);
        $invoiceNo = htmlspecialchars($payment['invoice_no'] ?? ('INV-' . strtoupper(substr(md5((string) ($payment['id'] ?? 0)), 0, 8))));
        $date      = date('F j, Y', strtotime((string) ($payment['paid_at'] ?: $payment['created_at'] ?: 'now')));
        $expiry    = !empty($payment['expires_at']) ? date('F j, Y', strtotime((string) $payment['expires_at'])) : ($this->resolveSubscriptionExpiry((string) ($payment['app_key'] ?? 'platform'), (int) ($payment['subscription_id'] ?? 0)) ?: 'Lifetime');
        $userName  = htmlspecialchars($user['name'] ?? $user['username'] ?? 'User');
        $userEmail = htmlspecialchars($user['email'] ?? '');
        $planName  = htmlspecialchars($payment['plan_name'] ?? 'Subscription');
        $billing   = htmlspecialchars((string) ($payment['billing_cycle'] ?? 'one-time'));
        $status    = htmlspecialchars((string) ($payment['status'] ?? 'paid'));
        $footer    = nl2br(htmlspecialchars($settings['invoice_footer_note'] ?? ''));
        $terms     = nl2br(htmlspecialchars($settings['invoice_terms'] ?? ''));
        $companyInfo = implode('<br>', array_filter([
            htmlspecialchars($settings['invoice_company_email'] ?? ''),
            htmlspecialchars($settings['invoice_company_phone'] ?? ''),
            nl2br(htmlspecialchars($settings['invoice_company_address'] ?? '')),
        ]));
        $logoHtml = $brandLogo ? '<img src="' . htmlspecialchars($brandLogo) . '" alt="Invoice Logo" style="max-width:160px;max-height:64px;margin-bottom:12px;">' : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {$invoiceNo}</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Segoe UI',Arial,sans-serif; background:#fff; color:#1a1a2e; padding:40px; max-width:760px; margin:0 auto; }
.invoice-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:36px; border-bottom:2px solid {$accent}; padding-bottom:24px; gap:24px; }
.brand { font-size:1.5rem; font-weight:800; color:{$accent}; }
.company { font-size:.82rem; color:#666; margin-top:8px; line-height:1.5; }
.inv-meta { text-align:right; }
.inv-meta h2 { font-size:1.1rem; color:{$accent}; }
.inv-meta p { font-size:.85rem; color:#666; margin-top:4px; }
.section { margin-bottom:28px; }
.section-title { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#888; margin-bottom:8px; }
.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.info-block p { font-size:.9rem; line-height:1.6; }
.info-block strong { display:block; font-size:.78rem; color:#888; margin-bottom:2px; }
table { width:100%; border-collapse:collapse; margin-bottom:20px; }
th { background:#f0f4ff; padding:10px 14px; text-align:left; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#444; }
td { padding:12px 14px; border-bottom:1px solid #e8edf5; font-size:.9rem; }
.total-row td { font-weight:700; font-size:1rem; background:#f8faff; }
.status-badge { background:#e6ffed; color:#1a7a3e; padding:3px 12px; border-radius:20px; font-size:.78rem; font-weight:700; }
.footer { margin-top:40px; padding-top:20px; border-top:1px solid #e8edf5; font-size:.78rem; color:#999; }
@media print { body { padding:20px; } }
</style>
</head>
<body>
<div class="invoice-header">
    <div>
        {$logoHtml}
        <div class="brand">{$brandName}</div>
        <div style="font-size:.82rem;color:#666;margin-top:4px;">Subscription Invoice</div>
        <div class="company">{$companyInfo}</div>
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
        <p><strong>Application</strong>{$this->getAppLabel((string) ($payment['app_key'] ?? 'platform'))}</p>
        <p><strong>Plan</strong>{$planName}</p>
        <p><strong>Started</strong>{$date}</p>
        <p><strong>Expires</strong>{$expiry}</p>
        <p><strong>Status</strong><span class="status-badge">{$status}</span></p>
    </div>
</div>
<div class="section">
    <table>
        <thead><tr><th>Description</th><th>Billing</th><th style="text-align:right;">Amount</th></tr></thead>
        <tbody><tr><td>{$planName} Subscription</td><td>{$billing}</td><td style="text-align:right;">{$cur} {$price}</td></tr></tbody>
        <tfoot><tr class="total-row"><td colspan="2">Total</td><td style="text-align:right;">{$cur} {$price}</td></tr></tfoot>
    </table>
</div>
<div class="footer">
    <div>{$footer}</div>
    <div style="margin-top:10px;">{$terms}</div>
    <div style="margin-top:10px;"><button onclick="window.print()" style="color:{$accent};background:none;border:none;cursor:pointer;font-size:.78rem;padding:0;text-decoration:underline;">🖨 Print / Save as PDF</button></div>
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
