<?php
/**
 * Helpdesk Pro Analytics Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class AnalyticsController
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

        $this->render('analytics/index', [
            'title'            => 'Analytics',
            'ticketAnalytics'  => $this->model->getTicketAnalytics(),
            'liveAnalytics'    => $this->model->getLiveAnalytics(),
            'agentPerformance' => $this->model->getAgentPerformance(),
            'isAgent'          => true,
        ]);
    }

    private function isAgent(): bool
    {
        return Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::hasRole('support');
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/' . $view . '.php';
    }
}
