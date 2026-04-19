<?php
/**
 * CodeXPro – Single Snippet View (ConvertX-style)
 */
use Core\View;
use Core\Auth;

$user        = Auth::user();
$currentPage = 'snippets';
$title       = View::e($snippet['title']) . ' – Snippet';
$isOwner     = $snippet['user_id'] == $user['id'];

$langColors = [
    'javascript' => '#f7df1e', 'python' => '#3776ab', 'php' => '#777bb4',
    'html'       => '#e34c26', 'css'    => '#264de4', 'sql' => '#336791',
    'java'       => '#007396', 'cpp'    => '#00599C', 'csharp' => '#9b4f96',
    'ruby'       => '#cc342d', 'go'     => '#00ADD8', 'rust' => '#CE422B',
];
$lang = strtolower($snippet['language'] ?? 'plaintext');
$lc   = $langColors[$lang] ?? 'var(--cx-primary)';

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 id="snippetTitle" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;"><?= View::e($snippet['title']) ?></h1>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <?php if ($isOwner): ?>
        <button onclick="toggleQuickEdit()" class="btn btn-secondary btn-sm" id="quickEditBtn">
            <i class="fas fa-sliders-h"></i> Config
        </button>
        <a href="/projects/codexpro/snippets/<?= (int)$snippet['id'] ?>/edit" class="btn btn-primary btn-sm">
            <i class="fas fa-pen-to-square"></i> Edit
        </a>
        <button onclick="deleteSnippet(<?= (int)$snippet['id'] ?>)" class="btn btn-sm" style="background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.25);">
            <i class="fas fa-trash"></i>
        </button>
        <?php endif; ?>
        <?php if ($snippet['is_public']): ?>
        <button onclick="shareSnippet()" class="btn btn-secondary btn-sm">
            <i class="fas fa-share-nodes"></i> Share
        </button>
        <?php endif; ?>
        <button onclick="copyCode()" class="btn btn-secondary btn-sm">
            <i class="fas fa-copy"></i> Copy
        </button>
    </div>
</div>

<!-- Quick Edit Panel -->
<?php if ($isOwner): ?>
<div id="quickEditPanel" class="quick-edit-panel" style="display:none;" onclick="event.stopPropagation();">
    <h3 style="color:var(--cx-primary);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
        <i class="fas fa-sliders-h"></i> Quick Configuration
    </h3>
    <div class="form-group">
        <label class="form-label">Title</label>
        <input type="text" id="quickTitle" class="form-control" value="<?= View::e($snippet['title']) ?>">
    </div>
    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea id="quickDescription" class="form-control" rows="2"><?= View::e($snippet['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label class="toggle-label">
            <input type="checkbox" id="quickPublic" <?= $snippet['is_public'] ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
            <span class="toggle-text">Public Snippet</span>
        </label>
    </div>
    <div class="form-actions">
        <button onclick="saveQuickEdit()" class="btn btn-primary btn-sm">
            <i class="fas fa-floppy-disk"></i> Save
        </button>
        <button onclick="toggleQuickEdit()" class="btn btn-secondary btn-sm">
            <i class="fas fa-xmark"></i> Cancel
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Snippet Card -->
<div class="card" style="margin-bottom:0;">
    <!-- Language accent bar -->
    <div style="height:4px;background:<?= $lc ?>;border-radius:4px 4px 0 0;margin:-1.5rem -1.5rem 1.25rem;"></div>

    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem;">
        <span style="background:<?= $lc ?>;color:#fff;font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;text-transform:uppercase;letter-spacing:.04em;">
            <?= View::e($lang) ?>
        </span>
        <span id="visibilityBadge" style="padding:.2rem .5rem;border-radius:20px;font-size:.72rem;font-weight:600;background:<?= $snippet['is_public'] ? 'rgba(0,255,136,.1)' : 'rgba(255,255,255,.06)' ?>;color:<?= $snippet['is_public'] ? 'var(--cx-secondary)' : 'var(--text-secondary)' ?>;">
            <i class="fas fa-<?= $snippet['is_public'] ? 'globe' : 'lock' ?>"></i>
            <span id="visibilityText"><?= $snippet['is_public'] ? 'Public' : 'Private' ?></span>
        </span>
        <span style="font-size:.75rem;color:var(--text-secondary);" title="<?= date('F j, Y g:i A', strtotime($snippet['created_at'])) ?>">
            <i class="fa-regular fa-calendar" style="margin-right:.25rem;"></i>
            <span class="relative-time" data-time="<?= strtotime($snippet['created_at']) ?>"></span>
        </span>
        <?php if (!empty($snippet['views'])): ?>
        <span style="font-size:.75rem;color:var(--text-secondary);">
            <i class="fas fa-eye" style="margin-right:.25rem;"></i><?= number_format($snippet['views']) ?> views
        </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($snippet['description'])): ?>
    <p id="snippetDescription" style="color:var(--text-secondary);font-size:.875rem;margin-bottom:1rem;line-height:1.6;">
        <?= View::e($snippet['description']) ?>
    </p>
    <?php endif; ?>

    <div class="code-container" style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.5rem;overflow:hidden;">
        <pre style="margin:0;padding:1.25rem;overflow-x:auto;"><code class="language-<?= View::e($lang) ?>"><?= View::e($snippet['code']) ?></code></pre>
    </div>

    <?php if (!empty($snippet['tags'])): ?>
    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;padding-top:1rem;border-top:1px solid var(--border-color);margin-top:1rem;">
        <i class="fas fa-tags" style="color:var(--text-secondary);font-size:.8rem;"></i>
        <?php foreach (explode(',', $snippet['tags']) as $tag): ?>
        <span style="background:rgba(0,240,255,.1);color:var(--cx-primary);padding:.15rem .55rem;border-radius:12px;font-size:.73rem;font-weight:500;">
            <?= View::e(trim($tag)) ?>
        </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Syntax highlight -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script>
hljs.highlightAll();
window.snippetId = <?= (int)$snippet['id'] ?>;
window.isOwner   = <?= $isOwner ? 'true' : 'false' ?>;
if (typeof updateRelativeTimes === 'function') {
    updateRelativeTimes();
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
