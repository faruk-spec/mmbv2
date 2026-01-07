# Live Server Deployment Checklist

Use this checklist when deploying to the live server.

## Pre-Deployment

- [ ] Pull latest changes: `git pull origin copilot/fix-mail-project-deployment`
- [ ] Check current branch: `git branch` (should show copilot/fix-mail-project-deployment)
- [ ] Verify scripts exist: `ls -la scripts/`
- [ ] Make scripts executable: `chmod +x scripts/*.sh`

## Verification

- [ ] Run verification script: `bash scripts/verify_deployment.sh`
- [ ] Expected result: ✅ Pass with 1 warning (database verification)
- [ ] If errors found: Fix them before proceeding
- [ ] If warnings only: Review and proceed

## Deployment

- [ ] Run deployment script: `bash scripts/deploy_mail.sh`
- [ ] Confirm when prompted about uncommitted changes (if any)
- [ ] Wait for backup creation
- [ ] Wait for deployment completion
- [ ] Check deployment log: `cat storage/logs/deployment_*.log`

## Database Verification

- [ ] Run: `php projects/mail/migrations/verify_mail_setup.php`
- [ ] Verify all required tables exist
- [ ] Check database connectivity works
- [ ] Fix any database issues before testing URLs

## URL Testing

Test each URL and mark when working:

### Domain Management
- [ ] http://your-domain.com/projects/mail/subscriber/domains
  - Should show: Domain list page
  - Expected: Table with domains or "No domains" message
  
- [ ] http://your-domain.com/projects/mail/subscriber/domains/add
  - Should show: Add domain form
  - Expected: Form with domain name input

### Alias Management
- [ ] http://your-domain.com/projects/mail/subscriber/aliases
  - Should show: Alias list page
  - Expected: Table with aliases or "No aliases" message
  
- [ ] http://your-domain.com/projects/mail/subscriber/aliases/add
  - Should show: Add alias form
  - Expected: Form with alias name and domain selector

### User Management
- [ ] http://your-domain.com/projects/mail/subscriber/users/add
  - Should show: Add user/mailbox form
  - Expected: Form with email and password inputs

### Billing
- [ ] http://your-domain.com/projects/mail/subscriber/billing
  - Should show: Billing history page
  - Expected: Table with billing records

### Webmail
- [ ] http://your-domain.com/projects/mail/webmail
  - Should show: Inbox or "create mailbox" message
  - Expected: Email list or setup prompt

### Upgrade
- [ ] http://your-domain.com/projects/mail/subscriber/upgrade?plan=4
  - Should show: Upgrade plan page
  - Expected: Plan selection interface

### Admin
- [ ] http://your-domain.com/admin/projects/mail/subscribers/1/billing
  - Should show: Admin billing view for subscriber
  - Expected: Billing information (admin access required)

## Log Monitoring

### Check for Debug Logs
- [ ] Open error log: `tail -f storage/logs/error.log`
- [ ] Test a URL (e.g., domains page)
- [ ] Verify debug logs appear like:
  ```
  [DomainController::index] START - User: 123
  [DomainController::index] Subscriber ID: 45
  [DomainController::index] Rendering view with 3 domains
  ```

### Check for Errors
- [ ] Look for PHP errors in log
- [ ] Look for database errors
- [ ] Look for authentication errors
- [ ] If errors found: Note them and investigate using deployment guide

## Functional Testing

After URLs are accessible, test functionality:

### Domains
- [ ] Can add a new domain
- [ ] Domain appears in list
- [ ] Can view DNS records
- [ ] Can verify domain (if DNS configured)

### Aliases
- [ ] Can add a new alias
- [ ] Alias appears in list
- [ ] Can toggle alias status
- [ ] Can delete alias

### Users
- [ ] Can add a new user/mailbox
- [ ] User appears in list
- [ ] User can login (if applicable)

### Billing
- [ ] Billing history displays correctly
- [ ] Transaction records visible

### Webmail
- [ ] Can access inbox
- [ ] Can compose email (if mailbox exists)
- [ ] Can view email

## Post-Deployment Verification

- [ ] No 404 errors on any tested URL
- [ ] No PHP errors in log
- [ ] Debug logs showing proper execution flow
- [ ] All forms display correctly
- [ ] Database queries executing successfully

## Rollback (If Needed)

If issues occur and cannot be fixed quickly:

- [ ] Find backup: `ls -lah backups/`
- [ ] Restore backup: `tar -xzf backups/mail_backup_TIMESTAMP.tar.gz -C projects/`
- [ ] Clear PHP cache: `php -r "opcache_reset();"`
- [ ] Or restart PHP: `sudo systemctl restart php-fpm`
- [ ] Test URL again
- [ ] Document issue for later investigation

## Success Confirmation

Mark when all criteria are met:

- [ ] All URLs accessible (no 404)
- [ ] No PHP errors in logs
- [ ] Debug logs present and showing execution
- [ ] Forms display correctly
- [ ] Can perform CRUD operations
- [ ] Database connectivity verified
- [ ] Performance acceptable

## Final Steps

- [ ] Document any issues encountered
- [ ] Note any warnings or errors that were fixed
- [ ] Save deployment log for reference
- [ ] Update team on deployment status
- [ ] Monitor logs for 24 hours for unexpected issues

## Notes

Use this section to document any issues, solutions, or observations:

```
Date: ________________
Time: ________________
Deployed by: ________________

Issues encountered:


Solutions applied:


Performance notes:


Additional observations:


```

## Reference

- **Deployment Guide:** `MAIL_DEPLOYMENT_GUIDE.md`
- **Scripts Help:** `scripts/README.md`
- **PR Summary:** `PR_SUMMARY.md`

## Support

If you encounter issues:
1. Check error log: `storage/logs/error.log`
2. Review debug logs for specific controller
3. Check deployment log: `storage/logs/deployment_*.log`
4. Consult troubleshooting section in `MAIL_DEPLOYMENT_GUIDE.md`

---

✅ **When all items are checked, deployment is successful!**
