-- Add WhatsApp API project to home_projects table
-- This makes it visible in dashboard and home page

-- Insert WhatsApp project
INSERT INTO `home_projects` (
    `project_key`, 
    `name`, 
    `description`, 
    `icon`, 
    `color`, 
    `is_enabled`, 
    `sort_order`, 
    `database_name`, 
    `url`
) VALUES (
    'whatsapp',
    'WhatsApp API',
    'WhatsApp API automation and messaging platform',
    'message-circle',
    '#25D366',
    1,
    70,
    'mmb_whatsapp',
    '/projects/whatsapp'
) ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `icon` = VALUES(`icon`),
    `color` = VALUES(`color`),
    `is_enabled` = VALUES(`is_enabled`),
    `database_name` = VALUES(`database_name`),
    `url` = VALUES(`url`);
