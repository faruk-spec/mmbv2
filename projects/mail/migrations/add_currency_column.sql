-- Add currency column to mail_subscription_plans table
-- Run this SQL query in your database to add multi-currency support

ALTER TABLE `mail_subscription_plans` 
ADD COLUMN `currency` VARCHAR(3) DEFAULT 'USD' COMMENT 'Currency code (USD, EUR, GBP, etc.)' 
AFTER `price_yearly`;

-- Update existing plans to USD if not set
UPDATE `mail_subscription_plans` SET `currency` = 'USD' WHERE `currency` IS NULL;

-- Optional: Create index for better query performance
-- CREATE INDEX idx_currency ON mail_subscription_plans(currency);
