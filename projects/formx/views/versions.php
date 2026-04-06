<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('main'); ?>
<?php View::section('styles'); ?><style>.main{padding:0!important;}</style><?php View::endSection(); ?>

<?php View::section('content'); ?>
<style>
    .fx-layout{display:flex;min-height:calc(100vh - 70px);}
    .fx-sidebar{width:220px;flex-shrink:0;background:var(--bg-card);border-right:1px solid var(--border-color);display:flex;flex-direction:column;padding:24px 0 20px;position:sticky;top:0;height:calc(100vh - 70px);overflow-y:auto;}
    .fx-sidebar-logo{display:flex;align-items:center;gap:10px;padding:0 16px 18px;border-bottom:1px solid var(--border-color);margin-bottom:10px;}
    .fx-sidebar-logo-icon{width:32px;height:32px;border-radius:7px;background:linear-gradient(135deg,var(--cyan),var(--purple));display:flex;align-items:center;justify-content:center;font-weight:800;color:#06060a;font-size:.85rem;flex-shrink:0;}
    .fx-sidebar-logo-text{font-size:1rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .fx-nav-section{padding:2px 8px;margin-bottom:2px;}
    .fx-nav-title{font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);padding:7px 8px 3px;opacity:.6;}
    .fx-nav-link{display:flex;align-items:center;gap:9px;padding:7px 9px;border-radius:7px;color:var(--text-secondary);text-decoration:none;font-size:.845rem;font-weight:500;transition:background .15s,color .15s;position:relative;}
    .fx-nav-link:hover{background:rgba(0,240,255,.07);color:var(--text-primary);text-decoration:none;}
    .fx-nav-link.active{background:rgba(0,240,255,.1);color:var(--cyan);}
    .fx-nav-link.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--cyan);border-radius:0 3px 3px 0;}
    .fx-nav-link i{width:16px;flex-shrink:0;text-align:center;opacity:.75;}
    .fx-main{flex:1;min-width:0;padding:28px;overflow-y:auto;}
    .ver-item{background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:16px 18px;margin-bottom:10px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
    .ver-idx{width:28px;height:28px;border-radius:7px;background:rgba(0,240,255,.1);border:1px solid rgba(0,240,255,.2);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;color:var(--cyan);flex-shrink:0;}
    .ver-meta{flex:1;min-width:0;}
    .ver-title{font-weight:600;font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .ver-time{font-size:.78rem;color:var(--text-secondary);margin-top:2px;}
    .ver-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:700;text-transform:uppercase;flex-shrink:0;}
    .fx-sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;}
    .fx-sidebar-toggle{display:none;position:fixed;bottom:24px;right:20px;z-index:100;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;cursor:pointer;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,240,255,.4);color:#06060a;font-size:1.1rem;}
    @media(max-width:900px){
        .fx-sidebar{position:fixed;left:-240px;top:0;height:100vh;z-index:100;width:220px;transition:left .25s;padding-top:70px;}
        .fx-sidebar.open{left:0;}
        .fx-sidebar-overlay{display:block;opacity:0;pointer-events:none;transition:opacity .25s;}
        .fx-sidebar-overlay.active{opacity:1;pointer-events:all;}
        .fx-sidebar-toggle{display:flex;}
        .fx-main{padding:18px 14px;}
    }
</style>

