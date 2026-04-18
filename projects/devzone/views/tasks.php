<?php
/**
 * DevZone – My Tasks view
 */
$currentView = 'tasks';
$filterStatus   = $_GET['status']   ?? '';
$filterPriority = $_GET['priority'] ?? '';
?>

<div class="page-header">
    <h1><i class="fas fa-list-check" style="-webkit-text-fill-color:transparent;"></i> My Tasks</h1>
    <p>All tasks assigned to you or created by you</p>
</div>

<!-- Filter bar -->
<form method="GET" style="display:flex;gap:.625rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center;">
    <select name="status" class="form-control form-select" style="width:auto;">
        <option value="">All statuses</option>
        <option value="todo"        <?= $filterStatus === 'todo'        ? 'selected' : '' ?>>To Do</option>
        <option value="in-progress" <?= $filterStatus === 'in-progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="done"        <?= $filterStatus === 'done'        ? 'selected' : '' ?>>Done</option>
        <option value="blocked"     <?= $filterStatus === 'blocked'     ? 'selected' : '' ?>>Blocked</option>
    </select>
    <select name="priority" class="form-control form-select" style="width:auto;">
        <option value="">All priorities</option>
        <option value="low"    <?= $filterPriority === 'low'    ? 'selected' : '' ?>>Low</option>
        <option value="medium" <?= $filterPriority === 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="high"   <?= $filterPriority === 'high'   ? 'selected' : '' ?>>High</option>
        <option value="urgent" <?= $filterPriority === 'urgent' ? 'selected' : '' ?>>Urgent</option>
    </select>
    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Filter</button>
    <?php if ($filterStatus || $filterPriority): ?>
    <a href="/projects/devzone/tasks" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Clear</a>
    <?php endif; ?>
</form>

<div class="card" style="padding:0;overflow:hidden;">
    <?php if (empty($tasks)): ?>
    <div style="text-align:center;padding:3rem 1.5rem;">
        <i class="fa-solid fa-inbox" style="font-size:3rem;background:linear-gradient(135deg,var(--dz-primary),var(--dz-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:block;margin-bottom:.75rem;"></i>
        <p style="color:var(--text-secondary);">No tasks found<?= ($filterStatus || $filterPriority) ? ' for current filters' : '' ?>.</p>
        <?php if ($filterStatus || $filterPriority): ?>
        <a href="/projects/devzone/tasks" class="btn btn-secondary" style="margin-top:1rem;">Clear filters</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="dz-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Board</th>
                    <th>Priority</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <?php
                $p = strtolower($task['priority'] ?? 'medium');
                $pClass = ['low'=>'badge-low','medium'=>'badge-medium','high'=>'badge-high','urgent'=>'badge-urgent'][$p] ?? 'badge-medium';
                $s = strtolower(str_replace(' ','-',$task['col_name'] ?? 'todo'));
                $sClass = str_contains($s,'done')||str_contains($s,'complet') ? 'badge-done' :
                    (str_contains($s,'block') ? 'badge-blocked' :
                    (str_contains($s,'progress') ? 'badge-in-progress' : 'badge-todo'));
                $overdue = !empty($task['due_date']) && !str_contains($s,'done') && strtotime($task['due_date']) < strtotime('today');
                ?>
                <tr>
                    <td style="max-width:280px;">
                        <div style="font-weight:600;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($task['title']) ?>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                        <div style="font-size:.73rem;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px;">
                            <?= htmlspecialchars($task['description']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/projects/devzone/boards/<?= (int)$task['board_id'] ?>" style="font-size:.82rem;color:var(--dz-primary);text-decoration:none;display:flex;align-items:center;gap:.375rem;">
                            <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($task['board_color'] ?? '#ff2ec4') ?>;flex-shrink:0;"></span>
                            <?= htmlspecialchars($task['board_name'] ?? '') ?>
                        </a>
                    </td>
                    <td><span class="badge <?= $pClass ?>"><?= ucfirst($p) ?></span></td>
                    <td style="font-size:.78rem;<?= $overdue ? 'color:var(--dz-danger);font-weight:600;' : 'color:var(--text-secondary);' ?>">
                        <?= !empty($task['due_date']) ? htmlspecialchars(substr($task['due_date'],0,10)) : '—' ?>
                        <?php if ($overdue): ?><i class="fa-solid fa-triangle-exclamation" style="font-size:.7rem;"></i><?php endif; ?>
                    </td>
                    <td><span class="badge <?= $sClass ?>"><?= htmlspecialchars($task['col_name'] ?? 'To Do') ?></span></td>
                    <td>
                        <a href="/projects/devzone/boards/<?= (int)$task['board_id'] ?>" class="btn btn-secondary btn-sm" title="Open board">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
