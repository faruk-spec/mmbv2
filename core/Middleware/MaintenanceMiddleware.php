<?php

namespace Core\Middleware;

use Core\Database;
use Core\Auth;

class MaintenanceMiddleware
{
    public static function handle(): bool
    {
        // Check for admin bypass parameter
        if (isset($_GET['bypass'])) {
            $expectedBypass = md5('maintenance_bypass_' . date('Ymd'));
            if ($_GET['bypass'] === $expectedBypass) {
                // Set a session variable to remember the bypass
                $_SESSION['maintenance_bypass'] = $expectedBypass;
            }
        }
        
        // Check if bypass is active in session
        if (isset($_SESSION['maintenance_bypass'])) {
            $expectedBypass = md5('maintenance_bypass_' . date('Ymd'));
            if ($_SESSION['maintenance_bypass'] === $expectedBypass) {
                return true;
            }
        }
        
        // Check if maintenance mode is enabled
        $db = Database::getInstance();
        $setting = $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_mode'");
        
        if ($setting && $setting['value'] === '1') {
            // Allow access for administrators
            if (Auth::isAdmin()) {
                return true;
            }
            
            // Get maintenance settings
            $maintenanceSettings = [
                'title' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_title'"),
                'message' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_message'"),
                'custom_html' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_custom_html'"),
                'show_countdown' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_show_countdown'"),
                'end_time' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_end_time'"),
                'contact_email' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_contact_email'"),
            ];
            
            // Show maintenance page for non-admin users
            http_response_code(503);
            self::showMaintenancePage(
                $maintenanceSettings['title']['value'] ?? 'We\'ll Be Back Soon!',
                $maintenanceSettings['message']['value'] ?? 'We\'re currently performing scheduled maintenance to improve your experience. Please check back in a few minutes.',
                $maintenanceSettings['custom_html']['value'] ?? '',
                ($maintenanceSettings['show_countdown']['value'] ?? '0') === '1',
                $maintenanceSettings['end_time']['value'] ?? '',
                $maintenanceSettings['contact_email']['value'] ?? ''
            );
            exit;
        }
        
        return true;
    }
    
