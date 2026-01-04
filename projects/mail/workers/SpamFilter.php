<?php

/**
 * Spam Filter
 * 
 * Calculates spam score for incoming emails using various heuristics
 * and can integrate with SpamAssassin or Rspamd
 */

class SpamFilter
{
    private $config;
    private $spamKeywords = [
        'viagra', 'cialis', 'pharmacy', 'casino', 'lottery', 'winner',
        'congratulations', 'prize', 'free money', 'nigerian prince',
        'weight loss', 'male enhancement', 'click here', 'act now',
        'limited time', 'urgent', 'guaranteed', 'risk-free'
    ];
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    /**
     * Calculate spam score for an email
     * 
     * @param array $email Parsed email data
     * @return float Spam score (0-10, higher = more spammy)
     */
    public function calculateScore($email)
    {
        $score = 0.0;
        
        // Use external spam filter if configured
        if ($this->config['spam_filter']['enabled'] ?? false) {
            $externalScore = $this->checkExternalFilter($email);
            if ($externalScore !== null) {
                return $externalScore;
            }
        }
        
        // Built-in spam detection heuristics
        $score += $this->checkSubject($email['subject'] ?? '');
        $score += $this->checkBody($email['body_text'] ?? '', $email['body_html'] ?? '');
        $score += $this->checkFrom($email['from'] ?? []);
        $score += $this->checkHeaders($email);
        $score += $this->checkLinks($email['body_html'] ?? '');
        $score += $this->checkAttachments($email['attachments'] ?? []);
        
        return min($score, 10.0);
    }
    
    /**
     * Check external spam filter (SpamAssassin or Rspamd)
     */
    private function checkExternalFilter($email)
    {
        $filterType = $this->config['spam_filter']['type'] ?? 'spamassassin';
        
        if ($filterType === 'spamassassin') {
            return $this->checkSpamAssassin($email);
        } elseif ($filterType === 'rspamd') {
            return $this->checkRspamd($email);
        }
        
        return null;
    }
    
    /**
     * Check email against SpamAssassin
     */
    private function checkSpamAssassin($email)
    {
        $host = $this->config['spam_filter']['spamassassin']['host'] ?? 'localhost';
        $port = $this->config['spam_filter']['spamassassin']['port'] ?? 783;
        
        // Create email message for SpamAssassin
        $message = $this->buildEmailMessage($email);
        
        // Connect to SpamAssassin
        $socket = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$socket) {
            return null;
        }
        
        // Send CHECK command
        $length = strlen($message);
        fwrite($socket, "CHECK SPAMC/1.2\r\n");
        fwrite($socket, "Content-length: $length\r\n\r\n");
        fwrite($socket, $message);
        
        // Read response
        $response = '';
        while (!feof($socket)) {
            $response .= fgets($socket);
        }
        fclose($socket);
        
        // Parse score from response
        if (preg_match('/Spam: (True|False) ; ([\d.]+) \/ ([\d.]+)/', $response, $matches)) {
            $score = floatval($matches[2]);
            // Normalize to 0-10 scale
            return min($score / 5 * 10, 10.0);
        }
        