<div class="fx-layout">
    <aside class="fx-sidebar" id="fxSidebar">
        <div class="fx-sidebar-logo">
            <div class="fx-sidebar-logo-icon"><i class="fas fa-wpforms" style="-webkit-text-fill-color:#06060a;"></i></div>
            <span class="fx-sidebar-logo-text">FormX</span>
        </div>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Workspace</div>
            <a href="/projects/formx" class="fx-nav-link"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a href="/projects/formx/create" class="fx-nav-link"><i class="fas fa-plus-circle"></i><span>New Form</span></a>
            <a href="/projects/formx/forms" class="fx-nav-link"><i class="fas fa-list"></i><span>My Forms</span></a>
        </div>
        <div class="fx-nav-section">
            <div class="fx-nav-title">This Form</div>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/edit" class="fx-nav-link"><i class="fas fa-edit"></i><span>Edit Builder</span></a>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/submissions" class="fx-nav-link"><i class="fas fa-inbox"></i><span>Submissions</span></a>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/analytics" class="fx-nav-link"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/versions" class="fx-nav-link active"><i class="fas fa-history"></i><span>Versions</span></a>
        </div>
        <?php if (!empty($sidebarForms)): ?>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Recent Forms</div>
            <?php foreach ($sidebarForms as $sf): ?>
            <a href="/projects/formx/<?= (int)$sf['id'] ?>/edit"
               class="fx-nav-link <?= (int)$sf['id'] === (int)$form['id'] ? 'active' : '' ?>"
               title="<?= htmlspecialchars($sf['title']) ?>">
                <i class="fas fa-file-alt" style="font-size:.75rem;"></i>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($sf['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>

    <main class="fx-main">
        <div style="margin-bottom:14px;font-size:.82rem;">
            <a href="/projects/formx/<?= (int)$form['id'] ?>/edit" style="color:var(--text-secondary);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Back to form
            </a>
        </div>

        <div style="margin-bottom:22px;">
            <h1 style="font-size:1.4rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0 0 3px;">
                Version History
            </h1>
            <p style="color:var(--text-secondary);font-size:.85rem;margin:0;"><?= View::e($form['title']) ?> — up to 20 recent snapshots saved automatically on each save.</p>
        </div>

        <?php if (Helpers::hasFlash('success')): ?>
        <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:9px 14px;border-radius:7px;margin-bottom:18px;font-size:.875rem;">
            <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
        </div>
        <?php endif; ?>

        <?php if (empty($versions)): ?>
        <div style="text-align:center;padding:52px 20px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;color:var(--text-secondary);">
            <i class="fas fa-history" style="font-size:2.5rem;opacity:.2;display:block;margin-bottom:12px;"></i>
            <p>No version history yet.</p>
            <p style="font-size:.82rem;margin-top:6px;">A snapshot is automatically saved each time you save the form. Edit and save the form to create your first snapshot.</p>
        </div>
        <?php else: ?>
        <?php
        $statusColors = ['active' => 'var(--green)', 'inactive' => 'var(--red)', 'draft' => 'var(--orange)'];
        foreach ($versions as $idx => $ver):
        ?>
        <div class="ver-item">
            <div class="ver-idx"><?= count($versions) - $idx ?></div>
            <div class="ver-meta">
                <div class="ver-title"><?= View::e($ver['title']) ?></div>
                <div class="ver-time">
                    <i class="fas fa-clock"></i> <?= date('M j, Y H:i', strtotime($ver['created_at'])) ?>
                    <?php if (!empty($ver['note'])): ?>
                    &nbsp;·&nbsp; <?= View::e($ver['note']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <span class="ver-badge" style="background:rgba(0,240,255,.08);border:1px solid rgba(0,240,255,.15);color:<?= $statusColors[$ver['status']] ?? 'var(--text-secondary)' ?>;">
                <?= htmlspecialchars(ucfirst($ver['status'])) ?>
            </span>
            <form method="POST" action="/projects/formx/<?= (int)$form['id'] ?>/versions/<?= (int)$ver['id'] ?>/restore"
                  style="flex-shrink:0;" onsubmit="return confirm('Restore this version? A snapshot of the current form will be saved first.');">
                <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                <button type="submit" style="padding:6px 14px;background:rgba(0,240,255,.08);border:1px solid rgba(0,240,255,.2);border-radius:7px;color:var(--cyan);cursor:pointer;font-size:.78rem;font-weight:600;display:inline-flex;align-items:center;gap:5px;">
                    <i class="fas fa-undo"></i> Restore
                </button>
            </form>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<div class="fx-sidebar-overlay" id="fxOverlay"></div>
<button class="fx-sidebar-toggle" id="fxToggle"><i class="fas fa-bars"></i></button>
<script>
(function(){
    const s=document.getElementById('fxSidebar'),o=document.getElementById('fxOverlay'),t=document.getElementById('fxToggle');
    t.addEventListener('click',()=>{const open=s.classList.toggle('open');o.classList.toggle('active',open);t.innerHTML=open?'<i class="fas fa-times"></i>':'<i class="fas fa-bars"></i>';});
    o.addEventListener('click',()=>{s.classList.remove('open');o.classList.remove('active');t.innerHTML='<i class="fas fa-bars"></i>';});
})();
</script>

<?php View::endSection(); ?>
