<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;">Notification Templates</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Edit HTML email templates for system notifications</p>
    </div>
    <a href="/admin/mail/config" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Mail Config</a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
<div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Slug</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Variables</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $tpl): ?>
                <tr>
                    <td><code><?= View::e($tpl['slug']) ?></code></td>
                    <td><?= View::e($tpl['name']) ?></td>
                    <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($tpl['subject']) ?></td>
                    <td>
                        <?php $vars = json_decode($tpl['variables'] ?? '[]', true) ?? []; ?>
                        <?php foreach ($vars as $v): ?>
                        <code style="font-size:11px;margin-right:4px;">{{<?= View::e($v) ?>}}</code>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm <?= $tpl['is_enabled'] ? 'btn-success' : 'btn-secondary' ?>"
                                onclick="toggleTemplate(<?= $tpl['id'] ?>, this)">
                            <?= $tpl['is_enabled'] ? '<i class="fas fa-check"></i> Enabled' : 'Disabled' ?>
                        </button>
                    </td>
                    <td>
                        <a href="/admin/mail/templates/edit?id=<?= $tpl['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function toggleTemplate(id, btn) {
    fetch('/admin/mail/templates/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken},
        body: '_csrf_token=' + encodeURIComponent(csrfToken) + '&id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) {
            btn.className = d.enabled ? 'btn btn-sm btn-success' : 'btn btn-sm btn-secondary';
            btn.innerHTML = d.enabled ? '<i class="fas fa-check"></i> Enabled' : 'Disabled';
        }
    });
}
</script>

<?php View::endSection(); ?>
