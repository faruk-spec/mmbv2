<?php /** @var array $recentBills @var int $totalBills @var array $config @var array $user */ ?>

<div class="page-header" style="margin-bottom:30px;text-align:center;">
    <h1 style="font-size:2.2rem;font-weight:700;background:linear-gradient(135deg,#f59e0b,#00f0ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        BillX Dashboard
    </h1>
    <p style="color:var(--text-secondary);margin-top:8px;font-size:1.05rem;">
        Generate professional bills &amp; receipts on the go
    </p>
</div>

<!-- Quick Actions -->
<div style="display:flex;gap:15px;margin-bottom:30px;flex-wrap:wrap;justify-content:center;">
    <a href="/projects/billx/generate" style="flex:1;min-width:200px;max-width:250px;background:linear-gradient(135deg,#f59e0b,#d97706);color:white;padding:20px;border-radius:12px;text-decoration:none;text-align:center;box-shadow:0 4px 15px rgba(245,158,11,0.3);transition:transform 0.2s;">
        <i class="fas fa-file-invoice" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
        <strong style="font-size:1.1rem;">Generate Bill</strong>
        <p style="margin:5px 0 0;font-size:0.85rem;opacity:0.9;">Create a new bill</p>
    </a>
    <a href="/projects/billx/history" style="flex:1;min-width:200px;max-width:250px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;padding:20px;border-radius:12px;text-decoration:none;text-align:center;box-shadow:0 4px 15px rgba(99,102,241,0.3);transition:transform 0.2s;">
        <i class="fas fa-history" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
        <strong style="font-size:1.1rem;">Bill History</strong>
        <p style="margin:5px 0 0;font-size:0.85rem;opacity:0.9;"><?= number_format($totalBills) ?> bill<?= $totalBills !== 1 ? 's' : '' ?></p>
    </a>
</div>

<!-- Stats -->
<div class="grid grid-3" style="margin-bottom:30px;">
    <div class="card stat-card">
        <div class="stat-value"><?= number_format($totalBills) ?></div>
        <div class="stat-label"><i class="fas fa-file-alt"></i> Total Bills</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value"><?= count($config['bill_types']) ?></div>
        <div class="stat-label"><i class="fas fa-th-list"></i> Bill Types</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value" style="font-size:1.5rem;">Free</div>
        <div class="stat-label"><i class="fas fa-check-circle"></i> Always Free</div>
    </div>
</div>

<!-- Bill Types Preview -->
<div class="card" style="margin-bottom:30px;">
    <h3 style="font-size:1.1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-th" style="color:var(--amber);"></i> Supported Bill Types
    </h3>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php foreach ($config['bill_types'] as $key => $label): ?>
        <a href="/projects/billx/generate?type=<?= htmlspecialchars($key) ?>"
           style="padding:6px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:20px;font-size:0.78rem;color:var(--text-secondary);text-decoration:none;transition:all 0.2s;"
           onmouseover="this.style.borderColor='#f59e0b';this.style.color='#f59e0b';"
           onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-secondary)';">
            <?= htmlspecialchars($label) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Bills -->
<?php if (!empty($recentBills)): ?>
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h3 style="font-size:1.1rem;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-clock" style="color:var(--amber);"></i> Recent Bills
        </h3>
        <a href="/projects/billx/history" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:8px 12px;font-size:0.8rem;color:var(--text-secondary);font-weight:600;">Bill #</th>
                    <th style="text-align:left;padding:8px 12px;font-size:0.8rem;color:var(--text-secondary);font-weight:600;">Type</th>
                    <th style="text-align:left;padding:8px 12px;font-size:0.8rem;color:var(--text-secondary);font-weight:600;">To</th>
                    <th style="text-align:right;padding:8px 12px;font-size:0.8rem;color:var(--text-secondary);font-weight:600;">Amount</th>
                    <th style="text-align:left;padding:8px 12px;font-size:0.8rem;color:var(--text-secondary);font-weight:600;">Date</th>
                    <th style="text-align:center;padding:8px 12px;font-size:0.8rem;color:var(--text-secondary);font-weight:600;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentBills as $bill): ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:10px 12px;font-size:0.85rem;"><?= htmlspecialchars($bill['bill_number']) ?></td>
                    <td style="padding:10px 12px;font-size:0.85rem;"><?= htmlspecialchars($config['bill_types'][$bill['bill_type']] ?? ucfirst($bill['bill_type'])) ?></td>
                    <td style="padding:10px 12px;font-size:0.85rem;"><?= htmlspecialchars($bill['to_name']) ?></td>
                    <td style="padding:10px 12px;font-size:0.85rem;text-align:right;font-weight:600;color:var(--amber);"><?= htmlspecialchars($bill['currency']) ?> <?= number_format((float)$bill['total_amount'], 2) ?></td>
                    <td style="padding:10px 12px;font-size:0.85rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($bill['bill_date'])) ?></td>
                    <td style="padding:10px 12px;text-align:center;">
                        <a href="/projects/billx/view/<?= (int)$bill['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-file-invoice"></i></div>
        <h3 style="margin-bottom:8px;">No bills yet</h3>
        <p style="color:var(--text-secondary);margin-bottom:20px;">Generate your first bill to get started!</p>
        <a href="/projects/billx/generate" class="btn btn-primary">
            <i class="fas fa-plus"></i> Generate Bill
        </a>
    </div>
</div>
<?php endif; ?>
