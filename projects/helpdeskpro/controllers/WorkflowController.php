<?php
/**
 * Helpdesk Pro Workflow Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Core\Security;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class WorkflowController
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

        $this->render('workflows/index', [
            'title'     => 'Workflows',
            'workflows' => $this->model->getWorkflows(),
            'isAgent'   => true,
        ]);
    }

    public function create(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/workflows');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            $this->flash('error', 'Workflow name is required.');
            $this->redirect('/projects/helpdeskpro/workflows');
        }

        $conditionType  = trim((string) ($_POST['condition_type'] ?? ''));
        $conditionValue = trim((string) ($_POST['condition_value'] ?? ''));
        $actionType     = trim((string) ($_POST['action_type'] ?? ''));
        $actionValue    = trim((string) ($_POST['action_value'] ?? ''));

        $conditions = [['type' => $conditionType, 'value' => $conditionValue]];
        $actions    = [['type' => $actionType, 'value' => $actionValue]];

        $this->model->createWorkflow(
            mb_substr($name, 0, 150),
            mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 1000),
            $conditions,
            $actions
        );

        $this->flash('success', 'Workflow created.');
        $this->redirect('/projects/helpdeskpro/workflows');
    }

    public function toggle(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/workflows');
        }

        $workflow = $this->model->getWorkflowById($id);
        if (!$workflow) {
            $this->flash('error', 'Workflow not found.');
            $this->redirect('/projects/helpdeskpro/workflows');
        }

        $isActive = !((bool) $workflow['is_active']);
        $conditions = json_decode((string) ($workflow['conditions'] ?? '[]'), true) ?: [];
        $actions    = json_decode((string) ($workflow['actions'] ?? '[]'), true) ?: [];

        $this->model->updateWorkflow(
            $id,
            (string) $workflow['name'],
            (string) ($workflow['description'] ?? ''),
            $conditions,
            $actions,
            $isActive
        );

        $this->flash('success', 'Workflow ' . ($isActive ? 'activated' : 'deactivated') . '.');
        $this->redirect('/projects/helpdeskpro/workflows');
    }

    public function delete(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/workflows');
        }

        $this->model->deleteWorkflow($id);
        $this->flash('success', 'Workflow deleted.');
        $this->redirect('/projects/helpdeskpro/workflows');
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
