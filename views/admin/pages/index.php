<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
        <div>
            <h1>Pages</h1>
            <p style="color:var(--text-secondary);">Manage public pages</p>
        </div>
        <a href="/admin/pages/create" class="btn btn-primary"><i class="fas fa-plus"></i> New Page</a>
    </div>

    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($pages)): ?>
            <div style="text-align:center;padding:60px 20px;color:var(--text-secondary);">
                <i class="fas fa-file-alt" style="font-size:3rem;margin-bottom:20px;opacity:0.3;"></i>
                <p>No pages yet. <a href="/admin/pages/create">Create your first page</a>.</p>
            </div>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Navbar</th>
                    <th>Footer</th>
                    <th>Sort</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $p): ?>
                <tr>
                    <td><?= View::e($p['title']) ?></td>
                    <td><a href="/pages/<?= View::e($p['slug']) ?>" target="_blank" style="font-family:monospace;">/pages/<?= View::e($p['slug']) ?></a></td>
                    <td>
                        <span class="badge <?= $p['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>">
                            <?= View::e($p['status']) ?>
                        </span>
                    </td>
                    <td><?= $p['show_navbar'] ? '<i class="fas fa-check" style="color:var(--green);"></i>' : '<i class="fas fa-times" style="color:var(--red);"></i>' ?></td>
                    <td><?= $p['show_footer'] ? '<i class="fas fa-check" style="color:var(--green);"></i>' : '<i class="fas fa-times" style="color:var(--red);"></i>' ?></td>
                    <td><?= (int)$p['sort_order'] ?></td>
                    <td>
                        <a href="/admin/pages/<?= (int)$p['id'] ?>/edit" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="/admin/pages/<?= (int)$p['id'] ?>/toggle" style="display:inline;">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm <?= $p['status'] === 'published' ? 'btn-warning' : 'btn-success' ?>" title="Toggle Status">
                                <i class="fas fa-<?= $p['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i>
                            </button>
                        </form>
                        <form method="POST" action="/admin/pages/<?= (int)$p['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this page?')">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
