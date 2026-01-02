# Phase 7: Advanced ProShare Features - Implementation Guide

## Overview
This document describes the advanced ProShare features implemented in Phase 7, including enhanced sharing, QR codes, analytics, and social media integration.

## Important: Database Configuration
All database operations follow the same database-agnostic approach as Phase 8:
- No hardcoded database names or credentials
- Configuration read from `/projects/proshare/config.php`
- Works with any database naming scheme (proshare, mmb_proshare, etc.)

## Completed Features

### 1. QR Code Generation

#### QRCode Class
Generate QR codes for share links.

**Location**: `/core/QRCode.php`

**Features**:
- QR code generation using Google Charts API
- SVG output support
- Data URL generation for downloads
- File saving capability

**Usage Examples**:

```php
use Core\QRCode;

// Generate QR code as HTML img tag
$qrHtml = QRCode::generate('https://example.com/share/abc123', 200);

// Generate as data URL for embedding
$dataUrl = QRCode::generateDataUrl('https://example.com/share/abc123');

// Save to file
QRCode::saveToFile('https://example.com/share/abc123', '/path/to/qr.png', 300);

// Generate complete share link with QR
$shareData = QRCode::generateShareLink('https://example.com/share/abc123', 'My File');
echo $shareData['html'];
echo $shareData['data_url'];
```

**In ProShare Views**:
```php
<!-- Display QR code for share link -->
<div class="qr-code">
    <?= QRCode::generate($shareUrl, 200) ?>
    <p>Scan to access</p>
</div>
```

### 2. Analytics System

#### Analytics Class
Track downloads, page views, and generate reports.

**Location**: `/core/Analytics.php`

**Features**:
- Event tracking with caching
- Download analytics
- Browser and platform detection
- Geographic tracking (placeholder)
- Report generation (HTML, CSV, JSON)

**Usage Examples**:

```php
use Core\Analytics;

// Track file download
Analytics::trackDownload($fileId, $userId);

// Track page view
Analytics::trackPageView('proshare_home', $userId);

// Track custom event
Analytics::track('file_shared', [
    'file_id' => $fileId,
    'shared_via' => 'email'
], $userId);

// Get download statistics
$stats = Analytics::getDownloadStats($fileId, 7); // Last 7 days
echo "Total downloads: " . $stats['total_downloads'];

// Generate report
$report = Analytics::generateReport('weekly', 'html');

// Flush queued analytics (run via cron)
$flushed = Analytics::flush();
```

**Integration in ProShare**:
```php
// In file download handler
public function download($shortCode) {
    $file = $this->getFile($shortCode);
    
    // Track the download
    Analytics::trackDownload($file['id'], Auth::id());
    
    // Serve file...
}
```

### 3. Enhanced Sharing Features

#### ShareLink Class
Social media integration, email invitations, and custom links.

**Location**: `/core/ShareLink.php`

**Features**:
- Social media share links (Facebook, Twitter, LinkedIn, WhatsApp, Telegram, Email)
- Email invitations with HTML templates
- Embed code generation
- Custom short link creation
- Shareable widget generation

**Usage Examples**:

```php
use Core\ShareLink;

// Generate social media links
$socialLinks = ShareLink::generateSocialLinks(
    'https://example.com/share/abc123',
    'Check out this file!'
);

foreach ($socialLinks as $platform => $link) {
    echo "<a href='{$link['url']}' style='color: {$link['color']}'>";
    echo "{$link['name']}</a> ";
}

// Send email invitation
ShareLink::sendEmailInvitation(
    'friend@example.com',
    'https://example.com/share/abc123',
    'I thought you might find this interesting!',
    Auth::id()
);

// Generate embed code
$embedCode = ShareLink::generateEmbedCode(
    'https://example.com/share/abc123',
    800,
    600
);

// Create custom short link
$result = ShareLink::createCustomLink('my-file', $targetUrl, Auth::id());
if ($result['success']) {
    echo "Short URL: " . $result['short_url'];
}

// Generate complete sharing widget
$widget = ShareLink::generateWidget($shareUrl, 'My Awesome File');
echo $widget; // Includes social buttons + QR code
```

**In ProShare Views**:
```php
<!-- Complete sharing interface -->
<div class="share-section">
    <h3>Share this file</h3>
    
    <!-- Social media buttons -->
    <?php $socialLinks = ShareLink::generateSocialLinks($shareUrl, $file['name']); ?>
    <div class="social-buttons">
        <?php foreach ($socialLinks as $platform => $link): ?>
            <a href="<?= $link['url'] ?>" target="_blank" class="btn-social" style="background: <?= $link['color'] ?>">
                <i class="<?= $link['icon'] ?>"></i> <?= $link['name'] ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- QR Code -->
    <div class="qr-section">
        <h4>Scan QR Code</h4>
        <?= QRCode::generate($shareUrl, 200) ?>
    </div>
    
    <!-- Email invitation -->
    <div class="email-invite">
        <h4>Send via Email</h4>
        <form method="POST" action="/proshare/send-invite">
            <input type="email" name="email" placeholder="friend@example.com" required>
            <textarea name="message" placeholder="Optional message"></textarea>
            <button type="submit">Send Invitation</button>
        </form>
    </div>
    
    <!-- Embed code -->
    <div class="embed-code">
        <h4>Embed Code</h4>
        <textarea readonly><?= ShareLink::generateEmbedCode($shareUrl) ?></textarea>
    </div>
</div>
```

