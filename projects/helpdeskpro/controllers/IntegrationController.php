<?php
/**
 * Helpdesk Pro Integration Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Core\Security;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class IntegrationController
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

        $userId = (int) Auth::id();

        $this->render('integrations/index', [
            'title'          => 'Integrations',
            'apiKeys'        => $this->model->getApiKeys($userId),
            'widgetSettings' => $this->model->getWidgetSettings(),
            'webhooks'       => $this->model->getWebhooks(),
            'isAgent'        => true,
        ]);
    }

    public function createApiKey(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            $this->flash('error', 'Key name is required.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $key = $this->model->createApiKey((int) Auth::id(), mb_substr($name, 0, 100));
        $this->flash('success', 'API key created: ' . $key);
        $this->redirect('/projects/helpdeskpro/integrations');
    }

    public function revokeApiKey(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $this->model->revokeApiKey($id, (int) Auth::id());
        $this->flash('success', 'API key revoked.');
        $this->redirect('/projects/helpdeskpro/integrations');
    }

    public function saveWidgetSettings(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $this->model->saveWidgetSettings(
            mb_substr(trim((string) ($_POST['greeting_text'] ?? 'Hi! How can we help you today?')), 0, 255),
            mb_substr(trim((string) ($_POST['primary_color'] ?? '#3b82f6')), 0, 20),
            trim((string) ($_POST['position'] ?? 'bottom-right')),
            mb_substr(trim((string) ($_POST['widget_title'] ?? 'Support')), 0, 100)
        );

        $this->flash('success', 'Widget settings saved.');
        $this->redirect('/projects/helpdeskpro/integrations');
    }

    public function createWebhook(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $url  = trim((string) ($_POST['url'] ?? ''));

        if ($name === '' || $url === '') {
            $this->flash('error', 'Name and URL are required.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->flash('error', 'Invalid webhook URL.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $allowedEvents = ['ticket.created', 'ticket.updated', 'chat.started', 'chat.closed'];
        $rawEvents = (array) ($_POST['events'] ?? []);
        $events = array_values(array_intersect($rawEvents, $allowedEvents));

        if (empty($events)) {
            $this->flash('error', 'Select at least one event.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $this->model->createWebhook(mb_substr($name, 0, 100), mb_substr($url, 0, 500), $events);
        $this->flash('success', 'Webhook added.');
        $this->redirect('/projects/helpdeskpro/integrations');
    }

    public function deleteWebhook(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/integrations');
        }

        $this->model->deleteWebhook($id);
        $this->flash('success', 'Webhook deleted.');
        $this->redirect('/projects/helpdeskpro/integrations');
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
