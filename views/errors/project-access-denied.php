<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="text-align: center; padding: 80px 20px;">
    <div style="font-size: 6rem; font-weight: 700; background: linear-gradient(135deg, var(--orange), var(--red)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        🔒
    </div>
    <h1 style="margin-bottom: 20px; font-size: 2rem;">Project Access Denied</h1>
    <p style="color: var(--text-secondary); margin-bottom: 15px; max-width: 500px; margin-left: auto; margin-right: auto; font-size: 1rem;">
        You don't have permission to access the <strong><?= htmlspecialchars($project ?? 'requested') ?></strong> project.
    </p>
    <?php if (isset($user) && $user): ?>
    <p style="color: var(--text-muted); margin-bottom: 30px; font-size: 0.875rem;">
        Logged in as: <strong><?= htmlspecialchars($user['email'] ?? '') ?></strong>
    </p>
    <?php endif; ?>
    
    <div style="margin-top: 40px;">
        <a href="/dashboard" class="btn btn-primary" style="margin-right: 10px;">
            <i class="bi bi-house"></i> Go to Dashboard
        </a>
        <a href="/settings" class="btn btn-secondary">
            <i class="bi bi-gear"></i> Check Settings
        </a>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: var(--bg-secondary); border-radius: 8px; max-width: 600px; margin-left: auto; margin-right: auto; text-align: left;">
        <h3 style="font-size: 1rem; margin-bottom: 10px;">
            <i class="bi bi-info-circle"></i> Why am I seeing this?
        </h3>
        <ul style="color: var(--text-secondary); font-size: 0.875rem; line-height: 1.6;">
            <li>The project may be disabled by an administrator</li>
            <li>Your account may not have the required permissions</li>
            <li>The project may be in maintenance mode</li>
        </ul>
        <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 15px;">
            If you believe this is an error, please contact your system administrator.
        </p>
    </div>
</div>
<?php View::endSection(); ?>
