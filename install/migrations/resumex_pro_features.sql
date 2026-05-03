-- ResumeX Pro Features Migration
ALTER TABLE `resumex_templates` ADD COLUMN IF NOT EXISTS `is_pro` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Pro users only';
ALTER TABLE `resumex_resumes` ADD COLUMN IF NOT EXISTS `share_token` VARCHAR(64) DEFAULT NULL COMMENT 'Public sharing token';
ALTER TABLE `resumex_resumes` ADD COLUMN IF NOT EXISTS `is_public` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = publicly viewable via share token';
CREATE INDEX IF NOT EXISTS `idx_share_token` ON `resumex_resumes` (`share_token`);
