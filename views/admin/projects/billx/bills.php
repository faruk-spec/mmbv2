<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-list" style="color:#f59e0b;"></i> BillX — All Bills</h1>
        <p style="color:var(--text-secondary);">Manage all generated bills</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/admin/projects/billx" class="btn btn-secondary"><i class="fas fa-tachometer-alt"></i> Overview</a>
        <a href="/admin/projects/billx/settings" class="btn btn-secondary"><i class="fas fa-cog"></i> Settings</a>
        <?php
        $exportParams = http_build_query(array_filter($filters ?? [], fn($v) => $v !== ''));
        ?>
        <a href="/admin/projects/billx/bills/export<?= $exportParams ? '?' . htmlspecialchars($exportParams) : '' ?>"
           class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</a>
    </div>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> Bill deleted successfully.
</div>
<?php endif; ?>
<?php if (isset($_GET['bulk_deleted'])): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> <?= (int)$_GET['bulk_deleted'] ?> bill(s) deleted successfully.
</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid #ff6b6b;color:#ff6b6b;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> Error: <?= View::e($_GET['error']) ?>
</div>
<?php endif; ?>

<!-- Search & Filter -->
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="/admin/projects/billx/bills" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:160px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Bill Type</label>
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
                <option value="<?= htmlspecialchars($k) ?>" <?= ($filters['bill_type'] ?? '') === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:1;min-width:160px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Bill / From / To</label>
            <input type="text" name="search" class="form-control" placeholder="Bill#, from or to name"
                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div style="flex:1;min-width:160px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">User (name/email)</label>
            <input type="text" name="user_search" class="form-control" placeholder="User name or email"
                   value="<?= htmlspecialchars($filters['user_search'] ?? '') ?>">
        </div>
        <div style="min-width:130px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Date From</label>
            <input type="date" name="date_from" class="form-control"
                   value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
        </div>
        <div style="min-width:130px;">
            <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Date To</label>
            <input type="date" name="date_to" class="form-control"
                   value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="/admin/projects/billx/bills" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- Bulk Actions Bar -->
<div id="bulkBar" style="display:none;background:rgba(245,158,11,.1);border:1px solid #f59e0b;border-radius:8px;padding:10px 16px;margin-bottom:16px;align-items:center;gap:12px;">
    <span id="bulkCount" style="font-weight:600;color:#f59e0b;">0 selected</span>
    <form method="POST" action="/admin/projects/billx/bills/bulk-delete" id="bulkDeleteForm"
          style="display:inline;" onsubmit="return confirmBulkDelete()">
        <?= \Core\Security::csrfField() ?>
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete Selected</button>
    </form>
    <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">
        <i class="fas fa-times"></i> Clear
    </button>
</div>

<!-- Bills Table -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3 class="card-title"><i class="fas fa-file-invoice"></i> Bills (<?= number_format($total) ?>)</h3>
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;color:var(--text-secondary);">
            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"> Select All Visible
        </label>
    </div>

    <?php if (empty($bills)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No bills found.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="table" id="billsTable">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="selectAllHead" onchange="toggleSelectAll(this)"></th>
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
                    <td><input type="checkbox" class="row-check" value="<?= (int)$bill['id'] ?>" onchange="updateBulkBar()"></td>
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
                        <div style="display:flex;gap:4px;">
                            <a href="/admin/projects/billx/bills/view/<?= (int)$bill['id'] ?>"
                               class="btn btn-sm btn-secondary" title="View Bill">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger"
                                onclick="confirmDelete(<?= (int)$bill['id'] ?>)"
                                title="Delete Bill"><i class="fas fa-trash"></i></button>
                        </div>
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
        $baseQuery = array_filter($filters ?? [], fn($v) => $v !== '');
    ?>
    <div style="display:flex;gap:8px;justify-content:center;padding:16px;flex-wrap:wrap;">
        <?php for ($i = 1; $i <= min($totalPages, 20); $i++): ?>
            <a href="?page=<?= $i ?>&<?= htmlspecialchars(http_build_query($baseQuery)) ?>"
               class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Delete single modal -->
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
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});

function toggleSelectAll(cb) {
    document.querySelectorAll('.row-check').forEach(c => { c.checked = cb.checked; });
    document.querySelectorAll('#selectAll, #selectAllHead').forEach(c => { c.checked = cb.checked; });
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const bar = document.getElementById('bulkBar');
    document.getElementById('bulkCount').textContent = checked.length + ' selected';
    bar.style.display = checked.length > 0 ? 'flex' : 'none';
    const cont = document.getElementById('bulkIdsContainer');
    cont.innerHTML = '';
    checked.forEach(c => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = c.value;
        cont.appendChild(inp);
    });
    // Sync select-all checkboxes
    const total = document.querySelectorAll('.row-check').length;
    const allChecked = checked.length === total && total > 0;
    document.querySelectorAll('#selectAll, #selectAllHead').forEach(c => { c.checked = allChecked; });
}

function clearSelection() {
    document.querySelectorAll('.row-check, #selectAll, #selectAllHead').forEach(c => { c.checked = false; });
    updateBulkBar();
}

function confirmBulkDelete() {
    const count = document.querySelectorAll('.row-check:checked').length;
    return confirm('Delete ' + count + ' selected bill(s)? This cannot be undone.');
}
</script>

<?php View::endSection(); ?>
