<?php
/**
 * Mail Controller
 *
 * Handles the webmail inbox at /mail on the main platform.
 * Routes: GET/POST /mail, /mail/view/{id}, /mail/compose,
 *         /mail/reply, /mail/forward, /mail/search,
 *         /mail/settings, /mail/sync, /mail/mark-read,
 *         /mail/delete, /mail/archive, /mail/star
 *
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Helpers;
use Core\MailService;
use Core\Security;
use Core\Logger;

class MailController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
    }

    // ------------------------------------------------------------------
    // Inbox / folder listing
    // ------------------------------------------------------------------

    public function inbox(): void
    {
        $db      = Database::getInstance();
        $userId  = Auth::id();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;
        $folder  = $_GET['folder'] ?? 'inbox';

        $where  = "user_id = ? AND is_deleted = 0";
        $params = [$userId];

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

        $total = (int)($db->fetch(
            "SELECT COUNT(*) AS c FROM mail_synced_messages WHERE $where",
            $params
        )['c'] ?? 0);

        $unread = (int)($db->fetch(
            "SELECT COUNT(*) AS c FROM mail_synced_messages
             WHERE user_id = ? AND is_read = 0 AND is_deleted = 0 AND is_archived = 0",
            [$userId]
        )['c'] ?? 0);

        $this->view('mail/inbox', compact('messages', 'total', 'page', 'perPage', 'folder', 'unread'));
    }

    // ------------------------------------------------------------------
    // View single message
    // ------------------------------------------------------------------

    public function viewMessage(string $id = '0'): void
    {
        $id     = (int)$id;
        $db     = Database::getInstance();
        $userId = Auth::id();

        $msg = $db->fetch(
            "SELECT * FROM mail_synced_messages WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $userId]
        );

        if (!$msg) {
            $this->redirect('/mail');
            return;
        }

        if (!$msg['is_read']) {
            $db->update('mail_synced_messages', ['is_read' => 1], 'id = ?', [$id]);
            $msg['is_read'] = 1;
        }

        $this->view('mail/view', compact('msg'));
    }

    // ------------------------------------------------------------------
    // Compose
    // ------------------------------------------------------------------

    public function compose(): void
    {
        $this->view('mail/compose', []);
    }

    // ------------------------------------------------------------------
    // Search
    // ------------------------------------------------------------------

    public function search(): void
    {
        $q      = trim($_GET['q'] ?? '');
        $userId = Auth::id();
        $db     = Database::getInstance();

        $messages = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $messages = $db->fetchAll(
                "SELECT id, subject, from_name, from_email, date_sent, is_read, is_starred
                 FROM mail_synced_messages
                 WHERE user_id = ? AND is_deleted = 0
                   AND (subject LIKE ? OR from_name LIKE ? OR from_email LIKE ? OR body_text LIKE ?)
                 ORDER BY date_sent DESC LIMIT 100",
                [$userId, $like, $like, $like, $like]
            );
        }

        $total  = count($messages);
        $page   = 1;
        $perPage = 100;
        $folder  = 'search';
        $unread  = 0;
        $searchQuery = $q;

        $this->view('mail/inbox', compact('messages', 'total', 'page', 'perPage', 'folder', 'unread', 'searchQuery'));
    }

    // ------------------------------------------------------------------
    // Settings
    // ------------------------------------------------------------------

    public function settings(): void
    {
        $provider = MailService::getActiveProvider();
        $user     = Auth::user();
        $this->view('mail/settings', compact('provider', 'user'));
    }

    public function saveSettings(): void
    {
        if (!$this->validateCsrf()) {
            Helpers::flash('error', 'Invalid request.');
            $this->redirect('/mail/settings');
            return;
        }
        Helpers::flash('success', 'Settings saved.');
        $this->redirect('/mail/settings');
    }

    // ------------------------------------------------------------------
    // Send (compose form submit)
    // ------------------------------------------------------------------

    public function send(): void
    {
        if (!$this->validateCsrf()) {
            Helpers::flash('error', 'Invalid request.');
            $this->redirect('/mail/compose');
            return;
        }

        $to      = trim($this->input('to', ''));
        $subject = trim($this->input('subject', ''));
        $body    = $this->input('body', '');
        $cc      = trim($this->input('cc', ''));
        $bcc     = trim($this->input('bcc', ''));
        $user    = Auth::user();

        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || empty($subject)) {
            Helpers::flash('error', 'Recipient email and subject are required.');
            $this->redirect('/mail/compose');
            return;
        }

        $opts = [
            'user_id'    => Auth::id(),
            'from_name'  => $user['name'] ?? '',
            'from_email' => $user['email'] ?? '',
        ];
        if ($cc !== '')  { $opts['cc']  = $cc; }
        if ($bcc !== '') { $opts['bcc'] = $bcc; }

        $sent = MailService::sendNow($to, $subject, $body, $opts);

        if ($sent) {
            Helpers::flash('success', "Email sent to $to.");
        } else {
            Helpers::flash('error', 'Failed to send email. Check mail configuration in admin panel.');
        }
        $this->redirect('/mail');
    }

    // ------------------------------------------------------------------
    // Reply
    // ------------------------------------------------------------------

    public function reply(): void
    {
        if (!$this->validateCsrf()) {
            Helpers::flash('error', 'Invalid request.');
            $this->redirect('/mail');
            return;
        }

        $origId  = (int)$this->input('orig_id', 0);
        $to      = trim($this->input('to', ''));
        $subject = trim($this->input('subject', ''));
        $body    = $this->input('body', '');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Helpers::flash('error', 'Invalid recipient email.');
            $this->redirect('/mail/view/' . $origId);
            return;
        }

        MailService::sendNow($to, $subject, $body, ['user_id' => Auth::id()]);
        Helpers::flash('success', 'Reply sent.');
        $this->redirect('/mail/view/' . $origId);
    }

    // ------------------------------------------------------------------
    // Forward
    // ------------------------------------------------------------------

    public function forward(): void
    {
        if (!$this->validateCsrf()) {
            Helpers::flash('error', 'Invalid request.');
            $this->redirect('/mail');
            return;
        }

        $origId  = (int)$this->input('orig_id', 0);
        $to      = trim($this->input('to', ''));
        $subject = trim($this->input('subject', ''));
        $body    = $this->input('body', '');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Helpers::flash('error', 'Invalid recipient email.');
            $this->redirect('/mail/view/' . $origId);
            return;
        }

        MailService::sendNow($to, $subject, $body, ['user_id' => Auth::id()]);
        Helpers::flash('success', 'Message forwarded.');
        $this->redirect('/mail/view/' . $origId);
    }

    // ------------------------------------------------------------------
    // AJAX actions (JSON responses)
    // ------------------------------------------------------------------

    public function markRead(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $id     = (int)$this->input('id', 0);
        $state  = (int)$this->input('state', 1);
        $userId = Auth::id();
        $db     = Database::getInstance();
        $db->update('mail_synced_messages', ['is_read' => $state], 'id = ? AND user_id = ?', [$id, $userId]);
        $this->json(['success' => true]);
    }

    public function delete(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $id     = (int)$this->input('id', 0);
        $userId = Auth::id();
        $db     = Database::getInstance();
        $db->update('mail_synced_messages', ['is_deleted' => 1], 'id = ? AND user_id = ?', [$id, $userId]);
        $this->json(['success' => true]);
    }

    public function archive(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $id     = (int)$this->input('id', 0);
        $state  = (int)$this->input('state', 1);
        $userId = Auth::id();
        $db     = Database::getInstance();
        $db->update('mail_synced_messages', ['is_archived' => $state], 'id = ? AND user_id = ?', [$id, $userId]);
        $this->json(['success' => true]);
    }

    public function star(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $id     = (int)$this->input('id', 0);
        $state  = (int)$this->input('state', 1);
        $userId = Auth::id();
        $db     = Database::getInstance();
        $db->update('mail_synced_messages', ['is_starred' => $state], 'id = ? AND user_id = ?', [$id, $userId]);
        $this->json(['success' => true]);
    }

    // ------------------------------------------------------------------
    // IMAP sync
    // ------------------------------------------------------------------

    public function sync(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $synced = MailService::syncInbox(Auth::id(), [], 50);
        $this->json(['success' => true, 'synced' => $synced]);
    }
}
