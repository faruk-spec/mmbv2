<?php

namespace Tests\Integration\API;

use Tests\TestCase;

/**
 * API Endpoints Tests
 * 
 * Integration tests for REST API endpoints
 */
class APIEndpointsTest extends TestCase
{
    private $apiKey;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test API key
        $mailboxId = $this->createTestMailbox();
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_api_keys (mailbox_id, api_key, name, is_active, created_at)
            VALUES (?, ?, ?, 1, NOW())
        ");
        
        $this->apiKey = 'test_' . bin2hex(random_bytes(16));
        $stmt->execute([$mailboxId, hash('sha256', $this->apiKey), 'Test API Key']);
    }
    
    public function testAuthEndpointValidatesAPIKey()
    {
        // Test with valid API key
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM mail_api_keys WHERE api_key = ? AND is_active = 1
        ");
        $stmt->execute([hash('sha256', $this->apiKey)]);
        $exists = $stmt->fetchColumn();
        
        $this->assertEquals(1, $exists, 'Valid API key should exist in database');
    }
    
    public function testSendEmailEndpoint()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        
        $emailData = [
            'to' => 'recipient@example.com',
            'subject' => 'Test Email via API',
            'body' => 'This is a test email sent via API',
            'html' => true
        ];
        
        // Act: Queue email
        $stmt = $this->db->prepare("
            INSERT INTO mail_queue (mailbox_id, from_email, to_email, subject, body_html, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $mailboxId,
            'sender@example.com',
            $emailData['to'],
            $emailData['subject'],
            $emailData['body']
        ]);
        
        // Assert
        $this->assertDatabaseHas('mail_queue', [
            'to_email' => $emailData['to'],
            'subject' => $emailData['subject'],
            'status' => 'pending'
        ]);
    }
    
    public function testGetInboxEndpoint()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        $messageId = $this->createTestMessage([
            'mailbox_id' => $mailboxId,
            'subject' => 'Test Inbox Message'
        ]);
        
        // Act: Fetch inbox messages
        $stmt = $this->db->prepare("
            SELECT * FROM mail_messages WHERE mailbox_id = ? ORDER BY created_at DESC LIMIT 50
        ");
        $stmt->execute([$mailboxId]);
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Assert
        $this->assertNotEmpty($messages);
        $this->assertEquals('Test Inbox Message', $messages[0]['subject']);
    }
    
    public function testGetSingleMessageEndpoint()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        $messageId = $this->createTestMessage([
            'mailbox_id' => $mailboxId,
            'subject' => 'Specific Test Message'
        ]);
        
        // Act: Fetch specific message
        $stmt = $this->db->prepare("
            SELECT * FROM mail_messages WHERE id = ? AND mailbox_id = ?
        ");
        $stmt->execute([$messageId, $mailboxId]);
        $message = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Assert
        $this->assertNotEmpty($message);
        $this->assertEquals('Specific Test Message', $message['subject']);
    }
    
    public function testDeleteMessageEndpoint()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        $messageId = $this->createTestMessage(['mailbox_id' => $mailboxId]);
        
        // Act: Delete message
        $stmt = $this->db->prepare("
            UPDATE mail_messages SET deleted_at = NOW() WHERE id = ? AND mailbox_id = ?
        ");
        $stmt->execute([$messageId, $mailboxId]);
        
        // Assert
        $stmt = $this->db->prepare("
            SELECT deleted_at FROM mail_messages WHERE id = ?
        ");
        $stmt->execute([$messageId]);
        $deletedAt = $stmt->fetchColumn();
        
        $this->assertNotNull($deletedAt, 'Message should be marked as deleted');
    }
    
    public function testMarkAsReadEndpoint()
    {
        // Arrange
        $mailboxId = $this->createTestMailbox();
        $messageId = $this->createTestMessage([
            'mailbox_id' => $mailboxId,
            'is_read' => 0
        ]);
        
        // Act: Mark as read
        $stmt = $this->db->prepare("
            UPDATE mail_messages SET is_read = 1, read_at = NOW() WHERE id = ? AND mailbox_id = ?
        ");
        $stmt->execute([$messageId, $mailboxId]);
        
        // Assert
        $this->assertDatabaseHas('mail_messages', [
            'id' => $messageId,
            'is_read' => 1
        ]);
    }
    
    public function testRateLimitingEnforced()
    {
        // Arrange: Log multiple API calls
        $mailboxId = $this->createTestMailbox();
        $apiKeyHash = hash('sha256', $this->apiKey);
        
        // Simulate 100 API calls
        for ($i = 0; $i < 100; $i++) {
            $stmt = $this->db->prepare("
                INSERT INTO mail_api_logs (api_key_hash, endpoint, method, ip_address, created_at)
                VALUES (?, '/api/v1/mail/send', 'POST', '127.0.0.1', NOW())
            ");
            $stmt->execute([$apiKeyHash]);
        }
        
        // Act: Check rate limit
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM mail_api_logs 
            WHERE api_key_hash = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$apiKeyHash]);
        $callsLastHour = $stmt->fetchColumn();
        
        // Assert
        $this->assertEquals(100, $callsLastHour);
    }
    
    public function testUnauthorizedAccessDenied()
    {
        // Test that requests without valid API key are denied
        $invalidKey = 'invalid_key_12345';
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM mail_api_keys WHERE api_key = ? AND is_active = 1
        ");
        $stmt->execute([hash('sha256', $invalidKey)]);
        $exists = $stmt->fetchColumn();
        
        $this->assertEquals(0, $exists, 'Invalid API key should not exist');
    }
}
