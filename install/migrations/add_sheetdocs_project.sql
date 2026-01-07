-- Add SheetDocs project to home_projects table
-- Migration: add_sheetdocs_project
-- Date: 2026-01-07

INSERT INTO `home_projects` (`project_key`, `name`, `description`, `icon`, `color`, `is_enabled`, `sort_order`, `database_name`, `url`) VALUES
('sheetdocs', 'SheetDocs', 'Collaborative spreadsheet and document editor', 'file-spreadsheet', '#00d4aa', 1, 7, 'sheetdocs', '/projects/sheetdocs')
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `icon` = VALUES(`icon`),
    `color` = VALUES(`color`),
    `database_name` = VALUES(`database_name`),
    `url` = VALUES(`url`);
