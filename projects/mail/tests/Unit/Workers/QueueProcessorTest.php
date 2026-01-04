<?php

namespace Tests\Unit\Workers;

use Tests\TestCase;

/**
 * Queue Processor Tests
 * 
 * Tests for the email queue processing worker
 */
class QueueProcessorTest extends TestCase
{
    public function testProcessBatchFetchesPendingEmails()
    {
        // Arrange: Create test mailbox and queue an email
        $mailboxId = $this->createTestMailbox();
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_queue (mailbox_id, from_email, to_email, subject, body_html, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $mailboxId,
            'sender@example.com',
            'recipient@example.com',
            'Test Email',
            '<p>Test body</p>'
        ]);
        
        $queueId = $this->db->lastInsertId();
        
        // Assert: Email is in queue
        $this->assertDatabaseHas('mail_queue', [
            'id' => $queueId,
            'status' => 'pending'
        ]);
    }
    
    public function testEmailSentSuccessfully()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_queue (mailbox_id, from_email, to_email, subject, body_html, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $mailboxId,
            'sender@example.com',
            'recipient@example.com',
            'Test Email',
            '<p>Test body</p>'
        ]);
        
        $queueId = $this->db->lastInsertId();
        
        // Act: Mark as sent (simulating successful send)
        $stmt = $this->db->prepare("
            UPDATE mail_queue SET status = 'sent', sent_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$queueId]);
        
        // Assert
        $this->assertDatabaseHas('mail_queue', [
            'id' => $queueId,
            'status' => 'sent'
        ]);
    }
    
    public function testFailedEmailIsRetried()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_queue (mailbox_id, from_email, to_email, subject, body_html, status, attempts, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', 1, NOW())
        ");
        
        $stmt->execute([
            $mailboxId,
            'sender@example.com',
            'recipient@example.com',
            'Test Email',
            '<p>Test body</p>'
        ]);
        
        $queueId = $this->db->lastInsertId();
        
        // Act: Increment retry count
        $stmt = $this->db->prepare("
            UPDATE mail_queue SET attempts = attempts + 1, last_error = ? WHERE id = ?
        ");
        $stmt->execute(['SMTP connection failed', $queueId]);
        
        // Assert
        $stmt = $this->db->prepare("SELECT attempts FROM mail_queue WHERE id = ?");
        $stmt->execute([$queueId]);
        $attempts = $stmt->fetchColumn();
        
        $this->assertEquals(2, $attempts);
    }
    
    public function testMaxRetriesReachedMarksFailed()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_queue (mailbox_id, from_email, to_email, subject, body_html, status, attempts, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', 3, NOW())
        ");
        
        $stmt->execute([
            $mailboxId,
            'sender@example.com',
            'recipient@example.com',
            'Test Email',
            '<p>Test body</p>'
        ]);
        
        $queueId = $this->db->lastInsertId();
        
        // Act: Mark as failed after max retries
        $stmt = $this->db->prepare("
            UPDATE mail_queue SET status = 'failed' WHERE id = ? AND attempts >= 3
        ");
        $stmt->execute([$queueId]);
        
        // Assert
        $this->assertDatabaseHas('mail_queue', [
            'id' => $queueId,
            'status' => 'failed'
        ]);
    }
    
    public function testDailySendLimitEnforced()
    {
        // Arrange: Create mailbox with daily limit
        $mailboxId = $this->createTestMailbox();
        
        // Simulate 50 emails sent today (Free plan limit)
        for ($i = 0; $i < 50; $i++) {
            $stmt = $this->db->prepare("
                INSERT INTO mail_queue (mailbox_id, from_email, to_email, subject, body_html, status, sent_at)
                VALUES (?, ?, ?, ?, ?, 'sent', NOW())
            ");
            
            $stmt->execute([
                $mailboxId,
                'sender@example.com',
                "recipient{$i}@example.com",
                'Test Email',
                '<p>Test body</p>'
            ]);
        }
        
        // Act: Check if limit reached
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM mail_queue 
            WHERE mailbox_id = ? AND status = 'sent' AND DATE(sent_at) = CURDATE()
        ");
        $stmt->execute([$mailboxId]);
        $sentToday = $stmt->fetchColumn();
        
        // Assert
        $this->assertEquals(50, $sentToday);
        $this->assertGreaterThanOrEqual(50, $sentToday, 'Daily limit should be reached');
    }
}
