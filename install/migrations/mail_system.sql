-- Mail System Migration
-- Creates tables for: provider configs, notification templates, synced messages

-- --------------------------------------------------------
-- Mail Provider Configurations (SMTP + IMAP)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mail_provider_configs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Display name e.g. Zoho Main',
  `provider_type` enum('smtp','zoho','gmail','outlook','custom') NOT NULL DEFAULT 'smtp',
  -- SMTP
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` text DEFAULT NULL COMMENT 'AES-256-CBC encrypted',
  `smtp_encryption` enum('tls','ssl','none') DEFAULT 'tls',
  -- IMAP
  `imap_host` varchar(255) DEFAULT NULL,
  `imap_port` int(11) DEFAULT 993,
  `imap_username` varchar(255) DEFAULT NULL,
  `imap_password` text DEFAULT NULL COMMENT 'AES-256-CBC encrypted',
  `imap_encryption` enum('ssl','tls','none') DEFAULT 'ssl',
  -- Sender identity
  `from_name` varchar(100) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `reply_to` varchar(255) DEFAULT NULL,
  -- State
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Only one can be active at a time',
  `is_imap_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Mail Notification Templates
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mail_notification_templates` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL COMMENT 'e.g. welcome, password_reset, registration',
  `name` varchar(200) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL COMMENT 'HTML body with {{variable}} placeholders',
  `variables` text DEFAULT NULL COMMENT 'JSON array of available variable names',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- IMAP-synced inbox messages cache
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mail_synced_messages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider_config_id` int(10) UNSIGNED DEFAULT NULL,
  `uid` varchar(100) NOT NULL COMMENT 'IMAP message UID',
  `folder` varchar(100) NOT NULL DEFAULT 'INBOX',
  `message_id` varchar(500) DEFAULT NULL,
  `subject` varchar(1000) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `to_email` text DEFAULT NULL,
  `cc_email` text DEFAULT NULL,
  `date_sent` datetime DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `body_text` longtext DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_starred` tinyint(1) NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `raw_headers` text DEFAULT NULL,
  `synced_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_folder_user` (`uid`,`folder`,`user_id`),
  KEY `user_id` (`user_id`,`is_deleted`,`is_archived`),
  KEY `date_sent` (`date_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Mail send log (audit trail)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mail_send_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template_slug` varchar(100) DEFAULT NULL,
  `provider_config_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Default notification templates
-- --------------------------------------------------------
INSERT IGNORE INTO `mail_notification_templates` (`slug`, `name`, `subject`, `body`, `variables`, `is_enabled`) VALUES

('welcome', 'Welcome Email', 'Welcome to {{app_name}}!',
'<h2>Welcome, {{name}}!</h2>
<p>Thank you for joining <strong>{{app_name}}</strong>. Your account has been created successfully.</p>
<p><a href="{{login_url}}" style="background:#667eea;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;">Login Now</a></p>
<p>If you have any questions, feel free to contact our support team.</p>',
'["name","app_name","login_url"]', 1),

('email_verification', 'Email Verification', 'Verify your email address',
'<h2>Hi {{name}},</h2><p>Thanks for registering! Please confirm your email address by entering the verification code below:</p><div style="text-align:center;margin:24px 0;"><span style="display:inline-block;font-size:36px;font-weight:700;letter-spacing:10px;background:#f4f7fb;border:2px dashed #667eea;border-radius:10px;padding:16px 32px;color:#333;">{{otp}}</span></div><p>This code expires in 5 minutes. If you did not create an account, please ignore this email.</p>',
'["name","otp","verify_url","app_name"]', 1),

('password_reset', 'Password Reset', 'Reset your password',
'<h2>Hi {{name}},</h2>
<p>You requested a password reset. Click the button below to set a new password:</p>
<p><a href="{{reset_url}}" style="background:#e74c3c;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;">Reset Password</a></p>
<p>This link expires in 1 hour. If you did not request this, please ignore this email and your password will remain unchanged.</p>',
'["name","reset_url","app_name"]', 1),

('login_alert', 'Login Alert', 'New login to your {{app_name}} account',
'<h2>Hi {{name}},</h2>
<p>A new login was detected on your account.</p>
<table style="border-collapse:collapse;width:100%;margin:16px 0;">
<tr><td style="padding:8px;border:1px solid #ddd;"><strong>IP Address</strong></td><td style="padding:8px;border:1px solid #ddd;">{{ip_address}}</td></tr>
<tr><td style="padding:8px;border:1px solid #ddd;"><strong>Time</strong></td><td style="padding:8px;border:1px solid #ddd;">{{login_time}}</td></tr>
</table>
<p>If this was not you, please <a href="{{reset_url}}">reset your password</a> immediately.</p>',
'["name","ip_address","login_time","reset_url","app_name"]', 1),

('password_changed', 'Password Changed', 'Your password was changed',
'<h2>Hi {{name}},</h2>
<p>Your password was successfully changed on {{changed_at}}.</p>
<p>If you did not make this change, please <a href="{{reset_url}}">reset your password</a> immediately and contact support.</p>',
'["name","changed_at","reset_url","app_name"]', 1),

('subscription-confirmed', 'Subscription Confirmed', 'Your {{plan_name}} subscription is active',
'<h2>Hi {{user_name}},</h2><p>Your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong> is now active.</p><p><a href="{{invoice_url}}">View Invoice</a></p><p><a href="{{dashboard_url}}">Manage subscription</a></p>',
'["user_name","plan_name","app_name","currency","amount","billing_cycle","started_at","expires_at","invoice_url","dashboard_url"]', 1),

('subscription-expiring', 'Subscription Expiring Soon', 'Your {{plan_name}} subscription expires in {{days_left}} day(s)',
'<h2>Hi {{user_name}},</h2><p>Your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong> will expire on <strong>{{expires_at}}</strong>.</p><p><a href="{{renew_url}}">Renew Now</a></p>',
'["user_name","plan_name","app_name","expires_at","days_left","renew_url"]', 1),

('subscription-expired', 'Subscription Expired', 'Your {{plan_name}} subscription has expired',
'<h2>Hi {{user_name}},</h2><p>Your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong> expired on <strong>{{expired_at}}</strong>.</p><p><a href="{{renew_url}}">Subscribe Again</a></p>',
'["user_name","plan_name","app_name","expired_at","renew_url"]', 1),

('subscription-renewal', 'Subscription Renewal Reminder', 'Renew your {{plan_name}} subscription',
'<h2>Hi {{user_name}},</h2><p>This is a reminder to renew your <strong>{{plan_name}}</strong> subscription for <strong>{{app_name}}</strong>.</p><p>Plan amount: <strong>{{currency}} {{amount}}</strong></p><p><a href="{{renew_url}}">Renew Subscription</a></p>',
'["user_name","plan_name","app_name","currency","amount","renew_url"]', 1);
