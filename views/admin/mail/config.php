<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;">Mail Configuration</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Manage SMTP/IMAP providers for sending and syncing email</p>
    </div>
    <a href="/admin/mail/config/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Provider
    </a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<!-- Stats row -->
<div class="grid grid-4" style="margin-bottom:24px;">
    <div class="stat-card">
        <i class="fas fa-hourglass-half" style="color:var(--orange);"></i>
        <div>
            <p class="stat-label">Queue Pending</p>
            <p class="stat-value"><?= (int)($stats['pending'] ?? 0) ?></p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-check-circle" style="color:var(--green);"></i>
        <div>
            <p class="stat-label">Queue Sent</p>
            <p class="stat-value"><?= (int)($stats['sent'] ?? 0) ?></p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-times-circle" style="color:var(--red);"></i>
        <div>
            <p class="stat-label">Queue Failed</p>
            <p class="stat-value"><?= (int)($stats['failed'] ?? 0) ?></p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-paper-plane" style="color:var(--cyan);"></i>
        <div>
            <p class="stat-label">Total Sent (log)</p>
            <p class="stat-value"><?= (int)($logCount ?? 0) ?></p>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <button id="btnProcessQueue" class="btn btn-secondary" onclick="processQueue()">
            <i class="fas fa-play"></i> Process Queue Now
        </button>
        <a href="/admin/mail/templates" class="btn btn-secondary">
            <i class="fas fa-file-alt"></i> Notification Templates
        </a>
        <a href="/admin/mail/logs" class="btn btn-secondary">
            <i class="fas fa-list"></i> Send Log
        </a>
        <a href="/admin/email/queue" class="btn btn-secondary">
            <i class="fas fa-inbox"></i> Email Queue
        </a>
    </div>
</div>

<!-- Providers table -->
<div class="card">
    <h3 style="margin:0 0 16px;font-size:16px;">Mail Providers</h3>
    <?php if (empty($providers)): ?>
        <div style="text-align:center;padding:40px;color:var(--text-secondary);">
            <i class="fas fa-envelope-open" style="font-size:40px;margin-bottom:12px;opacity:.4;"></i>
            <p>No mail providers configured. <a href="/admin/mail/config/create">Add one now</a>.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>SMTP Host</th>
                    <th>From Email</th>
                    <th>IMAP</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($providers as $p): ?>
                <tr>
                    <td><strong><?= View::e($p['name']) ?></strong></td>
                    <td><span class="badge badge-info"><?= View::e(strtoupper($p['provider_type'])) ?></span></td>
                    <td><code><?= View::e($p['smtp_host'] ?: '—') ?></code></td>
                    <td><?= View::e($p['from_email'] ?: '—') ?></td>
                    <td>
                        <?php if ($p['is_imap_enabled']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Enabled</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['is_active']): ?>
                            <span class="badge badge-success"><i class="fas fa-circle"></i> Active</span>
                        <?php else: ?>
                            <span class="badge" style="color:var(--text-secondary);">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;">
                            <a href="/admin/mail/config/edit?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if (!$p['is_active']): ?>
                            <button class="btn btn-sm btn-primary" onclick="activateProvider(<?= $p['id'] ?>)">
                                <i class="fas fa-power-off"></i> Activate
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" onclick="deleteProvider(<?= $p['id'] ?>, '<?= View::e($p['name']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function activateProvider(id) {
    if (!confirm('Activate this provider? It will become the default sender.')) return;
    fetch('/admin/mail/config/activate', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken},
        body: '_csrf_token=' + encodeURIComponent(csrfToken) + '&id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); } else { alert(d.message || 'Error'); }
    });
}

function deleteProvider(id, name) {
    if (!confirm('Delete provider "' + name + '"? This cannot be undone.')) return;
    fetch('/admin/mail/config/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken},
        body: '_csrf_token=' + encodeURIComponent(csrfToken) + '&id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); } else { alert(d.message || 'Error'); }
    });
}

function processQueue() {
    const btn = document.getElementById('btnProcessQueue');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';
    fetch('/admin/mail/config/process-queue', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken},
        body: '_csrf_token=' + encodeURIComponent(csrfToken) + '&limit=50'
    }).then(r => r.json()).then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play"></i> Process Queue Now';
        alert(d.message || 'Done');
        location.reload();
    }).catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-play"></i> Process Queue Now';
    });
}
</script>

<?php View::endSection(); ?>
