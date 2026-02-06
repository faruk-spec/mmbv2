# Database Migration Required for Google OAuth Users

## Issue
Google OAuth users may encounter errors when accessing the system if the `oauth_only` column is missing from the `users` table.

## Solution
Run the following migration script to add the required column:

```bash
mysql -u your_username -p your_database < install/migrations/add_oauth_only_column.sql
```

Or manually execute:

```sql
ALTER TABLE users 
ADD COLUMN oauth_only TINYINT(1) DEFAULT 0 COMMENT 'User only has OAuth login (no manual password set)';

ADD INDEX idx_oauth_only (oauth_only);
```

## What This Column Does
The `oauth_only` column tracks users who signed up exclusively through Google OAuth and have never set a manual password. This is used to:
- Prevent OAuth-only users from unlinking their Google account without setting a password first
- Show the correct password form (Set Password vs Change Password)
- Ensure users always have a way to log in

## When to Run This Migration
Run this migration if you see errors like:
- "Unknown column 'oauth_only' in 'where clause'"
- Google OAuth users unable to access their accounts
- Password management features not working for OAuth users

## Production Deployment
Make sure to run this migration during deployment before enabling Google OAuth in production.
