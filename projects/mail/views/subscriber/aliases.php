<?php
$title = 'Email Aliases';
require_once __DIR__ . '/layout.php';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Email Aliases</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/projects/mail/subscriber/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Aliases</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Plan Usage Card -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2">Alias Usage</h5>
                                <div class="progress" style="height: 25px;">
                                    <?php 
                                    $percentage = ($plan['aliases_count'] / $plan['max_aliases']) * 100;
                                    $progressClass = $percentage >= 100 ? 'bg-danger' : ($percentage >= 80 ? 'bg-warning' : 'bg-info');
                                    ?>
                                    <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                         style="width: <?= min($percentage, 100) ?>%">
                                        <?= $plan['aliases_count'] ?> / <?= $plan['max_aliases'] ?> aliases
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if ($plan['aliases_count'] < $plan['max_aliases']): ?>
                                    <a href="/projects/mail/subscriber/aliases/add" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Alias
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-warning" onclick="alert('Alias limit reached. Please upgrade your plan.')">
                                        <i class="fas fa-arrow-up"></i> Upgrade Plan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aliases List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Email Aliases</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" id="searchAliases" class="form-control" placeholder="Search aliases...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <?php if (empty($aliases)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-at fa-4x text-muted mb-3"></i>
                                <h4>No Aliases Yet</h4>
                                <p class="text-muted">Create email aliases to forward emails to your mailboxes or external addresses.</p>
                                <?php if ($plan['aliases_count'] < $plan['max_aliases']): ?>
                                    <a href="/projects/mail/subscriber/aliases/add" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus"></i> Create Your First Alias
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Alias Email</th>
                                        <th>Domain</th>
                                        <th>Destination</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="aliasesTable">
                                    <?php foreach ($aliases as $alias): ?>
                                        <tr data-alias-id="<?= $alias['id'] ?>">
                                            <td>
                                                <strong><?= View::e($alias['alias_email']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?= View::e($alias['domain_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($alias['destination_type'] === 'mailbox'): ?>
                                                    <i class="fas fa-envelope text-primary"></i>
                                                    <?= View::e($alias['destination_email']) ?>
                                                <?php else: ?>
                                                    <i class="fas fa-external-link-alt text-info"></i>
                                                    <?= View::e($alias['destination_email']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $alias['destination_type'] === 'mailbox' ? 'primary' : 'info' ?>">
                                                    <?= ucfirst($alias['destination_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($alias['is_active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M d, Y', strtotime($alias['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-default" onclick="toggleAliasStatus(<?= $alias['id'] ?>)" 
                                                            title="<?= $alias['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                    <button class="btn btn-danger" onclick="deleteAlias(<?= $alias['id'] ?>)" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-info collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">About Email Aliases</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5>What are Email Aliases?</h5>
                        <p>Email aliases are alternative email addresses that forward messages to your primary mailbox. They're perfect for:</p>
                        <ul>
                            <li><strong>Organization:</strong> Create department-specific addresses (sales@, support@, info@)</li>
                            <li><strong>Privacy:</strong> Use different addresses for different purposes</li>
                            <li><strong>Spam Management:</strong> Create disposable addresses for signups</li>
                            <li><strong>Professional Image:</strong> Have multiple professional addresses</li>
                        </ul>
                        
                        <h5 class="mt-3">Types of Aliases</h5>
                        <ul>
                            <li><strong>Internal:</strong> Forward to a mailbox in your account</li>
                            <li><strong>External:</strong> Forward to any external email address</li>
                        </ul>

                        <div class="alert alert-info mt-3">
                            <i class="icon fas fa-info-circle"></i>
                            <strong>Pro Tip:</strong> Aliases don't count towards your mailbox limit and don't take up storage space!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Search functionality
document.getElementById('searchAliases').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#aliasesTable tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Toggle alias status
function toggleAliasStatus(aliasId) {
    if (!confirm('Are you sure you want to change the status of this alias?')) {
        return;
    }

    fetch(`/projects/mail/subscriber/aliases/${aliasId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to toggle alias status');
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}

// Delete alias
function deleteAlias(aliasId) {
    if (!confirm('Are you sure you want to delete this alias? This action cannot be undone.')) {
        return;
    }

    fetch(`/projects/mail/subscriber/aliases/${aliasId}/delete`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete alias');
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}
</script>

<?php require_once __DIR__ . '/layout.php'; ?>
