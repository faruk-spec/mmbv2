<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="text-align: center; padding: 80px 0;">
    <div style="font-size: 8rem; font-weight: 700; background: linear-gradient(135deg, var(--orange), var(--red)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        403
    </div>
    <h1 style="margin-bottom: 20px;">Access Denied</h1>
    <p style="color: var(--text-secondary); margin-bottom: 30px; max-width: 400px; margin-left: auto; margin-right: auto;">
        You don't have permission to access this page.
    </p>
    <a href="/" class="btn btn-primary">Go Home</a>
</div>
<?php View::endSection(); ?>
