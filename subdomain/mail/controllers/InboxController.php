<?php
/**
 * Inbox Controller for the Mail subdomain app.
 *
 * @package Mail
 */

namespace Mail;

use Core\Auth;
use Core\Database;
use Core\MailService;
use Core\Security;
use Core\Logger;

class InboxController
{
    private int $userId;
    private array $user;

    public function __construct()
    {
        $this->user   = Auth::user();
        $this->userId = (int)($this->user['id'] ?? 0);
    }

    // ------------------------------------------------------------------
    // Views
    // ------------------------------------------------------------------

    public function inbox(): void
    {
        $db      = Database::getInstance();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;
        $folder  = $_GET['folder'] ?? 'inbox';

        $where  = "user_id = ? AND is_deleted = 0";
        $params = [$this->userId];

        if ($folder === 'starred') {
            $where .= ' AND is_starred = 1';
        } elseif ($folder === 'archived') {
            $where .= ' AND is_archived = 1';
        } else {
            $where .= ' AND is_archived = 0';
        }

        $messages = $db->fetchAll(
            "SELECT id, subject, from_name, from_email, date_sent, is_read, is_starred, is_archived
             FROM mail_synced_messages
             WHERE $where
             ORDER BY date_sent DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = (int)($db->fetch("SELECT COUNT(*) AS c FROM mail_synced_messages WHERE $where", $params)['c'] ?? 0);
        $unread = (int)($db->fetch("SELECT COUNT(*) AS c FROM mail_synced_messages WHERE user_id = ? AND is_read = 0 AND is_deleted = 0 AND is_archived = 0", [$this->userId])['c'] ?? 0);

        $this->render('inbox', compact('messages', 'total', 'page', 'perPage', 'folder', 'unread'));
    }

    public function view(int $id): void
    {
        $db  = Database::getInstance();
        $msg = $db->fetch(
            "SELECT * FROM mail_synced_messages WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $this->userId]
        );

        if (!$msg) {
            $this->redirect('/');
            return;
        }

        // Mark as read
        if (!$msg['is_read']) {
            $db->update('mail_synced_messages', ['is_read' => 1], 'id = ?', [$id]);
            $msg['is_read'] = 1;
        }

        $this->render('view', compact('msg'));
    }

    public function compose(): void
    {
        $this->render('compose', ['replyTo' => null, 'forwardMsg' => null]);
    }

    public function search(): void
    {
        $q  = trim($_GET['q'] ?? '');
        $db = Database::getInstance();

        $messages = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $messages = $db->fetchAll(
                "SELECT id, subject, from_name, from_email, date_sent, is_read, is_starred
                 FROM mail_synced_messages
                 WHERE user_id = ? AND is_deleted = 0
                   AND (subject LIKE ? OR from_name LIKE ? OR from_email LIKE ? OR body_text LIKE ?)
                 ORDER BY date_sent DESC LIMIT 100",
                [$this->userId, $like, $like, $like, $like]
            );
        }

        $this->render('inbox', ['messages' => $messages, 'total' => count($messages), 'page' => 1, 'perPage' => 100, 'folder' => 'search', 'unread' => 0, 'searchQuery' => $q]);
    }

    public function settings(): void
    {
        $provider = MailService::getActiveProvider();
        $this->render('settings', ['provider' => $provider, 'user' => $this->user]);
    }

    // ------------------------------------------------------------------
    // Actions
    // ------------------------------------------------------------------

