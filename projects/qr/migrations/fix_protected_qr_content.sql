-- Fix QR codes with password/expiry to use access URLs
-- This migration updates existing QR codes that were created before the access URL fix

-- Update protected QR codes that still have direct URLs
UPDATE qr_codes 
SET content = CONCAT('https://mmbtech.online/projects/qr/access/', short_code)
WHERE (password_hash IS NOT NULL OR expires_at IS NOT NULL)
  AND short_code IS NOT NULL
  AND content NOT LIKE '%/projects/qr/access/%'
  AND is_dynamic = 1;

-- Also update bulk QR codes to use access URLs
UPDATE qr_codes 
SET content = CONCAT('https://mmbtech.online/projects/qr/access/', short_code)
WHERE is_dynamic = 1
  AND short_code IS NOT NULL
  AND content NOT LIKE '%/projects/qr/access/%'
  AND redirect_url IS NOT NULL;
