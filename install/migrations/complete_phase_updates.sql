-- =====================================================
-- MMB Platform - Complete Phase Updates (Phases 4-12)
-- =====================================================
-- This file contains all database table additions from
-- completed implementation phases.
-- Run this AFTER the main schema.sql
-- =====================================================

-- =====================================================
-- Phase 9: Email & Notification System Tables
-- =====================================================

-- Email queue table
CREATE TABLE IF NOT EXISTS email_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL,
    cc VARCHAR(512) NULL,
    bcc VARCHAR(512) NULL,
    reply_to VARCHAR(255) NULL,
    attachments JSON NULL,
    priority TINYINT DEFAULT 5,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification preferences
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    email_enabled TINYINT(1) DEFAULT 1,
    sms_enabled TINYINT(1) DEFAULT 0,
    push_enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_user_type (user_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Phase 11: API Development Tables
-- =====================================================

-- API keys
CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    api_key VARCHAR(100) NOT NULL UNIQUE,
    permissions JSON NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    revoked_at TIMESTAMP NULL,
    request_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_key (api_key),
    INDEX idx_user_id (user_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API request logs (optional, for analytics)
CREATE TABLE IF NOT EXISTS api_request_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT UNSIGNED NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    status_code INT NOT NULL,
    response_time INT NOT NULL COMMENT 'Milliseconds',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_key_id (api_key_id),
    INDEX idx_endpoint (endpoint),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Phase 7: Advanced ProShare Features Tables
-- =====================================================

-- Analytics events (for all projects)
CREATE TABLE IF NOT EXISTS analytics_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project VARCHAR(50) NOT NULL COMMENT 'proshare, codexpro, imgtxt',
    resource_type VARCHAR(50) NOT NULL COMMENT 'file, code, image',
    resource_id INT UNSIGNED NOT NULL,
    event_type VARCHAR(50) NOT NULL COMMENT 'view, download, share, edit',
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    browser VARCHAR(50) NULL,
    platform VARCHAR(50) NULL,
    country VARCHAR(2) NULL COMMENT 'ISO country code',
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_resource (project, resource_type, resource_id),
    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Custom short links
CREATE TABLE IF NOT EXISTS custom_short_links (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    project VARCHAR(50) NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    custom_slug VARCHAR(100) NOT NULL UNIQUE,
    original_slug VARCHAR(100) NOT NULL,
    clicks INT UNSIGNED DEFAULT 0,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_custom_slug (custom_slug),
    INDEX idx_user_id (user_id),
    INDEX idx_resource (project, resource_type, resource_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Phase 5: Advanced CodeXPro Features Tables
-- =====================================================
-- Note: Apply to CodeXPro database (configured in admin panel)

-- Multi-file project support
-- Run this on the CodeXPro project database
-- CREATE TABLE IF NOT EXISTS project_files (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     project_id INT UNSIGNED NOT NULL,
--     file_path VARCHAR(255) NOT NULL,
--     file_content LONGTEXT NOT NULL,
--     file_size INT UNSIGNED DEFAULT 0,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     UNIQUE KEY idx_project_file (project_id, file_path),
--     INDEX idx_project_id (project_id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project folders
-- CREATE TABLE IF NOT EXISTS project_folders (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     project_id INT UNSIGNED NOT NULL,
--     folder_path VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     UNIQUE KEY idx_project_folder (project_id, folder_path)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User templates (in main database)
CREATE TABLE IF NOT EXISTS user_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'custom',
    project_type VARCHAR(50) NOT NULL DEFAULT 'codexpro',
    is_public TINYINT(1) DEFAULT 0,
    downloads INT UNSIGNED DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_category (category),
    INDEX idx_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Template files
CREATE TABLE IF NOT EXISTS template_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_content LONGTEXT NOT NULL,
    FOREIGN KEY (template_id) REFERENCES user_templates(id) ON DELETE CASCADE,
    INDEX idx_template_id (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Phase 6: Advanced ImgTxt Features Tables
-- =====================================================
-- Note: Apply to ImgTxt database (configured in admin panel)

-- Batch job tracking (in main database)
CREATE TABLE IF NOT EXISTS ocr_batch_jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total_files INT NOT NULL,
    processed_files INT DEFAULT 0,
    successful_files INT DEFAULT 0,
    failed_files INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'completed_with_errors', 'failed') DEFAULT 'pending',
    options JSON NULL COMMENT 'OCR options: language, preprocess, etc',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batch job files
CREATE TABLE IF NOT EXISTS ocr_batch_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(512) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    result_text LONGTEXT NULL,
    confidence DECIMAL(5,2) NULL COMMENT 'OCR confidence 0-100',
    has_tables TINYINT(1) DEFAULT 0,
    tables_data JSON NULL,
    processing_time INT UNSIGNED NULL COMMENT 'Seconds',
    error_message TEXT NULL,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (job_id) REFERENCES ocr_batch_jobs(id) ON DELETE CASCADE,
    INDEX idx_job_id (job_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OCR history (in main database)
CREATE TABLE IF NOT EXISTS ocr_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(512) NOT NULL,
    file_type VARCHAR(50) NOT NULL COMMENT 'image/jpeg, application/pdf, etc',
    language VARCHAR(10) NOT NULL DEFAULT 'eng',
    confidence DECIMAL(5,2) NULL,
    has_tables TINYINT(1) DEFAULT 0,
    page_count INT DEFAULT 1,
    preprocessing_applied TINYINT(1) DEFAULT 0,
    processing_time INT UNSIGNED NULL COMMENT 'Seconds',
    result_size INT UNSIGNED NULL COMMENT 'Bytes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_language (language),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- System Configuration Tables
-- =====================================================

-- System settings (for caching config, websocket config, etc)
CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type VARCHAR(20) DEFAULT 'string' COMMENT 'string, json, boolean, integer',
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feature flags
CREATE TABLE IF NOT EXISTS feature_flags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feature_name VARCHAR(100) NOT NULL UNIQUE,
    is_enabled TINYINT(1) DEFAULT 0,
    description TEXT NULL,
    rollout_percentage INT DEFAULT 0 COMMENT '0-100',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_feature (feature_name),
    INDEX idx_enabled (is_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insert Default System Settings
-- =====================================================

-- WebSocket settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('websocket_enabled', 'true', 'boolean', 'Enable WebSocket real-time features'),
('websocket_host', '0.0.0.0', 'string', 'WebSocket server host'),
('websocket_port', '8080', 'integer', 'WebSocket server port'),
('websocket_url', 'ws://localhost:8080', 'string', 'WebSocket client connection URL')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Cache settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('cache_enabled', 'true', 'boolean', 'Enable file-based caching'),
('cache_default_ttl', '3600', 'integer', 'Default cache TTL in seconds'),
('cache_driver', 'file', 'string', 'Cache driver: file, redis, memcached')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Email settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('email_queue_enabled', 'true', 'boolean', 'Enable email queue'),
('email_queue_batch_size', '50', 'integer', 'Number of emails to process per batch'),
('email_queue_retry_attempts', '3', 'integer', 'Max retry attempts for failed emails')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- API settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('api_enabled', 'true', 'boolean', 'Enable REST API'),
('api_rate_limit_minute', '60', 'integer', 'API requests per minute'),
('api_rate_limit_hour', '1000', 'integer', 'API requests per hour'),
('api_rate_limit_day', '10000', 'integer', 'API requests per day')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Analytics settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('analytics_enabled', 'true', 'boolean', 'Enable analytics tracking'),
('analytics_retention_days', '90', 'integer', 'Days to retain analytics data')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- =====================================================
-- Insert Default Feature Flags
-- =====================================================

INSERT INTO feature_flags (feature_name, is_enabled, description) VALUES
('realtime_collaboration', 1, 'Enable real-time collaboration in CodeXPro'),
('batch_ocr_processing', 1, 'Enable batch OCR processing in ImgTxt'),
('advanced_sharing', 1, 'Enable advanced sharing features in ProShare'),
('api_access', 1, 'Enable REST API access'),
('email_notifications', 1, 'Enable email notifications'),
('analytics_tracking', 1, 'Enable analytics and usage tracking'),
('custom_templates', 1, 'Enable custom code templates'),
('table_detection', 1, 'Enable table detection in OCR'),
('pdf_processing', 1, 'Enable multi-page PDF processing')
ON DUPLICATE KEY UPDATE is_enabled=VALUES(is_enabled);

-- =====================================================
-- Performance Indexes (from Phase 8)
-- =====================================================
-- Already applied via phase8_database_optimization.sql
-- Included here for reference

-- Additional indexes for new tables
ALTER TABLE analytics_events ADD INDEX idx_project_created (project, created_at);
ALTER TABLE api_request_logs ADD INDEX idx_status_created (status_code, created_at);
ALTER TABLE ocr_history ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE notifications ADD INDEX idx_user_read_created (user_id, is_read, created_at);

-- =====================================================
-- Completion Message
-- =====================================================
SELECT 'Phase 4-12 Database Updates Applied Successfully!' AS message;
SELECT COUNT(*) AS total_tables FROM information_schema.tables 
WHERE table_schema = DATABASE() AS summary;