    private static function showMaintenancePage(string $title, string $message, string $customHtml, bool $showCountdown, string $endTime, string $contactEmail): void
    {
        $bypassToken = md5('maintenance_bypass_' . date('Ymd'));
        
        // If custom HTML template is provided, use it with variable replacement
        if (!empty($customHtml)) {
            // Calculate countdown values for replacement
            $countdownHtml = '';
            if ($showCountdown && !empty($endTime)) {
                $endTimestamp = strtotime($endTime);
                $now = time();
                $diff = $endTimestamp - $now;
                
                $days = floor($diff / 86400);
                $hours = floor(($diff % 86400) / 3600);
                $minutes = floor(($diff % 3600) / 60);
                $seconds = $diff % 60;
                
                $countdownHtml = sprintf(
                    '<div class="countdown"><span>%02d</span> Days <span>%02d</span> Hours <span>%02d</span> Minutes <span>%02d</span> Seconds</div>',
                    max(0, $days), max(0, $hours), max(0, $minutes), max(0, $seconds)
                );
            }
            
            // Replace template variables
            $customHtml = str_replace([
                '{{TITLE}}',
                '{{MESSAGE}}',
                '{{COUNTDOWN}}',
                '{{COUNTDOWN_DAYS}}',
                '{{COUNTDOWN_HOURS}}',
                '{{COUNTDOWN_MINUTES}}',
                '{{COUNTDOWN_SECONDS}}',
                '{{END_TIME}}',
                '{{BYPASS_TOKEN}}',
                '{{BYPASS_URL}}',
                '{{CONTACT_EMAIL}}',
                '{{CURRENT_YEAR}}'
            ], [
                htmlspecialchars($title),
                $message, // Already sanitized HTML
                $countdownHtml,
                isset($days) ? sprintf('%02d', max(0, $days)) : '00',
                isset($hours) ? sprintf('%02d', max(0, $hours)) : '00',
                isset($minutes) ? sprintf('%02d', max(0, $minutes)) : '00',
                isset($seconds) ? sprintf('%02d', max(0, $seconds)) : '00',
                htmlspecialchars($endTime),
                $bypassToken,
                '/admin?bypass=' . $bypassToken,
                htmlspecialchars($contactEmail),
                date('Y')
            ], $customHtml);
            
            echo $customHtml;
            return;
        }
        
        // Default maintenance page template
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Site Maintenance</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Poppins', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                }
                
                .maintenance-container {
                    text-align: center;
                    padding: 40px;
                    max-width: 600px;
                }
                
                .icon-wrapper {
                    width: 120px;
                    height: 120px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                    margin: 0 auto 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    backdrop-filter: blur(10px);
                }
                
                .icon-wrapper i {
                    font-size: 60px;
                    color: #fff;
                }
                
                h1 {
                    font-size: 2.5rem;
                    margin-bottom: 20px;
                    font-weight: 600;
                }
                
                p {
                    font-size: 1.1rem;
                    line-height: 1.6;
                    margin-bottom: 30px;
                    opacity: 0.9;
                }
                
                .message-content {
                    font-size: 1.1rem;
                    line-height: 1.6;
                    margin-bottom: 30px;
                    opacity: 0.9;
                }
                
                .message-content p {
                    margin-bottom: 15px;
                }
                
                .message-content a {
                    color: #fff;
                    text-decoration: underline;
                }
                
                .message-content b,
                .message-content strong {
                    font-weight: 600;
                }
                
                .message-content ul,
                .message-content ol {
                    text-align: left;
                    display: inline-block;
                    margin: 15px 0;
                }
                
                .message-content li {
                    margin-bottom: 8px;
                }
                
                .countdown {
                    display: flex;
                    justify-content: center;
                    gap: 20px;
                    margin: 30px 0;
                }
                
                .countdown-item {
                    background: rgba(255, 255, 255, 0.2);
                    padding: 15px 20px;
                    border-radius: 10px;
                    min-width: 80px;
                    backdrop-filter: blur(10px);
                }
                
                .countdown-value {
                    font-size: 2rem;
                    font-weight: 600;
                    display: block;
                }
                
                .countdown-label {
                    font-size: 0.8rem;
                    opacity: 0.8;
                    text-transform: uppercase;
                }
                
                .links {
                    display: flex;
                    gap: 15px;
                    justify-content: center;
                    flex-wrap: wrap;
                }
                
                .link-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 30px;
                    background: rgba(255, 255, 255, 0.2);
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    border-radius: 50px;
                    color: #fff;
                    text-decoration: none;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    backdrop-filter: blur(10px);
                }
                
                .link-btn:hover {
                    background: rgba(255, 255, 255, 0.3);
                    transform: translateY(-2px);
                }
            </style>
        </head>
        <body>
            <div class="maintenance-container">
                <div class="icon-wrapper">
                    <i class="fas fa-tools"></i>
                </div>
                <h1><?= htmlspecialchars($title) ?></h1>
                <div class="message-content"><?= $message ?></div>
                
                <?php if ($showCountdown && !empty($endTime)): ?>
                <div class="countdown" id="countdown">
                    <div class="countdown-item">
                        <span class="countdown-value" id="days">00</span>
                        <span class="countdown-label">Days</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="hours">00</span>
                        <span class="countdown-label">Hours</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="minutes">00</span>
                        <span class="countdown-label">Minutes</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="seconds">00</span>
                        <span class="countdown-label">Seconds</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="links">
                    <a href="/" class="link-btn">
                        <i class="fas fa-home"></i>
                        Back to Home
                    </a>
                    <?php if (!empty($contactEmail)): ?>
                    <a href="mailto:<?= htmlspecialchars($contactEmail) ?>" class="link-btn">
                        <i class="fas fa-envelope"></i>
                        Contact Support
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($showCountdown && !empty($endTime)): ?>
            <script>
                const endTime = new Date('<?= htmlspecialchars($endTime) ?>').getTime();
                
                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = endTime - now;
                    
                    if (distance < 0) {
                        document.getElementById('countdown').innerHTML = '<p style="font-size: 1.2rem;">Maintenance should be complete!</p>';
                        return;
                    }
                    
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    document.getElementById('days').textContent = String(days).padStart(2, '0');
                    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
                }
                
                updateCountdown();
                setInterval(updateCountdown, 1000);
            </script>
            <?php endif; ?>
        </body>
        </html>
        <?php
    }
}
