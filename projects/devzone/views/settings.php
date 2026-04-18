<?php
/**
 * DevZone Settings View
 */

$content = '';
ob_start();
?>

<h1 class="dz-page-title" style="display:flex;align-items:center;gap:.5rem;">
    <i class="fas fa-cog" style="color:var(--dz-accent);"></i> DevZone Settings
</h1>
<p class="dz-page-subtitle">Manage your workspace preferences and notifications</p>

<div class="dz-grid dz-grid-2" style="align-items:start;">
    <section class="dz-card" style="margin-bottom:0;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
            <i class="fas fa-sliders-h" style="color:var(--dz-primary);"></i> Preferences
        </h2>

        <form method="POST" action="/projects/devzone/settings" style="display:grid;gap:1rem;">
            <?= \Core\Security::csrfField() ?>

            <div>
                <label for="default_board_color" style="display:block;font-size:.82rem;color:var(--text-secondary);margin-bottom:.35rem;">Default board color</label>
                <input id="default_board_color" name="default_board_color" type="color"
                       value="<?= htmlspecialchars($settings['default_board_color'] ?? '#00f0ff') ?>"
                       style="width:64px;height:42px;padding:.15rem;border:1px solid var(--border-color);border-radius:.5rem;background:var(--bg-secondary);">
            </div>

            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                <input type="checkbox" name="email_notifications" value="1" <?= !empty($settings['email_notifications']) ? 'checked' : '' ?>>
                <span>Email notifications</span>
            </label>

            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                <input type="checkbox" name="task_reminders" value="1" <?= !empty($settings['task_reminders']) ? 'checked' : '' ?>>
                <span>Task reminders</span>
            </label>

            <div style="display:flex;gap:.6rem;flex-wrap:wrap;padding-top:.35rem;">
                <button type="submit" class="dz-btn dz-btn-primary">
                    <i class="fas fa-save"></i> Save settings
                </button>
                <a href="/projects/devzone" class="dz-btn dz-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to dashboard
                </a>
            </div>
        </form>
    </section>

    <section class="dz-card" style="margin-bottom:0;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
            <i class="fas fa-chart-pie" style="color:var(--dz-accent);"></i> Workspace Summary
        </h2>

        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;">
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.65rem;padding:.8rem;">
                <div style="font-size:1.15rem;font-weight:800;color:var(--dz-primary);"><?= (int)($stats['boards'] ?? 0) ?></div>
                <div style="font-size:.76rem;color:var(--text-secondary);">Boards</div>
            </div>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.65rem;padding:.8rem;">
                <div style="font-size:1.15rem;font-weight:800;color:var(--dz-accent);"><?= (int)($stats['tasks'] ?? 0) ?></div>
                <div style="font-size:.76rem;color:var(--text-secondary);">Active tasks</div>
            </div>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.65rem;padding:.8rem;">
                <div style="font-size:1.15rem;font-weight:800;color:var(--dz-warning);"><?= (int)($stats['due_soon'] ?? 0) ?></div>
                <div style="font-size:.76rem;color:var(--text-secondary);">Due this week</div>
            </div>
            <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.65rem;padding:.8rem;">
                <div style="font-size:1.15rem;font-weight:800;color:var(--dz-success);"><?= (int)($stats['members'] ?? 0) ?></div>
                <div style="font-size:.76rem;color:var(--text-secondary);">Team members</div>
            </div>
        </div>

        <p style="margin-top:1rem;font-size:.8rem;color:var(--text-secondary);line-height:1.5;">
            These values are live from your DevZone data tables and update automatically.
        </p>
    </section>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
