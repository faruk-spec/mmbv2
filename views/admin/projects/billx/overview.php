<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-file-invoice" style="color:#f59e0b;"></i> BillX — Overview</h1>
        <p style="color:var(--text-secondary);">Bill generation statistics and recent activity</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/projects/billx/bills" class="btn btn-secondary"><i class="fas fa-list"></i> All Bills</a>
        <a href="/admin/projects/billx/settings" class="btn btn-secondary"><i class="fas fa-cog"></i> Settings</a>
        <a href="/projects/billx" class="btn btn-primary" target="_blank"><i class="fas fa-external-link-alt"></i> Open BillX</a>
    </div>
</div>

<!-- Stats Row 1: Counts -->
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:#f59e0b;"><?= number_format((int)($stats['total'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Bills Generated</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= number_format((int)($stats['today'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Bills Today</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--green);"><?= number_format((int)($stats['this_month'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Bills This Month</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--magenta);"><?= number_format((int)($activeUsers ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Active Users</div>
    </div>
</div>

<!-- Stats Row 2: Revenue -->
<?php if (!empty($revenue)): ?>
<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">
            ₹<?= number_format((float)($revenue['total_revenue'] ?? 0), 0) ?>
        </div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Revenue (All Bills)</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:1.6rem;font-weight:700;color:var(--cyan);">
            ₹<?= number_format((float)($revenue['today_revenue'] ?? 0), 0) ?>
        </div>
        <div style="color:var(--text-secondary);font-size:13px;">Revenue Today</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:1.6rem;font-weight:700;color:var(--green);">
            ₹<?= number_format((float)($revenue['month_revenue'] ?? 0), 0) ?>
        </div>
        <div style="color:var(--text-secondary);font-size:13px;">Revenue This Month</div>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-2" style="gap:20px;">

    <!-- Bills by Type -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Bills by Type</h3>
        </div>
        <?php if (empty($byType)): ?>
            <p style="color:var(--text-secondary);padding:20px;text-align:center;">No bills generated yet.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bill Type</th>
                        <th style="text-align:right;">Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($byType as $row): ?>
                    <tr>
                        <td><?= View::e(ucwords(str_replace('_', ' ', $row['bill_type']))) ?></td>
                        <td style="text-align:right;font-weight:600;color:#f59e0b;"><?= number_format((int)$row['cnt']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Bills -->
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3 class="card-title"><i class="fas fa-history"></i> Recent Bills</h3>
            <a href="/admin/projects/billx/bills" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (empty($recentBills)): ?>
            <p style="color:var(--text-secondary);padding:20px;text-align:center;">No bills yet.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBills as $bill): ?>
                    <tr>
                        <td>
                            <div style="font-size:13px;font-weight:500;"><?= View::e($bill['user_name'] ?? '—') ?></div>
                            <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($bill['user_email'] ?? '') ?></div>
                        </td>
                        <td><span class="badge badge-info"><?= View::e($bill['bill_type']) ?></span></td>
                        <td style="font-weight:600;"><?= View::e($bill['currency'] ?? 'INR') ?> <?= number_format((float)$bill['total_amount'], 2) ?></td>
                        <td style="font-size:12px;"><?= date('M j, Y', strtotime($bill['created_at'])) ?></td>
                        <td>
                            <a href="/admin/projects/billx/bills/view/<?= (int)$bill['id'] ?>"
                               class="btn btn-sm btn-secondary" title="View"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php View::endSection(); ?>
