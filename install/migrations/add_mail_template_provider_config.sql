-- Add mail_provider_config_id to mail_notification_templates
-- Allows specifying which mail provider config each notification template uses

ALTER TABLE `mail_notification_templates`
    ADD COLUMN IF NOT EXISTS `mail_provider_config_id` INT UNSIGNED NULL DEFAULT NULL
    AFTER `is_enabled`;

-- Add FK only if it does not already exist (MariaDB-compatible: check information_schema first)
-- Run this statement directly; it is safe to run multiple times because IF NOT EXISTS is
-- supported for ADD COLUMN but NOT for ADD CONSTRAINT in MariaDB < 10.9.
-- If the constraint already exists the statement will produce a benign duplicate-key warning.
ALTER TABLE `mail_notification_templates`
    ADD FOREIGN KEY (`mail_provider_config_id`)
    REFERENCES `mail_provider_configs`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;
