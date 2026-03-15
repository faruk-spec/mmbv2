<?php
/**
 * BillX Bill Controller
 *
 * @package MMB\Projects\BillX\Controllers
 */

namespace Projects\BillX\Controllers;

use Core\Auth;
use Core\Security;
use Core\Logger;
use Core\Database;
use Projects\BillX\Models\BillModel;

class BillController
{
    private BillModel $model;

    public function __construct()
    {
        $this->model = new BillModel();
    }

    /**
     * Load admin settings from billx_settings table.
     * Returns defaults if table doesn't exist yet.
     */
    private function loadAdminSettings(): array
    {
        $defaults = [
            'max_bills_per_user'   => 500,
            'allowed_bill_types'   => [],  // empty = all allowed
            'default_currency'     => 'INR',
            'require_policy_agree' => 1,
        ];
        try {
            $db = Database::getInstance();
            $row = $db->fetch(
                "SELECT setting_value FROM billx_settings WHERE setting_key = 'admin_config'"
            );
            if ($row && !empty($row['setting_value'])) {
                $saved = json_decode($row['setting_value'], true);
                if (is_array($saved)) {
                    return array_merge($defaults, $saved);
                }
            }
        } catch (\Exception $e) {
            // Table may not exist yet — return defaults silently
        }
        return $defaults;
    }

    /**
     * Merge admin settings into the bill config:
     * - Filter bill_types to only allowed ones (if any restriction set)
     * - Apply default currency from admin settings
     * - Apply require_policy_agree flag
     */
    private function applyAdminSettings(array $config): array
    {
        $settings = $this->loadAdminSettings();

        // Filter allowed bill types
        if (!empty($settings['allowed_bill_types'])) {
            $config['bill_types'] = array_filter(
                $config['bill_types'],
                fn($key) => in_array($key, $settings['allowed_bill_types'], true),
                ARRAY_FILTER_USE_KEY
            );
            // If all types filtered out (bad config), restore all types
            if (empty($config['bill_types'])) {
                $config['bill_types'] = (require PROJECT_PATH . '/config.php')['bill_types'];
            }
        }

        // Expose admin settings as config keys for the view
        $config['admin_settings'] = $settings;

        return $config;
    }

    /** GET /projects/billx/generate */
    public function showForm(): void
    {
        $config = $this->applyAdminSettings(require PROJECT_PATH . '/config.php');
        $this->render('generate', [
            'title'  => 'Generate Bill',
            'user'   => Auth::user(),
            'config' => $config,
        ]);
    }

    /** POST /projects/billx/generate */
    public function generate(): void
    {
        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo "Invalid CSRF token.";
            return;
        }

        $userId = Auth::id();
        $config = $this->applyAdminSettings(require PROJECT_PATH . '/config.php');

        $billType   = Security::sanitize($_POST['bill_type']   ?? 'general');
        $billNumber = Security::sanitize(substr($_POST['bill_number'] ?? '', 0, 50));
        $billDate   = $_POST['bill_date'] ?? date('Y-m-d');
        $fromName   = Security::sanitize(substr($_POST['from_name']    ?? '', 0, 255));
        $fromAddr   = Security::sanitize(substr($_POST['from_address'] ?? '', 0, 1000));
        $fromPhone  = Security::sanitize(substr($_POST['from_phone']   ?? '', 0, 50));
        $fromEmail  = Security::sanitize(substr($_POST['from_email']   ?? '', 0, 255));
        $toName     = Security::sanitize(substr($_POST['to_name']      ?? '', 0, 255));
        $toAddr     = Security::sanitize(substr($_POST['to_address']   ?? '', 0, 1000));
        $toPhone    = Security::sanitize(substr($_POST['to_phone']     ?? '', 0, 50));
        $toEmail    = Security::sanitize(substr($_POST['to_email']     ?? '', 0, 255));
        $notes      = Security::sanitize(substr($_POST['notes']        ?? '', 0, 2000));
        $currency   = Security::sanitize($_POST['currency'] ?? 'INR');
        $taxPct     = (float)($_POST['tax_percent']     ?? 0);
        $discount   = (float)($_POST['discount_amount'] ?? 0);
        $saveAction = Security::sanitize($_POST['save_action'] ?? 'view');

