<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.ws-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px 24px;
    position: relative;
    overflow: hidden;
}
.ws-stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 50%;
    opacity: .08;
}
.ws-stat-card.cyan::before  { background: var(--cyan); }
.ws-stat-card.green::before  { background: var(--green); }
.ws-stat-card.magenta::before { background: var(--magenta); }
.ws-stat-card.orange::before  { background: var(--orange); }
.ws-status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 20px; font-size: .8rem; font-weight: 600;
}
.ws-status-badge.online  { background: rgba(34,197,94,.12); color: #22c55e; border: 1px solid rgba(34,197,94,.3); }
.ws-status-badge.offline { background: rgba(239,68,68,.12); color: #ef4444; border: 1px solid rgba(239,68,68,.3); }
.ws-status-badge .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; animation: wsPulse 2s ease-in-out infinite; }
@keyframes wsPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.3)} }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="page-header mb-3">
    <div>
        <h1 class="page-title">WebSocket Status</h1>
        <p class="page-subtitle" style="color:var(--text-secondary);font-size:.9rem;">Real-time server connection health &amp; configuration</p>
    </div>
    <span id="wsStatusBadge" class="ws-status-badge <?= $serverStatus === 'online' ? 'online' : 'offline' ?>">
        <span class="dot"></span>
        <?= $serverStatus === 'online' ? 'Online' : 'Offline' ?>
    </span>
</div>

<div class="grid grid-4 mb-3">
    <div class="ws-stat-card cyan">
        <div style="font-size:1.9rem;font-weight:700;color:var(--cyan);" id="statConnections">—</div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:4px;">Active Connections</div>
    </div>
    <div class="ws-stat-card green">
        <div style="font-size:1.9rem;font-weight:700;color:var(--green);" id="statRooms">—</div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:4px;">Active Rooms</div>
    </div>
    <div class="ws-stat-card magenta">
        <div style="font-size:1.9rem;font-weight:700;color:var(--magenta);"><?= htmlspecialchars($host) ?>:<?= (int)$port ?></div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:4px;">Server Address</div>
    </div>
    <div class="ws-stat-card orange">
        <div style="font-size:1.9rem;font-weight:700;color:var(--orange);" id="statUptime">—</div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:4px;">Server Checked</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Connection Details</h3>
        <button class="btn btn-sm" onclick="refreshStatus()" style="font-size:.8rem;">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
    <div style="padding:20px;">
        <table style="width:100%;border-collapse:collapse;">
            <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:12px 8px;color:var(--text-secondary);width:180px;">Status</td>
                <td style="padding:12px 8px;" id="detailStatus">
                    <span class="ws-status-badge <?= $serverStatus === 'online' ? 'online' : 'offline' ?>">
                        <span class="dot"></span><?= ucfirst($serverStatus) ?>
                    </span>
                </td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:12px 8px;color:var(--text-secondary);">Host</td>
                <td style="padding:12px 8px;font-family:monospace;"><?= htmlspecialchars($host) ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:12px 8px;color:var(--text-secondary);">Port</td>
                <td style="padding:12px 8px;font-family:monospace;"><?= (int)$port ?></td>
            </tr>
            <tr style="border-bottom:1px solid var(--border-color);">
                <td style="padding:12px 8px;color:var(--text-secondary);">SSE Endpoint</td>
                <td style="padding:12px 8px;font-family:monospace;">/notifications/stream</td>
            </tr>
            <tr>
                <td style="padding:12px 8px;color:var(--text-secondary);">Last Checked</td>
                <td style="padding:12px 8px;" id="lastChecked"><?= date('H:i:s') ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div style="padding:16px 20px;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="/admin/websocket/connections" class="btn btn-secondary" style="font-size:.85rem;">
            <i class="fas fa-users"></i> View Connections
        </a>
        <a href="/admin/websocket/rooms" class="btn btn-secondary" style="font-size:.85rem;">
            <i class="fas fa-layer-group"></i> View Rooms
        </a>
        <a href="/admin/websocket/settings" class="btn btn-secondary" style="font-size:.85rem;">
            <i class="fas fa-cog"></i> Settings
        </a>
    </div>
</div>

<script>
function refreshStatus() {
    fetch('/admin/api/live-stats', {credentials:'same-origin'})
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            document.getElementById('statConnections').textContent = d.online_now ?? '—';
            document.getElementById('statUptime').textContent = new Date().toLocaleTimeString();
            document.getElementById('lastChecked').textContent = new Date().toLocaleTimeString();
        }).catch(() => {});

    // Re-check SSE connectivity
    fetch('/notifications/stream?last_id=0', {credentials:'same-origin'}).then(r => {
        var badge = document.getElementById('wsStatusBadge');
        if (r.ok) {
            badge.className = 'ws-status-badge online';
            badge.innerHTML = '<span class="dot"></span>Online';
        } else {
            badge.className = 'ws-status-badge offline';
            badge.innerHTML = '<span class="dot"></span>Offline';
        }
    }).catch(() => {
        var badge = document.getElementById('wsStatusBadge');
        badge.className = 'ws-status-badge offline';
        badge.innerHTML = '<span class="dot"></span>Offline';
    });
}
// Auto-refresh every 15 s
refreshStatus();
setInterval(refreshStatus, 15000);
</script>
<?php View::endSection(); ?>
