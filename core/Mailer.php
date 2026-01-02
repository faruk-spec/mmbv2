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
     * Get mail configuration
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = require BASE_PATH . '/config/mail.php';
        }
        return self::$config;
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
        $smtp = $config['smtp'];
        $from = $options['from'] ?? $config['from'];
        
        try {
            // Create socket connection
            $socket = fsockopen(
                ($smtp['encryption'] === 'ssl' ? 'ssl://' : '') . $smtp['host'],
                $smtp['port'],
                $errno,
                $errstr,
                30
            );
            
            if (!$socket) {
                Logger::error("SMTP connection failed: $errstr ($errno)");
                return false;
            }
            
            // Read server greeting
            self::smtpRead($socket);
            
            // Send EHLO
            self::smtpCommand($socket, "EHLO " . gethostname());
            
            // Start TLS if required
            if ($smtp['encryption'] === 'tls') {
                self::smtpCommand($socket, "STARTTLS");
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                self::smtpCommand($socket, "EHLO " . gethostname());
            }
            
            // Authenticate
            if (!empty($smtp['username'])) {
                self::smtpCommand($socket, "AUTH LOGIN");
                self::smtpCommand($socket, base64_encode($smtp['username']));
                self::smtpCommand($socket, base64_encode($smtp['password']));
            }
            
            // Set sender and recipient
            self::smtpCommand($socket, "MAIL FROM:<{$from['address']}>");
            self::smtpCommand($socket, "RCPT TO:<$to>");
            
            // Send data
            self::smtpCommand($socket, "DATA");
            
            // Build message
            $headers = [
                "From: {$from['name']} <{$from['address']}>",
                "To: $to",
                "Subject: $subject",
                "MIME-Version: 1.0",
                "Content-Type: text/html; charset=UTF-8",
                "Date: " . date('r')
            ];
            
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
            self::smtpCommand($socket, $message);
            
            // Quit
            self::smtpCommand($socket, "QUIT");
            fclose($socket);
            
            Logger::info("Email sent to $to: $subject");
            return true;
            
        } catch (\Exception $e) {
            Logger::error("SMTP error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send SMTP command
     */
    private static function smtpCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return self::smtpRead($socket);
    }
    
    /**
     * Read SMTP response
     */
    private static function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
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
            "X-Mailer: PHP/" . phpversion()
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
     * Send welcome email
     */
    public static function sendWelcomeEmail(string $to, string $name): bool
    {
        return self::sendTemplate($to, 'welcome', [
            'subject' => 'Welcome to ' . APP_NAME,
            'name' => $name,
            'login_url' => APP_URL . '/login'
        ]);
    }
}
