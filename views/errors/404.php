<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="text-align: center; padding: 80px 0;">
    <div style="font-size: 8rem; font-weight: 700; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        404
    </div>
    <h1 style="margin-bottom: 20px;">Page Not Found</h1>
    <p style="color: var(--text-secondary); margin-bottom: 30px; max-width: 400px; margin-left: auto; margin-right: auto;">
        The page you're looking for doesn't exist or has been moved.
    </p>
    <a href="/" class="btn btn-primary">Go Home</a>
</div>
<?php View::endSection(); ?>
