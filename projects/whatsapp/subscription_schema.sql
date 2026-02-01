-- WhatsApp Subscription System Schema
-- Version: 1.0
-- Created: <?= date('Y-m-d') ?>

-- Table: whatsapp_subscription_plans
-- Stores available subscription plans
CREATE TABLE IF NOT EXISTS whatsapp_subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) NOT NULL DEFAULT 'USD',
    messages_limit INT NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    sessions_limit INT NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    api_calls_limit INT NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    duration_days INT NOT NULL DEFAULT 30,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: whatsapp_subscriptions
-- Stores user subscriptions
-- Note: Foreign key constraint to users(id) removed to avoid cross-database reference issues
-- Application-level integrity is maintained by controllers checking user_id validity
CREATE TABLE IF NOT EXISTS whatsapp_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_type ENUM('free', 'basic', 'premium', 'enterprise') NOT NULL DEFAULT 'free',
    status ENUM('active', 'inactive', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    messages_limit INT NOT NULL DEFAULT 100,
    sessions_limit INT NOT NULL DEFAULT 1,
    api_calls_limit INT NOT NULL DEFAULT 1000,
    messages_used INT NOT NULL DEFAULT 0,
    sessions_used INT NOT NULL DEFAULT 0,
    api_calls_used INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_plan_type (plan_type),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans
INSERT INTO whatsapp_subscription_plans (name, description, price, currency, messages_limit, sessions_limit, api_calls_limit, duration_days, is_active) VALUES
('Free Plan', 'Basic plan for testing and small-scale use', 0.00, 'USD', 100, 1, 1000, 30, 1),
('Basic Plan', 'Suitable for individuals and small businesses', 9.99, 'USD', 1000, 3, 10000, 30, 1),
('Premium Plan', 'Advanced features for growing businesses', 29.99, 'USD', 10000, 10, 100000, 30, 1),
('Enterprise Plan', 'Unlimited access for large organizations', 99.99, 'USD', 0, 0, 0, 30, 1);

-- Create a view for subscription details with plan information
CREATE OR REPLACE VIEW whatsapp_subscription_details AS
SELECT 
    s.id,
    s.user_id,
    u.name as user_name,
    u.email as user_email,
    s.plan_type,
    s.status,
    s.start_date,
    s.end_date,
    s.messages_limit,
    s.sessions_limit,
    s.api_calls_limit,
    s.messages_used,
    s.sessions_used,
    s.api_calls_used,
    CASE 
        WHEN s.messages_limit = 0 THEN 100
        ELSE ROUND((s.messages_used / s.messages_limit) * 100, 2)
    END as messages_usage_percent,
    CASE 
        WHEN s.sessions_limit = 0 THEN 100
        ELSE ROUND((s.sessions_used / s.sessions_limit) * 100, 2)
    END as sessions_usage_percent,
    CASE 
        WHEN s.api_calls_limit = 0 THEN 100
        ELSE ROUND((s.api_calls_used / s.api_calls_limit) * 100, 2)
    END as api_calls_usage_percent,
    DATEDIFF(s.end_date, NOW()) as days_remaining,
    s.created_at,
    s.updated_at
FROM whatsapp_subscriptions s
JOIN users u ON s.user_id = u.id;
