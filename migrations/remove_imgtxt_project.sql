-- Migration: Remove ImgTxt from home_projects and clean up related records
-- Apply to the main application database

DELETE FROM `home_projects` WHERE `project_key` = 'imgtxt';
