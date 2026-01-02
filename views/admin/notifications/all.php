<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content"><h1><?= $title ?></h1><div class="stats-grid"><div class="stat-card"><h3>Total</h3><p class="stat-value"><?= $stats["total"] ?></p></div><div class="stat-card"><h3>Unread</h3><p class="stat-value"><?= $stats["unread"] ?></p></div><div class="stat-card"><h3>Today</h3><p class="stat-value"><?= $stats["today"] ?></p></div></div><div class="card"><table class="table"><thead><tr><th>Time</th><th>Type</th><th>User</th><th>Message</th><th>Status</th></tr></thead><tbody><?php foreach ($notifications as $notif): ?><tr><td><?= $notif["created_at"] ?></td><td><?= $notif["type"] ?></td><td><?= htmlspecialchars($notif["user_name"] ?? "N/A") ?></td><td><?= htmlspecialchars(substr($notif["message"], 0, 50)) ?></td><td><?= $notif["is_read"] ? "Read" : "Unread" ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php View::endSection(); ?>
