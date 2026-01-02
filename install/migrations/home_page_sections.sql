-- Migration for section headings management
-- Run this migration after home_page_stats_timeline.sql

CREATE TABLE IF NOT EXISTS `home_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `subheading` text,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default section headings
INSERT INTO `home_sections` (`section_key`, `heading`, `subheading`, `is_active`, `sort_order`) VALUES
('stats', 'Our Impact in Numbers', 'Trusted by developers and teams worldwide', 1, 1),
('timeline', 'Our Journey', 'Milestones and achievements that shaped our platform', 1, 2),
('features', '🚀 Platform Features', 'Powerful capabilities across all projects', 1, 3)
ON DUPLICATE KEY UPDATE `heading` = VALUES(`heading`), `subheading` = VALUES(`subheading`);
