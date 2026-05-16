<?php

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;
use Core\SubscriptionService;

class SubscriptionPaymentsController extends BaseController
{
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->subscriptionService = new SubscriptionService(Database::getInstance());
        $this->subscriptionService->ensureInfrastructure();
        $this->subscriptionService->ensureNotificationTemplates();
    }

    public function index(): void
    {
        $appKey = trim((string) ($_GET['app'] ?? ''));
        $appKey = in_array($appKey, ['resumex', 'qr', 'convertx', 'whatsapp'], true) ? $appKey : null;

        $this->view('admin/subscription-payments/index', [
            'title' => 'Subscription Payments',
            'payments' => $this->subscriptionService->getAdminPayments($appKey),
            'activeApp' => $appKey,
        ]);
    }

    public function approve(string $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/subscription-payments');
            return;
        }

        if ($this->subscriptionService->approvePayment((int) $id, Auth::id())) {
            $this->flash('success', 'Payment approved and subscription activated.');
        } else {
            $this->flash('error', 'Unable to approve payment.');
        }

        $this->redirect('/admin/subscription-payments');
    }

    public function reject(string $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/subscription-payments');
            return;
        }

        $reason = trim((string) ($_POST['reason'] ?? ''));
        if ($this->subscriptionService->rejectPayment((int) $id, Auth::id(), $reason)) {
            $this->flash('success', 'Payment marked as failed.');
        } else {
            $this->flash('error', 'Unable to reject payment.');
        }

        $this->redirect('/admin/subscription-payments');
    }

    public function cancelPlan(string $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/subscription-payments');
            return;
        }

        $db = Database::getInstance();
        $payment = $db->fetch("SELECT * FROM subscription_payments WHERE id = ?", [(int) $id]);
        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect('/admin/subscription-payments');
            return;
        }

        // Cancel the subscription row in the app's own table
        $result = $this->subscriptionService->adminCancelSubscription((int) $id, Auth::id());
        if ($result) {
            $this->flash('success', 'Subscription cancelled successfully.');
        } else {
            $this->flash('error', 'Unable to cancel subscription.');
        }

        $this->redirect('/admin/subscription-payments');
    }

    public function refunds(): void
    {
        $this->view('admin/subscription-payments/refunds', [
            'title' => 'Refund Requests',
            'payments' => $this->subscriptionService->getAdminRefundPayments(),
        ]);
    }

    public function refund(string $id): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/subscription-payments');
            return;
        }

        $decision = in_array($_POST['decision'] ?? '', ['approved', 'rejected', 'refunded'], true)
            ? (string) $_POST['decision']
            : 'rejected';

        $cancelSubscription = !empty($_POST['cancel_subscription']) && $_POST['cancel_subscription'] === '1';

        if ($this->subscriptionService->reviewRefundRequest((int) $id, $decision, Auth::id(), trim((string) ($_POST['reason'] ?? '')), $cancelSubscription)) {
            $this->flash('success', 'Refund request updated.');
        } else {
            $this->flash('error', 'Unable to update refund request.');
        }

        $this->redirect('/admin/subscription-payments');
    }
}
