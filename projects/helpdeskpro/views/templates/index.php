<?php
$csrfToken = \Core\Security::generateCsrfToken();
ob_start();
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
    <h2 style="margin:0;font-size:1.25rem;">Templates</h2>
</div>

<?php if ($isAgent): ?>
<div class="card" style="margin-bottom:1.2rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Create Category</h3>
    <form method="POST" action="/projects/helpdeskpro/templates/categories/create" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div style="flex:1;min-width:160px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Name *</label>
            <input type="text" name="name" placeholder="Category name" required maxlength="100">
        </div>
        <div style="flex:1;min-width:120px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Icon (FA class)</label>
            <input type="text" name="icon" placeholder="folder" value="folder" maxlength="50">
        </div>
        <div style="flex:2;min-width:200px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Description</label>
            <input type="text" name="description" placeholder="Optional description" maxlength="255">
        </div>
        <button type="submit" class="btn btn-primary" style="white-space:nowrap;"><i class="fas fa-plus"></i> Add</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Icon</th>
                <th>Name</th>
                <th>Description</th>
                <th>Subcategories</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($categories)): ?>
            <tr><td colspan="5" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No template categories yet.</td></tr>
        <?php else: ?>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><i class="fas fa-<?= htmlspecialchars($cat['icon'] ?? 'folder') ?>" style="color:var(--hp-primary);"></i></td>
                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                <td style="color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars(mb_substr((string)($cat['description'] ?? ''), 0, 80)) ?></td>
                <td><span class="badge badge-open"><?= (int)($cat['sub_count'] ?? 0) ?></span></td>
                <td style="display:flex;gap:.4rem;flex-wrap:wrap;">
                    <a href="/projects/helpdeskpro/templates/category/<?= (int)$cat['id'] ?>" class="btn btn-secondary" style="padding:.3rem .6rem;font-size:.8rem;"><i class="fas fa-eye"></i> View</a>
                    <?php if ($isAgent): ?>
                    <form method="POST" action="/projects/helpdeskpro/templates/categories/delete/<?= (int)$cat['id'] ?>" onsubmit="return confirm('Delete this category?');" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3);padding:.3rem .6rem;font-size:.8rem;"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
