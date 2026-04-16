<?php
/**
 * Mail Service
 * 
 * Handles email sending via SMTP, sendmail, or PHP mail()
 * 
 * @package MMB\Core
 */

namespace Core;

class Mailer
{
    private static ?array $config = null;
    
    /**
     * Get mail configuration.
     * First checks the admin-configured mail_provider_configs DB table for an active provider.
     * Falls back to config/mail.php if no active DB provider is found.
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = self::loadConfig();
        }
        return self::$config;
    }

    private static function loadConfig(): array
    {
        // Try to load from DB-stored active provider (configured via Admin → Mail Config)
        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT * FROM mail_provider_configs WHERE is_active = 1 LIMIT 1"
            );
            if ($row && !empty($row['smtp_host'])) {
                // Passwords are stored encrypted by MailConfigController; decrypt before use.
                $smtpPassword = MailService::decryptPassword($row['smtp_password'] ?? '');
                return [
                    'driver' => 'smtp',
                    'smtp'   => [
                        'host'       => $row['smtp_host'],
                        'port'       => (int)($row['smtp_port'] ?? 587),
                        'encryption' => $row['smtp_encryption'] ?? 'tls',
                        'username'   => $row['smtp_username'] ?? '',
                        'password'   => $smtpPassword,
                    ],
                    'from' => [
                        'address' => $row['from_email'] ?? ($row['smtp_username'] ?? ''),
                        'name'    => $row['from_name'] ?? 'Support',
                    ],
                ];
            }
        } catch (\Exception $e) {
            // DB not ready or table doesn't exist; fall through to file config
            Logger::warning('Mailer: could not load DB mail config — ' . $e->getMessage());
        }
        // Fall back to static file config
        return require BASE_PATH . '/config/mail.php';
    }

    /**
     * Reset cached config (useful after admin saves new mail settings)
     */
    public static function resetConfig(): void
    {
        self::$config = null;
    }
    
    /**
     * Send an email
     */
    public static function send(string $to, string $subject, string $body, array $options = []): bool
    {
        $config = self::getConfig();
        $driver = $config['driver'] ?? 'mail';
        
        switch ($driver) {
            case 'smtp':
                return self::sendViaSMTP($to, $subject, $body, $options);
            case 'sendmail':
                return self::sendViaSendmail($to, $subject, $body, $options);
            default:
                return self::sendViaMail($to, $subject, $body, $options);
        }
    }
    
