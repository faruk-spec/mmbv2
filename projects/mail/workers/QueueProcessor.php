<?php

/**
 * Email Queue Processor
 * 
 * Background worker that processes queued emails for delivery
 * Handles rate limiting, retry logic, and delivery tracking
 * 
 * Usage: php /path/to/QueueProcessor.php
 * Or run as systemd service for automatic restart
 */

require_once __DIR__ . '/../../../bootstrap.php';

class QueueProcessor
{
    private $db;
    private $running = true;
    private $batchSize = 100;
    private $sleepTime = 5; // seconds between batches
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Handle graceful shutdown
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
    }
    
    public function run()
    {
        echo "[" . date('Y-m-d H:i:s') . "] Queue processor started\n";
        
        while ($this->running) {
            pcntl_signal_dispatch();
            
            try {
                $this->processBatch();
            } catch (\Exception $e) {
                echo "[ERROR] " . $e->getMessage() . "\n";
                sleep($this->sleepTime);
            }
            
            sleep($this->sleepTime);
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Queue processor stopped\n";
    }
    
    private function processBatch()
    {
        // Fetch pending emails
        $emails = $this->db->fetchAll(
            "SELECT q.*, m.email as from_email, s.id as subscriber_id
             FROM mail_queue q
             JOIN mail_mailboxes m ON q.mailbox_id = m.id
             JOIN mail_subscribers s ON m.subscriber_id = s.id
             WHERE q.status = 'pending' 
             AND q.attempts < 3
             AND (q.next_retry_at IS NULL OR q.next_retry_at <= NOW())
             ORDER BY q.priority DESC, q.created_at ASC
             LIMIT ?",
            [$this->batchSize]
        );
        
        if (empty($emails)) {
            return;
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Processing " . count($emails) . " emails\n";
        
        foreach ($emails as $email) {
            $this->processEmail($email);
        }
    }
    
    private function processEmail($email)
    {
        // Mark as processing
        $this->db->query(
            "UPDATE mail_queue SET status = 'processing', attempts = attempts + 1 
             WHERE id = ?",
            [$email['id']]
        );
        
        // Check daily send limit
        if (!$this->checkSendLimit($email['mailbox_id'])) {
            $this->markFailed($email['id'], 'Daily send limit exceeded');
            echo "[LIMIT] Email {$email['id']}: Daily limit exceeded\n";
            return;
        }
        
        // Send email via SMTP
        $sent = $this->sendViaSMTP($email);
        
        if ($sent) {
            // Mark as sent
            $this->db->query(
                "UPDATE mail_queue SET status = 'sent', sent_at = NOW() WHERE id = ?",
                [$email['id']]
            );
            
            // Copy to sent folder
            $this->copyToSentFolder($email);
            
            echo "[SUCCESS] Email {$email['id']}: Sent to {$email['to_email']}\n";
        } else {
            // Schedule retry with exponential backoff
            if ($email['attempts'] + 1 < 3) {
                $retryDelay = pow(2, $email['attempts']) * 5; // 5, 10, 20 minutes
                $this->db->query(
                    "UPDATE mail_queue SET status = 'pending', 
                     next_retry_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) 
                     WHERE id = ?",
                    [$retryDelay, $email['id']]
                );
                echo "[RETRY] Email {$email['id']}: Scheduled for retry in {$retryDelay} min\n";
            } else {
                $this->markFailed($email['id'], 'Max retry attempts reached');
                echo "[FAILED] Email {$email['id']}: Max retries exceeded\n";
            }
        }
    }
    
    private function sendViaSMTP($email)
    {
        try {
            // Get SMTP configuration
            $config = $this->getSmtpConfig();
            
            // Create PHP Mailer instance
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $email['from_email'];
            $mail->Password = $this->getMailboxPassword($email['mailbox_id']);
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port = $config['port'];
            
            // Email details
            $mail->setFrom($email['from_email'], $email['from_name'] ?? '');
            $mail->addAddress($email['to_email'], $email['to_name'] ?? '');
            
            // CC recipients
            if (!empty($email['cc_emails'])) {
                $ccList = json_decode($email['cc_emails'], true);
                foreach ($ccList as $cc) {
                    $mail->addCC($cc);
                }
            }
            
            // BCC recipients
            if (!empty($email['bcc_emails'])) {
                $bccList = json_decode($email['bcc_emails'], true);
                foreach ($bccList as $bcc) {
                    $mail->addBCC($bcc);
                }
            }
            
            // Reply-To
            if (!empty($email['reply_to'])) {
                $mail->addReplyTo($email['reply_to']);
            }
            
            $mail->Subject = $email['subject'];
            
            // Body
            if ($email['is_html']) {
                $mail->isHTML(true);
                $mail->Body = $email['body_html'];
                $mail->AltBody = $email['body_text'] ?? strip_tags($email['body_html']);
            } else {
                $mail->isHTML(false);
                $mail->Body = $email['body_text'];
            }
            
            // Attachments
            if (!empty($email['attachments'])) {
                $attachments = json_decode($email['attachments'], true);
                foreach ($attachments as $attachment) {
                    if (file_exists($attachment['path'])) {
                        $mail->addAttachment($attachment['path'], $attachment['name']);
                    }
                }
            }
            
            // Send
            $mail->send();
            return true;
            
        } catch (\Exception $e) {
            echo "[SMTP ERROR] " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function checkSendLimit($mailboxId)
    {
        // Get mailbox plan limit
        $limit = $this->db->fetch(
            "SELECT sp.daily_send_limit
             FROM mail_mailboxes m
             JOIN mail_subscribers s ON m.subscriber_id = s.id
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE m.id = ?",
            [$mailboxId]
        );
        
        if (!$limit) {
            return false;
        }
        
        // Count emails sent today
        $sent = $this->db->fetch(
            "SELECT COUNT(*) as count FROM mail_queue 
             WHERE mailbox_id = ? 
             AND status = 'sent' 
             AND DATE(sent_at) = CURDATE()",
            [$mailboxId]
        );
        
        return $sent['count'] < $limit['daily_send_limit'];
    }
    
    private function copyToSentFolder($email)
    {
        // Get sent folder ID
        $folder = $this->db->fetch(
            "SELECT id FROM mail_folders 
             WHERE mailbox_id = ? AND folder_type = 'sent'",
            [$email['mailbox_id']]
        );
        
        if ($folder) {
            // Insert into messages table
            $this->db->query(
                "INSERT INTO mail_messages (mailbox_id, folder_id, from_email, to_email, 
                                           subject, body_html, body_text, is_html, 
                                           message_size, sent_at, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [$email['mailbox_id'], $folder['id'], $email['from_email'], 
                 $email['to_email'], $email['subject'], $email['body_html'], 
                 $email['body_text'], $email['is_html'], 
                 strlen($email['body_html'] ?? $email['body_text'])]
            );
        }
    }
    
    private function markFailed($queueId, $reason)
    {
        $this->db->query(
            "UPDATE mail_queue SET status = 'failed', failed_at = NOW(), 
             error_message = ? WHERE id = ?",
            [$reason, $queueId]
        );
    }
    
    private function getSmtpConfig()
    {
        $settings = $this->db->fetch(
            "SELECT setting_value FROM mail_system_settings WHERE setting_key = 'smtp_config'"
        );
        
        if ($settings) {
            return json_decode($settings['setting_value'], true);
        }
        
        // Default SMTP config
        return [
            'host' => env('SMTP_HOST', 'localhost'),
            'port' => env('SMTP_PORT', 587),
            'encryption' => env('SMTP_ENCRYPTION', 'tls'),
        ];
    }
    
    private function getMailboxPassword($mailboxId)
    {
        $mailbox = $this->db->fetch(
            "SELECT password_hash FROM mail_mailboxes WHERE id = ?",
            [$mailboxId]
        );
        
        return $mailbox['password_hash'] ?? '';
    }
    
    public function shutdown()
    {
        echo "\n[SHUTDOWN] Received shutdown signal\n";
        $this->running = false;
    }
}

// Run the queue processor
$processor = new QueueProcessor();
$processor->run();
