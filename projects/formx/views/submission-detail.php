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
    .fx-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:22px 24px;margin-bottom:20px;}
    .fx-field-row{display:flex;gap:16px;padding:14px 0;border-bottom:1px solid var(--border-color);}
    .fx-field-row:last-child{border-bottom:none;}
    .fx-field-label{width:180px;flex-shrink:0;font-size:.8rem;font-weight:600;color:var(--text-secondary);padding-top:2px;}
    .fx-field-value{flex:1;font-size:.875rem;word-break:break-word;}
    .fx-sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;}
    .fx-sidebar-toggle{display:none;position:fixed;bottom:24px;right:20px;z-index:100;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;cursor:pointer;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,240,255,.4);color:#06060a;font-size:1.1rem;}
    @media(max-width:900px){
        .fx-sidebar{position:fixed;left:-240px;top:0;height:100vh;z-index:100;width:220px;transition:left .25s;padding-top:70px;}
        .fx-sidebar.open{left:0;}
        .fx-sidebar-overlay{display:block;opacity:0;pointer-events:none;transition:opacity .25s;}
        .fx-sidebar-overlay.active{opacity:1;pointer-events:all;}
        .fx-sidebar-toggle{display:flex;}
        .fx-main{padding:18px 14px;}
        .fx-field-row{flex-direction:column;gap:4px;}
        .fx-field-label{width:auto;}
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
        <!-- Breadcrumb -->
        <div style="margin-bottom:18px;font-size:.82rem;color:var(--text-secondary);">
            <a href="/projects/formx/forms" style="color:var(--text-secondary);text-decoration:none;">My Forms</a>
            <span style="margin:0 6px;">›</span>
            <a href="/projects/formx/<?= (int)$form['id'] ?>/submissions" style="color:var(--text-secondary);text-decoration:none;"><?= View::e($form['title']) ?></a>
            <span style="margin:0 6px;">›</span>
            <span style="color:var(--text-primary);">Submission #<?= (int)$sub['id'] ?></span>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <h1 style="font-size:1.3rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0;">
                Submission #<?= (int)$sub['id'] ?>
            </h1>
            <div style="display:flex;gap:8px;">
                <a href="/projects/formx/<?= (int)$form['id'] ?>/submissions" style="padding:7px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);text-decoration:none;font-size:.82rem;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <form method="POST" action="/admin/formx/<?= (int)$form['id'] ?>/submissions/<?= (int)$sub['id'] ?>/delete"
                      style="display:inline;" onsubmit="return confirm('Delete this submission?');">
                    <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
                    <button type="submit" style="padding:7px 14px;background:rgba(255,107,107,.08);border:1px solid rgba(255,107,107,.25);border-radius:8px;color:var(--red);font-size:.82rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Meta info -->
        <div class="fx-card" style="display:flex;gap:24px;flex-wrap:wrap;padding:16px 22px;">
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin-bottom:3px;">Submitted</div>
                <div style="font-size:.9rem;font-weight:600;"><?= date('M j, Y H:i:s', strtotime($sub['created_at'])) ?></div>
            </div>
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin-bottom:3px;">IP Address</div>
                <div style="font-size:.9rem;font-weight:600;font-family:monospace;"><?= View::e($sub['ip_address'] ?? '—') ?></div>
            </div>
            <?php if (!empty($sub['device'])): ?>
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin-bottom:3px;">Device</div>
                <div style="font-size:.9rem;font-weight:600;"><?= View::e($sub['device']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($sub['browser'])): ?>
            <div>
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin-bottom:3px;">Browser</div>
                <div style="font-size:.9rem;font-weight:600;"><?= View::e($sub['browser']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Field values -->
        <div class="fx-card">
            <h2 style="font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin:0 0 16px;">Submitted Data</h2>
            <?php
            $dataFields = array_filter($form['fields'], fn($f) => !in_array($f['type'] ?? '', ['divider','heading','paragraph']));
            foreach ($dataFields as $field):
                $key = $field['name'] ?? '';
                $val = $sub['data'][$key] ?? null;
                if ($val === null) continue;
                if (is_array($val)) $val = implode(', ', $val);
            ?>
            <div class="fx-field-row">
                <div class="fx-field-label"><?= View::e($field['label'] ?? $key) ?></div>
                <div class="fx-field-value"><?= $val !== '' ? View::e((string)$val) : '<span style="color:var(--text-secondary);font-style:italic;">—</span>' ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Raw user-agent -->
        <?php if (!empty($sub['user_agent'])): ?>
        <details style="margin-top:16px;">
            <summary style="font-size:.78rem;color:var(--text-secondary);cursor:pointer;">User-Agent string</summary>
            <div style="font-size:.75rem;color:var(--text-secondary);font-family:monospace;margin-top:6px;word-break:break-all;padding:10px;background:var(--bg-secondary);border-radius:6px;">
                <?= htmlspecialchars($sub['user_agent']) ?>
            </div>
        </details>
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
