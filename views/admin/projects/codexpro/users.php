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
}

.badge-active {
    background: rgba(0, 255, 136, 0.2);
    color: var(--green);
}

.badge-inactive, .badge-expired {
    background: rgba(255, 107, 107, 0.2);
    color: var(--red);
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
        <h2 style="font-size: 1.5rem; margin: 0;">User Management</h2>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Projects</th>
                    <th>Last Activity</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">No users found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge badge-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span></td>
                            <td><?= $user['project_count'] ?></td>
                            <td><?= $user['last_project_update'] ? date('M d, Y', strtotime($user['last_project_update'])) : 'N/A' ?></td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
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