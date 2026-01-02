-- =====================================================
-- Sample Analytics Data for Testing
-- =====================================================
-- This script populates the analytics_events table with
-- sample data for testing the analytics dashboard.
-- Run this AFTER the complete_phase_updates.sql
-- =====================================================

-- Clear existing test data (optional)
-- DELETE FROM analytics_events WHERE project = 'platform';

-- Insert sample page visits
INSERT INTO analytics_events (project, resource_type, resource_id, event_type, user_id, ip_address, user_agent, browser, platform, country, metadata, created_at) VALUES
-- Today's visits
('platform', 'page', 0, 'page_visit', NULL, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Chrome', 'Windows', 'US', '{"page": "/", "url": "/"}', NOW() - INTERVAL 5 MINUTE),
('platform', 'page', 0, 'page_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{"page": "/dashboard", "url": "/dashboard"}', NOW() - INTERVAL 3 MINUTE),
('platform', 'page', 0, 'page_visit', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{"page": "/projects", "url": "/projects"}', NOW() - INTERVAL 2 MINUTE),
('platform', 'page', 0, 'page_visit', NULL, '192.168.1.103', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)', 'Safari', 'iOS', 'CA', '{"page": "/login", "url": "/login"}', NOW() - INTERVAL 1 MINUTE),
('platform', 'page', 0, 'page_visit', 3, '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0)', 'Edge', 'Windows', 'US', '{"page": "/admin", "url": "/admin"}', NOW()),

-- Login events today
('platform', 'auth', 1, 'user_login', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 10 MINUTE, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 10 MINUTE),
('platform', 'auth', 2, 'user_login', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 8 MINUTE, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 8 MINUTE),
('platform', 'auth', 3, 'user_login', 3, '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0)', 'Edge', 'Windows', 'US', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 5 MINUTE, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 5 MINUTE),

-- Registration event today
('platform', 'auth', 4, 'user_register', 4, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Chrome', 'Windows', 'US', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 30 MINUTE, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 30 MINUTE),

-- Return visit today
('platform', 'user', 1, 'return_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', CONCAT('{"days_since_last_visit": 2, "timestamp": "', DATE_FORMAT(NOW() - INTERVAL 20 MINUTE, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 20 MINUTE),

-- Yesterday's data
('platform', 'page', 0, 'page_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{"page": "/dashboard"}', NOW() - INTERVAL 1 DAY + INTERVAL 2 HOUR),
('platform', 'page', 0, 'page_visit', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{"page": "/projects"}', NOW() - INTERVAL 1 DAY + INTERVAL 3 HOUR),
('platform', 'auth', 1, 'user_login', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 1 DAY, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 1 DAY),
('platform', 'auth', 2, 'user_login', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 1 DAY, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 1 DAY),

-- Last week's data
('platform', 'page', 0, 'page_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{"page": "/"}', NOW() - INTERVAL 3 DAY),
('platform', 'page', 0, 'page_visit', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{"page": "/dashboard"}', NOW() - INTERVAL 4 DAY),
('platform', 'page', 0, 'page_visit', 3, '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0)', 'Edge', 'Windows', 'US', '{"page": "/admin"}', NOW() - INTERVAL 5 DAY),
('platform', 'auth', 1, 'user_login', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 3 DAY, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 3 DAY),
('platform', 'auth', 2, 'user_login', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', CONCAT('{"timestamp": "', DATE_FORMAT(NOW() - INTERVAL 4 DAY, '%Y-%m-%d %H:%i:%s'), '"}'), NOW() - INTERVAL 4 DAY),

-- Conversion events
('platform', 'conversion', 0, 'conversion_signup', 4, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Chrome', 'Windows', 'US', '{"conversion_type": "signup"}', NOW() - INTERVAL 30 MINUTE),
('platform', 'conversion', 0, 'conversion_project_create', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{"conversion_type": "project_create"}', NOW() - INTERVAL 1 DAY),

-- More diverse browser/platform data for last 30 days
('platform', 'page', 0, 'page_visit', NULL, '192.168.1.110', 'Mozilla/5.0 (Android 11; Mobile)', 'Chrome', 'Android', 'IN', '{"page": "/"}', NOW() - INTERVAL 7 DAY),
('platform', 'page', 0, 'page_visit', NULL, '192.168.1.111', 'Mozilla/5.0 (iPad; CPU OS 14_0)', 'Safari', 'iOS', 'AU', '{"page": "/"}', NOW() - INTERVAL 10 DAY),
('platform', 'page', 0, 'page_visit', NULL, '192.168.1.112', 'Mozilla/5.0 (Windows NT 10.0)', 'Edge', 'Windows', 'DE', '{"page": "/"}', NOW() - INTERVAL 15 DAY),
('platform', 'page', 0, 'page_visit', NULL, '192.168.1.113', 'Mozilla/5.0 (X11; Ubuntu)', 'Firefox', 'Linux', 'FR', '{"page": "/"}', NOW() - INTERVAL 20 DAY),
('platform', 'page', 0, 'page_visit', NULL, '192.168.1.114', 'Opera/9.80', 'Opera', 'Windows', 'ES', '{"page": "/"}', NOW() - INTERVAL 25 DAY);

-- =====================================================
-- Completion Message
-- =====================================================
SELECT 'Sample analytics data inserted successfully!' AS message;
SELECT COUNT(*) AS total_events FROM analytics_events WHERE project = 'platform';
