<?php
/**
 * Support Ticket Controller (user-facing)
 *
 * @package Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
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
                    $fieldType = $field['type'] ?? 'text';
                    $value = '';
                    if ($fieldType === 'checkbox') {
                        $value = !empty($_POST['custom_' . $fieldName]) ? 'Yes' : 'No';
                    } elseif ($fieldType === 'attachment') {
                        $fileKey = 'custom_' . $fieldName;
                        if (!empty($_FILES[$fileKey]) && (int) ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                            $allowed = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt', 'zip', 'doc', 'docx', 'xlsx', 'csv'];
                            $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
                            if (in_array($ext, $allowed, true)) {
                                $baseDir = BASE_PATH . '/storage/uploads/support_attachments';
                                if (!is_dir($baseDir)) {
                                    @mkdir($baseDir, 0755, true);
                                }
                                try {
                                    $safeName = 'att_' . bin2hex(random_bytes(16)) . '.' . $ext;
                                } catch (\Throwable $e) {
                                    continue;
                                }
                                $target = rtrim($baseDir, '/') . '/' . $safeName;
                                if (@move_uploaded_file($_FILES[$fileKey]['tmp_name'], $target)) {
                                    $value = '/support-attachments/' . $safeName;
                                }
                            }
                        }
                    } else {
                        $value = trim($_POST['custom_' . $fieldName] ?? '');
                    }
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

        $this->sendSupportEmail($user['email'] ?? '', 'support-ticket-created', [
            'ticketId'    => $ticketId,
            'subject'     => $subject,
            'userName'    => $user['name'] ?? 'User',
            'ticketUrl'   => $ticketUrl,
            'description' => $description,
        ], "Support Ticket #" . sprintf('%07d', $ticketId) . " Created: {$subject}");

        // Notify all admins
        $adminUrl = '/admin/support/tickets/' . $ticketId;
        $this->notifyAdmins(
            'support_ticket',
            "New support ticket #" . sprintf('%07d', $ticketId) . ": {$subject}",
            ['ticket_id' => $ticketId, 'url' => $adminUrl]
        );

        $this->flash('success', "Support ticket #" . sprintf('%07d', $ticketId) . " created successfully.");
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

        $ticketIdFormatted = sprintf('%07d', $id);
        $adminUrl          = '/admin/support/tickets/' . $id;
        $this->notifyAdmins(
            'support_ticket_reply',
            "New reply on ticket #{$ticketIdFormatted}: " . $ticket['subject'],
            ['ticket_id' => $id, 'url' => $adminUrl]
        );

        // Email the assigned agent (or all admins if no agent assigned) about the user reply
        if (!empty($ticket['assigned_to'])) {
            $agentRow = \Core\Database::getInstance()->fetch(
                "SELECT email, name FROM users WHERE id = ?",
                [(int) $ticket['assigned_to']]
            );
        } else {
            $agentRow = null;
        }
        $ticketUrl  = $this->baseUrl() . '/admin/support/tickets/' . $id;
        $emailSubject = "User replied to Support Ticket #{$ticketIdFormatted}";
        $emailVars  = [
            'ticketId'     => $id,
            'subject'      => $ticket['subject'],
            'userName'     => $ticket['user_name'] ?? Auth::user()['name'] ?? 'User',
            'replyMessage' => $message,
            'ticketUrl'    => $ticketUrl,
            'status'       => $ticket['status'],
        ];
        if ($agentRow && !empty($agentRow['email'])) {
            $this->sendSupportEmail($agentRow['email'], 'support-ticket-reply', $emailVars, $emailSubject);
        } else {
            $db         = \Core\Database::getInstance();
            $firstAdmin = $db->fetch("SELECT email FROM users WHERE role LIKE '%admin%' LIMIT 1");
            if ($firstAdmin && !empty($firstAdmin['email'])) {
                $this->sendSupportEmail($firstAdmin['email'], 'support-ticket-reply', $emailVars, $emailSubject);
            }
        }

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
        $db       = \Core\Database::getInstance();
        $keys     = ['live_support_title', 'live_support_tagline', 'live_support_response_time', 'live_support_hours', 'live_support_extra_note'];
        $settings = [];
        foreach ($keys as $key) {
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            $settings[$key] = $row ? $row['value'] : '';
        }

        $this->view('support/live', [
            'title'          => $settings['live_support_title'] ?: 'Live Support',
            'currentPage'    => 'live',
            'isSupportAdmin' => $this->isSupportAdmin(),
            'liveSettings'   => $settings,
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
    // Admin Portal views within support portal
    // GET /support/admin/tickets[?status=...]
    // -------------------------------------------------------------------------

    public function adminTickets(): void
    {
        $this->requireSupportAdmin();

        $filters = [];
        $status  = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $assignedTo = (int) ($_GET['assigned_to'] ?? 0);
        $query = trim($_GET['q'] ?? '');
        if ($status !== '') {
            $filters['status'] = $status;
        }
        if ($priority !== '') {
            $filters['priority'] = $priority;
        }
        if ($assignedTo > 0) {
            $filters['assigned_to'] = $assignedTo;
        }
        if ($query !== '') {
            $filters['query'] = $query;
        }

        $tickets = $this->model->getAllTickets($filters);
        $stats   = $this->model->getTicketStats();
        $agents  = $this->model->getAssignableAgents();

        $currentPage = match($status) {
            'open'        => 'admin_open',
            'in_progress' => 'admin_inprogress',
            'resolved'    => 'admin_resolved',
            'closed'      => 'admin_closed',
            default       => 'admin_tickets',
        };

        $this->view('support/admin/tickets', [
            'title'          => 'All Requests',
            'tickets'        => $tickets,
            'stats'          => $stats,
            'agents'         => $agents,
            'statusFilter'   => $status,
            'priorityFilter' => $priority,
            'assignedFilter' => $assignedTo > 0 ? $assignedTo : '',
            'searchQuery'    => $query,
            'currentPage'    => $currentPage,
            'isSupportAdmin' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /support/admin/live
    // -------------------------------------------------------------------------

    public function adminLive(): void
    {
        $this->requireSupportAdmin();

        $chats = $this->model->getLiveChats();

        $this->view('support/admin/live', [
            'title'          => 'Live Chats',
            'chats'          => $chats,
            'currentPage'    => 'admin_live',
            'isSupportAdmin' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /support/admin/reports
    // -------------------------------------------------------------------------

    public function adminReports(): void
    {
        $this->requireSupportAdmin();

        $filters = [
            'status'      => $_GET['status'] ?? '',
            'priority'    => $_GET['priority'] ?? '',
            'assigned_to' => (int) ($_GET['assigned_to'] ?? 0),
            'from_date'   => $_GET['from_date'] ?? '',
            'to_date'     => $_GET['to_date'] ?? '',
            'query'       => trim($_GET['q'] ?? ''),
        ];
        $filters = array_filter($filters, static fn ($v) => ($v !== '' && $v !== null && $v !== 0));

        $tickets = $this->model->getAllTickets($filters);
        $stats = $this->model->getTicketStats();
        $agents = $this->model->getAssignableAgents();
        $kpi = $this->buildKpi($tickets, $stats);

        $this->view('support/admin/reports', [
            'title'          => 'Reports',
            'tickets'        => $tickets,
            'stats'          => $stats,
            'agents'         => $agents,
            'kpi'            => $kpi,
            'filters'        => [
                'status' => $_GET['status'] ?? '',
                'priority' => $_GET['priority'] ?? '',
                'assigned_to' => (string) ($_GET['assigned_to'] ?? ''),
                'from_date' => $_GET['from_date'] ?? '',
                'to_date' => $_GET['to_date'] ?? '',
                'q' => trim($_GET['q'] ?? ''),
            ],
            'currentPage'    => 'admin_reports',
            'isSupportAdmin' => true,
        ]);
    }

    public function adminExportReportsCsv(): void
    {
        $this->requireSupportAdmin();

        $filters = [
            'status'      => $_GET['status'] ?? '',
            'priority'    => $_GET['priority'] ?? '',
            'assigned_to' => (int) ($_GET['assigned_to'] ?? 0),
            'from_date'   => $_GET['from_date'] ?? '',
            'to_date'     => $_GET['to_date'] ?? '',
            'query'       => trim($_GET['q'] ?? ''),
        ];
        $filters = array_filter($filters, static fn ($v) => ($v !== '' && $v !== null && $v !== 0));
        $tickets = $this->model->getAllTickets($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="support-tickets-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Ticket ID', 'Subject', 'Customer', 'Customer Email', 'Status', 'Priority', 'Assigned Agent', 'Created At', 'Updated At', 'Last Reply At', 'Closed At']);
        foreach ($tickets as $ticket) {
            fputcsv($out, [
                '#' . sprintf('%07d', (int) ($ticket['id'] ?? 0)),
                $ticket['subject'] ?? '',
                $ticket['user_name'] ?? '',
                $ticket['user_email'] ?? '',
                $ticket['status'] ?? '',
                $ticket['priority'] ?? '',
                $ticket['agent_name'] ?? '',
                $ticket['created_at'] ?? '',
                $ticket['updated_at'] ?? '',
                $ticket['last_reply_at'] ?? '',
                $ticket['closed_at'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    // -------------------------------------------------------------------------
    // GET /support/admin/ticket/{id}
    // -------------------------------------------------------------------------

    public function adminViewTicket(int $id): void
    {
        $this->requireSupportAdmin();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            http_response_code(404);
            parent::view('errors/404', ['title' => 'Ticket Not Found']);
            return;
        }

        $messages = $this->model->getTicketMessages($id, true); // include internal notes
        $activities = $this->model->getTicketActivities($id);
        $agents = $this->model->getAssignableAgents();

        $this->view('support/admin/ticket_view', [
            'title'          => 'Manage Ticket #' . sprintf('%07d', $id),
            'ticket'         => $ticket,
            'messages'       => $messages,
            'activities'     => $activities,
            'agents'         => $agents,
            'currentPage'    => 'admin_tickets',
            'isSupportAdmin' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /support/admin/ticket/{id}/reply
    // -------------------------------------------------------------------------

    public function adminReplyTicket(int $id): void
    {
        $this->requireSupportAdmin();
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            $this->flash('error', 'Ticket not found.');
            $this->redirect('/support/admin/tickets');
            return;
        }
        if ($ticket['status'] === 'closed') {
            $this->flash('error', 'This ticket is closed.');
            $this->redirect('/support/admin/ticket/' . $id);
            return;
        }

        $message    = trim($_POST['message'] ?? '');
        $isInternal = !empty($_POST['is_internal']);

        if ($message === '') {
            $this->flash('error', 'Reply message cannot be empty.');
            $this->redirect('/support/admin/ticket/' . $id);
            return;
        }

        $this->model->addTicketMessage($id, 'agent', Auth::id(), $message, $isInternal);

        if (!$isInternal && !empty($ticket['user_email'])) {
            $ticketUrl = $this->baseUrl() . '/support/view/' . $id;
            $this->sendSupportEmail($ticket['user_email'], 'support-ticket-reply', [
                'ticketId'     => $id,
                'subject'      => $ticket['subject'],
                'userName'     => $ticket['user_name'],
                'replyMessage' => $message,
                'ticketUrl'    => $ticketUrl,
                'status'       => $ticket['status'],
            ], "Update on your Support Ticket #" . sprintf('%07d', $id));

            Notification::send(
                (int) $ticket['user_id'],
                'support_ticket_reply',
                "An agent replied to your ticket #" . sprintf('%07d', $id) . ': ' . $ticket['subject'],
                ['ticket_id' => $id, 'url' => '/support/view/' . $id]
            );
        }

        $this->flash('success', $isInternal ? 'Internal note added.' : 'Reply sent successfully.');
        $this->redirect('/support/admin/ticket/' . $id);
    }

    // -------------------------------------------------------------------------
    // POST /support/admin/ticket/{id}/status
    // -------------------------------------------------------------------------

    public function adminUpdateTicketStatus(int $id): void
    {
        $this->requireSupportAdmin();
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            $this->flash('error', 'Ticket not found.');
            $this->redirect('/support/admin/tickets');
            return;
        }

        $validStatuses = ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'];
        $status        = $_POST['status'] ?? '';
        $reason        = trim($_POST['status_reason'] ?? '');
        if (!in_array($status, $validStatuses, true)) {
            $this->flash('error', 'Invalid status.');
            $this->redirect('/support/admin/ticket/' . $id);
            return;
        }
        if ($reason === '') {
            $this->flash('error', 'Status change reason is required.');
            $this->redirect('/support/admin/ticket/' . $id);
            return;
        }

        $this->model->updateTicketStatus($id, $status, Auth::id());
        $this->model->addTicketActivity(
            $id,
            'status_note',
            Auth::id(),
            'Status note: ' . $reason,
            ['status' => $status]
        );

        // Add a visible system message to the chat thread so the reason is
        // visible in context for both the user and agents.
        $statusLabel = ucwords(str_replace('_', ' ', $status));
        $sysMsg = "Status changed to {$statusLabel}." . ($reason !== '' ? "\n\nNote: {$reason}" : '');
        $this->model->addSystemMessage($id, $sysMsg);

        if (!empty($ticket['user_email'])) {
            $ticketUrl  = $this->baseUrl() . '/support/view/' . $id;
            $safeReason = mb_substr(trim(strip_tags($reason)), 0, 500);

            if ($status === 'closed' || $status === 'resolved') {
                $this->sendSupportEmail($ticket['user_email'], 'support-ticket-closed', [
                    'ticketId'   => $id,
                    'subject'    => $ticket['subject'],
                    'userName'   => $ticket['user_name'],
                    'ticketUrl'  => $ticketUrl,
                    'resolution' => $safeReason !== '' ? $safeReason : 'Your issue has been resolved.',
                ], "Support Ticket #" . sprintf('%07d', $id) . " — Status: {$statusLabel}");
            } else {
                $this->sendSupportEmail($ticket['user_email'], 'support-ticket-status-update', [
                    'ticketId'  => $id,
                    'subject'   => $ticket['subject'],
                    'userName'  => $ticket['user_name'],
                    'ticketUrl' => $ticketUrl,
                    'status'    => $status,
                    'note'      => $safeReason,
                ], "Support Ticket #" . sprintf('%07d', $id) . " — Status: {$statusLabel}");
            }

            Notification::send(
                (int) $ticket['user_id'],
                'support_ticket_' . $status,
                "Your support ticket #" . sprintf('%07d', $id) . " has been updated to: " . ucwords(str_replace('_', ' ', $status)) . ($safeReason !== '' ? ". Note: {$safeReason}" : ''),
                ['ticket_id' => $id, 'url' => '/support/view/' . $id]
            );
        }

        $this->flash('success', "Status updated to '" . ucfirst(str_replace('_', ' ', $status)) . "'.");
        $this->redirect('/support/admin/ticket/' . $id);
    }

    public function adminAssignTicketAgent(int $id): void
    {
        $this->requireSupportAdmin();
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        $backUrl = '/support/admin/ticket/' . $id;
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refererPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
            if (is_string($refererPath) && str_starts_with($refererPath, '/support/admin/')) {
                $backUrl = $refererPath;
            }
        }

        if (!$ticket) {
            $this->flash('error', 'Ticket not found.');
            $this->redirect('/support/admin/tickets');
            return;
        }

        $agentId = (int) ($_POST['assigned_to'] ?? 0);
        $agents = $this->model->getAssignableAgents();
        $validIds = array_map(static fn ($a) => (int) ($a['id'] ?? 0), $agents);

        if ($agentId <= 0 || !in_array($agentId, $validIds, true)) {
            $this->flash('error', 'Please select a valid support agent.');
            $this->redirect($backUrl);
            return;
        }

        $this->model->assignTicketAgent($id, $agentId, Auth::id());
        Notification::send(
            (int) $ticket['user_id'],
            'support_ticket_assigned',
            "Your ticket #" . sprintf('%07d', $id) . ' has been assigned to a support agent.',
            ['ticket_id' => $id, 'url' => '/support/view/' . $id]
        );
        $this->flash('success', 'Ticket assignment updated.');
        $this->redirect($backUrl);
    }

    // -------------------------------------------------------------------------
    // POST /support/admin/ticket/{id}/priority
    // -------------------------------------------------------------------------

    public function adminUpdateTicketPriority(int $id): void
    {
        $this->requireSupportAdmin();
        $this->validateCsrf();

        $ticket = $this->model->getTicketById($id);
        if (!$ticket) {
            $this->flash('error', 'Ticket not found.');
            $this->redirect('/support/admin/tickets');
            return;
        }

        $valid    = ['low', 'medium', 'high', 'urgent'];
        $priority = $_POST['priority'] ?? '';
        if (!in_array($priority, $valid, true)) {
            $this->flash('error', 'Invalid priority.');
            $this->redirect('/support/admin/ticket/' . $id);
            return;
        }

        $this->model->updateTicketPriority($id, $priority);

        $this->flash('success', "Priority updated to '" . ucfirst($priority) . "'.");
        $this->redirect('/support/admin/ticket/' . $id);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function requireSupportAdmin(): void
    {
        if (!$this->isSupportAdmin()) {
            $this->flash('error', 'Access denied.');
            $this->redirect('/support');
        }
    }

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

    private function buildKpi(array $tickets, array $stats): array
    {
        $total = (int) ($stats['total'] ?? 0);
        $resolved = (int) ($stats['resolved'] ?? 0) + (int) ($stats['closed'] ?? 0);
        $resolutionRate = $total > 0 ? round(($resolved / $total) * 100, 1) : 0.0;
        $unassigned = 0;
        $repliedWithin24h = 0;
        $firstReplyMap = $this->model->getFirstAgentReplyMap(array_map(static fn ($t) => (int) ($t['id'] ?? 0), $tickets));
        foreach ($tickets as $ticket) {
            if (empty($ticket['assigned_to'])) {
                $unassigned++;
            }
            $firstAgentReplyAt = $firstReplyMap[(int) ($ticket['id'] ?? 0)] ?? null;
            if (!empty($firstAgentReplyAt) && !empty($ticket['created_at'])) {
                $first = strtotime($ticket['created_at']);
                $reply = strtotime($firstAgentReplyAt);
                if ($first && $reply && ($reply - $first) <= 86400) {
                    $repliedWithin24h++;
                }
            }
        }
        $responseRate24h = $total > 0 ? round(($repliedWithin24h / $total) * 100, 1) : 0.0;

        return [
            'resolution_rate' => $resolutionRate,
            'unassigned_count' => $unassigned,
            'first_response_24h' => $responseRate24h,
            'active_workload' => (int) ($stats['open'] ?? 0) + (int) ($stats['in_progress'] ?? 0) + (int) ($stats['waiting_customer'] ?? 0),
        ];
    }
}
