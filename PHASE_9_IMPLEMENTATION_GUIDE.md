# Phase 9: Email & Notification System - Implementation Guide

## Overview
This document describes the email and notification system implemented in Phase 9 of the MMB platform.

## Important: Database Configuration
All email and notification features follow the database-agnostic approach:
- No hardcoded database names or credentials
- Configuration read from `/config/mail.php`
- User preferences stored in respective databases

## Completed Features

### 1. Email Service

#### Email Class
Comprehensive email sending with SMTP and queue management.

**Location**: `/core/Email.php`

**Features**:
- SMTP configuration support
- Email queue system
- Template-based emails
- HTML email layout
- Retry logic for failed sends
- Queue statistics

**Usage Examples**:

```php
use Core\Email;

// Send simple email
Email::send('user@example.com', 'Welcome!', '<p>Hello World</p>');

// Send to multiple recipients
Email::send(['user1@example.com', 'user2@example.com'], 'Subject', '<p>Body</p>');

// Send with options
Email::send('user@example.com', 'Subject', '<p>Body</p>', [
    'cc' => ['admin@example.com'],
    'bcc' => ['archive@example.com'],
    'reply_to' => 'support@example.com'
]);

// Send from template
Email::sendTemplate('user@example.com', 'welcome', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'dashboard_url' => 'https://example.com/dashboard'
], 'Welcome to Our Platform');

// Process email queue (run via cron)
$processed = Email::processQueue(10); // Process up to 10 emails

// Get queue statistics
$stats = Email::getQueueStats();
// Returns: ['total' => 5, 'pending' => 3, 'retrying' => 2]

// Disable queue (send immediately)
Email::setQueueEnabled(false);
Email::send('user@example.com', 'Subject', 'Body');
```

### 2. Notification System

#### Notification Class
Multi-channel notification delivery.

**Location**: `/core/Notification.php`

**Features**:
- Database notifications (in-app)
- Email notifications
- SMS notifications (placeholder)
- Push notifications (placeholder)
- User preference checking
- Read/unread tracking

**Usage Examples**:

```php
use Core\Notification;

// Send in-app notification
Notification::send(
    $userId,
    'file_downloaded',
    'Your file "document.pdf" was downloaded',
    ['file_id' => 123],
    ['database']
);

// Send multi-channel notification
Notification::send(
    $userId,
    'ocr_completed',
    'Your OCR job is complete',
    [
        'file_name' => 'scan.pdf',
        'processing_time' => 45,
        'confidence' => 98.5
    ],
    ['database', 'email', 'push']
);

// Get user notifications
$notifications = Notification::getForUser($userId, true, 20); // Unread only, limit 20

// Mark as read
Notification::markAsRead($notificationId);

// Mark all as read
Notification::markAllAsRead($userId);

// Get unread count
$unreadCount = Notification::getUnreadCount($userId);
```

### 3. Email Templates

#### Created Templates
- **`layout.php`** - Main email layout with header/footer
- **`welcome.php`** - Welcome email (already existed)
- **`password-reset.php`** - Password reset email (already existed)
- **`verify.php`** - Email verification (already existed)
- **`file-downloaded.php`** - ProShare file download notification
- **`ocr-completed.php`** - ImgTxt job completion notification

#### Template Structure

```php
<!-- views/emails/custom-template.php -->
<h2>Your Custom Title</h2>

<p>Hi <?= htmlspecialchars($name ?? 'there') ?>,</p>

<p>Your custom message here.</p>

<p style="text-align: center;">
    <a href="<?= $action_url ?? '#' ?>" class="button">Call to Action</a>
</p>

<p>Best regards,<br>
The <?= $app_name ?? 'MMB Platform' ?> Team</p>
```

The template is automatically wrapped in the email layout.

### 4. Configuration

#### Mail Configuration
**Location**: `/config/mail.php`

```php
return [
    'driver' => 'smtp',
    'host' => 'smtp.mailtrap.io',  // Or your SMTP server
    'port' => 587,
    'username' => 'your_username',
    'password' => 'your_password',
    'encryption' => 'tls',
    'from' => [
        'address' => 'noreply@example.com',
        'name' => 'MMB Platform'
    ],
    'queue' => [
        'enabled' => true,
        'batch_size' => 10,
        'retry_attempts' => 3
    ]
];
```

#### Environment Variables
Create `.env` file:
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="MMB Platform"
```

## Integration Examples

### ProShare - File Download Notification

```php
// In ProShare download controller
use Core\Notification;
use Core\Analytics;

public function download($shortCode) {
    $file = $this->getFile($shortCode);
    $userId = $file['user_id'];
    
    // Track download
    Analytics::trackDownload($file['id'], Auth::id());
    
    // Send notification to file owner
    if ($userId) {
        Notification::send(
            $userId,
            'file_downloaded',
            "Your file '{$file['original_name']}' was downloaded",
            [
                'file_id' => $file['id'],
                'file_name' => $file['original_name'],
                'downloaded_at' => date('Y-m-d H:i:s'),
                'download_count' => $file['downloads'] + 1
            ],
            ['database', 'email']
        );
    }
    
    // Serve file...
}
```

### ImgTxt - OCR Completion Notification

```php
// In ImgTxt OCR processor
use Core\Notification;

