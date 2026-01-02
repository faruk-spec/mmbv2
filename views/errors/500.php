<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="text-align: center; padding: 80px 0;">
    <div style="font-size: 8rem; font-weight: 700; background: linear-gradient(135deg, var(--red), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        500
    </div>
    <h1 style="margin-bottom: 20px;">Server Error</h1>
    <p style="color: var(--text-secondary); margin-bottom: 30px; max-width: 400px; margin-left: auto; margin-right: auto;">
        Something went wrong on our end. Please try again later.
    </p>
    <a href="/" class="btn btn-primary">Go Home</a>
</div>
<?php View::endSection(); ?>
