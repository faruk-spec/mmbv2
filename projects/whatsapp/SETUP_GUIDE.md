# WhatsApp API Platform - Complete Setup Guide

## Prerequisites
- MySQL database server
- PHP 7.4 or higher
- Main MMB platform installed and configured

## Step-by-Step Installation

### Step 1: Register WhatsApp in Main Platform

This step is **CRITICAL** - it registers WhatsApp in the `home_projects` table, which:
- Makes WhatsApp visible in dashboard and home page
- Enables access control (prevents redirect loops)
- Configures project routing

```bash
mysql -u your_username -p YOUR_MAIN_DATABASE < projects/whatsapp/install.sql
```

**Replace:** `YOUR_MAIN_DATABASE` with your main platform database name (e.g., `testuser`, `mmbtech`, etc.)

**What it does:**
- Inserts WhatsApp into `home_projects` table
- Sets `is_enabled = 1` to allow access
- Configures project key, name, icon, color, database name

### Step 2: Create WhatsApp Core Tables

Creates the 6 core tables for WhatsApp functionality:

```bash
mysql -u your_username -p mmb_whatsapp < projects/whatsapp/schema.sql
```

**Tables created:**
1. `whatsapp_sessions` - WhatsApp connection sessions
2. `whatsapp_messages` - Message history
3. `whatsapp_contacts` - Synced contacts
4. `whatsapp_api_keys` - User API keys
5. `whatsapp_user_settings` - User preferences
6. `whatsapp_api_logs` - API request logs

### Step 3: Create Subscription Tables

Creates the subscription management system:

```bash
mysql -u your_username -p mmb_whatsapp < projects/whatsapp/subscription_schema.sql
```

**Tables created:**
1. `whatsapp_subscription_plans` - Subscription tier definitions
2. `whatsapp_subscriptions` - User subscriptions
3. `whatsapp_subscription_details` - Optimized view for queries

**Default plans included:**
- Free: 100 messages, 1 session, 1K API calls
- Basic: 1K messages, 3 sessions, 10K API calls ($9.99/month)
- Premium: 10K messages, 10 sessions, 100K API calls ($29.99/month)
- Enterprise: Unlimited everything ($99.99/month)

## Verification

After running all 3 SQL files, verify the installation:

### 1. Check Home Projects Table

```sql
SELECT * FROM home_projects WHERE project_key = 'whatsapp';
```

Should return 1 row with `is_enabled = 1`

### 2. Check WhatsApp Database

```sql
USE mmb_whatsapp;
SHOW TABLES;
```

Should show 9 items:
- 6 core tables (sessions, messages, contacts, api_keys, user_settings, api_logs)
- 2 subscription tables (subscription_plans, subscriptions)
- 1 view (subscription_details)

### 3. Access the Platform

**User Dashboard:**
- Visit: `https://yourdomain.com/projects/whatsapp`
- Should load without redirect loops
- Should show dashboard with statistics

**Admin Panel:**
- Visit: `https://yourdomain.com/admin/whatsapp/overview`
- Should show admin dashboard

**Subscription Management:**
- Visit: `https://yourdomain.com/admin/whatsapp/subscription-plans`
- Should show 4 default plans

## Common Issues & Solutions

### Issue: Redirect Loop When Accessing WhatsApp

**Symptom:** Clicking WhatsApp in dashboard redirects back to dashboard

**Cause:** WhatsApp not registered in `home_projects` table

**Solution:** Run `install.sql` on your MAIN database (Step 1)

### Issue: Subscription Tables Not Found

**Symptom:** Error "Table 'whatsapp_subscription_plans' doesn't exist"

**Cause:** Subscription schema not imported

**Solution:** Run `subscription_schema.sql` (Step 3)

### Issue: Access Denied / 403 Error

**Symptom:** "You don't have access to this project"

**Causes:**
1. WhatsApp not in `home_projects` table → Run `install.sql`
2. `is_enabled = 0` in `home_projects` → Update to 1:
   ```sql
   UPDATE home_projects SET is_enabled = 1 WHERE project_key = 'whatsapp';
   ```

### Issue: Database Connection Errors

**Symptom:** "SQLSTATE[HY000] [1049] Unknown database"

**Cause:** Database `mmb_whatsapp` doesn't exist

**Solution:** Create the database first:
```sql
CREATE DATABASE mmb_whatsapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Access Points After Setup

### User Interface
- **Dashboard:** `/projects/whatsapp`
- **Sessions:** `/projects/whatsapp/sessions`
- **Messages:** `/projects/whatsapp/messages`
- **Contacts:** `/projects/whatsapp/contacts`
- **API Docs:** `/projects/whatsapp/api-docs`
- **Settings:** `/projects/whatsapp/settings`
- **Subscription:** `/projects/whatsapp/subscription`

### Admin Panel
- **Overview:** `/admin/whatsapp/overview`
- **Sessions:** `/admin/whatsapp/sessions`
- **Messages:** `/admin/whatsapp/messages`
- **Users:** `/admin/whatsapp/users`
- **API Logs:** `/admin/whatsapp/api-logs`
- **Plans:** `/admin/whatsapp/subscription-plans`
- **User Subscriptions:** `/admin/whatsapp/user-subscriptions`

### REST API
- **Base:** `/api/whatsapp/*`
- **Auth:** Bearer token (whapi_* API keys)
- **Docs:** Available in-platform at `/projects/whatsapp/api-docs`

## Next Steps

1. **Assign Subscriptions:** Go to `/admin/whatsapp/user-subscriptions/assign` to assign plans to users
2. **Configure API Keys:** Users can generate API keys at `/projects/whatsapp/settings`
3. **Test Integration:** Use the API docs to test endpoints
4. **Production Integration:** Integrate with WhatsApp Web client (see INSTALLATION.md)

## Support

For issues or questions:
1. Check this guide for common issues
2. Review INSTALLATION.md for detailed configuration
3. Check IMPLEMENTATION_SUMMARY.md for technical details

## Database Configuration

The platform uses two databases:

1. **Main Database** (e.g., `testuser`, `mmbtech`)
   - Contains `home_projects` table
   - Stores platform-wide configuration

2. **WhatsApp Database** (`mmb_whatsapp`)
   - Contains all WhatsApp-specific tables
   - Isolated for security and scalability

## Upgrading

To upgrade, pull latest changes and run:

```bash
# Update main registration if needed
mysql -u user -p YOUR_MAIN_DATABASE < projects/whatsapp/install.sql

# Update schema (safe with IF NOT EXISTS)
mysql -u user -p mmb_whatsapp < projects/whatsapp/schema.sql
mysql -u user -p mmb_whatsapp < projects/whatsapp/subscription_schema.sql
```

The SQL files use `IF NOT EXISTS` and `ON DUPLICATE KEY UPDATE`, so they're safe to run multiple times.
