<?php

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\SecureUpload;
use Core\SubscriptionService;

class InvoiceSettingsController extends BaseController
{
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('settings');
        $this->subscriptionService = new SubscriptionService(Database::getInstance());
        $this->subscriptionService->ensureInfrastructure();
    }

    public function index(): void
    {
        $this->view('admin/invoice-settings', [
            'title' => 'Invoice Settings',
            'settings' => $this->subscriptionService->getInvoiceSettings(true),
        ]);
    }

    public function save(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/invoice-settings');
            return;
        }

        $logoPath = $_POST['current_invoice_logo'] ?? '';
        if (!empty($_POST['remove_invoice_logo'])) {
            $logoPath = '';
        } elseif (!empty($_FILES['invoice_logo']['name'])) {
            $upload = SecureUpload::process($_FILES['invoice_logo'], [
                'destination_dir' => BASE_PATH . '/storage/uploads/invoices',
                'allowed_extensions' => ['png', 'jpg', 'jpeg', 'webp', 'svg'],
                'allowed_mime_types' => ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'],
                'max_size' => 2 * 1024 * 1024,
                'source' => 'admin.invoice_settings.logo',
                'filename_prefix' => 'invoice_logo',
                'user_id' => Auth::id(),
            ]);
            if (!$upload['success']) {
                $this->flash('error', $upload['error'] ?? 'Failed to upload invoice logo.');
                $this->redirect('/admin/invoice-settings');
                return;
            }
            $logoPath = '/uploads/invoices/' . $upload['filename'];
        }

        $this->subscriptionService->saveInvoiceSettings([
            'invoice_company_name' => $_POST['invoice_company_name'] ?? '',
            'invoice_company_email' => $_POST['invoice_company_email'] ?? '',
            'invoice_company_phone' => $_POST['invoice_company_phone'] ?? '',
            'invoice_company_address' => $_POST['invoice_company_address'] ?? '',
            'invoice_logo' => $logoPath,
            'invoice_prefix' => $_POST['invoice_prefix'] ?? 'INV',
            'invoice_accent_color' => $_POST['invoice_accent_color'] ?? '#0077cc',
            'invoice_footer_note' => $_POST['invoice_footer_note'] ?? '',
            'invoice_terms' => $_POST['invoice_terms'] ?? '',
        ]);

        $this->flash('success', 'Invoice settings updated.');
        $this->redirect('/admin/invoice-settings');
    }
}
