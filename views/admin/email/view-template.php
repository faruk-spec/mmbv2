<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content"><h1><?= $title ?></h1><div class="card"><pre><code><?= htmlspecialchars($content) ?></code></pre></div></div>
<?php View::endSection(); ?>
