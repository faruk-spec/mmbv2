<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="page-header mb-3">
    <div>
        <h1 class="page-title">Real-time Settings</h1>
        <p class="page-subtitle" style="color:var(--text-secondary);font-size:.9rem;">Configure SSE / WebSocket server parameters</p>
    </div>
</div>

<div class="grid grid-2 mb-3" style="gap:20px;">
<div class="card">
    <div class="card-header"><h3 class="card-title">Server Configuration</h3></div>
    <form id="wsSettingsForm" style="padding:20px;">
        <?= \Core\Security::csrfField() ?>
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div>
                <label style="display:block;font-size:.85rem;color:var(--text-secondary);margin-bottom:6px;">WebSocket Host</label>
                <input type="text" name="websocket_host" id="wsHost"
                       value="<?php
                           foreach ($settings as $s) { if ($s['setting_key'] === 'websocket_host') { echo htmlspecialchars(json_decode($s['setting_value'], true) ?? $s['setting_value']); break; } }
                       ?>"
                       placeholder="localhost"
                       style="width:100%;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:10px 14px;color:var(--text-primary);font-size:.875rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.85rem;color:var(--text-secondary);margin-bottom:6px;">WebSocket Port</label>
                <input type="number" name="websocket_port" id="wsPort"
                       value="<?php
                           foreach ($settings as $s) { if ($s['setting_key'] === 'websocket_port') { echo htmlspecialchars(json_decode($s['setting_value'], true) ?? $s['setting_value']); break; } }
                       ?>"
                       placeholder="8080" min="1" max="65535"
                       style="width:100%;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:10px 14px;color:var(--text-primary);font-size:.875rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.85rem;color:var(--text-secondary);margin-bottom:6px;">SSE Poll Interval (ms)</label>
                <input type="number" name="websocket_sse_interval" id="wsInterval"
                       value="<?php
                           $found = false;
                           foreach ($settings as $s) { if ($s['setting_key'] === 'websocket_sse_interval') { echo htmlspecialchars(json_decode($s['setting_value'], true) ?? $s['setting_value']); $found = true; break; } }
                           if (!$found) echo '15000';
                       ?>"
                       placeholder="15000" min="5000" max="120000" step="1000"
                       style="width:100%;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:10px 14px;color:var(--text-primary);font-size:.875rem;box-sizing:border-box;">
                <div style="font-size:.75rem;color:var(--text-secondary);margin-top:4px;">Browser reconnect interval in milliseconds (default: 15000 = 15s)</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <input type="checkbox" name="websocket_enabled" id="wsEnabled"
                       <?php foreach ($settings as $s) { if ($s['setting_key'] === 'websocket_enabled' && json_decode($s['setting_value']) == true) echo 'checked'; } ?>
                       style="width:16px;height:16px;accent-color:var(--cyan);">
                <label for="wsEnabled" style="font-size:.875rem;color:var(--text-primary);cursor:pointer;">Enable SSE real-time notifications</label>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary" id="wsSubmitBtn">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <div id="wsSaveMsg" style="display:none;align-items:center;gap:6px;font-size:.85rem;"></div>
        </div>
    </form>
</div>

<div style="display:flex;flex-direction:column;gap:20px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title">SSE Info</h3></div>
        <div style="padding:16px 20px;font-size:.85rem;line-height:1.7;color:var(--text-secondary);">
            <p>The real-time system uses <strong style="color:var(--cyan);">Server-Sent Events (SSE)</strong> — a lightweight HTTP-based push protocol. No persistent WebSocket server process is required.</p>
            <p style="margin-top:8px;">Each user's browser connects to <code style="background:rgba(255,255,255,.07);padding:1px 5px;border-radius:3px;">/notifications/stream</code> and reconnects automatically every <em>SSE Poll Interval</em> ms.</p>
            <ul style="margin-top:10px;padding-left:18px;">
                <li>Zero extra infrastructure needed</li>
                <li>Works behind any standard PHP host</li>
                <li>Fire-and-close (no long-held workers)</li>
                <li>Auto-reconnects on errors</li>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Existing Settings</h3></div>
        <div style="padding:0;">
            <?php if (empty($settings)): ?>
            <div style="padding:16px 20px;color:var(--text-secondary);font-size:.875rem;">No settings stored yet. Save above to create them.</div>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="padding:10px 16px;text-align:left;color:var(--text-secondary);">Key</th>
                    <th style="padding:10px 16px;text-align:left;color:var(--text-secondary);">Value</th>
                </tr>
                <?php foreach ($settings as $s): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:8px 16px;font-family:monospace;color:var(--cyan);"><?= htmlspecialchars($s['setting_key']) ?></td>
                    <td style="padding:8px 16px;color:var(--text-primary);"><?= htmlspecialchars($s['setting_value']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script>
document.getElementById('wsSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('wsSubmitBtn');
    var msg = document.getElementById('wsSaveMsg');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

    var body = new URLSearchParams(new FormData(this));
    // Checkbox: if unchecked, explicitly send false
    if (!document.getElementById('wsEnabled').checked) {
        body.set('websocket_enabled', 'false');
    }

    fetch('/admin/websocket/settings', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body.toString()
    })
    .then(r => r.json())
    .then(d => {
        msg.style.display = 'flex';
        if (d.success) {
            msg.style.color = 'var(--green)';
            msg.innerHTML = '<i class="fas fa-check-circle"></i> Saved successfully';
        } else {
            msg.style.color = 'var(--red,#ef4444)';
            msg.innerHTML = '<i class="fas fa-times-circle"></i> ' + (d.message || 'Error saving');
        }
        setTimeout(() => { msg.style.display = 'none'; }, 3000);
    })
    .catch(() => {
        msg.style.display = 'flex';
        msg.style.color = 'var(--red,#ef4444)';
        msg.innerHTML = '<i class="fas fa-times-circle"></i> Request failed';
        setTimeout(() => { msg.style.display = 'none'; }, 3000);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Settings';
    });
});
</script>
<?php View::endSection(); ?>
