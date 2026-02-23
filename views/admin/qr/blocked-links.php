<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
    <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
        <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
        <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<!-- Add New Block -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-ban"></i> Block URL Pattern</h3>
    </div>
    <form method="POST" action="/admin/qr/blocked-links/add" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;padding-top:8px;">
        <?= \Core\Security::csrfField() ?>
        <div style="flex:2;min-width:220px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">URL Pattern (substring match)</label>
            <input type="text" name="url_pattern" class="form-control" placeholder="e.g. malware.com or phishing keyword" required>
        </div>
        <div style="flex:2;min-width:200px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Reason (optional)</label>
            <input type="text" name="reason" class="form-control" placeholder="Phishing, spam, malware…">
        </div>
        <div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> Block Pattern</button>
        </div>
    </form>
    <p style="font-size:12px;color:var(--text-secondary);margin-top:10px;">
        <i class="fas fa-info-circle"></i> Adding a block pattern will also automatically block all existing QR codes whose content or redirect URL contains this pattern.
    </p>
</div>

<!-- Blocked Links Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shield-alt"></i> Blocked URL Patterns (<?= count($blockedLinks) ?>)</h3>
    </div>

    <?php if (empty($blockedLinks)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No blocked URL patterns. The platform is clean!</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>URL Pattern</th>
                    <th>Reason</th>
                    <th>Blocked By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blockedLinks as $link): ?>
                    <tr>
                        <td>
                            <code style="background:var(--bg-secondary);padding:3px 8px;border-radius:4px;font-size:13px;">
                                <?= View::e($link['url_pattern']) ?>
                            </code>
                        </td>
                        <td style="color:var(--text-secondary);font-size:13px;"><?= View::e($link['reason'] ?? '—') ?></td>
                        <td style="font-size:13px;"><?= View::e($link['blocked_by_name'] ?? 'System') ?></td>
                        <td style="font-size:12px;"><?= date('M j, Y H:i', strtotime($link['created_at'])) ?></td>
                        <td>
                            <form method="POST" action="/admin/qr/blocked-links/<?= $link['id'] ?>/remove" style="display:inline;">
                                <?= \Core\Security::csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-secondary"
                                    onclick="return confirm('Remove this block pattern?')" title="Unblock">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
