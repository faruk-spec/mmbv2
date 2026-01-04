<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Domain</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                        <li class="breadcrumb-item"><a href="/admin/projects/mail">Mail Server</a></li>
                        <li class="breadcrumb-item"><a href="/admin/projects/mail/domains">Domains</a></li>
                        <li class="breadcrumb-item active">Edit Domain</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Domain Details - <?= htmlspecialchars($domain['domain_name']) ?></h3>
                        </div>
                        <form method="POST" action="/admin/projects/mail/domains/<?= $domain['id'] ?>/edit">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Domain Name</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($domain['domain_name']) ?>" disabled>
                                    <small class="form-text text-muted">Domain name cannot be changed after creation</small>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($domain['description'] ?? '') ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="catch_all_email">Catch-All Email</label>
                                    <input type="email" class="form-control" id="catch_all_email" name="catch_all_email" 
                                           value="<?= htmlspecialchars($domain['catch_all_email'] ?? '') ?>"
                                           placeholder="optional@example.com">
                                    <small class="form-text text-muted">Emails sent to non-existent addresses will be forwarded here</small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" 
                                               name="is_active" value="1" <?= $domain['is_active'] ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="is_active">
                                            Domain is Active
                                        </label>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> Domain Status</h5>
                                    <ul class="mb-0">
                                        <li><strong>Verified:</strong> <?= $domain['is_verified'] ? 'Yes' : 'No' ?></li>
                                        <li><strong>Subscriber:</strong> <?= htmlspecialchars($subscriber['account_name'] ?? 'N/A') ?></li>
                                        <li><strong>Created:</strong> <?= date('F d, Y', strtotime($domain['created_at'])) ?></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Domain
                                </button>
                                <a href="/admin/projects/mail/domains" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group-vertical w-100">
                                <a href="/admin/projects/mail/subscribers/<?= $domain['subscriber_id'] ?>" class="btn btn-info mb-2">
                                    <i class="fas fa-user"></i> View Subscriber
                                </a>
                                <button type="button" class="btn btn-warning mb-2" onclick="verifyDNS()">
                                    <i class="fas fa-sync"></i> Verify DNS Records
                                </button>
                                <?php if ($domain['is_active']): ?>
                                <button type="button" class="btn btn-danger mb-2" onclick="suspendDomain()">
                                    <i class="fas fa-ban"></i> Suspend Domain
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-success mb-2" onclick="activateDomain()">
                                    <i class="fas fa-check"></i> Activate Domain
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">DNS Records</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-sm">DNS records must be configured for this domain to work properly.</p>
                            <a href="/admin/projects/mail/domains/<?= $domain['id'] ?>/dns" class="btn btn-primary btn-sm">
                                <i class="fas fa-dns"></i> View DNS Records
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function verifyDNS() {
    if (confirm('Start DNS verification for this domain?')) {
        fetch('/admin/projects/mail/domains/<?= $domain['id'] ?>/verify-dns', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('DNS verification started');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Verification failed'));
            }
        });
    }
}

function activateDomain() {
    if (confirm('Activate this domain?')) {
        fetch('/admin/projects/mail/domains/<?= $domain['id'] ?>/activate', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Domain activated');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Activation failed'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

function suspendDomain() {
    if (confirm('Suspend this domain? All mailboxes will be disabled.')) {
        fetch('/admin/projects/mail/domains/<?= $domain['id'] ?>/suspend', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Domain suspended');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Suspension failed'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}
</script>

<?php View::endSection(); ?>
