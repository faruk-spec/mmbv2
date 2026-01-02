<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    <div class="stats-grid">
        <div class="stat-card <?= $serverStatus == "online" ? "success" : "danger" ?>">
            <h3>Server Status</h3>
            <p class="stat-value"><?= ucfirst($serverStatus) ?></p>
        </div>
        <div class="stat-card">
            <h3>Host</h3>
            <p class="stat-value"><?= htmlspecialchars($host) ?></p>
        </div>
        <div class="stat-card">
            <h3>Port</h3>
            <p class="stat-value"><?= $port ?></p>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
