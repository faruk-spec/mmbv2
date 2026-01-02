<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content"><h1><?= $title ?></h1><div class="card"><h2>Server Information</h2><table class="table"><tbody><?php foreach ($serverInfo as $key => $value): ?><tr><td><strong><?= ucwords(str_replace("_", " ", $key)) ?></strong></td><td><?= htmlspecialchars($value) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php View::endSection(); ?>
