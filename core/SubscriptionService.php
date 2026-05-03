<?php

namespace Core;

class SubscriptionService
{
    private Database $db;

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
                    )
            ");
        } catch (\Throwable $e) {
            Logger::error('SubscriptionService::ensureNotificationTemplates - ' . $e->getMessage());
        }
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
        return $this->db->fetch(
            "SELECT * FROM subscription_payments WHERE id = ? AND user_id = ? LIMIT 1",
            [$paymentId, $userId]
        ) ?: null;
    }

    public function getUserPayments(int $userId, ?string $appKey = null): array
    {
        if ($appKey !== null) {
            return $this->db->fetchAll(
                "SELECT * FROM subscription_payments WHERE user_id = ? AND app_key = ? ORDER BY id DESC",
                [$userId, $appKey]
            );
        }

        return $this->db->fetchAll(
            "SELECT * FROM subscription_payments WHERE user_id = ? ORDER BY id DESC",
            [$userId]
        );
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

        $subscriptionId = $payment['app_key'] === 'resumex'
            ? $this->activateResumeXSubscription((int) $payment['user_id'], (int) $payment['plan_id'], $adminId)
            : $this->activatePlatformSubscription((int) $payment['user_id'], (int) $payment['plan_id'], $adminId);

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
        $payload = [
            'order_id' => $orderId,
            'order_amount' => (float) $payment['amount'],
            'order_currency' => (string) $payment['currency'],
            'customer_details' => [
                'customer_id' => 'user_' . (int) $payment['user_id'],
                'customer_name' => (string) ($customer['name'] ?? 'Customer'),
                'customer_email' => (string) ($customer['email'] ?? ''),
                'customer_phone' => (string) ($customer['phone'] ?? '9999999999'),
            ],
            'order_meta' => [
                'return_url' => $validatedReturnUrl . (str_contains($validatedReturnUrl, '?') ? '&' : '?') . 'order_id={order_id}',
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
                return;
            }

            $invoiceUrl = $payment['app_key'] === 'resumex'
                ? '/projects/resumex/plans/invoice/' . (int) $payment['subscription_id']
                : '/plans/invoice/' . (int) $payment['subscription_id'];
            $dashboardUrl = $payment['app_key'] === 'resumex' ? '/projects/resumex/plans' : '/plans';
            $expiresAt = $this->resolveSubscriptionExpiry((string) $payment['app_key'], (int) $payment['subscription_id']);

            MailService::sendNotification(
                (string) $payment['user_email'],
                'subscription-confirmed',
                [
                    'user_name' => $payment['user_name'] ?? 'User',
                    'plan_name' => $payment['plan_name'],
                    'app_name' => $payment['app_key'] === 'resumex' ? 'ResumeX' : 'MMB Platform',
                    'currency' => $payment['currency'],
                    'amount' => number_format((float) $payment['amount'], 2, '.', ''),
                    'billing_cycle' => $payment['billing_cycle'] ?: 'one-time',
                    'started_at' => date('F j, Y'),
                    'expires_at' => $expiresAt ?: 'Lifetime',
                    'invoice_url' => $this->absoluteUrl($invoiceUrl),
                    'dashboard_url' => $this->absoluteUrl($dashboardUrl),
                ],
                true
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

    private function resolveSubscriptionExpiry(string $appKey, int $subscriptionId): ?string
    {
        $table = $appKey === 'resumex' ? 'resumex_user_subscriptions' : 'platform_user_subscriptions';
        try {
            $row = $this->db->fetch("SELECT expires_at FROM {$table} WHERE id = ? LIMIT 1", [$subscriptionId]);
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
}
