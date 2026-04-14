<?php
/**
 * MailService
 *
 * Unified mail service: loads SMTP/IMAP config from DB (mail_provider_configs),
 * sends emails via SMTP, syncs IMAP inbox, renders notification templates,
 * and manages the DB-backed email queue.
 *
 * @package MMB\Core
 */

namespace Core;

class MailService
{
    /** Cached active provider config */
    private static ?array $provider = null;

    // ------------------------------------------------------------------
    // Config loading
    // ------------------------------------------------------------------

    /**
     * Get the currently active mail provider config from DB.
     * Falls back to config/mail.php values if no DB record is active.
     */
    public static function getActiveProvider(): ?array
    {
        if (self::$provider !== null) {
            return self::$provider;
        }

        try {
            $db = Database::getInstance();
            $row = $db->fetch(
                "SELECT * FROM mail_provider_configs WHERE is_active = 1 LIMIT 1"
            );
            if ($row) {
                // Decrypt passwords
                $row['smtp_password'] = self::decryptPassword($row['smtp_password']);
                $row['imap_password'] = self::decryptPassword($row['imap_password']);
                self::$provider = $row;
                return self::$provider;
            }
        } catch (\Exception $e) {
            // DB not available, fall through to file config
        }

        // Fallback to file-based config
        $file = BASE_PATH . '/config/mail.php';
        if (file_exists($file)) {
            $cfg = require $file;
            self::$provider = [
                'id' => null,
                'provider_type' => $cfg['driver'] ?? 'smtp',
                'smtp_host' => $cfg['smtp']['host'] ?? '',
                'smtp_port' => $cfg['smtp']['port'] ?? 587,
                'smtp_username' => $cfg['smtp']['username'] ?? '',
                'smtp_password' => $cfg['smtp']['password'] ?? '',
                'smtp_encryption' => $cfg['smtp']['encryption'] ?? 'tls',
                'from_name' => $cfg['from']['name'] ?? (defined('APP_NAME') ? APP_NAME : 'Platform'),
                'from_email' => $cfg['from']['address'] ?? '',
                'reply_to' => $cfg['from']['address'] ?? '',
                'is_imap_enabled' => 0,
                'imap_host' => '',
                'imap_port' => 993,
                'imap_username' => '',
                'imap_password' => '',
                'imap_encryption' => 'ssl',
            ];
            return self::$provider;
        }

        return null;
    }

    /** Force reload of cached provider (call after updating config) */
    public static function clearProviderCache(): void
    {
        self::$provider = null;
    }

    // ------------------------------------------------------------------
    // SMTP sending
    // ------------------------------------------------------------------

