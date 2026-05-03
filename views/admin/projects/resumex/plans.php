<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.plan-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; margin-bottom:24px; overflow:hidden; }
.plan-header { padding:20px 24px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
.plan-body { padding:20px 24px; }
.feature-toggle { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border-color); }
.feature-toggle:last-child { border-bottom:none; }
.toggle-switch { position:relative; width:44px; height:22px; cursor:pointer; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; top:0; left:0; right:0; bottom:0; background:#444; border-radius:22px; transition:.2s; }
.toggle-slider:before { content:""; position:absolute; width:16px; height:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
input:checked + .toggle-slider { background:var(--cyan); }
input:checked + .toggle-slider:before { transform:translateX(22px); }
.plan-toggle-feedback { position:fixed; bottom:20px; right:20px; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; z-index:9999; opacity:0; transition:opacity .3s; pointer-events:none; }
.plan-toggle-feedback.show { opacity:1; }
.plan-toggle-feedback.ok  { background:rgba(0,255,136,.15); border:1px solid var(--green); color:var(--green); }
.plan-toggle-feedback.err { background:rgba(255,107,107,.15); border:1px solid var(--red); color:var(--red); }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<input type="hidden" id="global_csrf" value="<?= \Core\Security::generateCsrfToken() ?>">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-crown" style="color:#f59e0b;"></i> ResumeX — Subscription Plans</h1>
        <p style="color:var(--text-secondary);">Manage subscription plans, pricing, and feature access for ResumeX</p>
    </div>
    <a href="/admin/projects/resumex" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Overview</a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
        <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
        <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<?php $supportedCurrencies = ['USD','EUR','GBP','INR','AED','SAR','BDT','PKR','NGN','BRL','MXN','CAD','AUD','JPY']; ?>

<!-- Create New Plan -->
<div class="plan-card" style="margin-bottom:32px;">
    <div class="plan-header">
        <h3 style="margin:0;font-size:1.1rem;"><i class="fas fa-plus-circle" style="color:var(--cyan);margin-right:8px;"></i>Create New Plan</h3>
        <button class="btn btn-secondary btn-sm" onclick="toggleNewPlanForm()"><i class="fas fa-chevron-down" id="newPlanChevron"></i></button>
    </div>
    <div id="new-plan-form" style="display:none;padding:20px 24px;background:var(--bg-secondary);">
        <form method="POST" action="/admin/projects/resumex/plans/create">
            <?= \Core\Security::csrfField() ?>
            <div class="grid grid-3" style="gap:12px;margin-bottom:12px;">
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Plan Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Pro" required>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Slug <span style="color:var(--red);">*</span></label>
                    <input type="text" name="slug" class="form-control" placeholder="e.g. pro" required>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Status</label>
                    <select name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Price</label>
                    <input type="number" name="price" class="form-control" value="0.00" step="0.01" min="0">
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Currency</label>
                    <select name="currency" class="form-control">
                        <?php foreach ($supportedCurrencies as $cur): ?>
                        <option value="<?= $cur ?>"><?= $cur ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Billing Cycle</label>
                    <select name="billing_cycle" class="form-control">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Max Resumes (0 = unlimited)</label>
                    <input type="number" name="max_resumes" class="form-control" value="5">
                </div>
                <div>
                    <label style="font-size:12px;color:var(--text-secondary);">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Create Plan</button>
        </form>
    </div>
</div>

<?php if (empty($plans)): ?>
    <div class="card">
        <p style="color:var(--text-secondary);text-align:center;padding:30px;">No subscription plans found. Create one above.</p>
    </div>
<?php endif; ?>

<?php
$featureLabels = [
    'unlimited_resumes'  => ['icon'=>'fas fa-infinity',         'label'=>'Unlimited Resume Creation'],
    'pdf_export'         => ['icon'=>'fas fa-file-pdf',         'label'=>'PDF Export'],
    'pdf_no_watermark'   => ['icon'=>'fas fa-tint-slash',       'label'=>'PDF Without Watermark'],
    'premium_templates'  => ['icon'=>'fas fa-star',             'label'=>'Premium / Designer Templates'],
    'ai_suggestions'     => ['icon'=>'fas fa-robot',            'label'=>'AI Writing Suggestions'],
    'linkedin_import'    => ['icon'=>'fab fa-linkedin',         'label'=>'LinkedIn Import'],
    'public_sharing'     => ['icon'=>'fas fa-share-alt',        'label'=>'Public Resume Sharing'],
    'custom_domain'      => ['icon'=>'fas fa-globe',            'label'=>'Custom Domain'],
    'analytics'          => ['icon'=>'fas fa-chart-line',       'label'=>'Resume Analytics'],
    'priority_support'   => ['icon'=>'fas fa-headset',          'label'=>'Priority Support'],
];
?>

<?php foreach ($plans as $plan): ?>
    <?php $features = json_decode($plan['features'] ?? '{}', true) ?: []; ?>
    <div class="plan-card">
        <div class="plan-header">
            <div>
                <h3 style="margin:0;font-size:1.2rem;"><?= View::e($plan['name']) ?></h3>
                <p style="margin:4px 0 0;font-size:13px;color:var(--text-secondary);">
                    Slug: <code><?= View::e($plan['slug']) ?></code> &bull;
                    <?php
                    $price = (float)($plan['price'] ?? 0);
                    $cur   = View::e($plan['currency'] ?? 'USD');
                    echo $price > 0 ? $cur . ' ' . number_format($price, 2) . ' / ' . View::e($plan['billing_cycle'] ?? '') : 'Free';
                    ?>
                    &bull; Max resumes: <?= $plan['max_resumes'] == 0 ? '&#8734; Unlimited' : (int)$plan['max_resumes'] ?>
                    &bull; <?= (int)($plan['subscriber_count'] ?? 0) ?> subscriber(s) &bull;
                    <span class="badge <?= $plan['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($plan['status']) ?></span>
                    <?php if (!empty($plan['is_default'])): ?>
                        <span class="badge badge-secondary ml-1">Default</span>
                    <?php endif; ?>
                </p>
            </div>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-secondary btn-sm" onclick="togglePlanForm(<?= $plan['id'] ?>)">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <?php if (empty($plan['is_default'])): ?>
                <form method="POST" action="/admin/projects/resumex/plans/<?= $plan['id'] ?>/delete" style="margin:0;"
                      onsubmit="return confirm('Delete plan <?= addslashes(View::e($plan['name'])) ?>? This cannot be undone.')">
                    <?= \Core\Security::csrfField() ?>
                    <button type="submit" class="btn btn-sm" style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);cursor:pointer;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit form (collapsed) -->
        <div id="plan-form-<?= $plan['id'] ?>" style="display:none;padding:16px 24px;background:var(--bg-secondary);border-bottom:1px solid var(--border-color);">
            <form method="POST" action="/admin/projects/resumex/plans/<?= $plan['id'] ?>/update">
                <?= \Core\Security::csrfField() ?>
                <div class="grid grid-3" style="gap:12px;margin-bottom:12px;">
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Plan Name</label>
                        <input type="text" name="name" class="form-control" value="<?= View::e($plan['name']) ?>">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Price</label>
                        <input type="number" name="price" class="form-control" value="<?= number_format((float)($plan['price'] ?? 0), 2, '.', '') ?>" step="0.01" min="0">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Currency</label>
                        <select name="currency" class="form-control">
                            <?php foreach ($supportedCurrencies as $cur): ?>
                            <option value="<?= $cur ?>" <?= ($plan['currency'] ?? 'USD') === $cur ? 'selected' : '' ?>><?= $cur ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Billing Cycle</label>
                        <select name="billing_cycle" class="form-control">
                            <?php foreach (['monthly','yearly','lifetime'] as $bc): ?>
                            <option value="<?= $bc ?>" <?= ($plan['billing_cycle'] ?? 'monthly') === $bc ? 'selected' : '' ?>><?= ucfirst($bc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Max Resumes (0 = unlimited)</label>
                        <input type="number" name="max_resumes" class="form-control" value="<?= (int)($plan['max_resumes'] ?? 5) ?>">
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= $plan['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $plan['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px;color:var(--text-secondary);">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($plan['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>

        <!-- Feature toggles -->
        <div class="plan-body">
            <h4 style="margin:0 0 12px;font-size:0.95rem;color:var(--text-secondary);">Feature Flags — click to toggle (saves instantly)</h4>
            <div class="grid grid-2" style="gap:0;">
                <?php foreach ($featureLabels as $key => $meta): ?>
                    <div class="feature-toggle">
                        <span style="font-size:14px;">
                            <i class="<?= $meta['icon'] ?>" style="width:18px;color:var(--cyan);"></i>
                            <?= $meta['label'] ?>
                        </span>
                        <label class="toggle-switch">
                            <input type="checkbox"
                                <?= !empty($features[$key]) ? 'checked' : '' ?>
                                onchange="togglePlanFeature(<?= $plan['id'] ?>,'<?= $key ?>',this)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="plan-toggle-feedback" id="planFeedback"></div>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
const _csrf = document.getElementById('global_csrf').value;

function toggleNewPlanForm() {
    const el = document.getElementById('new-plan-form');
    const ch = document.getElementById('newPlanChevron');
    const open = el.style.display === 'none';
    el.style.display = open ? 'block' : 'none';
    ch.className = open ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
}

function togglePlanForm(id) {
    const el = document.getElementById('plan-form-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function showFeedback(msg, ok) {
    const el = document.getElementById('planFeedback');
    el.textContent = msg;
    el.className = 'plan-toggle-feedback show ' + (ok ? 'ok' : 'err');
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.className = 'plan-toggle-feedback'; }, 2500);
}

function togglePlanFeature(planId, feature, checkbox) {
    const enabled = checkbox.checked;
    const fd = new FormData();
    fd.append('feature', feature);
    fd.append('enabled', enabled ? '1' : '0');
    fd.append('_csrf_token', _csrf);

    fetch('/admin/projects/resumex/plans/' + planId + '/toggle-feature', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showFeedback((enabled ? '\u2713 Enabled: ' : '\u2717 Disabled: ') + feature, true);
            } else {
                checkbox.checked = !enabled;
                showFeedback('Error: ' + (data.message || 'Unknown error'), false);
            }
        })
        .catch(() => {
            checkbox.checked = !enabled;
            showFeedback('Network error \u2014 please retry.', false);
        });
}
</script>
<?php View::endSection(); ?>
