<?php
/**
 * BillX Dashboard Controller
 *
 * @package MMB\Projects\BillX\Controllers
 */

namespace Projects\BillX\Controllers;

use Core\Auth;
use Core\Database;
use Projects\BillX\Models\BillModel;

class DashboardController
{
    private BillModel $model;

    public function __construct()
    {
        $this->model = new BillModel();
    }

    /**
     * Load admin settings from billx_settings and apply them to the config:
     * - Filter bill_types to admin-allowed subset
     * - Expose admin_settings array for the views
     */
    private function applyAdminSettings(array $config): array
    {
        $defaults = [
            'max_bills_per_user'   => 500,
            'allowed_bill_types'   => [],
            'default_currency'     => 'INR',
            'require_policy_agree' => 1,
        ];
        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT setting_value FROM billx_settings WHERE setting_key = 'admin_config'"
            );
            if ($row && !empty($row['setting_value'])) {
                $saved = json_decode($row['setting_value'], true);
                if (is_array($saved)) {
                    $defaults = array_merge($defaults, $saved);
                }
            }
        } catch (\Exception $e) {
            // Table may not exist yet — use defaults silently
        }

        if (!empty($defaults['allowed_bill_types'])) {
            $filtered = array_filter(
                $config['bill_types'],
                fn($key) => in_array($key, $defaults['allowed_bill_types'], true),
                ARRAY_FILTER_USE_KEY
            );
            if (!empty($filtered)) {
                $config['bill_types'] = $filtered;
            }
        }

        $config['admin_settings'] = $defaults;
        return $config;
    }

    public function index(): void
    {
        $userId  = Auth::id();
        $recentBills = $this->model->getByUser($userId, 5);
        $totalBills  = $this->model->countByUser($userId);
        $config      = $this->applyAdminSettings(require PROJECT_PATH . '/config.php');

        $this->render('dashboard', [
            'title'       => 'BillX Dashboard',
            'user'        => Auth::user(),
            'recentBills' => $recentBills,
            'totalBills'  => $totalBills,
            'config'      => $config,
        ]);
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
