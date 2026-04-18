<?php
/**
 * DevZone – Settings View (ConvertX-style)
 */
$currentView = 'settings';
$settings    = $settings ?? [];
$user        = $user     ?? \Core\Auth::user();
?>

<div class="page-header">
    <h1><i class="fa-solid fa-gear" style="-webkit-text-fill-color:transparent;"></i> Settings</h1>
    <p>Manage your DevZone workspace preferences</p>
</div>

<form method="POST" action="/projects/devzone/settings" style="display:contents;">
    <?= \Core\Security::csrfField() ?>

    <!-- Account info (read-only) -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-circle-user"></i> Account
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fa-solid fa-user" style="color:var(--dz-primary);"></i> Username</label>
                <input type="text" class="form-control"
                       value="<?= htmlspecialchars($user['username'] ?? $user['name'] ?? 'User') ?>"
                       readonly style="opacity:.7;cursor:default;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label"><i class="fa-solid fa-envelope" style="color:var(--dz-primary);"></i> Email</label>
                <input type="email" class="form-control"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                       readonly style="opacity:.7;cursor:default;">
            </div>
        </div>
    </div>

    <!-- Workspace preferences -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-sliders"></i> Workspace Preferences
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;">
            <!-- Default board color -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">
                    <i class="fa-solid fa-palette" style="color:var(--dz-primary);"></i> Default Board Color
                </label>
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                    <input type="color" name="default_board_color" id="defColor"
                           value="<?= htmlspecialchars($settings['default_board_color'] ?? '#ff2ec4') ?>"
                           style="width:56px;height:40px;padding:.15rem;border:1px solid var(--border-color);border-radius:.5rem;background:var(--bg-secondary);cursor:pointer;">
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        <?php foreach (['#ff2ec4','#00f0ff','#9945ff','#ffaa00','#00ff88','#ff6b6b','#6366f1','#0891b2'] as $c): ?>
                        <button type="button" onclick="document.getElementById('defColor').value='<?= $c ?>'"
                                style="width:24px;height:24px;border-radius:50%;background:<?= $c ?>;border:2px solid transparent;cursor:pointer;transition:border-color .15s;"
                                onmouseover="this.style.borderColor='#fff'" onmouseout="this.style.borderColor='transparent'"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:.375rem;">Applied to new boards by default</div>
            </div>

            <!-- Workspace stats (live summary) -->
            <div>
                <div class="form-label" style="margin-bottom:.625rem;">
                    <i class="fa-solid fa-chart-pie" style="color:var(--dz-primary);"></i> Workspace Summary
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;">
                    <?php
                    $statItems = [
                        ['Boards',       $stats['boards']   ?? 0, 'var(--dz-primary)',   'fa-columns'],
                        ['Active Tasks', $stats['tasks']    ?? 0, 'var(--dz-secondary)', 'fa-list-check'],
                        ['Due This Week',$stats['due_soon'] ?? 0, 'var(--dz-warning)',   'fa-clock'],
                        ['Members',      $stats['members']  ?? 0, 'var(--dz-success)',   'fa-users'],
                    ];
                    foreach ($statItems as [$label, $val, $color, $icon]): ?>
                    <div style="background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:.625rem;padding:.625rem .75rem;display:flex;align-items:center;gap:.5rem;">
                        <i class="fa-solid <?= $icon ?>" style="color:<?= $color ?>;font-size:.875rem;"></i>
                        <div>
                            <div style="font-size:1.1rem;font-weight:800;color:<?= $color ?>;"><?= (int)$val ?></div>
                            <div style="font-size:.7rem;color:var(--text-secondary);"><?= htmlspecialchars($label) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-bell"></i> Notifications
        </div>

        <label class="dz-option" style="max-width:420px;">
            <input type="checkbox" name="email_notifications" value="1"
                   <?= !empty($settings['email_notifications']) ? 'checked' : '' ?>>
            <i class="fa-solid fa-envelope-circle-check"></i>
            <div>
                <div style="font-weight:600;font-size:.875rem;color:var(--text-primary);">Email notifications</div>
                <div style="font-size:.78rem;color:var(--text-secondary);">Get notified when tasks are assigned or updated</div>
            </div>
        </label>

        <div style="margin-top:.625rem;">
            <label class="dz-option" style="max-width:420px;">
                <input type="checkbox" name="task_reminders" value="1"
                       <?= !empty($settings['task_reminders']) ? 'checked' : '' ?>>
                <i class="fa-solid fa-alarm-clock"></i>
                <div>
                    <div style="font-weight:600;font-size:.875rem;color:var(--text-primary);">Task due reminders</div>
                    <div style="font-size:.78rem;color:var(--text-secondary);">Receive reminders for tasks due within 24 hours</div>
                </div>
            </label>
        </div>
    </div>

    <!-- Save / navigation -->
    <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;padding-bottom:2rem;">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Save Settings
        </button>
        <a href="/projects/devzone" class="btn btn-secondary">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        <a href="/projects/devzone/boards" class="btn btn-secondary">
            <i class="fa-solid fa-columns"></i> My Boards
        </a>
    </div>
</form>
