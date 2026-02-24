<?php
/**
 * ConvertX Admin — Settings / AI Providers
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cog text-primary"></i> ConvertX — Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Flash messages -->
        <?php if (!empty($_SESSION['_flash']['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['_flash']['success']) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        <?php unset($_SESSION['_flash']['success']); ?>
        <?php endif; ?>

        <!-- AI Providers -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-robot"></i> AI Providers</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Slug</th>
                                <th>Model</th>
                                <th>Capabilities</th>
                                <th>Priority</th>
                                <th>Cost/1K tokens</th>
                                <th>Status</th>
                                <th>Toggle</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($providers)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">No providers found. Run schema.sql to create tables.</td></tr>
                        <?php else: ?>
                            <?php foreach ($providers as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                <td><code><?= htmlspecialchars($p['slug']) ?></code></td>
                                <td><?= htmlspecialchars($p['model'] ?? '—') ?></td>
                                <td>
                                    <?php $caps = json_decode($p['capabilities'] ?? '[]', true) ?: []; ?>
                                    <?php foreach ($caps as $c): ?>
                                    <span class="badge badge-light"><?= htmlspecialchars($c) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td><?= (int) $p['priority'] ?></td>
                                <td>$<?= number_format((float)$p['cost_per_1k_tokens'], 6) ?></td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="/admin/projects/convertx/settings" style="display:inline;">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                        <input type="hidden" name="provider_id" value="<?= (int) $p['id'] ?>">
                                        <?php if ($p['is_active']): ?>
                                            <input type="hidden" name="is_active" value="0">
                                            <button type="submit" class="btn btn-xs btn-warning">Disable</button>
                                        <?php else: ?>
                                            <input type="hidden" name="is_active" value="1">
                                            <button type="submit" class="btn btn-xs btn-success">Enable</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>
<?php View::endSection(); ?>
