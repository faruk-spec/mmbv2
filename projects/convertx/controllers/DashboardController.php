<?php
/**
 * ConvertX Dashboard Controller
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Database;
use Core\SubscriptionService;
use Projects\ConvertX\Models\ConversionJobModel;

class DashboardController
{
    private ConversionJobModel $jobModel;

    public function __construct()
    {
        $this->jobModel = new ConversionJobModel();
    }

    public function index(): void
    {
        $userId = Auth::id();
        $usage  = $userId ? $this->jobModel->getMonthlyUsage($userId) : [];
        $recent = $userId ? $this->jobModel->getHistory($userId, 1, 5)['jobs'] : [];

        $this->render('dashboard', [
            'title'  => 'ConvertX Dashboard',
            'user'   => Auth::user(),
            'usage'  => $usage,
            'recent' => $recent,
        ]);
    }

    public function docs(): void
    {
        $this->render('docs', [
            'title' => 'ConvertX API Docs',
            'user'  => Auth::user(),
        ]);
    }

    public function plan(): void
    {
        $db = Database::getInstance();
        $subscriptionService = new SubscriptionService($db);
        $subscriptionService->ensureInfrastructure();
        $uid = (int) (Auth::id() ?? 0);
        $currentPlanSlug = 'free';
        $currentSub = null;
        $plans = [];
        $history = [];
        $paymentHistory = [];

        if ($uid > 0) {
            $currentSub = $subscriptionService->getCurrentSubscription('convertx', $uid);
            $plans = $subscriptionService->getActivePlans('convertx');
            $history = $subscriptionService->getSubscriptionHistory('convertx', $uid);
            $paymentHistory = $subscriptionService->getUserPayments($uid, 'convertx');
            if ($currentSub) {
                $currentPlanSlug = $currentSub['plan_slug'] ?? 'free';
            }
        }

        $this->render('plan', [
            'title' => 'ConvertX Plans & Pricing',
            'user'  => Auth::user(),
            'currentPlanSlug' => $currentPlanSlug,
            'currentSub' => $currentSub,
            'plans' => $plans,
            'history' => $history,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    // ------------------------------------------------------------------ //

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }
}
