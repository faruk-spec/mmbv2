-- Migration: Add show_features_text and show_features_url columns to home_projects
-- These allow the "Show Features" button on project cards to have custom text and a custom link.

ALTER TABLE `home_projects`
    ADD COLUMN IF NOT EXISTS `show_features_text` VARCHAR(100) NULL DEFAULT 'Show Features' AFTER `features`,
    ADD COLUMN IF NOT EXISTS `show_features_url` VARCHAR(255) NULL DEFAULT NULL AFTER `show_features_text`;
