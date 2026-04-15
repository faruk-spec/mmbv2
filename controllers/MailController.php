<?php
/**
 * Mail Controller
 *
 * Handles the webmail inbox at /mail on the main platform.
 * Routes: GET/POST /mail, /mail/view/{id}, /mail/compose,
 *         /mail/reply, /mail/forward, /mail/search,
 *         /mail/settings, /mail/sync, /mail/mark-read,
 *         /mail/delete, /mail/archive, /mail/star,
 *         /mail/sent, /mail/suggest-recipients
 *
 * Access: admin role OR explicit 'mail' permission in admin_user_permissions.
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

        // /mail is only accessible to admins or users explicitly granted the 'mail' permission
        if (!\Core\Auth::isAdmin() && !\Core\Auth::hasPermission('mail')) {
            $isXhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json');
            if ($isXhr) {
                $this->json(['success' => false, 'message' => 'Access denied.'], 403);
            } else {
                \Core\Helpers::flash('error', 'You do not have permission to access the mail module.');
                $this->redirect('/dashboard');
            }
            exit;
        }

        MailService::ensureSchema();
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

        $where  = "user_id = ?";
        $params = [$userId];

        if ($folder === 'trash') {
            $where .= ' AND is_deleted = 1';
        } elseif ($folder === 'starred') {
            $where .= ' AND is_deleted = 0 AND is_starred = 1';
        } elseif ($folder === 'archived') {
            $where .= ' AND is_deleted = 0 AND is_archived = 1';
        } else {
            $where .= ' AND is_deleted = 0 AND is_archived = 0';
        }

        $messages = $db->fetchAll(
            "SELECT id, subject, from_name, from_email, date_sent, is_read, is_starred, is_archived,
                    LEFT(body_text, 120) AS body_text
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

        // Load sent replies that reference this inbox message
        $sentReplies = [];
        try {
            $sentReplies = $db->fetchAll(
                "SELECT id, recipient, subject, body_html, body_text, sent_at, status
                 FROM mail_send_log
                 WHERE reply_to_inbox_id = ? AND user_id = ?
                 ORDER BY sent_at ASC",
                [$id, $userId]
            );
        } catch (\Exception $e) {
            // reply_to_inbox_id column may not exist yet
        }

        $this->view('mail/view', compact('msg', 'sentReplies'));
    }

    // ------------------------------------------------------------------
    // Compose
    // ------------------------------------------------------------------

    public function compose(): void
    {
        $providers = MailService::getAllProviders();
        $composeTemplates = [];
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT slug, name, subject, body
                 FROM mail_notification_templates
                 WHERE is_enabled = 1
                 ORDER BY name ASC"
            );
            foreach ($rows as $row) {
                $composeTemplates[] = [
                    'name' => $row['name'] ?: $row['slug'],
                    'subject' => $row['subject'] ?? '',
                    'body' => $row['body'] ?? '',
                ];
            }
        } catch (\Throwable $e) {
            // optional templates
        }

        $this->view('mail/compose', compact('providers', 'composeTemplates'));
    }

    // ------------------------------------------------------------------
    // Sent history
    // ------------------------------------------------------------------

    public function sent(): void
    {
        $db      = Database::getInstance();
        $userId  = Auth::id();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $messages = $db->fetchAll(
            "SELECT id, recipient AS to_email, subject, provider_config_id, status, error_message, sent_at
             FROM mail_send_log
             WHERE user_id = ?
             ORDER BY sent_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $perPage, $offset]
        );

        $total = (int)($db->fetch(
            "SELECT COUNT(*) AS c FROM mail_send_log WHERE user_id = ?",
            [$userId]
        )['c'] ?? 0);

        $this->view('mail/sent', compact('messages', 'total', 'page', 'perPage'));
    }

    // ------------------------------------------------------------------
    // Recipient autocomplete (AJAX)
    // ------------------------------------------------------------------

    public function suggestRecipients(): void
    {
        $q      = trim($_GET['q'] ?? '');
        $userId = Auth::id();
        $db     = Database::getInstance();

        if (strlen($q) < 2) {
            $this->json([]);
            return;
        }

        $like    = '%' . $q . '%';
        $results = $db->fetchAll(
            "SELECT recipient FROM mail_send_log
             WHERE user_id = ? AND recipient LIKE ? AND status = 'sent'
             GROUP BY recipient
             ORDER BY MAX(sent_at) DESC
             LIMIT 10",
            [$userId, $like]
        );

        $this->json(array_column($results, 'recipient'));
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

        $toRaw      = trim($this->input('to', ''));
        $subject    = trim($this->input('subject', ''));
        $body       = $this->input('body', '');
        $cc         = trim($this->input('cc', ''));
        $bcc        = trim($this->input('bcc', ''));
        $providerId = (int)$this->input('provider_id', 0);
        $user       = Auth::user();

        if (empty($toRaw) || empty($subject)) {
            Helpers::flash('error', 'Recipient email and subject are required.');
            $this->redirect('/mail/compose');
            return;
        }

        // Validate all To addresses
        $toList = array_filter(array_map('trim', explode(',', $toRaw)), fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL));
        if (empty($toList)) {
            Helpers::flash('error', 'Please enter at least one valid recipient email address.');
            $this->redirect('/mail/compose');
            return;
        }

        $opts = ['user_id' => Auth::id()];
        if ($cc !== '')           { $opts['cc']  = $cc; }
        if ($bcc !== '')          { $opts['bcc'] = $bcc; }
        if ($providerId > 0)      { $opts['provider_id'] = $providerId; }

        // Join multiple recipients as comma-separated for sendNow
        $toStr = implode(',', $toList);
        $sent  = MailService::sendNow($toStr, $subject, $body, $opts);

        if ($sent) {
            $count = count($toList);
            Helpers::flash('success', $count > 1 ? "Email sent to $count recipients." : "Email sent to {$toList[array_key_first($toList)]}.");
        } else {
            Helpers::flash('error', 'Failed to send email. Check mail configuration in admin panel.');
        }
        $this->redirect('/mail/sent');
    }

    // ------------------------------------------------------------------
    // View a sent message
    // ------------------------------------------------------------------

    public function viewSent(string $id = '0'): void
    {
        $id     = (int)$id;
        $db     = Database::getInstance();
        $userId = Auth::id();

        $msg = $db->fetch(
            "SELECT * FROM mail_send_log WHERE id = ? AND user_id = ? LIMIT 1",
            [$id, $userId]
        );

        if (!$msg) {
            $this->redirect('/mail/sent');
            return;
        }

        // Build reply thread: walk up to root, then load all replies in that thread
        $rootId = $msg['in_reply_to_id'] ?? null;
        $thread = [];
        try {
            if ($rootId) {
                // Load the original message if we are a reply
                $root = $db->fetch(
                    "SELECT id, recipient, subject, body_html, body_text, sent_at, status FROM mail_send_log WHERE id = ? AND user_id = ? LIMIT 1",
                    [$rootId, $userId]
                );
                if ($root) {
                    $thread[] = $root;
                }
            }
            // Load all replies to this message (replies sent by this user referencing current id)
            $replies = $db->fetchAll(
                "SELECT id, recipient, subject, body_html, body_text, sent_at, status FROM mail_send_log
                 WHERE in_reply_to_id = ? AND user_id = ?
                 ORDER BY sent_at ASC",
                [$id, $userId]
            );
            foreach ($replies as $r) {
                if ((int)$r['id'] !== $id) {
                    $thread[] = $r;
                }
            }
        } catch (\Exception $e) {
            // in_reply_to_id column may not exist yet — thread just stays empty
        }

        $this->view('mail/sent-view', compact('msg', 'thread'));
    }

    // ------------------------------------------------------------------
    // Reply to a sent message
    // ------------------------------------------------------------------

    public function replySent(): void
    {
        if (!$this->validateCsrf()) {
            Helpers::flash('error', 'Invalid request.');
            $this->redirect('/mail/sent');
            return;
        }

        $origId     = (int)$this->input('orig_id', 0);
        $to         = trim($this->input('to', ''));
        $subject    = trim($this->input('subject', ''));
        $body       = $this->input('body', '');
        $providerId = (int)$this->input('provider_id', 0);

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Helpers::flash('error', 'Invalid recipient email.');
            $this->redirect('/mail/sent/view/' . $origId);
            return;
        }

        $opts = ['user_id' => Auth::id()];
        if ($providerId > 0) { $opts['provider_id'] = $providerId; }
        if ($origId > 0) { $opts['in_reply_to_id'] = $origId; }

        MailService::sendNow($to, $subject, $body, $opts);
        Helpers::flash('success', 'Reply sent.');
        $this->redirect('/mail/sent/view/' . $origId);
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

        $opts = [
            'user_id'           => Auth::id(),
            'reply_to_inbox_id' => $origId > 0 ? $origId : null,
        ];

        MailService::sendNow($to, $subject, $body, $opts);
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

        $userId    = Auth::id();
        $providers = MailService::getUserProviders($userId);
        $imapCount = count(array_filter($providers, fn($p) => !empty($p['imap_host']) && !empty($p['is_imap_enabled'])));
        $synced    = MailService::syncInboxForUser($userId, 50);

        $message = $synced > 0
            ? "$synced new message(s) synced."
            : ($imapCount === 0
                ? 'No IMAP account configured. Ask your admin to assign one under Admin → Mail User Access.'
                : 'Inbox is up to date.');

        $this->json(['success' => true, 'synced' => $synced, 'message' => $message]);
    }
}
