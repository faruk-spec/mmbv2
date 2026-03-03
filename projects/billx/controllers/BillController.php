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
use Projects\BillX\Models\BillModel;

class BillController
{
    private BillModel $model;

    public function __construct()
    {
        $this->model = new BillModel();
    }

    /** GET /projects/billx/generate */
    public function showForm(): void
    {
        $config = require PROJECT_PATH . '/config.php';
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
        $config = require PROJECT_PATH . '/config.php';

        $billType   = Security::sanitize($_POST['bill_type']   ?? 'general');
        $billNumber = Security::sanitize($_POST['bill_number'] ?? '');
        $billDate   = Security::sanitize($_POST['bill_date']   ?? date('Y-m-d'));
        $fromName   = Security::sanitize($_POST['from_name']   ?? '');
        $fromAddr   = Security::sanitize($_POST['from_address'] ?? '');
        $fromPhone  = Security::sanitize($_POST['from_phone']  ?? '');
        $fromEmail  = Security::sanitize($_POST['from_email']  ?? '');
        $toName     = Security::sanitize($_POST['to_name']     ?? '');
        $toAddr     = Security::sanitize($_POST['to_address']  ?? '');
        $toPhone    = Security::sanitize($_POST['to_phone']    ?? '');
        $toEmail    = Security::sanitize($_POST['to_email']    ?? '');
        $notes      = Security::sanitize($_POST['notes']       ?? '');
        $currency   = Security::sanitize($_POST['currency']    ?? 'INR');
        $taxPct     = (float)($_POST['tax_percent']      ?? 0);
        $discount   = (float)($_POST['discount_amount']  ?? 0);
        $saveAction = Security::sanitize($_POST['save_action'] ?? 'view');

        // Collect template_data from td_* POST fields (type-specific extras: CGST, SGST, vehicle, etc.)
        // Only accept scalar string values to prevent injection of unexpected data types.
        $templateData = [];
        foreach ($_POST as $k => $v) {
            if (strncmp($k, 'td_', 3) === 0 && is_string($v)) {
                $templateData[substr($k, 3)] = Security::sanitize($v);
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

        $items    = [];
        $subtotal = 0.0;
        foreach ($descriptions as $i => $desc) {
            $desc   = Security::sanitize($desc);
            $qty    = (float)($quantities[$i] ?? 1);
            $rate   = (float)($rates[$i]      ?? 0);
            $amount = $qty * $rate;
            if ($desc === '' && $rate == 0) continue;
            $items[] = ['description' => $desc, 'qty' => $qty, 'rate' => $rate, 'amount' => $amount];
            $subtotal += $amount;
        }

        $taxAmount = round($subtotal * $taxPct / 100, 2);
        $total     = round($subtotal + $taxAmount - $discount, 2);

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
        $userId = Auth::id();
        $bill   = $this->model->getById($id);

        if (!$bill || (int)$bill['user_id'] !== $userId) {
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
        $userId = Auth::id();
        $bill   = $this->model->getById($id);

        if (!$bill || (int)$bill['user_id'] !== $userId) {
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
        $userId = Auth::id();
        $bill   = $this->model->getById($id);

        if (!$bill || (int)$bill['user_id'] !== $userId) {
            http_response_code(404);
            echo "Bill not found.";
            return;
        }

        Logger::activity($userId, 'billx_bill_pdf_view', ['bill_id' => $id]);
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
