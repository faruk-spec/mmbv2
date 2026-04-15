<?php
/**
 * Admin Support Templates
 */
use Core\View;

View::extend('admin');
View::section('content');
?>

<div style="padding:28px;">
    <div style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
            <i class="fas fa-folder-tree" style="color:#00f0ff;margin-right:10px;"></i>Support Templates
        </h1>
        <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">Manage issue categories and ticket templates.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

        <!-- Categories -->
        <div>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
                <div style="padding:16px 18px;border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:.95rem;font-weight:600;color:var(--text-primary,#e8eefc);">Categories</h3>
                    <span style="color:var(--text-secondary,#8892a6);font-size:.8rem;"><?= count($categories) ?> total</span>
                </div>
                <div style="padding:16px 18px;">
                    <?php if (empty($categories)): ?>
                    <p style="color:var(--text-secondary,#8892a6);font-size:.85rem;text-align:center;padding:20px 0;">No categories yet.</p>
                    <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                        <?php foreach ($categories as $cat): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:rgba(255,255,255,.03);border:1px solid var(--border-color,rgba(255,255,255,.06));border-radius:8px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fas fa-<?= htmlspecialchars($cat['icon']) ?>" style="color:#00f0ff;width:16px;"></i>
                                <span style="color:var(--text-primary,#e8eefc);font-size:.88rem;font-weight:500;"><?= htmlspecialchars($cat['name']) ?></span>
                            </div>
                            <form method="POST" action="/admin/support/templates/category/<?= (int)$cat['id'] ?>/delete" onsubmit="return confirm('Delete this category?')">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:.75rem;padding:4px 8px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Add category form -->
                    <div style="border-top:1px solid var(--border-color,rgba(255,255,255,.06));padding-top:14px;">
                        <h4 style="margin:0 0 12px;font-size:.85rem;font-weight:600;color:var(--text-secondary,#8892a6);">Add Category</h4>
                        <form method="POST" action="/admin/support/templates/category/create">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <input type="text" name="name" required placeholder="Category name"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;">
                            <input type="text" name="icon" value="folder" placeholder="Font Awesome icon name"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;">
                            <textarea name="description" placeholder="Description (optional)" rows="2"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;resize:vertical;"></textarea>
                            <button type="submit" style="width:100%;padding:8px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:6px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
                                <i class="fas fa-plus" style="margin-right:5px;"></i>Add Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
                <div style="padding:16px 18px;border-bottom:1px solid var(--border-color,rgba(255,255,255,.06));display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:.95rem;font-weight:600;color:var(--text-primary,#e8eefc);">Template Items</h3>
                    <span style="color:var(--text-secondary,#8892a6);font-size:.8rem;"><?= count($items) ?> total</span>
                </div>
                <div style="padding:16px 18px;">
                    <?php if (empty($items)): ?>
                    <p style="color:var(--text-secondary,#8892a6);font-size:.85rem;text-align:center;padding:20px 0;">No template items yet.</p>
                    <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                        <?php foreach ($items as $item):
                            $pc = ['urgent'=>'#ff6b6b','high'=>'#ff9f43','medium'=>'#00f0ff','low'=>'#8892a6'];
                            $pColor = $pc[$item['default_priority']] ?? '#8892a6';
                        ?>
                        <div style="padding:10px 12px;background:rgba(255,255,255,.03);border:1px solid var(--border-color,rgba(255,255,255,.06));border-radius:8px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                                <span style="color:var(--text-primary,#e8eefc);font-size:.88rem;font-weight:500;"><?= htmlspecialchars($item['name']) ?></span>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600;background:<?= $pColor ?>1a;color:<?= $pColor ?>"><?= ucfirst($item['default_priority']) ?></span>
                                    <form method="POST" action="/admin/support/templates/item/<?= (int)$item['id'] ?>/delete" onsubmit="return confirm('Delete this item?')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                        <button type="submit" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:.75rem;padding:2px 6px;"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div style="color:var(--text-secondary,#8892a6);font-size:.75rem;"><?= htmlspecialchars($item['category_name'] ?? '') ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Add item form -->
                    <?php if (!empty($categories)): ?>
                    <div style="border-top:1px solid var(--border-color,rgba(255,255,255,.06));padding-top:14px;">
                        <h4 style="margin:0 0 12px;font-size:.85rem;font-weight:600;color:var(--text-secondary,#8892a6);">Add Item</h4>
                        <form method="POST" action="/admin/support/templates/item/create">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            <select name="category_id" required style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="name" required placeholder="Item name"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;">
                            <select name="default_priority" style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;">
                                <?php foreach (['low','medium','high','urgent'] as $p): ?>
                                <option value="<?= $p ?>" <?= $p==='medium'?'selected':'' ?>><?= ucfirst($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="description" placeholder="Description (optional)" rows="2"
                                style="width:100%;padding:8px 10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:6px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;margin-bottom:8px;box-sizing:border-box;resize:vertical;"></textarea>
                            <button type="submit" style="width:100%;padding:8px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:6px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;">
                                <i class="fas fa-plus" style="margin-right:5px;"></i>Add Item
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <p style="color:var(--text-secondary,#8892a6);font-size:.82rem;text-align:center;margin-top:10px;">Create a category first.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php View::endSection(); ?>
