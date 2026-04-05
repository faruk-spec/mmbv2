<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1><i class="fas fa-layer-group" style="color:#6366f1;"></i> CardX — Bulk Jobs</h1>
        <p style="color:var(--text-secondary);">History of all bulk ID card generation jobs</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/admin/projects/idcard/settings" class="btn btn-secondary"><i class="fas fa-cog"></i> Settings</a>
        <a href="/admin/projects/idcard" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Overview</a>
    </div>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:#6366f1;"><?= number_format((int)($stats['total'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Bulk Jobs</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= number_format((int)($stats['today'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Jobs Today</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--green);"><?= number_format((int)($stats['cards_sum'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Cards Generated via Bulk</div>
    </div>
</div>

<!-- Jobs table -->
<div class="card" style="padding:20px;">
    <?php if (empty($jobs)): ?>
    <div style="text-align:center;padding:48px;color:var(--text-secondary);">
        <i class="fas fa-layer-group" style="font-size:2.5rem;opacity:0.3;display:block;margin-bottom:12px;"></i>
        No bulk jobs have been submitted yet.
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-weight:600;">Job #</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-weight:600;">User</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-weight:600;">Template</th>
                    <th style="text-align:center;padding:10px 12px;color:var(--text-secondary);font-weight:600;">Total</th>
                    <th style="text-align:center;padding:10px 12px;color:var(--text-secondary);font-weight:600;">Completed</th>
                    <th style="text-align:center;padding:10px 12px;color:var(--text-secondary);font-weight:600;">Failed</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-weight:600;">Status</th>
                    <th style="text-align:left;padding:10px 12px;color:var(--text-secondary);font-weight:600;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:10px 12px;font-family:monospace;font-size:12px;color:var(--text-secondary);">#<?= (int)$job['id'] ?></td>
                    <td style="padding:10px 12px;">
                        <div style="font-weight:500;"><?= htmlspecialchars($job['user_name'] ?? '—') ?></div>
                        <div style="font-size:11px;color:var(--text-secondary);"><?= htmlspecialchars($job['user_email'] ?? '') ?></div>
                    </td>
                    <td style="padding:10px 12px;">
                        <span style="background:rgba(99,102,241,0.12);color:#6366f1;
                                     padding:2px 10px;border-radius:10px;font-size:11px;font-weight:600;">
                            <?= htmlspecialchars($job['template_key']) ?>
                        </span>
                    </td>
                    <td style="padding:10px 12px;text-align:center;font-weight:600;"><?= (int)$job['total_rows'] ?></td>
                    <td style="padding:10px 12px;text-align:center;font-weight:700;color:#00ff88;"><?= (int)$job['completed'] ?></td>
                    <td style="padding:10px 12px;text-align:center;font-weight:<?= $job['failed'] > 0 ? '700' : '400' ?>;
                               color:<?= $job['failed'] > 0 ? '#ef4444' : 'var(--text-secondary)' ?>;">
                        <?= (int)$job['failed'] ?>
                    </td>
                    <td style="padding:10px 12px;">
                        <?php
                        $statusColors = [
                            'done'       => ['bg'=>'rgba(0,255,136,0.12)', 'color'=>'#00ff88'],
                            'error'      => ['bg'=>'rgba(239,68,68,0.12)', 'color'=>'#ef4444'],
                            'processing' => ['bg'=>'rgba(245,158,11,0.12)', 'color'=>'#f59e0b'],
                            'pending'    => ['bg'=>'rgba(99,102,241,0.12)', 'color'=>'#6366f1'],
                        ];
                        $sc = $statusColors[$job['status']] ?? $statusColors['pending'];
                        ?>
                        <span style="display:inline-block;padding:2px 10px;border-radius:10px;
                                     background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;
                                     font-size:11px;font-weight:700;letter-spacing:0.02em;">
                            <?= htmlspecialchars(ucfirst($job['status'])) ?>
                        </span>
                    </td>
                    <td style="padding:10px 12px;font-size:12px;color:var(--text-secondary);">
                        <?= date('d M Y, H:i', strtotime($job['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a href="/admin/projects/idcard/bulk-jobs?page=<?= $p ?>"
           style="display:inline-flex;align-items:center;justify-content:center;
                  width:34px;height:34px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;
                  <?= $p === $page
                      ? 'background:var(--indigo);color:#fff;'
                      : 'background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-primary);' ?>">
            <?= $p ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
