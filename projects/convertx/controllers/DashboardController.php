<?php
/**
 * ConvertX Dashboard Controller
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Projects\ConvertX\Models\ConversionJobModel;

class DashboardController
{
    private ConversionJobModel $jobModel;

    public function __construct()
    {
        $this->jobModel = new ConversionJobModel();
    }

    public function index(): void
    {
        $userId = Auth::id();
        $usage  = $userId ? $this->jobModel->getMonthlyUsage($userId) : [];
        $recent = $userId ? $this->jobModel->getHistory($userId, 1, 5)['jobs'] : [];

        $this->render('dashboard', [
            'title'  => 'ConvertX Dashboard',
            'user'   => Auth::user(),
            'usage'  => $usage,
            'recent' => $recent,
        ]);
    }

    public function docs(): void
    {
        $this->render('docs', [
            'title' => 'ConvertX API Docs',
            'user'  => Auth::user(),
        ]);
    }

    public function plan(): void
    {
        $this->render('plan', [
            'title' => 'ConvertX Plans & Pricing',
            'user'  => Auth::user(),
        ]);
    }

    // ------------------------------------------------------------------ //

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }
}
