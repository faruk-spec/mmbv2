-- Fix QR codes with password/expiry to use access URLs (Dynamic Version)
-- This migration updates existing QR codes that were created before the access URL fix
-- NOTE: Replace {YOUR_DOMAIN} with your actual domain when running this migration
-- Example: https://yourdomain.com or https://mmbtech.online

-- Update protected QR codes that still have direct URLs
UPDATE qr_codes 
SET content = CONCAT('{YOUR_DOMAIN}/projects/qr/access/', short_code)
WHERE (password_hash IS NOT NULL OR expires_at IS NOT NULL)
  AND short_code IS NOT NULL
  AND content NOT LIKE '%/projects/qr/access/%'
  AND is_dynamic = 1;

-- Also update bulk QR codes to use access URLs
UPDATE qr_codes 
SET content = CONCAT('{YOUR_DOMAIN}/projects/qr/access/', short_code)
WHERE is_dynamic = 1
  AND short_code IS NOT NULL
  AND content NOT LIKE '%/projects/qr/access/%'
  AND redirect_url IS NOT NULL;

-- Instructions:
-- 1. Replace {YOUR_DOMAIN} with your full domain URL (e.g., https://mmbtech.online)
-- 2. Run this migration: mysql -u username -p database_name < fix_protected_qr_content_dynamic.sql
