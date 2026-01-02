<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    
    <!-- Filters -->
    <div class="card mb-3">
        <h3>Filters</h3>
        <form method="get" class="form-inline">
            <div class="form-group">
                <label>Event Type:</label>
                <select name="event_type" class="form-control">
                    <option value="">All Types</option>
                    <?php foreach ($eventTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type['event_type']) ?>" 
                            <?= ($filters['event_type'] ?? '') === $type['event_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['event_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>From:</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="form-control">
            </div>
            <div class="form-group">
                <label>To:</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/analytics/events" class="btn btn-secondary">Clear</a>
        </form>
    </div>
    
    <!-- Events Table -->
    <div class="card">
        <h3>Events (<?= number_format($total) ?> total)</h3>
        <?php if (empty($events)): ?>
            <p>No analytics events found. Events will appear here as users interact with the platform.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Event Type</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>Country</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= date('Y-m-d H:i:s', strtotime($event['created_at'])) ?></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars($event['event_type']) ?></span></td>
                        <td><?= htmlspecialchars($event['user_name'] ?? 'Guest') ?></td>
                        <td><?= htmlspecialchars($event['ip_address'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($event['browser'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($event['country'] ?? 'N/A') ?></td>
                        <td><code style="font-size: 11px;"><?= htmlspecialchars(substr($event['event_data'] ?? '{}', 0, 100)) ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= Helpers::paginationUrl($i, $filters) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.form-inline {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: 600;
    margin-bottom: 5px;
}
.pagination {
    display: flex;
    gap: 5px;
    margin-top: 20px;
    justify-content: center;
}
.pagination a, .pagination .current {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}
.pagination .current {
    background: #007bff;
    color: white;
    border-color: #007bff;
}
.pagination a:hover {
    background: #f0f0f0;
}
.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}
.badge-info {
    background: #17a2b8;
    color: white;
}
</style>

<?php View::endSection(); ?>
