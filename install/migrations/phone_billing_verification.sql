-- Add phone verification columns to user_profiles
ALTER TABLE user_profiles 
  ADD COLUMN IF NOT EXISTS phone_verified_at TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS phone_otp VARCHAR(10) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS phone_otp_expires_at TIMESTAMP NULL DEFAULT NULL;

-- Create billing details table
CREATE TABLE IF NOT EXISTS user_billing_details (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(100) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  phone VARCHAR(20) NOT NULL DEFAULT '',
  address_line1 VARCHAR(255) NOT NULL DEFAULT '',
  address_line2 VARCHAR(255) NULL DEFAULT NULL,
  city VARCHAR(100) NOT NULL DEFAULT '',
  state VARCHAR(100) NOT NULL DEFAULT '',
  postal_code VARCHAR(20) NOT NULL DEFAULT '',
  country VARCHAR(100) NOT NULL DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add require_mobile_verification setting (default disabled)
INSERT IGNORE INTO settings (`key`, value, type) VALUES ('require_mobile_verification', '0', 'string');
