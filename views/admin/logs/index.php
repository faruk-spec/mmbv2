<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>Logs</h1>
        <p style="color: var(--text-secondary);">View system and activity logs</p>
    </div>
</div>

<div class="grid grid-2">
    <a href="/admin/logs/activity" class="card" style="text-decoration: none; color: inherit;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; background: rgba(0, 240, 255, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
            </div>
            <div>
                <h3>Activity Logs</h3>
                <p style="color: var(--text-secondary); margin: 0;">User actions and events</p>
            </div>
        </div>
    </a>
    
    <a href="/admin/logs/system" class="card" style="text-decoration: none; color: inherit;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; background: rgba(255, 46, 196, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <div>
                <h3>System Logs</h3>
                <p style="color: var(--text-secondary); margin: 0;">Application error and debug logs</p>
            </div>
        </div>
    </a>
</div>
<?php View::endSection(); ?>
