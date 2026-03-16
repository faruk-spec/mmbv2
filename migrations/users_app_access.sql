-- Migration: add app_access JSON column to users table
-- Stores a JSON array of app slugs the user is allowed to access.
-- NULL / empty array = access to ALL apps (no restriction, backwards-compatible).
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `app_access` JSON NULL DEFAULT NULL
        COMMENT 'JSON array of allowed app slugs, e.g. ["qr","whatsapp"]. NULL = unrestricted.'
    AFTER `role`;
