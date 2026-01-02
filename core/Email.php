<?php
/**
 * Email Service
 * 
 * Handles email sending with SMTP configuration and queue management
 * Part of Phase 9: Email & Notification System
 * 
 * @package MMB\Core
 */

namespace Core;

class Email
{
    private static $config = null;
    private static $queueEnabled = true;
    
    /**
     * Initialize email configuration
     */
    private static function init(): void
    {
        if (self::$config === null) {
            $configFile = BASE_PATH . '/config/mail.php';
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            } else {
                // Default configuration
                self::$config = [
                    'driver' => 'smtp',
                    'host' => 'localhost',
                    'port' => 587,
                    'username' => '',
                    'password' => '',
                    'encryption' => 'tls',
                    'from' => [
                        'address' => 'noreply@example.com',
                        'name' => APP_NAME ?? 'MMB Platform'
                    ]
                ];
            }
        }
    }
    
    /**
     * Send email
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param array $options Additional options (cc, bcc, attachments, etc.)
     * @return bool Success status
     */
    public static function send($to, string $subject, string $body, array $options = []): bool
    {
        self::init();
        
        // Queue email if enabled
        if (self::$queueEnabled) {
            return self::queue($to, $subject, $body, $options);
        }
        
        // Send immediately
        return self::sendNow($to, $subject, $body, $options);
    }
    
    /**
     * Send email immediately
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $options Additional options
     * @return bool Success status
     */
    private static function sendNow($to, string $subject, string $body, array $options = []): bool
    {
        self::init();
        
        try {
            // Prepare recipients
            $recipients = is_array($to) ? $to : [$to];
            
            // Build headers
            $headers = self::buildHeaders($options);
            
            // Send to each recipient
            foreach ($recipients as $recipient) {
                if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    Logger::error("Invalid email address: {$recipient}");
                    continue;
                }
                
                // Use PHP mail() function
                // In production, use PHPMailer or similar library
                $sent = mail($recipient, $subject, $body, $headers);
                
                if ($sent) {
                    Logger::info("Email sent to {$recipient}: {$subject}");
                } else {
                    Logger::error("Failed to send email to {$recipient}: {$subject}");
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Email send error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Queue email for later sending
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $options Additional options
     * @return bool Success status
     */
    private static function queue($to, string $subject, string $body, array $options = []): bool
    {
        $recipients = is_array($to) ? $to : [$to];
        
        $queueKey = 'email_queue';
        $queue = Cache::get($queueKey, []);
        
        foreach ($recipients as $recipient) {
            $queue[] = [
                'to' => $recipient,
                'subject' => $subject,
                'body' => $body,
                'options' => $options,
                'queued_at' => date('Y-m-d H:i:s'),
                'attempts' => 0
            ];
        }
        
        Cache::set($queueKey, $queue, 0); // No expiration for queue
        
        Logger::info("Email queued for " . count($recipients) . " recipient(s): {$subject}");
        
        return true;
    }
    
    /**
     * Process email queue
     * 
     * @param int $limit Maximum number of emails to process
     * @return int Number of emails processed
     */
    public static function processQueue(int $limit = 10): int
    {
        $queueKey = 'email_queue';
        $queue = Cache::get($queueKey, []);
        
        if (empty($queue)) {
            return 0;
        }
        
        $processed = 0;
        $remaining = [];
        
        foreach ($queue as $email) {
            if ($processed >= $limit) {
                $remaining[] = $email;
                continue;
            }
            
            $success = self::sendNow(
                $email['to'],
                $email['subject'],
                $email['body'],
                $email['options'] ?? []
            );
            
            if ($success) {
                $processed++;
            } else {
                // Retry logic
                $email['attempts'] = ($email['attempts'] ?? 0) + 1;
                if ($email['attempts'] < 3) {
                    $remaining[] = $email;
                } else {
                    Logger::error("Email failed after 3 attempts: " . $email['to'] . " - " . $email['subject']);
                }
            }
        }
        
        // Update queue with remaining emails
        Cache::set($queueKey, $remaining, 0);
        
        return $processed;
    }
    
    /**
     * Build email headers
     * 
     * @param array $options Email options
     * @return string Headers string
     */
    private static function buildHeaders(array $options = []): string
    {
        self::init();
        
        $headers = [];
        
        // From header
        $fromAddress = $options['from_address'] ?? self::$config['from']['address'];
        $fromName = $options['from_name'] ?? self::$config['from']['name'];
        $headers[] = "From: {$fromName} <{$fromAddress}>";
        
        // Reply-To header
        if (isset($options['reply_to'])) {
            $headers[] = "Reply-To: {$options['reply_to']}";
        }
        
        // CC header
        if (isset($options['cc'])) {
            $cc = is_array($options['cc']) ? implode(', ', $options['cc']) : $options['cc'];
            $headers[] = "Cc: {$cc}";
        }
        
        // BCC header
        if (isset($options['bcc'])) {
            $bcc = is_array($options['bcc']) ? implode(', ', $options['bcc']) : $options['bcc'];
            $headers[] = "Bcc: {$bcc}";
        }
        
        // Content type
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        
        return implode("\r\n", $headers);
    }
    
    /**
     * Send email from template
     * 
     * @param string|array $to Recipient email(s)
     * @param string $template Template name
     * @param array $data Template data
     * @param string $subject Email subject
     * @return bool Success status
     */
    public static function sendTemplate($to, string $template, array $data = [], string $subject = ''): bool
    {
        $body = self::renderTemplate($template, $data);
        
        if (empty($subject) && isset($data['subject'])) {
            $subject = $data['subject'];
        }
        
        return self::send($to, $subject, $body);
    }
    
    /**
     * Render email template
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string Rendered HTML
     */
    private static function renderTemplate(string $template, array $data = []): string
    {
        $templateFile = BASE_PATH . "/views/emails/{$template}.php";
        
        if (!file_exists($templateFile)) {
            Logger::error("Email template not found: {$template}");
            return '';
        }
        
        // Extract data for template
        extract($data);
        
        // Start output buffering
        ob_start();
        require $templateFile;
        $content = ob_get_clean();
        
        // Wrap in email layout
        return self::wrapInLayout($content, $data);
    }
    
    /**
     * Wrap content in email layout
     * 
     * @param string $content Email content
     * @param array $data Template data
     * @return string Complete HTML email
     */
    private static function wrapInLayout(string $content, array $data = []): string
    {
        $layoutFile = BASE_PATH . '/views/emails/layout.php';
        
        if (file_exists($layoutFile)) {
            extract($data);
            ob_start();
            require $layoutFile;
            return ob_get_clean();
        }
        
        // Default layout
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$data['app_name'] ?? 'MMB Platform'}</h1>
        </div>
        <div class="content">
            {$content}
        </div>
        <div class="footer">
            <p>&copy; " . date('Y') . " {$data['app_name'] ?? 'MMB Platform'}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Enable/disable email queue
     * 
     * @param bool $enabled Queue enabled
     */
    public static function setQueueEnabled(bool $enabled): void
    {
        self::$queueEnabled = $enabled;
    }
    
    /**
     * Get queue statistics
     * 
     * @return array Queue stats
     */
    public static function getQueueStats(): array
    {
        $queue = Cache::get('email_queue', []);
        
        return [
            'total' => count($queue),
            'pending' => count(array_filter($queue, fn($e) => ($e['attempts'] ?? 0) === 0)),
            'retrying' => count(array_filter($queue, fn($e) => ($e['attempts'] ?? 0) > 0))
        ];
    }
}
