<?php
/**
 * ConvertX Admin — Overview Dashboard
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-file-export text-primary"></i>
                    ConvertX — Overview
                </h1>
                <p class="text-muted mb-0">Conversion jobs, usage stats and AI activity</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item active">ConvertX</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($stats['total_jobs']) ?></h3>
                        <p>Total Jobs</p>
                    </div>
                    <div class="icon"><i class="fas fa-tasks"></i></div>
                    <a href="/admin/projects/convertx/jobs" class="small-box-footer">All Jobs <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= number_format($stats['completed_jobs']) ?></h3>
                        <p>Completed</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <a href="/admin/projects/convertx/jobs?status=completed" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= number_format($stats['failed_jobs']) ?></h3>
                        <p>Failed</p>
                    </div>
                    <div class="icon"><i class="fas fa-times-circle"></i></div>
                    <a href="/admin/projects/convertx/jobs?status=failed" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= number_format($stats['pending_jobs']) ?></h3>
                        <p>Pending</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <a href="/admin/projects/convertx/jobs?status=pending" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= number_format($stats['active_users']) ?></h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <a href="/admin/projects/convertx/users" class="small-box-footer">Users <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box" style="background:#6366f1;color:#fff;">
                    <div class="inner">
                        <h3><?= number_format($stats['tokens_used']) ?></h3>
                        <p>AI Tokens Used</p>
                    </div>
                    <div class="icon"><i class="fas fa-robot"></i></div>
                    <a href="/admin/projects/convertx/settings" class="small-box-footer" style="color:rgba(255,255,255,.8);">Providers <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-link"></i> Quick Links</h3>
                    </div>
                    <div class="card-body">
                        <a href="/admin/projects/convertx/jobs" class="btn btn-outline-primary mr-2 mb-2">
                            <i class="fas fa-tasks"></i> All Jobs
                        </a>
                        <a href="/admin/projects/convertx/users" class="btn btn-outline-info mr-2 mb-2">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a href="/admin/projects/convertx/api-keys" class="btn btn-outline-secondary mr-2 mb-2">
                            <i class="fas fa-key"></i> API Keys
                        </a>
                        <a href="/admin/projects/convertx/settings" class="btn btn-outline-warning mr-2 mb-2">
                            <i class="fas fa-cog"></i> Settings / AI Providers
                        </a>
                        <a href="/admin/projects/convertx/roles" class="btn btn-outline-dark mr-2 mb-2">
                            <i class="fas fa-users-cog"></i> Roles / User Features
                        </a>
                        <a href="/projects/convertx/dashboard" class="btn btn-outline-success mr-2 mb-2" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Open ConvertX
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Usage -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-brain"></i> AI Usage by Provider</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th class="text-right">AI Jobs</th>
                                    <th class="text-right">Tokens</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($aiUsage['providers'])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No provider usage found yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($aiUsage['providers'] as $row): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($row['provider_used'] ?: 'unknown') ?></code></td>
                                    <td class="text-right"><?= number_format((int) ($row['jobs'] ?? 0)) ?></td>
                                    <td class="text-right"><?= number_format((int) ($row['tokens'] ?? 0)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted">
                        This month: <strong><?= number_format((int) ($aiUsage['this_month_ai_jobs'] ?? 0)) ?></strong> AI jobs,
                        <strong><?= number_format((int) ($aiUsage['this_month_tokens'] ?? 0)) ?></strong> tokens.
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-astronaut"></i> Top AI Users</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th class="text-right">AI Jobs</th>
                                    <th class="text-right">Tokens</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($aiUsage['users'])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No user token usage found yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($aiUsage['users'] as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name'] ?? $row['email'] ?? 'Unknown') ?></td>
                                    <td class="text-right"><?= number_format((int) ($row['jobs'] ?? 0)) ?></td>
                                    <td class="text-right"><?= number_format((int) ($row['tokens'] ?? 0)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Recent Conversion Jobs</h3>
                        <div class="card-tools">
                            <a href="/admin/projects/convertx/jobs" class="btn btn-sm btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>File</th>
                                        <th>Conversion</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($recentJobs)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">No jobs found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentJobs as $job): ?>
                                    <tr>
                                        <td><?= (int) $job['id'] ?></td>
                                        <td><?= htmlspecialchars($job['user_name'] ?? $job['user_email'] ?? 'Unknown') ?></td>
                                        <td><?= htmlspecialchars($job['input_filename'] ?? '') ?></td>
                                        <td>
                                            <span class="badge badge-light"><?= strtoupper(htmlspecialchars($job['input_format'] ?? '?')) ?></span>
                                            <i class="fas fa-arrow-right" style="font-size:10px;"></i>
                                            <span class="badge badge-primary"><?= strtoupper(htmlspecialchars($job['output_format'] ?? '?')) ?></span>
                                        </td>
                                        <td>
                                            <?php $sc = ['pending'=>'warning','processing'=>'info','completed'=>'success','failed'=>'danger','cancelled'=>'secondary'][$job['status']] ?? 'dark'; ?>
                                            <span class="badge badge-<?= $sc ?>"><?= strtoupper(htmlspecialchars($job['status'])) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars(date('M d, H:i', strtotime($job['created_at']))) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
<?php View::endSection(); ?>
