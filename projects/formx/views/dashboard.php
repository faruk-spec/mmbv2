<?php use Core\View; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>

<div style="max-width:1100px;margin:0 auto;padding:32px 20px;">

    <!-- Page Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;flex-wrap:wrap;gap:14px;">
        <div>
            <h1 style="font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:4px;">
                <i class="fas fa-wpforms" style="-webkit-text-fill-color:var(--cyan);margin-right:10px;"></i>FormX
            </h1>
            <p style="color:var(--text-secondary);font-size:.9rem;">Drag-and-drop form builder — create forms and collect responses.</p>
        </div>
        <a href="/admin/formx/create" style="padding:10px 20px;background:linear-gradient(135deg,var(--cyan),var(--purple));border-radius:8px;color:#fff;text-decoration:none;font-weight:600;font-size:.875rem;display:inline-flex;align-items:center;gap:8px;">
            <i class="fas fa-plus"></i> New Form
        </a>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:32px;">
        <?php
        $stats = [
            ['label'=>'Total Forms',       'value'=>$totalForms,       'icon'=>'fa-wpforms',   'color'=>'var(--cyan)'],
            ['label'=>'Active Forms',       'value'=>$activeForms,      'icon'=>'fa-toggle-on', 'color'=>'var(--green)'],
            ['label'=>'Total Submissions',  'value'=>$totalSubmissions, 'icon'=>'fa-inbox',     'color'=>'var(--purple)'],
        ];
        foreach ($stats as $s): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <div style="width:38px;height:38px;border-radius:10px;background:<?= $s['color'] ?>22;display:flex;align-items:center;justify-content:center;color:<?= $s['color'] ?>;">
                    <i class="fas <?= $s['icon'] ?>"></i>
                </div>
                <span style="font-size:.8rem;color:var(--text-secondary);"><?= $s['label'] ?></span>
            </div>
            <div style="font-size:1.8rem;font-weight:700;color:var(--text-primary);"><?= number_format($s['value']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Forms -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h2 style="font-size:1rem;font-weight:700;">Recent Forms</h2>
            <a href="/admin/formx" style="font-size:.8rem;color:var(--cyan);text-decoration:none;">View all <i class="fas fa-arrow-right"></i></a>
        </div>

        <?php if (empty($recentForms)): ?>
        <div style="text-align:center;padding:40px 20px;color:var(--text-secondary);">
            <i class="fas fa-wpforms" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:12px;"></i>
            <p>No forms yet.</p>
            <a href="/admin/formx/create" style="margin-top:10px;display:inline-block;padding:8px 18px;background:var(--cyan);color:#000;border-radius:8px;text-decoration:none;font-size:.875rem;font-weight:600;">Create your first form</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($recentForms as $f):
                $statusColors = ['active'=>'var(--green)','inactive'=>'var(--red)','draft'=>'var(--orange)'];
                $sColor = $statusColors[$f['status']] ?? 'var(--text-secondary)';
            ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--bg-secondary);border-radius:8px;gap:12px;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:.9rem;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($f['title']) ?></div>
                    <div style="font-size:.78rem;color:var(--text-secondary);">/forms/<?= View::e($f['slug']) ?></div>
                </div>
                <span style="font-size:.75rem;padding:3px 10px;border-radius:20px;background:<?= $sColor ?>22;color:<?= $sColor ?>;white-space:nowrap;"><?= ucfirst($f['status']) ?></span>
                <div style="font-size:.8rem;color:var(--text-secondary);white-space:nowrap;"><?= (int)$f['submissions_count'] ?> submissions</div>
                <div style="display:flex;gap:8px;">
                    <a href="/admin/formx/<?= $f['id'] ?>/edit" style="padding:5px 10px;background:var(--bg-primary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);text-decoration:none;font-size:.78rem;">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="/admin/formx/<?= $f['id'] ?>/submissions" style="padding:5px 10px;background:var(--bg-primary);border:1px solid var(--border-color);border-radius:6px;color:var(--cyan);text-decoration:none;font-size:.78rem;">
                        <i class="fas fa-inbox"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php View::endSection(); ?>
