<?php
/**
 * Support Live Chat Controller
 *
 * Handles floating chat widget AJAX endpoints. Works for both logged-in
 * users and guests — no global auth required.
 *
 * @package Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Notification;
use Core\Security;
use Models\SupportModel;

class SupportLiveChatController
{
    private SupportModel $model;

    public function __construct()
    {
        $this->model = new SupportModel();
    }

    // -------------------------------------------------------------------------
    // POST /support/live/start
    // -------------------------------------------------------------------------

    public function start(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->jsonError('Invalid CSRF token.', 403);
            return;
        }

        if (Auth::check()) {
            // Use session-stored key so each browser tab/session can start a fresh chat
            // without hitting the UNIQUE constraint on session_key from a previous closed chat.
            if (!isset($_SESSION['support_live_key'])) {
                $_SESSION['support_live_key'] = 'user_' . Auth::id() . '_' . bin2hex(random_bytes(8));
            }
            $sessionKey = $_SESSION['support_live_key'];
            $userId     = Auth::id();
            $guestName  = '';
            $guestEmail = '';
        } else {
            if (!isset($_SESSION['support_live_key'])) {
                $_SESSION['support_live_key'] = bin2hex(random_bytes(16));
            }
            $sessionKey = $_SESSION['support_live_key'];
            $userId     = null;
            $guestName  = trim($_POST['guest_name'] ?? '');
            $guestEmail = trim($_POST['guest_email'] ?? '');

            if ($guestName === '' || $guestEmail === '') {
                $this->jsonError('Name and email are required for guest chat.');
                return;
            }
        }

        $chat = $this->model->findOrCreateChat($userId, $sessionKey, $guestName, $guestEmail);

        // Add AI greeting if this is a brand-new chat (no messages yet)
        $existing = $this->model->getLiveMessages((int) $chat['id']);
        if (empty($existing)) {
            $this->model->addLiveMessage(
                (int) $chat['id'],
                'ai',
                null,
                "Hi! I'm your AI support assistant. How can I help you today?"
            );
        }

        $messages = $this->model->getLiveMessages((int) $chat['id']);

        $this->jsonOk([
            'chat_id'     => (int) $chat['id'],
            'session_key' => $sessionKey,
            'messages'    => $messages,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /support/live/send
    // -------------------------------------------------------------------------

    public function send(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->jsonError('Invalid CSRF token.', 403);
            return;
        }

        $sessionKey = $_POST['session_key'] ?? '';
        $message    = trim($_POST['message'] ?? '');

        if ($sessionKey === '') {
            $this->jsonError('Missing session key.');
            return;
        }
        if ($message === '') {
            $this->jsonError('Message cannot be empty.');
            return;
        }
        if (strlen($message) > 2000) {
            $this->jsonError('Message is too long (max 2000 characters).');
            return;
        }

        $chat = $this->model->getChatBySessionKey($sessionKey);
        if (!$chat || $chat['status'] !== 'active') {
            $this->jsonError('Chat session not found or is closed.');
            return;
        }

        $chatId     = (int) $chat['id'];
        $senderType = Auth::check() ? 'user' : 'guest';
        $senderId   = Auth::check() ? Auth::id() : null;

        $this->model->addLiveMessage($chatId, $senderType, $senderId, $message);

        // AI keyword-based auto-reply
        $lower = strtolower($message);

        if (
            str_contains($lower, 'human') ||
            str_contains($lower, 'agent') ||
            str_contains($lower, 'person') ||
            str_contains($lower, 'real person') ||
            str_contains($lower, 'support staff')
        ) {
            $this->model->addLiveMessage(
                $chatId,
                'ai',
                null,
                "Connecting you to a human agent... Please hold on. An agent will join shortly."
            );
            // Notify admins
            $this->notifyAdmins(
                'support_live_chat',
                "Live chat #{$chatId}: user requested a human agent.",
                ['chat_id' => $chatId, 'url' => '/admin/support/live-chats/' . $chatId]
            );
        } elseif (
            str_contains($lower, 'hello') ||
            str_contains($lower, 'hi') ||
            str_contains($lower, 'hey')
        ) {
            $this->model->addLiveMessage($chatId, 'ai', null, "Hello! How can I assist you today?");
        } elseif (str_contains($lower, 'billing') || str_contains($lower, 'invoice') || str_contains($lower, 'payment')) {
            $this->model->addLiveMessage(
                $chatId,
                'ai',
                null,
                "For billing inquiries, please visit the Plans page or create a support ticket for faster assistance."
            );
        } elseif (str_contains($lower, 'password') || str_contains($lower, 'login') || str_contains($lower, 'access')) {
            $this->model->addLiveMessage(
                $chatId,
                'ai',
                null,
                "For account access issues, you can use the Forgot Password feature on the login page. If the issue persists, please create a support ticket."
            );
        } elseif (str_contains($lower, 'refund') || str_contains($lower, 'cancel')) {
            $this->model->addLiveMessage(
                $chatId,
                'ai',
                null,
                "I'll need to connect you with our billing team for refund or cancellation requests. Please hold while I transfer you."
            );
            $this->notifyAdmins(
                'support_live_chat',
                "Live chat #{$chatId}: user requested refund/cancellation.",
                ['chat_id' => $chatId, 'url' => '/admin/support/live-chats/' . $chatId]
            );
        } elseif (str_contains($lower, 'thank')) {
            $this->model->addLiveMessage($chatId, 'ai', null, "You're welcome! Is there anything else I can help you with?");
        } elseif (str_contains($lower, 'bye') || str_contains($lower, 'goodbye')) {
            $this->model->addLiveMessage($chatId, 'ai', null, "Goodbye! Feel free to reach out anytime. Have a great day!");
        } else {
            $this->model->addLiveMessage(
                $chatId,
                'ai',
                null,
                "Thank you for your message. Our support team will review it shortly. You can also create a formal support ticket at any time for tracked assistance."
            );
        }

        $this->jsonOk([]);
    }

    // -------------------------------------------------------------------------
    // GET /support/live/messages
    // -------------------------------------------------------------------------

    public function poll(): void
    {
        $sessionKey = $_GET['session_key'] ?? '';
        $afterId    = (int) ($_GET['after'] ?? 0);

        if ($sessionKey === '') {
            $this->jsonError('Missing session key.');
            return;
        }

        $chat = $this->model->getChatBySessionKey($sessionKey);
        if (!$chat) {
            $this->jsonError('Chat not found.');
            return;
        }

        $chatId   = (int) $chat['id'];
        $messages = $this->model->getLiveMessages($chatId);

        if ($afterId > 0) {
            $messages = array_values(array_filter($messages, fn($m) => (int) $m['id'] > $afterId));
        }

        $this->jsonOk([
            'messages' => $messages,
            'status'   => $chat['status'],
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /support/live/close
    // -------------------------------------------------------------------------

    public function close(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->jsonError('Invalid CSRF token.', 403);
            return;
        }

        $sessionKey = $_POST['session_key'] ?? '';
        if ($sessionKey === '') {
            $this->jsonError('Missing session key.');
            return;
        }

        $chat = $this->model->getChatBySessionKey($sessionKey);
        if (!$chat) {
            $this->jsonError('Chat not found.');
            return;
        }

        $this->model->closeChat((int) $chat['id']);

        if (isset($_SESSION['support_live_key']) && $_SESSION['support_live_key'] === $sessionKey) {
            unset($_SESSION['support_live_key']);
        }

        $this->jsonOk([]);
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/live-chats/{id}/reply
    // -------------------------------------------------------------------------

    public function agentReply(int $chatId): void
    {
        if (!Auth::check()) {
            $this->jsonError('Unauthenticated.', 401);
            return;
        }
        if (!Auth::isAdmin() && !Auth::hasPermissionGroup('support')) {
            $this->jsonError('Forbidden.', 403);
            return;
        }

        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->jsonError('Invalid CSRF token.', 403);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            $this->jsonError('Message cannot be empty.');
            return;
        }

        $chat = $this->model->getChatById($chatId);
        if (!$chat || $chat['status'] !== 'active') {
            $this->jsonError('Chat not found or is closed.');
            return;
        }

        $this->model->addLiveMessage($chatId, 'agent', Auth::id(), $message);
        $this->model->assignChatAgent($chatId, Auth::id());

        // Notify the user if they are logged in
        if (!empty($chat['user_id'])) {
            Notification::send(
                (int) $chat['user_id'],
                'live_chat_reply',
                "An agent replied to your chat session.",
                ['chat_id' => $chatId, 'url' => '#']
            );
        }

        if ($this->isJsonRequest()) {
            $this->jsonOk([]);
        } else {
            header('Location: /admin/support/live-chats/' . $chatId);
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // POST /admin/support/live-chats/{id}/close
    // -------------------------------------------------------------------------

    public function agentClose(int $chatId): void
    {
        if (!Auth::check() || !Auth::isAdmin()) {
            $this->jsonError('Forbidden.', 403);
            return;
        }

        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->jsonError('Invalid CSRF token.', 403);
            return;
        }

        $chat = $this->model->getChatById($chatId);
        if (!$chat) {
            $this->jsonError('Chat not found.');
            return;
        }

        $this->model->closeChat($chatId);

        header('Location: /admin/support/live-chats');
        exit;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function notifyAdmins(string $type, string $message, array $data = []): void
    {
        $db     = Database::getInstance();
        $admins = $db->fetchAll("SELECT id FROM users WHERE role LIKE '%admin%'") ?: [];
        foreach ($admins as $admin) {
            Notification::send((int) $admin['id'], $type, $message, $data);
        }
    }

    private function jsonOk(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }

    private function jsonError(string $message, int $status = 400): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

    private function isJsonRequest(): bool
    {
        $accept      = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($contentType) === 'xmlhttprequest';
    }
}
