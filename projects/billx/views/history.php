<?php
/** @var array $bills @var int $total @var int $page @var int $pages @var array $config @var array $user */
$csrfToken = \Core\Security::generateCsrfToken();
$deleted = isset($_GET['deleted']);
$hasError = isset($_GET['error']);
?>

<a href="/projects/billx" class="back-link"><i class="fas fa-arrow-left"></i> Dashboard</a>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h2 style="font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,#f59e0b,#00f0ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            <i class="fas fa-history" style="-webkit-text-fill-color:#f59e0b;"></i> Bill History
        </h2>
        <p style="color:var(--text-secondary);margin-top:4px;"><?= number_format($total) ?> bill<?= $total !== 1 ? 's' : '' ?> generated</p>
    </div>
    <a href="/projects/billx/generate" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Bill
    </a>
</div>

<?php if ($deleted): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Bill deleted successfully.</div>
<?php elseif ($hasError): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Could not delete bill.</div>
<?php endif; ?>

<?php if (empty($bills)): ?>
<div class="card">
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-file-invoice"></i></div>
        <h3 style="margin-bottom:8px;">No bills yet</h3>
        <p style="color:var(--text-secondary);margin-bottom:20px;">Generate your first bill to see it here.</p>
        <a href="/projects/billx/generate" class="btn btn-primary"><i class="fas fa-plus"></i> Generate Bill</a>
    </div>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
            <thead>
                <tr style="background:var(--bg-secondary);border-bottom:1px solid var(--border-color);">
                    <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Bill #</th>
                    <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Type</th>
                    <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">From</th>
                    <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">To</th>
                    <th style="padding:12px 16px;text-align:right;color:var(--text-secondary);font-weight:600;">Total</th>
                    <th style="padding:12px 16px;text-align:left;color:var(--text-secondary);font-weight:600;">Date</th>
                    <th style="padding:12px 16px;text-align:center;color:var(--text-secondary);font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bills as $bill): ?>
                <tr style="border-bottom:1px solid var(--border-color);transition:background 0.15s;"
                    onmouseover="this.style.background='var(--bg-secondary)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;font-weight:600;"><?= htmlspecialchars($bill['bill_number']) ?></td>
                    <td style="padding:12px 16px;">
                        <span style="padding:3px 10px;background:rgba(245,158,11,0.15);color:#f59e0b;border-radius:12px;font-size:0.75rem;font-weight:600;">
                            <?= htmlspecialchars($config['bill_types'][$bill['bill_type']] ?? ucfirst($bill['bill_type'])) ?>
                        </span>
                    </td>
                    <td style="padding:12px 16px;"><?= htmlspecialchars($bill['from_name']) ?></td>
                    <td style="padding:12px 16px;"><?= htmlspecialchars($bill['to_name']) ?></td>
                    <td style="padding:12px 16px;text-align:right;font-weight:700;color:#f59e0b;">
                        <?= htmlspecialchars($bill['currency']) ?> <?= number_format((float)$bill['total_amount'], 2) ?>
                    </td>
                    <td style="padding:12px 16px;color:var(--text-secondary);"><?= date('d M Y', strtotime($bill['bill_date'])) ?></td>
                    <td style="padding:12px 16px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                            <a href="/projects/billx/view/<?= (int)$bill['id'] ?>" class="btn btn-secondary btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/projects/billx/download/<?= (int)$bill['id'] ?>" class="btn btn-secondary btn-sm" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm"
                                    title="Delete"
                                    onclick="confirmDelete(<?= (int)$bill['id'] ?>, '<?= htmlspecialchars(addslashes($bill['bill_number'])) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap;">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
    <a href="/projects/billx/history?page=<?= $p ?>"
       style="padding:6px 14px;border-radius:6px;text-decoration:none;font-size:0.85rem;
              background:<?= $p === $page ? 'linear-gradient(135deg,#f59e0b,#d97706)' : 'var(--bg-card)' ?>;
              color:<?= $p === $page ? 'white' : 'var(--text-secondary)' ?>;
              border:1px solid <?= $p === $page ? 'transparent' : 'var(--border-color)' ?>;">
        <?= $p ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Delete modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div class="card" style="max-width:400px;width:90%;padding:28px;">
        <h3 style="margin-bottom:12px;"><i class="fas fa-exclamation-triangle" style="color:#ff6b6b;"></i> Delete Bill</h3>
        <p style="color:var(--text-secondary);margin-bottom:20px;">Are you sure you want to delete bill <strong id="deleteBillNo"></strong>? This cannot be undone.</p>
        <form method="POST" action="/projects/billx/delete">
            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="id" id="deleteId">
            <div class="form-actions">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                <button type="button" class="btn btn-secondary" onclick="closeDelete()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, billNo) {
    document.getElementById('deleteId').value    = id;
    document.getElementById('deleteBillNo').textContent = billNo;
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
}
function closeDelete() { document.getElementById('deleteModal').style.display = 'none'; }
document.getElementById('deleteModal').addEventListener('click', function(e){ if(e.target === this) closeDelete(); });
</script>
