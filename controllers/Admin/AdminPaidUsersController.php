<?php

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;
use Core\SubscriptionService;

class AdminPaidUsersController extends BaseController
{
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->subscriptionService = new SubscriptionService(Database::getInstance());
        $this->subscriptionService->ensureInfrastructure();
    }

    // GET /admin/paid-plan-users
    public function index(): void
    {
        $this->view('admin/paid-plan-users/index', [
            'title'      => 'Users with Paid Plans',
            'paidUsers'  => $this->subscriptionService->getUsersWithActivePaidPlans(),
        ]);
    }

    // GET /admin/paid-plan-users/user-plans?user_id=X  (AJAX JSON)
    public function getUserPlans(): void
    {
        $userId = (int) ($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user.']);
            return;
        }

        $payments = $this->subscriptionService->getUserActivePaidPayments($userId);
        $this->json(['success' => true, 'payments' => $payments]);
    }

    // POST /admin/paid-plan-users/cancel
    public function cancel(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/paid-plan-users');
            return;
        }

        $userId     = (int) ($_POST['user_id'] ?? 0);
        $paymentIds = array_filter(array_map('intval', (array) ($_POST['payment_ids'] ?? [])));
        $notify     = !empty($_POST['notify_user']);
        $issueRefund = !empty($_POST['issue_refund']);

        if ($userId <= 0 || empty($paymentIds)) {
            $this->flash('error', 'Please select a user and at least one plan to cancel.');
            $this->redirect('/admin/paid-plan-users');
            return;
        }

        $result = $this->subscriptionService->adminBulkCancelUserPlans(
            $userId,
            array_values($paymentIds),
            $issueRefund,
            $notify,
            Auth::id()
        );

        $msg = $result['cancelled'] . ' subscription(s) cancelled. User assigned free plan.';
        if ($result['refunds_queued'] > 0) {
            $msg .= ' ' . $result['refunds_queued'] . ' refund request(s) queued for review.';
        }

        $this->flash('success', $msg);
        $this->redirect('/admin/paid-plan-users');
    }
}
