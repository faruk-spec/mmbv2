<?php

/**
 * IMAP Email Fetcher
 * 
 * Background worker that fetches incoming emails from IMAP server
 * and stores them in the database
 */

class IMAPFetcher
{
    private $db;
    private $config;
    private $running = true;
    
    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
        
        // Handle shutdown signals gracefully
        pcntl_signal(SIGTERM, [$this, 'handleShutdown']);
        pcntl_signal(SIGINT, [$this, 'handleShutdown']);
    }
    
    public function handleShutdown()
    {
        echo "\nReceived shutdown signal. Finishing current batch...\n";
        $this->running = false;
    }
    
    /**
     * Start the IMAP fetcher daemon
     */
    public function start()
    {
        echo "IMAP Fetcher started at " . date('Y-m-d H:i:s') . "\n";
        echo "Checking for new emails every 60 seconds...\n";
        
        while ($this->running) {
            pcntl_signal_dispatch();
            
            try {
                $this->fetchEmails();
            } catch (Exception $e) {
                $this->logError("IMAP Fetch Error: " . $e->getMessage());
            }
            
            // Check every minute
            if ($this->running) {
                sleep(60);
            }
        }
        
        echo "\nIMAP Fetcher stopped gracefully.\n";
    }
    
    /**
     * Fetch emails from all active mailboxes
     */
    private function fetchEmails()
    {
        // Get all active mailboxes
        $mailboxes = $this->db->query(
            "SELECT m.*, d.domain_name 
             FROM mail_mailboxes m
             JOIN mail_domains d ON m.domain_id = d.id
             WHERE m.is_active = 1 
             AND m.deleted_at IS NULL
             AND d.is_active = 1
             LIMIT 100"
        );
        
        $totalFetched = 0;
        
        foreach ($mailboxes as $mailbox) {
            $fetched = $this->fetchMailboxEmails($mailbox);
            $totalFetched += $fetched;
        }
        
        if ($totalFetched > 0) {
            echo "[" . date('H:i:s') . "] Fetched $totalFetched new emails\n";
        }
    }
    
    /**
     * Fetch emails for a specific mailbox
     */
    private function fetchMailboxEmails($mailbox)
    {
        $email = $mailbox['email'];
        $fetchedCount = 0;
        
        // Connect to IMAP server
        $imapHost = $this->config['imap']['host'] ?? 'localhost';
        $imapPort = $this->config['imap']['port'] ?? 993;
        $imapEncryption = $this->config['imap']['encryption'] ?? 'ssl';
        
        $imapString = "{{$imapHost}:{$imapPort}/imap/{$imapEncryption}}INBOX";
        
        // Use mailbox password for authentication
        $inbox = @imap_open($imapString, $email, $mailbox['password_hash']);
        
        if (!$inbox) {
            $this->logError("Failed to connect to IMAP for {$email}: " . imap_last_error());
            return 0;
        }
        
        // Get unseen emails
        $emails = imap_search($inbox, 'UNSEEN', SE_UID);
        
        if ($emails) {
            foreach ($emails as $uid) {
                try {
                    $this->processEmail($inbox, $uid, $mailbox);
                    $fetchedCount++;
                } catch (Exception $e) {
                    $this->logError("Error processing email UID $uid for {$email}: " . $e->getMessage());
                }
            }
        }
        
        imap_close($inbox);
        
        return $fetchedCount;
    }
    
    /**
     * Process a single email
     */
    private function processEmail($inbox, $uid, $mailbox)
    {
        $msgNo = imap_msgno($inbox, $uid);
        
        // Get email structure
        $structure = imap_fetchstructure($inbox, $msgNo);
        $header = imap_headerinfo($inbox, $msgNo);
        $overview = imap_fetch_overview($inbox, $msgNo);
        
        // Parse email using MIMEParser
        require_once __DIR__ . '/MIMEParser.php';
        $parser = new MIMEParser();
        $parsed = $parser->parse($inbox, $msgNo, $structure, $header);
        
        // Calculate spam score
        require_once __DIR__ . '/SpamFilter.php';
        $spamFilter = new SpamFilter($this->config);
        $spamScore = $spamFilter->calculateScore($parsed);
        
        // Determine folder (inbox or spam)
        $folderType = ($spamScore > 5.0) ? 'spam' : 'inbox';
        
        // Get folder ID
        $folder = $this->db->fetch(
            "SELECT id FROM mail_folders 
             WHERE mailbox_id = ? AND folder_type = ?",
            [$mailbox['id'], $folderType]
        );
        
        if (!$folder) {
            // Create folder if doesn't exist
            $this->db->query(
                "INSERT INTO mail_folders (mailbox_id, folder_name, folder_type, created_at)
                 VALUES (?, ?, ?, NOW())",
                [$mailbox['id'], ucfirst($folderType), $folderType]
            );
            $folderId = $this->db->lastInsertId();
        } else {
            $folderId = $folder['id'];
        }
        
        // Store message in database
        $messageId = $this->db->query(
            "INSERT INTO mail_messages (
                mailbox_id, folder_id, message_id_header, from_email, from_name,
                to_email, cc_email, bcc_email, subject, body_html, body_text,
                is_read, is_starred, has_attachments, size_bytes, spam_score,
                received_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, NOW())",
            [
                $mailbox['id'],
                $folderId,
                $parsed['message_id'] ?? '',
                $parsed['from']['email'] ?? '',
                $parsed['from']['name'] ?? '',
                $parsed['to'] ?? '',
                $parsed['cc'] ?? '',
                $parsed['bcc'] ?? '',
                $parsed['subject'] ?? '',
                $parsed['body_html'] ?? '',
                $parsed['body_text'] ?? '',
                !empty($parsed['attachments']) ? 1 : 0,
                $parsed['size'] ?? 0,
                $spamScore,
                date('Y-m-d H:i:s', $header->udate ?? time())
            ]
        );
        
        // Store attachments
        if (!empty($parsed['attachments'])) {
            foreach ($parsed['attachments'] as $attachment) {
                $this->storeAttachment($messageId, $attachment);
            }
        }
        
        // Check auto-responder rules
        $this->checkAutoResponder($mailbox, $parsed);
        
        // Check email filters
        $this->applyEmailFilters($mailbox, $messageId, $parsed);
        
        // Mark as seen on IMAP server
        imap_setflag_full($inbox, $uid, "\\Seen", ST_UID);
    }
    
    /**
     * Store attachment in database and filesystem
     */
    private function storeAttachment($messageId, $attachment)
    {
        $uploadDir = $this->config['storage']['attachments_path'] ?? '/var/mail/attachments';
        $filename = uniqid() . '_' . basename($attachment['filename']);
        $filepath = $uploadDir . '/' . $filename;
        
        // Create directory if doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Save file
        file_put_contents($filepath, $attachment['content']);
        
        // Store in database
        $this->db->query(
            "INSERT INTO mail_attachments (
                message_id, filename, original_filename, filepath,
                mime_type, size_bytes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                $messageId,
                $filename,
                $attachment['filename'],
                $filepath,
                $attachment['mime_type'],
                strlen($attachment['content'])
            ]
        );
    }
    
    /**
     * Check and send auto-responder if configured
     */
    private function checkAutoResponder($mailbox, $parsed)
    {
        // Get active auto-responder
        $autoResponder = $this->db->fetch(
            "SELECT * FROM mail_auto_responders 
             WHERE mailbox_id = ? 
             AND is_active = 1
             AND (start_date IS NULL OR start_date <= CURDATE())
             AND (end_date IS NULL OR end_date >= CURDATE())",
            [$mailbox['id']]
        );
        
        if ($autoResponder) {
            // Queue auto-response
            $this->db->query(
                "INSERT INTO mail_queue (
                    mailbox_id, from_email, to_email, subject, body_html,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())",
                [
                    $mailbox['id'],
                    $mailbox['email'],
                    $parsed['from']['email'],
                    'Re: ' . $parsed['subject'],
                    $autoResponder['message']
                ]
            );
        }
    }
    
    /**
     * Apply user-defined email filters
     */
    private function applyEmailFilters($mailbox, $messageId, $parsed)
    {
        // Get active filters for this mailbox
        $filters = $this->db->query(
            "SELECT * FROM mail_email_filters 
             WHERE mailbox_id = ? 
             AND is_active = 1
             ORDER BY priority ASC",
            [$mailbox['id']]
        );
        
        foreach ($filters as $filter) {
            $match = false;
            
            // Check filter criteria
            switch ($filter['field']) {
                case 'from':
                    $match = stripos($parsed['from']['email'], $filter['value']) !== false;
                    break;
                case 'subject':
                    $match = stripos($parsed['subject'], $filter['value']) !== false;
                    break;
                case 'to':
                    $match = stripos($parsed['to'], $filter['value']) !== false;
                    break;
            }
            
            if ($match) {
                // Apply action
                switch ($filter['action']) {
                    case 'move_to_folder':
                        $this->db->query(
                            "UPDATE mail_messages SET folder_id = ? WHERE id = ?",
                            [$filter['target_folder_id'], $messageId]
                        );
                        break;
                    case 'mark_as_read':
                        $this->db->query(
                            "UPDATE mail_messages SET is_read = 1 WHERE id = ?",
                            [$messageId]
                        );
                        break;
                    case 'star':
                        $this->db->query(
                            "UPDATE mail_messages SET is_starred = 1 WHERE id = ?",
                            [$messageId]
                        );
                        break;
                    case 'delete':
                        $this->db->query(
                            "UPDATE mail_messages SET deleted_at = NOW() WHERE id = ?",
                            [$messageId]
                        );
                        break;
                    case 'forward':
                        $this->forwardEmail($messageId, $filter['forward_to_email']);
                        break;
                }
                
                // Stop if filter says so
                if ($filter['stop_processing']) {
                    break;
                }
            }
        }
    }
    
    /**
     * Forward email to another address
     */
    private function forwardEmail($messageId, $forwardTo)
    {
        $message = $this->db->fetch(
            "SELECT * FROM mail_messages WHERE id = ?",
            [$messageId]
        );
        
        if ($message) {
            $this->db->query(
                "INSERT INTO mail_queue (
                    mailbox_id, from_email, to_email, subject, body_html,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())",
                [
                    $message['mailbox_id'],
                    $message['from_email'],
                    $forwardTo,
                    'Fwd: ' . $message['subject'],
                    $message['body_html']
                ]
            );
        }
    }
    
    /**
     * Log error to file and database
     */
    private function logError($message)
    {
        $logFile = $this->config['logs']['imap_fetcher'] ?? '/var/log/mail/imap-fetcher.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
        
        // Also log to database
        $this->db->query(
            "INSERT INTO mail_logs (log_type, message, created_at)
             VALUES ('imap_error', ?, NOW())",
            [$message]
        );
    }
}

// Run the fetcher if executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../../../config/database.php';
    
    $db = new Database();
    $config = include __DIR__ . '/../config.php';
    
    $fetcher = new IMAPFetcher($db, $config);
    $fetcher->start();
}
