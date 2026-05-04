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
        $appKey = in_array($appKey, ['platform', 'resumex'], true) ? $appKey : null;

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

        if ($this->subscriptionService->reviewRefundRequest((int) $id, $decision, Auth::id(), trim((string) ($_POST['reason'] ?? '')))) {
            $this->flash('success', 'Refund request updated.');
        } else {
            $this->flash('error', 'Unable to update refund request.');
        }

        $this->redirect('/admin/subscription-payments');
    }
}
