# Database Setup Guide for MMB Projects

## Overview

This guide will help you set up the databases for CodeXPro, ImgTxt, and ProShare projects. Each project requires its own database tables.

---

## 🎯 Quick Summary

**You need to**:
1. Create 3 separate databases (recommended) OR use tables in your existing database
2. Import the schema files for each project
3. Update database configuration
4. Test the connection

---

## 📋 Option 1: Separate Databases (RECOMMENDED)

This approach keeps each project's data isolated and is easier to manage.

### Step 1: Create Databases

```bash
# Access MySQL
mysql -u testuser -p

# Create databases
CREATE DATABASE testuser_codexpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE testuser_imgtxt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE testuser_proshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Grant permissions
GRANT ALL PRIVILEGES ON testuser_codexpro.* TO 'testuser'@'localhost';
GRANT ALL PRIVILEGES ON testuser_imgtxt.* TO 'testuser'@'localhost';
GRANT ALL PRIVILEGES ON testuser_proshare.* TO 'testuser'@'localhost';
FLUSH PRIVILEGES;

# Exit
exit;
```

### Step 2: Import Schema Files

```bash
# Navigate to your project directory
cd /www/wwwroot/test.mymultibranch.com

# Import CodeXPro schema
mysql -u testuser -p testuser_codexpro < projects/codexpro/schema.sql

# Import ImgTxt schema
mysql -u testuser -p testuser_imgtxt < projects/imgtxt/schema.sql

# Import ProShare schema
mysql -u testuser -p testuser_proshare < projects/proshare/schema.sql
```

### Step 3: Verify Tables Created

```bash
# Check CodeXPro tables
mysql -u testuser -p testuser_codexpro -e "SHOW TABLES;"

# Check ImgTxt tables
mysql -u testuser -p testuser_imgtxt -e "SHOW TABLES;"

# Check ProShare tables
mysql -u testuser -p testuser_proshare -e "SHOW TABLES;"
```

**Expected Output for CodeXPro**:
```
+----------------------------+
| Tables_in_testuser_codexpro|
+----------------------------+
| activity_logs              |
| projects                   |
| snippets                   |
| templates                  |
| user_settings              |
+----------------------------+
```

**Expected Output for ImgTxt**:
```
+-------------------------+
| Tables_in_testuser_imgtxt|
+-------------------------+
| activity_logs           |
| batch_jobs              |
| ocr_jobs                |
| usage_stats             |
| user_settings           |
+-------------------------+
```

**Expected Output for ProShare**:
```
+----------------------------+
| Tables_in_testuser_proshare|
+----------------------------+
| backups                    |
| chat_messages              |
| chat_participants          |
| chat_rooms                 |
| downloads                  |
| files                      |
| link_access_control        |
| notifications              |
| text_shares                |
| text_views                 |
| user_settings              |
+----------------------------+
```

### Step 4: Update Database Configuration

Update the database configuration in each project's config file:

**For CodeXPro**: `projects/codexpro/config.php`
```php
'database' => [
    'host' => 'localhost',
    'name' => 'testuser_codexpro',
    'user' => 'testuser',
    'pass' => 'your_password',
    'charset' => 'utf8mb4'
]
```

**For ImgTxt**: `projects/imgtxt/config.php`
```php
'database' => [
    'host' => 'localhost',
    'name' => 'testuser_imgtxt',
    'user' => 'testuser',
    'pass' => 'your_password',
    'charset' => 'utf8mb4'
]
```

**For ProShare**: `projects/proshare/config.php`
```php
'database' => [
    'host' => 'localhost',
    'name' => 'testuser_proshare',
    'user' => 'testuser',
    'pass' => 'your_password',
    'charset' => 'utf8mb4'
]
```

---

## 📋 Option 2: Using phpMyAdmin

If you prefer a visual interface:

### Step 1: Create Databases

1. Log in to phpMyAdmin
2. Click "Databases" tab
3. Enter database name: `testuser_codexpro`
4. Select collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Repeat for `testuser_imgtxt` and `testuser_proshare`

### Step 2: Import Schema Files

For each database:

1. Click on the database name in the left sidebar
2. Click "Import" tab
3. Click "Choose File"
4. Select the schema file:
   - `projects/codexpro/schema.sql` for testuser_codexpro
   - `projects/imgtxt/schema.sql` for testuser_imgtxt
   - `projects/proshare/schema.sql` for testuser_proshare
5. Click "Go" at the bottom
6. Wait for success message: "Import has been successfully finished"

### Step 3: Verify Tables

1. Click on the database name
2. You should see the list of tables
3. Click on any table name to view its structure

---

## 📋 Option 3: Single Database (Alternative)

If you prefer to use your existing database with prefixed tables:

### Step 1: Modify Schema Files

You need to add a prefix to all table names in the schema files. For example, in `projects/codexpro/schema.sql`, change:

```sql
CREATE TABLE projects (
```

