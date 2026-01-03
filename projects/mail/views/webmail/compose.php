<?php
/**
 * Email Composer View
 * Rich text editor for composing emails
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Email - <?php echo View::e($mailbox['email']); ?></title>
    <link rel="stylesheet" href="/assets/adminlte/css/adminlte.min.css">
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.mce.com/1/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .compose-wrapper {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .compose-header {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .compose-body {
            padding: 20px;
        }
        .compose-footer {
            padding: 20px;
            border-top: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }
        .form-control {
            border-radius: 4px;
        }
        .cc-bcc-toggle {
            cursor: pointer;
            color: #007bff;
            font-size: 14px;
        }
        .cc-bcc-toggle:hover {
            text-decoration: underline;
        }
        .attachment-list {
            margin-top: 10px;
        }
        .attachment-item {
            display: inline-block;
            padding: 5px 10px;
            margin: 5px;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 14px;
        }
        .attachment-item .remove {
            margin-left: 10px;
            cursor: pointer;
            color: #dc3545;
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

    <div class="compose-wrapper">
        <div class="compose-header">
            <h4>
                <?php if ($replyTo): ?>
                    <i class="fas fa-reply"></i> Reply
                <?php elseif ($forward): ?>
                    <i class="fas fa-forward"></i> Forward
                <?php else: ?>
                    <i class="fas fa-pen"></i> New Message
                <?php endif; ?>
            </h4>
        </div>

        <form method="POST" action="/projects/mail/webmail/send" enctype="multipart/form-data" id="composeForm">
            <div class="compose-body">
                <!-- To Field -->
                <div class="mb-3">
                    <label class="form-label">To:</label>
                    <input type="email" class="form-control" name="to" id="to" required
                           value="<?php echo $replyTo && $originalMessage ? View::e($originalMessage['from_email']) : ''; ?>"
                           multiple>
                    <small class="form-text text-muted">
                        Separate multiple email addresses with commas
                        <span class="cc-bcc-toggle float-end" onclick="toggleCcBcc()">Cc/Bcc</span>
                    </small>
                </div>

                <!-- CC Field (Hidden by default) -->
                <div class="mb-3" id="ccField" style="display: none;">
                    <label class="form-label">Cc:</label>
                    <input type="email" class="form-control" name="cc" id="cc" multiple>
                </div>

                <!-- BCC Field (Hidden by default) -->
                <div class="mb-3" id="bccField" style="display: none;">
                    <label class="form-label">Bcc:</label>
                    <input type="email" class="form-control" name="bcc" id="bcc" multiple>
                </div>

                <!-- Subject Field -->
                <div class="mb-3">
                    <label class="form-label">Subject:</label>
                    <input type="text" class="form-control" name="subject" id="subject" required
                           value="<?php 
                           if ($replyTo && $originalMessage) {
                               echo 'Re: ' . View::e($originalMessage['subject']);
                           } elseif ($forward && $originalMessage) {
                               echo 'Fwd: ' . View::e($originalMessage['subject']);
                           }
                           ?>">
                </div>

                <!-- Body Field -->
                <div class="mb-3">
                    <label class="form-label">Message:</label>
                    <textarea name="body" id="body" class="form-control" rows="10">
                        <?php if ($signature): ?>
                            <br><br>--<br><?php echo $signature; ?>
                        <?php endif; ?>
                        
                        <?php if ($replyTo && $originalMessage): ?>
                            <br><br>
                            <blockquote style="border-left: 2px solid #ccc; padding-left: 10px; color: #666;">
                                <p><strong>On <?php echo date('M d, Y \a\t g:i A', strtotime($originalMessage['received_at'])); ?>, 
                                   <?php echo View::e($originalMessage['from_name'] ?: $originalMessage['from_email']); ?> wrote:</strong></p>
                                <?php echo $originalMessage['body_html']; ?>
                            </blockquote>
                        <?php endif; ?>

                        <?php if ($forward && $originalMessage): ?>
                            <br><br>
                            <div style="border-top: 1px solid #ccc; padding-top: 10px;">
                                <p><strong>---------- Forwarded message ----------</strong></p>
                                <p><strong>From:</strong> <?php echo View::e($originalMessage['from_name'] ?: $originalMessage['from_email']); ?></p>
                                <p><strong>Date:</strong> <?php echo date('M d, Y \a\t g:i A', strtotime($originalMessage['received_at'])); ?></p>
                                <p><strong>Subject:</strong> <?php echo View::e($originalMessage['subject']); ?></p>
                                <br>
                                <?php echo $originalMessage['body_html']; ?>
                            </div>
                        <?php endif; ?>
                    </textarea>
                </div>

                <!-- Attachments -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-paperclip"></i> Attachments:
                    </label>
                    <input type="file" class="form-control" name="attachments[]" id="attachments" multiple>
                    <small class="form-text text-muted">Max file size: 25MB per file</small>
                    <div class="attachment-list" id="attachmentList"></div>
                </div>

                <?php if ($replyTo): ?>
                    <input type="hidden" name="reply_to_id" value="<?php echo $replyTo; ?>">
                <?php endif; ?>
            </div>

            <div class="compose-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
                <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                    <i class="fas fa-save"></i> Save Draft
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="discardDraft()">
                    <i class="fas fa-trash"></i> Discard
                </button>
            </div>
        </form>
    </div>

    <script src="/assets/jquery/jquery.min.js"></script>
    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#body',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor | code',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
        });

        function toggleCcBcc() {
            const ccField = document.getElementById('ccField');
            const bccField = document.getElementById('bccField');
            ccField.style.display = ccField.style.display === 'none' ? 'block' : 'none';
            bccField.style.display = bccField.style.display === 'none' ? 'block' : 'none';
        }

        // Display selected attachments
        document.getElementById('attachments').addEventListener('change', function(e) {
            const list = document.getElementById('attachmentList');
            list.innerHTML = '';
            
            Array.from(e.target.files).forEach((file, index) => {
                const item = document.createElement('span');
                item.className = 'attachment-item';
                item.innerHTML = `
                    <i class="fas fa-file"></i> ${file.name} (${formatFileSize(file.size)})
                    <span class="remove" onclick="removeAttachment(${index})">
                        <i class="fas fa-times"></i>
                    </span>
                `;
                list.appendChild(item);
            });
        });

        function removeAttachment(index) {
            const input = document.getElementById('attachments');
            const dt = new DataTransfer();
            const files = input.files;
            
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }
            
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function saveDraft() {
            alert('Draft saving feature coming soon!');
        }

        function discardDraft() {
            if (confirm('Are you sure you want to discard this draft?')) {
                window.location.href = '/projects/mail/webmail';
            }
        }

        // Auto-save draft every 2 minutes
        setInterval(function() {
            // Implement auto-save logic here
            console.log('Auto-saving draft...');
        }, 120000);
    </script>
</body>
</html>
