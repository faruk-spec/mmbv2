<?php use Core\View; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
    /* Override main padding so rx-layout can be full-bleed */
    .main { padding: 0 !important; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<style>
    /* ++ Layout */
    .rx-layout {
        display: flex;
        min-height: calc(100vh - 70px);
    }

    /* ++ Left Sidebar */
    .rx-sidebar {
        width: 240px;
        flex-shrink: 0;
        background: var(--bg-card);
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        padding: 28px 0 20px;
        position: sticky;
        top: 0;
        height: calc(100vh - 70px);
        overflow-y: auto;
    }
    .rx-sidebar-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 20px 24px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 12px;
    }
    .rx-sidebar-logo-icon {
        width: 36px; height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-weight: 800; color: #06060a; font-size: 1rem;
    }
    .rx-sidebar-logo-text {
        font-size: 1.1rem; font-weight: 800;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .rx-nav-section { padding: 4px 12px; margin-bottom: 2px; }
    .rx-nav-section-title {
        font-size: 0.68rem; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: var(--text-secondary);
        padding: 10px 8px 4px; opacity: 0.6;
    }
    .rx-nav-link {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 10px; border-radius: 8px;
        color: var(--text-secondary); text-decoration: none;
        font-size: 0.875rem; font-weight: 500;
        transition: background 0.15s, color 0.15s;
        position: relative;
    }
    .rx-nav-link:hover { background: rgba(0,240,255,0.07); color: var(--text-primary); text-decoration: none; }
    .rx-nav-link.active { background: rgba(0,240,255,0.1); color: var(--cyan); }
    .rx-nav-link.active::before {
        content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
        width: 3px; height: 60%; background: var(--cyan); border-radius: 0 3px 3px 0;
    }
    .rx-nav-link svg, .rx-nav-link i { width: 18px; flex-shrink: 0; text-align: center; opacity: 0.75; }
    .rx-nav-link.active svg, .rx-nav-link.active i { opacity: 1; }
    .rx-nav-badge {
        margin-left: auto; background: rgba(0,240,255,0.12); color: var(--cyan);
        border-radius: 20px; padding: 1px 8px; font-size: 0.7rem; font-weight: 700;
    }
    .rx-nav-pro-badge {
        margin-left: auto;
        background: linear-gradient(135deg, #f59e0b, #ef4444);
        color: #fff; border-radius: 20px; padding: 1px 7px;
        font-size: 0.65rem; font-weight: 700; letter-spacing: 0.04em;
    }
    .rx-sidebar-upgrade {
        margin: auto 12px 0;
        padding: 14px;
        background: linear-gradient(135deg, rgba(153,69,255,0.15), rgba(0,240,255,0.08));
        border: 1px solid rgba(153,69,255,0.25);
        border-radius: 10px;
    }
    .rx-sidebar-upgrade h4 { font-size: 0.82rem; font-weight: 700; color: var(--text-primary); margin: 0 0 4px; }
    .rx-sidebar-upgrade p { font-size: 0.75rem; color: var(--text-secondary); margin: 0 0 10px; line-height: 1.4; }
    .rx-upgrade-btn {
        display: block; text-align: center; padding: 7px 12px;
        background: linear-gradient(135deg, var(--purple), var(--cyan));
        color: #06060a; font-weight: 700; font-size: 0.78rem;
        border-radius: 7px; text-decoration: none; transition: opacity 0.2s;
    }
    .rx-upgrade-btn:hover { opacity: 0.88; text-decoration: none; color: #06060a; }

    /* ++ Main */
    .rx-main { flex: 1; padding: 32px 28px; min-width: 0; }

    /* ++ Header */
    .rx-header {
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 16px; margin-bottom: 28px; flex-wrap: wrap;
    }
    .rx-header-left h1 {
        font-size: 1.9rem; font-weight: 800; letter-spacing: -0.5px; line-height: 1.1;
        background: linear-gradient(135deg, var(--cyan) 0%, var(--purple) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin: 0 0 4px;
    }
    .rx-header-left p { color: var(--text-secondary); font-size: 0.88rem; margin: 0; }
    .rx-btn-create {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 20px;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        color: #06060a; font-weight: 700; font-size: 0.875rem;
        border-radius: 10px; text-decoration: none;
        transition: var(--transition); white-space: nowrap;
        box-shadow: 0 4px 20px rgba(0,240,255,0.25); flex-shrink: 0;
    }
    .rx-btn-create:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,240,255,0.4); color: #06060a; text-decoration: none; }

    /* ++ Stats */
    .rx-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 28px; }
    .rx-stat-card {
        background: var(--bg-card); border: 1px solid var(--border-color);
        border-radius: 12px; padding: 18px 16px;
        display: flex; align-items: center; gap: 14px;
        transition: var(--transition); position: relative; overflow: hidden;
    }
    .rx-stat-card::before { content: ''; position: absolute; inset: 0; border-radius: 12px; opacity: 0; transition: opacity 0.3s; }
    .rx-stat-card:hover::before { opacity: 1; }
    .rx-stat-card:hover { transform: translateY(-2px); }
    .rx-stat-card.cyan { border-color: rgba(0,240,255,0.2); }
    .rx-stat-card.cyan::before { background: rgba(0,240,255,0.04); }
    .rx-stat-card.cyan:hover { box-shadow: 0 6px 24px rgba(0,240,255,0.15); }
    .rx-stat-card.purple { border-color: rgba(153,69,255,0.2); }
    .rx-stat-card.purple::before { background: rgba(153,69,255,0.04); }
    .rx-stat-card.purple:hover { box-shadow: 0 6px 24px rgba(153,69,255,0.15); }
    .rx-stat-card.green { border-color: rgba(0,255,136,0.2); }
    .rx-stat-card.green::before { background: rgba(0,255,136,0.04); }
    .rx-stat-card.green:hover { box-shadow: 0 6px 24px rgba(0,255,136,0.15); }
    .rx-stat-card.orange { border-color: rgba(245,158,11,0.2); }
    .rx-stat-card.orange::before { background: rgba(245,158,11,0.04); }
    .rx-stat-card.orange:hover { box-shadow: 0 6px 24px rgba(245,158,11,0.15); }
    .rx-stat-icon { width: 42px; height: 42px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rx-stat-icon.cyan { background: rgba(0,240,255,0.12); color: var(--cyan); }
    .rx-stat-icon.purple { background: rgba(153,69,255,0.12); color: var(--purple); }
    .rx-stat-icon.green { background: rgba(0,255,136,0.12); color: var(--green); }
    .rx-stat-icon.orange { background: rgba(245,158,11,0.12); color: #f59e0b; }
    .rx-stat-val { font-size: 1.6rem; font-weight: 800; line-height: 1; margin-bottom: 2px; }
    .rx-stat-card.cyan .rx-stat-val { color: var(--cyan); }
    .rx-stat-card.purple .rx-stat-val { color: var(--purple); }
    .rx-stat-card.green .rx-stat-val { color: var(--green); }
    .rx-stat-card.orange .rx-stat-val { color: #f59e0b; }
    .rx-stat-label { font-size: 0.78rem; color: var(--text-secondary); font-weight: 500; }

    /* ++ Quick Actions */
    .rx-quick-actions { display: flex; gap: 10px; margin-bottom: 28px; flex-wrap: wrap; }
    .rx-quick-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px;
        font-size: 0.82rem; font-weight: 600; text-decoration: none;
        transition: var(--transition);
        border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-secondary);
    }
    .rx-quick-btn:hover { background: rgba(0,240,255,0.07); border-color: rgba(0,240,255,0.3); color: var(--cyan); text-decoration: none; }

    /* ++ Section */
    .rx-section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .rx-section-head h2 { font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0; }
    .rx-count-badge { background: rgba(0,240,255,0.1); color: var(--cyan); border: 1px solid rgba(0,240,255,0.2); border-radius: 20px; padding: 3px 12px; font-size: 0.75rem; font-weight: 600; }

    /* ++ Grid & Cards */
    .rx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }
    .rx-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; display: flex; flex-direction: column; gap: 12px; transition: var(--transition); position: relative; }
    .rx-card:hover { border-color: rgba(0,240,255,0.25); box-shadow: 0 8px 28px rgba(0,240,255,0.08); transform: translateY(-2px); }
    .rx-card-accent { position: absolute; top: 0; left: 20px; right: 20px; height: 2px; border-radius: 0 0 4px 4px; opacity: 0; transition: opacity 0.3s; }
    .rx-card:hover .rx-card-accent { opacity: 1; }
    .rx-card-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rx-card-meta { display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }
    .rx-theme-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 5px currentColor; }
    .rx-card-meta-text { font-size: 0.78rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rx-card-meta-sep { color: var(--border-color); font-size: 0.72rem; }
    .rx-card-date { font-size: 0.75rem; color: var(--text-secondary); display: flex; align-items: center; gap: 5px; }
    .rx-card-actions { display: flex; gap: 6px; margin-top: auto; flex-wrap: wrap; }
    .rx-action-btn { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 6px 11px; border-radius: 7px; font-size: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: var(--transition); background: none; line-height: 1; white-space: nowrap; }
    .rx-action-btn.edit { background: rgba(0,240,255,0.08); border-color: rgba(0,240,255,0.2); color: var(--cyan); }
    .rx-action-btn.edit:hover { background: rgba(0,240,255,0.18); text-decoration: none; color: var(--cyan); }
    .rx-action-btn.preview { background: rgba(153,69,255,0.08); border-color: rgba(153,69,255,0.2); color: var(--purple); }
    .rx-action-btn.preview:hover { background: rgba(153,69,255,0.18); text-decoration: none; color: var(--purple); }
    .rx-action-btn.duplicate { background: rgba(0,255,136,0.07); border-color: rgba(0,255,136,0.18); color: var(--green); }
    .rx-action-btn.duplicate:hover { background: rgba(0,255,136,0.15); text-decoration: none; color: var(--green); }
    .rx-action-btn.delete { background: rgba(255,107,107,0.07); border-color: rgba(255,107,107,0.18); color: var(--red); }
    .rx-action-btn.delete:hover { background: rgba(255,107,107,0.16); text-decoration: none; color: var(--red); }
    .rx-action-btn.share { background: rgba(74,222,128,0.07); border-color: rgba(74,222,128,0.2); color: #4ade80; }
    .rx-action-btn.share:hover { background: rgba(74,222,128,0.15); text-decoration: none; color: #4ade80; }
    .rx-action-btn.share.active { background: rgba(74,222,128,0.18); border-color: rgba(74,222,128,0.5); color: #4ade80; }

    /* ++ Empty */
    .rx-empty { text-align: center; padding: 60px 24px; background: var(--bg-card); border: 1px dashed rgba(0,240,255,0.2); border-radius: 16px; }
    .rx-empty-icon { width: 72px; height: 72px; margin: 0 auto 20px; background: rgba(0,240,255,0.07); border: 1px solid rgba(0,240,255,0.15); border-radius: 18px; display: flex; align-items: center; justify-content: center; color: var(--cyan); }
    .rx-empty h3 { font-size: 1.2rem; font-weight: 700; margin: 0 0 8px; color: var(--text-primary); }
    .rx-empty p { color: var(--text-secondary); font-size: 0.88rem; margin: 0 0 24px; max-width: 320px; margin-left: auto; margin-right: auto; }

    /* ++ Modals */
    .rx-modal-overlay { position: fixed; inset: 0; background: rgba(6,6,10,0.85); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 24px; opacity: 0; pointer-events: none; transition: opacity 0.25s; }
    .rx-modal-overlay.open { opacity: 1; pointer-events: all; }
    .rx-modal { background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 16px; padding: 28px; max-width: 400px; width: 100%; transform: translateY(16px) scale(0.97); transition: transform 0.25s; box-shadow: 0 24px 64px rgba(0,0,0,0.6); }
    .rx-modal-overlay.open .rx-modal { transform: translateY(0) scale(1); }
    .rx-modal-icon { width: 52px; height: 52px; border-radius: 13px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
    .rx-modal-icon.danger { background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.25); color: var(--red); }
    .rx-modal-icon.success { background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.25); color: var(--green); }
    .rx-modal h3 { font-size: 1.1rem; font-weight: 700; text-align: center; margin: 0 0 8px; }
    .rx-modal p { color: var(--text-secondary); font-size: 0.875rem; text-align: center; margin: 0 0 20px; line-height: 1.6; }
    .rx-modal-title-preview { font-weight: 600; color: var(--text-primary); }
    .rx-modal-actions { display: flex; gap: 8px; }
    .rx-modal-actions .btn { flex: 1; justify-content: center; }

    /* ++ Flash */
    .rx-flash { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 9px; margin-bottom: 20px; font-size: 0.875rem; font-weight: 500; background: rgba(0,255,136,0.08); border: 1px solid rgba(0,255,136,0.25); color: var(--green); animation: rx-fadein 0.4s ease; }
    @keyframes rx-fadein { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

    /* ++ Mobile sidebar toggle button */
    .rx-sidebar-toggle {
        display: none;
        position: fixed;
        bottom: 24px;
        right: 20px;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        border: none;
        color: #06060a;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(0, 240, 255, 0.35);
        z-index: 1010;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        will-change: transform;
    }
    .rx-sidebar-toggle:active { transform: scale(0.93); }
    .rx-sidebar-toggle svg { width: 22px; height: 22px; flex-shrink: 0; }

    /* ++ Mobile sidebar overlay */
    .rx-sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(6, 6, 10, 0.7);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        z-index: 1005;
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    .rx-sidebar-overlay.active {
        display: block;
        opacity: 1;
    }

    /* ++ Responsive */
    @media (max-width: 1024px) { .rx-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
        .rx-layout { flex-direction: row; } /* keep row so sidebar slides over content */
        .rx-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            z-index: 1006;
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            padding-top: 70px; /* below main navbar */
            border-right: 1px solid var(--border-color);
        }
        .rx-sidebar.open { transform: translateX(0); }
        .rx-sidebar-logo { display: flex; } /* keep logo visible in drawer */
        .rx-nav-section-title { display: block; }
        .rx-nav-link { padding: 10px 16px; font-size: 0.875rem; }
        .rx-sidebar-upgrade { display: block; }
        .rx-sidebar-toggle { display: flex; }
        .rx-main { padding: 20px 16px; margin-left: 0; width: 100%; }
        .rx-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .rx-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) { .rx-stats { grid-template-columns: 1fr 1fr; gap: 8px; } }
</style>

<div class="rx-layout">

    <!-- Left Sidebar -->
    <aside class="rx-sidebar" id="rxSidebar">
        <div class="rx-sidebar-logo">
            <div class="rx-sidebar-logo-icon">RX</div>
            <span class="rx-sidebar-logo-text">ResumeX</span>
        </div>

        <div class="rx-nav-section">
            <div class="rx-nav-section-title">Workspace</div>
            <a href="/projects/resumex" class="rx-nav-link active">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Dashboard</span>
            </a>
            <a href="/projects/resumex/create" class="rx-nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>New Resume</span>
            </a>
            <a href="/projects/resumex/templates" class="rx-nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                <span>Templates</span>
                <?php if (!empty($allThemes ?? [])): ?>
                    <span class="rx-nav-badge"><?= count($allThemes) ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="rx-nav-section">
            <div class="rx-nav-section-title">Tools</div>
            <a href="/projects/resumex/create?tab=ai" class="rx-nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                <span>AI Assistant</span>
                <span class="rx-nav-pro-badge">PRO</span>
            </a>
            <a href="/projects/resumex/import" class="rx-nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path></svg>
                <span>Import Resume</span>
            </a>
            <?php if (($featureSettings['resumex_linkedin_import'] ?? '0') === '1'): ?>
            <a href="/projects/resumex/import?source=linkedin" class="rx-nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                <span>LinkedIn Import</span>
                <span class="rx-nav-pro-badge">PRO</span>
            </a>
            <?php endif; ?>
            <?php if (($featureSettings['resumex_public_resumes'] ?? '0') === '1'): ?>
            <a href="#rx-all-resumes" class="rx-nav-link" onclick="event.preventDefault();document.getElementById('rx-all-resumes')&&document.getElementById('rx-all-resumes').scrollIntoView({behavior:'smooth'});">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                <span>Share Resume</span>
            </a>
            <?php endif; ?>
            <?php if (($featureSettings['resumex_custom_domain'] ?? '0') === '1'): ?>
            <a href="#" class="rx-nav-link" onclick="event.preventDefault();openCustomDomainModal();">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span>Custom Domain</span>
                <span class="rx-nav-pro-badge">PRO</span>
            </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($resumes)): ?>
        <div class="rx-nav-section">
            <div class="rx-nav-section-title">My Resumes</div>
            <?php foreach (array_slice($resumes, 0, 5) as $sideResume): ?>
                <a href="/projects/resumex/edit/<?= (int)$sideResume['id'] ?>" class="rx-nav-link" title="<?= htmlspecialchars($sideResume['title'] ?? 'Untitled') ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($sideResume['title'] ?? 'Untitled') ?></span>
                </a>
            <?php endforeach; ?>
            <?php if (count($resumes) > 5): ?>
                <a href="#rx-all-resumes" class="rx-nav-link" style="font-size:0.75rem;opacity:0.7;">+<?= count($resumes) - 5 ?> more...</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="rx-sidebar-upgrade">
            <h4>Upgrade to Pro</h4>
            <p>Unlock unlimited resumes, AI writing, premium templates and more.</p>
            <a href="#upgrade" class="rx-upgrade-btn">Upgrade Now</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="rx-main">

        <?php if (isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
            <div class="rx-flash" role="alert">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Resume saved successfully.
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="rx-header">
            <div class="rx-header-left">
                <h1>My Dashboard</h1>
                <p>Welcome back<?php if (!empty($user['name'] ?? null)): ?>, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?><?php endif; ?>! Here is an overview of your resumes.</p>
            </div>
            <a href="/projects/resumex/create" class="rx-btn-create">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Create Resume
            </a>
        </div>

        <!-- Stats -->
        <div class="rx-stats">
            <div class="rx-stat-card cyan">
                <div class="rx-stat-icon cyan">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
                <div>
                    <div class="rx-stat-val"><?= (int)($stats['total'] ?? 0) ?></div>
                    <div class="rx-stat-label">Total Resumes</div>
                </div>
            </div>
            <div class="rx-stat-card purple">
                <div class="rx-stat-icon purple">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </div>
                <div>
                    <div class="rx-stat-val"><?= (int)($stats['templates_used'] ?? 0) ?></div>
                    <div class="rx-stat-label">Templates Used</div>
                </div>
            </div>
            <div class="rx-stat-card green">
                <div class="rx-stat-icon green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
                <div>
                    <div class="rx-stat-val"><?= count($allThemes ?? []) ?></div>
                    <div class="rx-stat-label">Available Themes</div>
                </div>
            </div>
            <div class="rx-stat-card orange">
                <div class="rx-stat-icon orange">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div>
                    <?php
                        $lastUpdated = $stats['last_updated'] ?? null;
                        $lastUpdatedStr = $lastUpdated ? date('M j', strtotime($lastUpdated)) : 'Never';
                    ?>
                    <div class="rx-stat-val" style="font-size:<?= $lastUpdated ? '1.1rem' : '1.4rem' ?>;"><?= htmlspecialchars($lastUpdatedStr) ?></div>
                    <div class="rx-stat-label">Last Updated</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rx-quick-actions">
            <a href="/projects/resumex/create" class="rx-quick-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Resume
            </a>
            <a href="/projects/resumex/templates" class="rx-quick-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                Browse Templates
            </a>
            <a href="/projects/resumex/create?tab=ai" class="rx-quick-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                AI Assistant
            </a>
        </div>

        <!-- Resume Grid -->
        <div id="rx-all-resumes">
            <div class="rx-section-head">
                <h2>Your Resumes</h2>
                <?php if (!empty($resumes)): ?>
                    <span class="rx-count-badge"><?= count($resumes) ?> resume<?= count($resumes) !== 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($resumes)): ?>
                <div class="rx-empty">
                    <div class="rx-empty-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                    </div>
                    <h3>No resumes yet</h3>
                    <p>Start building your professional resume. Choose a template and customize it to stand out.</p>
                    <a href="/projects/resumex/create" class="rx-btn-create" style="display:inline-flex;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Create Your First Resume
                    </a>
                </div>
            <?php else: ?>
                <div class="rx-grid">
                    <?php foreach ($resumes as $resume): ?>
                        <?php
                            $primaryColor = htmlspecialchars($resume['primaryColor'] ?? '#00f0ff');
                            $templateLabel = ucwords(str_replace(['-', '_'], ' ', $resume['template'] ?? 'default'));
                            $themeName = htmlspecialchars($resume['theme_name'] ?? 'Default');
                            $updatedAt = $resume['updated_at'] ?? $resume['created_at'] ?? null;
                            $updatedStr = $updatedAt ? date('M j, Y', strtotime($updatedAt)) : '---';
                            $resumeId = (int)$resume['id'];
                            $resumeTitle = htmlspecialchars($resume['title'] ?? 'Untitled');
                        ?>
                        <div class="rx-card">
                            <div class="rx-card-accent" style="background: linear-gradient(90deg, <?= $primaryColor ?>, transparent);"></div>
                            <div class="rx-card-title" title="<?= $resumeTitle ?>"><?= $resumeTitle ?></div>
                            <div class="rx-card-meta">
                                <span class="rx-theme-dot" style="background: <?= $primaryColor ?>; color: <?= $primaryColor ?>;"></span>
                                <span class="rx-card-meta-text"><?= $themeName ?></span>
                                <span class="rx-card-meta-sep">&middot;</span>
                                <span class="rx-card-meta-text"><?= htmlspecialchars($templateLabel) ?></span>
                            </div>
                            <div class="rx-card-date">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                Updated <?= $updatedStr ?>
                                <?php if (!empty($resume['is_public']) && !empty($resume['share_token'])): ?>
                                <span style="margin-left:6px;font-size:0.68rem;color:#4ade80;font-weight:600;"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg> Shared</span>
                                <?php endif; ?>
                            </div>
                            <div class="rx-card-actions">
                                <a href="/projects/resumex/edit/<?= $resumeId ?>" class="rx-action-btn edit">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    Edit
                                </a>
                                <a href="/projects/resumex/preview/<?= $resumeId ?>" class="rx-action-btn preview" target="_blank" rel="noopener">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    Preview
                                </a>
                                <button type="button" class="rx-action-btn"
                                        style="color:#00f0ff;"
                                        title="Generate QR for resume preview"
                                        onclick="ecoQrOpen('<?= htmlspecialchars((defined('APP_URL') ? APP_URL : '') . '/projects/resumex/preview/' . $resumeId, ENT_QUOTES) ?>')">
                                    <i class="fas fa-qrcode" style="font-size:0.7rem;"></i> QR
                                </button>
                                <?php if (($featureSettings['resumex_public_resumes'] ?? '0') === '1'): ?>
                                <button type="button" class="rx-action-btn share<?= !empty($resume['is_public']) ? ' active' : '' ?>"
                                        onclick="openShareModal(<?= $resumeId ?>, <?= json_encode($resume['share_token'] ?? '') ?>, <?= !empty($resume['is_public']) ? 'true' : 'false' ?>)"
                                        title="Share resume publicly">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                    Share
                                </button>
                                <?php endif; ?>
                                <button type="button" class="rx-action-btn duplicate"
                                    onclick="openDuplicateModal(<?= $resumeId ?>, <?= htmlspecialchars(json_encode($resume['title'] ?? 'Untitled'), ENT_QUOTES, 'UTF-8') ?>)">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    Copy
                                </button>
                                <button type="button" class="rx-action-btn delete"
                                    onclick="openDeleteModal(<?= $resumeId ?>, <?= htmlspecialchars(json_encode($resume['title'] ?? 'Untitled'), ENT_QUOTES, 'UTF-8') ?>)">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- Mobile sidebar overlay -->
<div class="rx-sidebar-overlay" id="rxSidebarOverlay"></div>

<!-- Mobile sidebar toggle button (hamburger FAB) -->
<button class="rx-sidebar-toggle" id="rxSidebarToggle" aria-label="Open navigation menu" aria-expanded="false" aria-controls="rxSidebar">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6"  x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<!-- Share Modal -->
<div id="rx-share-modal" class="rx-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rx-share-title">
    <div class="rx-modal">
        <div class="rx-modal-icon" style="background:rgba(74,222,128,0.12);border:1px solid rgba(74,222,128,0.3);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        </div>
        <h3 id="rx-share-title">Share Resume</h3>
        <p style="color:var(--text-secondary);font-size:0.875rem;margin:0 0 14px;">Generate a public link to share your resume with anyone — no login required.</p>
        <div id="rx-share-link-wrap" style="display:none;margin-bottom:14px;">
            <label style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:6px;">Public Link</label>
            <div style="display:flex;gap:6px;">
                <input id="rx-share-link-input" type="text" readonly
                       style="flex:1;min-width:0;padding:9px 12px;border-radius:8px;border:1px solid var(--border-color);background:rgba(0,240,255,0.04);color:var(--text-primary);font-size:0.78rem;font-family:monospace;"
                       value="">
                <button onclick="copyShareLink()" style="padding:9px 14px;border-radius:8px;border:1px solid rgba(74,222,128,0.3);background:rgba(74,222,128,0.1);color:#4ade80;font-weight:700;cursor:pointer;font-size:0.78rem;white-space:nowrap;flex-shrink:0;">
                    Copy
                </button>
            </div>
        </div>
        <input type="hidden" id="rx-share-csrf" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" id="rx-share-id" value="">
        <div class="rx-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeShareModal()" style="flex:1;">Cancel</button>
            <button type="button" id="rx-share-generate-btn" class="btn btn-primary" onclick="generateShare()" style="flex:1;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Generate Link
            </button>
        </div>
    </div>
</div>

<!-- Custom Domain Modal -->
<div id="rx-custom-domain-modal" class="rx-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rx-custom-domain-title">
    <div class="rx-modal" style="max-width:480px;">
        <div class="rx-modal-icon" style="background:rgba(153,69,255,0.12);border:1px solid rgba(153,69,255,0.3);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <h3 id="rx-custom-domain-title">Custom Domain</h3>
        <p style="color:var(--text-secondary);font-size:0.875rem;margin:0 0 18px;line-height:1.6;">Point your own domain (e.g. <code style="background:rgba(255,255,255,0.06);padding:2px 6px;border-radius:4px;font-size:0.8rem;">cv.yourname.com</code>) to your public resume. This is a Pro feature.</p>
        <div style="background:rgba(153,69,255,0.06);border:1px solid rgba(153,69,255,0.2);border-radius:10px;padding:16px;margin-bottom:20px;font-size:0.82rem;color:var(--text-secondary);line-height:1.7;">
            <strong style="color:var(--text-primary);display:block;margin-bottom:8px;">How it works:</strong>
            <ol style="margin:0;padding-left:18px;">
                <li>Make your resume public using the <strong>Share</strong> button</li>
                <li>Add a CNAME record in your DNS pointing to <code style="background:rgba(255,255,255,0.06);padding:1px 5px;border-radius:3px;">share.resumex.app</code></li>
                <li>Contact support to link your domain to your resume</li>
            </ol>
        </div>
        <div class="rx-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeCustomDomainModal()" style="flex:1;">Close</button>
            <a href="mailto:support@<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'example.com') ?>" class="btn btn-primary" style="flex:1;text-align:center;">Contact Support</a>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="rx-delete-modal" class="rx-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rx-delete-title">
    <div class="rx-modal">
        <div class="rx-modal-icon danger">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
        </div>
        <h3 id="rx-delete-title">Delete Resume</h3>
        <p>Are you sure you want to delete <span class="rx-modal-title-preview" id="rx-delete-name"></span>? This action cannot be undone.</p>
        <input type="hidden" id="rx-delete-csrf" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" id="rx-delete-id" value="">
        <div class="rx-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="rx-delete-confirm-btn">Delete</button>
        </div>
    </div>
</div>

<!-- Duplicate Modal -->
<div id="rx-duplicate-modal" class="rx-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rx-duplicate-title">
    <div class="rx-modal">
        <div class="rx-modal-icon success">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        </div>
        <h3 id="rx-duplicate-title">Duplicate Resume</h3>
        <p>Create a copy of <span class="rx-modal-title-preview" id="rx-duplicate-name"></span>?</p>
        <form id="rx-duplicate-form" method="POST" action="/projects/resumex/duplicate">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" id="rx-duplicate-id" value="">
            <div class="rx-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDuplicateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Duplicate</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var deleteOverlay    = document.getElementById('rx-delete-modal');
    var deleteIdInput    = document.getElementById('rx-delete-id');
    var deleteNameEl     = document.getElementById('rx-delete-name');
    var deleteBtn        = document.getElementById('rx-delete-confirm-btn');
    var duplicateOverlay = document.getElementById('rx-duplicate-modal');
    var duplicateIdInput = document.getElementById('rx-duplicate-id');
    var duplicateNameEl  = document.getElementById('rx-duplicate-name');

    window.openDeleteModal = function (id, title) {
        deleteIdInput.value = id;
        deleteNameEl.textContent = title;
        deleteOverlay.classList.add('open');
        deleteBtn.focus();
    };
    window.closeDeleteModal = function () { deleteOverlay.classList.remove('open'); };

    deleteBtn.addEventListener('click', function () {
        var id    = deleteIdInput.value;
        var token = document.getElementById('rx-delete-csrf').value;
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Deleting...';
        var fd = new FormData();
        fd.append('_csrf_token', token);
        fd.append('id', id);
        fetch('/projects/resumex/delete', { method: 'POST', body: fd })
            .then(function () { window.location.reload(); })
            .catch(function () { window.location.reload(); });
    });

    window.openDuplicateModal = function (id, title) {
        duplicateIdInput.value = id;
        duplicateNameEl.textContent = title;
        duplicateOverlay.classList.add('open');
        duplicateOverlay.querySelector('.btn-primary').focus();
    };
    window.closeDuplicateModal = function () { duplicateOverlay.classList.remove('open'); };

    [deleteOverlay, duplicateOverlay].forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            deleteOverlay.classList.remove('open');
            duplicateOverlay.classList.remove('open');
            closeShareModal();
            if (typeof closeCustomDomainModal === 'function') closeCustomDomainModal();
            rxCloseSidebar();
        }
    });

    // ── Share modal ─────────────────────────────────────────────────────────────
    var shareModal  = document.getElementById('rx-share-modal');
    var shareInput  = document.getElementById('rx-share-link-input');
    var shareLinkWrap = document.getElementById('rx-share-link-wrap');
    var shareGenBtn   = document.getElementById('rx-share-generate-btn');

    window.openShareModal = function (id, token, isPublic) {
        document.getElementById('rx-share-id').value = id;
        shareLinkWrap.style.display = 'none';
        shareGenBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg> Generate Link';
        shareGenBtn.disabled = false;
        if (token && isPublic) {
            shareInput.value = window.location.origin + '/projects/resumex/share/' + token;
            shareLinkWrap.style.display = 'block';
            shareGenBtn.textContent = 'Disable Sharing';
        }
        shareModal.classList.add('open');
    };
    window.closeShareModal = function () { shareModal.classList.remove('open'); };

    window.generateShare = function () {
        var id    = document.getElementById('rx-share-id').value;
        var token = document.getElementById('rx-share-csrf').value;
        shareGenBtn.disabled = true;
        shareGenBtn.textContent = 'Working…';
        var fd = new FormData();
        fd.append('_csrf_token', token);
        fd.append('id', id);
        fetch('/projects/resumex/share/generate', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success && d.is_public === true && d.token) {
                    // Sharing enabled — show the public link
                    shareInput.value = window.location.origin + '/projects/resumex/share/' + d.token;
                    shareLinkWrap.style.display = 'block';
                    shareGenBtn.textContent = 'Disable Sharing';
                    shareGenBtn.disabled = false;
                } else if (d.success) {
                    // Sharing disabled — close modal and refresh card state
                    closeShareModal();
                    window.location.reload();
                } else {
                    alert(d.error || 'Failed to update sharing.');
                    shareGenBtn.disabled = false;
                    shareGenBtn.textContent = 'Generate Link';
                }
            })
            .catch(function () {
                alert('Network error.');
                shareGenBtn.disabled = false;
                shareGenBtn.textContent = 'Generate Link';
            });
    };

    window.copyShareLink = function () {
        var input = document.getElementById('rx-share-link-input');
        var copyBtn = input.nextElementSibling;
        navigator.clipboard.writeText(input.value).then(function () {
            if (copyBtn) { var t = copyBtn.textContent; copyBtn.textContent = 'Copied!'; setTimeout(function(){ copyBtn.textContent = t; }, 1500); }
        }).catch(function () {
            input.select();
            document.execCommand('copy');
        });
    };

    if (shareModal) {
        shareModal.addEventListener('click', function (e) {
            if (e.target === shareModal) closeShareModal();
        });
    }

    // ── Custom Domain modal ──────────────────────────────────────────────────────
    var customDomainModal = document.getElementById('rx-custom-domain-modal');
    window.openCustomDomainModal = function () {
        if (customDomainModal) customDomainModal.classList.add('open');
    };
    window.closeCustomDomainModal = function () {
        if (customDomainModal) customDomainModal.classList.remove('open');
    };
    if (customDomainModal) {
        customDomainModal.addEventListener('click', function (e) {
            if (e.target === customDomainModal) closeCustomDomainModal();
        });
    }

    // ── Mobile sidebar toggle ────────────────────────────────────────────────────
    var rxSidebar  = document.getElementById('rxSidebar');
    var rxOverlay  = document.getElementById('rxSidebarOverlay');
    var rxToggleBtn= document.getElementById('rxSidebarToggle');

    function rxOpenSidebar() {
        if (!rxSidebar) return;
        rxSidebar.classList.add('open');
        rxOverlay.classList.add('active');
        rxToggleBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function rxCloseSidebar() {
        if (!rxSidebar) return;
        rxSidebar.classList.remove('open');
        rxOverlay.classList.remove('active');
        rxToggleBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (rxToggleBtn) {
        rxToggleBtn.addEventListener('click', function () {
            rxSidebar.classList.contains('open') ? rxCloseSidebar() : rxOpenSidebar();
        });
    }
    if (rxOverlay) {
        rxOverlay.addEventListener('click', rxCloseSidebar);
    }
    // Close on nav link click (mobile UX)
    if (rxSidebar) {
        rxSidebar.querySelectorAll('.rx-nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) rxCloseSidebar();
            });
        });
    }
    // Close on resize above breakpoint
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) rxCloseSidebar();
    });
    // Close on Escape — handled by the shared keydown listener above
}());
</script>
<?php require BASE_PATH . '/views/partials/eco-qr-modal.php'; ?>
<?php View::end(); ?>
