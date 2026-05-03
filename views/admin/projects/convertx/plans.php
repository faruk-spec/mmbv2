<?php
/**
 * ConvertX Admin — Subscription Plans
 */
use Core\View;
View::extend('admin');
$planFeatureLabels = $planFeatureLabels ?? [];
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tags text-primary"></i> ConvertX — Subscription Plans</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Plans</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
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
            <?= htmlspecialchars($flashError) ?>
        </div>
        <?php endif; ?>

        <!-- Add Plan -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle"></i> Create New Plan</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/projects/convertx/plans/create">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Pro" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" class="form-control" placeholder="e.g. pro" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Price</label>
                            <input type="number" name="price" class="form-control" value="0.00" step="0.01" min="0">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Currency</label>
                            <select name="currency" class="form-control">
                                <?php foreach (['USD','EUR','GBP','INR','AED','SAR','BDT','PKR','NGN','BRL','MXN','CAD','AUD','JPY'] as $cur): ?>
                                <option value="<?= $cur ?>"><?= $cur ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Billing Cycle</label>
                            <select name="billing_cycle" class="form-control">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="lifetime">Lifetime</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Short description">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Max Jobs/Month (-1=&#8734;)</label>
                            <input type="number" name="max_jobs_per_month" class="form-control" value="50">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Max File Size (MB)</label>
                            <input type="number" name="max_file_size_mb" class="form-control" value="10">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Max Batch Size</label>
                            <input type="number" name="max_batch_size" class="form-control" value="5">
                        </div>
                        <div class="col-md-1 form-group">
                            <label>Sort</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="new_ai_access" name="ai_access" value="1">
                                <label class="custom-control-label" for="new_ai_access">AI Access</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="new_api_access" name="api_access" value="1">
                                <label class="custom-control-label" for="new_api_access">API Access</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="new_batch_convert" name="batch_convert" value="1" checked>
                                <label class="custom-control-label" for="new_batch_convert">Batch Convert</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox mt-4">
                                <input type="checkbox" class="custom-control-input" id="new_priority" name="priority_processing" value="1">
                                <label class="custom-control-label" for="new_priority">Priority Processing</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6 form-group">
                            <label>Contact / Sales Page URL <small class="text-muted">(shown instead of payment for this plan — leave empty to use default payment)</small></label>
                            <input type="url" name="contact_sale_url" class="form-control" placeholder="https://example.com/contact-sales">
                        </div>
                    </div>
                    <?php if (!empty($planFeatureLabels)): ?>
                    <div class="mt-3">
                        <label class="d-block mb-2"><strong>Micro Features</strong></label>
                        <div class="row">
                            <?php foreach ($planFeatureLabels as $fKey => $fLabel): ?>
                            <div class="col-md-3 col-sm-6">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="new_feature_<?= htmlspecialchars($fKey) ?>" name="feature_<?= htmlspecialchars($fKey) ?>" value="1">
                                    <label class="custom-control-label" for="new_feature_<?= htmlspecialchars($fKey) ?>"><?= htmlspecialchars($fLabel) ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-sm mt-2"><i class="fas fa-plus"></i> Create Plan</button>
                </form>
            </div>
        </div>

        <!-- Plans list -->
        <?php if (empty($plans)): ?>
        <div class="card">
            <div class="card-body text-center text-muted py-4">No plans found. Create one above.</div>
        </div>
        <?php else: ?>
        <?php foreach ($plans as $plan): ?>
        <?php $featureMap = json_decode($plan['features'] ?? '{}', true) ?: []; ?>
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
                <div>
                    <strong><?= htmlspecialchars($plan['name']) ?></strong>
                    <span class="badge badge-secondary ml-2"><?= htmlspecialchars($plan['slug']) ?></span>
                    <span class="badge <?= $plan['status'] === 'active' ? 'badge-success' : 'badge-danger' ?> ml-1"><?= ucfirst($plan['status']) ?></span>
                    &nbsp; <?= htmlspecialchars($plan['currency'] ?? 'USD') ?> <?= number_format((float)$plan['price'], 2) ?>/<?= htmlspecialchars($plan['billing_cycle']) ?>
                    &nbsp; <small class="text-muted"><?= (int)($plan['subscriber_count'] ?? 0) ?> subscribers</small>
                </div>
                <div class="d-flex" style="gap:6px;">
                    <button class="btn btn-sm btn-info" onclick="toggleEditPlan(<?= (int)$plan['id'] ?>)">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <form method="POST" action="/admin/projects/convertx/plans/delete" style="display:inline;"
                          onsubmit="return confirm('Delete plan ' + <?= json_encode($plan['name'], JSON_HEX_APOS | JSON_HEX_QUOT) ?> + '?')">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                        <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
            <!-- Inline edit form -->
            <div id="edit-plan-<?= (int)$plan['id'] ?>" style="display:none;padding:16px 24px;background:var(--bg-secondary,#f8f9fa);border-top:1px solid #dee2e6;">
                <form method="POST" action="/admin/projects/convertx/plans/update">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                    <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($plan['name']) ?>" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Price</label>
                            <input type="number" name="price" class="form-control" value="<?= number_format((float)$plan['price'],2,'.','') ?>" step="0.01" min="0">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Currency</label>
                            <select name="currency" class="form-control">
                                <?php foreach (['USD','EUR','GBP','INR','AED','SAR','BDT','PKR','NGN','BRL','MXN','CAD','AUD','JPY'] as $cur): ?>
                                <option value="<?= $cur ?>" <?= ($plan['currency'] ?? 'USD') === $cur ? 'selected' : '' ?>><?= $cur ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Billing Cycle</label>
                            <select name="billing_cycle" class="form-control">
                                <?php foreach (['monthly','yearly','lifetime'] as $bc): ?>
                                <option value="<?= $bc ?>" <?= $plan['billing_cycle'] === $bc ? 'selected' : '' ?>><?= ucfirst($bc) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($plan['description'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active" <?= $plan['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $plan['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Max Jobs/Month</label>
                            <input type="number" name="max_jobs_per_month" class="form-control" value="<?= (int)$plan['max_jobs_per_month'] ?>">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Max File (MB)</label>
                            <input type="number" name="max_file_size_mb" class="form-control" value="<?= (int)$plan['max_file_size_mb'] ?>">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Max Batch</label>
                            <input type="number" name="max_batch_size" class="form-control" value="<?= (int)$plan['max_batch_size'] ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="ai_<?= (int)$plan['id'] ?>" name="ai_access" value="1" <?= $plan['ai_access'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="ai_<?= (int)$plan['id'] ?>">AI Access</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="api_<?= (int)$plan['id'] ?>" name="api_access" value="1" <?= $plan['api_access'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="api_<?= (int)$plan['id'] ?>">API Access</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="batch_<?= (int)$plan['id'] ?>" name="batch_convert" value="1" <?= $plan['batch_convert'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="batch_<?= (int)$plan['id'] ?>">Batch Convert</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="prio_<?= (int)$plan['id'] ?>" name="priority_processing" value="1" <?= $plan['priority_processing'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="prio_<?= (int)$plan['id'] ?>">Priority</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6 form-group">
                            <label>Contact / Sales Page URL <small class="text-muted">(leave empty to use default payment)</small></label>
                            <input type="url" name="contact_sale_url" class="form-control"
                                   value="<?= htmlspecialchars($plan['contact_sale_url'] ?? '') ?>"
                                   placeholder="https://example.com/contact-sales">
                        </div>
                    </div>
                    <?php if (!empty($planFeatureLabels)): ?>
                    <div class="row mt-2">
                        <div class="col-12">
                            <label class="d-block mb-2"><strong>Micro Features</strong></label>
                        </div>
                        <?php foreach ($planFeatureLabels as $fKey => $fLabel): ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="feature_<?= (int)$plan['id'] ?>_<?= htmlspecialchars($fKey) ?>"
                                       name="feature_<?= htmlspecialchars($fKey) ?>"
                                       value="1"
                                       <?= !empty($featureMap[$fKey]) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="feature_<?= (int)$plan['id'] ?>_<?= htmlspecialchars($fKey) ?>"><?= htmlspecialchars($fLabel) ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditPlan(<?= (int)$plan['id'] ?>)">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
function toggleEditPlan(id) {
    var el = document.getElementById('edit-plan-' + id);
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php View::endSection(); ?>
