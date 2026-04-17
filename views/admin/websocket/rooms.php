<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="page-header mb-3">
    <div>
        <h1 class="page-title">Rooms &amp; Channels</h1>
        <p class="page-subtitle" style="color:var(--text-secondary);font-size:.9rem;">Logical notification channels and activity groups</p>
    </div>
</div>

<div class="grid grid-3 mb-3">
    <?php
    $rooms = [
        ['name' => 'notifications', 'icon' => 'fa-bell',        'color' => 'var(--cyan)',    'desc' => 'In-app user notifications (SSE stream)',    'type' => 'SSE'],
        ['name' => 'admin-live',    'icon' => 'fa-shield-alt',  'color' => 'var(--magenta)', 'desc' => 'Admin live stats and real-time dashboard',  'type' => 'SSE'],
        ['name' => 'qr',            'icon' => 'fa-qrcode',      'color' => 'var(--green)',   'desc' => 'QR code generation events',                 'type' => 'Event'],
        ['name' => 'formx',         'icon' => 'fa-wpforms',     'color' => 'var(--orange)',  'desc' => 'Form submission events',                    'type' => 'Event'],
        ['name' => 'proshare',      'icon' => 'fa-share-alt',   'color' => '#a855f7',        'desc' => 'File upload / share events',                'type' => 'Event'],
        ['name' => 'codexpro',      'icon' => 'fa-code',        'color' => 'var(--cyan)',    'desc' => 'Code project & snippet events',             'type' => 'Event'],
        ['name' => 'notex',         'icon' => 'fa-sticky-note', 'color' => '#fbbf24',        'desc' => 'Note & folder create/update/delete events', 'type' => 'Event'],
        ['name' => 'billx',         'icon' => 'fa-file-invoice','color' => 'var(--green)',   'desc' => 'Bill generation & deletion events',         'type' => 'Event'],
        ['name' => 'resumex',       'icon' => 'fa-file-alt',    'color' => 'var(--magenta)', 'desc' => 'Resume create/delete events',               'type' => 'Event'],
        ['name' => 'idcard',        'icon' => 'fa-id-card',     'color' => 'var(--orange)',  'desc' => 'ID Card generation & deletion events',      'type' => 'Event'],
        ['name' => 'linkshortner',  'icon' => 'fa-link',        'color' => 'var(--cyan)',    'desc' => 'Short link create/update/delete events',    'type' => 'Event'],
        ['name' => 'session-alerts','icon' => 'fa-exclamation-triangle','color'=>'#ef4444',  'desc' => 'New login / suspicious session alerts',     'type' => 'SSE'],
    ];
    foreach ($rooms as $room): ?>
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 18px;display:flex;align-items:flex-start;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,0,0,.3);border:1px solid <?= $room['color'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas <?= $room['icon'] ?>" style="color:<?= $room['color'] ?>;font-size:.9rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.9rem;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                    <?= htmlspecialchars($room['name']) ?>
                    <span style="font-size:.7rem;padding:2px 7px;border-radius:10px;background:rgba(59,130,246,.1);color:var(--cyan);border:1px solid rgba(59,130,246,.2);font-weight:600;"><?= $room['type'] ?></span>
                </div>
                <div style="font-size:.78rem;color:var(--text-secondary);margin-top:3px;line-height:1.4;"><?= htmlspecialchars($room['desc']) ?></div>
            </div>
        </div>
        <div style="padding:8px 18px 12px;display:flex;align-items:center;gap:6px;">
            <span style="width:7px;height:7px;border-radius:50%;background:var(--green);display:inline-block;"></span>
            <span style="font-size:.75rem;color:var(--green);">Active</span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">About Real-time Channels</h3>
    </div>
    <div style="padding:16px 20px;font-size:.875rem;color:var(--text-secondary);line-height:1.7;">
        <p>This system uses <strong style="color:var(--cyan);">Server-Sent Events (SSE)</strong> for real-time notification delivery. Each connected browser polls
        <code style="background:rgba(255,255,255,.07);padding:2px 6px;border-radius:4px;">/notifications/stream</code> every 15 seconds. 
        Project events (CRUD in QR, FormX, NoteX, etc.) dispatch <code style="background:rgba(255,255,255,.07);padding:2px 6px;border-radius:4px;">\Core\Notification::send()</code> 
        which persists to the <code style="background:rgba(255,255,255,.07);padding:2px 6px;border-radius:4px;">notifications</code> table and is picked up on the next SSE poll.</p>
        <p style="margin-top:8px;">Admin users additionally receive live updates via the admin SSE client in the layout and the <strong style="color:var(--magenta);">Live Activity</strong> panel on the dashboard.</p>
    </div>
</div>
<?php View::endSection(); ?>