## Implementation Checklist

### Enhanced Sharing Features (7.2)
- [x] QR code generation for share links
- [x] Social media sharing integration (Facebook, Twitter, LinkedIn, WhatsApp, Telegram)
- [x] Email share invitations with HTML templates
- [x] Shareable embed codes
- [x] Link customization (custom slugs)
- [ ] Integrate into ProShare UI
- [ ] Add database tables for custom links
- [ ] Implement email sending service

### Analytics & Reporting (7.4)
- [x] Analytics tracking infrastructure
- [x] Download analytics with caching
- [x] Browser/platform statistics
- [x] Report generation (HTML, CSV, JSON)
- [ ] Add analytics dashboard view
- [ ] Implement geographic location tracking (GeoIP service)
- [ ] Create analytics database tables
- [ ] Add export functionality to UI

### Pending Features
- [ ] End-to-End Encryption (7.1) - Requires client-side crypto implementation
- [ ] Chat & Messaging (7.3) - Requires WebSocket infrastructure (Phase 4)

## Database Schema Additions

Add these tables to your ProShare database:

```sql
-- Analytics events table
CREATE TABLE IF NOT EXISTS `analytics_events` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `event` VARCHAR(100) NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `data` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `referer` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event` (`event`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom short links table
CREATE TABLE IF NOT EXISTS `custom_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `target_url` VARCHAR(500) NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `clicks` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email invitation log
CREATE TABLE IF NOT EXISTS `email_invitations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `file_id` INT UNSIGNED NOT NULL,
    `sender_id` INT UNSIGNED NULL,
    `recipient_email` VARCHAR(255) NOT NULL,
    `message` TEXT NULL,
    `sent_at` TIMESTAMP NULL,
    `opened_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
    INDEX `idx_file_id` (`file_id`),
    INDEX `idx_sender_id` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Configuration

### QR Code Service
By default, uses Google Charts API. For production, consider:
- Self-hosted QR code library (e.g., endroid/qr-code)
- Local generation for better privacy
- Caching generated QR codes

### Analytics
Configure analytics tracking:
```php
// In ProShare config
'analytics' => [
    'enabled' => true,
    'batch_size' => 100,
    'flush_interval' => 3600, // 1 hour
    'track_ip' => true,
    'track_user_agent' => true
]
```

### Email Service
For production email sending:
- Use SMTP service (SendGrid, Mailgun, AWS SES)
- Configure in `/config/mail.php`
- Implement queue processing for bulk emails

## Performance Considerations

### Caching
- QR codes cached for 24 hours
- Analytics data cached for 5 minutes
- Social links generated on-demand

### Optimization
- Batch analytics events for database writes
- Use CDN for QR code images
- Lazy load social media widgets
- Compress analytics reports

## Usage in ProShare Controllers

```php
namespace Projects\ProShare\Controllers;

use Core\QRCode;
use Core\ShareLink;
use Core\Analytics;

class ShareController extends BaseController
{
    public function show($shortCode)
    {
        $file = $this->getFile($shortCode);
        
        // Track page view
        Analytics::trackPageView('file_view_' . $file['id'], Auth::id());
        
        // Generate share data
        $shareUrl = APP_URL . '/share/' . $shortCode;
        $qrCode = QRCode::generate($shareUrl, 200);
        $socialLinks = ShareLink::generateSocialLinks($shareUrl, $file['original_name']);
        $embedCode = ShareLink::generateEmbedCode($shareUrl);
        
        $this->view('share', [
            'file' => $file,
            'shareUrl' => $shareUrl,
            'qrCode' => $qrCode,
            'socialLinks' => $socialLinks,
            'embedCode' => $embedCode
        ]);
    }
    
    public function download($shortCode)
    {
        $file = $this->getFile($shortCode);
        
        // Track download
        Analytics::trackDownload($file['id'], Auth::id());
        
        // Serve file...
    }
    
    public function sendInvite()
    {
        $email = $_POST['email'] ?? '';
        $message = $_POST['message'] ?? '';
        $shortCode = $_POST['short_code'] ?? '';
        
        $file = $this->getFile($shortCode);
        $shareUrl = APP_URL . '/share/' . $shortCode;
        
        $success = ShareLink::sendEmailInvitation(
            $email,
            $shareUrl,
            $message,
            Auth::id()
        );
        
        if ($success) {
            $this->flash('success', 'Invitation sent!');
        } else {
            $this->flash('error', 'Failed to send invitation.');
        }
        
        $this->redirect('/share/' . $shortCode);
    }
}
```

## Testing

Test the features:
1. Generate QR codes and verify they scan correctly
2. Test social media sharing links
3. Send test email invitations
4. Track analytics events and verify logging
5. Generate reports and export data

## Next Steps

After Phase 7:
1. Implement E2E encryption (requires crypto library)
2. Add WebSocket support for real-time chat (Phase 4)
3. Create analytics dashboard UI
4. Integrate email sending service
5. Add GeoIP service for location tracking

## Support

For issues or questions:
1. Check error logs in `/storage/logs/`
2. Verify QR code generation is working
3. Test email queue functionality
4. Review analytics cache data
5. Check the main README.md for general setup issues
