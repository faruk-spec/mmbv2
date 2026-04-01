<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1><i class="fas fa-id-card" style="color:#6366f1;"></i> CardX — Overview</h1>
        <p style="color:var(--text-secondary);">ID card generation statistics and recent activity</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/projects/idcard/cards" class="btn btn-secondary"><i class="fas fa-list"></i> All Cards</a>
        <a href="/admin/projects/idcard/bulk-jobs" class="btn btn-secondary"><i class="fas fa-layer-group"></i> Bulk Jobs</a>
        <a href="/admin/projects/idcard/settings" class="btn btn-secondary"><i class="fas fa-cog"></i> Settings</a>
        <a href="/projects/idcard" class="btn btn-primary" target="_blank"><i class="fas fa-external-link-alt"></i> Open CardX</a>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-4" style="margin-bottom:24px;">
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:#6366f1;"><?= number_format((int)($stats['total'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Cards Generated</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= number_format((int)($stats['today'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Cards Today</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--green);"><?= number_format((int)($stats['this_month'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Cards This Month</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--magenta);"><?= number_format((int)($activeUsers ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Active Users (30d)</div>
    </div>
</div>

<!-- Bulk generation stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
    <div style="background:var(--bg-card);border:1px solid rgba(99,102,241,0.25);border-radius:12px;padding:18px;">
        <div style="font-size:1.6rem;font-weight:700;color:#6366f1;"><?= number_format((int)($bulkStats['total'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:12px;">Total Bulk Jobs</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid rgba(99,102,241,0.25);border-radius:12px;padding:18px;">
        <div style="font-size:1.6rem;font-weight:700;color:var(--cyan);"><?= number_format((int)($bulkStats['today'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:12px;">Bulk Jobs Today</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid rgba(99,102,241,0.25);border-radius:12px;padding:18px;display:flex;align-items:center;gap:12px;justify-content:space-between;">
        <div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--green);"><?= number_format((int)($bulkStats['cards_sum'] ?? 0)) ?></div>
            <div style="color:var(--text-secondary);font-size:12px;">Cards via Bulk</div>
        </div>
        <a href="/admin/projects/idcard/bulk-jobs" class="btn btn-secondary btn-sm" style="white-space:nowrap;">
            <i class="fas fa-layer-group"></i> View All
        </a>
    </div>
</div>

<!-- Recent Cards -->
<?php if (!empty($recent)): ?>
<div class="card" style="padding:20px;">
    <h3 style="font-size:1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-clock" style="color:#6366f1;"></i> Recent Cards
    </h3>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:8px 12px;color:var(--text-secondary);font-weight:600;">Card #</th>
                    <th style="text-align:left;padding:8px 12px;color:var(--text-secondary);font-weight:600;">Name</th>
                    <th style="text-align:left;padding:8px 12px;color:var(--text-secondary);font-weight:600;">Template</th>
                    <th style="text-align:left;padding:8px 12px;color:var(--text-secondary);font-weight:600;">User</th>
                    <th style="text-align:left;padding:8px 12px;color:var(--text-secondary);font-weight:600;">Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $card): ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:8px 12px;font-family:monospace;font-size:12px;color:var(--text-secondary);"><?= htmlspecialchars($card['card_number']) ?></td>
                    <td style="padding:8px 12px;font-weight:500;"><?= htmlspecialchars($card['card_data']['name'] ?? '—') ?></td>
                    <td style="padding:8px 12px;">
                        <span style="background:rgba(99,102,241,0.15);color:#6366f1;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">
                            <?= htmlspecialchars($card['template_key']) ?>
                        </span>
                    </td>
                    <td style="padding:8px 12px;font-size:12px;">
                        <?= htmlspecialchars($card['user_name'] ?? '—') ?><br>
                        <span style="color:var(--text-secondary);"><?= htmlspecialchars($card['user_email'] ?? '') ?></span>
                    </td>
                    <td style="padding:8px 12px;font-size:12px;color:var(--text-secondary);"><?= date('d M Y, H:i', strtotime($card['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="text-align:right;margin-top:12px;">
        <a href="/admin/projects/idcard/cards" class="btn btn-secondary btn-sm">View All Cards →</a>
    </div>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px;">
    <i class="fas fa-id-card" style="font-size:2.5rem;opacity:0.3;margin-bottom:12px;display:block;"></i>
    <p style="color:var(--text-secondary);">No cards generated yet.</p>
</div>
<?php endif; ?>

<?php View::endSection(); ?>
