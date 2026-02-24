<?php
/**
 * ConvertX Admin — Jobs List
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tasks text-primary"></i> ConvertX — All Jobs</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Jobs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="form-inline">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search file or email" value="<?= htmlspecialchars($search) ?>">
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">All Statuses</option>
                        <?php foreach (['pending','processing','completed','failed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary mr-1"><i class="fas fa-search"></i> Filter</button>
                    <a href="/admin/projects/convertx/jobs" class="btn btn-sm btn-secondary">Reset</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Conversion Jobs <span class="badge badge-secondary"><?= number_format($total) ?></span></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Input File</th>
                                <th>Conversion</th>
                                <th>Status</th>
                                <th>AI Tasks</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">No jobs found</td></tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?= (int) $job['id'] ?></td>
                                <td><?= htmlspecialchars($job['user_name'] ?? $job['user_email'] ?? 'N/A') ?></td>
                                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($job['input_filename'] ?? '') ?>">
                                    <?= htmlspecialchars($job['input_filename'] ?? '') ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= strtoupper(htmlspecialchars($job['input_format'] ?? '?')) ?></span>
                                    → <span class="badge badge-primary"><?= strtoupper(htmlspecialchars($job['output_format'] ?? '?')) ?></span>
                                </td>
                                <td>
                                    <?php $sc = ['pending'=>'warning','processing'=>'info','completed'=>'success','failed'=>'danger','cancelled'=>'secondary'][$job['status']] ?? 'dark'; ?>
                                    <span class="badge badge-<?= $sc ?>"><?= strtoupper(htmlspecialchars($job['status'])) ?></span>
                                </td>
                                <td>
                                    <?php $aiTasks = json_decode($job['ai_tasks'] ?? '[]', true) ?: []; ?>
                                    <?php if ($aiTasks): ?>
                                        <?php foreach ($aiTasks as $t): ?>
                                        <span class="badge badge-light"><?= htmlspecialchars($t) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('M d, H:i', strtotime($job['created_at']))) ?></td>
                                <td>
                                    <?php if (in_array($job['status'], ['pending','processing'])): ?>
                                    <form method="POST" action="/admin/projects/convertx/jobs/cancel" style="display:inline;">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                        <input type="hidden" name="job_id" value="<?= (int) $job['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-warning" onclick="return confirm('Cancel this job?')">
                                            <i class="fas fa-stop"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" action="/admin/projects/convertx/jobs/delete" style="display:inline;">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                        <input type="hidden" name="job_id" value="<?= (int) $job['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Delete job #<?= (int)$job['id'] ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total > $perPage): ?>
            <div class="card-footer">
                <?php $pages = ceil($total / $perPage); ?>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>

    </div>
</section>
<?php View::endSection(); ?>
