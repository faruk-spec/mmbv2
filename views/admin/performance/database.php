<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content"><h1><?= $title ?></h1><div class="stats-grid"><div class="stat-card"><h3>Total Size</h3><p class="stat-value"><?= $totalSizeFormatted ?></p></div><div class="stat-card"><h3>Total Rows</h3><p class="stat-value"><?= number_format($totalRows) ?></p></div></div><div class="card"><table class="table"><thead><tr><th>Table</th><th>Rows</th><th>Size</th><th>Actions</th></tr></thead><tbody><?php foreach ($tables as $table): ?><tr><td><?= $table["name"] ?></td><td><?= number_format($table["rows"]) ?></td><td><?= $table["total_size_formatted"] ?></td><td><button onclick="optimizeTable('<?= $table["name"] ?>')" class="btn btn-sm btn-primary">Optimize</button></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php View::endSection(); ?>
