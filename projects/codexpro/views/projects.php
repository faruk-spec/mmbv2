<?php
$title = 'My Projects';
$currentPage = 'projects';
$pageTitle = 'My Projects';

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">All Projects</h2>
        <a href="/projects/codexpro/editor/new" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            New Project
        </a>
    </div>
    
    <?php if (empty($projects)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
            <i class="fas fa-folder-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.3;"></i>
            <h3 style="margin-bottom: 10px;">No projects yet</h3>
            <p style="margin-bottom: 20px;">Create your first project to get started!</p>
            <a href="/projects/codexpro/editor/new" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Create Project
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <?php foreach ($projects as $project): ?>
                <div style="padding: 20px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 12px; transition: var(--transition); cursor: pointer;" onmouseover="this.style.borderColor='var(--cyan)'" onmouseout="this.style.borderColor='var(--border-color)'" onclick="window.location.href='/projects/codexpro/editor/<?= $project['id'] ?>'">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <h3 style="font-size: 18px; color: var(--cyan); margin: 0;"><?= htmlspecialchars($project['name']) ?></h3>
                        <span style="padding: 4px 12px; background: rgba(0, 240, 255, 0.1); color: var(--cyan); border-radius: 12px; font-size: 12px; font-weight: 500;">
                            <?= htmlspecialchars($project['language'] ?? 'HTML') ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($project['description'])): ?>
                        <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 16px; min-height: 40px;">
                            <?= htmlspecialchars(substr($project['description'], 0, 100)) ?><?= strlen($project['description']) > 100 ? '...' : '' ?>
                        </p>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid var(--border-color); font-size: 13px; color: var(--text-secondary);">
                        <span title="<?= date('F j, Y g:i A', strtotime($project['updated_at'])) ?>">
                            <span class="relative-time" data-time="<?= strtotime($project['updated_at']) ?>"></span>
                        </span>
                        <?php if (isset($project['visibility'])): ?>
                            <span class="badge" style="padding: 4px 8px; border-radius: 8px; font-size: 11px; background: <?= $project['visibility'] === 'public' ? 'rgba(0, 255, 136, 0.1)' : 'rgba(255, 255, 255, 0.1)' ?>; color: <?= $project['visibility'] === 'public' ? 'var(--green)' : 'var(--text-secondary)' ?>;">
                                <?= ucfirst($project['visibility']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap;" onclick="event.stopPropagation();">
                        <button onclick="toggleProjectQuickEdit(<?= (int)$project['id'] ?>)" class="btn btn-secondary btn-project-action" style="font-size: 14px; padding: 8px 12px; flex: 1; min-width: 120px;">
                            <i class="fas fa-sliders-h"></i>
                            <span class="btn-text">Quick Config</span>
                        </button>
                        <a href="/projects/codexpro/editor/<?= (int)$project['id'] ?>" class="btn btn-primary btn-project-action" style="flex: 1; justify-content: center; font-size: 14px; padding: 8px 12px; min-width: 120px;">
                            <i class="fas fa-edit"></i>
                            <span class="btn-text">Edit</span>
                        </a>
                        <a href="/projects/codexpro/projects/<?= (int)$project['id'] ?>" class="btn btn-secondary btn-project-action" style="font-size: 14px; padding: 8px 12px;">
                            <i class="fas fa-eye"></i>
                            <span class="btn-text-desktop">View</span>
                        </a>
                        <button onclick="deleteProject(<?= (int)$project['id'] ?>)" class="btn btn-danger btn-project-action" style="font-size: 14px; padding: 8px 12px; background: rgba(239, 68, 68, 0.2); color: #ef4444;">
                            <i class="fas fa-trash"></i>
                            <span class="btn-text-desktop">Delete</span>
                        </button>
                    </div>
                    
                    <!-- Quick Edit Panel for Project -->
                    <div id="projectQuickEditPanel<?= $project['id'] ?>" class="quick-edit-panel" style="display:none; margin-top: 16px;" onclick="event.stopPropagation();">
                        <div class="quick-edit-content">
                            <h4 style="color: var(--cyan); margin-bottom: 16px;">
                                <i class="fas fa-sliders-h"></i> Quick Configuration
                            </h4>
                            <div class="quick-edit-form">
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
                                    <button onclick="saveProjectQuickEdit(<?= $project['id'] ?>)" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                    <button onclick="toggleProjectQuickEdit(<?= $project['id'] ?>)" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // All functions are now in layout.php
    // Just initialize relative times
    if (typeof updateRelativeTimes === "function") {
        updateRelativeTimes();
        setInterval(updateRelativeTimes, 60000);
    }
</script>

<style>
/* Mobile responsive styles for projects page */
@media (max-width: 768px) {
    .btn-project-action {
        padding: 8px 10px;
        font-size: 13px;
    }
    
    .btn-project-action .btn-text-desktop {
        display: none;
    }
    
    .btn-project-action i {
        margin-right: 0;
    }
}

@media (max-width: 480px) {
    .btn-project-action {
        min-width: 40px;
        flex: unset;
    }
    
    .btn-project-action .btn-text {
        display: none;
    }
    
    .btn-project-action:nth-child(1),
    .btn-project-action:nth-child(2) {
        flex: 1;
        min-width: 80px;
    }
    
    .btn-project-action:nth-child(1) .btn-text,
    .btn-project-action:nth-child(2) .btn-text {
        display: inline;
    }
}

.btn-danger:hover {
    background: rgba(239, 68, 68, 0.3);
    transform: translateY(-2px);
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
