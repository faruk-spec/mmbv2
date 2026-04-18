-- Add new admin-controlled settings keys to proshare_settings.
-- These keys govern notification visibility, auto-delete defaults,
-- and whether users are allowed to override those defaults.

INSERT IGNORE INTO `proshare_settings` (`key`, `value`, `type`, `description`, `is_system`) VALUES
('enable_email_notifications',   '1', 'boolean', 'Show email notification option to users',           1),
('enable_sms_notifications',     '1', 'boolean', 'Show SMS notification option to users',             1),
('default_auto_delete',          '0', 'boolean', 'Default state of Auto-Delete for new users',        1),
('user_can_change_auto_delete',  '1', 'boolean', 'Allow users to toggle Auto-Delete in their settings', 1),
('user_file_size_options',       '50,100,200,500', 'string', 'Comma-separated MB values for the user file size dropdown', 1);
