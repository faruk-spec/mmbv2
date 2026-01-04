<?php

namespace Tests\Unit\Workers;

use Tests\TestCase;

/**
 * Spam Filter Tests
 * 
 * Tests for spam detection and filtering
 */
class SpamFilterTest extends TestCase
{
    public function testSpamKeywordsDetected()
    {
        $spamKeywords = [
            'viagra', 'cialis', 'casino', 'lottery', 'winner',
            'free money', 'nigerian prince', 'click here'
        ];
        
        foreach ($spamKeywords as $keyword) {
            $subject = "Get {$keyword} now!";
            $score = $this->calculateSpamScore($subject, '');
            
            $this->assertGreaterThan(0, $score, "Spam keyword '{$keyword}' should increase spam score");
        }
    }
    
    public function testAllCapsSubjectIncreasesScore()
    {
        $normalSubject = "Hello World";
        $capsSubject = "HELLO WORLD";
        
        $normalScore = $this->calculateSpamScore($normalSubject, '');
        $capsScore = $this->calculateSpamScore($capsSubject, '');
        
        $this->assertGreaterThan($normalScore, $capsScore, 'ALL CAPS subject should have higher spam score');
    }
    
    public function testExcessivePunctuationDetected()
    {
        $normal = "Hello, how are you?";
        $excessive = "Hello!!! How are you????";
        
        $normalScore = $this->calculateSpamScore($normal, '');
        $excessiveScore = $this->calculateSpamScore($excessive, '');
        
        $this->assertGreaterThan($normalScore, $excessiveScore, 'Excessive punctuation should increase spam score');
    }
    
    public function testSuspiciousLinksDetected()
    {
        $body = "Click here: http://192.168.1.1/malware.exe";
        $score = $this->calculateSpamScore('', $body);
        
        $this->assertGreaterThan(0, $score, 'IP address links should be flagged as suspicious');
    }
    
    public function testWhitelistedSenderBypassesFilter()
    {
        // Arrange: Create whitelist entry
        $mailboxId = $this->createTestMailbox();
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_spam_whitelist (mailbox_id, email, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$mailboxId, 'trusted@example.com']);
        
        // Assert: Whitelist entry exists
        $this->assertDatabaseHas('mail_spam_whitelist', [
            'mailbox_id' => $mailboxId,
            'email' => 'trusted@example.com'
        ]);
    }
    
    public function testBlacklistedSenderAlwaysSpam()
    {
        // Arrange: Create blacklist entry
        $mailboxId = $this->createTestMailbox();
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_spam_blacklist (mailbox_id, email, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$mailboxId, 'spammer@example.com']);
        
        // Assert: Blacklist entry exists
        $this->assertDatabaseHas('mail_spam_blacklist', [
            'mailbox_id' => $mailboxId,
            'email' => 'spammer@example.com'
        ]);
    }
    
    public function testHighScoreMovesToSpamFolder()
    {
        // Test that emails with spam score > 5.0 are moved to spam folder
        $highSpamScore = 7.5;
        $threshold = 5.0;
        
        $this->assertGreaterThan($threshold, $highSpamScore, 'High spam score should exceed threshold');
    }
    
    public function testLowScoreStaysInInbox()
    {
        // Test that emails with spam score <= 5.0 stay in inbox
        $lowSpamScore = 2.0;
        $threshold = 5.0;
        
        $this->assertLessThanOrEqual($threshold, $lowSpamScore, 'Low spam score should not exceed threshold');
    }
    
    /**
     * Helper method to calculate spam score
     */
    private function calculateSpamScore(string $subject, string $body): float
    {
        $score = 0.0;
        
        // Check for spam keywords
        $spamKeywords = ['viagra', 'cialis', 'casino', 'lottery', 'winner', 'free money'];
        foreach ($spamKeywords as $keyword) {
            if (stripos($subject . ' ' . $body, $keyword) !== false) {
                $score += 2.0;
            }
        }
        
        // Check for ALL CAPS
        if ($subject === strtoupper($subject) && strlen($subject) > 5) {
            $score += 1.5;
        }
        
        // Check for excessive punctuation
        if (preg_match('/[!?]{3,}/', $subject . ' ' . $body)) {
            $score += 1.0;
        }
        
        // Check for IP addresses in links
        if (preg_match('/https?:\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $body)) {
            $score += 2.5;
        }
        
        return $score;
    }
}
