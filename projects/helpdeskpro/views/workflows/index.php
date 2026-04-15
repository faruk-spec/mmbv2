<?php
$csrfToken = \Core\Security::generateCsrfToken();
ob_start();
?>
<div style="margin-bottom:1.2rem;">
    <h2 style="margin:0;font-size:1.25rem;"><i class="fas fa-diagram-project" style="color:var(--hp-primary);margin-right:.4rem;"></i> Workflows</h2>
</div>

<div class="card" style="margin-bottom:1.2rem;">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Create Workflow Rule</h3>
    <form method="POST" action="/projects/helpdeskpro/workflows/create">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="grid g2" style="margin-bottom:.75rem;">
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Name *</label>
                <input type="text" name="name" placeholder="Workflow name" required maxlength="150">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Description</label>
                <input type="text" name="description" placeholder="Optional description" maxlength="500">
            </div>
        </div>
        <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:.5rem;padding:.75rem;margin-bottom:.75rem;">
            <p style="margin:0 0 .5rem;font-size:.82rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;">WHEN (Condition)</p>
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:140px;">
                    <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Type</label>
                    <select name="condition_type">
                        <option value="priority">Priority equals</option>
                        <option value="channel">Channel equals</option>
                        <option value="status">Status equals</option>
                    </select>
                </div>
                <div style="flex:1;min-width:140px;">
                    <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Value</label>
                    <input type="text" name="condition_value" placeholder="e.g. urgent">
                </div>
            </div>
        </div>
        <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:.5rem;padding:.75rem;margin-bottom:.75rem;">
            <p style="margin:0 0 .5rem;font-size:.82rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;">THEN (Action)</p>
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:140px;">
                    <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Type</label>
                    <select name="action_type">
                        <option value="assign_agent">Assign to Agent</option>
                        <option value="auto_reply">Auto Reply</option>
                        <option value="send_email">Send Email</option>
                        <option value="escalate">Escalate</option>
                    </select>
                </div>
                <div style="flex:2;min-width:160px;">
                    <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:.3rem;">Value / Message</label>
                    <input type="text" name="action_value" placeholder="e.g. agent email or message text">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create Workflow</button>
    </form>
</div>

<div class="card">
    <h3 style="margin:0 0 .75rem;font-size:1rem;">Workflow Rules</h3>
    <?php if (empty($workflows)): ?>
        <p style="color:var(--text-secondary);text-align:center;padding:1.5rem;">No workflow rules defined.</p>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:.6rem;">
    <?php foreach ($workflows as $wf): ?>
        <?php
        $conditions = json_decode((string)($wf['conditions'] ?? '[]'), true) ?: [];
        $actions    = json_decode((string)($wf['actions'] ?? '[]'), true) ?: [];
        $condPreview = implode(', ', array_map(fn($c) => ($c['type'] ?? '') . '=' . ($c['value'] ?? ''), $conditions));
        $actPreview  = implode(', ', array_map(fn($a) => ($a['type'] ?? '') . ': ' . ($a['value'] ?? ''), $actions));
        ?>
        <div style="border:1px solid var(--border);border-radius:.6rem;padding:.75rem;background:var(--bg-card);">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;">
                        <strong><?= htmlspecialchars($wf['name']) ?></strong>
                        <span class="badge" style="<?= $wf['is_active'] ? 'background:rgba(16,185,129,.12);color:#34d399;' : 'background:rgba(107,114,128,.12);color:#9ca3af;' ?>">
                            <?= $wf['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <?php if (!empty($wf['description'])): ?>
                    <p style="margin:0 0 .3rem;color:var(--text-secondary);font-size:.82rem;"><?= htmlspecialchars(mb_substr((string)$wf['description'], 0, 100)) ?></p>
                    <?php endif; ?>
                    <p style="margin:0;font-size:.8rem;color:var(--text-secondary);">
                        <strong>IF:</strong> <?= htmlspecialchars($condPreview) ?> &nbsp;|&nbsp;
                        <strong>THEN:</strong> <?= htmlspecialchars($actPreview) ?>
                    </p>
                </div>
                <div style="display:flex;gap:.4rem;">
                    <form method="POST" action="/projects/helpdeskpro/workflows/toggle/<?= (int)$wf['id'] ?>" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn btn-secondary" style="padding:.3rem .6rem;font-size:.8rem;">
                            <i class="fas fa-<?= $wf['is_active'] ? 'pause' : 'play' ?>"></i> <?= $wf['is_active'] ? 'Disable' : 'Enable' ?>
                        </button>
                    </form>
                    <form method="POST" action="/projects/helpdeskpro/workflows/delete/<?= (int)$wf['id'] ?>" onsubmit="return confirm('Delete workflow?');" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3);padding:.3rem .6rem;font-size:.8rem;"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require PROJECT_PATH . '/views/layout.php';
