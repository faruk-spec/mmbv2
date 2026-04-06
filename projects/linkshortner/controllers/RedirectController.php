<?php
/**
 * LinkShortner Redirect Handler
 * Handles public short URL redirection: /l/{code}
 *
 * @package MMB\Projects\LinkShortner\Controllers
 */

namespace Projects\LinkShortner\Controllers;

use Core\Database;

class RedirectController
{
    public function redirect(string $code): void
    {
        try {
            $db   = Database::projectConnection('linkshortner');
            $link = $db->fetch(
                "SELECT * FROM linkshortner_links WHERE code = ? AND status = 'active'",
                [$code]
            );
        } catch (\Exception $e) {
            http_response_code(404);
            echo '<!DOCTYPE html><html><body><h1>Link not found</h1></body></html>';
            exit;
        }

        if (!$link) {
            http_response_code(404);
            echo '<!DOCTYPE html><html><body><h1>Link not found or inactive</h1></body></html>';
            exit;
        }

        // Check expiry
        if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
            $db->query("UPDATE linkshortner_links SET status = 'expired' WHERE id = ?", [$link['id']]);
            http_response_code(410);
            echo '<!DOCTYPE html><html><body><h1>Link has expired</h1></body></html>';
            exit;
        }

        // Check click limit
        if ($link['click_limit'] && $link['total_clicks'] >= $link['click_limit']) {
            http_response_code(410);
            echo '<!DOCTYPE html><html><body><h1>Link has reached its click limit</h1></body></html>';
            exit;
        }

        // Record click
        $ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip     = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        // Use only the first IP from a potentially comma-separated list, validate it
        $ip     = trim(explode(',', $ip)[0]);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '';
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        $device = $this->detectDevice($ua);
        $os     = $this->detectOS($ua);
        $browser = $this->detectBrowser($ua);

        $db->query(
            "INSERT INTO linkshortner_clicks (link_id, ip_address, user_agent, referer, device, os, browser) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$link['id'], $ip, $ua, $referer, $device, $os, $browser]
        );

        $db->query(
            "UPDATE linkshortner_links SET total_clicks = total_clicks + 1 WHERE id = ?",
            [$link['id']]
        );

        // Build redirect URL (append UTM params if set)
        $url = $link['original_url'];
        $utms = [];
        if ($link['utm_source'])   $utms['utm_source']   = $link['utm_source'];
        if ($link['utm_medium'])   $utms['utm_medium']   = $link['utm_medium'];
        if ($link['utm_campaign']) $utms['utm_campaign']  = $link['utm_campaign'];

        if (!empty($utms)) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . http_build_query($utms);
        }

        header('Location: ' . $url, true, 302);
        exit;
    }

    private function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        if (preg_match('/mobile|android|iphone|ipod/i', $ua)) return 'mobile';
        return 'desktop';
    }

    private function detectOS(string $ua): string
    {
        if (preg_match('/windows/i', $ua)) return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $ua)) return 'macOS';
        if (preg_match('/linux/i', $ua)) return 'Linux';
        if (preg_match('/android/i', $ua)) return 'Android';
        if (preg_match('/iphone|ipad|ios/i', $ua)) return 'iOS';
        return 'Unknown';
    }

    private function detectBrowser(string $ua): string
    {
        if (preg_match('/edg/i', $ua)) return 'Edge';
        if (preg_match('/chrome/i', $ua)) return 'Chrome';
        if (preg_match('/firefox/i', $ua)) return 'Firefox';
        if (preg_match('/safari/i', $ua)) return 'Safari';
        if (preg_match('/opera/i', $ua)) return 'Opera';
        return 'Unknown';
    }
}
