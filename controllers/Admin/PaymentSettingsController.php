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
use Core\SecureUpload;

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
            $existingSettings = $this->subscriptionService->getPaymentSettings(true);
            $gatewayLogos = [
                'payment_upi_logo' => $this->resolveGatewayLogo(
                    'payment_upi_logo',
                    'payment_upi_logo_file',
                    'remove_payment_upi_logo',
                    'upi'
                ),
                'payment_cashfree_logo' => $this->resolveGatewayLogo(
                    'payment_cashfree_logo',
                    'payment_cashfree_logo_file',
                    'remove_payment_cashfree_logo',
                    'cashfree'
                ),
                'payment_manual_review_logo' => $this->resolveGatewayLogo(
                    'payment_manual_review_logo',
                    'payment_manual_review_logo_file',
                    'remove_payment_manual_review_logo',
                    'manual_review'
                ),
            ];
            foreach ($gatewayLogos as $gatewayKey => $gatewayValue) {
                if ($gatewayValue === null) {
                    $gatewayLogos[$gatewayKey] = (string) ($existingSettings[$gatewayKey] ?? '');
                }
            }

            $this->subscriptionService->savePaymentSettings([
                'payment_method' => Security::sanitize(trim($_POST['payment_method'] ?? 'request')),
                'payment_upi_id' => Security::sanitize(trim($_POST['payment_upi_id'] ?? '')),
                'payment_cashfree_enabled' => isset($_POST['payment_cashfree_enabled']) ? '1' : '0',
                'payment_cashfree_app_id' => Security::sanitize(trim($_POST['payment_cashfree_app_id'] ?? '')),
                'payment_cashfree_secret' => trim($_POST['payment_cashfree_secret'] ?? ''),
                'payment_cashfree_sandbox' => isset($_POST['payment_cashfree_sandbox']) ? '1' : '0',
                'payment_currency' => Security::sanitize(trim($_POST['payment_currency'] ?? 'INR')),
                'payment_manual_review_enabled' => isset($_POST['payment_manual_review_enabled']) ? '1' : '0',
                'require_mobile_verification'   => isset($_POST['require_mobile_verification'])   ? '1' : '0',
                'payment_default_refund_days'   => (int) ($_POST['payment_default_refund_days'] ?? 7),
                'payment_default_cancel_days'   => (int) ($_POST['payment_default_cancel_days'] ?? 0),
                'payment_upi_logo'              => $gatewayLogos['payment_upi_logo'],
                'payment_cashfree_logo'         => $gatewayLogos['payment_cashfree_logo'],
                'payment_manual_review_logo'    => $gatewayLogos['payment_manual_review_logo'],
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

    private function resolveGatewayLogo(
        string $urlField,
        string $uploadField,
        string $removeField,
        string $prefix
    ): ?string {
        if (!empty($_POST[$removeField])) {
            return '';
        }

        if (!empty($_FILES[$uploadField]['name'])) {
            $upload = SecureUpload::process($_FILES[$uploadField], [
                'destination_dir' => BASE_PATH . '/storage/uploads/payment-gateways',
                'allowed_extensions' => ['png', 'jpg', 'jpeg', 'webp'],
                'allowed_mime_types' => ['image/png', 'image/jpeg', 'image/webp'],
                'max_size' => 2 * 1024 * 1024,
                'source' => 'admin.payment_settings.' . $prefix . '_logo',
                'filename_prefix' => 'pay_' . $prefix . '_logo',
                'user_id' => Auth::id(),
            ]);
            if (!$upload['success']) {
                throw new \RuntimeException($upload['error'] ?? 'Failed to upload gateway logo.');
            }
            return '/uploads/payment-gateways/' . $upload['filename'];
        }

        $url = Security::sanitize(trim((string) ($_POST[$urlField] ?? '')));
        if ($url !== '') {
            return $url;
        }

        return null;
    }
}
