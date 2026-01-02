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

.badge {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    background: rgba(0, 240, 255, 0.2);
    color: var(--cyan);
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
        <h2 style="font-size: 1.5rem; margin: 0;">Template Management</h2>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Language</th><th>Creator</th><th>Uses</th><th>Created</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No templates found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $template): ?>
                        <tr>
                            <td>#<?= $template['id'] ?></td>
                            <td><?= htmlspecialchars($template['name'] ?? 'Unnamed') ?></td>
                            <td><span class="badge"><?= htmlspecialchars($template['language'] ?? 'N/A') ?></span></td>
                            <td><?= htmlspecialchars($template['creator_name'] ?? 'Unknown') ?></td>
                            <td><?= $template['usage_count'] ?? 0 ?></td>
                            <td><?= date('M d, Y', strtotime($template['created_at'])) ?></td>
                            <td><a href="/admin/projects/codexpro/templates/<?= $template['id'] ?>" class="btn-view">View</a></td>
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
