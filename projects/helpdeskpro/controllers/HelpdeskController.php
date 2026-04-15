<?php
/**
 * Helpdesk Pro Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Core\Mailer;
use Core\Security;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class HelpdeskController
{
    private HelpdeskModel $model;
    private array $config;

    public function __construct()
    {
        $this->model = new HelpdeskModel();
        $this->config = require PROJECT_PATH . '/config.php';
    }

    public function dashboard(): void
    {
        $userId = (int) Auth::id();
        $isAgent = $this->isAgent();

        $this->render('dashboard', [
            'title' => 'Helpdesk Pro Dashboard',
            'isAgent' => $isAgent,
            'stats' => $this->model->getDashboardStats($userId, $isAgent),
            'tickets' => $this->model->getTickets($userId, $isAgent, 8),
            'liveSessions' => $isAgent ? $this->model->getAgentLiveSessions(6) : [],
            'agentPerformance' => $isAgent ? $this->model->getAgentPerformance() : [],
        ]);
    }

    public function tickets(): void
    {
        $isAgent = $this->isAgent();
        $this->render('tickets/index', [
            'title' => 'Tickets',
            'isAgent' => $isAgent,
            'tickets' => $this->model->getTickets((int) Auth::id(), $isAgent),
        ]);
    }

    public function createTicketForm(): void
    {
        $this->render('tickets/create', [
            'title' => 'Create Ticket',
            'priorities' => $this->config['ticket_priorities'] ?? ['low', 'medium', 'high', 'urgent'],
        ]);
    }

    public function storeTicket(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/tickets/create');
        }

        $subject = trim((string) ($_POST['subject'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $priority = trim((string) ($_POST['priority'] ?? 'medium'));
        $allowedPriorities = $this->config['ticket_priorities'] ?? ['low', 'medium', 'high', 'urgent'];

        if ($subject === '' || $description === '') {
            $this->flash('error', 'Subject and description are required.');
            $this->redirect('/projects/helpdeskpro/tickets/create');
        }

        if (!in_array($priority, $allowedPriorities, true)) {
            $priority = 'medium';
        }

        $user = Auth::user() ?? [];
        $ticketId = $this->model->createTicket(
            (int) Auth::id(),
            mb_substr($subject, 0, 255),
            mb_substr($description, 0, 5000),
            $priority,
            $user['email'] ?? null
        );

        $this->model->addTicketMessage($ticketId, 'customer', (int) Auth::id(), mb_substr($description, 0, 5000));
        $this->sendTicketCreatedEmails($ticketId, $user['email'] ?? '', $user['name'] ?? 'Customer', $subject);

        $this->flash('success', 'Ticket created successfully.');
        $this->redirect('/projects/helpdeskpro/tickets/view/' . $ticketId);
    }

    public function viewTicket(int $ticketId): void
    {
        $isAgent = $this->isAgent();
        $ticket = $this->model->getTicketById($ticketId, (int) Auth::id(), $isAgent);
        if (!$ticket) {
            http_response_code(404);
            echo 'Ticket not found.';
            return;
        }

        $this->render('tickets/view', [
            'title' => 'Ticket #' . $ticketId,
            'ticket' => $ticket,
            'messages' => $this->model->getTicketMessages($ticketId, $isAgent),
            'isAgent' => $isAgent,
            'statuses' => $this->config['ticket_statuses'] ?? [],
        ]);
    }

    public function replyTicket(int $ticketId): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/tickets/view/' . $ticketId);
        }

        $isAgent = $this->isAgent();
        $ticket = $this->model->getTicketById($ticketId, (int) Auth::id(), $isAgent);
        if (!$ticket) {
            http_response_code(404);
            echo 'Ticket not found.';
            return;
        }

        $message = trim((string) ($_POST['message'] ?? ''));
        if ($message === '') {
            $this->flash('error', 'Reply message cannot be empty.');
            $this->redirect('/projects/helpdeskpro/tickets/view/' . $ticketId);
        }

        $senderType = $isAgent ? 'agent' : 'customer';
        $isInternal = $isAgent && !empty($_POST['is_internal']);
        $safeMessage = mb_substr($message, 0, 5000);

        $this->model->addTicketMessage($ticketId, $senderType, (int) Auth::id(), $safeMessage, $isInternal);
        if ($isAgent && empty($ticket['assigned_agent_id'])) {
            $this->model->updateTicketStatus($ticketId, 'in_progress', (int) Auth::id());
        }

        if ($isAgent) {
            $customerEmail = $ticket['requester_email'] ?: ($ticket['requester_user_email'] ?? '');
            if (!$isInternal && $customerEmail !== '') {
                $ticketUrl = $this->projectUrl('/tickets/view/' . $ticketId);
                $this->sendSafeEmail(
                    $customerEmail,
                    'Update on your support ticket #' . $ticketId,
                    '<p>Your support ticket has a new reply from our support team.</p><p><strong>Reply:</strong><br>' . nl2br(htmlspecialchars($safeMessage)) . '</p><p>View ticket: <a href="' . htmlspecialchars($ticketUrl) . '">' . htmlspecialchars($ticketUrl) . '</a></p>'
                );
            }
        } else {
            $this->notifyAgents('Customer replied to ticket #' . $ticketId, 'Customer posted a new reply on ticket #' . $ticketId . '.');
        }

        $this->flash('success', 'Reply sent.');
        $this->redirect('/projects/helpdeskpro/tickets/view/' . $ticketId);
    }

    public function updateTicketStatus(int $ticketId): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/tickets/view/' . $ticketId);
        }

        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $status = trim((string) ($_POST['status'] ?? 'open'));
        $allowedStatuses = $this->config['ticket_statuses'] ?? [];
        if (!in_array($status, $allowedStatuses, true)) {
            $this->flash('error', 'Invalid ticket status.');
            $this->redirect('/projects/helpdeskpro/tickets/view/' . $ticketId);
        }

        $ticket = $this->model->getTicketById($ticketId, (int) Auth::id(), true);
        if (!$ticket) {
            http_response_code(404);
            echo 'Ticket not found.';
            return;
        }

        $this->model->updateTicketStatus($ticketId, $status, (int) Auth::id());
        $customerEmail = $ticket['requester_email'] ?: ($ticket['requester_user_email'] ?? '');
        if ($customerEmail !== '') {
            $ticketUrl = $this->projectUrl('/tickets/view/' . $ticketId);
            $this->sendSafeEmail(
                $customerEmail,
                'Ticket #' . $ticketId . ' status updated',
                '<p>Your ticket status is now <strong>' . htmlspecialchars(strtoupper(str_replace('_', ' ', $status))) . '</strong>.</p><p>View: <a href="' . htmlspecialchars($ticketUrl) . '">' . htmlspecialchars($ticketUrl) . '</a></p>'
            );
        }

        $this->flash('success', 'Ticket status updated.');
        $this->redirect('/projects/helpdeskpro/tickets/view/' . $ticketId);
    }

    public function liveSupport(): void
    {
        $user = Auth::user() ?? [];
        $session = $this->model->getOpenLiveSessionByUser((int) Auth::id());

        $this->render('live/customer', [
            'title' => 'Live Support',
            'session' => $session,
            'messages' => $session ? $this->model->getLiveMessages((int) $session['id']) : [],
            'prefillName' => $user['name'] ?? '',
            'prefillEmail' => $user['email'] ?? '',
        ]);
    }

    public function startLiveSupport(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/live-support');
        }

        $existing = $this->model->getOpenLiveSessionByUser((int) Auth::id());
        if ($existing) {
            $this->redirect('/projects/helpdeskpro/live-support?sid=' . (int) $existing['id']);
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        if ($name === '' || $email === '') {
            $this->flash('error', 'Name and email are required to start live support.');
            $this->redirect('/projects/helpdeskpro/live-support');
        }

        $sessionId = $this->model->createLiveSession((int) Auth::id(), mb_substr($name, 0, 120), mb_substr($email, 0, 255));
        $this->model->addLiveMessage($sessionId, 'ai', null, "Hi {$name}! I'm your support assistant. Please share your issue and I'll help right away.", true);
        $this->notifyAgents('New live support session #' . $sessionId, 'A new live support conversation has started.');

        $this->redirect('/projects/helpdeskpro/live-support?sid=' . $sessionId);
    }

    public function sendLiveMessage(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/live-support');
        }

        $message = trim((string) ($_POST['message'] ?? ''));
        if ($message === '') {
            $this->flash('error', 'Message cannot be empty.');
            $this->redirect('/projects/helpdeskpro/live-support');
        }

        $session = $this->model->getOpenLiveSessionByUser((int) Auth::id());
        if (!$session) {
            $this->flash('error', 'Please start a live support session first.');
            $this->redirect('/projects/helpdeskpro/live-support');
        }

        $sessionId = (int) $session['id'];
        $safeMessage = mb_substr($message, 0, 3000);
        $this->model->addLiveMessage($sessionId, 'customer', (int) Auth::id(), $safeMessage, false);

        $agents = $this->model->getSupportAgents();
        $keywords = $this->config['human_handoff_keywords'] ?? ['human', 'agent', 'person', 'representative', 'real'];
        $escapedKeywords = array_map(static fn($k) => preg_quote((string) $k, '~'), $keywords);
        $pattern = '~\b(' . implode('|', $escapedKeywords) . ')\b~i';
        $requestedHuman = preg_match($pattern, $safeMessage) === 1;

        if ((int) ($session['assigned_agent_id'] ?? 0) === 0 && $requestedHuman && !empty($agents)) {
            $selectedAgent = $this->pickAgentForHandoff($agents);
            $agentId = (int) $selectedAgent['id'];
            $this->model->assignLiveSessionToAgent($sessionId, $agentId);
            $this->model->addLiveMessage($sessionId, 'ai', null, "Got it — I'm connecting a human support specialist now. Please stay online.", true);

            $agentEmail = trim((string) ($selectedAgent['email'] ?? ''));
            if ($agentEmail !== '') {
                $agentUrl = $this->projectUrl('/agent/live-support?sid=' . $sessionId);
                $this->sendSafeEmail(
                    $agentEmail,
                    'Live support handoff needed (Session #' . $sessionId . ')',
                    '<p>A customer requested a human agent in live support.</p><p>Open: <a href="' . htmlspecialchars($agentUrl) . '">' . htmlspecialchars($agentUrl) . '</a></p>'
                );
            }
        } elseif ((int) ($session['assigned_agent_id'] ?? 0) === 0) {
            $this->model->addLiveMessage($sessionId, 'ai', null, $this->generateAiReply($safeMessage), true);
        }

        $this->redirect('/projects/helpdeskpro/live-support?sid=' . $sessionId);
    }

    public function agentLiveSupport(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $sessionId = (int) ($_GET['sid'] ?? 0);
        $sessions = $this->model->getAgentLiveSessions();
        $activeSession = null;
        $messages = [];

        if ($sessionId > 0) {
            $activeSession = $this->model->getLiveSessionById($sessionId, (int) Auth::id(), true);
            if ($activeSession) {
                if ((int) ($activeSession['assigned_agent_id'] ?? 0) === 0) {
                    $this->model->assignLiveSessionToAgent($sessionId, (int) Auth::id());
                    $activeSession['assigned_agent_id'] = (int) Auth::id();
                }
                $messages = $this->model->getLiveMessages($sessionId);
            }
        }

        $this->render('live/agent', [
            'title' => 'Agent Live Support',
            'sessions' => $sessions,
            'activeSession' => $activeSession,
            'messages' => $messages,
        ]);
    }

    public function agentReplyLive(int $sessionId): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/agent/live-support?sid=' . $sessionId);
        }

        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $session = $this->model->getLiveSessionById($sessionId, (int) Auth::id(), true);
        if (!$session) {
            http_response_code(404);
            echo 'Session not found.';
            return;
        }

        $message = trim((string) ($_POST['message'] ?? ''));
        if ($message === '') {
            $this->flash('error', 'Reply message cannot be empty.');
            $this->redirect('/projects/helpdeskpro/agent/live-support?sid=' . $sessionId);
        }

        if ((int) ($session['assigned_agent_id'] ?? 0) === 0) {
            $this->model->assignLiveSessionToAgent($sessionId, (int) Auth::id());
        }

        $safeMessage = mb_substr($message, 0, 3000);
        $this->model->addLiveMessage($sessionId, 'agent', (int) Auth::id(), $safeMessage, false);

        $customerEmail = $session['customer_email'] ?: ($session['user_email'] ?? '');
        if ($customerEmail !== '') {
            $chatUrl = $this->projectUrl('/live-support?sid=' . $sessionId);
            $this->sendSafeEmail(
                $customerEmail,
                'New live support reply',
                '<p>Your support chat has a new message from an agent:</p><p>' . nl2br(htmlspecialchars($safeMessage)) . '</p><p>Continue chat: <a href="' . htmlspecialchars($chatUrl) . '">' . htmlspecialchars($chatUrl) . '</a></p>'
            );
        }

        $this->flash('success', 'Reply sent to customer.');
        $this->redirect('/projects/helpdeskpro/agent/live-support?sid=' . $sessionId);
    }

    private function generateAiReply(string $message): string
    {
        $m = strtolower($message);
        if (str_contains($m, 'refund') || str_contains($m, 'payment') || str_contains($m, 'invoice')) {
            return 'I can help with billing. Please share your order ID or invoice number, and I will check the payment status and next steps right away.';
        }
        if (str_contains($m, 'login') || str_contains($m, 'password') || str_contains($m, 'otp')) {
            return 'For account access issues, please try resetting your password first. If it still fails, share the exact error text and I will open priority troubleshooting for you.';
        }
        if (str_contains($m, 'bug') || str_contains($m, 'error') || str_contains($m, 'not working')) {
            return 'Thanks for reporting this. Please share the exact steps, any error screenshot/text, and when it started so I can provide a targeted fix quickly.';
        }
        return 'Thanks for the details. I am reviewing your case now. Please share any relevant IDs, screenshots, or exact error messages so I can resolve this faster.';
    }

    private function sendTicketCreatedEmails(int $ticketId, string $customerEmail, string $customerName, string $subject): void
    {
        if ($customerEmail !== '') {
            $ticketUrl = $this->projectUrl('/tickets/view/' . $ticketId);
            $this->sendSafeEmail(
                $customerEmail,
                'Ticket #' . $ticketId . ' created successfully',
                '<p>Hi ' . htmlspecialchars($customerName) . ',</p><p>Your support ticket has been created and our team will respond shortly.</p><p><strong>Ticket:</strong> #' . $ticketId . '<br><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p><p>Track ticket: <a href="' . htmlspecialchars($ticketUrl) . '">' . htmlspecialchars($ticketUrl) . '</a></p>'
            );
        }

        $this->notifyAgents(
            'New support ticket #' . $ticketId,
            'A new ticket has been opened: #' . $ticketId . ' - ' . $subject . '. Review in Helpdesk Pro.'
        );
    }

    private function notifyAgents(string $subject, string $message): void
    {
        foreach ($this->model->getSupportAgents() as $agent) {
            $email = trim((string) ($agent['email'] ?? ''));
            if ($email === '') {
                continue;
            }

            $this->sendSafeEmail(
                $email,
                $subject,
                '<p>' . nl2br(htmlspecialchars($message)) . '</p><p>Open Helpdesk Pro: <a href="' . htmlspecialchars($this->projectUrl('/')) . '">' . htmlspecialchars($this->projectUrl('/')) . '</a></p>'
            );
        }
    }

    private function sendSafeEmail(string $to, string $subject, string $body): void
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mailer::send($to, $subject, $body);
        } catch (\Throwable $e) {
            // Avoid user-facing failure on email transport issues.
        }
    }

    private function pickAgentForHandoff(array $agents): array
    {
        $selected = $agents[0];
        $bestLoad = PHP_INT_MAX;

        foreach ($agents as $agent) {
            $agentId = (int) ($agent['id'] ?? 0);
            if ($agentId <= 0) {
                continue;
            }
            $load = $this->model->getAgentActiveWorkload($agentId);
            if ($load < $bestLoad) {
                $bestLoad = $load;
                $selected = $agent;
            }
        }

        return $selected;
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

    private function projectUrl(string $path): string
    {
        $base = rtrim((defined('APP_URL') ? APP_URL : ''), '/');
        return $base . '/projects/helpdeskpro' . $path;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/' . $view . '.php';
    }
}
