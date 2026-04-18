<?php
/**
 * DevZone – Board Detail View (Kanban)
 */
$currentView = 'board';
$board    = $board   ?? [];
$columns  = $columns ?? [];
$colMap   = [];
foreach ($columns as $col) {
    $colMap[$col['id']] = $col;
}
?>

<!-- Board header -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;">
    <div>
        <div style="display:flex;align-items:center;gap:.625rem;">
            <span style="width:12px;height:12px;border-radius:50%;background:<?= htmlspecialchars($board['color'] ?? '#ff2ec4') ?>;flex-shrink:0;box-shadow:0 0 8px <?= htmlspecialchars($board['color'] ?? '#ff2ec4') ?>;"></span>
            <h1 style="font-size:1.5rem;font-weight:800;background:linear-gradient(135deg,var(--dz-primary),var(--dz-secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                <?= htmlspecialchars($board['name']) ?>
            </h1>
        </div>
        <?php if (!empty($board['description'])): ?>
        <p style="font-size:.85rem;color:var(--text-secondary);margin-top:.25rem;"><?= htmlspecialchars($board['description']) ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <button onclick="openTaskModal()" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Add Task
        </button>
        <a href="/projects/devzone/boards/<?= (int)$board['id'] ?>/edit" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
        <a href="/projects/devzone/boards" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Boards
        </a>
    </div>
</div>

<div class="dz-separator"></div>

<!-- Stats bar -->
<?php
$totalTasks = 0;
$doneTasks  = 0;
foreach ($columns as $col) {
    $totalTasks += count($col['tasks'] ?? []);
    if (in_array(strtolower($col['name']), ['done','complete','completed','closed'], true)) {
        $doneTasks += count($col['tasks'] ?? []);
    }
}
$progress = $totalTasks > 0 ? round($doneTasks / $totalTasks * 100) : 0;
?>
<div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <span style="font-size:.8rem;color:var(--text-secondary);">
        <strong style="color:var(--text-primary);"><?= $totalTasks ?></strong> tasks total
    </span>
    <span style="font-size:.8rem;color:var(--text-secondary);">
        <strong style="color:var(--dz-success);"><?= $doneTasks ?></strong> done
    </span>
    <div style="flex:1;min-width:120px;background:var(--border-color);border-radius:1rem;height:6px;max-width:200px;overflow:hidden;">
        <div style="width:<?= $progress ?>%;height:100%;background:linear-gradient(90deg,var(--dz-primary),var(--dz-secondary));border-radius:1rem;transition:width .5s;"></div>
    </div>
    <span style="font-size:.8rem;font-weight:700;color:var(--dz-primary);"><?= $progress ?>%</span>
</div>

<!-- Kanban board -->
<div class="kanban-board">
    <?php foreach ($columns as $col): ?>
    <div class="kanban-col" id="col-<?= (int)$col['id'] ?>">
        <div class="kanban-col-header">
            <span class="kanban-col-title">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($col['color'] ?? 'var(--dz-primary)') ?>;flex-shrink:0;"></span>
                <?= htmlspecialchars($col['name']) ?>
            </span>
            <span class="kanban-col-count"><?= count($col['tasks'] ?? []) ?></span>
        </div>

        <?php foreach ($col['tasks'] ?? [] as $task): ?>
        <div class="task-card" onclick="openEditTaskModal(<?= (int)$task['id'] ?>)">
            <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
            <div class="task-meta">
                <?php
                $p = strtolower($task['priority'] ?? 'medium');
                $pClass = ['low' => 'badge-low', 'medium' => 'badge-medium', 'high' => 'badge-high', 'urgent' => 'badge-urgent'][$p] ?? 'badge-medium';
                ?>
                <span class="badge <?= $pClass ?>"><?= ucfirst($p) ?></span>
                <?php if (!empty($task['due_date'])): ?>
                <?php $overdue = strtotime($task['due_date']) < time(); ?>
                <span class="task-due <?= $overdue ? 'overdue' : '' ?>">
                    <i class="fa-solid fa-clock"></i> <?= htmlspecialchars(substr($task['due_date'], 0, 10)) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Add task to this column inline -->
        <button onclick="openTaskModal(<?= (int)$col['id'] ?>)"
                style="background:none;border:1px dashed var(--border-color);border-radius:.5rem;padding:.5rem;color:var(--text-secondary);cursor:pointer;font-size:.8rem;display:flex;align-items:center;gap:.375rem;width:100%;justify-content:center;transition:border-color .2s,color .2s;margin-top:.25rem;"
                onmouseover="this.style.borderColor='var(--dz-primary)';this.style.color='var(--dz-primary)'"
                onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-secondary)'">
            <i class="fa-solid fa-plus"></i> Add task
        </button>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Add / Edit Task Modal ── -->
