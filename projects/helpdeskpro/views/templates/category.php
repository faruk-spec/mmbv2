<?php
$csrfToken = \Core\Security::generateCsrfToken();
ob_start();
?>
<div style="margin-bottom:1rem;">
    <a href="/projects/helpdeskpro/templates" style="color:var(--text-secondary);text-decoration:none;font-size:.85rem;"><i class="fas fa-arrow-left"></i> Back to Templates</a>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <div style="width:2.5rem;height:2.5rem;border-radius:.6rem;background:rgba(59,130,246,.15);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-<?= htmlspecialchars($category['icon'] ?? 'folder') ?>" style="color:var(--hp-primary);font-size:1.1rem;"></i>
        </div>
        <div>
            <h2 style="margin:0;font-size:1.2rem;"><?= htmlspecialchars($category['name']) ?></h2>
            <?php if (!empty($category['description'])): ?>
            <p style="margin:.2rem 0 0;color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars($category['description']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($isAgent): ?>
<div class="card" style="margin-bottom:1rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Add Subcategory</h3>
    <form method="POST" action="/projects/helpdeskpro/templates/subcategories/create" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
        <div style="flex:1;min-width:160px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Name *</label>
            <input type="text" name="name" placeholder="Subcategory name" required maxlength="100">
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
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Subcategories</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Items</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($subcategories)): ?>
            <tr><td colspan="4" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No subcategories yet.</td></tr>
        <?php else: ?>
            <?php foreach ($subcategories as $sub): ?>
            <tr>
                <td><strong><?= htmlspecialchars($sub['name']) ?></strong></td>
                <td style="color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars(mb_substr((string)($sub['description'] ?? ''), 0, 80)) ?></td>
                <td><span class="badge badge-open"><?= (int)($sub['item_count'] ?? 0) ?></span></td>
                <td style="display:flex;gap:.4rem;">
                    <a href="/projects/helpdeskpro/templates/subcategory/<?= (int)$sub['id'] ?>" class="btn btn-secondary" style="padding:.3rem .6rem;font-size:.8rem;"><i class="fas fa-eye"></i> View</a>
                    <?php if ($isAgent): ?>
                    <form method="POST" action="/projects/helpdeskpro/templates/subcategories/delete/<?= (int)$sub['id'] ?>" onsubmit="return confirm('Delete?');" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3);padding:.3rem .6rem;font-size:.8rem;"><i class="fas fa-trash"></i></button>
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