        // Enforce max bills per user limit from admin settings
        $maxBills = (int)($config['admin_settings']['max_bills_per_user'] ?? 500);
        if ($maxBills > 0 && $this->model->countByUser($userId) >= $maxBills) {
            $this->render('generate', [
                'title'  => 'Generate Bill',
                'user'   => Auth::user(),
                'config' => $config,
                'error'  => "You have reached the maximum limit of {$maxBills} bills. Please delete older bills to continue.",
            ]);
            return;
        }

        // Validate bill_date is a real calendar date in YYYY-MM-DD format
        $parsedDate = \DateTime::createFromFormat('Y-m-d', $billDate);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $billDate) {
            $billDate = date('Y-m-d');
        }

        // Clamp numeric fields to sane ranges
        $taxPct   = max(0.0, min(100.0, $taxPct));
        $discount = max(0.0, $discount);

        // Validate currency against allowed list
        $allowedCurrencies = ['INR', 'USD', 'EUR', 'GBP'];
        if (!in_array($currency, $allowedCurrencies, true)) {
            $currency = 'INR';
        }

        // Validate save_action
        if (!in_array($saveAction, ['save', 'download', 'view'], true)) {
            $saveAction = 'view';
        }

        // Validate email fields
        if ($fromEmail !== '' && !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = '';
        }
        if ($toEmail !== '' && !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $toEmail = '';
        }

        // Collect template_data from td_* POST fields (type-specific extras: CGST, SGST, vehicle, etc.)
        // Only accept scalar string values to prevent injection of unexpected data types.
        // Each field is truncated to 500 characters to prevent oversized DB entries.
        $maxTdFieldLen = 500;
        $templateData = [];
        foreach ($_POST as $k => $v) {
            if (strncmp($k, 'td_', 3) === 0 && is_string($v)) {
                $templateData[substr($k, 3)] = Security::sanitize(substr($v, 0, $maxTdFieldLen));
            }
        }

        // Validate bill type
        if (!array_key_exists($billType, $config['bill_types'])) {
            $billType = 'general';
        }

        // Build items array
        $descriptions = $_POST['item_description'] ?? [];
        $quantities   = $_POST['item_qty']         ?? [];
        $rates        = $_POST['item_rate']        ?? [];

        // Limit items to a reasonable count (prevent abuse)
        $maxItems = 100;
        $items    = [];
        $subtotal = 0.0;
        foreach (array_slice((array)$descriptions, 0, $maxItems) as $i => $desc) {
            $desc   = Security::sanitize(substr((string)$desc, 0, 500));
            $qty    = max(0.0, (float)($quantities[$i] ?? 1));
            $rate   = max(0.0, (float)($rates[$i]      ?? 0));
            $amount = $qty * $rate;
            if ($desc === '' && $rate == 0) continue;
            $items[] = ['description' => $desc, 'qty' => $qty, 'rate' => $rate, 'amount' => $amount];
            $subtotal += $amount;
        }

        $cgstPct   = (float)($templateData['cgst_pct'] ?? 0);
        $sgstPct   = (float)($templateData['sgst_pct'] ?? 0);
        $cgstPct   = max(0.0, min(50.0, $cgstPct));
        $sgstPct   = max(0.0, min(50.0, $sgstPct));
        $cgstAmt   = round($subtotal * $cgstPct / 100, 2);
        $sgstAmt   = round($subtotal * $sgstPct / 100, 2);
        $taxAmount = round($subtotal * $taxPct / 100, 2);
        $total     = round($subtotal + $taxAmount + $cgstAmt + $sgstAmt - $discount, 2);

        $id = $this->model->create([
            'user_id'         => $userId,
            'bill_type'       => $billType,
            'bill_number'     => $billNumber,
            'bill_date'       => $billDate,
            'from_name'       => $fromName,
            'from_address'    => $fromAddr,
            'from_phone'      => $fromPhone,
            'from_email'      => $fromEmail,
            'to_name'         => $toName,
            'to_address'      => $toAddr,
            'to_phone'        => $toPhone,
            'to_email'        => $toEmail,
            'items'           => json_encode($items),
            'subtotal'        => $subtotal,
            'tax_percent'     => $taxPct,
            'tax_amount'      => $taxAmount,
            'discount_amount' => $discount,
            'total_amount'    => $total,
            'notes'           => $notes,
            'currency'        => $currency,
            'template_data'   => $templateData ? json_encode($templateData) : null,
            'status'          => 'generated',
        ]);

        if ($id) {
            Logger::activity($userId, 'billx_bill_created', ['bill_id' => $id, 'type' => $billType]);
            if ($saveAction === 'save') {
                header('Location: /projects/billx/history?saved=1');
            } elseif ($saveAction === 'download') {
                // Redirect to PDF page with download=1 so JS auto-triggers actual PDF download
                header('Location: /projects/billx/pdf/' . $id . '?download=1');
            } else {
                header('Location: /projects/billx/view/' . $id);
            }
            exit;
        }

        // Fallback: re-render form with error
        $this->render('generate', [
            'title'  => 'Generate Bill',
            'user'   => Auth::user(),
            'config' => $config,
            'error'  => 'Failed to save bill. Please try again.',
        ]);
    }

    /** GET /projects/billx/history */
    public function history(): void
    {
        $userId = Auth::id();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $bills = $this->model->getByUser($userId, $limit, $offset);
        $total = $this->model->countByUser($userId);
        $pages = (int)ceil($total / $limit);

        $config = require PROJECT_PATH . '/config.php';

        $this->render('history', [
            'title'  => 'Bill History',
            'user'   => Auth::user(),
            'bills'  => $bills,
            'total'  => $total,
            'page'   => $page,
            'pages'  => $pages,
            'config' => $config,
        ]);
    }

    /** GET /projects/billx/view/{id} */
    public function view(int $id): void
    {
        $userId  = Auth::id();
        $isAdmin = Auth::isAdmin();
        $bill    = $this->model->getById($id);

        if (!$bill || ((int)$bill['user_id'] !== $userId && !$isAdmin)) {
            http_response_code(404);
            echo "Bill not found.";
            return;
        }

        $bill['items'] = json_decode($bill['items'] ?? '[]', true) ?: [];
        $config = require PROJECT_PATH . '/config.php';

        $this->render('view', [
            'title'  => 'View Bill #' . htmlspecialchars($bill['bill_number']),
            'user'   => Auth::user(),
            'bill'   => $bill,
            'config' => $config,
        ]);
    }

    /** GET /projects/billx/pdf/{id} — standalone print/PDF view */
    public function pdf(int $id): void
    {
        $userId  = Auth::id();
        $isAdmin = Auth::isAdmin();
        $bill    = $this->model->getById($id);

        // Allow the bill owner OR any admin to view/print/PDF a bill
        if (!$bill || ((int)$bill['user_id'] !== $userId && !$isAdmin)) {
            http_response_code(404);
            echo "Bill not found.";
            return;
        }

        $bill['items'] = json_decode($bill['items'] ?? '[]', true) ?: [];
        $config = require PROJECT_PATH . '/config.php';

        header('Content-Type: text/html; charset=utf-8');
        // Render standalone PDF-print page (no site layout)
        extract(['bill' => $bill, 'config' => $config]);
        include PROJECT_PATH . '/views/pdf.php';
        Logger::activity($userId, 'billx_bill_pdf', ['bill_id' => $id]);
    }

    /** GET /projects/billx/download/{id} */
    public function download(int $id): void
    {
        $userId  = Auth::id();
        $isAdmin = Auth::isAdmin();
        $bill    = $this->model->getById($id);

        // Allow the bill owner OR any admin to download a bill
        if (!$bill || ((int)$bill['user_id'] !== $userId && !$isAdmin)) {
            http_response_code(404);
            echo "Bill not found.";
            return;
        }

        Logger::activity($userId, 'billx_bill_download', ['bill_id' => $id]);
        header('Location: /projects/billx/pdf/' . $id . '?download=1');
    }

    /** POST /projects/billx/delete */
    public function delete(): void
    {
        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            header('Location: /projects/billx/history?error=csrf');
            exit;
        }

        $userId = Auth::id();
        $id     = (int)($_POST['id'] ?? 0);

        if ($id && $this->model->delete($id, $userId)) {
            Logger::activity($userId, 'billx_bill_deleted', ['bill_id' => $id]);
            header('Location: /projects/billx/history?deleted=1');
        } else {
            header('Location: /projects/billx/history?error=1');
        }
        exit;
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        include PROJECT_PATH . '/views/layout.php';
    }
}
