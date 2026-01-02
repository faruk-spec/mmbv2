<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content"><h1><?= $title ?></h1><div class="card"><table class="table"><thead><tr><th>Name</th><th>Size</th><th>Modified</th><th>Actions</th></tr></thead><tbody><?php foreach ($templates as $tpl): ?><tr><td><?= $tpl["name"] ?></td><td><?= number_format($tpl["size"]) ?> bytes</td><td><?= date("Y-m-d H:i", $tpl["modified"]) ?></td><td><a href="/admin/email/templates/view?template=<?= $tpl["name"] ?>" class="btn btn-sm btn-primary">View</a></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php View::endSection(); ?>
