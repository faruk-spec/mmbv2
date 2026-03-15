<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<style>
/* ── App Access Toggles ──────────────────────────────────────── */
.app-toggle-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;margin-top:10px;}
.app-toggle-item{position:relative;}
.app-toggle-item input[type=checkbox]{position:absolute;opacity:0;width:0;height:0;}
.app-toggle-label{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;
    padding:14px 8px;border-radius:12px;border:1px solid var(--border-color);
    background:var(--bg-secondary);cursor:pointer;transition:.15s;text-align:center;}
.app-toggle-label .app-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;transition:.15s;}
.app-toggle-label .app-name{font-size:11px;font-weight:600;color:var(--text-secondary);transition:.15s;line-height:1.2;}
.app-toggle-label .app-badge{font-size:9px;padding:2px 6px;border-radius:20px;background:rgba(0,240,255,.1);color:var(--cyan);display:none;font-weight:700;letter-spacing:.3px;}

/* Checked state */
.app-toggle-item input:checked + .app-toggle-label{border-color:var(--app-color,var(--cyan));background:rgba(var(--app-color-rgb,0,240,255),.08);}
.app-toggle-item input:checked + .app-toggle-label .app-name{color:var(--text-primary);}
.app-toggle-item input:checked + .app-toggle-label .app-badge{display:inline-block;}

/* Hover */
.app-toggle-label:hover{border-color:var(--app-color,var(--cyan));background:rgba(var(--app-color-rgb,0,240,255),.05);}

/* Unrestricted banner */
.app-unrestricted{display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(0,200,100,.08);border:1px solid rgba(0,200,100,.3);border-radius:8px;font-size:12px;color:var(--green);margin-bottom:10px;}
</style>

<div style="margin-bottom: 30px;">
    <a href="/admin/users" style="color: var(--text-secondary);">&larr; Back to Users</a>
