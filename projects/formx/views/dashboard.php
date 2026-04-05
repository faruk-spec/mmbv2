<?php use Core\View; use Core\Auth; use Core\Helpers; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
    .main { padding: 0 !important; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<?php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$statusColors = ['active' => 'var(--green)', 'inactive' => 'var(--red)', 'draft' => 'var(--orange)'];
?>
<style>
    /* Layout */
    .fx-layout { display: flex; min-height: calc(100vh - 70px); }

    /* Sidebar */
    .fx-sidebar {
        width: 240px; flex-shrink: 0;
        background: var(--bg-card); border-right: 1px solid var(--border-color);
        display: flex; flex-direction: column; padding: 24px 0 20px;
        position: sticky; top: 0; height: calc(100vh - 70px); overflow-y: auto;
    }
    .fx-sidebar-logo {
        display: flex; align-items: center; gap: 10px;
        padding: 0 18px 20px; border-bottom: 1px solid var(--border-color); margin-bottom: 10px;
    }
    .fx-sidebar-logo-icon {
        width: 34px; height: 34px; border-radius: 8px;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; color: #06060a; font-size: .9rem; flex-shrink: 0;
    }
    .fx-sidebar-logo-text {
        font-size: 1.05rem; font-weight: 800;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .fx-nav-section { padding: 2px 10px; margin-bottom: 2px; }
    .fx-nav-title {
        font-size: .66rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        color: var(--text-secondary); padding: 8px 8px 3px; opacity: .6;
    }
    .fx-nav-link {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; border-radius: 8px; color: var(--text-secondary);
        text-decoration: none; font-size: .875rem; font-weight: 500;
        transition: background .15s, color .15s; position: relative;
    }
    .fx-nav-link:hover { background: rgba(0,240,255,.07); color: var(--text-primary); text-decoration: none; }
    .fx-nav-link.active { background: rgba(0,240,255,.1); color: var(--cyan); }
    .fx-nav-link.active::before {
        content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
        width: 3px; height: 60%; background: var(--cyan); border-radius: 0 3px 3px 0;
    }
    .fx-nav-link i { width: 18px; flex-shrink: 0; text-align: center; opacity: .75; }
    .fx-nav-link.active i { opacity: 1; }
    .fx-nav-badge {
        margin-left: auto; background: rgba(0,240,255,.12); color: var(--cyan);
        border-radius: 20px; padding: 1px 8px; font-size: .7rem; font-weight: 700;
    }

    /* Main content */
    .fx-main { flex: 1; min-width: 0; padding: 28px 28px; overflow-y: auto; }

    /* Header */
    .fx-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 26px; flex-wrap: wrap; }
    .fx-header h1 {
        font-size: 1.8rem; font-weight: 800; letter-spacing: -.5px; line-height: 1.1;
        background: linear-gradient(135deg, var(--cyan) 0%, var(--purple) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0 0 4px;
    }
    .fx-header p { color: var(--text-secondary); font-size: .88rem; margin: 0; }
    .fx-btn-create {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        color: #06060a; font-weight: 700; font-size: .875rem; border-radius: 10px;
        text-decoration: none; transition: all .2s; white-space: nowrap;
        box-shadow: 0 4px 20px rgba(0,240,255,.25); flex-shrink: 0;
    }
    .fx-btn-create:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,240,255,.4); color: #06060a; text-decoration: none; }

    /* Stats */
    .fx-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 26px; }
    .fx-stat {
        background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px;
        padding: 16px; display: flex; align-items: center; gap: 12px; transition: transform .2s;
    }
    .fx-stat:hover { transform: translateY(-2px); }
    .fx-stat-icon { width: 40px; height: 40px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .fx-stat-val { font-size: 1.5rem; font-weight: 800; line-height: 1; margin-bottom: 2px; }
    .fx-stat-label { font-size: .76rem; color: var(--text-secondary); font-weight: 500; }

    /* Cards grid */
    .fx-section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .fx-section-head h2 { font-size: 1rem; font-weight: 700; margin: 0; }
    .fx-view-all { font-size: .8rem; color: var(--cyan); text-decoration: none; }
    .fx-view-all:hover { text-decoration: underline; }
    .fx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .fx-form-card {
        background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px;
        padding: 18px; display: flex; flex-direction: column; gap: 10px;
        transition: all .2s; position: relative;
    }
    .fx-form-card:hover { border-color: rgba(0,240,255,.3); box-shadow: 0 6px 24px rgba(0,240,255,.08); transform: translateY(-2px); }
    .fx-form-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .fx-form-icon { width: 36px; height: 36px; border-radius: 8px; background: rgba(0,240,255,.1); display: flex; align-items: center; justify-content: center; color: var(--cyan); flex-shrink: 0; }
    .fx-form-title { font-size: .9rem; font-weight: 700; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
    .fx-status-badge { font-size: .7rem; padding: 2px 9px; border-radius: 20px; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
    .fx-form-meta { font-size: .76rem; color: var(--text-secondary); display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .fx-form-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .fx-action-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 4px;
        padding: 5px 10px; border-radius: 7px; font-size: .74rem; font-weight: 600;
        cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: all .15s;
        background: none; line-height: 1;
    }
    .fx-action-btn.edit { background: rgba(0,240,255,.08); border-color: rgba(0,240,255,.2); color: var(--cyan); }
    .fx-action-btn.edit:hover { background: rgba(0,240,255,.18); text-decoration: none; color: var(--cyan); }
    .fx-action-btn.view { background: rgba(153,69,255,.08); border-color: rgba(153,69,255,.2); color: var(--purple); }
    .fx-action-btn.view:hover { background: rgba(153,69,255,.18); text-decoration: none; color: var(--purple); }
    .fx-action-btn.subs { background: rgba(0,255,136,.07); border-color: rgba(0,255,136,.18); color: var(--green); }
    .fx-action-btn.subs:hover { background: rgba(0,255,136,.15); text-decoration: none; color: var(--green); }
    .fx-action-btn.del { background: rgba(255,107,107,.07); border-color: rgba(255,107,107,.18); color: var(--red); }
    .fx-action-btn.del:hover { background: rgba(255,107,107,.15); text-decoration: none; color: var(--red); }

    /* Empty state */
    .fx-empty { text-align: center; padding: 52px 20px; color: var(--text-secondary); }
    .fx-empty i { font-size: 3rem; opacity: .2; display: block; margin-bottom: 14px; }

    /* Mobile sidebar */
    .fx-sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 99; }
    .fx-sidebar-toggle {
        display: none; position: fixed; bottom: 24px; right: 20px; z-index: 100;
        width: 48px; height: 48px; border-radius: 50%;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        border: none; cursor: pointer; align-items: center; justify-content: center;
        box-shadow: 0 4px 18px rgba(0,240,255,.4); color: #06060a; font-size: 1.1rem;
    }

    @media (max-width: 900px) {
        .fx-sidebar { position: fixed; left: -260px; top: 0; height: 100vh; z-index: 100; width: 240px; transition: left .25s; padding-top: 70px; }
        .fx-sidebar.open { left: 0; }
        .fx-sidebar-overlay { display: block; opacity: 0; pointer-events: none; transition: opacity .25s; }
        .fx-sidebar-overlay.active { opacity: 1; pointer-events: all; }
        .fx-sidebar-toggle { display: flex; }
        .fx-main { padding: 20px 16px; margin-left: 0; width: 100%; }
        .fx-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .fx-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .fx-stats { grid-template-columns: 1fr 1fr; gap: 8px; }
        .fx-header h1 { font-size: 1.4rem; }
    }
</style>

<div class="fx-layout">

    <!-- Sidebar -->
    <aside class="fx-sidebar" id="fxSidebar">
        <div class="fx-sidebar-logo">
            <div class="fx-sidebar-logo-icon"><i class="fas fa-wpforms" style="-webkit-text-fill-color:#06060a;"></i></div>
            <span class="fx-sidebar-logo-text">FormX</span>
        </div>

        <div class="fx-nav-section">
            <div class="fx-nav-title">Workspace</div>
            <a href="/projects/formx" class="fx-nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>
            <a href="/projects/formx/create" class="fx-nav-link <?= $activePage === 'create' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i><span>New Form</span>
            </a>
            <a href="/projects/formx/forms" class="fx-nav-link <?= $activePage === 'forms' ? 'active' : '' ?>">
                <i class="fas fa-list"></i><span>My Forms</span>
                <?php if ($totalForms > 0): ?>
                <span class="fx-nav-badge"><?= $totalForms ?></span>
                <?php endif; ?>
            </a>
        </div>

        <?php if (!empty($recentForms)): ?>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Recent Forms</div>
            <?php foreach (array_slice($recentForms, 0, 6) as $rf): ?>
            <a href="/projects/formx/<?= (int)$rf['id'] ?>/edit" class="fx-nav-link" title="<?= htmlspecialchars($rf['title']) ?>">
                <i class="fas fa-file-alt" style="font-size:.75rem;"></i>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($rf['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Main -->
    <main class="fx-main">

        <?php if (Helpers::hasFlash('success')): ?>
        <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:11px 16px;border-radius:8px;margin-bottom:20px;font-size:.875rem;">
            <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
        </div>
        <?php endif; ?>
        <?php if (Helpers::hasFlash('error')): ?>
        <div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:11px 16px;border-radius:8px;margin-bottom:20px;font-size:.875rem;">
            <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="fx-header">
            <div>
                <h1>FormX</h1>
                <p>Build forms, collect responses, track submissions.</p>
            </div>
            <a href="/projects/formx/create" class="fx-btn-create"><i class="fas fa-plus"></i> New Form</a>
        </div>

        <!-- Stats -->
        <div class="fx-stats">
            <?php
            $stats = [
                ['val' => $totalForms,       'label' => 'Total Forms',       'icon' => 'fa-wpforms',   'color' => 'var(--cyan)'],
                ['val' => $activeForms,      'label' => 'Active Forms',      'icon' => 'fa-toggle-on', 'color' => 'var(--green)'],
                ['val' => $draftForms,       'label' => 'Drafts',            'icon' => 'fa-pen',       'color' => 'var(--orange)'],
                ['val' => $totalSubmissions, 'label' => 'Total Submissions', 'icon' => 'fa-inbox',     'color' => 'var(--purple)'],
            ];
            foreach ($stats as $s): ?>
            <div class="fx-stat" style="border-color:<?= $s['color'] ?>22;">
                <div class="fx-stat-icon" style="background:<?= $s['color'] ?>18;color:<?= $s['color'] ?>;">
                    <i class="fas <?= $s['icon'] ?>"></i>
                </div>
                <div>
                    <div class="fx-stat-val" style="color:<?= $s['color'] ?>;"><?= number_format((int)$s['val']) ?></div>
                    <div class="fx-stat-label"><?= $s['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Recent Forms -->
        <div class="fx-section-head">
            <h2>Recent Forms</h2>
            <?php if (!empty($recentForms)): ?>
            <a href="/projects/formx/forms" class="fx-view-all">View all <i class="fas fa-arrow-right"></i></a>
            <?php endif; ?>
        </div>

        <?php if (empty($recentForms)): ?>
        <div class="fx-empty" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;">
            <i class="fas fa-wpforms"></i>
            <p style="margin-bottom:16px;">You haven't created any forms yet.</p>
            <a href="/projects/formx/create" class="fx-btn-create" style="display:inline-flex;">
                <i class="fas fa-plus"></i> Create your first form
            </a>
        </div>
        <?php else: ?>
        <div class="fx-grid">
            <?php foreach ($recentForms as $f):
                $sColor = $statusColors[$f['status']] ?? 'var(--text-secondary)';
            ?>
            <div class="fx-form-card">
                <div class="fx-form-card-top">
                    <div class="fx-form-icon"><i class="fas fa-wpforms"></i></div>
                    <div class="fx-form-title" title="<?= View::e($f['title']) ?>"><?= View::e($f['title']) ?></div>
                    <span class="fx-status-badge" style="background:<?= $sColor ?>18;color:<?= $sColor ?>;"><?= ucfirst($f['status']) ?></span>
                </div>
                <div class="fx-form-meta">
                    <span><i class="fas fa-inbox" style="margin-right:3px;"></i><?= (int)($f['submissions_count'] ?? 0) ?> submissions</span>
                    <span><i class="fas fa-clock" style="margin-right:3px;"></i><?= date('M j', strtotime($f['updated_at'] ?? $f['created_at'])) ?></span>
                </div>
                <div class="fx-form-actions">
                    <a href="/projects/formx/<?= $f['id'] ?>/edit" class="fx-action-btn edit"><i class="fas fa-edit"></i> Edit</a>
                    <?php if ($f['status'] === 'active'): ?>
                    <a href="/forms/<?= View::e($f['slug']) ?>" target="_blank" class="fx-action-btn view"><i class="fas fa-external-link-alt"></i> Preview</a>
                    <?php endif; ?>
                    <a href="/projects/formx/<?= $f['id'] ?>/submissions" class="fx-action-btn subs"><i class="fas fa-inbox"></i> <?= (int)($f['submissions_count'] ?? 0) ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </main>
</div>

<div class="fx-sidebar-overlay" id="fxOverlay"></div>
<button class="fx-sidebar-toggle" id="fxToggle" aria-label="Open menu"><i class="fas fa-bars"></i></button>

<script>
(function() {
    const sidebar  = document.getElementById('fxSidebar');
    const overlay  = document.getElementById('fxOverlay');
    const toggle   = document.getElementById('fxToggle');
    function open()  { sidebar.classList.add('open'); overlay.classList.add('active'); toggle.innerHTML = '<i class="fas fa-times"></i>'; }
    function close() { sidebar.classList.remove('open'); overlay.classList.remove('active'); toggle.innerHTML = '<i class="fas fa-bars"></i>'; }
    toggle.addEventListener('click', () => sidebar.classList.contains('open') ? close() : open());
    overlay.addEventListener('click', close);
})();
</script>

<?php View::endSection(); ?>
