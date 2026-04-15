<?php
/**
 * Support Admin Controller
 *
 * @package Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Mailer;
use Core\Notification;
use Models\SupportModel;

class SupportAdminController extends BaseController
{
    private SupportModel $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->model = new SupportModel();
    }

    // -------------------------------------------------------------------------
    // GET /admin/support
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $stats       = $this->model->getTicketStats();
        $activeChats = count($this->model->getAllActiveChats());

        $this->view('admin/support/index', [
            'title'       => 'Support Overview',
            'stats'       => $stats,
            'activeChats' => $activeChats,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /admin/support/tickets
    // -------------------------------------------------------------------------

    public function tickets(): void
    {
        $filters  = [
            'status'   => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
        ];
        // Remove empty filters
        $filters = array_filter($filters, fn($v) => $v !== '');

        $tickets = $this->model->getAllTickets($filters);
        $stats   = $this->model->getTicketStats();

        $this->view('admin/support/tickets', [
            'title'   => 'Support Tickets',
            'tickets' => $tickets,
            'stats'   => $stats,
            'filters' => array_merge(['status' => '', 'priority' => ''], $filters),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /admin/support/tickets/{id}
    // -------------------------------------------------------------------------

    public function viewTicket(int $id): void
    {
        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Ticket Not Found']);
            return;
        }

        $messages = $this->model->getTicketMessages($id, true);

        $this->view('admin/support/ticket-view', [
            'title'    => "Ticket #{$id}: " . htmlspecialchars($ticket['subject']),
            'ticket'   => $ticket,
            'messages' => $messages,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/tickets/{id}/reply
    // -------------------------------------------------------------------------

    public function replyTicket(int $id): void
    {
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            $this->flash('error', 'Ticket not found.');
            $this->redirect('/admin/support/tickets');
            return;
        }
        if ($ticket['status'] === 'closed') {
            $this->flash('error', 'This ticket is closed.');
            $this->redirect('/admin/support/tickets/' . $id);
            return;
        }

        $message    = trim($_POST['message'] ?? '');
        $isInternal = !empty($_POST['is_internal']);

        if ($message === '') {
            $this->flash('error', 'Reply message cannot be empty.');
            $this->redirect('/admin/support/tickets/' . $id);
            return;
        }

        $this->model->addTicketMessage($id, 'agent', Auth::id(), $message, $isInternal);

        if (!$isInternal) {
            // Email the user
            $ticketUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                       . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                       . '/support/view/' . $id;

            $emailVars = [
                'ticketId'     => $id,
                'subject'      => $ticket['subject'],
                'userName'     => $ticket['user_name'],
                'replyMessage' => $message,
                'ticketUrl'    => $ticketUrl,
                'status'       => $ticket['status'],
            ];
            $emailBody = $this->renderEmail('support-ticket-reply', $emailVars);
            if ($emailBody !== null && !empty($ticket['user_email'])) {
                Mailer::send(
                    $ticket['user_email'],
                    "Update on your Support Ticket #{$id}",
                    $emailBody
                );
            }

            // In-app notification
            Notification::send(
                (int) $ticket['user_id'],
                'support_ticket_reply',
                "An agent replied to your ticket #{$id}: " . $ticket['subject'],
                ['ticket_id' => $id, 'url' => '/support/view/' . $id]
            );
        }

        $this->flash('success', 'Reply submitted successfully.');
        $this->redirect('/admin/support/tickets/' . $id);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/tickets/{id}/status
    // -------------------------------------------------------------------------

    public function updateTicketStatus(int $id): void
    {
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            $this->flash('error', 'Ticket not found.');
            $this->redirect('/admin/support/tickets');
            return;
        }

        $validStatuses = ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'];
        $status        = $_POST['status'] ?? '';
        if (!in_array($status, $validStatuses, true)) {
            $this->flash('error', 'Invalid status.');
            $this->redirect('/admin/support/tickets/' . $id);
            return;
        }

        $this->model->updateTicketStatus($id, $status, Auth::id());

        if ($status === 'closed') {
            $ticketUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                       . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                       . '/support/view/' . $id;

            $resolution = trim($_POST['resolution'] ?? 'Your issue has been resolved.');
            $emailVars  = [
                'ticketId'   => $id,
                'subject'    => $ticket['subject'],
                'userName'   => $ticket['user_name'],
                'ticketUrl'  => $ticketUrl,
                'resolution' => $resolution,
            ];
            $emailBody = $this->renderEmail('support-ticket-closed', $emailVars);
            if ($emailBody !== null && !empty($ticket['user_email'])) {
                Mailer::send(
                    $ticket['user_email'],
                    "Support Ticket #{$id} Closed",
                    $emailBody
                );
            }

            Notification::send(
                (int) $ticket['user_id'],
                'support_ticket_closed',
                "Your support ticket #{$id} has been closed.",
                ['ticket_id' => $id, 'url' => '/support/view/' . $id]
            );
        }

        $this->flash('success', "Ticket status updated to '{$status}'.");
        $this->redirect('/admin/support/tickets/' . $id);
    }

    // -------------------------------------------------------------------------
    // GET /admin/support/live-chats
    // -------------------------------------------------------------------------

    public function liveChats(): void
    {
        $chats = $this->model->getAllChats();

        $this->view('admin/support/live-chats', [
            'title' => 'Live Chats',
            'chats' => $chats,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /admin/support/live-chats/{id}
    // -------------------------------------------------------------------------

    public function viewLiveChat(int $id): void
    {
        $chat = $this->model->getChatById($id);
        if (!$chat) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Chat Not Found']);
            return;
        }

        $messages = $this->model->getLiveMessages($id);

        $this->view('admin/support/live-chat-view', [
            'title'    => "Live Chat #{$id}",
            'chat'     => $chat,
            'messages' => $messages,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /admin/support/templates
    // -------------------------------------------------------------------------

    public function templates(): void
    {
        $categories = $this->model->getTemplateCategories();
        $items      = $this->model->getTemplateItems();

        $this->view('admin/support/templates', [
            'title'      => 'Support Templates',
            'categories' => $categories,
            'items'      => $items,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/templates/category/create
    // -------------------------------------------------------------------------

    public function createCategory(): void
    {
        $this->validateCsrf();

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon        = trim($_POST['icon'] ?? 'folder');

        if ($name === '') {
            $this->flash('error', 'Category name is required.');
            $this->redirect('/admin/support/templates');
            return;
        }

        $this->model->createTemplateCategory($name, $description, $icon);
        $this->flash('success', 'Template category created.');
        $this->redirect('/admin/support/templates');
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/templates/category/{id}/delete
    // -------------------------------------------------------------------------

    public function deleteCategory(int $id): void
    {
        $this->validateCsrf();
        $this->model->deleteTemplateCategory($id);
        $this->flash('success', 'Category deleted.');
        $this->redirect('/admin/support/templates');
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/templates/item/create
    // -------------------------------------------------------------------------

    public function createItem(): void
    {
        $this->validateCsrf();

        $categoryId      = (int) ($_POST['category_id'] ?? 0);
        $name            = trim($_POST['name'] ?? '');
        $description     = trim($_POST['description'] ?? '');
        $defaultPriority = $_POST['default_priority'] ?? 'medium';
        $fieldsSchema    = trim($_POST['fields_schema'] ?? '[]');

        if ($name === '' || $categoryId === 0) {
            $this->flash('error', 'Category and item name are required.');
            $this->redirect('/admin/support/templates');
            return;
        }

        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($defaultPriority, $validPriorities, true)) {
            $defaultPriority = 'medium';
        }

        $this->model->createTemplateItem($categoryId, $name, $description, $defaultPriority, $fieldsSchema);
        $this->flash('success', 'Template item created.');
        $this->redirect('/admin/support/templates');
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/templates/item/{id}/delete
    // -------------------------------------------------------------------------

    public function deleteItem(int $id): void
    {
        $this->validateCsrf();
        $this->model->deleteTemplateItem($id);
        $this->flash('success', 'Template item deleted.');
        $this->redirect('/admin/support/templates');
    }

    // -------------------------------------------------------------------------
    // GET /admin/support/users
    // -------------------------------------------------------------------------

    public function userAccess(): void
    {
        $users = $this->model->getSupportUsers();

        $this->view('admin/support/users', [
            'title' => 'Support Users',
            'users' => $users,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

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
