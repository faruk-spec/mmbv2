# URGENT: Deployment Required

## ⚠️ IMPORTANT: Code Changes Must Be Deployed

The fixes are in this branch but **NOT yet deployed to the live site**. You are seeing errors because:

1. ✅ Code is fixed in this repository (copilot/fix-access-denied-issues branch)
2. ❌ Code has NOT been deployed to https://test.mymultibranch.com
3. ❌ Database migrations have NOT been run on testuser database

## Step 1: Deploy Code to Live Site

You need to update the code on your server:

```bash
# SSH to your server
ssh your-server

# Navigate to your application directory
cd /path/to/mmbv2

# Pull the latest changes from this branch
git fetch origin
git checkout copilot/fix-access-denied-issues
git pull origin copilot/fix-access-denied-issues

# Clear any caches
# (adjust based on your setup)
php artisan cache:clear  # if using Laravel
# or
rm -rf storage/cache/*
```

## Step 2: Run Database Migrations

**CRITICAL:** You must run these migrations in order:

### Option A: Automated Script (Recommended)
```bash
cd projects/mail/migrations
chmod +x run_migrations.sh
./run_migrations.sh

# When prompted, enter:
# Database host: localhost (or your DB host)
# Database name: testuser
# Database user: your_db_user
# Database password: your_db_password
```

### Option B: Manual Migration (if script fails)
```bash
cd projects/mail/migrations

# Run in this exact order:
mysql -u your_user -p testuser < fix_table_names.sql
mysql -u your_user -p testuser < create_billing_history_table.sql
mysql -u your_user -p testuser < update_domains_table.sql
mysql -u your_user -p testuser < update_mailboxes_table.sql
mysql -u your_user -p testuser < update_aliases_table.sql
mysql -u your_user -p testuser < add_currency_column.sql
```

## Step 3: Verify Migrations

Check that tables were created:

```sql
-- Connect to your database
mysql -u your_user -p testuser

-- Check for mail_billing_history table
DESC mail_billing_history;

-- Check for double-prefix tables (should be EMPTY)
SHOW TABLES LIKE 'mail_mail_%';

-- Check aliases has subscriber_id
DESC mail_aliases;

-- Exit MySQL
EXIT;
```

## Step 4: Restart Web Server

```bash
# Depending on your setup:
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
sudo systemctl restart php-fpm
# or
sudo service apache2 restart
```

## Step 5: Test the Application

After deployment and migrations, test these URLs:

- ✅ https://test.mymultibranch.com/projects/mail/subscriber/domains
- ✅ https://test.mymultibranch.com/projects/mail/subscriber/aliases
- ✅ https://test.mymultibranch.com/projects/mail/subscriber/users/add
- ✅ https://test.mymultibranch.com/projects/mail/subscriber/billing
- ✅ https://test.mymultibranch.com/projects/mail/webmail
- ✅ https://test.mymultibranch.com/admin/projects/mail/subscribers/1/billing

## Common Issues

### Issue: "Still seeing access denied"
**Solution:** You haven't deployed the code. Run Step 1.

### Issue: "Table doesn't exist"
**Solution:** You haven't run migrations. Run Step 2.

### Issue: "Syntax error, unexpected variable $this"
**Solution:** This means old code is still running. Clear caches and restart web server.

### Issue: "No active mailbox found"
**Solution:** After migrations, you need to:
1. Go to /projects/mail/subscriber/users/add
2. Add a mailbox user
3. Then access webmail

## Verification Checklist

Before reporting issues, verify:

- [ ] Code deployed (check file timestamps on server)
- [ ] Migrations run (check tables exist in database)
- [ ] Web server restarted
- [ ] Cache cleared
- [ ] PHP error logs checked
- [ ] MySQL error logs checked

## Need Help?

If you still have issues AFTER completing all steps above:

1. Check PHP error logs: `tail -f /var/log/apache2/error.log`
2. Check MySQL errors: Check the output from migration script
3. Verify you're on the correct branch: `git branch` (should show copilot/fix-access-denied-issues)
4. Verify file timestamps: `ls -la projects/mail/controllers/SubscriberController.php`

## Quick Deployment Script

If you have SSH access, save this as `deploy.sh` and run it:

```bash
#!/bin/bash
set -e

echo "Deploying mail project fixes..."

# Update code
git fetch origin
git checkout copilot/fix-access-denied-issues
git pull origin copilot/fix-access-denied-issues

# Run migrations
cd projects/mail/migrations
chmod +x run_migrations.sh
./run_migrations.sh

# Clear cache (adjust for your setup)
cd ../../..
rm -rf storage/cache/* 2>/dev/null || true

# Restart services
sudo systemctl restart apache2 || sudo service apache2 restart

echo "Deployment complete! Please test the application."
```

---

**IMPORTANT:** The code in this repository IS CORRECT. You just need to deploy it to your live server and run the migrations!
