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

class PaymentSettingsController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $settings = $this->getSettings();

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

        $keys = [
            'payment_method'             => Security::sanitize(trim($_POST['payment_method']   ?? 'request')),
            'payment_upi_id'             => Security::sanitize(trim($_POST['payment_upi_id']   ?? '')),
            'payment_cashfree_enabled'   => isset($_POST['payment_cashfree_enabled']) ? '1' : '0',
            'payment_cashfree_app_id'    => Security::sanitize(trim($_POST['payment_cashfree_app_id'] ?? '')),
            'payment_cashfree_secret'    => Security::sanitize(trim($_POST['payment_cashfree_secret']  ?? '')),
            'payment_cashfree_sandbox'   => isset($_POST['payment_cashfree_sandbox']) ? '1' : '0',
            'payment_currency'           => Security::sanitize(trim($_POST['payment_currency'] ?? 'INR')),
        ];

        foreach ($keys as $key => $value) {
            try {
                $existing = $this->db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
                if ($existing) {
                    $this->db->update('settings', ['value' => $value], '`key` = ?', [$key]);
                } else {
                    $this->db->insert('settings', ['key' => $key, 'value' => $value, 'type' => 'string']);
                }
            } catch (\Exception $e) {
                Logger::error('PaymentSettings save: ' . $e->getMessage());
            }
        }

        ActivityLogger::log(Auth::id(), 'payment_settings_updated');
        $_SESSION['_flash']['success'] = 'Payment settings saved successfully.';
        $this->redirect('/admin/payment-settings');
    }

    private function getSettings(): array
    {
        $defaults = [
            'payment_method'           => 'request',
            'payment_upi_id'           => '',
            'payment_cashfree_enabled' => '0',
            'payment_cashfree_app_id'  => '',
            'payment_cashfree_secret'  => '',
            'payment_cashfree_sandbox' => '1',
            'payment_currency'         => 'INR',
        ];
        try {
            $rows = $this->db->fetchAll(
                "SELECT `key`, value FROM settings WHERE `key` LIKE 'payment_%'"
            );
            foreach ($rows as $row) {
                $defaults[$row['key']] = $row['value'];
            }
        } catch (\Exception $e) {}
        return $defaults;
    }
}
