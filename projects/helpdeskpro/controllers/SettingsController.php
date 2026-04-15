<?php
/**
 * Helpdesk Pro Settings Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Core\Security;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class SettingsController
{
    private HelpdeskModel $model;

    public function __construct()
    {
        $this->model = new HelpdeskModel();
    }

    public function index(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $this->render('settings/index', [
            'title'    => 'Settings',
            'slaRules' => $this->model->getSlaRules(),
            'isAgent'  => true,
        ]);
    }

    public function saveSla(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/settings');
        }

        $priorities = ['low', 'medium', 'high', 'urgent'];
        $firstResponse = (array) ($_POST['first_response'] ?? []);
        $resolution    = (array) ($_POST['resolution'] ?? []);

        foreach ($priorities as $priority) {
            $fr  = max(1, (int) ($firstResponse[$priority] ?? 24));
            $res = max(1, (int) ($resolution[$priority] ?? 72));
            $this->model->upsertSlaRule($priority, $fr, $res);
        }

        $this->flash('success', 'SLA rules saved.');
        $this->redirect('/projects/helpdeskpro/settings');
    }

    private function isAgent(): bool
    {
        return Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::hasRole('support');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/' . $view . '.php';
    }
}
