-- QUICK FIX: Create Subscription for Existing User
-- This script creates a subscription for a user who doesn't have one
-- This fixes the "Access Denied" errors

-- ============================================
-- INSTRUCTIONS
-- ============================================
-- 1. Find your user ID from the main users table
-- 2. Replace USER_ID_HERE with your actual user ID in the queries below
-- 3. Run this script in your MySQL client

-- ============================================
-- STEP 1: Find Your User ID
-- ============================================

-- Run this to find your user ID:
SELECT id, name, email FROM users WHERE email = 'your-email@example.com';
-- Copy the 'id' value and use it below

-- ============================================
-- STEP 2: Create Subscription (EDIT THIS)
-- ============================================

-- SET YOUR USER ID HERE:
SET @mmb_user_id = 1; -- CHANGE THIS TO YOUR USER ID

-- Choose a plan (1 = Free, 2 = Starter, 3 = Business, 4 = Developer)
SET @plan_id = 1;

-- ============================================
-- STEP 3: Run the Fix
-- ============================================

-- Check if subscriber already exists
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Subscriber already exists - no action needed' 
        ELSE 'Creating new subscriber...' 
    END as status
FROM mail_subscribers 
WHERE mmb_user_id = @mmb_user_id;

-- Create subscriber if doesn't exist
INSERT INTO mail_subscribers (mmb_user_id, account_name, status, created_at)
SELECT 
    @mmb_user_id,
    CONCAT('Account for User ', @mmb_user_id),
    'active',
    NOW()
FROM dual
WHERE NOT EXISTS (
    SELECT 1 FROM mail_subscribers WHERE mmb_user_id = @mmb_user_id
);

-- Get the subscriber ID
SELECT @subscriber_id := id FROM mail_subscribers WHERE mmb_user_id = @mmb_user_id LIMIT 1;

SELECT CONCAT('Subscriber ID: ', @subscriber_id) as info;

-- Check if subscription already exists
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Subscription already exists - no action needed' 
        ELSE 'Creating new subscription...' 
    END as status
FROM mail_subscriptions 
WHERE subscriber_id = @subscriber_id;

-- Create subscription if doesn't exist
INSERT INTO mail_subscriptions (subscriber_id, plan_id, status, billing_cycle, current_period_start, current_period_end, created_at)
SELECT 
    @subscriber_id,
    @plan_id,
    'active',
    'monthly',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 1 MONTH),
    NOW()
FROM dual
WHERE NOT EXISTS (
    SELECT 1 FROM mail_subscriptions WHERE subscriber_id = @subscriber_id
);

-- ============================================
-- STEP 4: Verify Success
-- ============================================

SELECT 'VERIFICATION - Check if subscription was created:' as step;

SELECT 
    s.id as subscriber_id,
    s.mmb_user_id,
    s.account_name,
    s.status as subscriber_status,
    sub.id as subscription_id,
    sub.status as subscription_status,
    sp.plan_name,
    sp.max_users,
    sp.max_domains,
    s.created_at
FROM mail_subscribers s
LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
LEFT JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
WHERE s.mmb_user_id = @mmb_user_id;

-- If you see a row with your user details, the fix is complete!
-- You should now be able to access:
-- - /projects/mail/subscriber/domains
-- - /projects/mail/subscriber/aliases
-- - /projects/mail/subscriber/users/add
-- - /projects/mail/subscriber/billing

SELECT 'SUCCESS! You should now have access to the mail subscriber features.' as result;
SELECT 'If you still see errors, clear your browser cache and try again.' as note;
