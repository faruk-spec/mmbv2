<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.conn-row { display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.05); }
.conn-row:last-child { border-bottom:none; }
.conn-avatar { width:36px;height:36px;border-radius:50%;background:var(--cyan);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0; }
.conn-badge { display:inline-block;padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:600; }
.conn-badge.sse  { background:rgba(59,130,246,.12);color:var(--cyan);border:1px solid rgba(59,130,246,.25); }
.conn-badge.user { background:rgba(168,85,247,.12);color:#a855f7;border:1px solid rgba(168,85,247,.25); }
.conn-badge.admin{ background:rgba(255,193,7,.12);color:#ffc107;border:1px solid rgba(255,193,7,.25); }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="page-header mb-3">
    <div>
        <h1 class="page-title">Active Connections</h1>
        <p class="page-subtitle" style="color:var(--text-secondary);font-size:.9rem;">Users with active SSE streams or recent sessions</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:.8rem;color:var(--text-secondary);" id="connLastUpdated">updating…</span>
        <button class="btn btn-sm" onclick="loadConnections()"><i class="fas fa-sync-alt"></i> Refresh</button>
    </div>
</div>

<div class="grid grid-3 mb-3">
    <div class="card" style="padding:16px 20px;text-align:center;">
        <div style="font-size:1.7rem;font-weight:700;color:var(--cyan);" id="totalOnline">—</div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">Online Now</div>
    </div>
    <div class="card" style="padding:16px 20px;text-align:center;">
        <div style="font-size:1.7rem;font-weight:700;color:var(--green);" id="loginsToday">—</div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">Logins Today</div>
    </div>
    <div class="card" style="padding:16px 20px;text-align:center;">
        <div style="font-size:1.7rem;font-weight:700;color:var(--magenta);" id="regsToday">—</div>
        <div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">New Users Today</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Activity Feed</h3>
    </div>
    <div id="connList" style="min-height:100px;">
        <div style="padding:32px;text-align:center;color:var(--text-secondary);">
            <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;margin-bottom:8px;display:block;"></i>
            Loading…
        </div>
    </div>
</div>

<script>
function timeAgo(d) {
    var s = Math.floor((Date.now() - new Date(d).getTime()) / 1000);
    if (s < 60) return s + 's ago';
    if (s < 3600) return Math.floor(s/60) + 'm ago';
    if (s < 86400) return Math.floor(s/3600) + 'h ago';
    return Math.floor(s/86400) + 'd ago';
}
function actionLabel(a) {
    var m = {login:'Logged in',logout:'Logged out',register:'Registered','2fa_enabled':'Enabled 2FA',password_changed:'Changed password',profile_updated:'Updated profile'};
    return m[a] || a.replace(/_/g,' ');
}
function loadConnections() {
    fetch('/admin/api/live-stats', {credentials:'same-origin'})
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            document.getElementById('totalOnline').textContent   = d.online_now          ?? '—';
            document.getElementById('loginsToday').textContent   = d.logins_today        ?? '—';
            document.getElementById('regsToday').textContent     = d.registrations_today ?? '—';
            document.getElementById('connLastUpdated').textContent = 'Updated ' + new Date().toLocaleTimeString();

            var list = document.getElementById('connList');
            if (!d.recent_activity || !d.recent_activity.length) {
                list.innerHTML = '<div style="padding:32px;text-align:center;color:var(--text-secondary);">No recent activity.</div>';
                return;
            }
            list.innerHTML = d.recent_activity.map(function(a) {
                var initials = (a.user||'?').charAt(0).toUpperCase();
                return '<div class="conn-row">' +
                    '<div class="conn-avatar">' + initials + '</div>' +
                    '<div style="flex:1;">' +
                        '<strong style="color:var(--text-primary);font-size:.875rem;">' + (a.user || 'Unknown') + '</strong>' +
                        '<div style="color:var(--text-secondary);font-size:.8rem;margin-top:2px;">' + actionLabel(a.action) + '</div>' +
                    '</div>' +
                    '<div style="text-align:right;">' +
                        '<span class="conn-badge sse">SSE</span>' +
                        '<div style="color:var(--text-secondary);font-size:.75rem;margin-top:3px;">' + timeAgo(a.time) + '</div>' +
                    '</div>' +
                '</div>';
            }).join('');
        }).catch(() => {});
}
loadConnections();
setInterval(loadConnections, 15000);
</script>
<?php View::endSection(); ?>
