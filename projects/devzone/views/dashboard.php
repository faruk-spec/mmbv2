<?php
/**
 * DevZone Dashboard View (ConvertX-style)
 */
$currentView = 'dashboard';
?>

<!-- Page header -->
<div class="page-header">
    <h1><i class="fas fa-terminal" style="-webkit-text-fill-color:transparent;"></i> DevZone</h1>
    <p>Developer workspace — boards, tasks, and team collaboration</p>
</div>

<!-- ── Stats ── -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon"><i class="fas fa-columns"></i></span>
        <span class="value"><?= (int)($stats['boards'] ?? 0) ?></span>
        <span class="label">My Boards</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><i class="fas fa-list-check"></i></span>
        <span class="value"><?= (int)($stats['tasks'] ?? 0) ?></span>
        <span class="label">Active Tasks</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><i class="fas fa-clock"></i></span>
        <span class="value"><?= (int)($stats['due_soon'] ?? 0) ?></span>
        <span class="label">Due This Week</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><i class="fas fa-circle-check"></i></span>
        <span class="value"><?= (int)($stats['done_this_week'] ?? 0) ?></span>
        <span class="label">Completed</span>
    </div>
</div>

<!-- ── Quick actions ── -->
<div class="dz-quick-row">
    <a href="/projects/devzone/boards/create"
       class="dz-quick-card"
       style="background:linear-gradient(135deg,var(--dz-primary),#c4006e);box-shadow:0 4px 16px rgba(255,46,196,.35);">
        <i class="fa-solid fa-plus qc-icon"></i>
        <strong>New Board</strong>
        <p>Create a Kanban board</p>
    </a>
    <a href="/projects/devzone/tasks"
       class="dz-quick-card"
       style="background:linear-gradient(135deg,#7c3aed,var(--dz-secondary));box-shadow:0 4px 16px rgba(124,58,237,.3);">
        <i class="fa-solid fa-list-check qc-icon"></i>
        <strong>My Tasks</strong>
        <p>View assigned tasks</p>
    </a>
    <a href="/projects/devzone/boards"
       class="dz-quick-card"
       style="background:linear-gradient(135deg,#0891b2,#06b6d4);box-shadow:0 4px 16px rgba(8,145,178,.3);">
        <i class="fa-solid fa-columns qc-icon"></i>
        <strong>All Boards</strong>
        <p>Manage your boards</p>
    </a>
    <a href="/projects/devzone/settings"
       class="dz-quick-card"
       style="background:linear-gradient(135deg,#0891b2,var(--dz-success));box-shadow:0 4px 16px rgba(8,145,178,.3);">
        <i class="fa-solid fa-gear qc-icon"></i>
        <strong>Settings</strong>
        <p>Preferences &amp; workspace</p>
    </a>
</div>

<!-- ── Feature hub (like ConvertX AI hub) ── -->
<div class="card" style="border-color:var(--border-hover);background:linear-gradient(135deg,rgba(255,46,196,.05),rgba(0,240,255,.04));">
    <div class="card-header" style="border-color:rgba(255,46,196,.2);">
        <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--dz-primary),var(--dz-secondary));border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa-solid fa-terminal" style="color:#fff;font-size:1rem;"></i>
        </div>
        <div>
            <div style="font-weight:700;color:var(--text-primary);">DevZone Toolkit</div>
            <div style="font-size:.78rem;font-weight:400;color:var(--text-secondary);">All your developer workspace tools</div>
        </div>
        <span style="margin-left:auto;background:linear-gradient(135deg,var(--dz-primary),var(--dz-secondary));background-size:200% auto;animation:dz-shimmer 2.8s linear infinite;color:#fff;padding:.2rem .65rem;border-radius:20px;font-size:.7rem;font-weight:700;letter-spacing:.04em;">⚡ WORKSPACE</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;">
        <a href="/projects/devzone/boards" class="dz-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,var(--dz-primary),#c4006e);">
                <i class="fa-solid fa-columns"></i>
            </div>
            <div class="tile-title">Kanban Boards</div>
            <div class="tile-desc">Visualize your workflow</div>
        </a>

        <a href="/projects/devzone/tasks" class="dz-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#7c3aed,var(--dz-secondary));">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div class="tile-title">Task Tracker</div>
            <div class="tile-desc">Manage assigned work</div>
        </a>

        <a href="/projects/devzone/boards/create" class="dz-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4);">
                <i class="fa-solid fa-plus"></i>
            </div>
            <div class="tile-title">New Board</div>
            <div class="tile-desc">Start a fresh project</div>
        </a>

        <a href="/projects/devzone/settings" class="dz-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                <i class="fa-solid fa-gear"></i>
            </div>
            <div class="tile-title">Preferences</div>
            <div class="tile-desc">Workspace settings</div>
        </a>
    </div>
</div>

<!-- ── Recent boards ── -->
<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fa-solid fa-columns"></i> Recent Boards</span>
        <a href="/projects/devzone/boards" style="font-size:.8rem;color:var(--dz-primary);text-decoration:none;">View all →</a>
    </div>

    <?php if (empty($boards)): ?>
        <div style="text-align:center;padding:2.5rem 1rem;">
            <i class="fa-solid fa-inbox" style="font-size:3rem;background:linear-gradient(135deg,var(--dz-primary),var(--dz-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:.75rem;display:block;"></i>
            <p style="color:var(--text-secondary);font-size:.9rem;">No boards yet.</p>
            <a href="/projects/devzone/boards/create" class="btn btn-primary" style="margin-top:1rem;">
                <i class="fa-solid fa-plus"></i> Create your first board
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="dz-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Board</th>
                        <th>Tasks</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($boards as $b): ?>
                    <tr>
                        <td style="width:16px;">
                            <span class="board-color-dot" style="background:<?= htmlspecialchars($b['color'] ?? '#ff2ec4') ?>;"></span>
                        </td>
                        <td>
                            <a href="/projects/devzone/boards/<?= (int)$b['id'] ?>" style="font-weight:600;color:var(--text-primary);text-decoration:none;">
                                <?= htmlspecialchars($b['name']) ?>
                            </a>
                            <?php if (!empty($b['description'])): ?>
                            <div style="font-size:.75rem;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px;">
                                <?= htmlspecialchars($b['description']) ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-in-progress"><?= (int)($b['task_count'] ?? 0) ?> tasks</span>
                        </td>
                        <td style="color:var(--text-secondary);font-size:.78rem;">
                            <?= htmlspecialchars(substr($b['updated_at'] ?? $b['created_at'] ?? '', 0, 10)) ?>
                        </td>
                        <td>
                            <a href="/projects/devzone/boards/<?= (int)$b['id'] ?>" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-arrow-right"></i> Open
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
