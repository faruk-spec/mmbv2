<?php
/**
 * Email View
 * Display single email with actions
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo View::e($message['subject']); ?> - Webmail</title>
    <link rel="stylesheet" href="/assets/adminlte/css/adminlte.min.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .email-wrapper {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .email-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .email-body {
            padding: 30px;
        }
        .email-subject {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .email-meta {
            font-size: 14px;
            color: #6c757d;
        }
        .email-from {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .email-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #007bff;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
        }
        .email-content {
            line-height: 1.6;
            font-size: 14px;
        }
        .email-content img {
            max-width: 100%;
            height: auto;
        }
        .attachments-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .attachment-item {
            display: inline-block;
            padding: 10px 15px;
            margin: 5px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }
        .attachment-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="/projects/mail/webmail">
                <i class="fas fa-arrow-left"></i> Back to Inbox
            </a>
            <span><?php echo View::e($mailbox['email']); ?></span>
        </div>
    </nav>

    <div class="email-wrapper">
        <!-- Actions Bar -->
        <div class="email-actions">
            <div class="btn-group" role="group">
                <a href="/projects/mail/webmail/compose?reply=<?php echo $message['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-reply"></i> Reply
                </a>
                <a href="/projects/mail/webmail/compose?forward=<?php echo $message['id']; ?>" class="btn btn-sm btn-secondary">
                    <i class="fas fa-forward"></i> Forward
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleStar()">
                    <i class="fas fa-star <?php echo $message['is_starred'] ? 'text-warning' : ''; ?>"></i>
                    <?php echo $message['is_starred'] ? 'Unstar' : 'Star'; ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEmail()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>

            <div class="btn-group float-end" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="markUnread()">
                    <i class="fas fa-envelope"></i> Mark Unread
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <!-- Email Header -->
        <div class="email-header">
            <div class="email-subject">
                <?php echo View::e($message['subject']); ?>
            </div>

            <div class="email-from">
                <div class="email-avatar">
                    <?php echo strtoupper(substr($message['from_name'] ?: $message['from_email'], 0, 1)); ?>
                </div>
                <div>
                    <strong><?php echo View::e($message['from_name'] ?: $message['from_email']); ?></strong>
                    <br>
                    <small class="text-muted">&lt;<?php echo View::e($message['from_email']); ?>&gt;</small>
                </div>
            </div>

            <div class="email-meta">
                <i class="fas fa-clock"></i>
                <?php echo date('F d, Y \a\t g:i A', strtotime($message['received_at'])); ?>
                
                <?php if ($message['to_email']): ?>
                    <span class="ms-3">
                        <i class="fas fa-user"></i> To: <?php echo View::e($message['to_email']); ?>
                    </span>
                <?php endif; ?>

                <?php if ($message['cc_email']): ?>
                    <span class="ms-3">
                        <i class="fas fa-users"></i> Cc: <?php echo View::e($message['cc_email']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Email Body -->
        <div class="email-body">
            <div class="email-content">
                <?php echo $message['body_html'] ?: nl2br(View::e($message['body_text'])); ?>
            </div>

            <!-- Attachments -->
            <?php if (!empty($attachments)): ?>
                <div class="attachments-section">
                    <h6><i class="fas fa-paperclip"></i> Attachments (<?php echo count($attachments); ?>)</h6>
                    <div class="mt-3">
                        <?php foreach ($attachments as $attachment): ?>
                            <a href="/projects/mail/webmail/attachment/<?php echo $attachment['id']; ?>" 
                               class="attachment-item" download>
                                <i class="fas fa-file"></i>
                                <?php echo View::e($attachment['file_name']); ?>
                                <span class="text-muted">(<?php echo formatFileSize($attachment['file_size']); ?>)</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/assets/jquery/jquery.min.js"></script>
    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleStar() {
            $.post('/projects/mail/webmail/toggle-star', {
                message_id: <?php echo $message['id']; ?>,
                is_starred: <?php echo $message['is_starred'] ? 0 : 1; ?>
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }

        function deleteEmail() {
            if (confirm('Move this email to trash?')) {
                $.post('/projects/mail/webmail/delete', {
                    message_id: <?php echo $message['id']; ?>
                }, function(response) {
                    if (response.success) {
                        window.location.href = '/projects/mail/webmail';
                    }
                });
            }
        }

        function markUnread() {
            $.post('/projects/mail/webmail/toggle-read', {
                message_id: <?php echo $message['id']; ?>,
                is_read: 0
            }, function(response) {
                if (response.success) {
                    window.location.href = '/projects/mail/webmail';
                }
            });
        }
    </script>
</body>
</html>

<?php
function formatFileSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>
