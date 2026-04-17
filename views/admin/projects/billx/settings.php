<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-cog" style="color:#f59e0b;"></i> BillX — Settings</h1>
        <p style="color:var(--text-secondary);">Configure BillX bill generator settings</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/projects/billx" class="btn btn-secondary"><i class="fas fa-tachometer-alt"></i> Overview</a>
        <a href="/admin/projects/billx/bills" class="btn btn-secondary"><i class="fas fa-list"></i> All Bills</a>
    </div>
</div>

<?php if (!empty($saved)): ?>
<div style="background:rgba(34,197,94,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> Settings saved successfully.
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid #ef4444;color:#ef4444;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e($error) ?>
</div>
<?php endif; ?>

<?php
$s = $settings ?? [];
$allBillTypes = [
    'fuel'       => 'Fuel Bill',
    'driver'     => 'Driver Salary',
    'helper'     => 'Daily Helper Bill',
    'rent'       => 'Rent Receipt',
    'book'       => 'Book Invoice',
    'internet'   => 'Internet Invoice',
    'restaurant' => 'Restaurant Bill',
    'lta'        => 'LTA Receipt',
    'ecom'       => 'E-Com Invoice',
    'general'    => 'General Bill',
    'recharge'   => 'Recharge Receipt',
    'medical'    => 'Medical Bill',
    'stationary' => 'Stationary Bill',
    'cab'        => 'Cab & Travel Bill',
    'mart'       => 'Mart Bill',
    'gym'        => 'Gym Bill',
    'hotel'      => 'Hotel Bill',
    'newspaper'  => 'News Paper Bill',
];
$allowedTypes = $s['allowed_bill_types'] ?? [];
// Empty allowed_types means "all allowed"
$allAllowed   = empty($allowedTypes);
?>

<form method="POST" action="/admin/projects/billx/settings">
    <?= \Core\Security::csrfField() ?>

    <div class="grid grid-2" style="gap:20px;margin-bottom:20px;">

        <!-- General Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sliders-h"></i> General Settings</h3>
            </div>
            <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="max_bills_per_user">Max Bills Per User</label>
                    <input type="number" id="max_bills_per_user" name="max_bills_per_user"
                           class="form-control" min="1" max="10000"
                           value="<?= (int)($s['max_bills_per_user'] ?? 500) ?>">
                    <small style="color:var(--text-secondary);font-size:12px;">
                        Maximum number of bills a single user can generate (1–10000).
                    </small>
                </div>

                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="default_currency">Default Currency</label>
                    <select id="default_currency" name="default_currency" class="form-control">
                        <?php foreach (['INR' => 'INR ₹', 'USD' => 'USD $', 'EUR' => 'EUR €', 'GBP' => 'GBP £'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($s['default_currency'] ?? 'INR') === $k ? 'selected' : '' ?>>
                            <?= $v ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin:0;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="require_policy_agree" value="1"
                               <?= !empty($s['require_policy_agree']) ? 'checked' : '' ?>
                               style="width:16px;height:16px;accent-color:var(--amber);">
                        <span class="form-label" style="margin:0;">Require Policy Agreement on Generate</span>
                    </label>
                    <small style="color:var(--text-secondary);font-size:12px;margin-top:4px;display:block;">
                        Users must check the policy box before generating a bill.
                    </small>
                </div>

            </div>
        </div>

        <!-- Project Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Project Status</h3>
            </div>
            <div style="padding:20px;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <span style="width:12px;height:12px;background:var(--green);border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                    <span>BillX is <strong>active</strong> and accessible at
                        <a href="/projects/billx" target="_blank">/projects/billx</a>
                    </span>
                </div>
                <p style="color:var(--text-secondary);font-size:13px;margin-bottom:12px;">
                    All bills are stored in the main database under the <code>billx_bills</code> table.
                    Settings are stored in the <code>billx_settings</code> table (auto-created).
                </p>
                <p style="color:var(--text-secondary);font-size:13px;">
                    To enable/disable BillX for users, manage their project access from
                    <a href="/admin/projects">Project Management</a>.
                </p>
            </div>
        </div>

    </div>

    <!-- Allowed Bill Types -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tags"></i> Allowed Bill Types</h3>
        </div>
        <div style="padding:16px 20px;">
            <p style="color:var(--text-secondary);font-size:13px;margin-bottom:12px;">
                Select which bill types users are allowed to generate. If none are checked, all types are allowed.
            </p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
                <?php foreach ($allBillTypes as $key => $label): ?>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 12px;
                              background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;
                              transition:border-color .15s;">
                    <input type="checkbox" name="allowed_types[<?= htmlspecialchars($key) ?>]" value="1"
                           style="width:15px;height:15px;accent-color:var(--amber);"
                           <?= ($allAllowed || in_array($key, $allowedTypes, true)) ? 'checked' : '' ?>>
                    <span style="font-size:13px;"><?= htmlspecialchars($label) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </div>

</form>

<?php View::endSection(); ?>
