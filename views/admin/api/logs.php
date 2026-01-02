<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1><?= $title ?></h1>
    </div>
    
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Key</th>
                    <th>User</th>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Status</th>
                    <th>Response Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= $log["created_at"] ?></td>
                    <td><?= htmlspecialchars($log["key_name"] ?? "Unknown") ?></td>
                    <td><?= htmlspecialchars($log["user_name"] ?? "N/A") ?></td>
                    <td><span class="badge badge-info"><?= $log["method"] ?></span></td>
                    <td><code><?= htmlspecialchars($log["endpoint"]) ?></code></td>
                    <td>
                        <?php
                        $statusClass = $log["status_code"] < 300 ? "success" : ($log["status_code"] < 400 ? "warning" : "danger");
                        ?>
                        <span class="badge badge-<?= $statusClass ?>"><?= $log["status_code"] ?></span>
                    </td>
                    <td><?= $log["response_time"] ?> ms</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? "active" : "" ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
