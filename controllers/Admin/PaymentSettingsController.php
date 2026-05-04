<?php
/**
 * Payment Settings Admin Controller
 * Manages Cashfree and UPI payment gateway configuration.
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\ActivityLogger;
use Core\Logger;
use Core\SubscriptionService;

class PaymentSettingsController extends BaseController
{
    private Database $db;
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        $this->db = Database::getInstance();
        $this->subscriptionService = new SubscriptionService($this->db);
        $this->subscriptionService->ensureInfrastructure();
        $this->subscriptionService->ensureNotificationTemplates();
    }

    public function index(): void
    {
        $settings = $this->subscriptionService->getPaymentSettings(true);

        $this->view('admin/payment-settings', [
            'title'    => 'Payment Settings',
            'settings' => $settings,
            'csrf'     => Security::generateCsrfToken(),
            'success'  => $_SESSION['_flash']['success'] ?? null,
            'error'    => $_SESSION['_flash']['error']   ?? null,
        ]);
        unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
    }

    public function save(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid security token.';
            $this->redirect('/admin/payment-settings');
            return;
        }

        try {
            $this->subscriptionService->savePaymentSettings([
                'payment_method' => Security::sanitize(trim($_POST['payment_method'] ?? 'request')),
                'payment_upi_id' => Security::sanitize(trim($_POST['payment_upi_id'] ?? '')),
                'payment_cashfree_enabled' => isset($_POST['payment_cashfree_enabled']) ? '1' : '0',
                'payment_cashfree_app_id' => Security::sanitize(trim($_POST['payment_cashfree_app_id'] ?? '')),
                'payment_cashfree_secret' => trim($_POST['payment_cashfree_secret'] ?? ''),
                'payment_cashfree_sandbox' => isset($_POST['payment_cashfree_sandbox']) ? '1' : '0',
                'payment_currency' => Security::sanitize(trim($_POST['payment_currency'] ?? 'INR')),
                'payment_manual_review_enabled' => isset($_POST['payment_manual_review_enabled']) ? '1' : '0',
            ]);
        } catch (\Throwable $e) {
            Logger::error('PaymentSettings save: ' . $e->getMessage());
            $_SESSION['_flash']['error'] = 'Failed to save payment settings.';
            $this->redirect('/admin/payment-settings');
            return;
        }

        ActivityLogger::log(Auth::id(), 'payment_settings_updated');
        $_SESSION['_flash']['success'] = 'Payment settings saved successfully.';
        $this->redirect('/admin/payment-settings');
    }
}
