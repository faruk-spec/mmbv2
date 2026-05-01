<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success" style="margin-bottom:20px;"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="alert alert-error" style="margin-bottom:20px;"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-2" style="gap:24px;align-items:start;">
    <!-- Profile Info -->
    <div class="card">
        <h3 style="margin:0 0 20px;font-size:16px;"><i class="fas fa-user" style="color:var(--cyan);margin-right:8px;"></i> Personal Information</h3>
        <form method="POST" action="/admin/profile/update" enctype="multipart/form-data">
            <?= \Core\Security::csrfField() ?>

            <!-- Avatar -->
            <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;">
                <?php
                $avatarSrc = !empty($profile['avatar'])
                    ? (str_starts_with($profile['avatar'], '/') ? $profile['avatar'] : '/uploads/avatars/' . $profile['avatar'])
                    : '';
                ?>
                <div style="width:80px;height:80px;border-radius:50%;overflow:hidden;border:2px solid var(--border-color);flex-shrink:0;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;">
                    <?php if ($avatarSrc): ?>
                        <img src="<?= View::e($avatarSrc) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span style="font-size:32px;font-weight:700;color:var(--cyan);"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                        <i class="fas fa-upload"></i> Upload Photo
                        <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="previewAvatar(this)">
                    </label>
                    <p style="font-size:12px;color:var(--text-secondary);margin:6px 0 0;">JPG, PNG, GIF, WebP. Max 2MB.</p>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" value="<?= View::e($user['name'] ?? '') ?>" required minlength="2" maxlength="100">
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" value="<?= View::e($user['email'] ?? '') ?>" disabled style="opacity:.6;cursor:not-allowed;">
                <small style="color:var(--text-secondary);">Email cannot be changed here.</small>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="<?= View::e($profile['phone'] ?? '') ?>" maxlength="30">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-input" rows="3" maxlength="500" style="resize:vertical;"><?= View::e($profile['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="card" id="change-password">
        <h3 style="margin:0 0 20px;font-size:16px;"><i class="fas fa-lock" style="color:var(--magenta);margin-right:8px;"></i> Change Password</h3>
        <form method="POST" action="/admin/profile/change-password">
            <?= \Core\Security::csrfField() ?>

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-input" required autocomplete="current-password">
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" id="newPw" class="form-input" required minlength="8" autocomplete="new-password">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirmPw" class="form-input" required autocomplete="new-password">
                <small id="pwMatch" style="font-size:12px;display:none;"></small>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
        </form>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const avatarDiv = document.querySelector('[style*="border-radius:50%"]');
            if (avatarDiv) {
                avatarDiv.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('confirmPw').addEventListener('input', function() {
    const match = document.getElementById('pwMatch');
    if (this.value === document.getElementById('newPw').value) {
        match.style.display = 'block';
        match.style.color = 'var(--green)';
        match.textContent = '✓ Passwords match';
    } else {
        match.style.display = 'block';
        match.style.color = 'var(--red)';
        match.textContent = '✗ Passwords do not match';
    }
});
</script>

<?php View::endSection(); ?>
