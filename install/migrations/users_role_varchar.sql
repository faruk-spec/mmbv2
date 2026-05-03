-- Migration: change users.role from ENUM to VARCHAR(100)
-- This allows custom role slugs (created in /admin/roles) to be assigned to users.
-- Safe to run multiple times; the column is only altered if it is still an ENUM type.

ALTER TABLE `users`
    MODIFY COLUMN `role` VARCHAR(100) NOT NULL DEFAULT 'user';
