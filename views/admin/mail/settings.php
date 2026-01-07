<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Mail Server Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/mail">Mail Server</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="/admin/projects/mail/settings/save" method="POST">
            <div class="row">
                <!-- General Settings -->
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">General Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="systemDomain">System Mail Domain</label>
                                <input type="text" class="form-control" id="systemDomain" name="system_domain" 
                                       value="<?= View::e($settings['system_domain'] ?? 'mail.yourdomain.com') ?>" required>
                                <small class="form-text text-muted">
                                    The primary domain for your mail server (e.g., mail.example.com)
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="systemEmail">System Email Address</label>
                                <input type="email" class="form-control" id="systemEmail" name="system_email" 
                                       value="<?= View::e($settings['system_email'] ?? 'noreply@yourdomain.com') ?>" required>
                                <small class="form-text text-muted">
                                    Email address used for system notifications
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="companyName">Company/Service Name</label>
                                <input type="text" class="form-control" id="companyName" name="company_name" 
                                       value="<?= View::e($settings['company_name'] ?? 'Mail Server') ?>" required>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="newSignups" name="allow_new_signups" 
                                           <?= ($settings['allow_new_signups'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="newSignups">
                                        Allow New Subscriber Signups
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="emailVerification" 
                                           name="require_email_verification" 
                                           <?= ($settings['require_email_verification'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="emailVerification">
                                        Require Email Verification for New Accounts
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SMTP Settings -->
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">SMTP Server Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="smtpHost">SMTP Host</label>
                                <input type="text" class="form-control" id="smtpHost" name="smtp_host" 
                                       value="<?= View::e($settings['smtp_host'] ?? 'localhost') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="smtpPort">SMTP Port</label>
                                <input type="number" class="form-control" id="smtpPort" name="smtp_port" 
                                       value="<?= View::e($settings['smtp_port'] ?? '587') ?>" required>
                                <small class="form-text text-muted">
                                    Standard ports: 25 (unencrypted), 587 (TLS), 465 (SSL)
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="smtpEncryption">SMTP Encryption</label>
                                <select class="form-control" id="smtpEncryption" name="smtp_encryption">
                                    <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="smtpTimeout">SMTP Timeout (seconds)</label>
                                <input type="number" class="form-control" id="smtpTimeout" name="smtp_timeout" 
                                       value="<?= View::e($settings['smtp_timeout'] ?? '30') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- IMAP Settings -->
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">IMAP Server Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="imapHost">IMAP Host</label>
                                <input type="text" class="form-control" id="imapHost" name="imap_host" 
                                       value="<?= View::e($settings['imap_host'] ?? 'localhost') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="imapPort">IMAP Port</label>
                                <input type="number" class="form-control" id="imapPort" name="imap_port" 
                                       value="<?= View::e($settings['imap_port'] ?? '993') ?>" required>
                                <small class="form-text text-muted">
                                    Standard ports: 143 (unencrypted), 993 (SSL)
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="imapEncryption">IMAP Encryption</label>
                                <select class="form-control" id="imapEncryption" name="imap_encryption">
                                    <option value="ssl" <?= ($settings['imap_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="tls" <?= ($settings['imap_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="none" <?= ($settings['imap_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="pop3Port">POP3 Port</label>
                                <input type="number" class="form-control" id="pop3Port" name="pop3_port" 
                                       value="<?= View::e($settings['pop3_port'] ?? '995') ?>" required>
                                <small class="form-text text-muted">
                                    Standard ports: 110 (unencrypted), 995 (SSL)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rate Limiting & Security -->
                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Rate Limiting & Security</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="maxSendRate">Max Emails Per Hour (Global)</label>
                                <input type="number" class="form-control" id="maxSendRate" name="max_send_rate" 
                                       value="<?= View::e($settings['max_send_rate'] ?? '1000') ?>" required>
                                <small class="form-text text-muted">
                                    Maximum number of emails that can be sent per hour across all users
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="maxLoginAttempts">Max Login Attempts</label>
                                <input type="number" class="form-control" id="maxLoginAttempts" name="max_login_attempts" 
                                       value="<?= View::e($settings['max_login_attempts'] ?? '5') ?>" required>
                                <small class="form-text text-muted">
                                    Number of failed login attempts before temporary lockout
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="lockoutDuration">Lockout Duration (minutes)</label>
                                <input type="number" class="form-control" id="lockoutDuration" name="lockout_duration" 
                                       value="<?= View::e($settings['lockout_duration'] ?? '15') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="maxAttachmentSize">Max Attachment Size (MB)</label>
                                <input type="number" class="form-control" id="maxAttachmentSize" name="max_attachment_size" 
                                       value="<?= View::e($settings['max_attachment_size'] ?? '25') ?>" required>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enableSpamFilter" 
                                           name="enable_spam_filter" 
                                           <?= ($settings['enable_spam_filter'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="enableSpamFilter">
                                        Enable Spam Filtering
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enableVirusScan" 
                                           name="enable_virus_scan" 
                                           <?= ($settings['enable_virus_scan'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="enableVirusScan">
                                        Enable Virus Scanning
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Storage Settings -->
                <div class="col-md-6">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Storage Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="storageBackend">Storage Backend</label>
                                <select class="form-control" id="storageBackend" name="storage_backend">
                                    <option value="local" <?= ($settings['storage_backend'] ?? 'local') === 'local' ? 'selected' : '' ?>>Local Filesystem (Maildir)</option>
                                    <option value="s3" <?= ($settings['storage_backend'] ?? '') === 's3' ? 'selected' : '' ?>>Amazon S3</option>
                                    <option value="gcs" <?= ($settings['storage_backend'] ?? '') === 'gcs' ? 'selected' : '' ?>>Google Cloud Storage</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="storagePath">Storage Path</label>
                                <input type="text" class="form-control" id="storagePath" name="storage_path" 
                                       value="<?= View::e($settings['storage_path'] ?? '/var/mail/vhosts') ?>" required>
                                <small class="form-text text-muted">
                                    Base directory for email storage (local filesystem only)
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="retentionDays">Email Retention Period (days)</label>
                                <input type="number" class="form-control" id="retentionDays" name="retention_days" 
                                       value="<?= View::e($settings['retention_days'] ?? '0') ?>">
                                <small class="form-text text-muted">
                                    Number of days to keep emails. 0 = keep forever
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Notification Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="adminEmail">Admin Notification Email</label>
                                <input type="email" class="form-control" id="adminEmail" name="admin_email" 
                                       value="<?= View::e($settings['admin_email'] ?? 'admin@yourdomain.com') ?>" required>
                                <small class="form-text text-muted">
                                    Email address to receive system notifications and alerts
                                </small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="notifyNewSubscriber" 
                                           name="notify_new_subscriber" 
                                           <?= ($settings['notify_new_subscriber'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="notifyNewSubscriber">
                                        Notify on New Subscriber Registration
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="notifyAbuseReport" 
                                           name="notify_abuse_report" 
                                           <?= ($settings['notify_abuse_report'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="notifyAbuseReport">
                                        Notify on New Abuse Report
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="notifyQuotaExceeded" 
                                           name="notify_quota_exceeded" 
                                           <?= ($settings['notify_quota_exceeded'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="notifyQuotaExceeded">
                                        Notify When Subscriber Exceeds Quota
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="notifySystemErrors" 
                                           name="notify_system_errors" 
                                           <?= ($settings['notify_system_errors'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="notifySystemErrors">
                                        Notify on System Errors
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save All Settings
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="testMailConnection()">
                                <i class="fas fa-vial"></i> Test Mail Server Connection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function testMailConnection() {
    if (!confirm('This will test the SMTP and IMAP server connections. Continue?')) {
        return;
    }

    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

    fetch('/admin/projects/mail/settings/test-connection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            alert('Connection test successful!\n\nSMTP: ' + (data.smtp ? 'OK' : 'Failed') + '\nIMAP: ' + (data.imap ? 'OK' : 'Failed'));
        } else {
            alert('Connection test failed: ' + data.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('An error occurred during the test.');
    });
}
</script>

<?php View::endSection(); ?>