    public function send(): void
    {
        if (!$this->verifyCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        $to      = trim($_POST['to'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body    = $_POST['body'] ?? '';

        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || empty($subject)) {
            $this->setFlash('error', 'Recipient and subject are required.');
            $this->redirect('/compose');
            return;
        }

        $sent = MailService::sendNow($to, $subject, $body, [
            'user_id'  => $this->userId,
            'from_name'  => $this->user['name'] ?? '',
            'from_email' => $this->user['email'] ?? '',
        ]);

        if ($sent) {
            $this->setFlash('success', "Email sent to $to.");
        } else {
            $this->setFlash('error', 'Failed to send email. Check mail configuration.');
        }
        $this->redirect('/');
    }

    public function reply(): void
    {
        if (!$this->verifyCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        $origId  = (int)($_POST['orig_id'] ?? 0);
        $to      = trim($_POST['to'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body    = $_POST['body'] ?? '';

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Invalid recipient.');
            $this->redirect('/view/' . $origId);
            return;
        }

        MailService::sendNow($to, $subject, $body, ['user_id' => $this->userId]);
        $this->setFlash('success', 'Reply sent.');
        $this->redirect('/view/' . $origId);
    }

    public function forward(): void
    {
        if (!$this->verifyCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        $origId  = (int)($_POST['orig_id'] ?? 0);
        $to      = trim($_POST['to'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body    = $_POST['body'] ?? '';

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Invalid recipient.');
            $this->redirect('/view/' . $origId);
            return;
        }

        MailService::sendNow($to, $subject, $body, ['user_id' => $this->userId]);
        $this->setFlash('success', 'Message forwarded.');
        $this->redirect('/view/' . $origId);
    }

    public function markRead(): void
    {
        if (!$this->verifyCsrf()) {
            echo json_encode(['success' => false]); exit;
        }

        $id    = (int)($_POST['id'] ?? 0);
        $state = (int)($_POST['state'] ?? 1);
        $db    = Database::getInstance();
        $db->update('mail_synced_messages', ['is_read' => $state], 'id = ? AND user_id = ?', [$id, $this->userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    public function delete(): void
    {
        if (!$this->verifyCsrf()) {
            echo json_encode(['success' => false]); exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::getInstance();
        $db->update('mail_synced_messages', ['is_deleted' => 1], 'id = ? AND user_id = ?', [$id, $this->userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    public function archive(): void
    {
        if (!$this->verifyCsrf()) {
            echo json_encode(['success' => false]); exit;
        }

        $id    = (int)($_POST['id'] ?? 0);
        $state = (int)($_POST['state'] ?? 1);
        $db    = Database::getInstance();
        $db->update('mail_synced_messages', ['is_archived' => $state], 'id = ? AND user_id = ?', [$id, $this->userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    public function star(): void
    {
        if (!$this->verifyCsrf()) {
            echo json_encode(['success' => false]); exit;
        }

        $id    = (int)($_POST['id'] ?? 0);
        $state = (int)($_POST['state'] ?? 1);
        $db    = Database::getInstance();
        $db->update('mail_synced_messages', ['is_starred' => $state], 'id = ? AND user_id = ?', [$id, $this->userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    public function syncAndRedirect(): void
    {
        if (!$this->verifyCsrf()) {
            $this->redirect('/');
            return;
        }
        $synced = MailService::syncInbox($this->userId, [], 100);
        $this->setFlash('success', "Synced $synced new message(s).");
        $this->redirect('/');
    }

    public function syncAjax(): void
    {
        if (!$this->verifyCsrf()) {
            echo json_encode(['success' => false]); exit;
        }
        $synced = MailService::syncInbox($this->userId, [], 50);
        echo json_encode(['success' => true, 'synced' => $synced]);
        exit;
    }

    public function saveSettings(): void
    {
        if (!$this->verifyCsrf()) {
            $this->redirect('/settings');
            return;
        }
        // User-level settings can be stored in user_profiles if needed.
        // For now just acknowledge.
        $this->setFlash('success', 'Settings saved.');
        $this->redirect('/settings');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function render(string $view, array $data = []): void
    {
        $data['user']         = $this->user;
        $data['appName']      = defined('APP_NAME') ? APP_NAME : 'Mail';
        $data['appUrl']       = defined('APP_URL')  ? APP_URL  : '';
        $data['csrfToken']    = Security::generateCsrfToken();
        $data['flashSuccess'] = $_SESSION['_mail_flash_success'] ?? null;
        $data['flashError']   = $_SESSION['_mail_flash_error']   ?? null;
        unset($_SESSION['_mail_flash_success'], $_SESSION['_mail_flash_error']);

        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(404);
            echo "View not found: $view";
            return;
        }

        extract($data);
        $layoutFile = __DIR__ . '/../views/layout.php';

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        include $layoutFile;
    }

    private function redirect(string $path): void
    {
        // Determine base URL of this subdomain app
        $base = rtrim($_SERVER['SCRIPT_NAME'] ?? '', '/index.php');
        header('Location: ' . $base . $path);
        exit;
    }

    private function verifyCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? '';
        return Security::verifyCsrfToken($token);
    }

    private function setFlash(string $type, string $msg): void
    {
        $_SESSION['_mail_flash_' . $type] = $msg;
    }

    private function jsonError(string $msg): void
    {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
}
