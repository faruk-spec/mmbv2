<?php
$csrfToken = \Core\Security::generateCsrfToken();
ob_start();
?>
<div style="margin-bottom:1rem;display:flex;gap:.5rem;align-items:center;">
    <a href="/projects/helpdeskpro/templates" style="color:var(--text-secondary);text-decoration:none;font-size:.85rem;"><i class="fas fa-arrow-left"></i> Templates</a>
    <span style="color:var(--text-secondary);">/</span>
    <?php if ($category): ?>
    <a href="/projects/helpdeskpro/templates/category/<?= (int)$category['id'] ?>" style="color:var(--text-secondary);text-decoration:none;font-size:.85rem;"><?= htmlspecialchars($category['name']) ?></a>
    <span style="color:var(--text-secondary);">/</span>
    <?php endif; ?>
    <span style="font-size:.85rem;"><?= htmlspecialchars($subcategory['name']) ?></span>
</div>

<div class="card" style="margin-bottom:1rem;">
    <h2 style="margin:0 0 .3rem;font-size:1.15rem;"><?= htmlspecialchars($subcategory['name']) ?></h2>
    <?php if (!empty($subcategory['description'])): ?>
    <p style="margin:0;color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars($subcategory['description']) ?></p>
    <?php endif; ?>
</div>

<?php if ($isAgent): ?>
<div class="card" style="margin-bottom:1rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Add Item</h3>
    <form method="POST" action="/projects/helpdeskpro/templates/items/create" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="subcategory_id" value="<?= (int)$subcategory['id'] ?>">
        <div style="flex:2;min-width:160px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Name *</label>
            <input type="text" name="name" placeholder="Item name" required maxlength="150">
        </div>
        <div style="flex:1;min-width:120px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Default Priority</label>
            <select name="default_priority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        <div style="flex:2;min-width:200px;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Description</label>
            <input type="text" name="description" placeholder="Optional description">
        </div>
        <button type="submit" class="btn btn-primary" style="white-space:nowrap;"><i class="fas fa-plus"></i> Add</button>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Items</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Default Priority</th>
                <th>Fields</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <tr><td colspan="4" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No items yet.</td></tr>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                <td><span class="badge badge-<?= htmlspecialchars($item['default_priority']) ?>"><?= htmlspecialchars(ucfirst($item['default_priority'])) ?></span></td>
                <td><span class="badge badge-open"><?= (int)($item['field_count'] ?? 0) ?></span></td>
                <td style="display:flex;gap:.4rem;">
                    <a href="/projects/helpdeskpro/templates/item/<?= (int)$item['id'] ?>" class="btn btn-secondary" style="padding:.3rem .6rem;font-size:.8rem;"><i class="fas fa-eye"></i> View</a>
                    <?php if ($isAgent): ?>
                    <form method="POST" action="/projects/helpdeskpro/templates/items/delete/<?= (int)$item['id'] ?>" onsubmit="return confirm('Delete?');" style="display:inline;">
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
