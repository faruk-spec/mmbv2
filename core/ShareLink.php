<?php
/**
 * Share Link Manager
 * 
 * Enhanced sharing features with social media and email integration
 * Part of Phase 7: Advanced ProShare Features
 * 
 * @package MMB\Core
 */

namespace Core;

class ShareLink
{
    /**
     * Generate social media share links
     * 
     * @param string $url URL to share
     * @param string $title Title/description
     * @param array $platforms Platforms to generate links for
     * @return array Share links
     */
    public static function generateSocialLinks(string $url, string $title = '', array $platforms = []): array
    {
        $encodedUrl = urlencode($url);
        $encodedTitle = urlencode($title);
        
        $defaultPlatforms = ['facebook', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'email'];
        $platforms = !empty($platforms) ? $platforms : $defaultPlatforms;
        
        $links = [];
        
        foreach ($platforms as $platform) {
            $links[$platform] = self::getSocialLink($platform, $encodedUrl, $encodedTitle);
        }
        
        return $links;
    }
    
    /**
     * Get share link for specific platform
     * 
     * @param string $platform Platform name
     * @param string $encodedUrl Encoded URL
     * @param string $encodedTitle Encoded title
     * @return array Platform details
     */
    private static function getSocialLink(string $platform, string $encodedUrl, string $encodedTitle): array
    {
        $links = [
            'facebook' => [
                'url' => "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}",
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook',
                'color' => '#3b5998'
            ],
            'twitter' => [
                'url' => "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}",
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter',
                'color' => '#1da1f2'
            ],
            'linkedin' => [
                'url' => "https://www.linkedin.com/sharing/share-offsite/?url={$encodedUrl}",
                'name' => 'LinkedIn',
                'icon' => 'fab fa-linkedin',
                'color' => '#0077b5'
            ],
            'whatsapp' => [
                'url' => "https://wa.me/?text={$encodedTitle}%20{$encodedUrl}",
                'name' => 'WhatsApp',
                'icon' => 'fab fa-whatsapp',
                'color' => '#25d366'
            ],
            'telegram' => [
                'url' => "https://t.me/share/url?url={$encodedUrl}&text={$encodedTitle}",
                'name' => 'Telegram',
                'icon' => 'fab fa-telegram',
                'color' => '#0088cc'
            ],
            'email' => [
                'url' => "mailto:?subject={$encodedTitle}&body={$encodedUrl}",
                'name' => 'Email',
                'icon' => 'fas fa-envelope',
                'color' => '#666666'
            ]
        ];
        
        return $links[$platform] ?? [
            'url' => $encodedUrl,
            'name' => ucfirst($platform),
            'icon' => 'fas fa-share',
            'color' => '#666666'
        ];
    }
    
    /**
     * Generate embed code for share link
     * 
     * @param string $url URL to embed
     * @param int $width Width in pixels
     * @param int $height Height in pixels
     * @return string Embed code
     */
    public static function generateEmbedCode(string $url, int $width = 600, int $height = 400): string
    {
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        
        return <<<HTML
<iframe src="{$escapedUrl}" width="{$width}" height="{$height}" frameborder="0" allowfullscreen></iframe>
HTML;
    }
    
    /**
     * Send email invitation
     * 
     * @param string $email Recipient email
     * @param string $url Share URL
     * @param string $message Optional message
     * @param int|null $senderId Sender user ID
     * @return bool Success status
     */
    public static function sendEmailInvitation(string $email, string $url, string $message = '', ?int $senderId = null): bool
    {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Get sender info if provided
        $senderName = 'Someone';
        if ($senderId) {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT name FROM users WHERE id = ?", [$senderId]);
            if ($user) {
                $senderName = $user['name'];
            }
        }
        
        // Prepare email content
        $subject = "{$senderName} shared a file with you";
        $body = self::generateEmailBody($senderName, $url, $message);
        
        // In production, use a proper email service
        // For now, log the invitation
        Logger::info("Email invitation sent to {$email} from {$senderName}");
        
        // Queue for later sending
        Cache::remember('email_queue', function() {
            return [];
        }, 0);
        
        $queue = Cache::get('email_queue', []);
        $queue[] = [
            'to' => $email,
            'subject' => $subject,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s')
        ];
        Cache::set('email_queue', $queue, 0);
        
        return true;
    }
    
    /**
     * Generate email body
     * 
     * @param string $senderName Sender name
     * @param string $url Share URL
     * @param string $message Optional message
     * @return string Email body HTML
     */
    private static function generateEmailBody(string $senderName, string $url, string $message): string
    {
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>You've received a shared file!</h1>
        </div>
        <div class="content">
            <p><strong>{$senderName}</strong> has shared a file with you.</p>
            {$escapedMessage}
            <p>Click the button below to access the file:</p>
            <a href="{$escapedUrl}" class="button">View Shared File</a>
            <p style="font-size: 12px; color: #666;">Or copy this link: {$escapedUrl}</p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Generate custom short link
     * 
     * @param string $customSlug Custom slug
     * @param string $targetUrl Target URL
     * @param int|null $userId User ID
     * @return array Result with success status and short URL
     */
    public static function createCustomLink(string $customSlug, string $targetUrl, ?int $userId = null): array
    {
        // Validate slug (alphanumeric and dashes only)
        if (!preg_match('/^[a-zA-Z0-9-]+$/', $customSlug)) {
            return [
                'success' => false,
                'error' => 'Invalid slug format. Use only letters, numbers, and dashes.'
            ];
        }
        
        // Check if slug is already taken
        // In production, check database
        $taken = false; // Placeholder
        
        if ($taken) {
            return [
                'success' => false,
                'error' => 'This slug is already taken.'
            ];
        }
        
        // Store custom link (in production, save to database)
        Logger::info("Custom link created: {$customSlug} -> {$targetUrl}");
        
        return [
            'success' => true,
            'short_url' => APP_URL . '/s/' . $customSlug,
            'slug' => $customSlug
        ];
    }
    
    /**
     * Generate shareable widget HTML
     * 
     * @param string $url Share URL
     * @param string $title Title
     * @return string Widget HTML
     */
    public static function generateWidget(string $url, string $title = 'Share'): string
    {
        $socialLinks = self::generateSocialLinks($url, $title);
        $qrCode = QRCode::generate($url, 150);
        
        $html = '<div class="share-widget" style="padding: 20px; background: #f5f5f5; border-radius: 10px;">';
        $html .= '<h3 style="margin-top: 0;">Share this file</h3>';
        $html .= '<div class="social-buttons" style="display: flex; gap: 10px; flex-wrap: wrap; margin: 15px 0;">';
        
        foreach ($socialLinks as $platform => $link) {
            $html .= sprintf(
                '<a href="%s" target="_blank" style="padding: 8px 15px; background: %s; color: white; text-decoration: none; border-radius: 5px; font-size: 14px;"><i class="%s"></i> %s</a>',
                $link['url'],
                $link['color'],
                $link['icon'],
                $link['name']
            );
        }
        
        $html .= '</div>';
        $html .= '<div class="qr-code" style="margin-top: 20px; text-align: center;">';
        $html .= '<p><strong>Or scan QR code:</strong></p>';
        $html .= $qrCode;
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
