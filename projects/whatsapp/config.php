<?php
/**
 * WhatsApp API Automation Configuration
 * 
 * @package MMB\Projects\WhatsApp
 */

return [
    'name' => 'WhatsApp API Automation',
    'version' => '1.0.0',
    'description' => 'SaaS-based WhatsApp API automation platform',
    
    // Database configuration
    'database' => 'mmb_whatsapp',
    
    // Session settings
    'session_timeout' => 3600, // 1 hour
    'max_sessions_per_user' => 5,
    
    // API settings
    'api_rate_limit' => 100, // requests per minute
    'api_key_length' => 32,
    
    // WhatsApp settings
    'qr_timeout' => 60, // seconds
    'reconnect_attempts' => 3,
    'message_queue_limit' => 1000,
    
    // Media settings
    'max_file_size' => 16 * 1024 * 1024, // 16MB
    'allowed_media_types' => ['image', 'video', 'audio', 'document'],
    
    // Webhook settings
    'webhook_enabled' => true,
    'webhook_retry_attempts' => 3,
];
