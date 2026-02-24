<?php
/**
 * ConvertX Admin — Settings / AI Providers (full CRUD)
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cog text-primary"></i> ConvertX — AI Provider Settings</h1>
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
        <?php
        $flashSuccess = $_SESSION['_flash']['success'] ?? null;
        $flashError   = $_SESSION['_flash']['error']   ?? null;
        unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
        ?>
        <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flashSuccess) ?>
        </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flashError) ?>
        </div>
        <?php endif; ?>

        <!-- Add Provider -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle"></i> Add New AI Provider</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/projects/convertx/settings/create-provider">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. OpenAI" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" placeholder="e.g. openai" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Base URL</label>
                            <input type="url" name="base_url" class="form-control" placeholder="https://api.openai.com">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>API Key</label>
                            <input type="text" name="api_key" class="form-control" placeholder="sk-...">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Model</label>
                            <input type="text" name="model" class="form-control" placeholder="gpt-4o-mini">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Capabilities (comma-separated)</label>
                            <input type="text" name="capabilities" class="form-control" placeholder="ocr,summarization,translation,classification">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Allowed Tiers (comma-separated)</label>
                            <input type="text" name="allowed_tiers" class="form-control" value="free,pro,enterprise">
                        </div>
                        <div class="col-md-1 form-group">
                            <label>Priority</label>
                            <input type="number" name="priority" class="form-control" value="10" min="1" max="100">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Cost/1K tokens ($)</label>
                            <input type="number" name="cost_per_1k_tokens" class="form-control" value="0.000150" step="0.000001" min="0">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Provider</button>
                </form>
            </div>
        </div>

        <!-- Providers list -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-robot"></i> AI Providers</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($providers)): ?>
                <p class="text-center text-muted py-4">No providers found. Add one above or run the migration SQL.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th><th>Slug</th><th>Model</th><th>Capabilities</th>
                                <th>Priority</th><th>Cost/1K</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
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
                            <td><?= (int)$p['priority'] ?></td>
                            <td>$<?= number_format((float)$p['cost_per_1k_tokens'], 6) ?></td>
                            <td>
                                <?php if ($p['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Toggle enable/disable -->
                                <form method="POST" action="/admin/projects/convertx/settings" style="display:inline;">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                    <input type="hidden" name="provider_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="is_active" value="<?= $p['is_active'] ? '0' : '1' ?>">
                                    <button type="submit" class="btn btn-xs <?= $p['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                        <?= $p['is_active'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                                <!-- Edit toggle -->
                                <button type="button" class="btn btn-xs btn-info" onclick="toggleEditProvider(<?= (int)$p['id'] ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <!-- Delete -->
                                <form method="POST" action="/admin/projects/convertx/settings/delete-provider" style="display:inline;"
                                      onsubmit="return confirm('Delete provider <?= htmlspecialchars($p['name']) ?>?')">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                    <input type="hidden" name="provider_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <!-- Inline edit row -->
                        <tr id="edit-provider-<?= (int)$p['id'] ?>" style="display:none;background:var(--bg-secondary,#f8f9fa);">
                            <td colspan="8" style="padding:16px 24px;">
                                <form method="POST" action="/admin/projects/convertx/settings/edit-provider">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                    <input type="hidden" name="provider_id" value="<?= (int)$p['id'] ?>">
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Base URL</label>
                                            <input type="url" name="base_url" class="form-control" value="<?= htmlspecialchars($p['base_url'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>API Key</label>
                                            <input type="text" name="api_key" class="form-control" value="<?= htmlspecialchars($p['api_key'] ?? '') ?>" placeholder="Leave blank to keep current">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Model</label>
                                            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($p['model'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Capabilities (comma-separated)</label>
                                            <input type="text" name="capabilities" class="form-control"
                                                   value="<?= htmlspecialchars(implode(',', json_decode($p['capabilities'] ?? '[]', true) ?: [])) ?>">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Allowed Tiers (comma-separated)</label>
                                            <input type="text" name="allowed_tiers" class="form-control"
                                                   value="<?= htmlspecialchars(implode(',', json_decode($p['allowed_tiers'] ?? '[]', true) ?: [])) ?>">
                                        </div>
                                        <div class="col-md-1 form-group">
                                            <label>Priority</label>
                                            <input type="number" name="priority" class="form-control" value="<?= (int)$p['priority'] ?>" min="1" max="100">
                                        </div>
                                        <div class="col-md-2 form-group">
                                            <label>Cost/1K tokens ($)</label>
                                            <input type="number" name="cost_per_1k_tokens" class="form-control"
                                                   value="<?= number_format((float)$p['cost_per_1k_tokens'], 6, '.', '') ?>"
                                                   step="0.000001" min="0">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Changes</button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditProvider(<?= (int)$p['id'] ?>)">Cancel</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<script>
function toggleEditProvider(id) {
    var row = document.getElementById('edit-provider-' + id);
    if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
}
</script>
<?php View::endSection(); ?>