    /**
     * Send an email immediately.
     *
     * @param string $to     Recipient address
     * @param string $subject
     * @param string $body   HTML body
     * @param array  $options  cc, bcc, reply_to, from_name, from_email
     */
    public static function sendNow(string $to, string $subject, string $body, array $options = []): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Logger::error("MailService: invalid recipient: $to");
            return false;
        }

        $provider = self::getActiveProvider();
        if (!$provider || empty($provider['smtp_host'])) {
            Logger::error('MailService: no active SMTP provider configured');
            return false;
        }

        $fromName  = $options['from_name']  ?? $provider['from_name']  ?? (defined('APP_NAME') ? APP_NAME : 'Platform');
        $fromEmail = $options['from_email'] ?? $provider['from_email'] ?? '';
        $replyTo   = $options['reply_to']   ?? $provider['reply_to']   ?? $fromEmail;

        try {
            $result = self::smtpSend(
                $provider,
                $to,
                $subject,
                $body,
                $fromName,
                $fromEmail,
                $replyTo,
                $options
            );

            self::logSend($options['user_id'] ?? null, $to, $subject, $options['template_slug'] ?? null, $provider['id'] ?? null, $result ? 'sent' : 'failed');

            return $result;
        } catch (\Exception $e) {
            Logger::error('MailService SMTP error: ' . $e->getMessage());
            self::logSend($options['user_id'] ?? null, $to, $subject, $options['template_slug'] ?? null, $provider['id'] ?? null, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Queue an email for background sending.
     */
    public static function queue(string $to, string $subject, string $body, array $options = []): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $db = Database::getInstance();
            $db->insert('email_queue', [
                'to_email'   => $to,
                'subject'    => $subject,
                'body'       => $body,
                'cc'         => $options['cc'] ?? null,
                'bcc'        => $options['bcc'] ?? null,
                'reply_to'   => $options['reply_to'] ?? null,
                'priority'   => $options['priority'] ?? 5,
                'status'     => 'pending',
                'attempts'   => 0,
                'max_attempts' => 3,
            ]);
            return true;
        } catch (\Exception $e) {
            Logger::error('MailService queue error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process pending emails in the queue.
     *
     * @param int $limit Max emails to process in one run
     * @return int Number of emails successfully sent
     */
    public static function processQueue(int $limit = 50): int
    {
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT * FROM email_queue
                 WHERE status = 'pending' AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                 ORDER BY priority ASC, created_at ASC
                 LIMIT ?",
                [$limit]
            );

            $sent = 0;
            foreach ($rows as $row) {
                // Mark as processing
                $db->update('email_queue', ['status' => 'processing'], 'id = ?', [$row['id']]);

                $success = self::sendNow($row['to_email'], $row['subject'], $row['body'], [
                    'cc'       => $row['cc'] ?? null,
                    'bcc'      => $row['bcc'] ?? null,
                    'reply_to' => $row['reply_to'] ?? null,
                ]);

                if ($success) {
                    $db->update('email_queue', ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')], 'id = ?', [$row['id']]);
                    $sent++;
                } else {
                    $attempts = (int)$row['attempts'] + 1;
                    $newStatus = $attempts >= (int)$row['max_attempts'] ? 'failed' : 'pending';
                    $db->update('email_queue', [
                        'status'   => $newStatus,
                        'attempts' => $attempts,
                        'error_message' => 'Send failed',
                    ], 'id = ?', [$row['id']]);
                }
            }
            return $sent;
        } catch (\Exception $e) {
            Logger::error('MailService processQueue error: ' . $e->getMessage());
            return 0;
        }
    }

    // ------------------------------------------------------------------
    // Notification templates
    // ------------------------------------------------------------------

    /**
     * Send a notification email using a stored template.
     *
     * @param string $to       Recipient address
     * @param string $slug     Template slug (e.g. 'welcome', 'password_reset')
     * @param array  $vars     Variables to replace in the template
     * @param bool   $queued   If true, adds to queue instead of sending immediately
     */
    public static function sendNotification(string $to, string $slug, array $vars = [], bool $queued = true): bool
    {
        try {
            $db = Database::getInstance();
            $template = $db->fetch(
                "SELECT * FROM mail_notification_templates WHERE slug = ? AND is_enabled = 1 LIMIT 1",
                [$slug]
            );
        } catch (\Exception $e) {
            $template = null;
        }

        // Fallback to view-file templates if no DB template
        if (!$template) {
            return self::sendFromViewTemplate($to, $slug, $vars, $queued);
        }

        $vars['app_name'] = $vars['app_name'] ?? (defined('APP_NAME') ? APP_NAME : 'Platform');
        $subject = self::renderVars($template['subject'], $vars);
        $body    = self::wrapBody(self::renderVars($template['body'], $vars), $vars);

        $options = array_merge($vars, ['template_slug' => $slug]);

        return $queued
            ? self::queue($to, $subject, $body, $options)
            : self::sendNow($to, $subject, $body, $options);
    }

    /**
     * Fallback: render from view file (e.g. views/emails/welcome.php)
     */
    private static function sendFromViewTemplate(string $to, string $slug, array $vars, bool $queued): bool
    {
        $viewMap = [
            'welcome'            => 'emails/welcome',
            'email_verification' => 'emails/verify',
            'password_reset'     => 'emails/password-reset',
        ];

        $viewFile = BASE_PATH . '/views/' . ($viewMap[$slug] ?? 'emails/' . $slug) . '.php';
        if (!file_exists($viewFile)) {
            Logger::error("MailService: template not found for slug '$slug'");
            return false;
        }

        $vars['app_name'] = $vars['app_name'] ?? (defined('APP_NAME') ? APP_NAME : 'Platform');
        extract($vars);
        ob_start();
        include $viewFile;
        $body = ob_get_clean();

        $subject = $vars['subject'] ?? ucwords(str_replace(['-', '_'], ' ', $slug));
        $options = array_merge($vars, ['template_slug' => $slug]);

        return $queued
            ? self::queue($to, $subject, $body, $options)
            : self::sendNow($to, $subject, $body, $options);
    }

    // ------------------------------------------------------------------
    // IMAP inbox sync
    // ------------------------------------------------------------------

    /**
     * Sync inbox for a user. Requires PHP imap extension.
     *
     * @param int   $userId        User ID whose inbox to sync
     * @param array $imapOverride  Optional IMAP credentials override
     * @param int   $fetchLimit    Max messages to fetch per sync
     * @return int  Number of new messages synced
     */
    public static function syncInbox(int $userId, array $imapOverride = [], int $fetchLimit = 50): int
    {
        if (!function_exists('imap_open')) {
            Logger::error('MailService: PHP imap extension not installed');
            return 0;
        }

        $provider = self::getActiveProvider();
        $cfg = array_merge($provider ?? [], $imapOverride);

        if (empty($cfg['imap_host']) || !($cfg['is_imap_enabled'] ?? false)) {
            return 0;
        }

        $enc    = strtolower($cfg['imap_encryption'] ?? 'ssl');
        $flags  = $enc === 'ssl' ? '/ssl' : ($enc === 'tls' ? '/tls' : '');
        $mailbox = '{' . $cfg['imap_host'] . ':' . ($cfg['imap_port'] ?? 993) . $flags . '/novalidate-cert}INBOX';

        $conn = @imap_open($mailbox, $cfg['imap_username'] ?? '', $cfg['imap_password'] ?? '');
        if (!$conn) {
            Logger::error('MailService IMAP: cannot connect – ' . imap_last_error());
            return 0;
        }

        $count   = imap_num_msg($conn);
        $start   = max(1, $count - $fetchLimit + 1);
        $synced  = 0;
        $db      = Database::getInstance();
        $provId  = $cfg['id'] ?? null;

        for ($i = $count; $i >= $start; $i--) {
            $uid = imap_uid($conn, $i);
            // Skip already-synced
            $exists = $db->fetch(
                "SELECT id FROM mail_synced_messages WHERE uid = ? AND folder = 'INBOX' AND user_id = ? LIMIT 1",
                [(string)$uid, $userId]
            );
            if ($exists) {
                continue;
            }

            $header  = imap_fetchheader($conn, $i);
            $info    = imap_headerinfo($conn, $i);
            $struct  = imap_fetchstructure($conn, $i);
            $bodyText = self::getImapBody($conn, $i, $struct, 'plain');
            $bodyHtml = self::getImapBody($conn, $i, $struct, 'html');

            $fromName  = isset($info->from[0]) ? self::decodeMimeStr($info->from[0]->personal ?? '') : '';
            $fromEmail = isset($info->from[0]) ? ($info->from[0]->mailbox . '@' . $info->from[0]->host) : '';
            $toEmail   = self::addressListToString($info->to ?? []);
            $ccEmail   = self::addressListToString($info->cc  ?? []);
            $subject   = self::decodeMimeStr($info->subject ?? '(no subject)');
            $dateSent  = $info->date ? date('Y-m-d H:i:s', strtotime($info->date)) : null;
            $msgId     = $info->message_id ?? null;
            $isRead    = (bool)($info->Unseen ?? false) ? 0 : 1;

            try {
                $db->insert('mail_synced_messages', [
                    'user_id'            => $userId,
                    'provider_config_id' => $provId,
                    'uid'                => (string)$uid,
                    'folder'             => 'INBOX',
                    'message_id'         => $msgId,
                    'subject'            => mb_substr($subject, 0, 1000),
                    'from_name'          => mb_substr($fromName, 0, 255),
                    'from_email'         => mb_substr($fromEmail, 0, 255),
                    'to_email'           => $toEmail,
                    'cc_email'           => $ccEmail,
                    'date_sent'          => $dateSent,
                    'body_html'          => $bodyHtml,
                    'body_text'          => $bodyText,
                    'is_read'            => $isRead,
                    'raw_headers'        => mb_substr($header, 0, 65535),
                ]);
                $synced++;
            } catch (\Exception $e) {
                // duplicate or insert error – skip
            }
        }

        imap_close($conn);
        return $synced;
    }

    // ------------------------------------------------------------------
    // Test connection
    // ------------------------------------------------------------------

    /**
     * Test SMTP connectivity with given credentials.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testSmtp(array $cfg): array
    {
        try {
            $host = ($cfg['smtp_encryption'] === 'ssl' ? 'ssl://' : '') . $cfg['smtp_host'];
            $port = (int)($cfg['smtp_port'] ?? 587);
            $socket = @fsockopen($host, $port, $errno, $errstr, 10);
            if (!$socket) {
                return ['success' => false, 'message' => "Cannot connect: $errstr ($errno)"];
            }
            $greeting = fgets($socket, 512);
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
            return ['success' => true, 'message' => 'Connected: ' . trim($greeting)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Test IMAP connectivity.
     */
    public static function testImap(array $cfg): array
    {
        if (!function_exists('imap_open')) {
            return ['success' => false, 'message' => 'PHP imap extension not installed on this server'];
        }
        $enc  = strtolower($cfg['imap_encryption'] ?? 'ssl');
        $flags = $enc === 'ssl' ? '/ssl' : ($enc === 'tls' ? '/tls' : '');
        $mailbox = '{' . $cfg['imap_host'] . ':' . ($cfg['imap_port'] ?? 993) . $flags . '/novalidate-cert}INBOX';
        $conn = @imap_open($mailbox, $cfg['imap_username'] ?? '', $cfg['imap_password'] ?? '');
        if (!$conn) {
            return ['success' => false, 'message' => 'IMAP connect failed: ' . imap_last_error()];
        }
        $count = imap_num_msg($conn);
        imap_close($conn);
        return ['success' => true, 'message' => "Connected. Inbox has $count message(s)."];
    }

    // ------------------------------------------------------------------
    // Internal SMTP implementation (raw socket)
    // ------------------------------------------------------------------

    private static function smtpSend(
        array  $provider,
        string $to,
        string $subject,
        string $body,
        string $fromName,
        string $fromEmail,
        string $replyTo,
        array  $options = []
    ): bool {
        $host = $provider['smtp_host'];
        $port = (int)($provider['smtp_port'] ?? 587);
        $enc  = strtolower($provider['smtp_encryption'] ?? 'tls');
        $user = $provider['smtp_username'] ?? '';
        $pass = $provider['smtp_password'] ?? '';

        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host;
        $socket = @fsockopen($remote, $port, $errno, $errstr, 15);
        if (!$socket) {
            throw new \RuntimeException("SMTP fsockopen failed: $errstr ($errno)");
        }

        stream_set_timeout($socket, 15);
        self::smtpRead($socket); // greeting

        self::smtpCmd($socket, "EHLO " . gethostname());

        if ($enc === 'tls') {
            self::smtpCmd($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            self::smtpCmd($socket, "EHLO " . gethostname());
        }

        if ($user !== '') {
            self::smtpCmd($socket, "AUTH LOGIN");
            self::smtpCmd($socket, base64_encode($user));
            self::smtpCmd($socket, base64_encode($pass));
        }

        self::smtpCmd($socket, "MAIL FROM:<$fromEmail>");
        self::smtpCmd($socket, "RCPT TO:<$to>");

        // Handle CC/BCC
        foreach (array_filter([$options['cc'] ?? null, $options['bcc'] ?? null]) as $extra) {
            foreach (array_map('trim', explode(',', $extra)) as $addr) {
                if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                    self::smtpCmd($socket, "RCPT TO:<$addr>");
                }
            }
        }

        self::smtpCmd($socket, "DATA");

        $messageId = '<' . uniqid('mmb', true) . '@' . gethostname() . '>';
        $headers = [
            "Message-ID: $messageId",
            "Date: " . date('r'),
            "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$fromEmail>",
            "To: $to",
            "Reply-To: $replyTo",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: base64",
            "X-Mailer: MMB-MailService/1.0",
        ];
        if (!empty($options['cc'])) {
            $headers[] = "Cc: " . $options['cc'];
        }

        $message = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body)) . "\r\n.";
        $resp = self::smtpCmd($socket, $message);

        self::smtpCmd($socket, "QUIT");
        fclose($socket);

        // SMTP success codes start with 2xx
        return substr(trim($resp), 0, 1) === '2';
    }

    private static function smtpCmd($socket, string $cmd): string
    {
        fwrite($socket, $cmd . "\r\n");
        return self::smtpRead($socket);
    }

    private static function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    // ------------------------------------------------------------------
    // IMAP helpers
    // ------------------------------------------------------------------

    private static function getImapBody($conn, int $msgNum, $struct, string $type): string
    {
        $mimeType = $type === 'html' ? 'text/html' : 'text/plain';
        $part = self::findImapPart($struct, $mimeType);
        if ($part !== null) {
            $raw = imap_fetchbody($conn, $msgNum, (string)($part ?: 1));
            return self::decodeImapBody($raw, $struct->parts[$part - 1] ?? $struct);
        }
        // fallback
        $raw = imap_body($conn, $msgNum);
        return $raw ?: '';
    }

    private static function findImapPart($struct, string $mimeType, int $partNum = 0): ?int
    {
        if (!isset($struct->parts)) {
            return null;
        }
        foreach ($struct->parts as $i => $part) {
            $ct = strtolower(($part->type ?? 0) . '/' . strtolower($part->subtype ?? ''));
            if ($ct === $mimeType || (int)$part->type === 0 && strtolower($part->subtype ?? '') === explode('/', $mimeType)[1]) {
                return $i + 1;
            }
        }
        return null;
    }

    private static function decodeImapBody(string $body, $struct): string
    {
        $encoding = $struct->encoding ?? 0;
        switch ($encoding) {
            case 3: return base64_decode($body);
            case 4: return quoted_printable_decode($body);
            default: return $body;
        }
    }

    private static function decodeMimeStr(string $str): string
    {
        if (function_exists('imap_utf8')) {
            return imap_utf8($str);
        }
        return mb_decode_mimeheader($str);
    }

    private static function addressListToString(array $addrs): string
    {
        $out = [];
        foreach ($addrs as $addr) {
            $email = ($addr->mailbox ?? '') . '@' . ($addr->host ?? '');
            $name  = self::decodeMimeStr($addr->personal ?? '');
            $out[] = $name ? "$name <$email>" : $email;
        }
        return implode(', ', $out);
    }

    // ------------------------------------------------------------------
    // Template helpers
    // ------------------------------------------------------------------

    private static function renderVars(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            if (is_scalar($value)) {
                $template = str_replace('{{' . $key . '}}', (string)$value, $template);
            }
        }
        return $template;
    }

    private static function wrapBody(string $content, array $vars = []): string
    {
        $appName = $vars['app_name'] ?? (defined('APP_NAME') ? APP_NAME : 'Platform');
        $year    = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background:#f4f7fb;color:#333}
.wrap{max-width:600px;margin:32px auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08)}
.header{background:linear-gradient(135deg,#667eea,#764ba2);padding:28px 32px;text-align:center;color:#fff}
.header h1{margin:0;font-size:24px;font-weight:700}
.body{padding:32px}
.body p{line-height:1.7;margin:0 0 16px}
.footer{padding:20px 32px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #eee}
a{color:#667eea}
</style>
</head>
<body>
<div class="wrap">
  <div class="header"><h1>{$appName}</h1></div>
  <div class="body">{$content}</div>
  <div class="footer">&copy; {$year} {$appName}. All rights reserved.</div>
</div>
</body>
</html>
HTML;
    }

    // ------------------------------------------------------------------
    // Password encryption
    // ------------------------------------------------------------------

    public static function encryptPassword(string $plain): string
    {
        if (empty($plain)) {
            return '';
        }
        $key = substr(hash('sha256', defined('APP_KEY') ? APP_KEY : 'mmb_default_key'), 0, 32);
        $iv  = random_bytes(16);
        $enc = openssl_encrypt($plain, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $enc);
    }

    public static function decryptPassword(string $encrypted): string
    {
        if (empty($encrypted)) {
            return '';
        }
        try {
            $key  = substr(hash('sha256', defined('APP_KEY') ? APP_KEY : 'mmb_default_key'), 0, 32);
            $data = base64_decode($encrypted);
            if (strlen($data) < 17) {
                return $encrypted; // not encrypted (plain fallback)
            }
            $iv  = substr($data, 0, 16);
            $enc = substr($data, 16);
            $dec = openssl_decrypt($enc, 'AES-256-CBC', $key, 0, $iv);
            return $dec !== false ? $dec : $encrypted;
        } catch (\Exception $e) {
            return $encrypted;
        }
    }

    // ------------------------------------------------------------------
    // Audit log
    // ------------------------------------------------------------------

    private static function logSend(?int $userId, string $recipient, ?string $subject, ?string $template, ?int $providerId, string $status, ?string $error = null): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('mail_send_log', [
                'user_id'            => $userId,
                'recipient'          => $recipient,
                'subject'            => $subject,
                'template_slug'      => $template,
                'provider_config_id' => $providerId,
                'status'             => $status,
                'error_message'      => $error,
            ]);
        } catch (\Exception $e) {
            // Non-critical – don't throw
        }
    }
}
