-- Troubleshooting Script for Mail Project Issues
-- Run this to diagnose and fix common issues after migrations

-- ============================================
-- STEP 1: Verify Tables Exist
-- ============================================

-- Check if all required tables exist
SELECT 'Checking tables...' as status;

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'OK' 
        ELSE 'MISSING' 
    END as mail_subscribers_table
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'mail_subscribers';

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'OK' 
        ELSE 'MISSING' 
    END as mail_subscriptions_table
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'mail_subscriptions';

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'OK' 
        ELSE 'MISSING' 
    END as mail_billing_history_table
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'mail_billing_history';

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'OK' 
        ELSE 'MISSING' 
    END as mail_subscription_plans_table
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'mail_subscription_plans';

-- ============================================
-- STEP 2: Check Subscription Plans
-- ============================================

SELECT 'Checking subscription plans...' as status;

SELECT COUNT(*) as plan_count FROM mail_subscription_plans WHERE is_active = 1;

-- Show all active plans
SELECT id, plan_name, plan_slug, price_monthly FROM mail_subscription_plans WHERE is_active = 1;

-- ============================================
-- STEP 3: Check Subscribers
-- ============================================

SELECT 'Checking subscribers...' as status;

-- Count total subscribers
SELECT COUNT(*) as total_subscribers FROM mail_subscribers;

-- Show all subscribers
SELECT 
    s.id,
    s.mmb_user_id,
    s.account_name,
    s.status,
    sub.status as subscription_status,
    sp.plan_name,
    s.created_at
FROM mail_subscribers s
LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
LEFT JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
ORDER BY s.created_at DESC;

-- ============================================
-- STEP 4: Check for Specific User Issues
-- ============================================

-- REPLACE 'YOUR_USER_ID' with actual mmb_user_id
-- To find user ID, check the users table or session data

SELECT 'Checking user subscription status...' as status;

-- Check if user has subscriber entry
SELECT 
    'Subscriber Entry' as check_type,
    CASE 
        WHEN COUNT(*) > 0 THEN 'EXISTS' 
        ELSE 'MISSING - This is why you get Access Denied!' 
    END as result
FROM mail_subscribers 
WHERE mmb_user_id = YOUR_USER_ID_HERE;

-- Check if user has active subscription
SELECT 
    'Active Subscription' as check_type,
    CASE 
        WHEN COUNT(*) > 0 THEN 'EXISTS' 
        ELSE 'MISSING' 
    END as result
FROM mail_subscribers s
JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
WHERE s.mmb_user_id = YOUR_USER_ID_HERE 
AND sub.status = 'active';

-- ============================================
-- STEP 5: Check Table Structure
-- ============================================

SELECT 'Checking mail_aliases structure...' as status;

-- Verify aliases table has subscriber_id column
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'OK - subscriber_id exists' 
        ELSE 'ERROR - subscriber_id missing!' 
    END as aliases_subscriber_id
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'mail_aliases' 
AND column_name = 'subscriber_id';

-- ============================================
-- FIX: Create Test Subscription (if needed)
-- ============================================

-- UNCOMMENT AND MODIFY THIS SECTION TO CREATE A TEST SUBSCRIPTION
-- ONLY RUN THIS IF USER DOESN'T HAVE A SUBSCRIPTION

/*
-- Replace YOUR_USER_ID_HERE with the actual user ID
SET @user_id = YOUR_USER_ID_HERE;
SET @plan_id = 1; -- Free plan

-- Check if subscriber already exists
SELECT @subscriber_exists := COUNT(*) FROM mail_subscribers WHERE mmb_user_id = @user_id;

-- Create subscriber if doesn't exist
INSERT INTO mail_subscribers (mmb_user_id, account_name, status, created_at)
SELECT @user_id, 'Test Account', 'active', NOW()
WHERE @subscriber_exists = 0;

-- Get subscriber ID
SELECT @subscriber_id := id FROM mail_subscribers WHERE mmb_user_id = @user_id;

-- Check if subscription exists
SELECT @subscription_exists := COUNT(*) FROM mail_subscriptions WHERE subscriber_id = @subscriber_id;

-- Create subscription if doesn't exist
INSERT INTO mail_subscriptions (subscriber_id, plan_id, status, billing_cycle, current_period_start, current_period_end, created_at)
SELECT @subscriber_id, @plan_id, 'active', 'monthly', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), NOW()
WHERE @subscription_exists = 0;

-- Verify creation
SELECT 
    s.id as subscriber_id,
    s.mmb_user_id,
    s.account_name,
    sub.id as subscription_id,
    sp.plan_name
FROM mail_subscribers s
JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
WHERE s.mmb_user_id = @user_id;
*/

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

SELECT 'Final verification...' as status;

-- Count domains per subscriber
SELECT 
    s.id as subscriber_id,
    s.account_name,
    COUNT(d.id) as domain_count
FROM mail_subscribers s
LEFT JOIN mail_domains d ON s.id = d.subscriber_id
GROUP BY s.id, s.account_name;

-- Count aliases per subscriber  
SELECT 
    s.id as subscriber_id,
    s.account_name,
    COUNT(a.id) as alias_count
FROM mail_subscribers s
LEFT JOIN mail_aliases a ON s.id = a.subscriber_id
GROUP BY s.id, s.account_name;

-- Check for orphaned data (domains without subscriber_id)
SELECT COUNT(*) as orphaned_domains 
FROM mail_domains 
WHERE subscriber_id IS NULL OR subscriber_id NOT IN (SELECT id FROM mail_subscribers);

SELECT COUNT(*) as orphaned_aliases 
FROM mail_aliases 
WHERE subscriber_id IS NULL OR subscriber_id NOT IN (SELECT id FROM mail_subscribers);
