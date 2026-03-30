<?php
/**
 * CardX Dashboard Controller
 *
 * @package MMB\Projects\IDCard\Controllers
 */

namespace Projects\IDCard\Controllers;

use Core\Auth;
use Projects\IDCard\Models\IDCardModel;

class DashboardController
{
    private IDCardModel $model;

    public function __construct()
    {
        $this->model = new IDCardModel();
    }

    public function index(): void
    {
        $userId     = Auth::id();
        $total      = $this->model->countByUser($userId);
        $recent     = $this->model->getByUser($userId, 5);
        $config     = require PROJECT_PATH . '/config.php';

        $this->render('dashboard', [
            'title'     => 'CardX Dashboard',
            'user'      => Auth::user(),
            'total'     => $total,
            'recent'    => $recent,
            'templates' => $config['templates'],
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
