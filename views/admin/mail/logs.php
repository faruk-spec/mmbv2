<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;">Mail Send Log</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Audit trail of all outgoing emails</p>
    </div>
    <a href="/admin/mail/config" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Template</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Sent At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= View::e($log['recipient']) ?></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($log['subject'] ?? '—') ?></td>
                    <td><?= $log['template_slug'] ? '<code>' . View::e($log['template_slug']) . '</code>' : '—' ?></td>
                    <td><?= View::e($log['user_name'] ?? '—') ?></td>
                    <td>
                        <?php if ($log['status'] === 'sent'): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Sent</span>
                        <?php else: ?>
                            <span class="badge badge-danger" title="<?= View::e($log['error_message'] ?? '') ?>">
                                <i class="fas fa-times"></i> Failed
                            </span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;"><?= View::e($log['sent_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-secondary);padding:32px;">No log entries yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;padding:16px 0 0;flex-wrap:wrap;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
