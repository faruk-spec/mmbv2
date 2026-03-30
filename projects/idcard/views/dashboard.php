<?php /** @var int $total @var array $recent @var array $templates @var array $user */ ?>

<div class="page-header" style="margin-bottom:30px;text-align:center;">
    <h1 style="font-size:2.2rem;font-weight:700;background:linear-gradient(135deg,#6366f1,#00f0ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        CardX Dashboard
    </h1>
    <p style="color:var(--text-secondary);margin-top:8px;font-size:1.05rem;">
        AI-powered professional ID card generator
    </p>
</div>

<!-- Quick Actions -->
<div style="display:flex;gap:15px;margin-bottom:30px;flex-wrap:wrap;justify-content:center;">
    <a href="/projects/idcard/generate"
       style="flex:1;min-width:200px;max-width:250px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;padding:20px;border-radius:12px;text-decoration:none;text-align:center;box-shadow:0 4px 15px rgba(99,102,241,0.3);transition:transform 0.2s;"
       onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
        <i class="fas fa-id-card" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
        <strong style="font-size:1.1rem;">Create ID Card</strong>
        <p style="margin:5px 0 0;font-size:0.85rem;opacity:0.9;">AI-powered generator</p>
    </a>
    <a href="/projects/idcard/history"
       style="flex:1;min-width:200px;max-width:250px;background:linear-gradient(135deg,#10b981,#059669);color:white;padding:20px;border-radius:12px;text-decoration:none;text-align:center;box-shadow:0 4px 15px rgba(16,185,129,0.3);transition:transform 0.2s;"
       onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
        <i class="fas fa-layer-group" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
        <strong style="font-size:1.1rem;">My Cards</strong>
        <p style="margin:5px 0 0;font-size:0.85rem;opacity:0.9;"><?= number_format($total) ?> card<?= $total !== 1 ? 's' : '' ?> generated</p>
    </a>
</div>

<!-- Stats -->
<div class="grid grid-3" style="margin-bottom:30px;">
    <div class="card stat-card">
        <div class="stat-value"><?= number_format($total) ?></div>
        <div class="stat-label"><i class="fas fa-id-card"></i> Cards Created</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value"><?= count($templates) ?></div>
        <div class="stat-label"><i class="fas fa-palette"></i> Templates</div>
    </div>
    <div class="card stat-card">
        <div class="stat-value" style="font-size:1.4rem;">AI✨</div>
        <div class="stat-label"><i class="fas fa-magic"></i> Powered Design</div>
    </div>
</div>

<!-- AI Feature Banner -->
<div class="card" style="margin-bottom:30px;background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(0,240,255,0.05));border:1.5px solid rgba(99,102,241,0.3);">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#6366f1,#00f0ff);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-robot" style="font-size:1.6rem;color:white;"></i>
        </div>
        <div style="flex:1;min-width:200px;">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:4px;">
                AI Design Assistant <span style="background:linear-gradient(135deg,#6366f1,#00f0ff);color:white;font-size:0.65rem;padding:2px 10px;border-radius:20px;font-weight:600;vertical-align:middle;">POWERED</span>
            </h3>
            <p style="color:var(--text-secondary);font-size:0.85rem;">
                CardX analyses your content and provides real-time design tips, layout suggestions, and field recommendations — powered by AI.
            </p>
        </div>
        <a href="/projects/idcard/generate" class="btn btn-primary" style="flex-shrink:0;">
            <i class="fas fa-magic"></i> Generate Now
        </a>
    </div>
</div>

<!-- Template Gallery -->
<div class="card" style="margin-bottom:30px;">
    <h3 style="font-size:1.1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-palette" style="color:var(--indigo);"></i> Available Templates
    </h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
        <?php foreach ($templates as $key => $tpl): ?>
        <a href="/projects/idcard/generate?template=<?= htmlspecialchars($key) ?>"
           style="display:block;padding:16px;border-radius:10px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.08);background:var(--bg-secondary);transition:all 0.2s;text-align:center;"
           onmouseover="this.style.borderColor='<?= htmlspecialchars($tpl['color']) ?>';this.style.transform='translateY(-2px)';"
           onmouseout="this.style.borderColor='rgba(255,255,255,0.08)';this.style.transform='none';">
            <div style="width:40px;height:40px;background:<?= htmlspecialchars($tpl['color']) ?>;border-radius:10px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-id-card" style="color:white;font-size:1.1rem;"></i>
            </div>
            <div style="font-weight:600;font-size:0.85rem;color:var(--text-primary);margin-bottom:4px;"><?= htmlspecialchars($tpl['name']) ?></div>
            <div style="font-size:0.72rem;color:var(--text-secondary);"><?= htmlspecialchars($tpl['description']) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent Cards -->
<?php if (!empty($recent)): ?>
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h3 style="font-size:1.1rem;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-clock" style="color:var(--indigo);"></i> Recent Cards
        </h3>
        <a href="/projects/idcard/history" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <th style="text-align:left;padding:8px 12px;font-size:0.78rem;color:var(--text-secondary);font-weight:600;">Card #</th>
                    <th style="text-align:left;padding:8px 12px;font-size:0.78rem;color:var(--text-secondary);font-weight:600;">Name</th>
                    <th style="text-align:left;padding:8px 12px;font-size:0.78rem;color:var(--text-secondary);font-weight:600;">Template</th>
                    <th style="text-align:left;padding:8px 12px;font-size:0.78rem;color:var(--text-secondary);font-weight:600;">Date</th>
                    <th style="text-align:center;padding:8px 12px;font-size:0.78rem;color:var(--text-secondary);font-weight:600;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $card): ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:10px 12px;font-size:0.82rem;font-family:monospace;color:var(--text-secondary);"><?= htmlspecialchars($card['card_number']) ?></td>
                    <td style="padding:10px 12px;font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($card['card_data']['name'] ?? '—') ?></td>
                    <td style="padding:10px 12px;">
                        <?php $tplKey = $card['template_key']; $tplDef = $templates[$tplKey] ?? null; ?>
                        <?php if ($tplDef): ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:0.78rem;">
                            <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($tplDef['color']) ?>;"></span>
                            <?= htmlspecialchars($tplDef['name']) ?>
                        </span>
                        <?php else: ?>
                        <span style="font-size:0.78rem;color:var(--text-secondary);"><?= htmlspecialchars($tplKey) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 12px;font-size:0.8rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($card['created_at'])) ?></td>
                    <td style="padding:10px 12px;text-align:center;">
                        <a href="/projects/idcard/view/<?= (int)$card['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px;">
    <div style="font-size:3rem;margin-bottom:12px;opacity:0.4;"><i class="fas fa-id-card"></i></div>
    <h3 style="margin-bottom:8px;">No ID Cards Yet</h3>
    <p style="color:var(--text-secondary);margin-bottom:20px;font-size:0.9rem;">Create your first professional ID card using our AI-powered generator.</p>
    <a href="/projects/idcard/generate" class="btn btn-primary"><i class="fas fa-plus"></i> Create First Card</a>
</div>
<?php endif; ?>
