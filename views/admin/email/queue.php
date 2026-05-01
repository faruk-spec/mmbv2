<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;"><?= View::e($title) ?></h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Monitor and manage outgoing email queue</p>
    </div>
    <a href="/admin/mail/config" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Mail Config</a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success" style="margin-bottom:16px;"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error" style="margin-bottom:16px;"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-4" style="margin-bottom:24px;">
    <a href="?status=all" class="stat-card" style="text-decoration:none;cursor:pointer;">
        <i class="fas fa-inbox" style="color:var(--cyan);"></i>
        <div><p class="stat-label">Total</p><p class="stat-value"><?= (int)$total ?></p></div>
    </a>
    <a href="?status=pending" class="stat-card" style="text-decoration:none;cursor:pointer;">
        <i class="fas fa-hourglass-half" style="color:var(--orange);"></i>
        <div><p class="stat-label">Pending</p><p class="stat-value"><?= (int)($stats['pending'] ?? 0) ?></p></div>
    </a>
    <a href="?status=sent" class="stat-card" style="text-decoration:none;cursor:pointer;">
        <i class="fas fa-check-circle" style="color:var(--green);"></i>
        <div><p class="stat-label">Sent</p><p class="stat-value"><?= (int)($stats['sent'] ?? 0) ?></p></div>
    </a>
    <a href="?status=failed" class="stat-card" style="text-decoration:none;cursor:pointer;">
        <i class="fas fa-times-circle" style="color:var(--red);"></i>
        <div><p class="stat-label">Failed</p><p class="stat-value"><?= (int)($stats['failed'] ?? 0) ?></p></div>
    </a>
</div>

<!-- Actions + Filter -->
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <button id="btnProcessQueue" class="btn btn-primary" onclick="processQueue()">
            <i class="fas fa-play"></i> Process Queue Now
        </button>
        <button id="btnDeleteFailed" class="btn btn-danger" onclick="deleteFailed()">
            <i class="fas fa-trash"></i> Delete Failed
        </button>
        <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
            <?php $statusFilters = ['all'=>'All','pending'=>'Pending','sent'=>'Sent','processing'=>'Processing','failed'=>'Failed']; ?>
            <?php foreach ($statusFilters as $val => $label): ?>
            <a href="?status=<?= $val ?>" class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-secondary' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Queue Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Created</th>
                    <th>Scheduled</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emails as $email): ?>
                <tr>
                    <td><?= View::e($email['recipient'] ?? $email['to_email'] ?? '—') ?></td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($email['subject'] ?? '—') ?></td>
                    <td>
                        <?php
                        $s = $email['status'] ?? '';
                        $badgeClass = match($s) {
                            'sent'       => 'badge-success',
                            'failed'     => 'badge-danger',
                            'processing' => 'badge-info',
                            default      => 'badge-warning',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= View::e($s) ?></span>
                    </td>
                    <td><?= (int)($email['attempts'] ?? 0) ?></td>
                    <td style="white-space:nowrap;"><?= View::e($email['created_at'] ?? '—') ?></td>
                    <td style="white-space:nowrap;"><?= View::e($email['scheduled_at'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($emails)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-secondary);padding:32px;">No queue entries<?= $status !== 'all' ? ' with status "' . View::e($status) . '"' : '' ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;padding:16px 0 0;flex-wrap:wrap;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?status=<?= View::e($status) ?>&page=<?= $i ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function processQueue() {
    const btn = document.getElementById('btnProcessQueue');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';
    fetch('/admin/email/queue/process', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken},
        body: '_csrf_token=' + encodeURIComponent(csrfToken) + '&limit=100'
    }).then(r => r.json()).then(d => {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-play"></i> Process Queue Now';
        alert(d.message || (d.success ? 'Done' : 'Error'));
        if (d.success) location.reload();
    }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-play"></i> Process Queue Now'; });
}

function deleteFailed() {
    if (!confirm('Delete all failed emails from the queue?')) return;
    const btn = document.getElementById('btnDeleteFailed');
    btn.disabled = true;
    fetch('/admin/email/queue/delete-failed', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken},
        body: '_csrf_token=' + encodeURIComponent(csrfToken)
    }).then(r => r.json()).then(d => {
        btn.disabled = false;
        alert(d.message || (d.success ? 'Done' : 'Error'));
        if (d.success) location.reload();
    }).catch(() => { btn.disabled = false; });
}
</script>

<?php View::endSection(); ?>
