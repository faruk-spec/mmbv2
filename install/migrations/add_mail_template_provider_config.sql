-- Add mail_provider_config_id to mail_notification_templates
-- Allows specifying which mail provider config each notification template uses

ALTER TABLE `mail_notification_templates`
    ADD COLUMN IF NOT EXISTS `mail_provider_config_id` INT UNSIGNED NULL DEFAULT NULL
    AFTER `is_enabled`;

-- Add the FK only if it does not already exist.
-- We query information_schema first and use a prepared statement so this is
-- safe to run on any MariaDB / MySQL version without the IF NOT EXISTS
-- constraint syntax (which is only available in MariaDB >= 10.9).
SET @_fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME        = 'mail_notification_templates'
      AND CONSTRAINT_NAME   = 'fk_mail_tpl_provider'
      AND CONSTRAINT_TYPE   = 'FOREIGN KEY'
);

SET @_fk_sql = IF(
    @_fk_exists = 0,
    'ALTER TABLE `mail_notification_templates`
         ADD CONSTRAINT `fk_mail_tpl_provider`
         FOREIGN KEY (`mail_provider_config_id`)
         REFERENCES `mail_provider_configs`(`id`)
         ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1 -- FK already exists, skipping'
);

PREPARE _fk_stmt FROM @_fk_sql;
EXECUTE _fk_stmt;
DEALLOCATE PREPARE _fk_stmt;
