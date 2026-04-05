<?php /** @var int $total @var array $recent @var array $templates @var array $user */ ?>

<style>
/* Dashboard page enhancements */
.dash-hero {
    background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(0,240,255,0.06) 100%);
    border: 1px solid rgba(99,102,241,0.25);
    border-radius: 16px;
    padding: 32px 28px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}
.dash-hero-icon {
    width: 72px; height: 72px; flex-shrink: 0;
    background: linear-gradient(135deg, #6366f1, #00f0ff);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
}
.dash-hero-title {
    font-size: clamp(1.5rem, 4vw, 2.1rem);
    font-weight: 800;
    background: linear-gradient(135deg, #6366f1, #00f0ff);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    margin-bottom: 4px;
}
.dash-hero-sub { color: var(--text-secondary); font-size: 0.95rem; }
.dash-hero-cta { margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap; }
@media (max-width: 560px) {
    .dash-hero { padding: 20px 16px; gap: 16px; }
    .dash-hero-cta { margin-left: 0; width: 100%; }
    .dash-hero-cta .btn { flex: 1; justify-content: center; }
}

/* Quick stats strip */
.dash-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
@media (max-width: 480px) { .dash-stats { grid-template-columns: 1fr; } }
.dash-stat {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 18px 16px;
    text-align: center;
    transition: border-color 0.2s;
}
.dash-stat:hover { border-color: rgba(99,102,241,0.4); }
.dash-stat-val {
    font-size: 2rem; font-weight: 800;
    background: linear-gradient(135deg, var(--indigo), var(--cyan));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.dash-stat-label { font-size: 0.78rem; color: var(--text-secondary); margin-top: 4px; }

/* Template gallery */
.tpl-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
}
.tpl-card {
    display: block; padding: 14px 12px; border-radius: 10px; text-decoration: none;
    border: 1.5px solid var(--border-color); background: var(--bg-secondary);
    transition: all 0.2s; text-align: center;
}
.tpl-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
.tpl-icon {
    width: 40px; height: 40px; border-radius: 10px; margin: 0 auto 10px;
    display: flex; align-items: center; justify-content: center;
}
</style>

<!-- Hero -->
<div class="dash-hero">
    <div class="dash-hero-icon">
        <i class="fas fa-id-card" style="font-size:2rem;color:#fff;"></i>
    </div>
    <div style="flex:1;min-width:180px;">
        <div class="dash-hero-title">CardX Dashboard</div>
        <div class="dash-hero-sub">
            AI-powered professional ID card generator &mdash;
            <span style="color:var(--indigo);font-weight:600;"><?= number_format($total) ?> card<?= $total !== 1 ? 's' : '' ?> created</span>
        </div>
    </div>
    <div class="dash-hero-cta">
        <a href="/projects/idcard/generate" class="btn btn-primary" style="font-size:0.95rem;padding:10px 20px;">
            <i class="fas fa-plus"></i> Create ID Card
        </a>
        <a href="/projects/idcard/history" class="btn btn-secondary">
            <i class="fas fa-layer-group"></i> My Cards
        </a>
        <a href="/projects/idcard/bulk" class="btn btn-secondary">
            <i class="fas fa-table"></i> Bulk Generate
        </a>
    </div>
</div>

<!-- Quick-start category cards -->
<div style="margin-bottom:24px;">
    <p style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:10px;">
        <i class="fas fa-bolt" style="color:var(--indigo);"></i> Quick Start — Pick a Category
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px;">
        <?php
        $quickCats = [
            'corporate'  => ['icon'=>'fa-building',     'label'=>'Corporate',    'color'=>'#1e40af'],
            'student'    => ['icon'=>'fa-graduation-cap','label'=>'Student',      'color'=>'#065f46'],
            'event'      => ['icon'=>'fa-calendar-star', 'label'=>'Event',        'color'=>'#b45309'],
            'visitor'    => ['icon'=>'fa-user-check',    'label'=>'Visitor',      'color'=>'#0369a1'],
            'medical'    => ['icon'=>'fa-stethoscope',   'label'=>'Medical',      'color'=>'#dc2626'],
            'tech'       => ['icon'=>'fa-laptop-code',   'label'=>'Tech',         'color'=>'#7c3aed'],
        ];
        foreach ($quickCats as $catKey => $cat): ?>
        <a href="/projects/idcard/generate?template=<?= $catKey ?>"
           style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:12px 8px;
                  background:var(--bg-card);border:1.5px solid var(--border-color);border-radius:10px;
                  text-decoration:none;transition:all 0.2s;text-align:center;"
           onmouseover="this.style.borderColor='<?= $cat['color'] ?>';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border-color)';this.style.transform='none'">
            <div style="width:36px;height:36px;background:<?= $cat['color'] ?>;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="fas <?= $cat['icon'] ?>" style="color:#fff;font-size:0.95rem;"></i>
            </div>
            <span style="font-size:0.72rem;font-weight:600;color:var(--text-primary);"><?= $cat['label'] ?></span>
        </a>
        <?php endforeach; ?>
        <a href="/projects/idcard/generate"
           style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:12px 8px;
                  background:var(--bg-secondary);border:1.5px dashed var(--border-color);border-radius:10px;
                  text-decoration:none;transition:all 0.2s;text-align:center;"
           onmouseover="this.style.borderColor='var(--indigo)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border-color)';this.style.transform='none'">
            <div style="width:36px;height:36px;background:var(--bg-secondary);border:2px dashed var(--indigo);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-ellipsis-h" style="color:var(--indigo);font-size:0.95rem;"></i>
            </div>
            <span style="font-size:0.72rem;font-weight:600;color:var(--indigo);">More…</span>
        </a>
    </div>
</div>

<!-- Stats strip -->
<div class="dash-stats">
    <div class="dash-stat">
        <div class="dash-stat-val"><?= number_format($total) ?></div>
        <div class="dash-stat-label"><i class="fas fa-id-card"></i> Cards Created</div>
    </div>
    <div class="dash-stat">
        <div class="dash-stat-val"><?= count($templates) ?></div>
        <div class="dash-stat-label"><i class="fas fa-palette"></i> Templates</div>
    </div>
    <div class="dash-stat" style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(0,240,255,0.04));border-color:rgba(99,102,241,0.2);">
        <div class="dash-stat-val" style="font-size:1.5rem;">AI ✨</div>
        <div class="dash-stat-label"><i class="fas fa-robot"></i> Powered Design</div>
    </div>
</div>

<!-- Template Gallery -->
<div class="card" style="margin-bottom:24px;padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <h3 style="font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px;margin:0;">
            <i class="fas fa-palette" style="color:var(--indigo);"></i> Templates
        </h3>
        <a href="/projects/idcard/generate" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Generate</a>
    </div>
    <div class="tpl-grid">
        <?php foreach ($templates as $key => $tpl): ?>
        <a href="/projects/idcard/generate?template=<?= htmlspecialchars($key) ?>" class="tpl-card"
           onmouseover="this.style.borderColor='<?= htmlspecialchars($tpl['color']) ?>'"
           onmouseout="this.style.borderColor='var(--border-color)'">
            <div class="tpl-icon" style="background:<?= htmlspecialchars($tpl['color']) ?>;">
                <i class="fas fa-id-card" style="color:white;font-size:1rem;"></i>
            </div>
            <div style="font-weight:600;font-size:0.82rem;color:var(--text-primary);margin-bottom:3px;
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($tpl['name']) ?>
            </div>
            <div style="font-size:0.68rem;color:var(--text-secondary);display:flex;align-items:center;justify-content:center;gap:4px;">
                <span style="width:6px;height:6px;border-radius:50%;background:<?= htmlspecialchars($tpl['color']) ?>;display:inline-block;flex-shrink:0;"></span>
                <?= $tpl['orientation'] === 'portrait' ? 'Portrait' : 'Landscape' ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Cards -->
<?php if (!empty($recent)): ?>
<div class="card" style="padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <h3 style="font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px;margin:0;">
            <i class="fas fa-clock" style="color:var(--indigo);"></i> Recent Cards
        </h3>
        <a href="/projects/idcard/history" class="btn btn-secondary btn-sm"><i class="fas fa-layer-group"></i> View All</a>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:420px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:8px 10px;font-size:0.72rem;color:var(--text-secondary);font-weight:600;">Card #</th>
                    <th style="text-align:left;padding:8px 10px;font-size:0.72rem;color:var(--text-secondary);font-weight:600;">Name</th>
                    <th style="text-align:left;padding:8px 10px;font-size:0.72rem;color:var(--text-secondary);font-weight:600;">Template</th>
                    <th style="text-align:left;padding:8px 10px;font-size:0.72rem;color:var(--text-secondary);font-weight:600;">Date</th>
                    <th style="text-align:center;padding:8px 10px;font-size:0.72rem;color:var(--text-secondary);font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $card):
                    $tplKey = $card['template_key'];
                    $tplDef = $templates[$tplKey] ?? null;
                ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:9px 10px;font-size:0.78rem;font-family:monospace;color:var(--text-secondary);">
                        <?= htmlspecialchars($card['card_number']) ?>
                    </td>
                    <td style="padding:9px 10px;font-size:0.85rem;font-weight:500;">
                        <?= htmlspecialchars($card['card_data']['name'] ?? '—') ?>
                    </td>
                    <td style="padding:9px 10px;">
                        <?php if ($tplDef): ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:0.78rem;">
                            <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($tplDef['color']) ?>;flex-shrink:0;"></span>
                            <?= htmlspecialchars($tplDef['name']) ?>
                        </span>
                        <?php else: ?>
                        <span style="font-size:0.78rem;color:var(--text-secondary);"><?= htmlspecialchars($tplKey) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:9px 10px;font-size:0.78rem;color:var(--text-secondary);">
                        <?= date('d M Y', strtotime($card['created_at'])) ?>
                    </td>
                    <td style="padding:9px 10px;text-align:center;">
                        <div style="display:inline-flex;gap:5px;">
                            <a href="/projects/idcard/view/<?= (int)$card['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="/projects/idcard/edit/<?= (int)$card['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:48px 24px;">
    <div style="font-size:3.5rem;margin-bottom:14px;opacity:0.35;"><i class="fas fa-id-card"></i></div>
    <h3 style="font-size:1.1rem;margin-bottom:8px;">No ID Cards Yet</h3>
    <p style="color:var(--text-secondary);margin-bottom:24px;font-size:0.9rem;max-width:380px;margin-left:auto;margin-right:auto;">
        Create your first professional ID card using our AI-powered generator. Choose from <?= count($templates) ?> templates.
    </p>
    <a href="/projects/idcard/generate" class="btn btn-primary" style="font-size:0.95rem;">
        <i class="fas fa-magic"></i> Create First Card
    </a>
</div>
<?php endif; ?>
