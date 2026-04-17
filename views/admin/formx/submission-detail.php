<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Breadcrumb -->
<div style="margin-bottom:16px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
    <a href="/admin/formx" style="color:var(--text-secondary);text-decoration:none;font-size:.875rem;" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
        <i class="fas fa-arrow-left"></i> Forms
    </a>
    <span style="color:var(--border-color);">›</span>
    <a href="/admin/formx/<?= $form['id'] ?>/submissions" style="color:var(--text-secondary);text-decoration:none;font-size:.875rem;">Submissions</a>
    <span style="color:var(--border-color);">›</span>
    <span style="color:var(--text-primary);font-size:.875rem;">Submission #<?= $submission['id'] ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

    <!-- Main content -->
    <div class="card" style="padding:24px;">
        <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">
            <i class="fas fa-file-alt" style="color:var(--cyan);margin-right:8px;"></i>
            Submission #<?= $submission['id'] ?>
        </h1>

        <?php
        $fieldDefs = [];
        foreach ($form['fields'] as $f) {
            if (!empty($f['name'])) $fieldDefs[$f['name']] = $f;
        }
        $data = $submission['data'];
        ?>

        <?php if (empty($data)): ?>
        <p style="color:var(--text-secondary);">No data found in this submission.</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:16px;">
            <?php foreach ($data as $key => $value): ?>
            <?php
            $label = isset($fieldDefs[$key]) ? ($fieldDefs[$key]['label'] ?? $key) : $key;
            $type  = isset($fieldDefs[$key]) ? ($fieldDefs[$key]['type'] ?? 'text') : 'text';
            if (is_array($value)) $value = implode(', ', $value);
            ?>
            <div style="border-bottom:1px solid var(--border-color);padding-bottom:16px;">
                <div style="font-size:.8rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">
                    <?= View::e($label) ?>
                    <span style="font-size:.72rem;padding:2px 6px;background:var(--bg-secondary);border-radius:4px;margin-left:6px;font-weight:400;text-transform:none;"><?= View::e($type) ?></span>
                </div>
                <div style="font-size:.95rem;color:var(--text-primary);white-space:pre-wrap;"><?= View::e($value !== '' ? $value : '—') ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar meta -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="padding:20px;">
            <h3 style="font-size:.85rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Meta</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <div>
                    <div style="font-size:.78rem;color:var(--text-secondary);margin-bottom:2px;">Submitted</div>
                    <div style="font-size:.875rem;color:var(--text-primary);"><?= date('M d, Y H:i:s', strtotime($submission['created_at'])) ?></div>
                </div>
                <div>
                    <div style="font-size:.78rem;color:var(--text-secondary);margin-bottom:2px;">IP Address</div>
                    <div style="font-size:.875rem;color:var(--text-primary);font-family:monospace;"><?= View::e($submission['ip_address'] ?? '—') ?></div>
                </div>
                <div>
                    <div style="font-size:.78rem;color:var(--text-secondary);margin-bottom:2px;">User Agent</div>
                    <div style="font-size:.78rem;color:var(--text-secondary);word-break:break-all;"><?= View::e(mb_substr($submission['user_agent'] ?? '—', 0, 120)) ?></div>
                </div>
                <div>
                    <div style="font-size:.78rem;color:var(--text-secondary);margin-bottom:2px;">Form</div>
                    <a href="/admin/formx/<?= $form['id'] ?>/edit" style="font-size:.875rem;color:var(--cyan);text-decoration:none;"><?= View::e($form['title']) ?></a>
                </div>
            </div>
        </div>

        <div class="card" style="padding:20px;">
            <h3 style="font-size:.85rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">Actions</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="/admin/formx/<?= $form['id'] ?>/submissions" style="padding:8px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);text-decoration:none;font-size:.875rem;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-arrow-left" style="color:var(--text-secondary);"></i> All Submissions
                </a>
                <form method="POST" action="/admin/formx/<?= $form['id'] ?>/submissions/<?= $submission['id'] ?>/delete" onsubmit="return confirm('Delete this submission?')">
                    <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                    <button type="submit" style="width:100%;padding:8px 12px;background:rgba(255,107,107,.1);border:1px solid var(--red);border-radius:8px;color:var(--red);cursor:pointer;font-size:.875rem;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-trash"></i> Delete Submission
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<?php View::endSection(); ?>
