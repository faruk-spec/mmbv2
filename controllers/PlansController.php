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
use Core\SubscriptionService;

class PlansController extends BaseController
{
    private Database $db;
    private SubscriptionService $subscriptionService;

    // Registered apps with display metadata
    private const APP_META = [
        'qr'        => ['name' => 'QR Generator',   'color' => '#9945ff', 'icon' => 'qr_code',      'url' => '/projects/qr'],
        'whatsapp'  => ['name' => 'WhatsApp API',    'color' => '#25D366', 'icon' => 'chat',         'url' => '/projects/whatsapp'],
        'convertx'  => ['name' => 'ConvertX',        'color' => '#6366f1', 'icon' => 'swap_horiz',   'url' => '/projects/convertx'],
        'proshare'  => ['name' => 'ProShare',        'color' => '#ffaa00', 'icon' => 'share',        'url' => '/projects/proshare'],
        'codexpro'  => ['name' => 'CodeXPro',        'color' => '#00f0ff', 'icon' => 'code',         'url' => '/projects/codexpro'],
        'resumex'   => ['name' => 'ResumeX',         'color' => '#ff6b6b', 'icon' => 'description', 'url' => '/projects/resumex'],
        'devzone'   => ['name' => 'DevZone',         'color' => '#ff2ec4', 'icon' => 'groups',      'url' => '/projects/devzone'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->subscriptionService = new SubscriptionService($this->db);
        $this->subscriptionService->ensureInfrastructure();
        $this->subscriptionService->ensureNotificationTemplates();
    }

    // -------------------------------------------------------------------------
    // User Plan Page
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $userId = Auth::id();

        // ── Per-app active subscriptions ──────────────────────────────────────
        $appSubscriptions = $this->getAppSubscriptions($userId);

        // ── Payment History — paginated ───────────────────────────────────────
        $payPerPage  = 10;
        $payPage     = max(1, (int) ($_GET['pay_page'] ?? 1));
        $allPayments = $this->subscriptionService->getUserPayments($userId);
        $payTotal    = count($allPayments);
        $payTotalPages = (int) ceil($payTotal / $payPerPage);
        $payPage     = min($payPage, max(1, $payTotalPages));
        $paymentHistory = array_slice($allPayments, ($payPage - 1) * $payPerPage, $payPerPage);

        $paymentSettings = $this->subscriptionService->getPaymentSettings();

        // ── Map app_key → latest paid payment ID for "Manage Subscription" links ──
        $appManagePaymentIds = [];
        foreach ($allPayments as $p) {
            if (($p['status'] ?? '') === 'paid' && isset(self::APP_META[$p['app_key'] ?? '']) && !isset($appManagePaymentIds[$p['app_key']])) {
                $appManagePaymentIds[$p['app_key']] = (int) $p['id'];
            }
        }

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
            'appManagePaymentIds'   => $appManagePaymentIds,
            'allPayments'           => $allPayments,
            'paymentHistory'        => $paymentHistory,
            'payPage'               => $payPage,
            'payTotalPages'         => $payTotalPages,
            'payTotal'              => $payTotal,
            'paymentSettings'       => $paymentSettings,
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

        if (!$this->checkSubscriptionPrerequisites('/plans/subscribe/' . urlencode($slug))) {
            return;
        }

        // Check if user already has this plan
        $existing = null;
        try {
            $existing = $this->db->fetch(
                "SELECT * FROM platform_user_subscriptions WHERE user_id = ? AND plan_id = ? AND status = 'active'",
                [$userId, $plan['id']]
            );
        } catch (\Exception $e) {}

        // Cross-check: any active platform plan (different from the one being purchased)
        $activePlanConflict = null;
        try {
            $activePlanConflict = $this->db->fetch(
                "SELECT s.*, p.name AS plan_name FROM platform_user_subscriptions s
                 JOIN platform_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active' AND s.plan_id != ?
                 LIMIT 1",
                [$userId, $plan['id']]
            );
        } catch (\Exception $e) {}

        Logger::activity($userId, 'subscription_page_viewed', ['plan_slug' => $slug]);

        $this->view('dashboard/plans-subscribe', [
            'title'    => 'Subscribe to Plan',
            'plan'     => $plan,
            'appMeta'  => self::APP_META,
            'existing' => $existing,
            'activePlanConflict' => $activePlanConflict,
            'paymentSettings' => $this->subscriptionService->getPaymentSettings(),
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
        $paymentSettings = $this->subscriptionService->getPaymentSettings();
        $paymentMethod = $_POST['payment_method'] ?? ($paymentSettings['payment_method'] ?? 'request');

        // If manual review is disabled, reject any attempt to use it.
        $manualReviewEnabled = ($paymentSettings['payment_manual_review_enabled'] ?? '1') === '1';
        if ($paymentMethod === 'request' && !$manualReviewEnabled) {
            $this->flash('error', 'Manual review is not available. Please select a different payment method.');
            $this->redirect('/plans/subscribe/' . urlencode($slug));
            return;
        }

        // Log the request so admin can see it in activity logs
        Logger::activity($userId, 'subscription_requested', [
            'plan_id'   => $plan['id'],
            'plan_name' => $plan['name'],
            'plan_slug' => $plan['slug'],
            'message'   => $message,
        ]);

        // If plan is free (price=0), auto-assign immediately — but only if user has no active paid plan
        if ((float)$plan['price'] == 0) {
            // Cross-verification: block free plan activation when user has an active paid platform plan
            $hasPaidPlan = false;
            try {
                $paid = $this->db->fetch(
                    "SELECT s.id FROM platform_user_subscriptions s
                     JOIN platform_plans p ON p.id = s.plan_id
                     WHERE s.user_id = ? AND s.status = 'active'
                     AND (s.expires_at IS NULL OR s.expires_at > NOW())
                     AND p.price > 0
                     LIMIT 1",
                    [$userId]
                );
                $hasPaidPlan = $paid !== null;
            } catch (\Exception $e) {}

            if ($hasPaidPlan) {
                $this->flash('error', 'You already have an active paid plan. Free plans are not available while a paid plan is active.');
                $this->redirect('/plans');
                return;
            }

            try {
                $payment = $this->subscriptionService->createOrReusePayment([
                    'user_id' => $userId,
                    'app_key' => 'platform',
                    'plan_id' => (int) $plan['id'],
                    'plan_name' => (string) $plan['name'],
                    'billing_cycle' => $plan['billing_cycle'] ?? 'monthly',
                    'gateway' => 'request',
                    'status' => 'paid',
                    'amount' => 0,
                    'currency' => (string) ($plan['currency'] ?? ($paymentSettings['payment_currency'] ?? 'USD')),
                    'metadata' => ['message' => $message, 'plan_slug' => $plan['slug']],
                ]);
                $this->subscriptionService->approvePayment((int) $payment['id'], $userId);
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

        $paymentTitle = preg_replace('/[^a-zA-Z0-9 .,_-]/', '', (string) $plan['name']) ?: 'Plan';
        $payment = $this->subscriptionService->createOrReusePayment([
            'user_id' => $userId,
            'app_key' => 'platform',
            'plan_id' => (int) $plan['id'],
            'plan_name' => (string) $plan['name'],
            'billing_cycle' => $plan['billing_cycle'] ?? 'monthly',
            'gateway' => in_array($paymentMethod, ['upi', 'cashfree'], true) ? $paymentMethod : 'request',
            'status' => $paymentMethod === 'request' ? 'verification_pending' : 'pending',
            'amount' => (float) $plan['price'],
            'currency' => (string) ($plan['currency'] ?? ($paymentSettings['payment_currency'] ?? 'USD')),
            'payment_payload' => $paymentMethod === 'upi' && !empty($paymentSettings['payment_upi_id'])
                ? 'upi://pay?' . http_build_query([
                    'pa' => $paymentSettings['payment_upi_id'],
                    'pn' => 'MMB Platform',
                    'am' => sprintf('%.2f', (float) $plan['price']),
                    'cu' => $plan['currency'] ?? ($paymentSettings['payment_currency'] ?? 'USD'),
                    'tn' => $paymentTitle . ' Plan',
                ])
                : null,
            'metadata' => ['message' => $message, 'plan_slug' => $plan['slug']],
        ]);

        // Only create a new Cashfree order when none exists yet.
        // If provider_order_id is already set (e.g. the user returned to the page and resubmitted),
        // reuse the existing session to avoid a 409 conflict from Cashfree's duplicate-order check.
        if ($paymentMethod === 'cashfree'
            && ($paymentSettings['payment_cashfree_enabled'] ?? '0') === '1'
            && !empty($paymentSettings['payment_cashfree_app_id'])
            && !empty($paymentSettings['payment_cashfree_secret'])
            && empty($payment['provider_order_id'])
        ) {
            $cashfreeResult = $this->subscriptionService->createCashfreeOrder(
                $payment,
                $paymentSettings,
                [
                    'name' => Auth::user()['name'] ?? 'Customer',
                    'email' => Auth::user()['email'] ?? '',
                    'phone' => Auth::user()['phone'] ?? '9999999999',
                ],
                $this->buildAbsoluteUrl('/plans/payment/' . (int) $payment['id'] . '/return')
            );

            if (!$cashfreeResult['success']) {
                $this->flash('error', $cashfreeResult['message'] ?? 'Unable to start Cashfree payment.');
                $this->redirect('/plans/subscribe/' . urlencode($slug));
                return;
            }
        }

        try { Notification::send($userId, 'plan_request_submitted', 'Your subscription request for the "' . $plan['name'] . '" plan has been submitted.', ['plan_id' => $plan['id'], 'plan_name' => $plan['name']]); } catch (\Exception $e) {}
        $this->redirect('/plans/payment/' . (int) $payment['id']);
    }

    public function payment(string $id): void
    {
        $payment = $this->subscriptionService->getUserPayment((int) $id, Auth::id());
        if (!$payment) {
            $this->flash('error', 'Payment record not found.');
            $this->redirect('/plans');
            return;
        }

        // Auto-refresh an expired Cashfree session for pending payments so the
        // user always lands on a valid checkout instead of a stale/rejected one.
        if ($payment['status'] === 'pending'
            && $payment['gateway'] === 'cashfree'
            && !empty($payment['provider_payment_session_id'])
            && !empty($payment['payment_session_expires_at'])
            && strtotime((string)$payment['payment_session_expires_at']) < time()
        ) {
            $paymentSettings = $this->subscriptionService->getPaymentSettings();
            if (($paymentSettings['payment_cashfree_enabled'] ?? '0') === '1'
                && !empty($paymentSettings['payment_cashfree_app_id'])
                && !empty($paymentSettings['payment_cashfree_secret'])
            ) {
                $user = Auth::user();
                $refreshResult = $this->subscriptionService->createCashfreeOrder(
                    $payment,
                    $paymentSettings,
                    [
                        'name'  => $user['name']  ?? 'Customer',
                        'email' => $user['email'] ?? '',
                        'phone' => $user['phone'] ?? '9999999999',
                    ],
                    $this->buildAbsoluteUrl('/plans/payment/' . (int) $payment['id'] . '/return')
                );
                if ($refreshResult['success']) {
                    // Re-fetch payment so the view gets the fresh session id.
                    $payment = $this->subscriptionService->getUserPayment((int) $id, Auth::id()) ?? $payment;
                }
            }
        }

        $this->view('dashboard/plans-payment', [
            'title' => 'Subscription Payment',
            'payment' => $payment,
            'paymentSettings' => $this->subscriptionService->getPaymentSettings(),
            'invoiceSettings' => $this->subscriptionService->getInvoiceSettings(true),
            'canCancel' => $this->subscriptionService->canCancelPayment($payment),
            'canRefund' => $this->subscriptionService->canRequestRefund($payment),
        ]);
    }

    public function confirmPayment(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/plans/payment/' . (int) $id);
            return;
        }

        if ($this->subscriptionService->markUserPaymentSubmitted((int) $id, Auth::id())) {
            $this->flash('success', 'Payment marked as submitted. Admin verification is pending.');
        } else {
            $this->flash('error', 'Unable to update payment status.');
        }

        $this->redirect('/plans/payment/' . (int) $id);
    }

    public function appSubscribe(string $app, string $slug): void
    {
        $plan = $this->subscriptionService->getAppPlan($app, $slug);
        if (!$plan) {
            $this->flash('error', 'Subscription plan not found.');
            $this->redirect('/plans');
            return;
        }

        if ((float) ($plan['price'] ?? 0) === 0.0 && $this->hasActivePaidAppSubscription($app, (int) Auth::id())) {
            $this->flash('error', 'You already have an active paid plan for this app. Free plans are not available while a paid plan is active.');
            $this->redirect(self::APP_META[$app]['url'] ?? '/plans');
            return;
        }

        $existing = $this->subscriptionService->getCurrentSubscription($app, Auth::id());

        // Detect downgrade: user has an active higher-priced plan and wants a lower-priced one.
        $isDowngrade = $existing !== null
            && (float) ($existing['price'] ?? 0) > 0
            && (float) ($plan['price'] ?? 0) < (float) ($existing['price'] ?? 0);

        if (!$this->checkSubscriptionPrerequisites('/plans/project/' . urlencode($app) . '/' . urlencode($slug))) {
            return;
        }

        $appMeta = self::APP_META[$app] ?? ['name' => ucfirst($app), 'url' => '/plans'];
        $projectInfo = Helpers::getProject($app);
        if (is_array($projectInfo)) {
            $appMeta['logo_url'] = (string) ($projectInfo['logo_url'] ?? '');
            if (!empty($projectInfo['color'])) {
                $appMeta['color'] = (string) $projectInfo['color'];
            }
            if (!empty($projectInfo['name'])) {
                $appMeta['name'] = (string) $projectInfo['name'];
            }
        }

        $this->view('dashboard/app-plan-subscribe', [
            'title' => 'Subscribe to ' . ucfirst($app) . ' Plan',
            'plan' => $plan,
            'app' => $app,
            'appMeta' => $appMeta,
            'existing' => $existing,
            'isDowngrade' => $isDowngrade,
            'paymentSettings' => $this->subscriptionService->getPaymentSettings(),
            'invoiceSettings' => $this->subscriptionService->getInvoiceSettings(true),
        ]);
    }

    public function processAppSubscribe(string $app, string $slug): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/plans/project/' . urlencode($app) . '/' . urlencode($slug));
            return;
        }

        $plan = $this->subscriptionService->getAppPlan($app, $slug);
        if (!$plan) {
            $this->flash('error', 'Subscription plan not found.');
            $this->redirect('/plans');
            return;
        }

        // Downgrade protection: if user has an active higher-priced plan, block downgrade.
        $existingForDowngrade = $this->subscriptionService->getCurrentSubscription($app, Auth::id());
        if ($existingForDowngrade !== null
            && (float) ($existingForDowngrade['price'] ?? 0) > 0
            && (float) ($plan['price'] ?? 0) < (float) ($existingForDowngrade['price'] ?? 0)
        ) {
            $this->flash('error', 'Downgrade not allowed. Please cancel your current "' . ($existingForDowngrade['plan_name'] ?? 'current plan') . '" plan before switching to a lower tier, or consider upgrading instead.');
            $this->redirect('/plans/project/' . urlencode($app) . '/' . urlencode($slug));
            return;
        }

        $paymentSettings = $this->subscriptionService->getPaymentSettings();
        $cashfreeEnabled = ($paymentSettings['payment_cashfree_enabled'] ?? '0') === '1'
            && !empty($paymentSettings['payment_cashfree_app_id'])
            && !empty($paymentSettings['payment_cashfree_secret']);
        $hasUpi = !empty($paymentSettings['payment_upi_id']);
        $manualReviewEnabled = ($paymentSettings['payment_manual_review_enabled'] ?? '1') === '1';

        // Determine the effective payment method; ensure it is one that is actually available.
        $requestedMethod = $_POST['payment_method'] ?? ($paymentSettings['payment_method'] ?? 'request');
        if ($requestedMethod === 'cashfree' && !$cashfreeEnabled) {
            $requestedMethod = $hasUpi ? 'upi' : 'request';
        }
        if ($requestedMethod === 'upi' && !$hasUpi) {
            $requestedMethod = $cashfreeEnabled ? 'cashfree' : 'request';
        }
        $paymentMethod = $requestedMethod;

        $user = Auth::user();
        $policy = [
            'cancel_days' => (int) ($plan['cancel_days'] ?? 0),
            'refund_days' => (int) ($plan['refund_days'] ?? 0),
        ];

        // If manual review is disabled, reject any attempt to use it.
        if ($paymentMethod === 'request' && !$manualReviewEnabled && (float) ($plan['price'] ?? 0) > 0) {
            $this->flash('error', 'Payment methods are currently unavailable. Please contact the administrator to set up payment processing.');
            $this->redirect('/plans/project/' . urlencode($app) . '/' . urlencode($slug));
            return;
        }

        if ((float) ($plan['price'] ?? 0) == 0) {
            // Cross-verification: block free plan if user already has active paid plan for same app
            $hasPaidAppPlan = $this->hasActivePaidAppSubscription($app, (int) Auth::id());

            if ($hasPaidAppPlan) {
                $this->flash('error', 'You already have an active paid plan for this app. Free plans are not available while a paid plan is active.');
                $this->redirect('/plans/project/' . urlencode($app) . '/' . urlencode($slug));
                return;
            }

            $payment = $this->subscriptionService->createOrReusePayment([
                'user_id' => Auth::id(),
                'app_key' => $app,
                'plan_id' => (int) $plan['id'],
                'plan_name' => (string) $plan['name'],
                'billing_cycle' => (string) ($plan['billing_cycle'] ?? 'free'),
                'gateway' => 'request',
                'status' => 'paid',
                'amount' => 0,
                'currency' => (string) ($plan['currency'] ?? 'USD'),
                'metadata' => $policy,
            ]);
            $this->subscriptionService->approvePayment((int) $payment['id'], Auth::id());
            $this->flash('success', 'Your free plan is now active.');
            $this->redirect(self::APP_META[$app]['url'] ?? '/plans');
            return;
        }

        $paymentTitle = preg_replace('/[^a-zA-Z0-9 .,_-]/', '', (string) $plan['name']) ?: 'Plan';
        $payment = $this->subscriptionService->createOrReusePayment([
            'user_id' => Auth::id(),
            'app_key' => $app,
            'plan_id' => (int) $plan['id'],
            'plan_name' => (string) $plan['name'],
            'billing_cycle' => (string) ($plan['billing_cycle'] ?? (($plan['duration_days'] ?? 30) . ' days')),
            'gateway' => in_array($paymentMethod, ['upi', 'cashfree'], true) ? $paymentMethod : 'request',
            'status' => $paymentMethod === 'request' ? 'verification_pending' : 'pending',
            'amount' => (float) $plan['price'],
            'currency' => (string) ($plan['currency'] ?? ($paymentSettings['payment_currency'] ?? 'USD')),
            'payment_payload' => $paymentMethod === 'upi' && $hasUpi
                ? 'upi://pay?' . http_build_query([
                    'pa' => $paymentSettings['payment_upi_id'],
                    'pn' => self::APP_META[$app]['name'] ?? ucfirst($app),
                    'am' => sprintf('%.2f', (float) $plan['price']),
                    'cu' => $plan['currency'] ?? ($paymentSettings['payment_currency'] ?? 'USD'),
                    'tn' => $paymentTitle . ' Plan',
                ])
                : null,
            'metadata' => array_merge($policy, ['plan_slug' => $plan['slug'] ?? $slug]),
        ]);

        // Only create a new Cashfree order when none exists yet (prevents 409 on retry).
        if ($paymentMethod === 'cashfree'
            && $cashfreeEnabled
            && empty($payment['provider_order_id'])
        ) {
            $result = $this->subscriptionService->createCashfreeOrder(
                $payment,
                $paymentSettings,
                [
                    'name' => $user['name'] ?? 'Customer',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '9999999999',
                ],
                $this->buildAbsoluteUrl('/plans/payment/' . (int) $payment['id'] . '/return')
            );
            if (!$result['success']) {
                $this->flash('error', $result['message'] ?? 'Unable to start payment.');
                $this->redirect('/plans/project/' . urlencode($app) . '/' . urlencode($slug));
                return;
            }
        }

        $this->flash('success', 'Subscription request created. Complete payment to activate.');
        $this->redirect('/plans/payment/' . (int) $payment['id']);
    }

    public function paymentInvoice(string $id): void
    {
        $payment = $this->subscriptionService->getUserPayment((int) $id, Auth::id());
        if (!$payment) {
            $this->flash('error', 'Invoice not found.');
            $this->redirect('/plans');
            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $this->subscriptionService->renderInvoiceHtmlForPayment($payment, Auth::user());
        exit;
    }

    public function appInvoice(string $app, string $id): void
    {
        $payment = $this->subscriptionService->getSubscriptionInvoicePayment($app, (int) $id, Auth::id());
        if (!$payment) {
            $this->flash('error', 'Invoice not found.');
            $this->redirect('/plans');
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $this->subscriptionService->renderInvoiceHtmlForPayment($payment, Auth::user());
        exit;
    }

    public function cancelOtpPage(string $id): void
    {
        $payment = $this->subscriptionService->getUserPayment((int) $id, Auth::id());
        if (!$payment || ($payment['status'] ?? '') !== 'paid' || empty($payment['subscription_id'])) {
            $this->flash('error', 'No active subscription found for this payment.');
            $this->redirect('/plans/payment/' . (int) $id);
            return;
        }

        $canCancel = $this->subscriptionService->canCancelPayment($payment);
        $refundWindow = $this->subscriptionService->getCancelRefundWindowDays($payment);

        $this->view('dashboard/plans-cancel-otp', [
            'title' => 'Cancel Subscription',
            'payment' => $payment,
            'canCancel' => $canCancel,
            'refundWindowDays' => $refundWindow,
        ]);
    }

    public function sendCancelOtp(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/plans/payment/' . (int) $id . '/cancel');
            return;
        }

        if ($this->subscriptionService->sendCancelOtp((int) $id, Auth::id())) {
            $this->flash('success', 'A 6-digit verification code has been sent to your email.');
        } else {
            $this->flash('error', 'Unable to send verification code. Please try again.');
        }

        $this->redirect('/plans/payment/' . (int) $id . '/cancel');
    }

    public function cancelPaymentSubscription(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/plans/payment/' . (int) $id);
            return;
        }

        $otp = trim((string) ($_POST['cancel_otp'] ?? ''));

        // OTP-based cancel flow (for active paid subscriptions)
        if ($otp !== '') {
            $result = $this->subscriptionService->cancelSubscriptionWithOtp((int) $id, Auth::id(), $otp);
            if ($result['success']) {
                $msg = $result['refund_eligible']
                    ? 'Subscription cancelled. A refund request has been submitted and is pending admin confirmation.'
                    : 'Subscription cancelled. Your access remains active until the end of the billing period.';
                $this->flash('success', $msg);
            } else {
                $this->flash('error', $result['message'] ?? 'Unable to cancel subscription.');
                $this->redirect('/plans/payment/' . (int) $id . '/cancel');
                return;
            }
            $this->redirect('/plans/payment/' . (int) $id);
            return;
        }

        // Legacy direct cancel (e.g. admin actions or non-paid subscriptions)
        if ($this->subscriptionService->cancelSubscriptionByPayment((int) $id, Auth::id())) {
            $this->flash('success', 'Subscription cancelled.');
        } else {
            $this->flash('error', 'Unable to cancel this subscription.');
        }
        $this->redirect('/plans/payment/' . (int) $id);
    }

    public function requestRefund(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/plans/payment/' . (int) $id);
            return;
        }
        if ($this->subscriptionService->requestRefund((int) $id, Auth::id())) {
            $this->flash('success', 'Refund request submitted.');
        } else {
            $this->flash('error', 'Refund request is not available for this payment.');
        }
        $this->redirect('/plans/payment/' . (int) $id);
    }

