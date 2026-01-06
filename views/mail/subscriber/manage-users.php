<!-- Subscriber User Management View -->
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-users mr-2"></i>
                        Manage Users
                    </h2>
                    <p class="text-muted">Add and manage users in your subscription</p>
                </div>
                <div>
                    <?php if ($canAddMore): ?>
                    <a href="/projects/mail/subscriber/users/add" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add New User
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary" disabled title="User limit reached">
                        <i class="fas fa-lock"></i> User Limit Reached
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Usage Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-<?= ($plan['users_count'] >= $plan['max_users']) ? 'danger' : 'info' ?>">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2">
                                <i class="fas fa-info-circle"></i> Plan Usage
                            </h5>
                            <p class="mb-0">
                                You are using <strong><?= $plan['users_count'] ?></strong> out of 
                                <strong><?= $plan['max_users'] ?></strong> available users in your plan.
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <?php if (!$canAddMore): ?>
                            <a href="/projects/mail/subscriber/subscription" class="btn btn-warning">
                                <i class="fas fa-arrow-up"></i> Upgrade Plan
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Progress Bar -->
                    <div class="progress mt-3" style="height: 25px;">
                        <?php
                        $percentage = ($plan['users_count'] / $plan['max_users']) * 100;
                        $progressClass = $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success');
                        ?>
                        <div class="progress-bar bg-<?= $progressClass ?>" role="progressbar" 
                             style="width: <?= min($percentage, 100) ?>%;" 
                             aria-valuenow="<?= $plan['users_count'] ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="<?= $plan['max_users'] ?>">
                            <?= round($percentage, 1) ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        All Users
                    </h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" id="searchUsers" class="form-control" placeholder="Search users...">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Users Yet</h4>
                        <p class="text-muted">Start by adding your first user to your subscription</p>
                        <?php if ($canAddMore): ?>
                        <a href="/projects/mail/subscriber/users/add" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Add First User
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Email Address</th>
                                    <th>Display Name</th>
                                    <th>Domain</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Storage</th>
                                    <th>Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-envelope text-primary"></i>
                                        <strong><?= View::e($user['email']) ?></strong>
                                    </td>
                                    <td><?= View::e($user['display_name'] ?? $user['username']) ?></td>
                                    <td>
                                        <i class="fas fa-globe text-info"></i>
                                        <?= View::e($user['domain_name']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $roleBadge = match($user['role_type']) {
                                            'subscriber_owner' => 'primary',
                                            'domain_admin' => 'warning',
                                            'end_user' => 'info',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $roleBadge ?>">
                                            <?= ucwords(str_replace('_', ' ', $user['role_type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                        <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle"></i> Inactive
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= formatBytes($user['storage_used'] ?? 0) ?> / 
                                            <?= formatBytes($user['storage_quota'] ?? 0) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?= date('M d, Y', strtotime($user['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-info" 
                                                    onclick="editUser(<?= $user['id'] ?>)" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <?php if ($user['role_type'] !== 'subscriber_owner'): ?>
                                            <button class="btn btn-secondary" 
                                                    onclick="changeRole(<?= $user['id'] ?>)" 
                                                    title="Change Role">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            
                                            <?php if ($user['is_active']): ?>
                                            <button class="btn btn-warning" 
                                                    onclick="suspendUser(<?= $user['id'] ?>)" 
                                                    title="Suspend">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-success" 
                                                    onclick="activateUser(<?= $user['id'] ?>)" 
                                                    title="Activate">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-danger" 
                                                    onclick="deleteUser(<?= $user['id'] ?>, '<?= addslashes($user['email']) ?>')" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-secondary" disabled title="Cannot modify owner">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title">
                    <i class="fas fa-user-tag"></i> Change User Role
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="changeRoleForm">
                    <input type="hidden" id="role_mailbox_id" name="mailbox_id">
                    <div class="form-group">
                        <label for="new_role_type">Select Role *</label>
                        <select class="form-control" id="new_role_type" name="role_type" required>
                            <option value="end_user">End User - Basic mailbox access</option>
                            <option value="domain_admin">Domain Admin - Manage domains and users</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Role Permissions:</strong><br>
                        <small>
                            <strong>End User:</strong> Can only send/receive emails and manage personal settings<br>
                            <strong>Domain Admin:</strong> Can manage mailboxes and settings for assigned domains
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmRoleChange()">
                    <i class="fas fa-check"></i> Change Role
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchUsers')?.addEventListener('keyup', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

function changeRole(mailboxId) {
    document.getElementById('role_mailbox_id').value = mailboxId;
    $('#changeRoleModal').modal('show');
}

function confirmRoleChange() {
    const mailboxId = document.getElementById('role_mailbox_id').value;
    const roleType = document.getElementById('new_role_type').value;
    
    fetch('/projects/mail/subscriber/users/assign-role', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `mailbox_id=${mailboxId}&role_type=${roleType}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            $('#changeRoleModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + d.message);
        }
    });
}

function suspendUser(id) {
    if (!confirm('Are you sure you want to suspend this user?')) return;
    
    fetch('/projects/mail/subscriber/users/' + id + '/suspend', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: ''
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('Error: ' + d.message);
    });
}

function activateUser(id) {
    if (!confirm('Are you sure you want to activate this user?')) return;
    
    fetch('/projects/mail/subscriber/users/' + id + '/activate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: ''
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('Error: ' + d.message);
    });
}

function deleteUser(id, email) {
    if (!confirm(`Are you sure you want to delete the user "${email}"?\n\nThis action cannot be undone and will delete all their emails and data.`)) return;
    
    fetch('/projects/mail/subscriber/users/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `mailbox_id=${id}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else alert('Error: ' + d.message);
    });
}

function editUser(id) {
    window.location.href = '/projects/mail/subscriber/users/' + id + '/edit';
}
</script>

<style>
.card-outline.card-danger {
    border-top: 3px solid #dc3545;
}
.card-outline.card-info {
    border-top: 3px solid #17a2b8;
}
</style>

<?php
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