public function processOCR($jobId) {
    $job = $this->getJob($jobId);
    
    // Process OCR...
    $result = $this->performOCR($job);
    
    // Update job status
    $this->updateJobStatus($jobId, 'completed', $result);
    
    // Send notification
    Notification::send(
        $job['user_id'],
        'ocr_completed',
        "Your OCR job for '{$job['original_filename']}' is complete",
        [
            'file_name' => $job['original_filename'],
            'status' => 'completed',
            'processing_time' => $result['processing_time'],
            'confidence' => $result['confidence'],
            'result_url' => "/imgtxt/jobs/{$jobId}"
        ],
        ['database', 'email']
    );
}
```

### CodeXPro - Collaboration Invite

```php
// In CodeXPro collaboration controller
use Core\Notification;
use Core\Email;

public function inviteCollaborator($projectId, $email) {
    $project = $this->getProject($projectId);
    $inviter = Auth::user();
    
    // Create invitation
    $token = $this->createInvitation($projectId, $email);
    
    // Send email invitation
    Email::sendTemplate($email, 'collaboration-invite', [
        'inviter_name' => $inviter['name'],
        'project_name' => $project['name'],
        'project_description' => $project['description'],
        'accept_url' => APP_URL . "/codexpro/accept-invite/{$token}"
    ], "You've been invited to collaborate on {$project['name']}");
}
```

## Database Schema

Add these tables to the main database:

```sql
-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `message` TEXT NOT NULL,
    `data` JSON NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User notification preferences
CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `email_enabled` TINYINT(1) DEFAULT 1,
    `sms_enabled` TINYINT(1) DEFAULT 0,
    `push_enabled` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `idx_user_type` (`user_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email queue (optional, if not using Cache)
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `to` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` LONGTEXT NOT NULL,
    `options` JSON NULL,
    `attempts` INT DEFAULT 0,
    `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    `error_message` TEXT NULL,
    `queued_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `sent_at` TIMESTAMP NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_queued_at` (`queued_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Cron Jobs

Set up cron jobs to process queues:

```bash
# Process email queue every 5 minutes
*/5 * * * * php /path/to/mmb/cron/process-email-queue.php

# Clean up old notifications (older than 30 days)
0 2 * * * php /path/to/mmb/cron/cleanup-notifications.php
```

### Email Queue Processor (`cron/process-email-queue.php`)

```php
<?php
require_once __DIR__ . '/../core/Email.php';
require_once __DIR__ . '/../core/Logger.php';

use Core\Email;
use Core\Logger;

$processed = Email::processQueue(50); // Process up to 50 emails

Logger::info("Processed {$processed} emails from queue");
```

## Notification Types

### Supported Types
- `welcome` - Welcome new users
- `password_reset` - Password reset requests
- `file_downloaded` - ProShare file download
- `link_expiring` - ProShare link expiring soon
- `ocr_completed` - ImgTxt OCR job completed
- `ocr_failed` - ImgTxt OCR job failed
- `collaboration_invite` - CodeXPro collaboration invitation
- `project_shared` - CodeXPro project shared

## Testing

### Test Email Sending

```php
// Test script
use Core\Email;

// Send test email
$result = Email::send(
    'test@example.com',
    'Test Email',
    '<p>This is a test email from MMB Platform</p>'
);

if ($result) {
    echo "Email sent successfully\n";
} else {
    echo "Failed to send email\n";
}

// Check queue
$stats = Email::getQueueStats();
print_r($stats);
```

### Test Notifications

```php
use Core\Notification;

// Test in-app notification
Notification::send(
    1, // User ID
    'test_notification',
    'This is a test notification',
    ['test' => true],
    ['database']
);

// Get notifications
$notifications = Notification::getForUser(1);
print_r($notifications);
```

## Best Practices

### Email Best Practices
1. Always use templates for consistency
2. Keep emails concise and actionable
3. Include unsubscribe links
4. Test emails before sending to users
5. Monitor bounce rates and delivery issues
6. Use queue for bulk emails
7. Implement retry logic for failures

### Notification Best Practices
1. Don't over-notify users
2. Make notifications actionable
3. Allow users to configure preferences
4. Group similar notifications
5. Show unread count in UI
6. Implement mark all as read
7. Auto-expire old notifications

## Security Considerations

### Email Security
- Validate all email addresses
- Sanitize user input in emails
- Use HTTPS for all links
- Implement rate limiting
- Protect against header injection
- Use secure SMTP connections

### Notification Security
- Validate user permissions
- Sanitize notification content
- Prevent XSS in notifications
- Limit notification frequency
- Implement spam prevention

## Troubleshooting

### Emails Not Sending
1. Check SMTP configuration in `/config/mail.php`
2. Verify SMTP credentials
3. Check firewall/port settings
4. Review error logs in `/storage/logs/`
5. Test SMTP connection manually
6. Check email queue status

### Notifications Not Appearing
1. Verify database table exists
2. Check user ID is valid
3. Review notification preferences
4. Check error logs
5. Verify cache is working

## Next Steps

After Phase 9:
- [ ] Add SMS integration (Twilio)
- [ ] Implement push notifications (FCM/WebPush)
- [ ] Create notification preferences UI
- [ ] Add email templates for all notification types
- [ ] Set up monitoring for email deliverability
- [ ] Implement notification center UI
- [ ] Add notification grouping
- [ ] Create admin panel for email management

## Support

For issues or questions:
1. Check error logs in `/storage/logs/`
2. Verify mail configuration
3. Test with Mailtrap.io for development
4. Review email queue status
5. Check notification database tables
6. Verify cron jobs are running