    /**
     * Send email via SMTP
     */
    private static function sendViaSMTP(string $to, string $subject, string $body, array $options = []): bool
    {
        $config = self::getConfig();
        $smtp   = $config['smtp'];
        $from   = $options['from'] ?? $config['from'];

        $enc  = strtolower($smtp['encryption'] ?? 'tls');
        $host = $smtp['host'];
        $port = (int)($smtp['port'] ?? 587);

        try {
            // ── Connect ────────────────────────────────────────────────
            $transport = ($enc === 'ssl') ? "ssl://{$host}" : "tcp://{$host}";
            $errno = 0; $errstr = '';
            $socket = stream_socket_client(
                "{$transport}:{$port}",
                $errno, $errstr, 30,
                STREAM_CLIENT_CONNECT
            );

            if (!$socket) {
                Logger::error("SMTP connect failed to {$host}:{$port} — {$errstr} ({$errno})");
                return false;
            }

            stream_set_timeout($socket, 30);

            // ── Helpers ────────────────────────────────────────────────
            $read = function () use ($socket): string {
                $resp = '';
                while (($line = fgets($socket, 1024)) !== false) {
                    $resp .= $line;
                    if (strlen($line) >= 4 && $line[3] === ' ') {
                        break; // last line of (multi-line) response
                    }
                }
                return $resp;
            };

            $cmd = function (string $command) use ($socket, $read): string {
                fwrite($socket, $command . "\r\n");
                return $read();
            };

            $code = fn(string $r): int => (int)substr(trim($r), 0, 3);

            // ── Greeting ───────────────────────────────────────────────
            $resp = $read();
            if ($code($resp) !== 220) {
                Logger::error("SMTP bad greeting: " . trim($resp));
                fclose($socket);
                return false;
            }

            // ── EHLO ───────────────────────────────────────────────────
            $resp = $cmd('EHLO ' . (gethostname() ?: 'localhost'));
            if ($code($resp) !== 250) {
                $resp = $cmd('HELO ' . (gethostname() ?: 'localhost'));
            }

            // ── STARTTLS ───────────────────────────────────────────────
            if ($enc === 'tls') {
                $resp = $cmd('STARTTLS');
                if ($code($resp) !== 220) {
                    Logger::error("SMTP STARTTLS failed: " . trim($resp));
                    fclose($socket);
                    return false;
                }
                if (!stream_socket_enable_crypto($socket, true,
                        STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    Logger::error('SMTP TLS handshake failed');
                    fclose($socket);
                    return false;
                }
                // Re-issue EHLO after STARTTLS
                $cmd('EHLO ' . (gethostname() ?: 'localhost'));
            }

            // ── Auth ───────────────────────────────────────────────────
            if (!empty($smtp['username'])) {
                $resp = $cmd('AUTH LOGIN');
                if ($code($resp) !== 334) {
                    Logger::error('SMTP AUTH LOGIN rejected: ' . trim($resp));
                    fclose($socket);
                    return false;
                }
                $resp = $cmd(base64_encode($smtp['username']));
                if ($code($resp) !== 334) {
                    Logger::error('SMTP auth username rejected: ' . trim($resp));
                    fclose($socket);
                    return false;
                }
                $resp = $cmd(base64_encode($smtp['password'] ?? ''));
                if ($code($resp) !== 235) {
                    Logger::error('SMTP auth password rejected: ' . trim($resp));
                    fclose($socket);
                    return false;
                }
            }

            // ── Envelope ───────────────────────────────────────────────
            $resp = $cmd("MAIL FROM:<{$from['address']}>");
            if ($code($resp) !== 250) {
                Logger::error('SMTP MAIL FROM rejected: ' . trim($resp));
                fclose($socket); return false;
            }
            $resp = $cmd("RCPT TO:<{$to}>");
            if ($code($resp) !== 250 && $code($resp) !== 251) {
                Logger::error('SMTP RCPT TO rejected: ' . trim($resp));
                fclose($socket); return false;
            }

            // ── DATA ───────────────────────────────────────────────────
            $resp = $cmd('DATA');
            if ($code($resp) !== 354) {
                Logger::error('SMTP DATA rejected: ' . trim($resp));
                fclose($socket); return false;
            }

            $msgId = sprintf('<%s@%s>', md5(uniqid('', true)), (gethostname() ?: 'localhost'));
            $hdrs  = implode("\r\n", [
                "Message-ID: {$msgId}",
                "From: =?UTF-8?B?" . base64_encode($from['name']) . "?= <{$from['address']}>",
                "To: {$to}",
                "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "Content-Transfer-Encoding: base64",
                "Date: " . date('r'),
                "X-Mailer: MMB Mailer/1.0",
            ]);
            $encodedBody = chunk_split(base64_encode($body));
            $dataPayload = $hdrs . "\r\n\r\n" . $encodedBody . "\r\n.";

            $resp = $cmd($dataPayload);
            if ($code($resp) !== 250) {
                Logger::error('SMTP message rejected: ' . trim($resp));
                fclose($socket); return false;
            }

            // ── Quit ───────────────────────────────────────────────────
            fwrite($socket, "QUIT\r\n");
            fclose($socket);

            Logger::info("Email sent via SMTP to {$to}: {$subject}");
            return true;

        } catch (\Throwable $e) {
            Logger::error('SMTP exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email via sendmail
     */
    private static function sendViaSendmail(string $to, string $subject, string $body, array $options = []): bool
    {
        $config = self::getConfig();
        $from = $options['from'] ?? $config['from'];
        
        $headers = [
            "From: {$from['name']} <{$from['address']}>",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8"
        ];
        
        $result = mail($to, $subject, $body, implode("\r\n", $headers), "-f{$from['address']}");
        
        if ($result) {
            Logger::info("Email sent via sendmail to $to: $subject");
        } else {
            Logger::error("Failed to send email via sendmail to $to");
        }
        
        return $result;
    }
    
    /**
     * Send email via PHP mail()
     */
    private static function sendViaMail(string $to, string $subject, string $body, array $options = []): bool
    {
        $config = self::getConfig();
        $from = $options['from'] ?? $config['from'];
        
        $headers = [
            "From: {$from['name']} <{$from['address']}>",
            "Reply-To: {$from['address']}",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "X-Mailer: PHP Mailer"
        ];
        
        $result = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($result) {
            Logger::info("Email sent to $to: $subject");
        } else {
            Logger::error("Failed to send email to $to");
        }
        
        return $result;
    }
    
    /**
     * Send email using a template
     */
    public static function sendTemplate(string $to, string $template, array $data = []): bool
    {
        $config = self::getConfig();
        
        // Load template
        $templatePath = BASE_PATH . '/views/' . $config['templates'][$template] . '.php';
        
        if (!file_exists($templatePath)) {
            Logger::error("Email template not found: $template");
            return false;
        }
        
        // Render template
        extract($data);
        ob_start();
        include $templatePath;
        $body = ob_get_clean();
        
        // Get subject from template data or use default
        $subject = $data['subject'] ?? ucfirst(str_replace(['-', '_'], ' ', $template));
        
        return self::send($to, $subject, $body, $data);
    }
    
    /**
     * Send verification email
     */
    public static function sendVerificationEmail(string $to, string $name, string $token): bool
    {
        $verifyUrl = APP_URL . '/verify-email/' . $token;
        
        return self::sendTemplate($to, 'verify_email', [
            'subject' => 'Verify Your Email Address',
            'name' => $name,
            'verify_url' => $verifyUrl
        ]);
    }
    
    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail(string $to, string $name, string $token): bool
    {
        $resetUrl = APP_URL . '/reset-password/' . $token;
        
        return self::sendTemplate($to, 'password_reset', [
            'subject' => 'Reset Your Password',
            'name' => $name,
            'reset_url' => $resetUrl
        ]);
    }
    
    /**
     * Send welcome email (respects user email_notifications preference)
     */
    public static function sendWelcomeEmail(string $to, string $name): bool
    {
        return self::sendTemplate($to, 'welcome', [
            'subject' => 'Welcome to ' . APP_NAME,
            'name' => $name,
            'login_url' => APP_URL . '/login'
        ]);
    }

    /**
     * Check if a user wants to receive a given notification type.
     * Types: 'email_notifications' (general), 'security_alerts', 'product_updates'
     * Security alerts default to ON (1); general notifications default to ON (1).
     */
    public static function userWantsEmail(int $userId, string $type = 'email_notifications'): bool
    {
        try {
            $db = Database::getInstance();
            $row = $db->fetch("SELECT {$type} FROM user_profiles WHERE user_id = ? LIMIT 1", [$userId]);
            if ($row !== null && isset($row[$type])) {
                return (bool) $row[$type];
            }
        } catch (\Exception $e) {
            // If DB fails, default to sending
        }
        return true;
    }
}
