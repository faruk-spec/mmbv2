<?php
/**
 * CodeXPro – Templates View (ConvertX-style)
 */
use Core\View;
$currentPage = 'templates';
$title       = 'Code Templates';

ob_start();

$langColors = [
    'javascript' => '#f7df1e', 'python' => '#3776ab', 'php' => '#777bb4',
    'html'       => '#e34c26', 'css'    => '#264de4', 'sql' => '#336791',
    'java'       => '#007396', 'cpp'    => '#00599C', 'csharp' => '#9b4f96',
    'ruby'       => '#cc342d', 'go'     => '#00ADD8', 'rust' => '#CE422B',
];
?>

<div class="page-header">
    <h1>Code Templates</h1>
    <p>Ready-made boilerplates to jumpstart your projects</p>
</div>

<?php if (empty($templates)): ?>
<div class="card" style="text-align:center;padding:3.5rem 1.5rem;">
    <i class="fa-solid fa-file-code" style="font-size:3rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:block;margin-bottom:1rem;"></i>
    <p style="color:var(--text-secondary);margin-bottom:.5rem;font-weight:600;color:var(--text-primary);">No Templates Yet</p>
    <p style="color:var(--text-secondary);font-size:.875rem;">Templates will appear here once added to the library.</p>
</div>

<?php else:
    $categories = [];
    foreach ($templates as $t) {
        $cat = $t['category'] ?? 'Other';
        $categories[$cat][] = $t;
    }
    foreach ($categories as $category => $catTemplates): ?>

<div class="card">
    <div class="card-header">
        <span><?= htmlspecialchars($category) ?></span>
        <span style="font-size:.78rem;font-weight:400;color:var(--text-secondary);"><?= count($catTemplates) ?> template<?= count($catTemplates) !== 1 ? 's' : '' ?></span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.875rem;">
        <?php foreach ($catTemplates as $template):
            $lang = strtolower($template['language'] ?? 'plaintext');
            $lc   = $langColors[$lang] ?? 'var(--cx-primary)';
        ?>
        <div class="cx-template-tile">
            <div style="height:3px;background:<?= $lc ?>;border-radius:3px 3px 0 0;margin:-1.25rem -1.25rem .75rem;"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.5rem;">
                <h3 style="font-size:.88rem;font-weight:700;color:var(--text-primary);margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">
                    <?= View::e($template['name']) ?>
                </h3>
                <?php if (!empty($template['language'])): ?>
                <span style="background:<?= $lc ?>;color:#fff;font-size:.65rem;font-weight:700;padding:.15rem .45rem;border-radius:10px;flex-shrink:0;text-transform:uppercase;">
                    <?= View::e(strtoupper($lang)) ?>
                </span>
                <?php endif; ?>
            </div>
            <?php if (!empty($template['description'])): ?>
            <p style="font-size:.77rem;color:var(--text-secondary);margin-bottom:.75rem;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                <?= View::e($template['description']) ?>
            </p>
            <?php endif; ?>
            <?php if (!empty($template['is_starter'])): ?>
            <button onclick="useStarterTemplate('<?= View::e($template['id']) ?>')" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;">
                <i class="fas fa-rocket"></i> Use Template
            </button>
            <?php else: ?>
            <button onclick="useTemplate(<?= (int)$template['id'] ?>)" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;">
                <i class="fas fa-check"></i> Use Template
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

    <?php endforeach;
endif; ?>

<style>
.cx-template-tile {
    background:var(--bg-secondary);
    border:1px solid var(--border-color);
    border-radius:.75rem;
    padding:1.25rem;
    transition:border-color .25s,box-shadow .25s,transform .25s;
}
.cx-template-tile:hover {
    border-color:var(--cx-primary);
    box-shadow:0 4px 20px rgba(0,240,255,.15);
    transform:translateY(-2px);
}
</style>

<script>
function useTemplate(templateId) {
    fetch('/projects/codexpro/templates/' + templateId)
        .then(async (r) => {
            const ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                const txt = await r.text();
                throw new Error(txt ? txt.slice(0, 120) : 'Unexpected non-JSON response');
            }
            return r.json();
        })
        .then(data => {
            if (data.success) {
                sessionStorage.setItem('template_data', JSON.stringify(data.template));
                window.location.href = '/projects/codexpro/editor/new';
            } else {
                showNotification('Error loading template: ' + (data.error || 'Unknown'), 'error');
            }
        })
        .catch(err => showNotification('Error: ' + err.message, 'error'));
}

function useStarterTemplate(templateKey) {
    fetch('/projects/codexpro/api/create-from-template', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ template: templateKey })
    })
        .then(async (r) => {
            const ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                const txt = await r.text();
                throw new Error(txt ? txt.slice(0, 120) : 'Unexpected non-JSON response');
            }
            return r.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = '/projects/codexpro/editor/' + data.project_id;
            } else {
                showNotification('Error: ' + (data.error || 'Unknown'), 'error');
            }
        })
        .catch(err => showNotification('Error: ' + err.message, 'error'));
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
