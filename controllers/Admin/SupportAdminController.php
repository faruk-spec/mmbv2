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
        $activities = $this->model->getTicketActivities($id);
        $firstAgentReplyAt = $this->model->getFirstAgentReplyAt($id);

        $this->view('admin/support/ticket-view', [
            'title'             => "Ticket #{$id}: " . htmlspecialchars($ticket['subject']),
            'ticket'            => $ticket,
            'messages'          => $messages,
            'activities'        => $activities,
            'firstAgentReplyAt' => $firstAgentReplyAt,
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

            $this->sendSupportEmail($ticket['user_email'] ?? '', 'support-ticket-reply', [
                'ticketId'     => $id,
                'subject'      => $ticket['subject'],
                'userName'     => $ticket['user_name'],
                'replyMessage' => $message,
                'ticketUrl'    => $ticketUrl,
                'status'       => $ticket['status'],
            ], "Update on your Support Ticket #" . sprintf('%07d', $id));

            // In-app notification
            Notification::send(
                (int) $ticket['user_id'],
                'support_ticket_reply',
                "An agent replied to your ticket #" . sprintf('%07d', $id) . ": " . $ticket['subject'],
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

        // Add a visible system message to the chat thread
        $resolution  = mb_substr(trim(strip_tags($_POST['resolution'] ?? '')), 0, 500);
        $statusLabel = ucwords(str_replace('_', ' ', $status));
        $sysMsg = "Status changed to {$statusLabel}." . ($resolution !== '' ? "\n\nNote: {$resolution}" : '');
        $this->model->addSystemMessage($id, $sysMsg);

        if (!empty($ticket['user_email'])) {
            $ticketUrl  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                        . '/support/view/' . $id;

            if ($status === 'closed' || $status === 'resolved') {
                $this->sendSupportEmail($ticket['user_email'], 'support-ticket-closed', [
                    'ticketId'   => $id,
                    'subject'    => $ticket['subject'],
                    'userName'   => $ticket['user_name'],
                    'ticketUrl'  => $ticketUrl,
                    'resolution' => $resolution !== '' ? $resolution : 'Your issue has been resolved.',
                ], "Support Ticket #" . sprintf('%07d', $id) . " — Status: {$statusLabel}");
            } else {
                $this->sendSupportEmail($ticket['user_email'], 'support-ticket-status-update', [
                    'ticketId'  => $id,
                    'subject'   => $ticket['subject'],
                    'userName'  => $ticket['user_name'],
                    'ticketUrl' => $ticketUrl,
                    'status'    => $status,
                    'note'      => $resolution,
                ], "Support Ticket #" . sprintf('%07d', $id) . " — Status: {$statusLabel}");
            }

            Notification::send(
                (int) $ticket['user_id'],
                'support_ticket_' . $status,
                "Your support ticket #" . sprintf('%07d', $id) . " status changed to: " . ucwords(str_replace('_', ' ', $status)),
                ['ticket_id' => $id, 'url' => '/support/view/' . $id]
            );
        }

        $this->flash('success', "Ticket status updated to '" . ucwords(str_replace('_', ' ', $status)) . "'.");
        $this->redirect('/admin/support/tickets/' . $id);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/tickets/{id}/reopen
    // -------------------------------------------------------------------------

    public function reopenTicket(int $id): void
    {
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            $this->flash('error', 'Ticket not found.');
            $this->redirect('/admin/support/tickets');
            return;
        }

        $this->model->updateTicketStatus($id, 'open', Auth::id());

        Notification::send(
            (int) $ticket['user_id'],
            'support_ticket_reopened',
            "Your support ticket #{$id} has been reopened.",
            ['ticket_id' => $id, 'url' => '/support/view/' . $id]
        );

        $this->flash('success', "Ticket #{$id} has been reopened.");
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
    // POST /admin/support/live-chats/{id}/reply
    // -------------------------------------------------------------------------

    public function replyLiveChat(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/admin/support/live-chats/' . $id);
            return;
        }

        $chat = $this->model->getChatById($id);
        if (!$chat) {
            $this->flash('error', 'Chat session not found.');
            $this->redirect('/admin/support/live-chats');
            return;
        }

        if ($chat['status'] !== 'active') {
            $this->flash('error', 'This chat session is already closed.');
            $this->redirect('/admin/support/live-chats/' . $id);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            $this->flash('error', 'Reply message cannot be empty.');
            $this->redirect('/admin/support/live-chats/' . $id);
            return;
        }

        $this->model->addLiveMessage($id, 'agent', Auth::id(), $message);
        $this->model->assignChatAgent($id, Auth::id());

        // Notify the user if they are logged in
        if (!empty($chat['user_id'])) {
            Notification::send(
                (int) $chat['user_id'],
                'live_chat_reply',
                'An agent replied to your live chat session.',
                ['chat_id' => $id, 'url' => '#']
            );
        }

        $this->flash('success', 'Message sent.');
        $this->redirect('/admin/support/live-chats/' . $id);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/live-chats/{id}/close
    // -------------------------------------------------------------------------

    public function closeLiveChat(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/admin/support/live-chats/' . $id);
            return;
        }

        $chat = $this->model->getChatById($id);
        if (!$chat) {
            $this->flash('error', 'Chat session not found.');
            $this->redirect('/admin/support/live-chats');
            return;
        }

        // Idempotent — closing an already-closed chat is fine
        $this->model->closeChat($id);

        if (!empty($chat['user_id'])) {
            Notification::send(
                (int) $chat['user_id'],
                'live_chat_closed',
                'Your live chat session has been closed by an agent.',
                ['chat_id' => $id]
            );
        }

        $this->flash('success', 'Chat session closed.');
        $this->redirect('/admin/support/live-chats');
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/live-chats/{id}/reopen
    // -------------------------------------------------------------------------

    public function reopenLiveChat(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/admin/support/live-chats/' . $id);
            return;
        }

        $chat = $this->model->getChatById($id);
        if (!$chat) {
            $this->flash('error', 'Chat session not found.');
            $this->redirect('/admin/support/live-chats');
            return;
        }

        $this->model->reopenChat($id);

        if (!empty($chat['user_id'])) {
            Notification::send(
                (int) $chat['user_id'],
                'live_chat_reopened',
                'Your live chat session has been reopened by an agent.',
                ['chat_id' => $id]
            );
        }

        $this->flash('success', 'Chat session reopened.');
        $this->redirect('/admin/support/live-chats/' . $id);
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
        $department  = trim($_POST['department'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon        = trim($_POST['icon'] ?? 'folder');

        if ($name === '') {
            $this->flash('error', 'Category name is required.');
            $this->redirect('/admin/support/templates');
            return;
        }

        $this->model->createTemplateCategory($name, $description, $icon, $department);
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
        $users  = $this->model->getSupportUsers();
        $agents = $this->model->getAllAgents();
        $allUsers = $this->model->getAllUsersForAgentAssign();

        $this->view('admin/support/users', [
            'title'    => 'Support Users & Agents',
            'users'    => $users,
            'agents'   => $agents,
            'allUsers' => $allUsers,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/agents/add
    // -------------------------------------------------------------------------

    public function addAgent(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/admin/support/users');
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $notes  = trim($_POST['notes'] ?? '');

        if ($userId <= 0) {
            $this->flash('error', 'Please select a user.');
            $this->redirect('/admin/support/users');
            return;
        }

        $this->model->addAgent($userId, Auth::id(), $notes);
        $this->flash('success', 'Agent added successfully.');
        $this->redirect('/admin/support/users');
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/agents/{id}/remove
    // -------------------------------------------------------------------------

    public function removeAgent(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/admin/support/users');
            return;
        }

        $this->model->removeAgent($id);
        $this->flash('success', 'Agent removed.');
        $this->redirect('/admin/support/users');
    }

    // -------------------------------------------------------------------------
    // GET /admin/support/settings
    // -------------------------------------------------------------------------

    public function supportSettings(): void
    {
        $db   = Database::getInstance();
        $keys = [
            'ticket_id_start',
            'live_support_title',
            'live_support_tagline',
            'live_support_response_time',
            'live_support_hours',
            'live_support_extra_note',
        ];
        $settings = [];
        foreach ($keys as $key) {
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            $settings[$key] = $row ? $row['value'] : '';
        }

        // Current AUTO_INCREMENT for support_tickets
        $aiRow = $db->fetch(
            "SELECT AUTO_INCREMENT FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_tickets'"
        );
        $currentAutoIncrement = $aiRow ? (int) $aiRow['AUTO_INCREMENT'] : 1;

        $this->view('admin/support/settings', [
            'title'                => 'Support Settings',
            'settings'             => $settings,
            'currentAutoIncrement' => $currentAutoIncrement,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/settings
    // -------------------------------------------------------------------------

    public function saveSupportSettings(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/admin/support/settings');
            return;
        }

        $db = Database::getInstance();

        $textKeys = [
            'live_support_title',
            'live_support_tagline',
            'live_support_response_time',
            'live_support_hours',
            'live_support_extra_note',
        ];
        foreach ($textKeys as $key) {
            $value    = trim($_POST[$key] ?? '');
            $existing = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($existing) {
                $db->update('settings', ['value' => $value], '`key` = ?', [$key]);
            } else {
                $db->insert('settings', ['key' => $key, 'value' => $value]);
            }
        }

        // Ticket ID start (AUTO_INCREMENT)
        $startRaw = (int) ($_POST['ticket_id_start'] ?? 0);
        if ($startRaw >= 1) {
            // Ensure it's at least higher than any existing ticket ID
            $maxRow = $db->fetch("SELECT MAX(id) AS max_id FROM support_tickets");
            $maxId  = (int) ($maxRow['max_id'] ?? 0);

            // Also check support_dyn_tickets if it exists
            try {
                $maxDynRow = $db->fetch("SELECT MAX(id) AS max_id FROM support_dyn_tickets");
                $maxId     = max($maxId, (int) ($maxDynRow['max_id'] ?? 0));
            } catch (\Throwable $e) {
                // Table may not exist yet — ignore
            }

            if ($startRaw <= $maxId) {
                $this->flash('error', "Ticket start number must be greater than the current maximum ticket ID ({$maxId}). No change applied.");
                $this->redirect('/admin/support/settings');
                return;
            }

            // Persist the configured value
            $existing = $db->fetch("SELECT id FROM settings WHERE `key` = 'ticket_id_start'");
            if ($existing) {
                $db->update('settings', ['value' => (string) $startRaw], '`key` = ?', ['ticket_id_start']);
            } else {
                $db->insert('settings', ['key' => 'ticket_id_start', 'value' => (string) $startRaw]);
            }

            // Apply AUTO_INCREMENT — $startRaw is cast to int; safe to interpolate directly
            // (MySQL does not support parameterized AUTO_INCREMENT values)
            $db->query("ALTER TABLE support_tickets AUTO_INCREMENT = {$startRaw}");
            $db->query("ALTER TABLE support_dyn_tickets AUTO_INCREMENT = {$startRaw}");
        }

        $this->flash('success', 'Support settings saved successfully.');
        $this->redirect('/admin/support/settings');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------
}
