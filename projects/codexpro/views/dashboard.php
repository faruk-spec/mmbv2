<?php
/**
 * CodeXPro – Dashboard View (ConvertX-style)
 */
use Core\View;
$currentPage = 'dashboard';
$title       = 'Dashboard';

ob_start();
?>

<!-- Page header -->
<div class="page-header">
    <h1>CodeXPro</h1>
    <p>Code editor, snippet manager and template library in one place</p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card" onclick="location.href='/projects/codexpro/projects'" style="cursor:pointer;">
        <span class="stat-icon"><i class="fas fa-folder"></i></span>
        <span class="value"><?= (int)($stats['total_projects'] ?? 0) ?></span>
        <span class="label">Total Projects</span>
    </div>
    <div class="stat-card" onclick="location.href='/projects/codexpro/snippets'" style="cursor:pointer;">
        <span class="stat-icon" style="background:linear-gradient(135deg,var(--cx-secondary),#059669);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><i class="fas fa-code"></i></span>
        <span class="value" style="background:linear-gradient(135deg,var(--cx-secondary),#059669);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= (int)($stats['total_snippets'] ?? 0) ?></span>
        <span class="label">Code Snippets</span>
    </div>
    <div class="stat-card" onclick="location.href='/projects/codexpro/templates'" style="cursor:pointer;">
        <span class="stat-icon" style="background:linear-gradient(135deg,var(--cx-accent),#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><i class="fas fa-file-code"></i></span>
        <span class="value" style="background:linear-gradient(135deg,var(--cx-accent),#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= (int)($stats['total_templates'] ?? 0) ?></span>
        <span class="label">Templates</span>
    </div>
    <div class="stat-card" onclick="location.href='/projects/codexpro/projects'" style="cursor:pointer;">
        <span class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><i class="fas fa-clock"></i></span>
        <span class="value" style="background:linear-gradient(135deg,#f59e0b,#ef4444);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= (int)($stats['recent_edits'] ?? 0) ?></span>
        <span class="label">Edits This Week</span>
    </div>
</div>

<!-- Quick Actions row -->
<div class="cx-quick-row">
    <a href="/projects/codexpro/editor/new"
       class="cx-quick-card"
       style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));box-shadow:0 4px 16px rgba(0,240,255,.35);">
        <i class="fa-solid fa-plus qc-icon"></i>
        <strong>New Editor</strong>
        <p>Open live code editor</p>
    </a>
    <a href="/projects/codexpro/projects"
       class="cx-quick-card"
       style="background:linear-gradient(135deg,#7c3aed,var(--cx-secondary));box-shadow:0 4px 16px rgba(124,58,237,.3);">
        <i class="fa-solid fa-folder qc-icon"></i>
        <strong>Projects</strong>
        <p>Manage all projects</p>
    </a>
    <a href="/projects/codexpro/snippets"
       class="cx-quick-card"
       style="background:linear-gradient(135deg,#0891b2,#06b6d4);box-shadow:0 4px 16px rgba(8,145,178,.3);">
        <i class="fa-solid fa-code qc-icon"></i>
        <strong>Snippets</strong>
        <p>Code snippet library</p>
    </a>
    <a href="/projects/codexpro/templates"
       class="cx-quick-card"
       style="background:linear-gradient(135deg,#0891b2,var(--cx-secondary));box-shadow:0 4px 16px rgba(8,145,178,.3);">
        <i class="fa-solid fa-file-code qc-icon"></i>
        <strong>Templates</strong>
        <p>Browse starter templates</p>
    </a>
</div>

<!-- Feature Hub -->
<div class="card" style="border-color:var(--border-hover);background:linear-gradient(135deg,rgba(0,240,255,.05),rgba(0,255,136,.04));">
    <div class="card-header" style="border-color:rgba(0,240,255,.2);">
        <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa-solid fa-terminal" style="color:#fff;font-size:1rem;"></i>
        </div>
        <div>
            <div style="font-weight:700;color:var(--text-primary);">CodeXPro Toolkit</div>
            <div style="font-size:.78rem;font-weight:400;color:var(--text-secondary);">All developer tools in one place</div>
        </div>
        <span style="margin-left:auto;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));color:#000;padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;letter-spacing:.04em;">⚡ PRO</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;">
        <a href="/projects/codexpro/editor/new" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));">
                <i class="fa-solid fa-code"></i>
            </div>
            <div class="tile-title">Live Editor</div>
            <div class="tile-desc">HTML/CSS/JS preview</div>
        </a>
        <a href="/projects/codexpro/snippets" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#7c3aed,var(--cx-secondary));">
                <i class="fa-solid fa-bookmark"></i>
            </div>
            <div class="tile-title">Snippets</div>
            <div class="tile-desc">Reusable code blocks</div>
        </a>
        <a href="/projects/codexpro/templates" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4);">
                <i class="fa-solid fa-file-code"></i>
            </div>
            <div class="tile-title">Templates</div>
            <div class="tile-desc">Starter boilerplates</div>
        </a>
        <a href="/projects/codexpro/settings" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div class="tile-title">Editor Config</div>
            <div class="tile-desc">Theme, font, shortcuts</div>
        </a>
    </div>
</div>

<!-- Recent Projects -->
<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fa-solid fa-clock-rotate-left"></i> Recent Projects</span>
        <a href="/projects/codexpro/projects" style="font-size:.8rem;color:var(--cx-primary);text-decoration:none;">View all →</a>
    </div>

    <?php if (empty($recentProjects)): ?>
        <div style="text-align:center;padding:2.5rem 1rem;">
            <i class="fa-solid fa-folder-open" style="font-size:3rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:.75rem;display:block;"></i>
            <p style="color:var(--text-secondary);font-size:.9rem;">No projects yet.</p>
            <a href="/projects/codexpro/editor/new" class="btn btn-primary" style="margin-top:1rem;">
                <i class="fa-solid fa-plus"></i> Create first project
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="cx-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Language</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProjects as $p): ?>
                    <tr>
                        <td style="font-weight:600;color:var(--text-primary);"><?= htmlspecialchars($p['name']) ?></td>
                        <td>
                            <span style="background:rgba(0,240,255,.1);color:var(--cx-primary);padding:.15rem .5rem;border-radius:12px;font-size:.75rem;font-weight:600;">
                                <?= htmlspecialchars($p['language'] ?? 'HTML') ?>
                            </span>
                        </td>
                        <td style="color:var(--text-secondary);font-size:.78rem;">
                            <?= !empty($p['updated_at']) ? date('M j, Y', strtotime($p['updated_at'])) : '—' ?>
                        </td>
                        <td>
                            <a href="/projects/codexpro/editor/<?= (int)$p['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.cx-quick-row { display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; }
.cx-quick-row .cx-quick-card { flex:1; min-width:140px; }
@media (max-width:37.5rem) {
    .cx-quick-row { gap:.625rem; }
    .cx-quick-row .cx-quick-card { min-width:calc(50% - .3125rem); max-width:calc(50% - .3125rem); }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
