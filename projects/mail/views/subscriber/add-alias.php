<?php
$title = 'Add Email Alias';
require_once __DIR__ . '/layout.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add Email Alias</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/projects/mail/subscriber/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/projects/mail/subscriber/aliases">Aliases</a></li>
                    <li class="breadcrumb-item active">Add Alias</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Form Column -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Alias Information</h3>
                    </div>
                    <form action="/projects/mail/subscriber/aliases/store" method="POST">
                        <div class="card-body">
                            <!-- Alias Email -->
                            <div class="form-group">
                                <label for="aliasEmail">Alias Email Address *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="aliasName" name="alias_name" 
                                           placeholder="Enter alias (e.g., sales, support, info)" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">@</span>
                                    </div>
                                    <select class="form-control" name="domain_id" id="domainSelect" required>
                                        <option value="">Select Domain</option>
                                        <?php foreach ($domains as $domain): ?>
                                            <option value="<?= $domain['id'] ?>">
                                                <?= View::e($domain['domain_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <small class="form-text text-muted">
                                    Full email: <strong id="fullEmail">-</strong>
                                </small>
                            </div>

                            <!-- Destination Type -->
                            <div class="form-group">
                                <label>Destination Type *</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="destination_type" 
                                           id="destMailbox" value="mailbox" checked>
                                    <label class="form-check-label" for="destMailbox">
                                        Internal Mailbox (forward to one of your mailboxes)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="destination_type" 
                                           id="destExternal" value="external">
                                    <label class="form-check-label" for="destExternal">
                                        External Email (forward to any email address)
                                    </label>
                                </div>
                            </div>

                            <!-- Internal Mailbox Selection -->
                            <div class="form-group" id="mailboxGroup">
                                <label for="mailboxSelect">Forward to Mailbox *</label>
                                <select class="form-control" name="destination_mailbox_id" id="mailboxSelect">
                                    <option value="">Select Mailbox</option>
                                    <?php foreach ($mailboxes as $mailbox): ?>
                                        <option value="<?= $mailbox['id'] ?>">
                                            <?= View::e($mailbox['email']) ?>
                                            (<?= View::e($mailbox['full_name']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- External Email -->
                            <div class="form-group" id="externalGroup" style="display: none;">
                                <label for="externalEmail">Forward to Email Address *</label>
                                <input type="email" class="form-control" name="destination_email" 
                                       id="externalEmail" placeholder="user@example.com">
                                <small class="form-text text-muted">
                                    Enter any valid email address where you want to receive forwarded emails
                                </small>
                            </div>

                            <!-- Active Status -->
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="isActive" 
                                           name="is_active" checked>
                                    <label class="custom-control-label" for="isActive">
                                        Activate alias immediately
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Alias
                            </button>
                            <a href="/projects/mail/subscriber/aliases" class="btn btn-default">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Sidebar -->
            <div class="col-md-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Help & Tips</h3>
                    </div>
                    <div class="card-body">
                        <h5><i class="fas fa-lightbulb text-warning"></i> What is an Email Alias?</h5>
                        <p class="small">
                            An email alias is an alternative email address that forwards all incoming 
                            messages to another mailbox. It's perfect for creating department addresses 
                            or managing multiple email identities.
                        </p>

                        <h5 class="mt-3"><i class="fas fa-shield-alt text-success"></i> Common Uses</h5>
                        <ul class="small">
                            <li><strong>sales@yourdomain.com</strong> - Sales inquiries</li>
                            <li><strong>support@yourdomain.com</strong> - Customer support</li>
                            <li><strong>info@yourdomain.com</strong> - General information</li>
                            <li><strong>noreply@yourdomain.com</strong> - Automated emails</li>
                        </ul>

                        <h5 class="mt-3"><i class="fas fa-star text-info"></i> Pro Tips</h5>
                        <ul class="small">
                            <li>Use descriptive names that are easy to remember</li>
                            <li>Aliases don't use storage space</li>
                            <li>You can create multiple aliases for the same mailbox</li>
                            <li>Use external forwarding to integrate with other services</li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <small>
                                <i class="icon fas fa-exclamation-triangle"></i>
                                <strong>Note:</strong> Make sure the destination mailbox or email address 
                                is active and can receive emails.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Domain Status Card -->
                <?php if (empty($domains)): ?>
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">No Verified Domains</h3>
                        </div>
                        <div class="card-body">
                            <p class="small">
                                You need to add and verify at least one domain before creating email aliases.
                            </p>
                            <a href="/projects/mail/subscriber/domains/add" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Add Domain
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
// Update full email preview
function updateFullEmail() {
    const aliasName = document.getElementById('aliasName').value;
    const domainSelect = document.getElementById('domainSelect');
    const domainText = domainSelect.options[domainSelect.selectedIndex]?.text;
    
    if (aliasName && domainText && domainText !== 'Select Domain') {
        document.getElementById('fullEmail').textContent = aliasName + '@' + domainText;
    } else {
        document.getElementById('fullEmail').textContent = '-';
    }
}

document.getElementById('aliasName').addEventListener('input', updateFullEmail);
document.getElementById('domainSelect').addEventListener('change', updateFullEmail);

// Toggle destination groups
document.querySelectorAll('input[name="destination_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'mailbox') {
            document.getElementById('mailboxGroup').style.display = 'block';
            document.getElementById('externalGroup').style.display = 'none';
            document.getElementById('mailboxSelect').required = true;
            document.getElementById('externalEmail').required = false;
        } else {
            document.getElementById('mailboxGroup').style.display = 'none';
            document.getElementById('externalGroup').style.display = 'block';
            document.getElementById('mailboxSelect').required = false;
            document.getElementById('externalEmail').required = true;
        }
    });
});

// Validate alias name
document.getElementById('aliasName').addEventListener('input', function() {
    // Remove invalid characters
    this.value = this.value.toLowerCase().replace(/[^a-z0-9._-]/g, '');
});
</script>

<?php require_once __DIR__ . '/layout.php'; ?>
