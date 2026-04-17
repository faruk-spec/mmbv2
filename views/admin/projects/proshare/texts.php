<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th {
    background: var(--bg-secondary);
    padding: 12px;
    text-align: left;
    color: var(--cyan);
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
}

table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
}

table tr:hover {
    background: rgba(0, 240, 255, 0.05);
}

.btn-view {
    padding: 6px 12px;
    background: var(--purple);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    transition: var(--transition);
    display: inline-block;
}

.btn-view:hover {
    opacity: 0.8;
}

.pagination {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}

.page-link {
    padding: 8px 16px;
    background: var(--bg-card);
    color: var(--cyan);
    text-decoration: none;
    border-radius: 6px;
    transition: var(--transition);
    border: 1px solid var(--border-color);
}

.page-link:hover {
    background: rgba(0, 240, 255, 0.1);
    border-color: var(--cyan);
}

.page-link.active {
    background: var(--cyan);
    color: var(--bg-primary);
    border-color: var(--cyan);
}

.empty-state {
    text-align: center;
    padding: 40px !important;
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    table {
        font-size: 14px;
    }
    
    table th, table td {
        padding: 8px;
    }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); margin-bottom: 20px;">
        <h2 style="font-size: 1.5rem; margin: 0;">Text Share Management</h2>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Views</th>
                    <th>Created</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($texts)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No text shares found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($texts as $text): ?>
                        <tr>
                            <td>#<?= $text['id'] ?></td>
                            <td><?= htmlspecialchars(substr($text['title'] ?? $text['content'] ?? 'Untitled', 0, 50)) ?></td>
                            <td><?= htmlspecialchars($text['user_name'] ?? 'Anonymous') ?></td>
                            <td><?= $text['view_count'] ?? 0 ?></td>
                            <td><?= date('M d, Y H:i', strtotime($text['created_at'])) ?></td>
                            <td><?php if ($text['expires_at']): ?><?= date('M d, Y', strtotime($text['expires_at'])) ?><?php else: ?>Never<?php endif; ?></td>
                            <td><a href="/text/<?= $text['share_code'] ?? $text['id'] ?>" class="btn-view" target="_blank">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-link <?= $i == $currentPage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
