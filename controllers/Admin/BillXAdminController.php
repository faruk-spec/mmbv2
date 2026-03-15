<?php
/**
 * BillX Admin Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Security;
use Core\Logger;
use Core\ActivityLogger;
use Projects\BillX\Models\BillModel;

class BillXAdminController extends BaseController
{
    private Database $db;
    private BillModel $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->db    = Database::getInstance();
        $this->model = new BillModel();
    }

    // ------------------------------------------------------------------ //
    //  Overview                                                            //
    // ------------------------------------------------------------------ //

    public function overview(): void
    {
        $stats        = $this->getStats();
        $revenue      = $this->model->getRevenueStats();
        $byType       = $this->getBillsByType();
        $recentBills  = $this->getRecentBills(10);
        $activeUsers  = $this->getActiveUsersCount();

        $this->view('admin/projects/billx/overview', [
            'title'       => 'BillX Admin — Overview',
            'stats'       => $stats,
            'revenue'     => $revenue,
            'byType'      => $byType,
            'recentBills' => $recentBills,
            'activeUsers' => $activeUsers,
            'dbConnected' => true,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Bills list                                                          //
    // ------------------------------------------------------------------ //

    public function bills(): void
    {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 30;
        $offset   = ($page - 1) * $perPage;

        $filters = [
            'bill_type'   => trim($_GET['bill_type']    ?? ''),
            'search'      => trim($_GET['search']       ?? ''),
            'user_search' => trim($_GET['user_search']  ?? ''),
            'date_from'   => trim($_GET['date_from']    ?? ''),
            'date_to'     => trim($_GET['date_to']      ?? ''),
        ];

        // Validate date filters
        foreach (['date_from', 'date_to'] as $k) {
            if ($filters[$k] !== '') {
                $d = \DateTime::createFromFormat('Y-m-d', $filters[$k]);
                if (!$d || $d->format('Y-m-d') !== $filters[$k]) {
                    $filters[$k] = '';
                }
            }
        }

        $total    = 0;
        $bills    = [];
        try {
            $total = $this->model->countSearch($filters);
            $bills = $this->model->searchBills($filters, $perPage, $offset);
        } catch (\Exception $e) {
            Logger::error('BillXAdmin bills query: ' . $e->getMessage());
        }

        $billTypes = $this->getBillTypesList();

        $this->view('admin/projects/billx/bills', [
            'title'     => 'BillX Admin — All Bills',
            'bills'     => $bills,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'filters'   => $filters,
            'billTypes' => $billTypes,
            'dbConnected' => true,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  View single bill (GET)                                              //
    // ------------------------------------------------------------------ //

    public function viewBill(int $id): void
    {
        $bill = $this->model->getById($id);
        if (!$bill) {
            http_response_code(404);
            $this->view('admin/projects/billx/bills', [
                'title'     => 'BillX Admin — Bill Not Found',
                'bills'     => [],
                'total'     => 0,
                'page'      => 1,
                'perPage'   => 30,
                'filters'   => [],
                'billTypes' => [],
                'dbConnected' => true,
                'error'     => 'Bill #' . $id . ' not found.',
            ]);
            return;
        }

        $bill['items']         = json_decode($bill['items'] ?? '[]', true) ?: [];
        $bill['template_data'] = json_decode($bill['template_data'] ?? '{}', true) ?: [];

        // Fetch the user info separately if not already joined
        $userRow = null;
        try {
            $userRow = $this->db->fetch(
                "SELECT name, email FROM users WHERE id = ?",
                [(int)$bill['user_id']]
            );
        } catch (\Exception $e) {}

        $config = require BASE_PATH . '/projects/billx/config.php';

        $this->view('admin/projects/billx/view', [
            'title'      => 'BillX Admin — Bill #' . htmlspecialchars($bill['bill_number']),
            'bill'       => $bill,
            'user'       => $userRow,
            'config'     => $config,
            'dbConnected'=> true,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Delete single bill (POST)                                           //
    // ------------------------------------------------------------------ //

    public function deleteBill(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->redirect('/admin/projects/billx/bills?error=invalid_token');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/projects/billx/bills?error=invalid_id');
            return;
        }

        try {
            $this->db->query("DELETE FROM billx_bills WHERE id = ?", [$id]);
            Logger::activity(Auth::id(), 'admin_delete_bill', ['bill_id' => $id]);
            try { ActivityLogger::logDelete(Auth::id(), 'billx', 'bill', $id); } catch (\Throwable $_) {}
            $this->redirect('/admin/projects/billx/bills?deleted=1');
        } catch (\Exception $e) {
            Logger::error('BillXAdmin deleteBill: ' . $e->getMessage());
            try { ActivityLogger::logFailure(Auth::id(), 'admin_delete_bill', $e->getMessage()); } catch (\Throwable $_) {}
            $this->redirect('/admin/projects/billx/bills?error=db_error');
        }
    }

    // ------------------------------------------------------------------ //
    //  Bulk delete (POST)                                                  //
    // ------------------------------------------------------------------ //

    public function bulkDelete(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->redirect('/admin/projects/billx/bills?error=invalid_token');
            return;
        }

        $rawIds = $_POST['ids'] ?? [];
        if (!is_array($rawIds) || empty($rawIds)) {
            $this->redirect('/admin/projects/billx/bills?error=no_ids');
            return;
        }

        $ids = array_filter(array_map('intval', $rawIds), fn($id) => $id > 0);
        if (empty($ids)) {
            $this->redirect('/admin/projects/billx/bills?error=invalid_ids');
            return;
        }

        // Limit bulk delete to 200 records at once to prevent accidental mass deletions
        // and to avoid long-running queries
        $ids = array_slice($ids, 0, 200);

        try {
            $count = $this->model->adminDeleteMultiple($ids);
            Logger::activity(Auth::id(), 'admin_bulk_delete_bills', ['count' => $count, 'ids' => implode(',', $ids)]);
            try { ActivityLogger::log(Auth::id(), 'bulk_deleted', ['module' => 'billx', 'resource_type' => 'bill', 'count' => $count, 'ids' => implode(',', $ids)]); } catch (\Throwable $_) {}
            $this->redirect('/admin/projects/billx/bills?bulk_deleted=' . $count);
        } catch (\Exception $e) {
            Logger::error('BillXAdmin bulkDelete: ' . $e->getMessage());
            try { ActivityLogger::logFailure(Auth::id(), 'admin_bulk_delete_bills', $e->getMessage()); } catch (\Throwable $_) {}
            $this->redirect('/admin/projects/billx/bills?error=db_error');
        }
    }

    // ------------------------------------------------------------------ //
    //  Export CSV (GET)                                                    //
    // ------------------------------------------------------------------ //

    public function exportCsv(): void
    {
        $filters = [
            'bill_type'   => trim($_GET['bill_type']    ?? ''),
            'search'      => trim($_GET['search']       ?? ''),
            'user_search' => trim($_GET['user_search']  ?? ''),
            'date_from'   => trim($_GET['date_from']    ?? ''),
            'date_to'     => trim($_GET['date_to']      ?? ''),
        ];

        // Validate date filters
        foreach (['date_from', 'date_to'] as $k) {
            if ($filters[$k] !== '') {
                $d = \DateTime::createFromFormat('Y-m-d', $filters[$k]);
                if (!$d || $d->format('Y-m-d') !== $filters[$k]) {
                    $filters[$k] = '';
                }
            }
        }

        try {
            $rows = $this->model->getAllForExport($filters);
        } catch (\Exception $e) {
            Logger::error('BillXAdmin exportCsv: ' . $e->getMessage());
            http_response_code(500);
            echo "Export failed.";
            return;
        }

        $filename = 'billx-export-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM for Excel UTF-8 compatibility
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'ID', 'Bill Number', 'Bill Type', 'Bill Date',
            'From (Issuer)', 'To (Recipient)',
            'Subtotal', 'Tax Amount', 'Discount', 'Total Amount', 'Currency',
            'Status', 'User Name', 'User Email', 'Created At',
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $row['bill_number'],
                $row['bill_type'],
                $row['bill_date'],
                $row['from_name'],
                $row['to_name'],
                $row['subtotal'],
                $row['tax_amount'],
                $row['discount_amount'],
                $row['total_amount'],
                $row['currency'],
                $row['status'],
                $row['user_name'] ?? '',
                $row['user_email'] ?? '',
                $row['created_at'],
            ]);
        }
        fclose($out);

        Logger::activity(Auth::id(), 'admin_billx_export_csv', ['rows' => count($rows)]);
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Settings (GET / POST)                                               //
    // ------------------------------------------------------------------ //

    public function settings(): void
    {
        $saved = false;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
                $this->view('admin/projects/billx/settings', [
                    'title'       => 'BillX Admin — Settings',
                    'error'       => 'Invalid CSRF token.',
                    'dbConnected' => true,
                    'saved'       => false,
                    'settings'    => $this->loadSettings(),
                ]);
                return;
            }

            // Collect and persist settings
            $newSettings = [
                'max_bills_per_user' => max(1, min(10000, (int)($_POST['max_bills_per_user'] ?? 500))),
                'allowed_bill_types' => array_keys($_POST['allowed_types'] ?? []),
                'default_currency'   => in_array($_POST['default_currency'] ?? 'INR', ['INR','USD','EUR','GBP'], true)
                    ? $_POST['default_currency'] : 'INR',
                'require_policy_agree' => !empty($_POST['require_policy_agree']) ? 1 : 0,
            ];
            $this->saveSettings($newSettings);
            $saved = true;
            Logger::activity(Auth::id(), 'admin_billx_settings_updated');
            try { ActivityLogger::logUpdate(Auth::id(), 'billx', 'settings', 0, [], $newSettings); } catch (\Throwable $_) {}
        }

        $this->view('admin/projects/billx/settings', [
            'title'       => 'BillX Admin — Settings',
            'dbConnected' => true,
            'saved'       => $saved,
            'error'       => $error,
            'settings'    => $this->loadSettings(),
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers                                                     //
    // ------------------------------------------------------------------ //

    private function getStats(): array
    {
        try {
            $r = $this->db->fetch(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_month
                 FROM billx_bills"
            );
            return $r ?: ['total' => 0, 'today' => 0, 'this_month' => 0];
        } catch (\Exception $e) {
            Logger::error('BillXAdmin getStats: ' . $e->getMessage());
            return ['total' => 0, 'today' => 0, 'this_month' => 0];
        }
    }

    private function getBillsByType(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT bill_type, COUNT(*) AS cnt
                   FROM billx_bills
                  GROUP BY bill_type
                  ORDER BY cnt DESC"
            ) ?: [];
        } catch (\Exception $e) {
            Logger::error('BillXAdmin getBillsByType: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentBills(int $limit = 10): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT b.*, u.name AS user_name, u.email AS user_email
                   FROM billx_bills b
                   LEFT JOIN users u ON b.user_id = u.id
                  ORDER BY b.created_at DESC
                  LIMIT ?",
                [$limit]
            ) ?: [];
        } catch (\Exception $e) {
            Logger::error('BillXAdmin getRecentBills: ' . $e->getMessage());
            return [];
        }
    }

    private function getActiveUsersCount(): int
    {
        try {
            $r = $this->db->fetch(
                "SELECT COUNT(DISTINCT user_id) AS cnt FROM billx_bills"
            );
            return (int)($r['cnt'] ?? 0);
        } catch (\Exception $e) {
            Logger::error('BillXAdmin getActiveUsersCount: ' . $e->getMessage());
            return 0;
        }
    }

    private function getBillTypesList(): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT DISTINCT bill_type FROM billx_bills ORDER BY bill_type"
            ) ?: [];
            return array_column($rows, 'bill_type');
        } catch (\Exception $e) {
            Logger::error('BillXAdmin getBillTypesList: ' . $e->getMessage());
            return [];
        }
    }

    private function loadSettings(): array
    {
        $defaults = [
            'max_bills_per_user'   => 500,
            'allowed_bill_types'   => [],  // empty = all allowed
            'default_currency'     => 'INR',
            'require_policy_agree' => 1,
        ];
        try {
            $row = $this->db->fetch(
                "SELECT setting_value FROM billx_settings WHERE setting_key = 'admin_config'"
            );
            if ($row && !empty($row['setting_value'])) {
                $saved = json_decode($row['setting_value'], true);
                if (is_array($saved)) {
                    return array_merge($defaults, $saved);
                }
            }
        } catch (\Exception $e) {
            // Table may not exist yet — silently use defaults
        }
        return $defaults;
    }

    private function saveSettings(array $data): void
    {
        try {
            $this->ensureSettingsTable();
            $json = json_encode($data);
            $existing = $this->db->fetch(
                "SELECT id FROM billx_settings WHERE setting_key = 'admin_config'"
            );
            if ($existing) {
                $this->db->query(
                    "UPDATE billx_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'admin_config'",
                    [$json]
                );
            } else {
                $this->db->query(
                    "INSERT INTO billx_settings (setting_key, setting_value) VALUES ('admin_config', ?)",
                    [$json]
                );
            }
        } catch (\Exception $e) {
            Logger::error('BillXAdmin saveSettings: ' . $e->getMessage());
            try { ActivityLogger::logFailure(Auth::id(), 'admin_billx_save_settings', $e->getMessage()); } catch (\Throwable $_) {}
        }
    }

    private function ensureSettingsTable(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `billx_settings` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_billx_settings_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // ------------------------------------------------------------------ //
    //  Activity Logs (GET)                                                  //
    // ------------------------------------------------------------------ //

    public function activityLogs(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $userId  = trim($_GET['user_id']  ?? '');
        $action  = trim($_GET['action']   ?? '');
        $search  = trim($_GET['search']   ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo   = trim($_GET['date_to']   ?? '');

        // Validate dates
        foreach ([&$dateFrom, &$dateTo] as &$d) {
            if ($d !== '') {
                $parsed = \DateTime::createFromFormat('Y-m-d', $d);
                if (!$parsed || $parsed->format('Y-m-d') !== $d) $d = '';
            }
        }
        unset($d);

        $where  = ["a.action LIKE 'billx%'"];
        $params = [];

        if ($userId !== '' && ctype_digit($userId)) {
            $where[]  = 'a.user_id = ?';
            $params[] = (int)$userId;
        }
        if ($action !== '') {
            $where[]  = 'a.action = ?';
            $params[] = $action;
        }
        if ($search !== '') {
            $where[]  = '(u.name LIKE ? OR u.email LIKE ? OR a.data LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($dateFrom !== '') {
            $where[]  = 'DATE(a.created_at) >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[]  = 'DATE(a.created_at) <= ?';
            $params[] = $dateTo;
        }

        $whereStr = implode(' AND ', $where);

        $total = 0;
        $logs  = [];
        try {
            $countRow = $this->db->fetch(
                "SELECT COUNT(*) AS cnt
                   FROM activity_logs a
                   LEFT JOIN users u ON a.user_id = u.id
                  WHERE {$whereStr}",
                $params
            );
            $total = (int)($countRow['cnt'] ?? 0);

            $logs = $this->db->fetchAll(
                "SELECT a.*, u.name AS user_name, u.email AS user_email
                   FROM activity_logs a
                   LEFT JOIN users u ON a.user_id = u.id
                  WHERE {$whereStr}
                  ORDER BY a.created_at DESC
                  LIMIT {$perPage} OFFSET {$offset}",
                $params
            ) ?: [];
        } catch (\Exception $e) {
            Logger::error('BillXAdmin activityLogs: ' . $e->getMessage());
        }

        // Distinct actions for filter dropdown
        $actions = [];
        try {
            $actRows = $this->db->fetchAll(
                "SELECT DISTINCT action FROM activity_logs WHERE action LIKE 'billx%' ORDER BY action"
            ) ?: [];
            $actions = array_column($actRows, 'action');
        } catch (\Exception $e) {}

        $this->view('admin/projects/billx/activity-logs', [
            'title'    => 'BillX Admin — Activity Logs',
            'logs'     => $logs,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'filters'  => compact('userId', 'action', 'search', 'dateFrom', 'dateTo'),
            'actions'  => $actions,
            'dbConnected' => true,
        ]);
    }
}

