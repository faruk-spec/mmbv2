<?php
/**
 * ResumeX Plans Controller (User-facing)
 * Shows available subscription plans and handles subscriptions.
 */

namespace Projects\ResumeX\Controllers;

use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;
use Core\View;
use Core\SubscriptionService;

class PlansController
{
    private Database $db;
    private int $userId;
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        $this->db     = Database::getInstance();
        $this->userId = Auth::id();
        $this->subscriptionService = new SubscriptionService($this->db);
        $this->ensureTables();
        $this->subscriptionService->ensureInfrastructure();
        $this->subscriptionService->ensureNotificationTemplates();
    }

    public function index(): void
    {
        $plans      = $this->getActivePlans();
        $currentSub = $this->getCurrentSubscription();
        $history    = $this->getSubscriptionHistory();
        $settings   = $this->getPaymentSettings();
        $paymentHistory = $this->subscriptionService->getUserPayments($this->userId, 'resumex');

        $this->render('plans', [
            'title'      => 'ResumeX Plans',
            'plans'      => $plans,
            'currentSub' => $currentSub,
            'history'    => $history,
            'settings'   => $settings,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    public function subscribePage(string $slug): void
    {
        $plan = $this->findPlanBySlug($slug);
        if (!$plan) {
            $_SESSION['_flash']['error'] = 'Plan not found.';
            header('Location: /projects/resumex/plans');
            exit;
        }

        $existing = $this->getCurrentSubscription();
        $settings = $this->getPaymentSettings();

        $this->render('plans-subscribe', [
            'title'    => 'Subscribe — ' . $plan['name'],
            'plan'     => $plan,
            'existing' => $existing,
            'settings' => $settings,
        ]);
    }

    public function subscribe(string $slug): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            header('Location: /projects/resumex/plans/' . urlencode($slug));
            exit;
        }

        $plan = $this->findPlanBySlug($slug);
        if (!$plan) {
            $_SESSION['_flash']['error'] = 'Plan not found.';
            header('Location: /projects/resumex/plans');
            exit;
        }

        // Free plan — auto-activate
        if ((float)$plan['price'] === 0.0) {
            $this->activateSubscription($plan, 'auto');
            $_SESSION['_flash']['success'] = 'You are now subscribed to the "' . $plan['name'] . '" plan!';
            Logger::activity($this->userId, 'resumex_subscription_activated', ['plan_slug' => $slug]);
            header('Location: /projects/resumex/plans');
            exit;
        }

        $settings = $this->getPaymentSettings();
        $method   = $_POST['payment_method'] ?? ($settings['payment_method'] ?? 'request');

        $payment = $this->subscriptionService->createOrReusePayment([
            'user_id' => $this->userId,
            'app_key' => 'resumex',
            'plan_id' => (int) $plan['id'],
            'plan_name' => (string) $plan['name'],
            'billing_cycle' => $plan['billing_cycle'] ?? 'monthly',
            'gateway' => in_array($method, ['upi', 'cashfree'], true) ? $method : 'request',
            'status' => $method === 'request' ? 'verification_pending' : 'pending',
            'amount' => (float) $plan['price'],
            'currency' => (string) ($plan['currency'] ?? ($settings['payment_currency'] ?? 'USD')),
            'payment_payload' => $method === 'upi' && !empty($settings['payment_upi_id'])
                ? 'upi://pay?' . http_build_query([
                    'pa' => $settings['payment_upi_id'],
                    'pn' => 'ResumeX',
                    'am' => sprintf('%.2f', (float) $plan['price']),
                    'cu' => $plan['currency'] ?? ($settings['payment_currency'] ?? 'USD'),
                    'tn' => $plan['name'] . ' Plan',
                ])
                : null,
            'metadata' => ['plan_slug' => $slug],
        ]);

        if ($method === 'cashfree'
            && ($settings['payment_cashfree_enabled'] ?? '0') === '1'
            && !empty($settings['payment_cashfree_app_id'])
            && !empty($settings['payment_cashfree_secret'])
        ) {
            $cashfreeResult = $this->subscriptionService->createCashfreeOrder(
                $payment,
                $settings,
                [
                    'name' => Auth::user()['name'] ?? 'Customer',
                    'email' => Auth::user()['email'] ?? '',
                    'phone' => Auth::user()['phone'] ?? '9999999999',
                ],
                $this->buildAbsoluteUrl('/projects/resumex/plans/payment/' . (int) $payment['id'] . '/return')
            );

            if (!$cashfreeResult['success']) {
                $_SESSION['_flash']['error'] = $cashfreeResult['message'] ?? 'Unable to start Cashfree payment.';
                header('Location: /projects/resumex/plans/' . urlencode($slug));
                exit;
            }
        }

        Logger::activity($this->userId, 'resumex_subscription_requested', ['plan_slug' => $slug, 'price' => $plan['price'], 'gateway' => $method]);
        header('Location: /projects/resumex/plans/payment/' . (int) $payment['id']);
        exit;
    }

    public function payment(int $id): void
    {
        $payment = $this->subscriptionService->getUserPayment($id, $this->userId);
        if (!$payment) {
            $_SESSION['_flash']['error'] = 'Payment record not found.';
            header('Location: /projects/resumex/plans');
            exit;
        }

        $this->render('plans-payment', [
            'title' => 'ResumeX Payment',
            'payment' => $payment,
            'settings' => $this->getPaymentSettings(),
        ]);
    }

    public function confirmPayment(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            header('Location: /projects/resumex/plans/payment/' . $id);
            exit;
        }

        if ($this->subscriptionService->markUserPaymentSubmitted($id, $this->userId)) {
            $_SESSION['_flash']['success'] = 'Payment marked as submitted. Admin verification is pending.';
        } else {
            $_SESSION['_flash']['error'] = 'Unable to update payment status.';
        }

        header('Location: /projects/resumex/plans/payment/' . $id);
        exit;
    }

    public function cashfreeReturn(int $id): void
    {
        $payment = $this->subscriptionService->getUserPayment($id, $this->userId);
        if (!$payment) {
            $_SESSION['_flash']['error'] = 'Payment record not found.';
            header('Location: /projects/resumex/plans');
            exit;
        }

        $result = $this->subscriptionService->verifyCashfreePayment($payment, $this->getPaymentSettings());
        if (!empty($result['success']) && !empty($result['paid']) && $this->subscriptionService->approvePayment($id, $this->userId)) {
            $_SESSION['_flash']['success'] = 'Payment received and your ResumeX plan is now active.';
        } elseif (!empty($result['success'])) {
            $_SESSION['_flash']['error'] = 'Cashfree payment is not marked paid yet. Please try again in a moment.';
        } else {
            $_SESSION['_flash']['error'] = $result['message'] ?? 'Unable to verify Cashfree payment.';
        }

        header('Location: /projects/resumex/plans/payment/' . $id);
        exit;
    }

    public function invoice(int $id): void
    {
        try {
            $sub = $this->db->fetch(
                "SELECT s.*, p.name plan_name, p.price, p.currency, p.billing_cycle
                 FROM resumex_user_subscriptions s
                 JOIN resumex_subscription_plans p ON p.id = s.plan_id
                 WHERE s.id = ? AND s.user_id = ?",
                [$id, $this->userId]
            );
        } catch (\Exception $e) {
            $sub = null;
        }

        if (!$sub) {
            header('Location: /projects/resumex/plans');
            exit;
        }

        $user     = Auth::user();
        $cur      = htmlspecialchars($sub['currency'] ?? 'USD');
        $price    = number_format((float)$sub['price'], 2);
        $isFree   = (float)$sub['price'] === 0.0;
        $invoiceNo = 'RX-' . strtoupper(substr(md5($sub['id'] . $sub['started_at']), 0, 8));
        $date     = date('F j, Y', strtotime($sub['started_at']));
        $expiry   = $sub['expires_at'] ? date('F j, Y', strtotime($sub['expires_at'])) : 'Lifetime';
        $userName = htmlspecialchars($user['name'] ?? $user['username'] ?? 'User');
        $userEmail= htmlspecialchars($user['email'] ?? '');
        $planName = htmlspecialchars($sub['plan_name']);
        $billing  = htmlspecialchars($sub['billing_cycle']);
        $status   = htmlspecialchars($sub['status']);
        $noPayment = $isFree ? 'This is a complimentary plan — no payment required.' : 'Payment is processed by your administrator.';

        header('Content-Type: text/html; charset=utf-8');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {$invoiceNo}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1a1a2e; padding: 40px; max-width: 700px; margin: 0 auto; }
.invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #ff6b6b; padding-bottom: 24px; }
.brand { font-size: 1.5rem; font-weight: 800; color: #ff6b6b; }
.inv-meta { text-align: right; }
.inv-meta h2 { font-size: 1.1rem; color: #ff6b6b; }
.inv-meta p { font-size: .85rem; color: #666; margin-top: 4px; }
.section { margin-bottom: 28px; }
.section-title { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #888; margin-bottom: 8px; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.info-block p { font-size: .9rem; line-height: 1.6; }
.info-block strong { display: block; font-size: .78rem; color: #888; margin-bottom: 2px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th { background: #fff0f0; padding: 10px 14px; text-align: left; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #444; }
td { padding: 12px 14px; border-bottom: 1px solid #f5e8e8; font-size: .9rem; }
.total-row td { font-weight: 700; font-size: 1rem; background: #fff8f8; }
.status-badge { background: #e6ffed; color: #1a7a3e; padding: 3px 12px; border-radius: 20px; font-size: .78rem; font-weight: 700; }
.footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #f5e8e8; font-size: .78rem; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="invoice-header">
    <div>
        <div class="brand">ResumeX</div>
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
        <div class="section-title">Subscription</div>
        <p><strong>Plan</strong>{$planName}</p>
        <p><strong>Started</strong>{$date}</p>
        <p><strong>Expires</strong>{$expiry}</p>
        <p><strong>Status</strong><span class="status-badge">{$status}</span></p>
    </div>
</div>
<div class="section">
    <table>
        <thead><tr><th>Description</th><th>Billing</th><th style="text-align:right;">Amount</th></tr></thead>
        <tbody><tr><td>{$planName} &mdash; ResumeX</td><td>{$billing}</td><td style="text-align:right;">{$cur} {$price}</td></tr></tbody>
        <tfoot><tr class="total-row"><td colspan="2">Total</td><td style="text-align:right;">{$cur} {$price}</td></tr></tfoot>
    </table>
    <p style="font-size:.8rem;color:#666;">{$noPayment}</p>
</div>
<div class="footer">
    ResumeX &bull; This is a computer-generated invoice &bull; No signature required<br>
    <button onclick="window.print()" style="color:#ff6b6b;background:none;border:none;cursor:pointer;font-size:.78rem;padding:0;text-decoration:underline;">&#128424; Print / Save as PDF</button>
</div>
</body>
</html>
HTML;
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function activateSubscription(array $plan, string $note = ''): void
    {
        try {
            $this->db->query(
                "UPDATE resumex_user_subscriptions SET status='cancelled', updated_at=NOW() WHERE user_id=? AND status='active'",
                [$this->userId]
            );

            $expiresAt = null;
            if ($plan['billing_cycle'] === 'monthly') {
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));
            } elseif ($plan['billing_cycle'] === 'yearly') {
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));
            }

            $status = $note === 'pending_payment' ? 'trial' : 'active';

            $this->db->query(
                "INSERT INTO resumex_user_subscriptions (user_id, plan_id, status, started_at, expires_at) VALUES (?,?,?,NOW(),?)",
                [$this->userId, $plan['id'], $status, $expiresAt]
            );
        } catch (\Exception $e) {
            Logger::error('ResumeX activateSubscription: ' . $e->getMessage());
        }
    }

    private function getActivePlans(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM resumex_subscription_plans WHERE status='active' ORDER BY sort_order ASC, price ASC"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getCurrentSubscription(): ?array
    {
        try {
            return $this->db->fetch(
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.price, p.currency, p.billing_cycle, p.max_resumes, p.features
                 FROM resumex_user_subscriptions s
                 JOIN resumex_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC LIMIT 1",
                [$this->userId]
            ) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getSubscriptionHistory(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT s.*, p.name plan_name, p.price, p.currency, p.billing_cycle
                 FROM resumex_user_subscriptions s
                 JOIN resumex_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ?
                 ORDER BY s.started_at DESC LIMIT 20",
                [$this->userId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function findPlanBySlug(string $slug): ?array
    {
        try {
            return $this->db->fetch(
                "SELECT * FROM resumex_subscription_plans WHERE slug=? AND status='active' LIMIT 1",
                [$slug]
            ) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getPaymentSettings(): array
    {
        return $this->subscriptionService->getPaymentSettings();
    }

    private function ensureTables(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `resumex_subscription_plans` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `slug` VARCHAR(50) NOT NULL,
                    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    `currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
                    `billing_cycle` ENUM('monthly','yearly','lifetime') DEFAULT 'monthly',
                    `max_resumes` INT DEFAULT 5,
                    `features` TEXT NULL,
                    `is_default` TINYINT(1) DEFAULT 0,
                    `status` ENUM('active','inactive') DEFAULT 'active',
                    `sort_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `unique_slug` (`slug`),
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `resumex_user_subscriptions` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT UNSIGNED NOT NULL,
                    `plan_id` INT UNSIGNED NOT NULL,
                    `status` ENUM('active','cancelled','expired','trial') DEFAULT 'active',
                    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `expires_at` TIMESTAMP NULL,
                    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_user_id` (`user_id`),
                    INDEX `idx_plan_id` (`plan_id`),
                    INDEX `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $this->db->query(
                "INSERT IGNORE INTO resumex_subscription_plans (name, slug, price, currency, billing_cycle, max_resumes, features, is_default, status, sort_order)
                 VALUES ('Free','free',0,'USD','lifetime',3,?,1,'active',0)",
                [json_encode(['pdf_export'=>true,'linkedin_import'=>true,'public_sharing'=>true])]
            );
        } catch (\Exception $e) {}
    }

    private function render(string $view, array $data = []): void
    {
        View::render('projects/resumex/' . $view, $data);
    }

    private function buildAbsoluteUrl(string $path): string
    {
        $base = defined('APP_URL') && APP_URL ? rtrim(APP_URL, '/') : '';
        if ($base !== '') {
            return $base . $path;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $path;
    }
}
