-- Phase 8: Database Optimization Migration
-- This file adds indexes and optimizations to improve query performance
-- 
-- IMPORTANT: This migration is database-agnostic and does not hardcode database names
-- Apply this to the appropriate database (main or project-specific) as needed
-- 
-- Usage:
--   For main database: mysql -u username -p database_name < phase8_database_optimization.sql
--   For project databases: Apply from admin panel or manually to each project database

-- ====================
-- MAIN DATABASE OPTIMIZATIONS
-- ====================
-- Apply these to your main database (e.g., testuser, mmb_main, etc.)

-- Users table optimizations
ALTER TABLE `users` 
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`),
    ADD INDEX IF NOT EXISTS `idx_last_login` (`last_login_at`);

-- Activity logs table optimizations
ALTER TABLE `activity_logs` 
    ADD INDEX IF NOT EXISTS `idx_user_action` (`user_id`, `action`),
    ADD INDEX IF NOT EXISTS `idx_action_created` (`action`, `created_at`);

-- ====================
-- PROJECT DATABASE OPTIMIZATIONS (CODEXPRO)
-- ====================
-- Apply these to your CodeXPro database (e.g., codexpro, mmb_codexpro, etc.)

-- Projects table optimizations
-- Add composite indexes for common queries
ALTER TABLE `projects` 
    ADD INDEX IF NOT EXISTS `idx_user_updated` (`user_id`, `updated_at`),
    ADD INDEX IF NOT EXISTS `idx_visibility_updated` (`visibility`, `updated_at`),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

-- Snippets table optimizations
ALTER TABLE `snippets` 
    ADD INDEX IF NOT EXISTS `idx_user_updated` (`user_id`, `updated_at`),
    ADD INDEX IF NOT EXISTS `idx_public_language` (`is_public`, `language`),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

-- Templates table optimizations
ALTER TABLE `templates` 
    ADD INDEX IF NOT EXISTS `idx_category_active` (`category`, `is_active`);

-- Project shares optimization
ALTER TABLE `project_shares` 
    ADD INDEX IF NOT EXISTS `idx_expires_at` (`expires_at`);

-- Activity logs optimization
ALTER TABLE `activity_logs` 
    ADD INDEX IF NOT EXISTS `idx_user_action` (`user_id`, `action`),
    ADD INDEX IF NOT EXISTS `idx_resource` (`resource_type`, `resource_id`);

-- ====================
-- PROJECT DATABASE OPTIMIZATIONS (IMGTXT)
-- ====================
-- Apply these to your ImgTxt database (e.g., imgtxt, mmb_imgtxt, etc.)

-- OCR jobs table optimizations
ALTER TABLE `ocr_jobs` 
    ADD INDEX IF NOT EXISTS `idx_user_status` (`user_id`, `status`),
    ADD INDEX IF NOT EXISTS `idx_status_created` (`status`, `created_at`),
    ADD INDEX IF NOT EXISTS `idx_language` (`language`);

-- Batch jobs optimizations
ALTER TABLE `batch_jobs` 
    ADD INDEX IF NOT EXISTS `idx_user_status` (`user_id`, `status`),
    ADD INDEX IF NOT EXISTS `idx_status_updated` (`status`, `updated_at`);

-- Usage stats optimizations
ALTER TABLE `usage_stats` 
    ADD INDEX IF NOT EXISTS `idx_user_id` (`user_id`);

-- Activity logs optimization
ALTER TABLE `activity_logs` 
    ADD INDEX IF NOT EXISTS `idx_user_action` (`user_id`, `action`);

-- ====================
-- PROJECT DATABASE OPTIMIZATIONS (PROSHARE)
-- ====================
-- Apply these to your ProShare database (e.g., proshare, mmb_proshare, etc.)

-- Files table optimizations
ALTER TABLE `files` 
    ADD INDEX IF NOT EXISTS `idx_user_created` (`user_id`, `created_at`),
    ADD INDEX IF NOT EXISTS `idx_status_expires` (`status`, `expires_at`),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

-- File downloads optimizations
ALTER TABLE `file_downloads` 
    ADD INDEX IF NOT EXISTS `idx_file_downloaded` (`file_id`, `downloaded_at`);

-- Text shares optimizations
ALTER TABLE `text_shares` 
    ADD INDEX IF NOT EXISTS `idx_user_created` (`user_id`, `created_at`),
    ADD INDEX IF NOT EXISTS `idx_status_expires` (`status`, `expires_at`);

-- Messages table optimizations
ALTER TABLE `messages` 
    ADD INDEX IF NOT EXISTS `idx_room_created` (`room_id`, `created_at`),
    ADD INDEX IF NOT EXISTS `idx_expires_at` (`expires_at`);

-- Notifications optimizations
ALTER TABLE `notifications` 
    ADD INDEX IF NOT EXISTS `idx_user_read` (`user_id`, `is_read`),
    ADD INDEX IF NOT EXISTS `idx_type` (`type`);

-- Audit logs optimizations
ALTER TABLE `audit_logs` 
    ADD INDEX IF NOT EXISTS `idx_user_action` (`user_id`, `action`),
    ADD INDEX IF NOT EXISTS `idx_resource` (`resource_type`, `resource_id`);

-- Activity logs optimization
ALTER TABLE `activity_logs` 
    ADD INDEX IF NOT EXISTS `idx_user_action` (`user_id`, `action`);

-- ====================
-- QUERY OPTIMIZATION NOTES
-- ====================
-- 1. Always use EXPLAIN on slow queries to identify missing indexes
-- 2. Monitor slow query log: SET GLOBAL slow_query_log = 'ON';
-- 3. Set slow query threshold: SET GLOBAL long_query_time = 2;
-- 4. Use ANALYZE TABLE periodically to update index statistics
-- 5. Consider partitioning large tables (activity_logs, file_downloads)

-- ====================
-- MAINTENANCE QUERIES
-- ====================
-- Run these periodically to maintain database performance

-- Analyze tables to update statistics (run monthly)
-- ANALYZE TABLE users, activity_logs;
-- ANALYZE TABLE projects, snippets, templates;
-- ANALYZE TABLE ocr_jobs, batch_jobs;
-- ANALYZE TABLE files, text_shares, file_downloads;

-- Optimize tables to reclaim space (run quarterly)
-- OPTIMIZE TABLE activity_logs;
-- OPTIMIZE TABLE file_downloads;
-- OPTIMIZE TABLE messages;

-- Archive old records (example queries to run before cleanup)
-- For activity_logs older than 90 days:
-- CREATE TABLE activity_logs_archive LIKE activity_logs;
-- INSERT INTO activity_logs_archive SELECT * FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
-- DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
