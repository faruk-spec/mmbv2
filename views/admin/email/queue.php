<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    <div class="stats-grid">
        <div class="stat-card"><h3>Pending</h3><p class="stat-value"><?= $stats["pending"] ?? 0 ?></p></div>
        <div class="stat-card"><h3>Sent</h3><p class="stat-value"><?= $stats["sent"] ?? 0 ?></p></div>
        <div class="stat-card"><h3>Failed</h3><p class="stat-value"><?= $stats["failed"] ?? 0 ?></p></div>
    </div>
    <div class="card">
        <button onclick="processQueue()" class="btn btn-primary">Process Queue</button>
        <table class="table">
            <thead><tr><th>To</th><th>Subject</th><th>Status</th><th>Created</th><th>Attempts</th></tr></thead>
            <tbody>
                <?php foreach ($emails as $email): ?>
                <tr>
                    <td><?= htmlspecialchars($email["recipient"]) ?></td>
                    <td><?= htmlspecialchars($email["subject"]) ?></td>
                    <td><span class="badge badge-<?= $email["status"] == "sent" ? "success" : ($email["status"] == "failed" ? "danger" : "warning") ?>"><?= $email["status"] ?></span></td>
                    <td><?= $email["created_at"] ?></td>
                    <td><?= $email["attempts"] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php View::endSection(); ?>
