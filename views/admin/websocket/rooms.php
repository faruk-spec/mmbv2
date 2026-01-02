<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    <p>Active rooms will appear here when WebSocket server is running.</p>
</div>
<?php View::endSection(); ?>
