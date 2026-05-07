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
                    <th>Send From (Mail Config)</th>
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
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($tpl['subject']) ?></td>
                    <td>
                        <?php
                        $selectedProviderId = $tpl['mail_provider_config_id'] ?? null;
                        $selectedProviderName = '— default —';
                        foreach ($providers as $prov) {
                            if ((int)$prov['id'] === (int)$selectedProviderId) {
                                $selectedProviderName = htmlspecialchars($prov['name']);
                                break;
                            }
                        }
                        ?>
                        <form method="POST" action="/admin/mail/templates/set-provider" style="display:inline-flex;align-items:center;gap:6px;">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="id" value="<?= $tpl['id'] ?>">
                            <select name="mail_provider_config_id"
                                    onchange="this.form.submit()"
                                    style="padding:4px 8px;border-radius:6px;border:1px solid var(--border-color);background:var(--bg-secondary);color:var(--text-primary);font-size:12px;max-width:160px;">
                                <option value="">— default —</option>
                                <?php foreach ($providers as $prov): ?>
                                <option value="<?= (int)$prov['id'] ?>" <?= (int)$prov['id'] === (int)$selectedProviderId ? 'selected' : '' ?>>
                                    <?= View::e($prov['name']) ?><?= $prov['is_active'] ? '' : ' (inactive)' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
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
                        <button class="btn btn-sm btn-secondary" style="margin-left:4px;"
                                onclick="sendTestTemplate(<?= $tpl['id'] ?>, '<?= View::e($tpl['slug']) ?>')">
                            <i class="fas fa-paper-plane"></i> Test
                        </button>
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

function sendTestTemplate(id, slug) {
    const to = prompt('Send test email for "' + slug + '" to (enter email address):');
    if (!to || !to.includes('@')) return;

    fetch('/admin/mail/templates/send-test', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrfToken},
        body: '_csrf_token=' + encodeURIComponent(csrfToken) + '&id=' + id + '&to=' + encodeURIComponent(to)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('✅ ' + d.message);
        } else {
            alert('❌ ' + d.message);
        }
    })
    .catch(() => alert('❌ Request failed. Check server logs.'));
}
</script>

<?php View::endSection(); ?>
