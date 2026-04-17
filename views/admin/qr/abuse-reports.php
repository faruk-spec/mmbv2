<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Stats -->
<div class="grid grid-4 mb-3">
    <?php
    $badges = [
        ['val' => $stats['total'] ?? 0,     'label' => 'Total Reports',    'color' => 'var(--cyan)'],
        ['val' => $stats['pending'] ?? 0,   'label' => 'Pending',          'color' => 'var(--orange)'],
        ['val' => $stats['resolved'] ?? 0,  'label' => 'Resolved',         'color' => 'var(--green)'],
        ['val' => $stats['dismissed'] ?? 0, 'label' => 'Dismissed',        'color' => 'var(--text-secondary)'],
    ];
    ?>
    <?php foreach ($badges as $b): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <div style="font-size:2rem;font-weight:700;color:<?= $b['color'] ?>;"><?= (int)$b['val'] ?></div>
            <div style="color:var(--text-secondary);font-size:13px;"><?= $b['label'] ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Flash messages -->
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

<!-- Reports table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-flag"></i> Abuse Reports</h3>
    </div>

    <?php if (empty($reports)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No abuse reports found.</p>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>QR Content</th>
                        <th>QR Status</th>
                        <th>Reporter</th>
                        <th>Reason</th>
                        <th>Report Status</th>
                        <th>Reported</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e($r['qr_content'] ?? '') ?>">
                                <?= View::e(substr($r['qr_content'] ?? '—', 0, 50)) ?>
                            </td>
                            <td>
                                <?php
                                $qrBadge = ['active'=>'badge-success','blocked'=>'badge-danger','inactive'=>'badge-warning'];
                                $qs = $r['qr_status'] ?? 'unknown';
                                ?>
                                <span class="badge <?= $qrBadge[$qs] ?? 'badge-info' ?>"><?= ucfirst($qs) ?></span>
                            </td>
                            <td style="font-size:13px;"><?= View::e($r['reporter_name'] ?? 'Anonymous') ?></td>
                            <td style="font-size:13px;color:var(--text-secondary);"><?= View::e($r['reason'] ?? '—') ?></td>
                            <td>
                                <?php
                                $rBadge = ['pending'=>'badge-warning','resolved'=>'badge-success','dismissed'=>''];
                                $rs = $r['status'] ?? 'pending';
                                ?>
                                <span class="badge <?= $rBadge[$rs] ?? 'badge-info' ?>"><?= ucfirst($rs) ?></span>
                            </td>
                            <td style="font-size:12px;"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <form method="POST" action="/admin/qr/abuse-reports/<?= $r['id'] ?>/resolve" style="display:inline;">
                                        <?= \Core\Security::csrfField() ?>
                                        <input type="hidden" name="action" value="resolve">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Block QR and mark resolved?')"
                                            title="Resolve (block QR)"><i class="fas fa-ban"></i> Resolve</button>
                                    </form>
                                    <form method="POST" action="/admin/qr/abuse-reports/<?= $r['id'] ?>/resolve" style="display:inline;">
                                        <?= \Core\Security::csrfField() ?>
                                        <input type="hidden" name="action" value="dismiss">
                                        <button type="submit" class="btn btn-sm btn-secondary"
                                            onclick="return confirm('Dismiss this report?')"
                                            title="Dismiss"><i class="fas fa-times"></i> Dismiss</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:var(--text-secondary);font-size:12px;"><?= ucfirst($r['status'] ?? '') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
