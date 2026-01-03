<?php
/**
 * Webmail Controller
 * Handles email inbox, composer, and email management operations
 */

class WebmailController extends BaseController
{
    private $db;
    private $subscriberId;
    private $mailboxId;
    private $mailbox;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        
        // Get authenticated user's mailbox
        $userId = Auth::id();
        $this->mailbox = $this->db->fetch(
            "SELECT * FROM mail_mailboxes WHERE user_id = ? AND is_active = 1 LIMIT 1",
            [$userId]
        );
        
        if (!$this->mailbox) {
            $this->error('No active mailbox found');
            redirect('/projects/mail');
            exit;
        }
        
        $this->mailboxId = $this->mailbox['id'];
        $this->subscriberId = $this->mailbox['subscriber_id'];
    }

    /**
     * Display inbox view
     */
    public function inbox()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $folderId = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Get inbox folder if not specified
        if (!$folderId) {
            $folder = $this->db->fetch(
                "SELECT id FROM mail_folders WHERE mailbox_id = ? AND folder_type = 'inbox' LIMIT 1",
                [$this->mailboxId]
            );
            $folderId = $folder['id'];
        }

        // Build query
        $query = "SELECT m.*, 
                  ma.file_name as has_attachment,
                  COUNT(ma.id) as attachment_count
                  FROM mail_messages m
                  LEFT JOIN mail_attachments ma ON m.id = ma.message_id
                  WHERE m.mailbox_id = ? AND m.folder_id = ?";
        $params = [$this->mailboxId, $folderId];

        if ($search) {
            $query .= " AND (m.subject LIKE ? OR m.from_email LIKE ? OR m.body_text LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " GROUP BY m.id ORDER BY m.received_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $messages = $this->db->fetchAll($query, $params);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM mail_messages 
                       WHERE mailbox_id = ? AND folder_id = ?";
        $countParams = [$this->mailboxId, $folderId];
        if ($search) {
            $countQuery .= " AND (subject LIKE ? OR from_email LIKE ? OR body_text LIKE ?)";
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
        }
        $totalResult = $this->db->fetch($countQuery, $countParams);
        $total = $totalResult['total'];

        // Get all folders
        $folders = $this->db->fetchAll(
            "SELECT * FROM mail_folders WHERE mailbox_id = ? ORDER BY sort_order ASC",
            [$this->mailboxId]
        );

        // Get unread counts per folder
        $unreadCounts = [];
        foreach ($folders as $folder) {
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM mail_messages 
                 WHERE mailbox_id = ? AND folder_id = ? AND is_read = 0",
                [$this->mailboxId, $folder['id']]
            );
            $unreadCounts[$folder['id']] = $result['count'];
        }

        View::render('mail/webmail/inbox', [
            'messages' => $messages,
            'folders' => $folders,
            'unreadCounts' => $unreadCounts,
            'currentFolder' => $folderId,
            'mailbox' => $this->mailbox,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'search' => $search
        ]);
    }

    /**
     * View single email
     */
    public function viewEmail($messageId)
    {
        $message = $this->db->fetch(
            "SELECT * FROM mail_messages WHERE id = ? AND mailbox_id = ?",
            [$messageId, $this->mailboxId]
        );

        if (!$message) {
            $this->error('Email not found');
            redirect('/projects/mail/webmail');
            exit;
        }

        // Mark as read
        if (!$message['is_read']) {
            $this->db->query(
                "UPDATE mail_messages SET is_read = 1, read_at = NOW() WHERE id = ?",
                [$messageId]
            );
        }

        // Get attachments
        $attachments = $this->db->fetchAll(
            "SELECT * FROM mail_attachments WHERE message_id = ?",
            [$messageId]
        );

        View::render('mail/webmail/view', [
            'message' => $message,
            'attachments' => $attachments,
            'mailbox' => $this->mailbox
        ]);
    }

    /**
     * Display compose form
     */
    public function compose()
    {
        $replyTo = isset($_GET['reply']) ? (int)$_GET['reply'] : null;
        $forward = isset($_GET['forward']) ? (int)$_GET['forward'] : null;
        
        $originalMessage = null;
        if ($replyTo) {
            $originalMessage = $this->db->fetch(
                "SELECT * FROM mail_messages WHERE id = ? AND mailbox_id = ?",
                [$replyTo, $this->mailboxId]
            );
        } elseif ($forward) {
            $originalMessage = $this->db->fetch(
                "SELECT * FROM mail_messages WHERE id = ? AND mailbox_id = ?",
                [$forward, $this->mailboxId]
            );
        }

        // Get email signature
        $signature = $this->db->fetch(
            "SELECT signature FROM mail_mailboxes WHERE id = ?",
            [$this->mailboxId]
        );

        View::render('mail/webmail/compose', [
            'mailbox' => $this->mailbox,
            'originalMessage' => $originalMessage,
            'replyTo' => $replyTo,
            'forward' => $forward,
            'signature' => $signature ? $signature['signature'] : ''
        ]);
    }

    /**
     * Send email
     */
    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/projects/mail/webmail/compose');
            exit;
        }

        $to = trim($_POST['to']);
        $cc = isset($_POST['cc']) ? trim($_POST['cc']) : '';
        $bcc = isset($_POST['bcc']) ? trim($_POST['bcc']) : '';
        $subject = trim($_POST['subject']);
        $body = $_POST['body'];
        $replyToId = isset($_POST['reply_to_id']) ? (int)$_POST['reply_to_id'] : null;

        // Validation
        if (empty($to) || empty($subject)) {
            $this->error('To and Subject fields are required');
            redirect('/projects/mail/webmail/compose');
            exit;
        }

        // Check daily send limit
        $today = date('Y-m-d');
        $sentToday = $this->db->fetch(
            "SELECT COUNT(*) as count FROM mail_messages 
             WHERE mailbox_id = ? AND DATE(sent_at) = ? AND message_type = 'sent'",
            [$this->mailboxId, $today]
        );

        // Get subscriber's plan limits
        $plan = $this->db->fetch(
            "SELECT sp.daily_send_limit 
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );

        if ($sentToday['count'] >= $plan['daily_send_limit']) {
            $this->error('Daily send limit reached. Please upgrade your plan.');
            redirect('/projects/mail/webmail/compose');
            exit;
        }

        // Insert into mail_queue for processing
        $queueId = $this->db->insert('mail_queue', [
            'mailbox_id' => $this->mailboxId,
            'from_email' => $this->mailbox['email'],
            'from_name' => $this->mailbox['display_name'],
            'to_email' => $to,
            'cc_email' => $cc,
            'bcc_email' => $bcc,
            'subject' => $subject,
            'body_html' => $body,
            'body_text' => strip_tags($body),
            'reply_to_message_id' => $replyToId,
            'status' => 'pending',
            'priority' => 'normal',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Handle attachments
        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            $this->handleAttachmentUploads($queueId, $_FILES['attachments']);
        }

        // Also save to sent folder
        $sentFolder = $this->db->fetch(
            "SELECT id FROM mail_folders WHERE mailbox_id = ? AND folder_type = 'sent' LIMIT 1",
            [$this->mailboxId]
        );

        $this->db->insert('mail_messages', [
            'mailbox_id' => $this->mailboxId,
            'folder_id' => $sentFolder['id'],
            'message_type' => 'sent',
            'from_email' => $this->mailbox['email'],
            'from_name' => $this->mailbox['display_name'],
            'to_email' => $to,
            'cc_email' => $cc,
            'bcc_email' => $bcc,
            'subject' => $subject,
            'body_html' => $body,
            'body_text' => strip_tags($body),
            'is_read' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'received_at' => date('Y-m-d H:i:s')
        ]);

        $this->success('Email queued for sending');
        redirect('/projects/mail/webmail');
    }

    /**
     * Handle attachment uploads
     */
    private function handleAttachmentUploads($queueId, $files)
    {
        $uploadDir = __DIR__ . '/../../storage/mail/attachments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $filename = basename($files['name'][$i]);
                $tmpName = $files['tmp_name'][$i];
                $fileSize = $files['size'][$i];
                $mimeType = $files['type'][$i];
                
                // Generate unique filename
                $uniqueName = uniqid() . '_' . $filename;
                $destination = $uploadDir . $uniqueName;
                
                if (move_uploaded_file($tmpName, $destination)) {
                    $this->db->insert('mail_attachments', [
                        'queue_id' => $queueId,
                        'file_name' => $filename,
                        'file_path' => $destination,
                        'file_size' => $fileSize,
                        'mime_type' => $mimeType,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    /**
     * Move email to folder
     */
    public function moveToFolder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $messageId = (int)$_POST['message_id'];
        $folderId = (int)$_POST['folder_id'];

        $this->db->query(
            "UPDATE mail_messages SET folder_id = ? WHERE id = ? AND mailbox_id = ?",
            [$folderId, $messageId, $this->mailboxId]
        );

        echo json_encode(['success' => true]);
    }

    /**
     * Mark email as read/unread
     */
    public function toggleRead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $messageId = (int)$_POST['message_id'];
        $isRead = (int)$_POST['is_read'];

        $this->db->query(
            "UPDATE mail_messages SET is_read = ?, read_at = ? WHERE id = ? AND mailbox_id = ?",
            [$isRead, $isRead ? date('Y-m-d H:i:s') : null, $messageId, $this->mailboxId]
        );

        echo json_encode(['success' => true]);
    }

    /**
     * Star/unstar email
     */
    public function toggleStar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $messageId = (int)$_POST['message_id'];
        $isStarred = (int)$_POST['is_starred'];

        $this->db->query(
            "UPDATE mail_messages SET is_starred = ? WHERE id = ? AND mailbox_id = ?",
            [$isStarred, $messageId, $this->mailboxId]
        );

        echo json_encode(['success' => true]);
    }

    /**
     * Delete email
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $messageId = (int)$_POST['message_id'];

        // Move to trash folder
        $trashFolder = $this->db->fetch(
            "SELECT id FROM mail_folders WHERE mailbox_id = ? AND folder_type = 'trash' LIMIT 1",
            [$this->mailboxId]
        );

        $this->db->query(
            "UPDATE mail_messages SET folder_id = ?, deleted_at = NOW() WHERE id = ? AND mailbox_id = ?",
            [$trashFolder['id'], $messageId, $this->mailboxId]
        );

        echo json_encode(['success' => true]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = $_POST['action'];
        $messageIds = isset($_POST['message_ids']) ? explode(',', $_POST['message_ids']) : [];

        if (empty($messageIds)) {
            echo json_encode(['success' => false, 'message' => 'No messages selected']);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));

        switch ($action) {
            case 'mark_read':
                $this->db->query(
                    "UPDATE mail_messages SET is_read = 1, read_at = NOW() 
                     WHERE id IN ($placeholders) AND mailbox_id = ?",
                    array_merge($messageIds, [$this->mailboxId])
                );
                break;

            case 'mark_unread':
                $this->db->query(
                    "UPDATE mail_messages SET is_read = 0, read_at = NULL 
                     WHERE id IN ($placeholders) AND mailbox_id = ?",
                    array_merge($messageIds, [$this->mailboxId])
                );
                break;

            case 'delete':
                $trashFolder = $this->db->fetch(
                    "SELECT id FROM mail_folders WHERE mailbox_id = ? AND folder_type = 'trash' LIMIT 1",
                    [$this->mailboxId]
                );
                $this->db->query(
                    "UPDATE mail_messages SET folder_id = ?, deleted_at = NOW() 
                     WHERE id IN ($placeholders) AND mailbox_id = ?",
                    array_merge([$trashFolder['id']], $messageIds, [$this->mailboxId])
                );
                break;

            case 'move':
                $folderId = (int)$_POST['folder_id'];
                $this->db->query(
                    "UPDATE mail_messages SET folder_id = ? 
                     WHERE id IN ($placeholders) AND mailbox_id = ?",
                    array_merge([$folderId], $messageIds, [$this->mailboxId])
                );
                break;
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($attachmentId)
    {
        $attachment = $this->db->fetch(
            "SELECT a.*, m.mailbox_id FROM mail_attachments a
             JOIN mail_messages m ON a.message_id = m.id
             WHERE a.id = ? AND m.mailbox_id = ?",
            [$attachmentId, $this->mailboxId]
        );

        if (!$attachment || !file_exists($attachment['file_path'])) {
            $this->error('Attachment not found');
            return;
        }

        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Disposition: attachment; filename="' . $attachment['file_name'] . '"');
        header('Content-Length: ' . $attachment['file_size']);
        readfile($attachment['file_path']);
        exit;
    }
}
