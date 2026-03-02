<?php
/**
 * BillX Admin — All Bills
 */
use Core\View;
use Core\Security;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-invoice text-warning"></i> BillX — All Bills</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/billx">BillX</a></li>
                    <li class="breadcrumb-item active">Bills</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if (!empty($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <i class="fas fa-check-circle"></i> Bill deleted successfully.
        </div>
        <?php endif; ?>

        <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <i class="fas fa-exclamation-circle"></i>
            <?php
            $errMap = ['invalid_token' => 'Invalid CSRF token.', 'invalid_id' => 'Invalid bill ID.', 'db_unavailable' => 'Database unavailable.', 'db_error' => 'Database error.'];
            echo htmlspecialchars($errMap[$_GET['error']] ?? 'An error occurred.');
            ?>
        </div>
        <?php endif; ?>

        <?php if (!$dbConnected): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>BillX database not connected.</strong>
            <a href="/admin/projects/database-setup/billx" class="alert-link ml-2">Configure database →</a>
        </div>
        <?php endif; ?>

        <!-- Quick Nav -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-outline card-warning">
                    <div class="card-body py-2">
                        <a href="/admin/projects/billx" class="btn btn-sm btn-outline-secondary mr-1"><i class="fas fa-tachometer-alt"></i> Overview</a>
                        <a href="/admin/projects/billx/bills" class="btn btn-sm btn-warning mr-1"><i class="fas fa-list"></i> All Bills</a>
                        <a href="/admin/projects/billx/settings" class="btn btn-sm btn-outline-secondary"><i class="fas fa-cog"></i> Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="form-inline">
                    <label class="mr-2">Bill Type:</label>
                    <select name="bill_type" class="form-control form-control-sm mr-2">
                        <option value="">All Types</option>
                        <?php foreach ($billTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $billType === $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary mr-1"><i class="fas fa-filter"></i> Filter</button>
                    <a href="/admin/projects/billx/bills" class="btn btn-sm btn-secondary">Reset</a>
                </form>
            </div>
        </div>

        <!-- Bills Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Bills
                    <span class="badge badge-secondary ml-1"><?= number_format($total) ?></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Bill Type</th>
                                <th>Bill Number</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($bills)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No bills found</td></tr>
                        <?php else: ?>
                            <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><?= (int)$bill['id'] ?></td>
                                <td>
                                    <?php if (!empty($bill['user_name'])): ?>
                                        <?= htmlspecialchars($bill['user_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($bill['user_email'] ?? '') ?></small>
                                    <?php elseif (!empty($bill['user_email'])): ?>
                                        <?= htmlspecialchars($bill['user_email']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">User #<?= (int)$bill['user_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($bill['bill_type'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($bill['bill_number'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($bill['total_amount'])): ?>
                                        <?= htmlspecialchars($bill['currency'] ?? '') ?> <?= number_format((float)$bill['total_amount'], 2) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars(substr($bill['created_at'] ?? '', 0, 16)) ?></small></td>
                                <td>
                                    <button class="btn btn-xs btn-danger"
                                            onclick="confirmDelete(<?= (int)$bill['id'] ?>)"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total > $perPage): ?>
            <div class="card-footer clearfix">
                <?php
                $totalPages = (int)ceil($total / $perPage);
                $typeParam  = $billType ? '&bill_type=' . urlencode($billType) : '';
                ?>
                <ul class="pagination pagination-sm m-0 float-right">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $typeParam ?>">«</a>
                    </li>
                    <?php endif; ?>

                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?><?= $typeParam ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $typeParam ?>">»</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <p class="text-muted float-left mt-1 mb-0">
                    Showing <?= number_format(($page - 1) * $perPage + 1) ?>–<?= number_format(min($page * $perPage, $total)) ?> of <?= number_format($total) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash text-danger mr-2"></i>Delete Bill</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete bill <strong id="deleteBillId"></strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="/admin/projects/billx/bills/delete" style="display:inline;">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="id" id="deleteIdInput">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('deleteBillId').textContent = '#' + id;
    document.getElementById('deleteIdInput').value = id;
    $('#deleteModal').modal('show');
}
</script>
<?php View::endSection(); ?>
