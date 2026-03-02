<?php
/**
 * BillX Dashboard Controller
 *
 * @package MMB\Projects\BillX\Controllers
 */

namespace Projects\BillX\Controllers;

use Core\Auth;
use Projects\BillX\Models\BillModel;

class DashboardController
{
    private BillModel $model;

    public function __construct()
    {
        $this->model = new BillModel();
    }

    public function index(): void
    {
        $userId  = Auth::id();
        $recentBills = $this->model->getByUser($userId, 5);
        $totalBills  = $this->model->countByUser($userId);
        $config      = require PROJECT_PATH . '/config.php';

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
