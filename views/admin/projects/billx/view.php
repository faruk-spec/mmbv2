<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php
/** @var array $bill @var array|null $user @var array $config */
$typeLabel = $config['bill_types'][$bill['bill_type']] ?? ucfirst($bill['bill_type']);
$sym = ['INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'][$bill['currency']] ?? ($bill['currency'] . ' ');
$group = $config['bill_groups'][$bill['bill_type']] ?? 'invoice';
$c     = $config['bill_colors'][$bill['bill_type']] ?? '#37474f';
$items    = $bill['items'];
$subtotal = (float)$bill['subtotal'];
$taxPct   = (float)$bill['tax_percent'];
$taxAmt   = (float)$bill['tax_amount'];
$discount = (float)$bill['discount_amount'];
$total    = (float)$bill['total_amount'];
$billDate = $bill['bill_date'] ? date('d M Y', strtotime($bill['bill_date'])) : '';
$td = $bill['template_data'];
$tplStyle = $td['template_style'] ?? '1';
$createdAt = $bill['created_at'] ? date('d M Y H:i', strtotime($bill['created_at'])) : '—';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1><i class="fas fa-file-invoice" style="color:#f59e0b;"></i>
            Bill #<?= View::e($bill['bill_number']) ?>
            <span style="font-size:1rem;font-weight:400;color:var(--text-secondary);">— <?= View::e($typeLabel) ?></span>
        </h1>
        <p style="color:var(--text-secondary);">
            Generated <?= $createdAt ?>
            <?php if ($user): ?>
            &nbsp;&bull;&nbsp; by <strong><?= View::e($user['name']) ?></strong>
            (<?= View::e($user['email']) ?>)
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/admin/projects/billx/bills" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Bills</a>
        <a href="/projects/billx/pdf/<?= (int)$bill['id'] ?>" target="_blank" class="btn btn-secondary">
            <i class="fas fa-print"></i> Print / PDF
        </a>
        <button type="button" class="btn btn-danger"
                onclick="document.getElementById('adminDeleteModal').style.display='flex'">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>
</div>

