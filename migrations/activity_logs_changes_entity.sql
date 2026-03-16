-- ============================================================
-- Activity Logs: changes, entity_name, user_name columns
-- Adds field-level change tracking and denormalized entity context.
-- Run once against the main database.
-- ============================================================

-- changes: computed diff of only the modified fields
-- Format: {"field": {"old": "previous_value", "new": "current_value"}}
ALTER TABLE `activity_logs`
    ADD COLUMN IF NOT EXISTS `changes`     JSON         NULL AFTER `new_values`,
    ADD COLUMN IF NOT EXISTS `entity_name` VARCHAR(255) NULL AFTER `resource_id`,
    ADD COLUMN IF NOT EXISTS `user_name`   VARCHAR(255) NULL AFTER `user_id`;

-- Index to support filtering by entity_name and fast user_name lookups
ALTER TABLE `activity_logs`
    ADD INDEX IF NOT EXISTS `idx_entity_name` (`entity_name`(100)),
    ADD INDEX IF NOT EXISTS `idx_user_name`   (`user_name`(100));