    public function cashfreeReturn(string $id): void
    {
        $payment = $this->subscriptionService->getUserPayment((int) $id, Auth::id());
        if (!$payment) {
            $this->flash('error', 'Payment record not found.');
            $this->redirect('/plans');
            return;
        }

        $result = $this->subscriptionService->confirmCashfreePayment($payment, $this->subscriptionService->getPaymentSettings(), Auth::id());
        if (!empty($result['success']) && !empty($result['paid']) && !empty($result['approved'])) {
            $this->flash('success', 'Payment received and subscription activated.');
        } elseif (!empty($result['success'])) {
            $this->flash('error', 'Cashfree payment is not marked paid yet. Please try again in a moment.');
        } else {
            $this->flash('error', $result['message'] ?? 'Unable to verify Cashfree payment.');
        }

        $this->redirect('/plans/payment/' . (int) $id);
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

        // ConvertX
        try {
            $row = $this->db->fetch(
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.price, p.currency, p.billing_cycle,
                        p.max_jobs_per_month, p.max_file_size_mb, p.max_batch_size, p.features, s.expires_at
                 FROM convertx_user_subscriptions s
                 JOIN convertx_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC LIMIT 1",
                [$userId]
            );
            if ($row) {
                $row['app_key'] = 'convertx';
                $row['features'] = $this->decodeFeatures($row['features'] ?? '');
                $subs['convertx'] = $row;
            }
        } catch (\Exception $e) {
        }

        // WhatsApp
        try {
            $row = $this->db->fetch(
                "SELECT s.*, p.id AS plan_id, p.name AS plan_name, LOWER(REPLACE(REPLACE(p.name, ' Plan', ''), ' ', '-')) AS plan_slug, p.price, p.currency,
                        CONCAT(p.duration_days, ' days') AS billing_cycle,
                        p.sessions_limit AS max_sessions, p.messages_limit AS max_messages_per_day,
                        s.start_date AS started_at, s.end_date AS expires_at
                 FROM whatsapp_subscriptions s
                 LEFT JOIN whatsapp_subscription_plans p
                   ON p.id = COALESCE(s.plan_id, (SELECT id FROM whatsapp_subscription_plans WHERE LOWER(REPLACE(REPLACE(name, ' Plan', ''), ' ', '-')) = s.plan_type LIMIT 1))
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

    private function hasActivePaidAppSubscription(string $app, int $userId): bool
    {
        try {
            if ($app === 'whatsapp') {
                $paid = $this->db->fetch(
                    "SELECT s.id
                     FROM whatsapp_subscriptions s
                     LEFT JOIN whatsapp_subscription_plans p
                       ON p.id = COALESCE(
                           s.plan_id,
                           (SELECT id FROM whatsapp_subscription_plans WHERE LOWER(REPLACE(REPLACE(name, ' Plan', ''), ' ', '-')) = s.plan_type LIMIT 1)
                       )
                     WHERE s.user_id = ? AND s.status = 'active'
                       AND (s.end_date IS NULL OR s.end_date > NOW())
                       AND COALESCE(p.price, 0) > 0
                     LIMIT 1",
                    [$userId]
                );

                return $paid !== null;
            }

            $tableMap = [
                'resumex'  => ['table' => 'resumex_user_subscriptions',  'plan_table' => 'resumex_subscription_plans',  'expires' => 'expires_at'],
                'convertx' => ['table' => 'convertx_user_subscriptions', 'plan_table' => 'convertx_subscription_plans', 'expires' => 'expires_at'],
                'qr'       => ['table' => 'qr_user_subscriptions',       'plan_table' => 'qr_subscription_plans',       'expires' => 'expires_at'],
            ];

            if (!isset($tableMap[$app])) {
                return false;
            }

            $t  = $tableMap[$app]['table'];
            $pt = $tableMap[$app]['plan_table'];
            $ex = $tableMap[$app]['expires'];
            $paid = $this->db->fetch(
                "SELECT s.id FROM {$t} s
                 JOIN {$pt} p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                   AND (s.{$ex} IS NULL OR s.{$ex} > NOW())
                   AND p.price > 0
                 LIMIT 1",
                [$userId]
            );

            return $paid !== null;
        } catch (\Exception $e) {
            return false;
        }
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
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.color, p.price, p.currency, p.billing_cycle, p.included_apps
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

    private function getPlatformSubscriptionHistory(int $userId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.price, p.currency, p.billing_cycle
                 FROM platform_user_subscriptions s
                 JOIN platform_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ?
                 ORDER BY s.started_at DESC
                 LIMIT 20",
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
        $payment = $this->db->fetch(
            "SELECT * FROM subscription_payments
             WHERE user_id = ? AND app_key = 'platform' AND subscription_id = ?
             ORDER BY id DESC LIMIT 1",
            [$userId, $subId]
        );
        if (!$payment) {
            $payment = [
                'id' => $sub['id'],
                'app_key' => 'platform',
                'subscription_id' => $sub['id'],
                'plan_name' => $sub['plan_name'],
                'billing_cycle' => $sub['billing_cycle'],
                'amount' => $sub['price'],
                'currency' => $sub['currency'] ?? 'USD',
                'invoice_no' => 'INV-' . strtoupper(substr(md5($sub['id'] . $sub['started_at']), 0, 8)),
                'paid_at' => $sub['started_at'],
                'created_at' => $sub['started_at'],
                'expires_at' => $sub['expires_at'],
                'status' => $sub['status'],
            ];
        }

        $invoiceHtml = $this->subscriptionService->renderInvoiceHtmlForPayment($payment, $user);
        header('Content-Type: text/html; charset=utf-8');
        echo $invoiceHtml;
        exit;
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

    /**
     * Check that the user meets all prerequisites before subscribing to a plan.
     *
     * Verifies (in order):
     * 1. Email is verified (email_verified_at set on the users table).
     * 2. Phone is verified when the admin has enabled require_mobile_verification.
     * 3. Billing details are fully completed.
     *
     * If any check fails the user is redirected to the appropriate page and false
     * is returned; the caller should immediately return on false.
     *
     * @param string $returnPath The URL path to redirect back to after completing prerequisites.
     * @return bool True if all prerequisites are satisfied.
     */
    private function checkSubscriptionPrerequisites(string $returnPath): bool
    {
        $db = $this->db;
        $userId = Auth::id();

        // 1. Email must be verified
        $user = $db->fetch("SELECT email_verified_at FROM users WHERE id = ?", [$userId]);
        if (empty($user['email_verified_at'])) {
            $this->flash('error', 'Please verify your email address before subscribing. Check your inbox for the verification link, or go to your profile to resend it.');
            $this->redirect('/profile');
            return false;
        }

        // 2. Mobile verification (if admin requires it)
        $requireMobile = '0';
        try {
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = 'require_mobile_verification'");
            if ($row) $requireMobile = $row['value'];
        } catch (\Throwable $e) {}

        if ($requireMobile === '1') {
            $profile = $db->fetch("SELECT phone_verified_at FROM user_profiles WHERE user_id = ?", [$userId]);
            if (empty($profile['phone_verified_at'])) {
                $this->flash('error', 'Please verify your phone number before subscribing. Go to Profile to verify.');
                $this->redirect('/profile');
                return false;
            }
        }

        // 3. Billing details must be complete
        try {
            $billing = $db->fetch(
                "SELECT full_name, email, phone, address_line1, city, state, postal_code, country FROM user_billing_details WHERE user_id = ?",
                [$userId]
            );
            $billingComplete = $billing
                && !empty($billing['full_name']) && !empty($billing['email'])
                && !empty($billing['phone']) && !empty($billing['address_line1'])
                && !empty($billing['city']) && !empty($billing['state'])
                && !empty($billing['postal_code']) && !empty($billing['country']);
        } catch (\Throwable $e) {
            $billingComplete = false;
        }

        if (!$billingComplete) {
            $_SESSION['billing_next'] = $returnPath;
            $this->flash('info', 'Please complete your billing details before subscribing.');
            $this->redirect('/billing-details?next=' . urlencode($returnPath));
            return false;
        }

        return true;
    }
}
