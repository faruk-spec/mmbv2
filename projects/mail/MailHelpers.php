<?php
/**
 * Mail Server Helper Functions
 * 
 * @package MMB\Projects\Mail
 */

namespace Mail;

class MailHelpers
{
    /**
     * Format file size in human-readable format
     */
    public static function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Parse email address with name
     */
    public static function parseEmail($email)
    {
        if (preg_match('/^(.+?)\s*<(.+?)>$/', $email, $matches)) {
            return [
                'name' => trim($matches[1], '"'),
                'email' => $matches[2]
            ];
        }
        return [
            'name' => null,
            'email' => $email
        ];
    }
    
    /**
     * Generate short code for items
     */
    public static function generateShortCode($length = 8)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate email address
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate domain name
     */
    public static function isValidDomain($domain)
    {
        return preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i', $domain);
    }
    
    /**
     * Extract domain from email
     */
    public static function getDomainFromEmail($email)
    {
        $parts = explode('@', $email);
        return $parts[1] ?? null;
    }
    
    /**
     * Calculate spam score based on content
     */
    public static function calculateSpamScore($subject, $body)
    {
        $score = 0;
        
        // Check for spam keywords
        $spamKeywords = ['viagra', 'cialis', 'lottery', 'winner', 'click here', 'free money', 'nigerian prince'];
        $content = strtolower($subject . ' ' . $body);
        
        foreach ($spamKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                $score += 2.0;
            }
        }
        
        // Check for excessive caps
        $capsCount = preg_match_all('/[A-Z]/', $subject, $matches);
        if ($capsCount > strlen($subject) * 0.5) {
            $score += 1.5;
        }
        
        // Check for excessive exclamation marks
        $exclamCount = substr_count($subject, '!');
        if ($exclamCount > 2) {
            $score += 1.0;
        }
        
        return min($score, 10.0);
    }
    
    /**
     * Generate DKIM signature
     */
    public static function generateDKIMSignature($domain, $selector, $privateKey, $headers, $body)
    {
        // Simplified DKIM generation - in production, use a proper library
        $canonicalizedHeaders = self::canonicalizeHeaders($headers);
        $canonicalizedBody = self::canonicalizeBody($body);
        
        $bodyHash = base64_encode(hash('sha256', $canonicalizedBody, true));
        
        $signatureData = "v=1; a=rsa-sha256; c=relaxed/relaxed; d=$domain; s=$selector; " .
                        "h=" . implode(':', array_keys($headers)) . "; " .
                        "bh=$bodyHash; b=";
        
        // In production, sign with RSA private key
        return $signatureData;
    }
    
    /**
     * Canonicalize headers for DKIM
     */
    private static function canonicalizeHeaders($headers)
    {
        $canonical = '';
        foreach ($headers as $name => $value) {
            $canonical .= strtolower($name) . ':' . trim($value) . "\r\n";
        }
        return $canonical;
    }
    
    /**
     * Canonicalize body for DKIM
     */
    private static function canonicalizeBody($body)
    {
        $body = str_replace("\r\n", "\n", $body);
        $body = str_replace("\n", "\r\n", $body);
        $body = rtrim($body, "\r\n") . "\r\n";
        return $body;
    }
    
    /**
     * Verify SPF record
     */
    public static function verifySPF($domain, $ipAddress)
    {
        $spfRecord = dns_get_record($domain, DNS_TXT);
        
        foreach ($spfRecord as $record) {
            if (isset($record['txt']) && strpos($record['txt'], 'v=spf1') === 0) {
                // Simplified SPF check - in production, use a proper library
                if (strpos($record['txt'], 'ip4:' . $ipAddress) !== false) {
                    return true;
                }
                if (strpos($record['txt'], '+all') !== false) {
                    return true;
                }
                if (strpos($record['txt'], 'mx') !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Generate DNS records for domain
     */
    public static function generateDNSRecords($domain, $mailServer)
    {
        return [
            'MX' => [
                'type' => 'MX',
                'name' => '@',
                'value' => $mailServer,
                'priority' => 10,
                'ttl' => 3600
            ],
            'SPF' => [
                'type' => 'TXT',
                'name' => '@',
                'value' => "v=spf1 mx ~all",
                'ttl' => 3600
            ],
            'DKIM' => [
                'type' => 'TXT',
                'name' => 'default._domainkey',
                'value' => "v=DKIM1; k=rsa; p=YOUR_PUBLIC_KEY",
                'ttl' => 3600
            ],
            'DMARC' => [
                'type' => 'TXT',
                'name' => '_dmarc',
                'value' => "v=DMARC1; p=quarantine; rua=mailto:postmaster@$domain",
                'ttl' => 3600
            ]
        ];
    }
    
    /**
     * Check if DNS record exists
     */
    public static function checkDNSRecord($domain, $type, $expectedValue)
    {
        $dnsType = match($type) {
            'MX' => DNS_MX,
            'TXT' => DNS_TXT,
            'CNAME' => DNS_CNAME,
            'A' => DNS_A,
            'AAAA' => DNS_AAAA,
            default => DNS_ANY
        };
        
        $records = dns_get_record($domain, $dnsType);
        
        foreach ($records as $record) {
            if ($type === 'MX' && isset($record['target'])) {
                if (strpos($record['target'], $expectedValue) !== false) {
                    return true;
                }
            } elseif ($type === 'TXT' && isset($record['txt'])) {
                if (strpos($record['txt'], $expectedValue) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize email content
     */
    public static function sanitizeEmailContent($html)
    {
        // Remove potentially dangerous tags and attributes
        $allowed_tags = '<p><br><a><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><div><span><img><table><tr><td><th>';
        $clean = strip_tags($html, $allowed_tags);
        
        // Remove javascript and other dangerous attributes
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $clean);
        $clean = preg_replace('/on\w+="[^"]*"/i', '', $clean);
        
        return $clean;
    }
    
    /**
     * Convert HTML to plain text
     */
    public static function htmlToText($html)
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
    
    /**
     * Format date for email display
     */
    public static function formatEmailDate($timestamp)
    {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            return date('l, g:i A', $time);
        } else {
            return date('M j, Y, g:i A', $time);
        }
    }
}
