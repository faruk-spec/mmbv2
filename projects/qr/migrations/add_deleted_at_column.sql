-- Add deleted_at column for soft delete functionality
-- This allows us to maintain total generated count even after deletion

ALTER TABLE qr_codes ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL DEFAULT NULL AFTER status;

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS idx_deleted_at ON qr_codes(deleted_at);
CREATE INDEX IF NOT EXISTS idx_user_deleted ON qr_codes(user_id, deleted_at);
