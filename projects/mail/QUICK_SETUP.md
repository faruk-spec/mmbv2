# Quick Setup Guide - Mail Project Fixes

## 🚀 Quick Start (5 Minutes)

### 1. Run Database Migrations

**Automated (Recommended):**
```bash
cd projects/mail/migrations
chmod +x run_migrations.sh
./run_migrations.sh
# Enter your database credentials when prompted
```

**Manual (if automated fails):**
```bash
cd projects/mail/migrations
mysql -u root -p your_database < fix_table_names.sql
mysql -u root -p your_database < create_billing_history_table.sql
mysql -u root -p your_database < update_domains_table.sql
mysql -u root -p your_database < update_mailboxes_table.sql
mysql -u root -p your_database < update_aliases_table.sql
```

### 2. Verify Installation

```bash
# Check if tables were created
mysql -u root -p -e "USE your_database; SHOW TABLES LIKE 'mail_%';"

# Should see tables like:
# - mail_billing_history
# - mail_aliases (updated)
# - mail_domains (updated)
# - mail_mailboxes (updated)
# And NO tables like mail_mail_* (double prefix)
```

### 3. Test the Application

#### A. Test as New User:
1. Go to `http://your-site.com/projects/mail`
2. Click "Subscribe" button
3. Choose a plan and subscribe
4. You should land on subscriber dashboard ✅

#### B. Test Domains (Previously: Access Denied):
1. From dashboard, click "Domains"
2. Click "Add New Domain"
3. Enter domain name → Should work! ✅

#### C. Test Aliases (Previously: Error):
1. From dashboard, click "Aliases"  
2. Should show alias list (empty if new) ✅
3. Click "Add Alias" → Should work! ✅

#### D. Test Users (Previously: Blank Page):
1. From dashboard, click "Users"
2. Click "Add New User"
3. Form should display ✅

#### E. Test Webmail (Previously: No Mailbox Error):
1. Add a mailbox user first (step D)
2. Then click "Webmail" in navbar
3. Should show inbox ✅

#### F. Test Upgrade (Previously: Redirect Loop):
1. From dashboard, click "Upgrade Plan"
2. Or go directly to: `/projects/mail/subscriber/upgrade?plan=4`
3. Should upgrade and redirect to dashboard ✅

#### G. Test Admin Billing (Previously: 404):
1. Login as admin
2. Go to `/admin/projects/mail/subscribers`
3. Click on any subscriber
4. Go to `/admin/projects/mail/subscribers/1/billing`
5. Should show billing page ✅

## 🔍 Verification Checklist

Quick check to ensure everything works:

```bash
# Run this in MySQL to verify schema
mysql -u root -p your_database << EOF
-- Check billing history table
DESC mail_billing_history;

-- Check aliases has subscriber_id
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'mail_aliases' AND COLUMN_NAME = 'subscriber_id';

-- Check domains has DKIM columns
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'mail_domains' AND COLUMN_NAME = 'dkim_private_key';

-- Check for double-prefix tables (should be empty)
SHOW TABLES LIKE 'mail_mail_%';
EOF
```

Expected output:
- `mail_billing_history` table structure displayed ✅
- `subscriber_id` column found in aliases ✅
- `dkim_private_key` column found in domains ✅
- Empty result for double-prefix tables ✅

## ⚠️ Common Issues & Quick Fixes

### Issue 1: "Table mail_billing_history doesn't exist"
**Fix:** Run `create_billing_history_table.sql`
```bash
mysql -u root -p your_database < projects/mail/migrations/create_billing_history_table.sql
```

### Issue 2: Still seeing "Access Denied" on domains
**Check:**
```sql
-- Verify subscriber exists
SELECT * FROM mail_subscribers WHERE mmb_user_id = YOUR_USER_ID;

-- Verify subscription is active
SELECT * FROM mail_subscriptions WHERE subscriber_id = YOUR_SUBSCRIBER_ID AND status = 'active';
```

**Fix:** If no subscription, create one:
```sql
INSERT INTO mail_subscribers (mmb_user_id, account_name, status, created_at) 
VALUES (YOUR_USER_ID, 'Test Account', 'active', NOW());

INSERT INTO mail_subscriptions (subscriber_id, plan_id, status, created_at) 
VALUES (LAST_INSERT_ID(), 1, 'active', NOW());
```

### Issue 3: Upgrade plan not working
**Check:** Browser console and PHP error logs
**Fix:** Clear browser cache and try again. The redirect should work now.

### Issue 4: Webmail shows "No mailbox"
**Fix:** 
1. Go to `/projects/mail/subscriber/users/add`
2. Add yourself as a mailbox user
3. Then access webmail

### Issue 5: Migration script permission denied
**Fix:**
```bash
chmod +x projects/mail/migrations/run_migrations.sh
```

## 📝 What Changed?

### Controllers Fixed:
- ✅ `DashboardController` - Fixed authentication
- ✅ `SubscriberController` - Fixed upgrade/downgrade
- ✅ `DomainController` - Fixed subscriber check
- ✅ `AliasController` - Fixed subscriber check
- ✅ `WebmailController` - Fixed mailbox check
- ✅ `MailAdminController` - Added billing method

### Database Changes:
- ✅ Created `mail_billing_history` table
- ✅ Fixed `mail_aliases` structure
- ✅ Fixed `mail_domains` structure
- ✅ Fixed `mail_mailboxes` structure
- ✅ Renamed double-prefix tables
- ✅ Created missing core tables

### Views Added:
- ✅ `getting-started.php` - Landing page
- ✅ `subscribe.php` - Pricing plans

### Routes Added:
- ✅ `/admin/projects/mail/subscribers/{id}/billing`

## 🎯 Next Steps After Setup

1. **Test thoroughly** - Go through each feature
2. **Check error logs** - Look for any remaining issues
3. **Setup payment gateway** - If using paid plans
4. **Configure SMTP** - For actual email sending
5. **Setup DNS** - For domain verification

## 💡 Pro Tips

1. **Backup database** before running migrations
2. **Test on staging** before production
3. **Check PHP version** (needs 7.4+)
4. **Enable error reporting** during testing
5. **Monitor logs** after deployment

## 🆘 Need Help?

1. Check `FIXES_SUMMARY.md` for detailed explanations
2. Check `migrations/README.md` for migration details
3. Check PHP error logs: `/var/log/apache2/error.log`
4. Check MySQL error logs
5. Enable debug mode in config

## ✅ Success Criteria

You'll know everything works when:
- ✅ No "access denied" errors
- ✅ All forms display properly
- ✅ Upgrade/downgrade works
- ✅ Webmail accessible after creating mailbox
- ✅ Admin billing page loads
- ✅ No PHP errors in logs
- ✅ No MySQL errors

That's it! Your mail project should now be fully functional. 🎉
