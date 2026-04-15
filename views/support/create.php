<?php
/**
 * Create Support Ticket (user view)
 */
use Core\View;

View::extend('main');
View::section('content');
?>

<div class="page-container" style="max-width:760px;margin:0 auto;padding:32px 20px;">
    <!-- Header -->
    <div style="margin-bottom:28px;">
        <a href="/support" style="color:var(--text-secondary,#8892a6);text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:14px;">
            <i class="fas fa-arrow-left"></i> Back to My Tickets
        </a>
        <h1 style="font-size:1.7rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
            <i class="fas fa-plus-circle" style="color:#00f0ff;margin-right:10px;"></i>Create Support Ticket
        </h1>
        <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.9rem;">Describe your issue and our team will get back to you.</p>
    </div>

    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:16px;padding:28px;">
        <form method="POST" action="/support/create" autocomplete="off">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <!-- Template selector (optional) -->
            <?php if (!empty($items)): ?>
            <div style="margin-bottom:22px;">
                <label style="display:block;font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;margin-bottom:8px;">
                    Issue Template <span style="color:var(--text-secondary,#8892a6);font-weight:400;">(optional)</span>
                </label>
                <select name="template_item_id" id="template_item_id" onchange="applyTemplate(this)"
                    style="width:100%;padding:10px 14px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.9rem;outline:none;">
                    <option value="">— Select a template (optional) —</option>
                    <?php
                    $grouped = [];
                    foreach ($items as $item) {
                        $grouped[$item['category_name'] ?? 'General'][] = $item;
                    }
                    foreach ($grouped as $catName => $catItems):
                    ?>
                    <optgroup label="<?= htmlspecialchars($catName) ?>">
                        <?php foreach ($catItems as $item): ?>
                        <option value="<?= (int)$item['id'] ?>"
                            data-priority="<?= htmlspecialchars($item['default_priority']) ?>"
                            data-name="<?= htmlspecialchars($item['name']) ?>"
                            data-description="<?= htmlspecialchars($item['description'] ?? '') ?>">
                            <?= htmlspecialchars($item['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Subject -->
            <div style="margin-bottom:22px;">
                <label for="subject" style="display:block;font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;margin-bottom:8px;">
                    Subject <span style="color:#ff6b6b;">*</span>
                </label>
                <input type="text" id="subject" name="subject" required maxlength="255"
                    value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                    placeholder="Brief description of your issue"
                    style="width:100%;padding:10px 14px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.9rem;outline:none;box-sizing:border-box;">
            </div>

            <!-- Description -->
            <div style="margin-bottom:22px;">
                <label for="description" style="display:block;font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;margin-bottom:8px;">
                    Description <span style="color:#ff6b6b;">*</span>
                </label>
                <textarea id="description" name="description" required rows="7" maxlength="5000"
                    placeholder="Describe your issue in detail. Include any error messages, steps to reproduce, etc."
                    style="width:100%;padding:10px 14px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.9rem;outline:none;resize:vertical;box-sizing:border-box;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                <div style="text-align:right;font-size:.75rem;color:var(--text-secondary,#8892a6);margin-top:4px;">Max 5000 characters</div>
            </div>

            <!-- Priority -->
            <div style="margin-bottom:28px;">
                <label for="priority" style="display:block;font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;margin-bottom:8px;">
                    Priority
                </label>
                <select id="priority" name="priority"
                    style="width:100%;padding:10px 14px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.9rem;outline:none;">
                    <?php foreach ($priorities as $p): ?>
                    <option value="<?= $p ?>" <?= (($_POST['priority'] ?? 'medium') === $p) ? 'selected' : '' ?>>
                        <?= ucfirst($p) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit"
                style="width:100%;padding:12px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:8px;color:white;font-weight:700;font-size:1rem;cursor:pointer;letter-spacing:.02em;">
                <i class="fas fa-paper-plane" style="margin-right:8px;"></i>Submit Ticket
            </button>
        </form>
    </div>
</div>

<script>
function applyTemplate(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;
    var name = opt.getAttribute('data-name') || '';
    var desc = opt.getAttribute('data-description') || '';
    var prio = opt.getAttribute('data-priority') || 'medium';
    if (name) document.getElementById('subject').value = name;
    if (desc) document.getElementById('description').value = desc;
    var prioSel = document.getElementById('priority');
    for (var i = 0; i < prioSel.options.length; i++) {
        if (prioSel.options[i].value === prio) { prioSel.selectedIndex = i; break; }
    }
}
</script>

<?php View::endSection(); ?>
