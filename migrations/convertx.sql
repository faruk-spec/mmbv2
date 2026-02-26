-- ConvertX Database Schema
-- AI-powered document conversion platform
--
-- Run against the application's main database (same DB as qr_codes, users, etc.).

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ------------------------------------------------------------------ --
--  Conversion Jobs                                                     --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS convertx_jobs (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED       NOT NULL,

    -- Input
    input_path       VARCHAR(512)       NOT NULL,
    input_filename   VARCHAR(255)       NOT NULL DEFAULT '',
    input_format     VARCHAR(20)        NOT NULL DEFAULT '',

    -- Output
    output_format    VARCHAR(20)        NOT NULL DEFAULT '',
    output_path      VARCHAR(512)       NULL,
    output_filename  VARCHAR(255)       NULL,

    -- Configuration
    options          JSON               NULL COMMENT 'Conversion options: quality, dpi, etc.',
    ai_tasks         JSON               NULL COMMENT 'Requested AI tasks: ocr, summarize, translate:fr, classify',
    webhook_url      VARCHAR(2048)      NULL,
    batch_id         VARCHAR(64)        NULL     COMMENT 'Groups jobs from the same batch upload',
    plan_tier        VARCHAR(20)        NOT NULL DEFAULT 'free',

    -- Status
    status           ENUM(
                         'pending',
                         'processing',
                         'completed',
                         'failed',
                         'cancelled'
                     ) NOT NULL DEFAULT 'pending',
    retry_count      TINYINT UNSIGNED   NOT NULL DEFAULT 0,
    error_message    TEXT               NULL,

    -- AI results
    ai_result        JSON               NULL,
    provider_used    VARCHAR(50)        NULL,
    tokens_used      INT UNSIGNED       NOT NULL DEFAULT 0,

    -- Timestamps
    created_at       DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at       DATETIME           NULL,
    completed_at     DATETIME           NULL,
    updated_at       DATETIME           NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_id   (user_id),
    INDEX idx_status    (status),
    INDEX idx_batch_id  (batch_id),
    INDEX idx_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------ --
--  AI Providers                                                        --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS convertx_ai_providers (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100)  NOT NULL,
    slug                VARCHAR(50)   NOT NULL UNIQUE  COMMENT 'e.g. openai, huggingface, tesseract',
    base_url            VARCHAR(512)  NULL,
    api_key             VARCHAR(512)  NULL             COMMENT 'Stored encrypted in production',
    model               VARCHAR(100)  NULL             COMMENT 'Default model for this provider',
    capabilities        JSON          NOT NULL         COMMENT 'Array: ["ocr","summarization","translation","classification"]',
    allowed_tiers       JSON          NOT NULL         COMMENT 'Array: ["free","pro","enterprise"]',
    priority            TINYINT       NOT NULL DEFAULT 10 COMMENT 'Lower = higher priority in routing',
    cost_per_1k_tokens  DECIMAL(10,6) NOT NULL DEFAULT 0.002000,
    is_active           TINYINT(1)    NOT NULL DEFAULT 1,
    is_healthy          TINYINT(1)    NOT NULL DEFAULT 1,
    total_tokens_used   BIGINT UNSIGNED NOT NULL DEFAULT 0,
    total_cost_usd      DECIMAL(12,4)   NOT NULL DEFAULT 0,
    last_used_at        DATETIME      NULL,
    health_checked_at   DATETIME      NULL,
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_active_priority (is_active, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------ --
--  Provider Usage Log                                                  --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS convertx_provider_usage (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id INT UNSIGNED    NOT NULL,
    tokens_used INT UNSIGNED    NOT NULL DEFAULT 0,
    cost_usd    DECIMAL(10,6)   NOT NULL DEFAULT 0,
    recorded_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_provider  (provider_id),
    INDEX idx_recorded  (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------ --
--  ConvertX API Keys                                                  --
--  Separate from the platform's api_keys table to avoid schema       --
--  conflicts (platform table has extra NOT NULL columns).             --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS convertx_api_keys (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED  NOT NULL,
    api_key    VARCHAR(100)  NOT NULL UNIQUE,
    is_active  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------ --
--  ConvertX User Settings                                              --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS convertx_user_settings (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED  NOT NULL UNIQUE,
    default_quality TINYINT UNSIGNED NOT NULL DEFAULT 85
                    COMMENT 'Default image quality 1-100',
    default_dpi   SMALLINT UNSIGNED NOT NULL DEFAULT 150
                    COMMENT 'Default DPI for image output',
    notify_on_complete TINYINT(1) NOT NULL DEFAULT 0,
    updated_at    DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------ --
--  Seed default AI providers                                           --
-- ------------------------------------------------------------------ --
INSERT IGNORE INTO convertx_ai_providers
    (name, slug, base_url, model, capabilities, allowed_tiers, priority, cost_per_1k_tokens, is_active)
VALUES
    (
        'OpenAI',
        'openai',
        'https://api.openai.com',
        'gpt-4o-mini',
        '["ocr","summarization","translation","classification"]',
        '["pro","enterprise"]',
        1,
        0.000150,
        1
    ),
    (
        'HuggingFace',
        'huggingface',
        'https://api-inference.huggingface.co',
        'facebook/bart-large-cnn',
        '["summarization","classification"]',
        '["free","pro","enterprise"]',
        5,
        0.000010,
        1
    ),
    (
        'Tesseract (Local)',
        'tesseract',
        NULL,
        NULL,
        '["ocr"]',
        '["free","pro","enterprise"]',
        3,
        0.000000,
        1
    );


-- ------------------------------------------------------------------ --
--  ConvertX Subscription Plans                                         --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS convertx_subscription_plans (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(50)  NOT NULL UNIQUE,
    description     TEXT NULL,
    price           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    billing_cycle   ENUM('monthly','yearly','lifetime') DEFAULT 'monthly',
    max_jobs_per_month  INT NOT NULL DEFAULT 50   COMMENT '-1 = unlimited',
    max_file_size_mb    INT NOT NULL DEFAULT 10   COMMENT 'Max upload size in MB',
    max_batch_size      INT NOT NULL DEFAULT 5    COMMENT 'Max files per batch',
    ai_access           TINYINT(1) NOT NULL DEFAULT 0,
    api_access          TINYINT(1) NOT NULL DEFAULT 0,
    batch_convert       TINYINT(1) NOT NULL DEFAULT 1,
    priority_processing TINYINT(1) NOT NULL DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',
    sort_order      INT DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_slug   (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ConvertX User Subscriptions
CREATE TABLE IF NOT EXISTS convertx_user_subscriptions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    plan_id     INT UNSIGNED NOT NULL,
    status      ENUM('active','cancelled','expired','trial') DEFAULT 'active',
    started_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME NULL,
    assigned_by INT UNSIGNED NULL COMMENT 'Admin user ID',
    notes       VARCHAR(500) NULL,
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status  (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default plans
INSERT IGNORE INTO convertx_subscription_plans
    (name, slug, description, price, billing_cycle, max_jobs_per_month, max_file_size_mb, max_batch_size, ai_access, api_access, batch_convert, priority_processing, status, sort_order)
VALUES
    ('Free', 'free', 'Basic file conversion, limited monthly jobs.', 0.00, 'monthly', 50, 10, 5, 0, 0, 1, 0, 'active', 1),
    ('Pro', 'pro', 'Unlimited conversions, AI tasks, API access.', 9.99, 'monthly', -1, 100, 50, 1, 1, 1, 1, 'active', 2),
    ('Enterprise', 'enterprise', 'Full access, custom limits, priority support.', 29.99, 'monthly', -1, 500, 100, 1, 1, 1, 1, 'active', 3);

-- ------------------------------------------------------------------ --
--  Seed ConvertX into home_projects (idempotent)                       --
-- ------------------------------------------------------------------ --
INSERT IGNORE INTO `home_projects`
    (`project_key`, `name`, `description`, `icon`, `color`, `is_enabled`, `sort_order`, `database_name`, `url`)
VALUES
    ('convertx', 'ConvertX', 'AI-powered file conversion and document processing platform',
     'file-export', '#6366f1', 1, 10, 'mmb_convertx', '/projects/convertx');
