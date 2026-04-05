<?php use Core\View; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="container-fluid">
    <div class="page-header" style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;"><i class="fas fa-cog" style="color:#00d4ff;margin-right:10px;"></i> LinkShortner Settings</h1>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:28px;max-width:600px;">
        <p style="color:var(--text-secondary);">LinkShortner project settings will be configurable here. Currently using defaults.</p>

        <div style="margin-top:20px;padding:16px;background:rgba(0,212,255,0.05);border:1px solid rgba(0,212,255,0.2);border-radius:8px;">
            <div style="font-weight:600;margin-bottom:8px;color:#00d4ff;"><i class="fas fa-info-circle" style="margin-right:6px;"></i> Database</div>
            <div style="color:var(--text-secondary);font-size:13px;">Database: <code style="color:#00d4ff;">mmb_linkshortner</code></div>
            <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">Run schema.sql to initialize tables if not done yet.</div>
        </div>
    </div>
</div>
<?php View::end(); ?>
