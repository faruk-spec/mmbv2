<?php use Core\View; use Core\Security; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.table-container { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
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
    vertical-align: middle;
}
table tr:hover { background: rgba(0, 240, 255, 0.05); }
.badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
}
.badge-active   { background: rgba(0, 240, 100, 0.15); color: #00f064; }
.badge-inactive { background: rgba(255, 80, 80, 0.15); color: #ff5050; }
.badge-lang     { background: rgba(0, 240, 255, 0.15); color: var(--cyan); }
.badge-cat      { background: rgba(160, 80, 255, 0.15); color: var(--purple); }
.btn-sm {
    padding: 5px 11px;
    border-radius: 6px;
    font-size: 13px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: opacity .2s;
}
.btn-sm:hover { opacity: .8; }
.btn-edit    { background: var(--cyan);   color: var(--bg-primary); }
.btn-toggle  { background: var(--purple); color: #fff; }
.btn-delete  { background: #e74c3c; color: #fff; }
.btn-create  { background: var(--cyan); color: var(--bg-primary); padding: 8px 18px; font-size: 14px; font-weight: 600; }
.pagination { display: flex; gap: 10px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }
.page-link {
    padding: 7px 14px;
    background: var(--bg-card);
    color: var(--cyan);
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    transition: var(--transition);
}
.page-link:hover { background: rgba(0,240,255,.1); border-color: var(--cyan); }
.page-link.active { background: var(--cyan); color: var(--bg-primary); border-color: var(--cyan); }
.empty-state { text-align: center; padding: 48px !important; color: var(--text-secondary); font-size: 15px; }
.flash-success { background: rgba(0,240,100,.12); border: 1px solid #00f064; color: #00f064; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; }
.flash-error   { background: rgba(231,76,60,.12);  border: 1px solid #e74c3c; color: #e74c3c; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; }

/* Modal */
.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.65);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}
.modal-box h3 { margin: 0 0 22px; color: var(--cyan); font-size: 1.2rem; }
.modal-close {
    position: absolute; top: 14px; right: 18px;
    background: none; border: none; font-size: 22px;
    cursor: pointer; color: var(--text-secondary); line-height: 1;
}
.modal-close:hover { color: var(--text-primary); }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 6px; color: var(--text-secondary); font-size: 13px; font-weight: 600; }
.form-group input[type=text],
.form-group select,
.form-group textarea {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 9px 12px;
    color: var(--text-primary);
    font-size: 14px;
    box-sizing: border-box;
    transition: border-color .2s;
}
.form-group input[type=text]:focus,
.form-group select:focus,
.form-group textarea:focus { outline: none; border-color: var(--cyan); }
.form-group textarea { font-family: 'Fira Mono', 'Consolas', monospace; min-height: 90px; resize: vertical; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-check { display: flex; align-items: center; gap: 10px; }
.form-check input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--cyan); }
.form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
.btn-cancel { background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); }
@media (max-width: 768px) {
    table { font-size: 13px; }
    table th, table td { padding: 8px 6px; }
    .form-row { grid-template-columns: 1fr; }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<?php if (!empty($flashMessage['success'])): ?>
    <div class="flash-success">✓ <?= htmlspecialchars($flashMessage['success']) ?></div>
<?php elseif (!empty($flashMessage['error'])): ?>
    <div class="flash-error">✗ <?= htmlspecialchars($flashMessage['error']) ?></div>
<?php endif; ?>

<?php
// Also render session flash directly
$sessionFlash = $_SESSION['flash'] ?? [];
if (!empty($sessionFlash['success'])): ?>
    <div class="flash-success">✓ <?= htmlspecialchars($sessionFlash['success']) ?></div>
<?php elseif (!empty($sessionFlash['error'])): ?>
    <div class="flash-error">✗ <?= htmlspecialchars($sessionFlash['error']) ?></div>
<?php endif;
unset($_SESSION['flash']); ?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:18px; border-bottom:1px solid var(--border-color); margin-bottom:20px;">
        <div>
            <h2 style="font-size:1.4rem; margin:0; color:var(--text-primary);">CodeXPro Templates</h2>
            <span style="font-size:13px; color:var(--text-secondary);">Total: <?= (int)($totalCount ?? 0) ?></span>
        </div>
        <button class="btn-sm btn-create" onclick="openModal('createModal')">+ Add Template</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Language</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            No templates in database yet. Use the <strong>Add Template</strong> button to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $tpl): ?>
                        <tr>
                            <td style="color:var(--text-secondary)">#<?= (int)$tpl['id'] ?></td>
                            <td><strong><?= htmlspecialchars($tpl['name'] ?? 'Unnamed') ?></strong></td>
                            <td><span class="badge badge-cat"><?= htmlspecialchars($tpl['category'] ?? 'N/A') ?></span></td>
                            <td><span class="badge badge-lang"><?= htmlspecialchars($tpl['language'] ?? 'N/A') ?></span></td>
                            <td>
                                <?php if (!empty($tpl['is_active'])): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-secondary); font-size:13px;">
                                <?= !empty($tpl['created_at']) ? date('M d, Y', strtotime($tpl['created_at'])) : '—' ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="/admin/projects/codexpro/templates/<?= (int)$tpl['id'] ?>/edit" class="btn-sm btn-edit">Edit</a>

                                <form method="POST" action="/admin/projects/codexpro/templates/toggle" style="display:inline;">
                                    <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                    <input type="hidden" name="template_id" value="<?= (int)$tpl['id'] ?>">
                                    <button type="submit" class="btn-sm btn-toggle">
                                        <?= !empty($tpl['is_active']) ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>

                                <form method="POST" action="/admin/projects/codexpro/templates/delete" style="display:inline;"
                                      onsubmit="return confirm('Delete this template? This cannot be undone.')">
                                    <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
                                    <input type="hidden" name="template_id" value="<?= (int)$tpl['id'] ?>">
                                    <button type="submit" class="btn-sm btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-link <?= $i == ($currentPage ?? 1) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ── Create Template Modal ───────────────────────────────────────── -->
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('createModal')" title="Close">&times;</button>
        <h3>Add New Template</h3>
        <form method="POST" action="/admin/projects/codexpro/templates/create">
            <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">

            <div class="form-group">
                <label>Name <span style="color:#e74c3c">*</span></label>
                <input type="text" name="name" required maxlength="120" placeholder="e.g. Bootstrap Starter">
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" maxlength="500" placeholder="Short description…">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="basic">Basic</option>
                        <option value="html">HTML</option>
                        <option value="css">CSS</option>
                        <option value="javascript">JavaScript</option>
                        <option value="php">PHP</option>
                        <option value="react">React</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Language</label>
                    <select name="language">
                        <option value="html">HTML</option>
                        <option value="css">CSS</option>
                        <option value="javascript">JavaScript</option>
                        <option value="php">PHP</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>HTML Content</label>
                <textarea name="html_content" placeholder="<!DOCTYPE html>…"></textarea>
            </div>
            <div class="form-group">
                <label>CSS Content</label>
                <textarea name="css_content" placeholder="body { … }"></textarea>
            </div>
            <div class="form-group">
                <label>JS Content</label>
                <textarea name="js_content" placeholder="// JavaScript…"></textarea>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked>
                    <label for="create_is_active" style="margin:0; cursor:pointer;">Active (visible to users)</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-sm btn-cancel" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn-sm btn-create">Create Template</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === el) closeModal(el.id);
    });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(function(el) { closeModal(el.id); });
});
</script>

<?php View::endSection(); ?>
