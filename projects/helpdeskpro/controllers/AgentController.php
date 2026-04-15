<?php
/**
 * Helpdesk Pro Agent Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class AgentController
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

        $agents = $this->model->getAllAgents();
        $performance = $this->model->getAgentPerformance();

        $perfMap = [];
        foreach ($performance as $p) {
            $perfMap[(int) $p['id']] = $p;
        }

        foreach ($agents as &$agent) {
            $aid = (int) $agent['id'];
            $agent['tickets_handled'] = $perfMap[$aid]['tickets_handled'] ?? 0;
            $agent['active_tickets']  = $perfMap[$aid]['active_tickets'] ?? 0;
            $agent['avg_resolution_hours'] = $perfMap[$aid]['avg_resolution_hours'] ?? 0;
        }
        unset($agent);

        $this->render('agents/index', [
            'title'   => 'Agents & Roles',
            'agents'  => $agents,
            'isAgent' => true,
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
