<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content"><h1><?= $title ?></h1><div class="card"><h2>CSS Files</h2><table class="table"><thead><tr><th>Name</th><th>Size</th><th>Actions</th></tr></thead><tbody><?php foreach ($assets["css"] as $file): ?><tr><td><?= $file["name"] ?></td><td><?= $file["sizeFormatted"] ?></td><td><button onclick="minifyAsset('<?= $file["name"] ?>', 'css')" class="btn btn-sm btn-primary">Minify</button></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php View::endSection(); ?>
