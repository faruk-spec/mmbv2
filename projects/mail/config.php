<?php
/**
 * Mail Hosting Server Project Configuration
 * 
 * @package MMB\Projects\Mail
 */

// Load main database configuration
$mainConfig = require dirname(dirname(__DIR__)) . '/config/database.php';

return [
    'name' => 'Mail Server',
    'version' => '1.0.0',
    'description' => 'Complete Mail Hosting Server SaaS Platform',
    
    'database' => [
        'host' => $mainConfig['host'] ?? 'localhost',
        'port' => $mainConfig['port'] ?? '3306',
        'database' => 'mail_server',
        'username' => $mainConfig['username'] ?? 'testuser',
        'password' => $mainConfig['password'] ?? 'testuser',
    ],
    
    'features' => [
        // SMTP Configuration
        'smtp_host' => 'localhost',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'smtp_auth_required' => true,
        
        // IMAP Configuration
        'imap_host' => 'localhost',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        
        // POP3 Configuration
        'pop3_host' => 'localhost',
        'pop3_port' => 995,
        'pop3_encryption' => 'ssl',
        
        // Mail Limits
        'max_mailbox_size' => '5GB',
        'max_message_size' => '25MB',
        'max_attachment_size' => '25MB',
        'max_recipients_per_message' => 100,
        'daily_sending_limit' => 1000,
        
        // Features
        'custom_domains' => true,
        'domain_verification' => true,
        'spam_filtering' => true,
        'antivirus_scanning' => true,
        'email_encryption' => true,
        'auto_responder' => true,
        'email_forwarding' => true,
        'email_aliases' => true,
        'webmail_interface' => true,
        'calendar_integration' => false,
        'contacts_management' => true,
        
        // Security
        'dkim_signing' => true,
        'spf_validation' => true,
        'dmarc_policy' => true,
        'two_factor_auth' => true,
        'rate_limiting' => true,
        'brute_force_protection' => true,
        
        // Storage
        'storage_backend' => 'local', // local, s3, etc.
        'storage_path' => BASE_PATH . '/storage/mail',
        'backup_enabled' => true,
        'backup_retention_days' => 30,
    ],
    
    'dns' => [
        // DNS Records for domain setup
        'mx_priority' => 10,
        'spf_record' => 'v=spf1 mx ~all',
        'dmarc_policy' => 'v=DMARC1; p=quarantine; rua=mailto:postmaster@example.com',
    ],
];
