# Deployment Scripts

This directory contains scripts for deploying and verifying the mail project deployment.

## Scripts

### verify_deployment.sh

Verifies that all mail project components are properly deployed and configured.

**Usage:**
```bash
bash scripts/verify_deployment.sh
```

**What it checks:**
- Core files (index.php, App.php, Router.php, Database.php)
- Mail project structure and files
- Mail controllers exist
- Debug logging is present in controllers
- Routes are properly configured
- Routes are loaded in mail index.php
- File permissions
- Web server configuration (.htaccess)

**Exit codes:**
- `0` - All checks passed or only warnings
- `1` - Critical errors found

### deploy_mail.sh

Deploys the mail project with proper verification, backup, and logging.

**Usage:**
```bash
bash scripts/deploy_mail.sh
```

**What it does:**
1. Runs pre-deployment verification
2. Checks Git status
3. Creates backup of current deployment
4. Sets correct file permissions
5. Clears PHP opcode cache
6. Creates deployment log
7. Runs post-deployment verification
8. Displays next steps

**Important Notes:**
- The script will prompt before continuing if uncommitted changes are detected
- Backups are stored in `backups/` directory
- Deployment logs are stored in `storage/logs/`

## Typical Deployment Workflow

1. **Before deployment:**
   ```bash
   # Verify everything is ready
   bash scripts/verify_deployment.sh
   ```

2. **Deploy:**
   ```bash
   # Run deployment script
   bash scripts/deploy_mail.sh
   ```

3. **After deployment:**
   ```bash
   # Monitor error logs
   tail -f storage/logs/error.log
   
   # Verify database setup
   php projects/mail/migrations/verify_mail_setup.php
   ```

4. **Test URLs:**
   - `/projects/mail/subscriber/domains` - Domain list
   - `/projects/mail/subscriber/domains/add` - Add domain form
   - `/projects/mail/subscriber/aliases` - Alias list
   - `/projects/mail/subscriber/aliases/add` - Add alias form
   - `/projects/mail/subscriber/users/add` - Add user form
   - `/projects/mail/subscriber/billing` - Billing history
   - `/projects/mail/webmail` - Webmail inbox
   - `/projects/mail/subscriber/upgrade?plan=4` - Upgrade plan
   - `/admin/projects/mail/subscribers/1/billing` - Admin billing

## Debug Logging

All mail controllers now have comprehensive debug logging:
- **DomainController** - Logs domain operations (index, create, store)
- **AliasController** - Logs alias operations (index, create, store)
- **SubscriberController** - Logs subscriber operations (dashboard, addUser, billing, upgradePlan)
- **WebmailController** - Logs webmail operations (inbox, viewEmail)

Logs are written to PHP's error log (typically `storage/logs/error.log` or server's error log).

To view logs:
```bash
# View error log
tail -f storage/logs/error.log

# Search for specific controller logs
grep "DomainController" storage/logs/error.log
grep "AliasController" storage/logs/error.log
grep "SubscriberController" storage/logs/error.log
grep "WebmailController" storage/logs/error.log
```

## Troubleshooting

### Verification fails
1. Check the error messages - they will indicate what's missing
2. Ensure all controllers exist in `projects/mail/controllers/`
3. Verify routes file exists at `projects/mail/routes/web.php`
4. Check file permissions on storage directory

### Deployment issues
1. Check deployment log in `storage/logs/deployment_*.log`
2. Verify PHP opcode cache was cleared
3. Check file permissions are correct (755 for directories, 644 for files)
4. Ensure database configuration is correct in `config/database.php`

### Routes not working
1. Check that routes are loaded in `projects/mail/index.php`
2. Verify `.htaccess` file has correct rewrite rules
3. Ensure mod_rewrite is enabled on Apache
4. Check error logs for routing errors

### Database connection issues
1. Verify database credentials in `config/database.php`
2. Run database verification: `php projects/mail/migrations/verify_mail_setup.php`
3. Check if all required tables exist
4. Verify database user has proper permissions

## Server-Specific Notes

### Apache
- Ensure `mod_rewrite` is enabled: `sudo a2enmod rewrite`
- Set `AllowOverride All` in VirtualHost configuration
- Restart Apache after changes: `sudo systemctl restart apache2`

### Nginx
- Add rewrite rules to your site configuration:
  ```nginx
  location / {
      try_files $uri $uri/ /index.php?url=$uri&$query_string;
  }
  ```
- Reload Nginx: `sudo systemctl reload nginx`

### File Permissions
```bash
# Set correct permissions
chmod -R 755 projects/mail
chmod -R 777 storage
chmod 644 projects/mail/config.php
```

## Maintenance

### Regular checks
```bash
# Run verification periodically
bash scripts/verify_deployment.sh

# Check error logs for issues
grep -i "error\|warning" storage/logs/error.log | tail -20
```

### Before updates
```bash
# Always create backup before updates
tar -czf backup_$(date +%Y%m%d).tar.gz projects/mail
```

## Contact

For issues or questions, check the project documentation or review the deployment logs.
