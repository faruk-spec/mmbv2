-- Add 'inactive' to proshare_files.status ENUM so that admins/users
-- can deactivate a file without it being treated as expired or deleted.
ALTER TABLE `proshare_files`
    MODIFY COLUMN `status` ENUM('active', 'expired', 'deleted', 'reported', 'inactive') DEFAULT 'active';
