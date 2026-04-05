<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>.main { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<?php $statusColors = ['active'=>'var(--green)','inactive'=>'var(--red)','draft'=>'var(--orange)']; ?>
<style>
    .fx-layout{display:flex;min-height:calc(100vh - 70px);}
    .fx-sidebar{width:240px;flex-shrink:0;background:var(--bg-card);border-right:1px solid var(--border-color);display:flex;flex-direction:column;padding:24px 0 20px;position:sticky;top:0;height:calc(100vh - 70px);overflow-y:auto;}
    .fx-sidebar-logo{display:flex;align-items:center;gap:10px;padding:0 18px 20px;border-bottom:1px solid var(--border-color);margin-bottom:10px;}
    .fx-sidebar-logo-icon{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--cyan),var(--purple));display:flex;align-items:center;justify-content:center;font-weight:800;color:#06060a;font-size:.9rem;flex-shrink:0;}
    .fx-sidebar-logo-text{font-size:1.05rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .fx-nav-section{padding:2px 10px;margin-bottom:2px;}
    .fx-nav-title{font-size:.66rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);padding:8px 8px 3px;opacity:.6;}
    .fx-nav-link{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;color:var(--text-secondary);text-decoration:none;font-size:.875rem;font-weight:500;transition:background .15s,color .15s;position:relative;}
    .fx-nav-link:hover{background:rgba(0,240,255,.07);color:var(--text-primary);text-decoration:none;}
    .fx-nav-link.active{background:rgba(0,240,255,.1);color:var(--cyan);}
    .fx-nav-link.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--cyan);border-radius:0 3px 3px 0;}
    .fx-nav-link i{width:18px;flex-shrink:0;text-align:center;opacity:.75;}
    .fx-nav-link.active i{opacity:1;}
    .fx-nav-badge{margin-left:auto;background:rgba(0,240,255,.12);color:var(--cyan);border-radius:20px;padding:1px 8px;font-size:.7rem;font-weight:700;}
    .fx-main{flex:1;min-width:0;padding:28px;}
    /* Table */
    .fx-table-wrap{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;overflow:hidden;}
    .fx-table{width:100%;border-collapse:collapse;font-size:.875rem;}
    .fx-table th{padding:12px 16px;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);border-bottom:1px solid var(--border-color);background:var(--bg-secondary);}
    .fx-table td{padding:14px 16px;border-bottom:1px solid var(--border-color);vertical-align:middle;}
    .fx-table tr:last-child td{border-bottom:none;}
    .fx-table tr:hover td{background:rgba(0,240,255,.02);}
    .fx-status-badge{font-size:.7rem;padding:2px 9px;border-radius:20px;font-weight:600;}
    .fx-action-btn{display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:5px 10px;border-radius:6px;font-size:.74rem;font-weight:600;cursor:pointer;text-decoration:none;border:1px solid transparent;transition:all .15s;background:none;line-height:1;}
    .fx-action-btn.edit{background:rgba(0,240,255,.08);border-color:rgba(0,240,255,.2);color:var(--cyan);}
    .fx-action-btn.edit:hover{background:rgba(0,240,255,.18);text-decoration:none;color:var(--cyan);}
    .fx-action-btn.subs{background:rgba(0,255,136,.07);border-color:rgba(0,255,136,.18);color:var(--green);}
    .fx-action-btn.subs:hover{background:rgba(0,255,136,.15);text-decoration:none;color:var(--green);}
    .fx-action-btn.del{background:rgba(255,107,107,.07);border-color:rgba(255,107,107,.18);color:var(--red);}
    .fx-action-btn.del:hover{background:rgba(255,107,107,.15);text-decoration:none;color:var(--red);}
    .fx-btn-create{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:linear-gradient(135deg,var(--cyan),var(--purple));color:#06060a;font-weight:700;font-size:.875rem;border-radius:9px;text-decoration:none;transition:all .2s;white-space:nowrap;box-shadow:0 4px 16px rgba(0,240,255,.25);}
    .fx-btn-create:hover{transform:translateY(-1px);box-shadow:0 8px 28px rgba(0,240,255,.4);color:#06060a;text-decoration:none;}
    .fx-empty{text-align:center;padding:52px 20px;color:var(--text-secondary);}
    .fx-empty i{font-size:3rem;opacity:.2;display:block;margin-bottom:14px;}
    /* Pagination */
    .fx-pager{display:flex;align-items:center;justify-content:center;gap:8px;padding:16px;}
    .fx-pager a,.fx-pager span{padding:5px 12px;border-radius:6px;font-size:.82rem;font-weight:600;text-decoration:none;}
    .fx-pager a{background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-secondary);}
    .fx-pager a:hover{background:rgba(0,240,255,.08);border-color:rgba(0,240,255,.3);color:var(--cyan);}
    .fx-pager span.current{background:rgba(0,240,255,.15);border:1px solid rgba(0,240,255,.3);color:var(--cyan);}
    /* Mobile */
    .fx-sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;}
    .fx-sidebar-toggle{display:none;position:fixed;bottom:24px;right:20px;z-index:100;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;cursor:pointer;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,240,255,.4);color:#06060a;font-size:1.1rem;}
    @media(max-width:900px){
        .fx-sidebar{position:fixed;left:-260px;top:0;height:100vh;z-index:100;width:240px;transition:left .25s;padding-top:70px;}
        .fx-sidebar.open{left:0;}
        .fx-sidebar-overlay{display:block;opacity:0;pointer-events:none;transition:opacity .25s;}
        .fx-sidebar-overlay.active{opacity:1;pointer-events:all;}
        .fx-sidebar-toggle{display:flex;}
        .fx-main{padding:20px 14px;width:100%;}
        .fx-table th:nth-child(3),.fx-table td:nth-child(3){display:none;}
    }
    @media(max-width:600px){
        .fx-table th:nth-child(4),.fx-table td:nth-child(4){display:none;}
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
            <a href="/projects/formx/forms" class="fx-nav-link active"><i class="fas fa-list"></i><span>My Forms</span></a>
        </div>
        <?php if (!empty($sidebarForms)): ?>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Recent</div>
            <?php foreach ($sidebarForms as $sf): ?>
            <a href="/projects/formx/<?= (int)$sf['id'] ?>/edit" class="fx-nav-link" title="<?= htmlspecialchars($sf['title']) ?>">
                <i class="fas fa-file-alt" style="font-size:.75rem;"></i>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($sf['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>

    <main class="fx-main">
        <?php if (Helpers::hasFlash('success')): ?>
        <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:11px 16px;border-radius:8px;margin-bottom:18px;font-size:.875rem;">
            <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
        </div>
        <?php endif; ?>
        <?php if (Helpers::hasFlash('error')): ?>
        <div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:11px 16px;border-radius:8px;margin-bottom:18px;font-size:.875rem;">
            <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
        </div>
        <?php endif; ?>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0 0 3px;">My Forms</h1>
                <p style="color:var(--text-secondary);font-size:.85rem;margin:0;">Manage all your forms in one place.</p>
            </div>
            <a href="/projects/formx/create" class="fx-btn-create"><i class="fas fa-plus"></i> New Form</a>
        </div>

        <!-- Filters -->
        <form method="GET" action="/projects/formx/forms"
              style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
            <div style="flex:1;min-width:180px;">
                <input type="text" name="search" value="<?= View::e($search) ?>" placeholder="Search forms…"
                       style="width:100%;padding:8px 12px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
            </div>
            <select name="status" style="padding:8px 12px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.875rem;">
                <option value="">All</option>
                <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="draft"    <?= $status === 'draft'    ? 'selected' : '' ?>>Draft</option>
            </select>
            <button type="submit" style="padding:8px 16px;background:var(--cyan);color:#000;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:.875rem;">
                <i class="fas fa-search"></i>
            </button>
            <?php if ($search || $status): ?>
            <a href="/projects/formx/forms" style="padding:8px 14px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;color:var(--text-secondary);text-decoration:none;font-size:.875rem;">Reset</a>
            <?php endif; ?>
        </form>

        <?php if (empty($forms)): ?>
        <div class="fx-empty" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;">
            <i class="fas fa-wpforms"></i>
            <p style="margin-bottom:16px;"><?= $search || $status ? 'No forms match your filter.' : 'No forms yet. Create your first one!' ?></p>
            <a href="/projects/formx/create" class="fx-btn-create" style="display:inline-flex;">
                <i class="fas fa-plus"></i> Create a Form
            </a>
        </div>
        <?php else: ?>
        <div class="fx-table-wrap">
            <table class="fx-table">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>Status</th>
                        <th>Submissions</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($forms as $f):
                    $sColor = $statusColors[$f['status']] ?? 'var(--text-secondary)';
                ?>
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:.875rem;margin-bottom:2px;"><?= View::e($f['title']) ?></div>
                        <div style="font-size:.75rem;color:var(--text-secondary);">/forms/<?= View::e($f['slug']) ?></div>
                    </td>
                    <td>
                        <span class="fx-status-badge" style="background:<?= $sColor ?>18;color:<?= $sColor ?>;"><?= ucfirst($f['status']) ?></span>
                    </td>
                    <td style="color:var(--text-secondary);font-size:.875rem;"><?= number_format((int)($f['submissions_count'] ?? 0)) ?></td>
                    <td style="color:var(--text-secondary);font-size:.8rem;white-space:nowrap;"><?= date('M j, Y', strtotime($f['updated_at'] ?? $f['created_at'])) ?></td>
                    <td>
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <a href="/projects/formx/<?= $f['id'] ?>/edit" class="fx-action-btn edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="/projects/formx/<?= $f['id'] ?>/submissions" class="fx-action-btn subs"><i class="fas fa-inbox"></i></a>
                            <?php if ($f['status'] === 'active'): ?>
                            <a href="/forms/<?= View::e($f['slug']) ?>" target="_blank" class="fx-action-btn" style="background:rgba(153,69,255,.08);border-color:rgba(153,69,255,.2);color:var(--purple);">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php endif; ?>
                            <form method="POST" action="/projects/formx/<?= $f['id'] ?>/delete" style="display:inline;"
                                  onsubmit="return confirm('Delete this form and all its submissions?');">
                                <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                                <button type="submit" class="fx-action-btn del"><i class="fas fa-trash"></i></button>
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
                <a href="?page=<?= $pagination['current'] - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">← Prev</a>
                <?php endif; ?>
                <span class="current">Page <?= $pagination['current'] ?> of <?= $pagination['total'] ?></span>
                <?php if ($pagination['current'] < $pagination['total']): ?>
                <a href="?page=<?= $pagination['current'] + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Next →</a>
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
    function open(){s.classList.add('open');o.classList.add('active');t.innerHTML='<i class="fas fa-times"></i>';}
    function close(){s.classList.remove('open');o.classList.remove('active');t.innerHTML='<i class="fas fa-bars"></i>';}
    t.addEventListener('click',()=>s.classList.contains('open')?close():open());
    o.addEventListener('click',close);
})();
</script>

<?php View::endSection(); ?>
