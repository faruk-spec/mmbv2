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
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> Settings saved.
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid #ff6b6b;color:#ff6b6b;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e($error) ?>
</div>
<?php endif; ?>

<div class="grid grid-2" style="gap:20px;">

    <!-- Project Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Project Status</h3>
        </div>
        <div style="padding:20px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <span style="width:12px;height:12px;background:var(--green);border-radius:50%;display:inline-block;"></span>
                <span>BillX is <strong>active</strong> and accessible at <a href="/projects/billx">/projects/billx</a></span>
            </div>
            <p style="color:var(--text-secondary);font-size:13px;">
                To enable/disable BillX, use the
                <a href="/admin/projects/billx">Project Management</a> page.
            </p>
        </div>
    </div>

    <!-- Supported Bill Types -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tags"></i> Supported Bill Types (18)</h3>
        </div>
        <div style="padding:16px;">
            <?php
            $types = [
                'Fuel Bill','Driver Salary','Daily Helper Bill','Rent Receipt',
                'Book Invoice','Internet Invoice','Restaurant Bill','LTA Receipt',
                'E-Com Invoice','General Bill','Recharge Receipt','Medical Bill',
                'Stationary Bill','Cab & Travel Bill','Mart Bill','Gym Bill',
                'Hotel Bill','News Paper Bill',
            ];
            foreach ($types as $t):
            ?>
            <span class="badge badge-info" style="margin:3px;"><?= htmlspecialchars($t) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Save form (placeholder for future settings) -->
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-save"></i> Save Settings</h3>
    </div>
    <form method="POST" action="/admin/projects/billx/settings" style="padding:20px;">
        <?= \Core\Security::csrfField() ?>
        <p style="color:var(--text-secondary);margin-bottom:16px;">
            BillX is an on-the-go bill generator with no inventory management.
            All bills are stored in the main database under the <code>billx_bills</code> table.
        </p>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
    </form>
</div>

<?php View::endSection(); ?>
