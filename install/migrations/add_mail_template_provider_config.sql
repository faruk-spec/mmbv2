-- Add mail_provider_config_id to mail_notification_templates
-- Allows specifying which mail provider config each notification template uses

ALTER TABLE `mail_notification_templates`
    ADD COLUMN IF NOT EXISTS `mail_provider_config_id` INT UNSIGNED NULL DEFAULT NULL
    AFTER `is_enabled`;

ALTER TABLE `mail_notification_templates`
    ADD CONSTRAINT IF NOT EXISTS `fk_mail_tpl_provider`
    FOREIGN KEY (`mail_provider_config_id`) REFERENCES `mail_provider_configs`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;
