<?php
/**
 * Support Ticket Controller (user-facing)
 *
 * @package Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Mailer;
use Core\Notification;
use Models\SupportModel;

class SupportController extends BaseController
{
    private SupportModel $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->model = new SupportModel();
    }

    // -------------------------------------------------------------------------
    // GET /support
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $userId  = Auth::id();
        $tickets = $this->model->getTicketsByUser($userId);
        $stats   = $this->model->getTicketStatsByUser($userId);

        $this->view('support/tickets', [
            'title'          => 'My Support Tickets',
            'tickets'        => $tickets,
            'stats'          => $stats,
            'currentPage'    => 'tickets',
            'isSupportAdmin' => $this->isSupportAdmin(),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /support/create
    // -------------------------------------------------------------------------

    public function createForm(): void
    {
        $categories = $this->model->getTemplateCategories();
        $items      = $this->model->getTemplateItemsWithSchema();

        $this->view('support/create', [
            'title'          => 'Create Support Ticket',
            'categories'     => $categories,
            'items'          => $items,
            'priorities'     => ['low', 'medium', 'high', 'urgent'],
            'currentPage'    => 'create',
            'isSupportAdmin' => $this->isSupportAdmin(),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /support/create
    // -------------------------------------------------------------------------

    public function store(): void
    {
        $this->validateCsrf();

        $subject        = trim($_POST['subject'] ?? '');
        $description    = trim($_POST['description'] ?? '');
        $priority       = $_POST['priority'] ?? 'medium';
        $templateItemId = !empty($_POST['template_item_id']) ? (int) $_POST['template_item_id'] : null;

        $validPriorities = ['low', 'medium', 'high', 'urgent'];

        if ($subject === '' || strlen($subject) > 255) {
            $this->flash('error', 'Subject is required and must not exceed 255 characters.');
            $this->redirect('/support/create');
            return;
        }
        if ($description === '' || strlen($description) > 5000) {
            $this->flash('error', 'Description is required and must not exceed 5000 characters.');
            $this->redirect('/support/create');
            return;
        }
        if (!in_array($priority, $validPriorities, true)) {
            $priority = 'medium';
        }

        // Collect custom field values and append to description
        $customFieldsData = [];
        if ($templateItemId) {
            $templateItem = $this->model->getTemplateItemById($templateItemId);
            if ($templateItem && !empty($templateItem['fields_schema'])) {
                $schema = json_decode($templateItem['fields_schema'], true) ?: [];
                foreach ($schema as $field) {
                    $fieldName = $field['name'] ?? '';
                    if ($fieldName === '') continue;
                    $value = trim($_POST['custom_' . $fieldName] ?? '');
                    if ($value !== '') {
                        $customFieldsData[$fieldName] = [
                            'label' => $field['label'] ?? $fieldName,
                            'value' => $value,
                        ];
                    }
                }
            }
        }

        // Append custom fields to description
        if (!empty($customFieldsData)) {
            $description .= "\n\n--- Additional Details ---";
            foreach ($customFieldsData as $fd) {
                $description .= "\n" . $fd['label'] . ': ' . $fd['value'];
            }
        }

        $userId   = Auth::id();
        $ticketId = $this->model->createTicket($userId, $templateItemId, $subject, $description, $priority);
        $this->model->addTicketMessage($ticketId, 'user', $userId, $description);

        // Email the user
        $user      = Auth::user();
        $ticketUrl = $this->baseUrl() . '/support/view/' . $ticketId;

        $emailVars = [
            'ticketId'    => $ticketId,
            'subject'     => $subject,
            'userName'    => $user['name'] ?? 'User',
            'ticketUrl'   => $ticketUrl,
            'description' => $description,
        ];
        $emailBody = $this->renderEmail('support-ticket-created', $emailVars);
        if ($emailBody !== null && !empty($user['email'])) {
            try {
                Mailer::send($user['email'], "Support Ticket #{$ticketId} Created: {$subject}", $emailBody);
            } catch (\Throwable $e) {
                // Mail failure is non-fatal
            }
        }

        // Notify all admins
        $adminUrl = '/admin/support/tickets/' . $ticketId;
        $this->notifyAdmins(
            'support_ticket',
            "New support ticket #{$ticketId}: {$subject}",
            ['ticket_id' => $ticketId, 'url' => $adminUrl]
        );

        $this->flash('success', "Support ticket #{$ticketId} created successfully.");
        $this->redirect('/support/view/' . $ticketId);
    }

    // -------------------------------------------------------------------------
    // GET /support/view/{id}
    // -------------------------------------------------------------------------

    public function show(int $id): void
    {
        $ticket = $this->model->getTicketById($id);

        if (!$ticket || (int) $ticket['user_id'] !== Auth::id()) {
            http_response_code(403);
            parent::view('errors/403', ['title' => 'Forbidden']);
            return;
        }

        $messages = $this->model->getTicketMessages($id, false);

        parent::view('support/view', [
            'title'          => "Ticket #{$id}: " . htmlspecialchars($ticket['subject']),
            'ticket'         => $ticket,
            'messages'       => $messages,
            'currentPage'    => 'tickets',
            'isSupportAdmin' => $this->isSupportAdmin(),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /support/view/{id}/reply
    // -------------------------------------------------------------------------

    public function reply(int $id): void
    {
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket || (int) $ticket['user_id'] !== Auth::id()) {
            http_response_code(403);
            parent::view('errors/403', ['title' => 'Forbidden']);
            return;
        }

        if ($ticket['status'] === 'closed') {
            $this->flash('error', 'This ticket is closed and cannot receive new replies.');
            $this->redirect('/support/view/' . $id);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        if ($message === '' || strlen($message) > 5000) {
            $this->flash('error', 'Reply message is required and must not exceed 5000 characters.');
            $this->redirect('/support/view/' . $id);
            return;
        }

        $this->model->addTicketMessage($id, 'user', Auth::id(), $message);

        $adminUrl = '/admin/support/tickets/' . $id;
        $this->notifyAdmins(
            'support_ticket_reply',
            "New reply on ticket #{$id}: " . $ticket['subject'],
            ['ticket_id' => $id, 'url' => $adminUrl]
        );

        $this->flash('success', 'Your reply has been submitted.');
        $this->redirect('/support/view/' . $id);
    }

    // -------------------------------------------------------------------------
    // GET /support/faq
    // -------------------------------------------------------------------------

    public function faq(): void
    {
        $this->view('support/faq', [
            'title'          => 'Frequently Asked Questions',
            'currentPage'    => 'faq',
            'isSupportAdmin' => $this->isSupportAdmin(),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /support/live
    // -------------------------------------------------------------------------

    public function liveSupport(): void
    {
        $this->view('support/live', [
            'title'          => 'Live Support',
            'currentPage'    => 'live',
            'isSupportAdmin' => $this->isSupportAdmin(),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /support/help
    // -------------------------------------------------------------------------

    public function help(): void
    {
        $this->view('support/help', [
            'title'          => 'Help & Resources',
            'currentPage'    => 'help',
            'isSupportAdmin' => $this->isSupportAdmin(),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /support/announcements
    // -------------------------------------------------------------------------

    public function announcements(): void
    {
        $this->view('support/announcements', [
            'title'          => 'Announcements',
            'currentPage'    => 'announcements',
            'isSupportAdmin' => $this->isSupportAdmin(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function isSupportAdmin(): bool
    {
        if (Auth::isAdmin()) {
            return true;
        }
        return $this->model->isAgent(Auth::id());
    }

    private function baseUrl(): string
    {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    private function notifyAdmins(string $type, string $message, array $data = []): void
    {
        $db     = Database::getInstance();
        $admins = $db->fetchAll("SELECT id FROM users WHERE role LIKE '%admin%'") ?: [];
        foreach ($admins as $admin) {
            Notification::send((int) $admin['id'], $type, $message, $data);
        }
    }

    private function renderEmail(string $template, array $vars): ?string
    {
        $file = BASE_PATH . '/views/emails/' . $template . '.php';
        if (!file_exists($file)) {
            return null;
        }
        ob_start();
        extract($vars, EXTR_SKIP);
        include $file;
        return ob_get_clean() ?: null;
    }
}
