<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<h1 style="margin-bottom: 30px;">Profile Settings</h1>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column: span 2;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Personal Information</h3>
            </div>
            
            <form method="POST" action="/profile" enctype="multipart/form-data">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           value="<?= View::e($user['name']) ?>" required>
                    <?php if (View::hasError('name')): ?>
                        <div class="form-error"><?= View::error('name') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" class="form-input" 
                           value="<?= View::e($user['email']) ?>" disabled>
                    <small style="color: var(--text-secondary);">Email cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" 
                           value="<?= View::e($user['phone'] ?? '') ?>" placeholder="+1 234 567 8900">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="form-input" rows="4" 
                              placeholder="Tell us about yourself..."><?= View::e($user['bio'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="avatar">Profile Picture</label>
                    <input type="file" id="avatar" name="avatar" class="form-input" accept="image/*">
                    <small style="color: var(--text-secondary);">JPG, PNG, GIF up to 2MB</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
    
    <div>
        <div class="card" style="text-align: center;">
            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 600;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            
            <h3><?= View::e($user['name']) ?></h3>
            <p style="color: var(--text-secondary); margin-bottom: 15px;"><?= View::e($user['email']) ?></p>
            
            <span class="badge badge-success"><?= ucfirst($user['role']) ?></span>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <div style="font-size: 13px; color: var(--text-secondary);">Member since</div>
                <div><?= Helpers::formatDate($user['created_at']) ?></div>
            </div>
        </div>
        
        <div class="card mt-2">
            <h4 style="margin-bottom: 15px;">Quick Links</h4>
            <a href="/security" style="display: block; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                Security Settings
            </a>
            <a href="/activity" style="display: block; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                Activity Log
            </a>
            <a href="/2fa/setup" style="display: block; padding: 10px 0;">
                Two-Factor Authentication
            </a>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
