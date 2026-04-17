<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
</div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
</div>
<?php endif; ?>

<!-- Page Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;">
            <i class="fas fa-wpforms" style="color:var(--cyan);margin-right:8px;"></i> FormX – Form Builder
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem;">Create and manage forms with the drag-and-drop builder.</p>
    </div>
    <a href="/admin/formx/create" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;text-decoration:none;background:linear-gradient(135deg,var(--cyan),var(--purple));color:#fff;font-weight:600;font-size:.875rem;">
        <i class="fas fa-plus"></i> New Form
    </a>
</div>

<!-- Filters -->
<div class="card" style="padding:16px;margin-bottom:24px;">
    <form method="GET" action="/admin/formx" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Search</label>
            <input type="text" name="search" value="<?= View::e($search) ?>" placeholder="Form title or slug…"
                   style="width:100%;padding:8px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
        </div>
        <div style="min-width:160px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Status</label>
            <select name="status" style="width:100%;padding:8px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
                <option value="">All Statuses</option>
                <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="draft"    <?= $status === 'draft'    ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" style="padding:8px 16px;background:var(--cyan);color:#000;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:.875rem;">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="/admin/formx" style="padding:8px 16px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);text-decoration:none;font-size:.875rem;">Reset</a>
        </div>
    </form>
</div>

<!-- Forms Table -->
<div class="card">
    <?php if (empty($forms)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-secondary);">
        <i class="fas fa-wpforms" style="font-size:3rem;margin-bottom:16px;opacity:.3;"></i>
        <p style="font-size:1rem;">No forms yet.</p>
        <a href="/admin/formx/create" style="margin-top:12px;display:inline-block;padding:8px 20px;background:var(--cyan);color:#000;border-radius:8px;text-decoration:none;font-weight:600;">Create your first form</a>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:12px 16px;font-size:.8rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Form</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.8rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Status</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.8rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Fields</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.8rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Submissions</th>
                    <th style="text-align:left;padding:12px 16px;font-size:.8rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Created</th>
                    <th style="text-align:right;padding:12px 16px;font-size:.8rem;color:var(--text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $f): ?>
                <?php
                $fFields = json_decode($f['fields'] ?? '[]', true) ?: [];
                $statusColors = ['active'=>'var(--green)','inactive'=>'var(--red)','draft'=>'var(--orange)'];
                $statusColor  = $statusColors[$f['status']] ?? 'var(--text-secondary)';
                ?>
                <tr style="border-bottom:1px solid var(--border-color);transition:background .2s;" onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 16px;">
                        <div style="font-weight:600;color:var(--text-primary);"><?= View::e($f['title']) ?></div>
                        <div style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;">
                            <i class="fas fa-link" style="margin-right:4px;"></i>
                            <a href="/forms/<?= View::e($f['slug']) ?>" target="_blank" style="color:var(--cyan);text-decoration:none;">/forms/<?= View::e($f['slug']) ?></a>
                        </div>
                    </td>
                    <td style="padding:14px 16px;">
                        <span style="padding:4px 10px;border-radius:20px;font-size:.75rem;font-weight:600;background:<?= $statusColor ?>22;color:<?= $statusColor ?>;">
                            <?= ucfirst(View::e($f['status'])) ?>
                        </span>
                    </td>
                    <td style="padding:14px 16px;color:var(--text-secondary);font-size:.875rem;"><?= count($fFields) ?></td>
                    <td style="padding:14px 16px;">
                        <a href="/admin/formx/<?= $f['id'] ?>/submissions" style="color:var(--cyan);text-decoration:none;font-weight:600;">
                            <?= (int)$f['submissions_count'] ?>
                        </a>
                    </td>
                    <td style="padding:14px 16px;color:var(--text-secondary);font-size:.8rem;"><?= date('M d, Y', strtotime($f['created_at'])) ?></td>
                    <td style="padding:14px 16px;text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                            <a href="/admin/formx/<?= $f['id'] ?>/edit" title="Edit" style="padding:6px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);text-decoration:none;font-size:.8rem;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/admin/formx/<?= $f['id'] ?>/submissions" title="Submissions" style="padding:6px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--cyan);text-decoration:none;font-size:.8rem;">
                                <i class="fas fa-inbox"></i>
                            </a>
                            <a href="/forms/<?= View::e($f['slug']) ?>" target="_blank" title="Preview" style="padding:6px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--green);text-decoration:none;font-size:.8rem;">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <!-- Toggle Status -->
                            <form method="POST" action="/admin/formx/<?= $f['id'] ?>/toggle" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <button type="submit" title="Toggle Status" style="padding:6px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--orange);cursor:pointer;font-size:.8rem;">
                                    <i class="fas fa-toggle-<?= $f['status'] === 'active' ? 'on' : 'off' ?>"></i>
                                </button>
                            </form>
                            <!-- Duplicate -->
                            <form method="POST" action="/admin/formx/<?= $f['id'] ?>/duplicate" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <button type="submit" title="Duplicate" style="padding:6px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--purple);cursor:pointer;font-size:.8rem;">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                            <!-- Delete -->
                            <form method="POST" action="/admin/formx/<?= $f['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this form and all its submissions? This cannot be undone.')">
                                <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                <button type="submit" title="Delete" style="padding:6px 10px;background:var(--bg-secondary);border:1px solid var(--red);border-radius:6px;color:var(--red);cursor:pointer;font-size:.8rem;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total'] > 1): ?>
    <div style="padding:16px;display:flex;justify-content:center;gap:8px;flex-wrap:wrap;">
        <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
        <a href="/admin/formx?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
           style="padding:6px 12px;border-radius:6px;text-decoration:none;font-size:.8rem;border:1px solid <?= $i === $pagination['current'] ? 'var(--cyan)' : 'var(--border-color)' ?>;background:<?= $i === $pagination['current'] ? 'var(--cyan)' : 'var(--bg-secondary)' ?>;color:<?= $i === $pagination['current'] ? '#000' : 'var(--text-primary)' ?>;">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