        return null;
    }
    
    /**
     * Check email against Rspamd
     */
    private function checkRspamd($email)
    {
        $url = $this->config['spam_filter']['rspamd']['url'] ?? 'http://localhost:11333/check';
        
        $message = $this->buildEmailMessage($email);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['score'])) {
                // Rspamd typically uses 0-15 scale, normalize to 0-10
                return min(floatval($result['score']) / 15 * 10, 10.0);
            }
        }
        
        return null;
    }
    
    /**
     * Check subject line for spam indicators
     */
    private function checkSubject($subject)
    {
        $score = 0.0;
        $subject = strtolower($subject);
        
        // Check for spam keywords
        foreach ($this->spamKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                $score += 1.0;
            }
        }
        
        // ALL CAPS subject
        if (preg_match('/^[A-Z\s!?]+$/', trim($subject)) && strlen($subject) > 10) {
            $score += 1.5;
        }
        
        // Excessive punctuation
        if (substr_count($subject, '!') > 2 || substr_count($subject, '?') > 2) {
            $score += 0.5;
        }
        
        // RE: or FW: without being a reply
        if (preg_match('/^(re|fw|fwd):/i', $subject)) {
            $score += 0.3;
        }
        
        return $score;
    }
    
    /**
     * Check email body for spam indicators
     */
    private function checkBody($bodyText, $bodyHtml)
    {
        $score = 0.0;
        $text = strtolower($bodyText . ' ' . strip_tags($bodyHtml));
        
        // Check for spam keywords
        foreach ($this->spamKeywords as $keyword) {
            $count = substr_count($text, $keyword);
            if ($count > 0) {
                $score += min($count * 0.5, 2.0);
            }
        }
        
        // Short body (common in spam)
        if (strlen($text) < 50) {
            $score += 0.5;
        }
        
        // Excessive capitalization
        $caps = preg_match_all('/[A-Z]/', $bodyText);
        $total = strlen($bodyText);
        if ($total > 0 && ($caps / $total) > 0.5) {
            $score += 1.0;
        }
        
        // Hidden text in HTML (white on white, font size 0)
        if (preg_match('/color:\s*#?fff|font-size:\s*0/', $bodyHtml)) {
            $score += 2.0;
        }
        
        return $score;
    }
    
    /**
     * Check sender for spam indicators
     */
    private function checkFrom($from)
    {
        $score = 0.0;
        $email = $from['email'] ?? '';
        $name = $from['name'] ?? '';
        
        // No display name
        if (empty($name)) {
            $score += 0.3;
        }
        
        // Display name contains email address
        if (!empty($name) && strpos($name, '@') !== false) {
            $score += 0.5;
        }
        
        // Free email provider (common in spam)
        $freeProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com'];
        $domain = substr(strrchr($email, '@'), 1);
        if (in_array(strtolower($domain), $freeProviders)) {
            $score += 0.2;
        }
        
        // Suspicious TLDs
        $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.gq', '.cn', '.ru'];
        foreach ($suspiciousTlds as $tld) {
            if (substr($email, -strlen($tld)) === $tld) {
                $score += 1.0;
                break;
            }
        }
        
        return $score;
    }
    
    /**
     * Check email headers for spam indicators
     */
    private function checkHeaders($email)
    {
        $score = 0.0;
        
        // Missing or invalid Message-ID
        if (empty($email['message_id'])) {
            $score += 0.5;
        }
        
        // Missing Date header
        if (empty($email['date'])) {
            $score += 0.3;
        }
        
        return $score;
    }
    
    /**
     * Check links in email body
     */
    private function checkLinks($bodyHtml)
    {
        $score = 0.0;
        
        // Extract all links
        preg_match_all('/<a[^>]+href=["\'](.*?)["\']/', $bodyHtml, $matches);
        $links = $matches[1] ?? [];
        
        // Too many links
        if (count($links) > 10) {
            $score += 1.0;
        }
        
        // Check for suspicious URLs
        foreach ($links as $link) {
            // IP address in URL
            if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $link)) {
                $score += 1.0;
            }
            
            // URL shorteners
            $shorteners = ['bit.ly', 'tinyurl.com', 'goo.gl', 't.co'];
            foreach ($shorteners as $shortener) {
                if (stripos($link, $shortener) !== false) {
                    $score += 0.5;
                    break;
                }
            }
            
            // Suspicious TLDs in links
            $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.gq'];
            foreach ($suspiciousTlds as $tld) {
                if (stripos($link, $tld) !== false) {
                    $score += 0.8;
                    break;
                }
            }
        }
        
        return $score;
    }
    
    /**
     * Check attachments for spam indicators
     */
    private function checkAttachments($attachments)
    {
        $score = 0.0;
        
        if (empty($attachments)) {
            return 0.0;
        }
        
        // Suspicious file extensions
        $suspiciousExtensions = ['.exe', '.scr', '.bat', '.com', '.pif', '.vbs', '.js'];
        
        foreach ($attachments as $attachment) {
            $filename = strtolower($attachment['filename'] ?? '');
            
            foreach ($suspiciousExtensions as $ext) {
                if (substr($filename, -strlen($ext)) === $ext) {
                    $score += 3.0;
                    break;
                }
            }
            
            // Double extension (e.g., .pdf.exe)
            if (preg_match('/\.[a-z]{2,4}\.[a-z]{2,4}$/', $filename)) {
                $score += 2.0;
            }
        }
        
        return $score;
    }
    
    /**
     * Build email message for external filters
     */
    private function buildEmailMessage($email)
    {
        $message = '';
        $message .= "From: " . ($email['from']['email'] ?? '') . "\r\n";
        $message .= "To: " . ($email['to'] ?? '') . "\r\n";
        $message .= "Subject: " . ($email['subject'] ?? '') . "\r\n";
        $message .= "Date: " . ($email['date'] ?? date('r')) . "\r\n";
        $message .= "Message-ID: " . ($email['message_id'] ?? '') . "\r\n";
        $message .= "\r\n";
        $message .= $email['body_text'] ?? '';
        
        return $message;
    }
    
    /**
     * Check if email is in whitelist
     */
    public function isWhitelisted($email, $mailboxId, $db)
    {
        $fromEmail = $email['from']['email'] ?? '';
        
        if (empty($fromEmail)) {
            return false;
        }
        
        $result = $db->fetch(
            "SELECT id FROM mail_spam_whitelist 
             WHERE mailbox_id = ? AND email = ?",
            [$mailboxId, $fromEmail]
        );
        
        return !empty($result);
    }
    
    /**
     * Check if email is in blacklist
     */
    public function isBlacklisted($email, $mailboxId, $db)
    {
        $fromEmail = $email['from']['email'] ?? '';
        
        if (empty($fromEmail)) {
            return false;
        }
        
        $result = $db->fetch(
            "SELECT id FROM mail_spam_blacklist 
             WHERE mailbox_id = ? AND email = ?",
            [$mailboxId, $fromEmail]
        );
        
        return !empty($result);
    }
}
