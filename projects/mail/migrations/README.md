# Mail Project Database Migrations

This directory contains SQL migration files to update the mail project database schema.

## Migration Files

### 1. create_billing_history_table.sql
Creates the `mail_billing_history` table for storing payment and transaction history.

**Dependencies:** Requires `mail_subscribers` and `mail_subscriptions` tables.

### 2. fix_table_names.sql
Fixes incorrectly named tables with double prefixes (e.g., `mail_mail_folders` → `mail_folders`).
Also creates missing core tables like `mail_messages`, `mail_attachments`, `mail_queue`, etc.

**Important:** Run this before other migrations if your database has the double-prefix issue.

### 3. update_aliases_table.sql
Updates the `mail_aliases` table structure to match controller expectations:
- Adds `subscriber_id` column
- Adds `alias_email` column
- Adds `destination_type` and `destination_mailbox_id` columns
- Adds `destination_email` column
- Migrates existing data to new structure

**Dependencies:** Requires `mail_domains` and `mail_subscribers` tables.

### 4. update_domains_table.sql
Adds missing columns to `mail_domains`:
- `description` - Domain description
- `catch_all_email` - Catch-all email address
- `dkim_private_key` - DKIM private key
- `dkim_public_key` - DKIM public key

Also adds `last_verified_at` to `mail_dns_records`.

### 5. update_mailboxes_table.sql
Adds the `signature` column to `mail_mailboxes` for storing email signatures.

### 6. add_currency_column.sql (existing)
Adds currency column to payments table.

## How to Run Migrations

### Option 1: Run All Migrations (Recommended)
```bash
cd /path/to/mmbv2/projects/mail/migrations
mysql -u your_username -p your_database < fix_table_names.sql
mysql -u your_username -p your_database < create_billing_history_table.sql
mysql -u your_username -p your_database < update_aliases_table.sql
mysql -u your_username -p your_database < update_domains_table.sql
mysql -u your_username -p your_database < update_mailboxes_table.sql
mysql -u your_username -p your_database < add_currency_column.sql
```

### Option 2: Run Individually
Run each migration file based on your needs:
```bash
mysql -u your_username -p your_database < migration_file.sql
```

### Option 3: Using MySQL Workbench or phpMyAdmin
1. Open your database management tool
2. Select your database
3. Open each SQL file and execute it in order

## Migration Order

**IMPORTANT:** Run migrations in this order to avoid foreign key errors:

1. `fix_table_names.sql` - Fix table naming issues first
2. `create_billing_history_table.sql` - Create billing history table
3. `update_domains_table.sql` - Update domains structure
4. `update_mailboxes_table.sql` - Update mailboxes structure
5. `update_aliases_table.sql` - Update aliases structure (depends on domains)
6. `add_currency_column.sql` - Add currency column

## Post-Migration Verification

After running migrations, verify the changes:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'mail_%';

-- Check mail_billing_history structure
DESC mail_billing_history;

-- Check mail_aliases structure
DESC mail_aliases;

-- Check mail_domains structure
DESC mail_domains;

-- Check mail_mailboxes structure
DESC mail_mailboxes;

-- Check for double-prefix tables (should return empty)
SHOW TABLES LIKE 'mail_mail_%';
```

## Troubleshooting

### Error: Table already exists
This is normal if running migrations multiple times. The migrations use `IF NOT EXISTS` where possible.

### Error: Column already exists
Use `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` syntax or check if the column exists before running.

### Error: Foreign key constraint fails
Ensure you run migrations in the correct order and that parent tables exist.

### Error: Syntax error near "IF NOT EXISTS"
Your MySQL version might be too old. Update to MySQL 5.7+ or MariaDB 10.2+.

## Rollback

To rollback changes (use with caution):

```sql
-- Rollback billing history
DROP TABLE IF EXISTS mail_billing_history;

-- Rollback alias changes
ALTER TABLE mail_aliases 
  DROP COLUMN IF EXISTS subscriber_id,
  DROP COLUMN IF EXISTS alias_email,
  DROP COLUMN IF EXISTS destination_type;

-- Rollback domain changes
ALTER TABLE mail_domains
  DROP COLUMN IF EXISTS description,
  DROP COLUMN IF EXISTS catch_all_email,
  DROP COLUMN IF EXISTS dkim_private_key,
  DROP COLUMN IF EXISTS dkim_public_key;

-- Rollback mailbox changes
ALTER TABLE mail_mailboxes
  DROP COLUMN IF EXISTS signature;
```

## Notes

- Always backup your database before running migrations
- Test migrations on a development environment first
- Some migrations include data migration logic to preserve existing data
- The `fix_table_names.sql` migration is critical if you have tables with double prefixes

## Support

If you encounter issues:
1. Check MySQL error logs
2. Verify MySQL version compatibility
3. Ensure proper database permissions
4. Review the SQL syntax in the migration files
