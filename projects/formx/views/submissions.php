<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>.main { padding: 0 !important; }</style>
<?php View::endSection(); ?>

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
    .fx-nav-link.active i{opacity:1;}
    .fx-main{flex:1;min-width:0;padding:28px;overflow-y:auto;}
    .fx-table-wrap{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:auto;}
    .fx-table{width:100%;border-collapse:collapse;font-size:.855rem;white-space:nowrap;}
    .fx-table th{padding:11px 14px;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);border-bottom:1px solid var(--border-color);background:var(--bg-secondary);}
    .fx-table td{padding:12px 14px;border-bottom:1px solid var(--border-color);vertical-align:top;white-space:normal;max-width:200px;overflow:hidden;text-overflow:ellipsis;}
    .fx-table tr:last-child td{border-bottom:none;}
    .fx-table tr:hover td{background:rgba(0,240,255,.02);}
    .fx-btn-create{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:linear-gradient(135deg,var(--cyan),var(--purple));color:#06060a;font-weight:700;font-size:.875rem;border-radius:9px;text-decoration:none;}
    .fx-action-btn{display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:5px 10px;border-radius:6px;font-size:.74rem;font-weight:600;cursor:pointer;text-decoration:none;border:1px solid transparent;transition:all .15s;background:none;line-height:1;}
    .fx-action-btn.del{background:rgba(255,107,107,.07);border-color:rgba(255,107,107,.18);color:var(--red);}
    .fx-action-btn.del:hover{background:rgba(255,107,107,.15);text-decoration:none;color:var(--red);}
    .fx-empty{text-align:center;padding:52px 20px;color:var(--text-secondary);}
    .fx-empty i{font-size:3rem;opacity:.2;display:block;margin-bottom:14px;}
    .fx-pager{display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;}
    .fx-pager a,.fx-pager span{padding:5px 12px;border-radius:6px;font-size:.82rem;font-weight:600;text-decoration:none;}
    .fx-pager a{background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-secondary);}
    .fx-pager a:hover{background:rgba(0,240,255,.08);border-color:rgba(0,240,255,.3);color:var(--cyan);}
    .fx-pager span.current{background:rgba(0,240,255,.15);border:1px solid rgba(0,240,255,.3);color:var(--cyan);}
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
        <?php if (Helpers::hasFlash('success')): ?>
        <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:9px 14px;border-radius:7px;margin-bottom:18px;font-size:.875rem;">
            <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="margin-bottom:8px;">
                    <a href="/projects/formx/<?= $form['id'] ?>/edit" style="color:var(--text-secondary);text-decoration:none;font-size:.82rem;">
                        <i class="fas fa-arrow-left"></i> Back to form
                    </a>
                </div>
                <h1 style="font-size:1.4rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0 0 3px;">
                    Submissions
                </h1>
                <p style="color:var(--text-secondary);font-size:.85rem;margin:0;"><?= View::e($form['title']) ?></p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <a href="/admin/formx/<?= $form['id'] ?>/export" style="padding:8px 14px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);text-decoration:none;font-size:.8rem;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </div>

        <?php if (empty($submissions)): ?>
        <div class="fx-empty" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;">
            <i class="fas fa-inbox"></i>
            <p>No submissions yet.</p>
            <?php if ($form['status'] === 'active'): ?>
            <a href="/forms/<?= View::e($form['slug']) ?>" target="_blank" class="fx-btn-create" style="display:inline-flex;margin-top:12px;">
                <i class="fas fa-external-link-alt"></i> View Public Form
            </a>
            <?php else: ?>
            <p style="font-size:.8rem;color:var(--orange);margin-top:8px;"><i class="fas fa-exclamation-triangle"></i> Form is not active. Activate it to receive submissions.</p>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="fx-table-wrap">
            <table class="fx-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Submitted</th>
                        <th>IP</th>
                        <?php foreach ($form['fields'] as $field):
                            if (in_array($field['type'], ['divider','heading','paragraph','hidden'])) continue;
                        ?>
                        <th><?= View::e($field['label'] ?? $field['name'] ?? 'Field') ?></th>
                        <?php endforeach; ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($submissions as $i => $sub): ?>
                <tr>
                    <td style="color:var(--text-secondary);font-size:.8rem;"><?= $i+1 ?></td>
                    <td style="font-size:.8rem;white-space:nowrap;"><?= date('M j, Y H:i', strtotime($sub['created_at'])) ?></td>
                    <td style="color:var(--text-secondary);font-size:.78rem;"><?= View::e($sub['ip_address'] ?? '') ?></td>
                    <?php foreach ($form['fields'] as $field):
                        if (in_array($field['type'], ['divider','heading','paragraph','hidden'])) continue;
                        $key = $field['name'] ?? '';
                        $val = $sub['data'][$key] ?? '';
                        if (is_array($val)) $val = implode(', ', $val);
                    ?>
                    <td style="font-size:.83rem;"><?= View::e(mb_strimwidth((string)$val, 0, 120, '…')) ?></td>
                    <?php endforeach; ?>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                        <a href="/projects/formx/<?= $form['id'] ?>/submissions/<?= $sub['id'] ?>"
                           class="fx-action-btn" style="background:rgba(0,240,255,.08);border-color:rgba(0,240,255,.2);color:var(--cyan);"
                           title="View"><i class="fas fa-eye"></i></a>
                        <form method="POST" action="/admin/formx/<?= $form['id'] ?>/submissions/<?= $sub['id'] ?>/delete"
                              style="display:inline;" onsubmit="return confirm('Delete this submission?');">
                            <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
                            <button type="submit" class="fx-action-btn del" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($pagination['total'] > 1): ?>
            <div class="fx-pager">
                <?php if ($pagination['current'] > 1): ?>
                <a href="?page=<?= $pagination['current'] - 1 ?>">← Prev</a>
                <?php endif; ?>
                <span class="current">Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?></span>
                <?php if ($pagination['current'] < $pagination['total']): ?>
                <a href="?page=<?= $pagination['current'] + 1 ?>">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
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
