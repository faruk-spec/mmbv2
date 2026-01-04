<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base Test Case
 * 
 * Provides common functionality for all tests including:
 * - Database setup and teardown
 * - Test data creation helpers
 * - Common assertions
 * - Mocking utilities
 */
abstract class TestCase extends BaseTestCase
{
    protected $db;
    protected $testSubscriberId;
    protected $testMailboxId;
    
    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize test database connection
        $this->db = new \PDO(
            'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        
        // Begin transaction for test isolation
        $this->db->beginTransaction();
    }
    
    /**
     * Teardown after each test
     */
    protected function tearDown(): void
    {
        // Rollback transaction to reset database state
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        
        parent::tearDown();
    }
    
    /**
     * Create a test subscriber
     */
    protected function createTestSubscriber(array $data = []): int
    {
        $defaults = [
            'user_id' => 1,
            'company_name' => 'Test Company',
            'status' => 'active',
            'users_count' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data = array_merge($defaults, $data);
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_subscribers (user_id, company_name, status, users_count, created_at)
            VALUES (:user_id, :company_name, :status, :users_count, :created_at)
        ");
        
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Create a test mailbox
     */
    protected function createTestMailbox(array $data = []): int
    {
        if (!$this->testSubscriberId) {
            $this->testSubscriberId = $this->createTestSubscriber();
        }
        
        $defaults = [
            'subscriber_id' => $this->testSubscriberId,
            'email' => 'test' . rand(1000, 9999) . '@example.com',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
            'role_type' => 'end_user',
            'is_active' => 1,
            'storage_quota_mb' => 1024,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data = array_merge($defaults, $data);
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_mailboxes (subscriber_id, email, password_hash, role_type, is_active, storage_quota_mb, created_at)
            VALUES (:subscriber_id, :email, :password_hash, :role_type, :is_active, :storage_quota_mb, :created_at)
        ");
        
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Create a test domain
     */
    protected function createTestDomain(array $data = []): int
    {
        if (!$this->testSubscriberId) {
            $this->testSubscriberId = $this->createTestSubscriber();
        }
        
        $defaults = [
            'subscriber_id' => $this->testSubscriberId,
            'domain_name' => 'test' . rand(1000, 9999) . '.example.com',
            'is_verified' => 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data = array_merge($defaults, $data);
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_domains (subscriber_id, domain_name, is_verified, is_active, created_at)
            VALUES (:subscriber_id, :domain_name, :is_verified, :is_active, :created_at)
        ");
        
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Create a test email message
     */
    protected function createTestMessage(array $data = []): int
    {
        if (!$this->testMailboxId) {
            $this->testMailboxId = $this->createTestMailbox();
        }
        
        $defaults = [
            'mailbox_id' => $this->testMailboxId,
            'folder_id' => 1, // Inbox
            'from_email' => 'sender@example.com',
            'to_email' => 'recipient@example.com',
            'subject' => 'Test Email',
            'body_html' => '<p>Test email body</p>',
            'body_text' => 'Test email body',
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $data = array_merge($defaults, $data);
        
        $stmt = $this->db->prepare("
            INSERT INTO mail_messages (mailbox_id, folder_id, from_email, to_email, subject, body_html, body_text, is_read, created_at)
            VALUES (:mailbox_id, :folder_id, :from_email, :to_email, :subject, :body_html, :body_text, :is_read, :created_at)
        ");
        
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Assert that a database record exists
     */
    protected function assertDatabaseHas(string $table, array $conditions): void
    {
        $where = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $count = $stmt->fetchColumn();
        
        $this->assertGreaterThan(0, $count, "Failed asserting that table '{$table}' contains matching record.");
    }
    
    /**
     * Assert that a database record does not exist
     */
    protected function assertDatabaseMissing(string $table, array $conditions): void
    {
        $where = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(0, $count, "Failed asserting that table '{$table}' does not contain matching record.");
    }
    
    /**
     * Get test fixture data
     */
    protected function getFixture(string $name): array
    {
        $path = __DIR__ . "/fixtures/{$name}.json";
        
        if (!file_exists($path)) {
            throw new \Exception("Fixture file not found: {$path}");
        }
        
        return json_decode(file_get_contents($path), true);
    }
    
    /**
     * Mock HTTP request
     */
    protected function mockRequest(string $method, string $uri, array $data = []): array
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        
        if ($method === 'POST') {
            $_POST = $data;
        } else {
            $_GET = $data;
        }
        
        // Capture output
        ob_start();
        
        // Execute request (simplified - actual implementation would route through your framework)
        $response = [
            'status' => 200,
            'headers' => [],
            'body' => ''
        ];
        
        $output = ob_get_clean();
        $response['body'] = $output;
        
        return $response;
    }
}
