<?php
/**
 * CodeXPro – Snippets List View (ConvertX-style)
 */
use Core\View;
$currentPage = 'snippets';
$title       = 'Code Snippets';

ob_start();

// Language color map
$langColors = [
    'javascript' => ['#f7df1e','#1a1a00'],
    'python'     => ['#3776ab','#fff'],
    'php'        => ['#777bb4','#fff'],
    'html'       => ['#e34c26','#fff'],
    'css'        => ['#264de4','#fff'],
    'sql'        => ['#336791','#fff'],
    'java'       => ['#007396','#fff'],
    'cpp'        => ['#00599C','#fff'],
    'csharp'     => ['#9b4f96','#fff'],
    'ruby'       => ['#cc342d','#fff'],
    'go'         => ['#00ADD8','#fff'],
    'rust'       => ['#CE422B','#fff'],
    'typescript' => ['#3178c6','#fff'],
    'bash'       => ['#4EAA25','#fff'],
];
?>

<div class="page-header">
    <h1>Code Snippets</h1>
    <p>Save and reuse code blocks across projects</p>
</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div style="font-size:.875rem;color:var(--text-secondary);">
        <span style="font-weight:600;color:var(--text-primary);"><?= count($snippets ?? []) ?></span>
        snippet<?= count($snippets ?? []) !== 1 ? 's' : '' ?>
    </div>
    <button onclick="showNewSnippetModal()" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Snippet
    </button>
</div>

<?php if (empty($snippets)): ?>
<div class="card" style="text-align:center;padding:3.5rem 1.5rem;">
    <i class="fa-solid fa-code" style="font-size:3rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:block;margin-bottom:1rem;"></i>
    <p style="color:var(--text-secondary);margin-bottom:1.25rem;">No snippets yet. Save your first code block.</p>
    <button onclick="showNewSnippetModal()" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create First Snippet
    </button>
</div>

<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
    <?php foreach ($snippets as $snippet):
        $lang   = strtolower($snippet['language'] ?? 'plaintext');
        $lc     = $langColors[$lang] ?? ['rgba(0,240,255,.2)','var(--cx-primary)'];
    ?>
    <div class="card" style="margin-bottom:0;display:flex;flex-direction:column;">
        <!-- Lang accent bar -->
        <div style="height:4px;background:<?= $lc[0] ?>;border-radius:4px 4px 0 0;margin:-1.5rem -1.5rem 1rem;"></div>

        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.625rem;">
            <h3 style="font-size:.9rem;font-weight:700;color:var(--text-primary);margin:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?= View::e($snippet['title']) ?>
            </h3>
            <span style="background:<?= $lc[0] ?>;color:<?= $lc[1] ?>;border-radius:20px;font-size:.67rem;font-weight:700;padding:.15rem .55rem;flex-shrink:0;text-transform:uppercase;letter-spacing:.04em;">
                <?= View::e($lang) ?>
            </span>
        </div>

        <?php if (!empty($snippet['description'])): ?>
        <p style="font-size:.78rem;color:var(--text-secondary);flex:1;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:.75rem;">
            <?= View::e($snippet['description']) ?>
        </p>
        <?php endif; ?>

        <div style="flex:1;"></div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:.625rem;border-top:1px solid var(--border-color);font-size:.73rem;color:var(--text-secondary);margin-bottom:.75rem;">
            <span><i class="fa-regular fa-calendar" style="margin-right:.25rem;"></i><?= date('M j, Y', strtotime($snippet['created_at'])) ?></span>
            <span style="padding:.15rem .4rem;border-radius:8px;font-size:.68rem;font-weight:600;background:<?= !empty($snippet['is_public']) ? 'rgba(0,255,136,.1)' : 'rgba(255,255,255,.05)' ?>;color:<?= !empty($snippet['is_public']) ? 'var(--cx-secondary)' : 'var(--text-secondary)' ?>;">
                <?= !empty($snippet['is_public']) ? 'Public' : 'Private' ?>
            </span>
        </div>

        <a href="/projects/codexpro/snippets/<?= (int)$snippet['id'] ?>" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
            <i class="fas fa-eye"></i> View Snippet
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- New Snippet Modal -->
<div id="newSnippetModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.75);align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:1rem;width:90%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 8px 40px rgba(0,0,0,.5);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-color);">
            <h2 style="font-size:1.1rem;font-weight:700;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:flex;align-items:center;gap:.5rem;">
                <i class="fas fa-plus-circle" style="-webkit-text-fill-color:var(--cx-primary);"></i> New Snippet
            </h2>
            <button onclick="closeNewSnippetModal()" style="background:none;border:none;color:var(--text-secondary);font-size:1.4rem;cursor:pointer;line-height:1;">×</button>
        </div>
        <form id="newSnippetForm" onsubmit="createSnippet(event)" style="padding:1.5rem;">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <div class="form-group">
                <label class="form-label">Title <span style="color:var(--cx-primary);">*</span></label>
                <input type="text" id="snippetTitle" name="title" required class="form-control" placeholder="e.g. Rate limiter middleware">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="snippetDescription" name="description" class="form-control" rows="2" placeholder="Brief description (optional)"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Language <span style="color:var(--cx-primary);">*</span></label>
                <select id="snippetLanguage" name="language" required class="form-control">
                    <option value="">Select language…</option>
                    <?php foreach (['javascript','python','php','html','css','sql','java','cpp','csharp','ruby','go','rust','typescript','bash'] as $lang): ?>
                    <option value="<?= $lang ?>"><?= ucfirst($lang) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Code <span style="color:var(--cx-primary);">*</span></label>
                <textarea id="snippetCode" name="code" required class="form-control" rows="10" style="font-family:'Fira Code','Courier New',monospace;font-size:.82rem;" placeholder="Paste your code here…"></textarea>
            </div>
            <div class="form-group">
                <label class="toggle-label">
                    <input type="checkbox" name="is_public">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text" style="font-size:.875rem;">Make snippet public</span>
                </label>
                <small style="color:var(--text-secondary);font-size:.73rem;display:block;margin-top:.375rem;">Anyone with the link can view public snippets</small>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.625rem;padding-top:1rem;border-top:1px solid var(--border-color);margin-top:1rem;">
                <button type="button" onclick="closeNewSnippetModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Snippet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showNewSnippetModal() {
    const m = document.getElementById('newSnippetModal');
    m.style.display = 'flex';
    m.style.animation = 'cx-fade-up .25s ease-out';
}
function closeNewSnippetModal() {
    document.getElementById('newSnippetModal').style.display = 'none';
    document.getElementById('newSnippetForm').reset();
}
function createSnippet(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating…';
    fetch('/projects/codexpro/snippets', { method: 'POST', body: new FormData(e.target) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/projects/codexpro/snippets/' + data.snippet_id;
            } else {
                showNotification('Error: ' + (data.error || 'Failed to create snippet'), 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus"></i> Create Snippet';
            }
        })
        .catch(() => {
            showNotification('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Create Snippet';
        });
}
document.getElementById('newSnippetModal').addEventListener('click', function(e) {
    if (e.target === this) closeNewSnippetModal();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