<div class="dz-modal-overlay" id="taskModal">
    <div class="dz-modal">
        <div class="dz-modal-header">
            <span class="dz-modal-title" id="taskModalTitle">Add Task</span>
            <button class="dz-modal-close" onclick="closeTaskModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="taskForm" action="/projects/devzone/tasks/store">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <input type="hidden" name="board_id" value="<?= (int)$board['id'] ?>">
            <input type="hidden" name="task_id" id="taskId" value="">
            <input type="hidden" name="action" id="taskAction" value="store">

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-heading" style="color:var(--dz-primary);"></i> Title *</label>
                <input type="text" name="title" id="taskTitle" class="form-control" required maxlength="200" placeholder="What needs to be done?">
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-align-left" style="color:var(--dz-primary);"></i> Description</label>
                <textarea name="description" id="taskDescription" class="form-control" rows="2" maxlength="1000" style="resize:vertical;" placeholder="Optional details…"></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label"><i class="fa-solid fa-columns" style="color:var(--dz-primary);"></i> Column</label>
                    <select name="column_id" id="taskColumn" class="form-control form-select">
                        <?php foreach ($columns as $col): ?>
                        <option value="<?= (int)$col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label"><i class="fa-solid fa-flag" style="color:var(--dz-primary);"></i> Priority</label>
                    <select name="priority" id="taskPriority" class="form-control form-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label"><i class="fa-solid fa-calendar" style="color:var(--dz-primary);"></i> Due Date</label>
                <input type="date" name="due_date" id="taskDueDate" class="form-control">
            </div>

            <div style="display:flex;gap:.5rem;margin-top:.5rem;flex-wrap:wrap;" id="taskFormActions">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Task</button>
                <button type="button" onclick="closeTaskModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" id="deleteTaskBtn" onclick="deleteTask()"
                        class="btn btn-danger" style="margin-left:auto;display:none;">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var boardId  = <?= (int)$board['id'] ?>;
var taskData = (function () {
    // Build a map of task_id → task (with column_id) for use in the edit modal
    <?php
    $taskMap = [];
    foreach ($columns as $col) {
        foreach ($col['tasks'] ?? [] as $task) {
            $taskMap[(int)$task['id']] = array_merge($task, ['column_id' => (int)$col['id']]);
        }
    }
    ?>
    return <?= json_encode($taskMap ?: new stdClass()) ?>;
}());

function openTaskModal(columnId) {
    document.getElementById('taskModalTitle').textContent = 'Add Task';
    document.getElementById('taskId').value       = '';
    document.getElementById('taskTitle').value    = '';
    document.getElementById('taskDescription').value = '';
    document.getElementById('taskDueDate').value  = '';
    document.getElementById('taskPriority').value = 'medium';
    document.getElementById('taskAction').value   = 'store';
    document.getElementById('taskForm').action    = '/projects/devzone/tasks/store';
    document.getElementById('deleteTaskBtn').style.display = 'none';
    if (columnId) document.getElementById('taskColumn').value = columnId;
    document.getElementById('taskModal').classList.add('open');
    setTimeout(function(){ document.getElementById('taskTitle').focus(); }, 120);
}

function openEditTaskModal(id) {
    var t = taskData[id];
    if (!t) return;
    document.getElementById('taskModalTitle').textContent  = 'Edit Task';
    document.getElementById('taskId').value      = id;
    document.getElementById('taskTitle').value   = t.title || '';
    document.getElementById('taskDescription').value = t.description || '';
    document.getElementById('taskDueDate').value = t.due_date ? t.due_date.substr(0,10) : '';
    document.getElementById('taskPriority').value = t.priority || 'medium';
    document.getElementById('taskColumn').value  = t.column_id || '';
    document.getElementById('taskAction').value  = 'update';
    document.getElementById('taskForm').action   = '/projects/devzone/tasks/' + id + '/update';
    document.getElementById('deleteTaskBtn').style.display = 'inline-flex';
    document.getElementById('taskModal').classList.add('open');
}

function closeTaskModal() {
    document.getElementById('taskModal').classList.remove('open');
}

function deleteTask() {
    var id = document.getElementById('taskId').value;
    if (!id) return;
    if (!confirm('Delete this task?')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '/projects/devzone/tasks/' + id + '/delete';
    var csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_csrf_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}

document.getElementById('taskModal').addEventListener('click', function(e){
    if (e.target === this) closeTaskModal();
});
</script>