to:

```sql
CREATE TABLE codexpro_projects (
```

Do this for all tables in all three schema files:
- CodeXPro tables: `codexpro_*`
- ImgTxt tables: `imgtxt_*`
- ProShare tables: `proshare_*`

### Step 2: Import to Existing Database

```bash
mysql -u testuser -p testuser < projects/codexpro/schema.sql
mysql -u testuser -p testuser < projects/imgtxt/schema.sql
mysql -u testuser -p testuser < projects/proshare/schema.sql
```

### Step 3: Update Configuration

Update each project's config to use the main database and include table prefixes.

---

## 🚨 Common Issues & Solutions

### Issue 1: "Access denied for user"

**Error**: `Access denied for user 'testuser'@'localhost'`

**Solution**:
```bash
mysql -u root -p
GRANT ALL PRIVILEGES ON testuser_codexpro.* TO 'testuser'@'localhost';
GRANT ALL PRIVILEGES ON testuser_imgtxt.* TO 'testuser'@'localhost';
GRANT ALL PRIVILEGES ON testuser_proshare.* TO 'testuser'@'localhost';
FLUSH PRIVILEGES;
exit;
```

### Issue 2: "Table already exists"

**Error**: `Table 'projects' already exists`

**Solution**: Drop the database and recreate it:
```bash
mysql -u testuser -p
DROP DATABASE testuser_codexpro;
CREATE DATABASE testuser_codexpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
# Then import again
```

### Issue 3: "Can't create database"

**Error**: `Can't create database 'testuser_codexpro'`

**Solution**: You need root/admin privileges:
```bash
mysql -u root -p
CREATE DATABASE testuser_codexpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON testuser_codexpro.* TO 'testuser'@'localhost';
exit;
```

### Issue 4: Projects return 403 Forbidden

**Issue**: Accessing `/projects/codexpro` returns 403 Forbidden

**Reason**: The root `.htaccess` file blocks direct access to the `projects/` directory for security reasons.

**Solution**: Projects are designed to be accessed through the main routing system, not as subdirectories. After database setup, access projects through:
- Admin Panel: `http://test.mymultibranch.com/admin/dashboard`
- Admin can manage all projects from there

### Issue 5: "Unknown database"

**Error**: `Unknown database 'testuser_codexpro'`

**Solution**: Database wasn't created. Follow Step 1 again to create the databases.

---

## ✅ Verification Checklist

After setup, verify everything works:

```bash
# Test database connections
mysql -u testuser -p testuser_codexpro -e "SELECT COUNT(*) FROM projects;"
mysql -u testuser -p testuser_imgtxt -e "SELECT COUNT(*) FROM ocr_jobs;"
mysql -u testuser -p testuser_proshare -e "SELECT COUNT(*) FROM files;"
```

All commands should return `COUNT(*) = 0` (empty tables) without errors.

---

## 🔐 Security Notes

1. **Never commit database passwords** to version control
2. **Use strong passwords** for database users
3. **Limit database user privileges** to only what's needed
4. **Regular backups**: Set up automatic database backups
5. **Monitor access**: Check database logs regularly

---

## 📊 Database Structure Summary

### CodeXPro (5 tables):
- `projects` - User code projects with HTML/CSS/JS
- `snippets` - Reusable code snippets
- `templates` - Project templates
- `user_settings` - Editor preferences
- `activity_logs` - Audit trail

### ImgTxt (5 tables):
- `ocr_jobs` - OCR processing jobs
- `batch_jobs` - Batch processing tracking
- `usage_stats` - Usage statistics
- `user_settings` - User preferences
- `activity_logs` - Audit trail

### ProShare (11 tables):
- `files` - File metadata and tracking
- `text_shares` - Text snippets sharing
- `downloads` - Download tracking
- `text_views` - Text view tracking
- `chat_rooms` - Chat rooms
- `chat_messages` - Chat messages
- `chat_participants` - Room participants
- `link_access_control` - Access permissions
- `notifications` - User notifications
- `user_settings` - User preferences
- `backups` - Backup metadata

---

## 🎯 Next Steps After Database Setup

1. **Test Admin Panel**: Visit `http://test.mymultibranch.com/admin/dashboard`
2. **Verify Statistics**: Check if project statistics load correctly
3. **Test CRUD Operations**: Try creating/editing through admin panel
4. **Monitor Logs**: Check `activity_logs` table for entries
5. **Ready for Phase 4**: Once verified, proceed to real-time features

---

## 📞 Need Help?

If you encounter issues:

1. Check the error message carefully
2. Look in this guide's "Common Issues" section
3. Verify database credentials in config files
4. Check MySQL error logs: `/var/log/mysql/error.log`
5. Test database connection manually with mysql command

---

## 🎉 Success!

Once you see tables listed in all three databases without errors, you're ready to use the admin panel and projects!

Access your admin panel at: `http://test.mymultibranch.com/admin/dashboard`