<!-- Bill metadata -->
<div class="grid grid-2" style="gap:20px;margin-bottom:24px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Bill Details</h3>
        </div>
        <table class="table" style="font-size:13px;">
            <tbody>
                <tr><td style="color:var(--text-secondary);width:40%;">Bill ID</td><td><?= (int)$bill['id'] ?></td></tr>
                <tr><td style="color:var(--text-secondary);">Bill Number</td><td><strong><?= View::e($bill['bill_number']) ?></strong></td></tr>
                <tr><td style="color:var(--text-secondary);">Bill Type</td><td><span class="badge badge-info"><?= View::e($bill['bill_type']) ?></span></td></tr>
                <tr><td style="color:var(--text-secondary);">Bill Date</td><td><?= $billDate ?></td></tr>
                <tr><td style="color:var(--text-secondary);">Currency</td><td><?= View::e($bill['currency']) ?></td></tr>
                <tr><td style="color:var(--text-secondary);">Status</td><td><?= View::e($bill['status']) ?></td></tr>
                <tr><td style="color:var(--text-secondary);">Created At</td><td><?= $createdAt ?></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calculator"></i> Financials</h3>
        </div>
        <table class="table" style="font-size:13px;">
            <tbody>
                <tr><td style="color:var(--text-secondary);width:40%;">Subtotal</td><td><strong><?= $sym . number_format($subtotal, 2) ?></strong></td></tr>
                <?php if ($taxPct > 0): ?>
                <tr><td style="color:var(--text-secondary);">Tax (<?= $taxPct ?>%)</td><td><?= $sym . number_format($taxAmt, 2) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($td['cgst_pct']) && (float)$td['cgst_pct'] > 0): ?>
                <tr><td style="color:var(--text-secondary);">CGST (<?= (float)$td['cgst_pct'] ?>%)</td>
                    <td><?= $sym . number_format(round($subtotal * (float)$td['cgst_pct'] / 100, 2), 2) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($td['sgst_pct']) && (float)$td['sgst_pct'] > 0): ?>
                <tr><td style="color:var(--text-secondary);">SGST (<?= (float)$td['sgst_pct'] ?>%)</td>
                    <td><?= $sym . number_format(round($subtotal * (float)$td['sgst_pct'] / 100, 2), 2) ?></td></tr>
                <?php endif; ?>
                <?php if ($discount > 0): ?>
                <tr><td style="color:var(--text-secondary);">Discount</td><td style="color:var(--green);">-<?= $sym . number_format($discount, 2) ?></td></tr>
                <?php endif; ?>
                <tr style="border-top:2px solid var(--border-color);">
                    <td style="font-weight:700;">TOTAL</td>
                    <td style="font-weight:700;font-size:15px;color:#f59e0b;"><?= $sym . number_format($total, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Parties -->
<div class="grid grid-2" style="gap:20px;margin-bottom:24px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-building"></i> From (Issuer)</h3></div>
        <div style="padding:16px;font-size:13px;line-height:1.8;">
            <div style="font-weight:600;font-size:15px;"><?= View::e($bill['from_name']) ?></div>
            <?php if ($bill['from_address']): ?><div style="color:var(--text-secondary);"><?= nl2br(View::e($bill['from_address'])) ?></div><?php endif; ?>
            <?php if ($bill['from_phone']): ?><div>📞 <?= View::e($bill['from_phone']) ?></div><?php endif; ?>
            <?php if ($bill['from_email']): ?><div>✉️ <?= View::e($bill['from_email']) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-user"></i> To (Recipient)</h3></div>
        <div style="padding:16px;font-size:13px;line-height:1.8;">
            <div style="font-weight:600;font-size:15px;"><?= View::e($bill['to_name']) ?></div>
            <?php if ($bill['to_address']): ?><div style="color:var(--text-secondary);"><?= nl2br(View::e($bill['to_address'])) ?></div><?php endif; ?>
            <?php if ($bill['to_phone']): ?><div>📞 <?= View::e($bill['to_phone']) ?></div><?php endif; ?>
            <?php if ($bill['to_email']): ?><div>✉️ <?= View::e($bill['to_email']) ?></div><?php endif; ?>
        </div>
    </div>
</div>

<!-- Items -->
<?php if (!empty($items)): ?>
<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-list-ul"></i> Items / Services</h3></div>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">Rate</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td style="color:var(--text-secondary);"><?= $i + 1 ?></td>
                    <td><?= View::e($item['description'] ?? '—') ?></td>
                    <td style="text-align:right;"><?= number_format((float)($item['qty'] ?? 1), 2) ?></td>
                    <td style="text-align:right;"><?= $sym . number_format((float)($item['rate'] ?? 0), 2) ?></td>
                    <td style="text-align:right;font-weight:600;"><?= $sym . number_format((float)($item['amount'] ?? 0), 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Additional / Template Data -->
<?php if (!empty($td)): ?>
<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-receipt"></i> Additional Details</h3></div>
    <div style="padding:16px;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;font-size:13px;">
        <?php foreach ($td as $tdKey => $tdVal):
            if ($tdKey === 'template_style' || trim((string)$tdVal) === '') continue;
            $label = ucwords(str_replace('_', ' ', $tdKey));
        ?>
        <div>
            <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;"><?= htmlspecialchars($label) ?></div>
            <div style="font-weight:500;"><?= View::e($tdVal) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Notes -->
<?php if (!empty($bill['notes'])): ?>
<div class="card" style="margin-bottom:24px;">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note"></i> Notes</h3></div>
    <div style="padding:16px;font-size:13px;color:var(--text-secondary);"><?= nl2br(View::e($bill['notes'])) ?></div>
</div>
<?php endif; ?>

<!-- Delete Modal -->
<div id="adminDeleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;">
    <div class="card" style="max-width:400px;width:90%;padding:24px;">
        <h3 style="margin-bottom:12px;"><i class="fas fa-exclamation-triangle" style="color:#ff6b6b;"></i> Confirm Delete</h3>
        <p style="color:var(--text-secondary);margin-bottom:20px;">
            Permanently delete bill <strong>#<?= View::e($bill['bill_number']) ?></strong>? This cannot be undone.
        </p>
        <form method="POST" action="/admin/projects/billx/bills/delete">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$bill['id'] ?>">
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('adminDeleteModal').style.display='none'"
                        class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('adminDeleteModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<?php View::endSection(); ?>
