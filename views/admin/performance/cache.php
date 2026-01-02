<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content"><h1><?= $title ?></h1><div class="stats-grid"><div class="stat-card"><h3>Cache Size</h3><p class="stat-value"><?= $cacheSizeFormatted ?></p></div><div class="stat-card"><h3>Files</h3><p class="stat-value"><?= $fileCount ?></p></div></div><div class="card"><button onclick="clearCache()" class="btn btn-danger">Clear All Cache</button></div></div>
<?php View::endSection(); ?>
