<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Overview Cards -->
<div class="grid grid-4 mb-3">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= number_format($storageStats['total_qr'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total QR Codes</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--green);"><?= number_format($storageStats['dynamic_qr'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Dynamic QR Codes</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--orange);"><?= number_format($storageStats['with_logo'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">QR with Logo</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <?php
        $units = ['B','KB','MB','GB'];
        $size = $diskUsed;
        $ui = 0;
        while ($size >= 1024 && $ui < 3) { $size /= 1024; $ui++; }
        ?>
        <div style="font-size:2rem;font-weight:700;color:var(--magenta);"><?= round($size, 1) ?> <?= $units[$ui] ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Logo Storage Used (<?= $fileCount ?> files)</div>
    </div>
</div>

<!-- Per-user breakdown -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Usage by User</h3>
    </div>

    <?php if (empty($userStorage)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No QR codes created yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Total QR</th>
                    <th>QR with Logo</th>
                    <th>Last Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userStorage as $u): ?>
                    <tr>
                        <td>
                            <div style="font-weight:500;font-size:13px;"><?= View::e($u['name']) ?></div>
                            <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($u['email']) ?></div>
                        </td>
                        <td><?= number_format($u['qr_count']) ?></td>
                        <td><?= number_format($u['qr_with_logo']) ?></td>
                        <td style="font-size:12px;"><?= $u['last_qr_at'] ? date('M j, Y', strtotime($u['last_qr_at'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
