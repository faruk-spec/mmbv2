-- Migration: Add maintenance mode settings columns
-- This ensures all maintenance-related settings exist in the settings table

-- Add maintenance settings if they don't exist
INSERT IGNORE INTO settings (`key`, `value`, `created_at`, `updated_at`) VALUES
('maintenance_mode', '0', NOW(), NOW()),
('maintenance_title', 'We''ll Be Back Soon!', NOW(), NOW()),
('maintenance_message', 'We''re currently performing scheduled maintenance to improve your experience. Please check back in a few minutes.', NOW(), NOW()),
('maintenance_show_countdown', '0', NOW(), NOW()),
('maintenance_end_time', '', NOW(), NOW()),
('maintenance_custom_html', '', NOW(), NOW()),
('maintenance_contact_email', '', NOW(), NOW());

-- Verify settings table structure
-- If your settings table doesn't have these columns, adjust accordingly
-- Expected columns: id, `key`, `value`, created_at, updated_at
