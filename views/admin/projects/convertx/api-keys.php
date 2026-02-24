<?php
/**
 * ConvertX Admin — API Keys
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-key text-primary"></i> ConvertX — API Keys</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">API Keys</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Issued API Keys</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Key (partial)</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($keys)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No API keys found</td></tr>
                        <?php else: ?>
                            <?php foreach ($keys as $k): ?>
                            <tr>
                                <td><?= (int) $k['id'] ?></td>
                                <td><?= htmlspecialchars($k['user_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($k['user_email'] ?? '') ?></td>
                                <td>
                                    <code><?= htmlspecialchars(substr($k['api_key'], 0, 10)) ?>…</code>
                                </td>
                                <td>
                                    <?php if ($k['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Revoked</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($k['created_at']))) ?></td>
                                <td>
                                    <?php if ($k['is_active']): ?>
                                    <form method="POST" action="/admin/projects/convertx/api-keys/revoke" style="display:inline;">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                        <input type="hidden" name="key_id" value="<?= (int) $k['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-warning" onclick="return confirm('Revoke this API key?')">
                                            <i class="fas fa-ban"></i> Revoke
                                        </button>
                                    </form>
                                    <?php endif; ?>
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
