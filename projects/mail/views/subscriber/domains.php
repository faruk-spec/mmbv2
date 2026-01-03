<!-- Domain Management View for Subscribers -->
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-globe mr-2"></i>
                        Manage Domains
                    </h2>
                    <p class="text-muted">Add and manage custom domains for your email service</p>
                </div>
                <div>
                    <a href="/projects/mail/subscriber/domains/add" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add New Domain
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Domains List -->
    <div class="row">
        <?php if (empty($domains)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-globe fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No Domains Added Yet</h4>
                    <p class="text-muted mb-4">
                        Start by adding your first domain to begin sending and receiving emails<br>
                        from your own domain name.
                    </p>
                    <a href="/projects/mail/subscriber/domains/add" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle"></i> Add Your First Domain
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($domains as $domain): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 <?= $domain['is_verified'] ? 'card-success' : 'card-warning' ?> card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-globe"></i>
                            <strong><?= View::e($domain['domain_name']) ?></strong>
                        </h3>
                        <div class="card-tools">
                            <?php if ($domain['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Verification Status -->
                        <div class="mb-3">
                            <h5 class="mb-2">Verification Status</h5>
                            <?php if ($domain['is_verified']): ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle"></i>
                                <strong>Verified</strong> - Domain is ready to use
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-clock"></i>
                                <strong>Pending</strong> - Awaiting DNS verification
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Domain Info -->
                        <div class="mb-3">
                            <p class="mb-1">
                                <small class="text-muted">Added:</small><br>
                                <strong><?= date('F d, Y', strtotime($domain['created_at'])) ?></strong>
                            </p>
                            <?php if ($domain['verified_at']): ?>
                            <p class="mb-1">
                                <small class="text-muted">Verified:</small><br>
                                <strong><?= date('F d, Y', strtotime($domain['verified_at'])) ?></strong>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- Statistics -->
                        <div class="row text-center mb-2">
                            <div class="col-6">
                                <div class="small-box bg-gradient-info">
                                    <div class="inner p-2">
                                        <h4><?= $domain['mailboxes_count'] ?? 0 ?></h4>
                                        <p class="mb-0"><small>Mailboxes</small></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small-box bg-gradient-success">
                                    <div class="inner p-2">
                                        <h4><?= $domain['aliases_count'] ?? 0 ?></h4>
                                        <p class="mb-0"><small>Aliases</small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group btn-block">
                            <a href="/projects/mail/subscriber/domains/<?= $domain['id'] ?>/dns" 
                               class="btn btn-info btn-sm">
                                <i class="fas fa-cog"></i> DNS Records
                            </a>
                            <a href="/projects/mail/subscriber/domains/<?= $domain['id'] ?>/edit" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($domain['mailboxes_count'] == 0): ?>
                            <button class="btn btn-danger btn-sm" 
                                    onclick="deleteDomain(<?= $domain['id'] ?>, '<?= addslashes($domain['domain_name']) ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- DNS Setup Guide -->
    <?php if (!empty($domains) && array_filter($domains, fn($d) => !$d['is_verified'])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle mr-2"></i>
                        DNS Setup Guide
                    </h3>
                </div>
                <div class="card-body">
                    <p>
                        <strong>To verify your domain and enable email functionality, you need to add DNS records to your domain registrar:</strong>
                    </p>
                    <ol>
                        <li>Click <strong>"DNS Records"</strong> button on any unverified domain above</li>
                        <li>Copy the provided DNS records (MX, SPF, DKIM, DMARC)</li>
                        <li>Add these records to your domain's DNS settings at your registrar</li>
                        <li>Wait 24-48 hours for DNS propagation</li>
                        <li>Click <strong>"Verify Now"</strong> to check verification status</li>
                    </ol>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> DNS propagation can take up to 48 hours. You can use 
                        <a href="https://mxtoolbox.com/" target="_blank">MXToolbox</a> to check if your records are live.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteDomain(id, domainName) {
    if (!confirm(`Are you sure you want to delete the domain "${domainName}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    fetch('/projects/mail/subscriber/domains/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `domain_id=${id}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert('Error: ' + d.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('An error occurred while deleting the domain');
    });
}
</script>

<style>
.card-outline.card-success {
    border-top: 3px solid #28a745;
}
.card-outline.card-warning {
    border-top: 3px solid #ffc107;
}
</style>
