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

class BillXAdminController extends BaseController
{
    private $db;
    private $billxDb;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->db = Database::getInstance();

        try {
            $this->billxDb = Database::getProjectInstance('billx');
        } catch (\Exception $e) {
            $this->billxDb = null;
            Logger::error('BillXAdmin: could not connect to billx DB — ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Overview                                                            //
    // ------------------------------------------------------------------ //

    public function overview(): void
    {
        $stats      = $this->getStats();
        $byType     = $this->getBillsByType();
        $recentBills = $this->getRecentBills(10);
        $activeUsers = $this->getActiveUsersCount();

        $this->view('admin/projects/billx/overview', [
            'title'       => 'BillX Admin — Overview',
            'stats'       => $stats,
            'byType'      => $byType,
            'recentBills' => $recentBills,
            'activeUsers' => $activeUsers,
            'dbConnected' => $this->billxDb !== null,
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
        $billType = trim($_GET['bill_type'] ?? '');

        $total = 0;
        $bills = [];

        if ($this->billxDb) {
            try {
                $where  = '1=1';
                $params = [];

                if ($billType !== '') {
                    $where           .= ' AND b.bill_type = ?';
                    $params[]         = $billType;
                }

                $countRow = $this->billxDb->fetch(
                    "SELECT COUNT(*) AS c FROM billx_bills b WHERE $where",
                    $params
                );
                $total = (int)($countRow['c'] ?? 0);

                $bills = $this->billxDb->fetchAll(
                    "SELECT b.*, u.name AS user_name, u.email AS user_email
                       FROM billx_bills b
                       LEFT JOIN mmb_main.users u ON b.user_id = u.id
                      WHERE $where
                      ORDER BY b.created_at DESC
                      LIMIT $perPage OFFSET $offset",
                    $params
                );
            } catch (\Exception $e) {
                Logger::error('BillXAdmin bills query: ' . $e->getMessage());
            }
        }

        $billTypes = $this->getBillTypesList();

        $this->view('admin/projects/billx/bills', [
            'title'    => 'BillX Admin — All Bills',
            'bills'    => $bills,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'billType' => $billType,
            'billTypes'=> $billTypes,
            'dbConnected' => $this->billxDb !== null,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Delete bill (POST)                                                  //
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

        if (!$this->billxDb) {
            $this->redirect('/admin/projects/billx/bills?error=db_unavailable');
            return;
        }

        try {
            $this->billxDb->query("DELETE FROM billx_bills WHERE id = ?", [$id]);
            Logger::activity(Auth::id(), 'admin_delete_bill', ['bill_id' => $id]);
            $this->redirect('/admin/projects/billx/bills?deleted=1');
        } catch (\Exception $e) {
            Logger::error('BillXAdmin deleteBill: ' . $e->getMessage());
            $this->redirect('/admin/projects/billx/bills?error=db_error');
        }
    }

    // ------------------------------------------------------------------ //
    //  Settings (GET / POST)                                               //
    // ------------------------------------------------------------------ //

    public function settings(): void
    {
        $saved = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
                $this->view('admin/projects/billx/settings', [
                    'title'      => 'BillX Admin — Settings',
                    'error'      => 'Invalid CSRF token.',
                    'dbConnected'=> $this->billxDb !== null,
                    'saved'      => false,
                ]);
                return;
            }
            // Settings are informational; nothing to persist right now.
            $saved = true;
            Logger::activity(Auth::id(), 'admin_billx_settings_updated');
        }

        $this->view('admin/projects/billx/settings', [
            'title'      => 'BillX Admin — Settings',
            'dbConnected'=> $this->billxDb !== null,
            'saved'      => $saved,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers                                                     //
    // ------------------------------------------------------------------ //

    private function getStats(): array
    {
        if (!$this->billxDb) {
            return ['total' => 0, 'today' => 0, 'this_month' => 0];
        }
        try {
            $r = $this->billxDb->fetch(
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
        if (!$this->billxDb) return [];
        try {
            return $this->billxDb->fetchAll(
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
        if (!$this->billxDb) return [];
        try {
            return $this->billxDb->fetchAll(
                "SELECT b.*, u.name AS user_name, u.email AS user_email
                   FROM billx_bills b
                   LEFT JOIN mmb_main.users u ON b.user_id = u.id
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
        if (!$this->billxDb) return 0;
        try {
            $r = $this->billxDb->fetch(
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
        if (!$this->billxDb) return [];
        try {
            $rows = $this->billxDb->fetchAll(
                "SELECT DISTINCT bill_type FROM billx_bills ORDER BY bill_type"
            ) ?: [];
            return array_column($rows, 'bill_type');
        } catch (\Exception $e) {
            Logger::error('BillXAdmin getBillTypesList: ' . $e->getMessage());
            return [];
        }
    }
}
