<?php
/**
 * CodeXPro – Projects List View (ConvertX-style)
 */
use Core\View;
$currentPage = 'projects';
$title       = 'My Projects';

ob_start();
?>

<div class="page-header">
    <h1>My Projects</h1>
    <p>All your code projects in one place</p>
</div>

<!-- Toolbar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div style="font-size:.875rem;color:var(--text-secondary);">
        <span style="font-weight:600;color:var(--text-primary);"><?= count($projects ?? []) ?></span>
        project<?= count($projects ?? []) !== 1 ? 's' : '' ?>
    </div>
    <a href="/projects/codexpro/editor/new" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> New Project
    </a>
</div>

<?php if (empty($projects)): ?>
<div class="card" style="text-align:center;padding:3.5rem 1.5rem;">
    <i class="fa-solid fa-folder-open" style="font-size:3rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:block;margin-bottom:1rem;"></i>
    <p style="color:var(--text-secondary);margin-bottom:1.25rem;">No projects yet. Create your first project.</p>
    <a href="/projects/codexpro/editor/new" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Create First Project
    </a>
</div>

<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
    <?php foreach ($projects as $project): ?>
    <div class="card" style="margin-bottom:0;cursor:pointer;transition:border-color .3s,box-shadow .3s,transform .3s;"
         onclick="location.href='/projects/codexpro/editor/<?= (int)$project['id'] ?>'">

        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.625rem;">
            <h3 style="font-size:.95rem;font-weight:700;color:var(--cx-primary);margin:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?= htmlspecialchars($project['name']) ?>
            </h3>
            <span style="background:rgba(0,240,255,.1);color:var(--cx-primary);border-radius:12px;font-size:.7rem;font-weight:600;padding:.15rem .5rem;flex-shrink:0;">
                <?= htmlspecialchars($project['language'] ?? 'HTML') ?>
            </span>
        </div>

        <?php if (!empty($project['description'])): ?>
        <p style="font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
            <?= htmlspecialchars($project['description']) ?>
        </p>
        <?php endif; ?>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:.625rem;border-top:1px solid var(--border-color);font-size:.75rem;color:var(--text-secondary);margin-bottom:.75rem;">
            <span class="relative-time" data-time="<?= strtotime($project['updated_at']) ?>"></span>
            <?php if (isset($project['visibility'])): ?>
            <span style="padding:.15rem .45rem;border-radius:8px;font-size:.68rem;font-weight:600;background:<?= $project['visibility'] === 'public' ? 'rgba(0,255,136,.1)' : 'rgba(255,255,255,.06)' ?>;color:<?= $project['visibility'] === 'public' ? 'var(--cx-secondary)' : 'var(--text-secondary)' ?>;">
                <?= ucfirst($project['visibility']) ?>
            </span>
            <?php endif; ?>
        </div>

        <div style="display:flex;gap:.375rem;flex-wrap:wrap;" onclick="event.stopPropagation();">
            <button onclick="toggleProjectQuickEdit(<?= (int)$project['id'] ?>)" class="btn btn-secondary btn-sm" style="flex:1;justify-content:center;min-width:100px;">
                <i class="fas fa-sliders-h"></i> Config
            </button>
            <a href="/projects/codexpro/editor/<?= (int)$project['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;min-width:100px;">
                <i class="fas fa-pen-to-square"></i> Edit
            </a>
            <a href="/projects/codexpro/projects/<?= (int)$project['id'] ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-eye"></i>
            </a>
            <button onclick="deleteProject(<?= (int)$project['id'] ?>)" class="btn btn-sm" style="background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.25);">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <!-- Quick Edit Panel -->
        <div id="projectQuickEditPanel<?= $project['id'] ?>" class="quick-edit-panel" style="display:none;margin-top:1rem;" onclick="event.stopPropagation();">
            <h4 style="color:var(--cx-primary);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
                <i class="fas fa-sliders-h"></i> Quick Configuration
            </h4>
            <div class="form-group">
                <label for="quickName<?= $project['id'] ?>">Project Name</label>
                <input type="text" id="quickName<?= $project['id'] ?>" class="form-control" value="<?= htmlspecialchars($project['name']) ?>" onclick="event.stopPropagation();">
            </div>
            <div class="form-group">
                <label for="quickDesc<?= $project['id'] ?>">Description</label>
                <textarea id="quickDesc<?= $project['id'] ?>" class="form-control" rows="2" onclick="event.stopPropagation();"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="toggle-label" onclick="event.stopPropagation();">
                    <input type="checkbox" id="quickPublicProject<?= $project['id'] ?>" <?= ($project['visibility'] ?? 'private') === 'public' ? 'checked' : '' ?> onclick="event.stopPropagation();">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">Public Project</span>
                </label>
            </div>
            <div class="form-actions">
                <button onclick="saveProjectQuickEdit(<?= $project['id'] ?>)" class="btn btn-primary btn-sm">
                    <i class="fas fa-floppy-disk"></i> Save
                </button>
                <button onclick="toggleProjectQuickEdit(<?= $project['id'] ?>)" class="btn btn-secondary btn-sm">
                    <i class="fas fa-xmark"></i> Cancel
                </button>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<script>
if (typeof updateRelativeTimes === 'function') {
    updateRelativeTimes();
    setInterval(updateRelativeTimes, 60000);
}
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