</div>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<div class="grid grid-2" style="gap: 24px; align-items: start;">
    <!-- Main user form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-edit"></i> Edit User</h3>
        </div>
        <form method="POST" action="/admin/users/<?= $editUser['id'] ?>/edit">
            <?= \Core\Security::csrfField() ?>
            <!-- preserve role value without showing the dropdown -->
            <input type="hidden" name="role" value="<?= View::e($editUser['role']) ?>">

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" 
                       value="<?= View::e($editUser['name']) ?>" required>
                <?php if (View::hasError('name')): ?>
                    <div class="form-error"><?= View::error('name') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" 
                       value="<?= View::e($editUser['email']) ?>" required>
                <?php if (View::hasError('email')): ?>
                    <div class="form-error"><?= View::error('email') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="Leave blank to keep current password">
                <small style="color: var(--text-secondary);">Leave blank to keep current password</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="status">Account Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="active"   <?= $editUser['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $editUser['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="banned"   <?= $editUser['status'] === 'banned'   ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>

            <!-- ── App Access ─────────────────────────────────────────────── -->
            <div class="form-group" style="margin-top:20px;">
                <label class="form-label" style="margin-bottom:6px;">
                    <i class="fas fa-th-large" style="color:var(--cyan);margin-right:5px;"></i> App Access
                </label>
                <small style="color:var(--text-secondary);display:block;margin-bottom:10px;">
                    Select which apps this user can access. Leave all unticked to grant access to <strong>all apps</strong>.
                </small>

                <?php
                // $userApps: null = unrestricted, [] = all locked, ['qr','whatsapp'] = partial
                $userApps = $userApps ?? null;
                $isUnrestricted = ($userApps === null);
                ?>

                <?php if ($isUnrestricted): ?>
                    <div class="app-unrestricted">
                        <i class="fas fa-globe"></i>
                        <span>Currently unrestricted — user has access to all apps. Toggle apps below to restrict.</span>
                    </div>
                <?php endif; ?>

                <div class="app-toggle-grid">
                    <?php
                    $appIconColors = [
                        'qr'       => ['color' => '#00f0ff', 'rgb' => '0,240,255'],
                        'whatsapp' => ['color' => '#25d366', 'rgb' => '37,211,102'],
                        'proshare' => ['color' => '#ff2ec4', 'rgb' => '255,46,196'],
                        'codexpro' => ['color' => '#9b59b6', 'rgb' => '155,89,182'],
                        'imgtxt'   => ['color' => '#f39c12', 'rgb' => '243,156,18'],
                        'convertx' => ['color' => '#3498db', 'rgb' => '52,152,219'],
                        'billx'    => ['color' => '#2ecc71', 'rgb' => '46,204,113'],
                        'resumex'  => ['color' => '#e67e22', 'rgb' => '230,126,34'],
                        'devzone'  => ['color' => '#e74c3c', 'rgb' => '231,76,60'],
                    ];
                    foreach ($apps as $slug => $app):
                        $isChecked = $isUnrestricted ? false : in_array($slug, $userApps ?? [], true);
                        $clr = $appIconColors[$slug] ?? ['color'=>'#00f0ff','rgb'=>'0,240,255'];
                    ?>
                    <div class="app-toggle-item">
                        <input type="checkbox"
                               name="app_access[]"
                               id="app_<?= $slug ?>"
                               value="<?= $slug ?>"
                               <?= $isChecked ? 'checked' : '' ?>>
                        <label for="app_<?= $slug ?>" class="app-toggle-label"
                               style="--app-color:<?= $clr['color'] ?>;--app-color-rgb:<?= $clr['rgb'] ?>;">
                            <div class="app-icon" style="background:<?= $clr['color'] ?>1a;color:<?= $clr['color'] ?>;">
                                <i class="<?= $app['icon'] ?>"></i>
                            </div>
                            <span class="app-name"><?= View::e($app['label']) ?></span>
                            <span class="app-badge">✓ ON</span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <small style="color:var(--text-secondary);display:block;margin-top:8px;font-size:11px;">
                    <i class="fas fa-info-circle"></i>
                    Tip: untick all apps to restore unrestricted access.
                </small>
            </div>
            <!-- ── /App Access ──────────────────────────────────────────── -->
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="/admin/users" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Right panel: QR Plan, Feature Overrides, Danger Zone -->
    <div>
        <!-- QR Subscription Plan -->
        <?php if (!empty($qrPlans)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-qrcode"></i> QR Subscription Plan</h3>
            </div>
            <?php if ($userQrPlan): ?>
                <p style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">
                    Current plan: <strong style="color: var(--cyan);"><?= View::e($userQrPlan['plan_name']) ?></strong>
                </p>
            <?php else: ?>
                <p style="margin-bottom: 12px; font-size: 14px; color: var(--text-secondary);">
                    No QR plan assigned.
                </p>
            <?php endif; ?>
            <form method="POST" action="/admin/qr/roles/assign-plan">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label style="display:block;margin-bottom:5px;font-size:13px;color:var(--text-secondary);">Assign Plan</label>
                        <select name="plan_id" class="form-input">
                            <option value="">— Select plan —</option>
                            <?php foreach ($qrPlans as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($userQrPlan && $userQrPlan['plan_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= View::e($p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">
                        <i class="fas fa-check"></i> Assign
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Link to feature overrides -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-toggle-on"></i> Feature Overrides</h3>
            </div>
            <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 16px;">
                Set custom per-feature access for this user, overriding their role and plan defaults.
            </p>
            <a href="/admin/qr/roles" class="btn btn-secondary btn-sm">
                <i class="fas fa-users-cog"></i> Manage Feature Overrides
            </a>
        </div>

        <!-- Danger zone -->
        <div class="card mt-3" style="border-color: var(--red);">
            <div class="card-header">
                <h3 class="card-title" style="color: var(--red);"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
            </div>
            <form method="POST" action="/admin/users/<?= $editUser['id'] ?>/delete"
                  onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                <?= \Core\Security::csrfField() ?>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete User
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// If ALL checkboxes are unchecked → send a signal meaning "unrestricted"
// We handle this server-side: no app_access[] keys = null (unrestricted)
// The user sees a visual cue via the green banner (only shown when currently null).
document.querySelector('form').addEventListener('submit', function() {
    // nothing special needed - unchecked = omitted = treated as unrestricted server-side
});
</script>

<?php View::endSection(); ?>


