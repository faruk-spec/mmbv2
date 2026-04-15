<?php
$csrfToken = \Core\Security::generateCsrfToken();
ob_start();
?>
<div style="margin-bottom:1rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
    <a href="/projects/helpdeskpro/templates" style="color:var(--text-secondary);text-decoration:none;font-size:.85rem;"><i class="fas fa-arrow-left"></i> Templates</a>
    <?php if ($category): ?>
    <span style="color:var(--text-secondary);">/</span>
    <a href="/projects/helpdeskpro/templates/category/<?= (int)$category['id'] ?>" style="color:var(--text-secondary);text-decoration:none;font-size:.85rem;"><?= htmlspecialchars($category['name']) ?></a>
    <?php endif; ?>
    <?php if ($subcategory): ?>
    <span style="color:var(--text-secondary);">/</span>
    <a href="/projects/helpdeskpro/templates/subcategory/<?= (int)$subcategory['id'] ?>" style="color:var(--text-secondary);text-decoration:none;font-size:.85rem;"><?= htmlspecialchars($subcategory['name']) ?></a>
    <?php endif; ?>
    <span style="color:var(--text-secondary);">/</span>
    <span style="font-size:.85rem;"><?= htmlspecialchars($item['name']) ?></span>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0 0 .3rem;font-size:1.15rem;"><?= htmlspecialchars($item['name']) ?></h2>
            <?php if (!empty($item['description'])): ?>
            <p style="margin:0;color:var(--text-secondary);font-size:.85rem;"><?= htmlspecialchars($item['description']) ?></p>
            <?php endif; ?>
        </div>
        <span class="badge badge-<?= htmlspecialchars($item['default_priority']) ?>" style="font-size:.8rem;">
            Default Priority: <?= htmlspecialchars(ucfirst($item['default_priority'])) ?>
        </span>
    </div>
</div>

<?php if ($isAgent): ?>
<div class="card" style="margin-bottom:1rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Add Custom Field</h3>
    <form method="POST" action="/projects/helpdeskpro/templates/fields/create" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.6rem;align-items:end;">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
        <div>
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Field Label *</label>
            <input type="text" name="field_label" placeholder="e.g. Phone Number" required maxlength="120">
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Field Type</label>
            <select name="field_type" id="fieldType" onchange="toggleOptions(this.value)">
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="dropdown">Dropdown</option>
                <option value="multiselect">Multiselect</option>
                <option value="date">Date</option>
                <option value="boolean">Yes/No</option>
                <option value="file">File Upload</option>
            </select>
        </div>
        <div id="optionsWrap" style="display:none;">
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Options (comma-separated)</label>
            <input type="text" name="field_options" placeholder="Option A, Option B">
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;padding-top:1.2rem;">
            <input type="checkbox" name="is_required" value="1" id="isRequired" style="width:auto;">
            <label for="isRequired" style="font-size:.88rem;cursor:pointer;">Required</label>
        </div>
        <div style="padding-top:1.2rem;">
            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-plus"></i> Add Field</button>
        </div>
    </form>
    <script>
    function toggleOptions(type) {
        document.getElementById('optionsWrap').style.display =
            (type === 'dropdown' || type === 'multiselect') ? 'block' : 'none';
    }
    </script>
</div>
<?php endif; ?>

<div class="card">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Custom Fields</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Label</th>
                <th>Type</th>
                <th>Options</th>
                <th>Required</th>
                <?php if ($isAgent): ?><th>Delete</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($fields)): ?>
            <tr><td colspan="<?= $isAgent ? 6 : 5 ?>" style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No custom fields defined.</td></tr>
        <?php else: ?>
            <?php foreach ($fields as $idx => $field): ?>
            <tr>
                <td style="color:var(--text-secondary);"><?= $idx + 1 ?></td>
                <td><strong><?= htmlspecialchars($field['field_label']) ?></strong></td>
                <td><span class="badge badge-open"><?= htmlspecialchars($field['field_type']) ?></span></td>
                <td style="color:var(--text-secondary);font-size:.82rem;"><?= htmlspecialchars(mb_substr((string)($field['field_options'] ?? ''), 0, 60)) ?></td>
                <td><?= $field['is_required'] ? '<span class="badge" style="background:rgba(239,68,68,.12);color:#f87171;">Yes</span>' : '<span style="color:var(--text-secondary);">No</span>' ?></td>
                <?php if ($isAgent): ?>
                <td>
                    <form method="POST" action="/projects/helpdeskpro/templates/fields/delete/<?= (int)$field['id'] ?>" onsubmit="return confirm('Delete field?');" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3);padding:.25rem .5rem;font-size:.8rem;"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
