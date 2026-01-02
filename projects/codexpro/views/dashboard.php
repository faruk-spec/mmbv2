<?php
$title = 'Dashboard';
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';

ob_start();
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card" onclick="window.location.href='/projects/codexpro/projects'" 
         onkeydown="if(event.key==='Enter'||event.key===' '){window.location.href='/projects/codexpro/projects'}"
         tabindex="0" role="button" aria-label="View all projects"
         style="cursor: pointer; transition: all 0.3s;">
        <div class="stat-icon cyan">
            <i class="fas fa-folder"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['total_projects'] ?? 0 ?></h3>
            <p>Total Projects</p>
        </div>
    </div>
    
    <div class="stat-card" onclick="window.location.href='/projects/codexpro/snippets'" 
         onkeydown="if(event.key==='Enter'||event.key===' '){window.location.href='/projects/codexpro/snippets'}"
         tabindex="0" role="button" aria-label="View all code snippets"
         style="cursor: pointer; transition: all 0.3s;">
        <div class="stat-icon magenta">
            <i class="fas fa-code"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['total_snippets'] ?? 0 ?></h3>
            <p>Code Snippets</p>
        </div>
    </div>
    
    <div class="stat-card" onclick="window.location.href='/projects/codexpro/templates'" 
         onkeydown="if(event.key==='Enter'||event.key===' '){window.location.href='/projects/codexpro/templates'}"
         tabindex="0" role="button" aria-label="Browse templates"
         style="cursor: pointer; transition: all 0.3s;">
        <div class="stat-icon green">
            <i class="fas fa-file-code"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['total_templates'] ?? 0 ?></h3>
            <p>Templates</p>
        </div>
    </div>
    
    <div class="stat-card" onclick="window.location.href='/projects/codexpro/projects'" 
         onkeydown="if(event.key==='Enter'||event.key===' '){window.location.href='/projects/codexpro/projects'}"
         tabindex="0" role="button" aria-label="View recent edits"
         style="cursor: pointer; transition: all 0.3s;">
        <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['recent_edits'] ?? 0 ?></h3>
            <p>Recent Edits</p>
        </div>
    </div>
</div>

<style>
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 240, 255, 0.3);
    border-color: var(--cyan);
}

.stat-card:focus {
    outline: 2px solid var(--cyan);
    outline-offset: 2px;
}
</style>

<!-- Quick Actions Card -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Quick Actions</h2>
    </div>
    <div class="quick-actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
        <a href="/projects/codexpro/editor/new" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            New Code Editor
        </a>
        <a href="/projects/codexpro/projects" class="btn btn-secondary">
            <i class="fas fa-folder"></i>
            View Projects
        </a>
        <a href="/projects/codexpro/snippets" class="btn btn-secondary">
            <i class="fas fa-code"></i>
            View Snippets
        </a>
        <a href="/projects/codexpro/templates" class="btn btn-secondary">
            <i class="fas fa-file-code"></i>
            Browse Templates
        </a>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .quick-actions-grid {
        grid-template-columns: 1fr !important;
    }
    
    .quick-actions-grid .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Recent Projects Card -->
<?php if (!empty($recentProjects)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Projects</h2>
        <a href="/projects/codexpro/projects" class="btn btn-secondary" style="font-size: 14px; padding: 8px 16px;">
            View All
        </a>
    </div>
    <div style="display: grid; gap: 16px;">
        <?php foreach (array_slice($recentProjects, 0, 5) as $project): ?>
        <div style="padding: 16px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 16px; margin-bottom: 4px;"><?= htmlspecialchars($project['name']) ?></h3>
                <p style="font-size: 13px; color: var(--text-secondary);">
                    <?= htmlspecialchars($project['language']) ?> • Updated <?= !empty($project['updated_at']) ? date('M j, Y', strtotime($project['updated_at'])) : 'Never' ?>
                </p>
            </div>
            <a href="/projects/codexpro/editor/<?= $project['id'] ?>" class="btn btn-primary" style="font-size: 14px; padding: 8px 16px;">
                <i class="fas fa-edit"></i>
                Edit
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
