<?php
/**
 * Webmail Inbox View
 * 3-column layout: Folders | Message List | Message Preview
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - <?php echo View::e($mailbox['email']); ?></title>
    <link rel="stylesheet" href="/assets/adminlte/css/adminlte.min.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <style>
        .mail-wrapper {
            display: flex;
            height: calc(100vh - 56px);
            overflow: hidden;
        }
        .mail-sidebar {
            width: 250px;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            background: #f8f9fa;
        }
        .mail-list {
            width: 400px;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            background: #fff;
        }
        .mail-content {
            flex: 1;
            overflow-y: auto;
            background: #fff;
            padding: 20px;
        }
        .folder-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
            border-left: 3px solid transparent;
        }
        .folder-item:hover {
            background: #e9ecef;
        }
        .folder-item.active {
            background: #e3f2fd;
            border-left-color: #007bff;
            font-weight: 600;
        }
        .folder-item .badge {
            float: right;
        }
        .message-item {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: background 0.2s;
        }
        .message-item:hover {
            background: #f8f9fa;
        }
        .message-item.unread {
            background: #f0f8ff;
            font-weight: 600;
        }
        .message-item.selected {
            background: #e3f2fd;
        }
        .message-from {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .message-subject {
            font-size: 13px;
            margin-bottom: 5px;
        }
        .message-snippet {
            font-size: 12px;
            color: #6c757d;
        }
        .message-time {
            font-size: 11px;
            color: #6c757d;
            float: right;
        }
        .message-checkbox {
            margin-right: 10px;
        }
        .compose-btn {
            margin: 15px;
            width: calc(100% - 30px);
        }
        .toolbar {
            padding: 10px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .search-box {
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        @media (max-width: 992px) {
            .mail-sidebar {
                display: none;
            }
            .mail-list {
                width: 100%;
            }
            .mail-content {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="/projects/mail/webmail">
                <i class="fas fa-envelope"></i> Webmail
            </a>
            <div class="d-flex align-items-center">
                <span class="me-3"><?php echo View::e($mailbox['email']); ?></span>
                <a href="/projects/mail/subscriber/dashboard" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>
    </nav>

    <div class="mail-wrapper">
        <!-- Sidebar - Folders -->
        <div class="mail-sidebar">
            <button class="btn btn-primary compose-btn" onclick="window.location.href='/projects/mail/webmail/compose'">
                <i class="fas fa-pen"></i> Compose
            </button>

            <div class="folders-list">
                <?php foreach ($folders as $folder): ?>
                    <div class="folder-item <?php echo $folder['id'] == $currentFolder ? 'active' : ''; ?>" 
                         onclick="window.location.href='/projects/mail/webmail?folder=<?php echo $folder['id']; ?>'">
                        <i class="fas fa-<?php echo getFolderIcon($folder['folder_type']); ?>"></i>
                        <?php echo View::e(ucfirst($folder['folder_name'])); ?>
                        <?php if (isset($unreadCounts[$folder['id']]) && $unreadCounts[$folder['id']] > 0): ?>
                            <span class="badge bg-primary"><?php echo $unreadCounts[$folder['id']]; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Message List -->
        <div class="mail-list">
            <!-- Search Box -->
            <div class="search-box">
                <form method="GET" action="/projects/mail/webmail">
                    <input type="hidden" name="folder" value="<?php echo $currentFolder; ?>">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search emails..." 
                               value="<?php echo View::e($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary" onclick="selectAll()">
                        <i class="fas fa-check-square"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="bulkAction('mark_read')">
                        <i class="fas fa-envelope-open"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="bulkAction('delete')">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="refresh()">
                        <i class="fas fa-sync"></i>
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div class="messages-list">
                <?php if (empty($messages)): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No messages in this folder</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-item <?php echo !$message['is_read'] ? 'unread' : ''; ?>" 
                             onclick="viewMessage(<?php echo $message['id']; ?>)"
                             data-id="<?php echo $message['id']; ?>">
                            <input type="checkbox" class="message-checkbox" onclick="event.stopPropagation()" 
                                   value="<?php echo $message['id']; ?>">
                            
                            <?php if ($message['is_starred']): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php endif; ?>
                            
                            <?php if ($message['attachment_count'] > 0): ?>
                                <i class="fas fa-paperclip"></i>
                            <?php endif; ?>

                            <span class="message-time"><?php echo formatTime($message['received_at']); ?></span>
                            
                            <div class="message-from">
                                <?php echo View::e($message['from_name'] ?: $message['from_email']); ?>
                            </div>
                            <div class="message-subject">
                                <?php echo View::e($message['subject']); ?>
                            </div>
                            <div class="message-snippet">
                                <?php echo View::e(substr(strip_tags($message['body_text']), 0, 100)); ?>...
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total > $perPage): ?>
                <div class="p-3 border-top">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <?php
                            $totalPages = ceil($total / $perPage);
                            for ($i = 1; $i <= $totalPages; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?folder=<?php echo $currentFolder; ?>&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

        <!-- Message Content (Preview) -->
        <div class="mail-content">
            <div class="text-center text-muted p-5">
                <i class="fas fa-envelope-open-text fa-4x mb-3"></i>
                <p>Select a message to read</p>
            </div>
        </div>
    </div>

    <script src="/assets/jquery/jquery.min.js"></script>
    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewMessage(id) {
            window.location.href = '/projects/mail/webmail/view/' + id;
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        }

        function bulkAction(action) {
            const checkboxes = document.querySelectorAll('.message-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);
            
            if (ids.length === 0) {
                alert('Please select messages first');
                return;
            }

            $.post('/projects/mail/webmail/bulk-action', {
                action: action,
                message_ids: ids.join(',')
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }

        function refresh() {
            location.reload();
        }
    </script>
</body>
</html>

<?php
// Helper functions
function getFolderIcon($type) {
    $icons = [
        'inbox' => 'inbox',
        'sent' => 'paper-plane',
        'drafts' => 'file-alt',
        'trash' => 'trash',
        'spam' => 'exclamation-triangle',
        'archive' => 'archive',
        'custom' => 'folder'
    ];
    return $icons[$type] ?? 'folder';
}

function formatTime($datetime) {
    $time = strtotime($datetime);
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');
    
    if ($time >= $today) {
        return date('g:i A', $time);
    } elseif ($time >= $yesterday) {
        return 'Yesterday';
    } elseif ($time >= strtotime('-7 days')) {
        return date('l', $time);
    } else {
        return date('M d', $time);
    }
}
?>
