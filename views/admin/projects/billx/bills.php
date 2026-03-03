<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-list" style="color:#f59e0b;"></i> BillX — All Bills</h1>
        <p style="color:var(--text-secondary);">Manage all generated bills</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/projects/billx" class="btn btn-secondary"><i class="fas fa-tachometer-alt"></i> Overview</a>
        <a href="/admin/projects/billx/settings" class="btn btn-secondary"><i class="fas fa-cog"></i> Settings</a>
    </div>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> Bill deleted successfully.
</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid #ff6b6b;color:#ff6b6b;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> Error: <?= View::e($_GET['error']) ?>
</div>
<?php endif; ?>

<!-- Filter -->
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="/admin/projects/billx/bills" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Filter by Bill Type</label>
            <select name="bill_type" class="form-control">
                <option value="">All Types</option>
                <?php
                $allBillTypes = [
                    'fuel'=>'Fuel Bill','driver'=>'Driver Salary','helper'=>'Daily Helper Bill',
                    'rent'=>'Rent Receipt','book'=>'Book Invoice','internet'=>'Internet Invoice',
                    'restaurant'=>'Restaurant Bill','lta'=>'LTA Receipt','ecom'=>'E-Com Invoice',
                    'general'=>'General Bill','recharge'=>'Recharge Receipt','medical'=>'Medical Bill',
                    'stationary'=>'Stationary Bill','cab'=>'Cab & Travel Bill','mart'=>'Mart Bill',
                    'gym'=>'Gym Bill','hotel'=>'Hotel Bill','newspaper'=>'News Paper Bill',
                ];
                foreach ($allBillTypes as $k => $v):
                ?>
                <option value="<?= htmlspecialchars($k) ?>" <?= $billType === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="/admin/projects/billx/bills" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- Bills Table -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card-title"><i class="fas fa-file-invoice"></i> Bills (<?= number_format($total) ?>)</h3>
    </div>

    <?php if (empty($bills)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No bills found.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Bill #</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bills as $bill): ?>
                <tr>
                    <td><?= (int)$bill['id'] ?></td>
                    <td>
                        <div style="font-size:13px;font-weight:500;"><?= View::e($bill['user_name'] ?? '—') ?></div>
                        <div style="font-size:11px;color:var(--text-secondary);"><?= View::e($bill['user_email'] ?? '') ?></div>
                    </td>
                    <td><span class="badge badge-info"><?= View::e($bill['bill_type']) ?></span></td>
                    <td style="font-size:13px;"><?= View::e($bill['bill_number']) ?></td>
                    <td style="font-size:13px;"><?= View::e($bill['to_name']) ?></td>
                    <td style="font-weight:600;"><?= View::e($bill['currency'] ?? 'INR') ?> <?= number_format((float)$bill['total_amount'], 2) ?></td>
                    <td style="font-size:12px;"><?= date('M j, Y', strtotime($bill['created_at'])) ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="confirmDelete(<?= (int)$bill['id'] ?>)"
                            title="Delete Bill"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php
    $totalPages = (int)ceil($total / $perPage);
    if ($totalPages > 1):
    ?>
    <div style="display:flex;gap:8px;justify-content:center;padding:16px;">
        <?php for ($i = 1; $i <= min($totalPages, 20); $i++): ?>
            <a href="?page=<?= $i ?>&bill_type=<?= urlencode($billType) ?>"
               class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Delete modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;">
    <div class="card" style="max-width:400px;width:90%;padding:24px;">
        <h3 style="margin-bottom:12px;"><i class="fas fa-exclamation-triangle" style="color:#ff6b6b;"></i> Confirm Delete</h3>
        <p style="color:var(--text-secondary);margin-bottom:20px;">Are you sure you want to permanently delete this bill? This cannot be undone.</p>
        <form method="POST" action="/admin/projects/billx/bills/delete" id="deleteForm">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" id="deleteId" value="">
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('deleteModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').style.display = 'flex';
}
</script>

<?php View::endSection(); ?>
