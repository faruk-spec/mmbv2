<?php
/**
 * @var array  $card
 * @var array  $tplConfig
 * @var array  $field_labels
 * @var bool   $printMode
 */
$printMode = $printMode ?? false;
$csrfToken = \Core\Security::generateCsrfToken();
$cd        = $card['card_data']  ?? [];
$design    = $card['design']     ?? [];
$aiSugg    = $card['ai_suggestions'] ?? [];

$bg   = $design['bg_color']      ?? $tplConfig['bg'];
$pri  = $design['primary_color'] ?? $tplConfig['color'];
$acc  = $design['accent_color']  ?? $tplConfig['accent'];
$txt  = $design['text_color']    ?? $tplConfig['text'];
$font = $design['font_family']   ?? 'Poppins';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=<?= urlencode($font) ?>:wght@400;600;700&display=swap');

.view-card-wrap {
    max-width:900px;
    margin:0 auto;
}
/* ── Printed ID card ── */
.id-card-display {
    width:100%;
    max-width:460px;
    margin:0 auto;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 24px 70px rgba(0,0,0,0.45);
    font-family:'<?= htmlspecialchars($font) ?>','Poppins',sans-serif;
    aspect-ratio:85.6/54;
    position:relative;
    background:<?= htmlspecialchars($bg) ?>;
    color:<?= htmlspecialchars($txt) ?>;
}
.id-card-inner {
    width:100%; height:100%;
    padding:4% 5%;
    display:flex;
    gap:5%;
    align-items:center;
    position:relative;
}
.id-card-stripe-top {
    position:absolute; top:0; left:0; right:0; height:8%;
    background:<?= htmlspecialchars($pri) ?>;
}
.id-card-stripe-bot {
    position:absolute; bottom:0; left:0; right:0; height:12%;
    background:<?= htmlspecialchars($pri) ?>;
    opacity:0.3;
}
.id-card-photo {
    width:20%; aspect-ratio:1; border-radius:50%;
    border:3px solid <?= htmlspecialchars($acc) ?>;
    overflow:hidden; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:<?= htmlspecialchars($pri) ?>22;
    margin-top:8%;
}
.id-card-photo img { width:100%; height:100%; object-fit:cover; }
.id-card-photo i { font-size:2rem; color:<?= htmlspecialchars($acc) ?>; }
.id-card-body { flex:1; min-width:0; margin-top:8%; }
.id-card-name  { font-size:clamp(0.8rem,2vw,1.1rem); font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.id-card-role  { font-size:clamp(0.6rem,1.4vw,0.82rem); color:<?= htmlspecialchars($acc) ?>; margin-top:1%; }
.id-card-fields { margin-top:4%; display:flex; flex-direction:column; gap:2%; }
.id-card-field { font-size:clamp(0.52rem,1.2vw,0.72rem); display:flex; align-items:center; gap:4%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; opacity:0.85; }
.id-card-num  {
    position:absolute; bottom:4%; right:4%;
    font-size:clamp(0.45rem,1vw,0.6rem); opacity:0.6; font-family:monospace;
}

/* Print */
@media print {
    .back-link, .view-actions, .ai-panel-view, .navbar { display:none !important; }
    body { background:white; }
    .cx-sidebar, .sidebar-toggle { display:none !important; }
    .cx-main { margin-left:0 !important; padding:0 !important; }
    .id-card-display { box-shadow:none; }
}
</style>

<div class="view-card-wrap">
    <?php if (!$printMode): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <a href="/projects/idcard/history" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Cards</a>
        <div class="view-actions" style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print / Save PDF</button>
            <a href="/projects/idcard/generate?template=<?= htmlspecialchars($card['template_key']) ?>" class="btn btn-secondary">
                <i class="fas fa-plus"></i> New Card
            </a>
            <button class="btn btn-danger btn-sm" onclick="document.getElementById('delModal').style.display='flex'">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">
        <!-- Card visual -->
        <div style="text-align:center;">
            <div class="id-card-display" id="idCardEl">
                <div class="id-card-stripe-top"></div>
                <div class="id-card-inner">
                    <!-- Photo -->
                    <div class="id-card-photo">
                        <?php if (!empty($card['photo_path']) && file_exists(BASE_PATH . '/' . $card['photo_path'])): ?>
                            <img src="/<?= htmlspecialchars($card['photo_path']) ?>" alt="Photo">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Details -->
                    <div class="id-card-body">
                        <div class="id-card-name"><?= htmlspecialchars($cd['name'] ?? 'Name') ?></div>
                        <?php
                        $roleKeys = ['designation','title','course','event_name'];
                        $roleVal  = '';
                        foreach ($roleKeys as $rk) {
                            if (!empty($cd[$rk])) { $roleVal = $cd[$rk]; break; }
                        }
                        ?>
                        <?php if ($roleVal): ?>
                        <div class="id-card-role"><?= htmlspecialchars($roleVal) ?></div>
                        <?php endif; ?>

                        <div class="id-card-fields">
                            <?php
                            $icons = [
                                'department'=>'building','employee_id'=>'hashtag','roll_number'=>'hashtag',
                                'phone'=>'phone','email'=>'envelope','blood_group'=>'tint',
                                'badge_id'=>'hashtag','host_name'=>'user','purpose'=>'clipboard',
                                'visit_date'=>'calendar','license_no'=>'certificate',
                                'organization'=>'building','id_number'=>'hashtag','year'=>'graduation-cap',
                            ];
                            $skipKeys = array_merge(['name'], $roleKeys);
                            $shown = 0;
                            foreach ($cd as $fKey => $fVal):
                                if (in_array($fKey, $skipKeys) || !$fVal || $shown >= 3) continue;
                                $shown++;
                                $ic = $icons[$fKey] ?? 'info-circle';
                            ?>
                            <div class="id-card-field">
                                <i class="fas fa-<?= $ic ?>" style="font-size:0.55rem;opacity:0.6;flex-shrink:0;"></i>
                                <span><?= htmlspecialchars($fVal) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($card['logo_path']) && file_exists(BASE_PATH . '/' . $card['logo_path'])): ?>
                    <img src="/<?= htmlspecialchars($card['logo_path']) ?>"
                         style="position:absolute;top:10%;right:4%;width:10%;aspect-ratio:1;object-fit:contain;"
                         alt="Logo">
                    <?php endif; ?>

                    <div class="id-card-num"><?= htmlspecialchars($card['card_number']) ?></div>
                </div>
                <div class="id-card-stripe-bot"></div>
            </div>

            <!-- Download hint -->
            <p style="font-size:0.75rem;color:var(--text-secondary);margin-top:12px;text-align:center;">
                <i class="fas fa-info-circle"></i> Click "Print / Save PDF" to download as PDF or image
            </p>
        </div>

        <!-- Details panel -->
        <div>
            <div class="card" style="margin-bottom:16px;padding:16px;">
                <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-info-circle"></i> Card Details
                </h3>
                <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                    <tr>
                        <td style="padding:6px 0;color:var(--text-secondary);width:40%;">Template</td>
                        <td style="padding:6px 0;font-weight:600;"><?= htmlspecialchars($tplConfig['name'] ?? $card['template_key']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:var(--text-secondary);">Card Number</td>
                        <td style="padding:6px 0;font-family:monospace;"><?= htmlspecialchars($card['card_number']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0;color:var(--text-secondary);">Created</td>
                        <td style="padding:6px 0;"><?= date('d M Y, H:i', strtotime($card['created_at'])) ?></td>
                    </tr>
                    <?php foreach ($cd as $fKey => $fVal): if (!$fVal) continue; ?>
                    <tr>
                        <td style="padding:6px 0;color:var(--text-secondary);"><?= htmlspecialchars($field_labels[$fKey] ?? ucfirst(str_replace('_',' ',$fKey))) ?></td>
                        <td style="padding:6px 0;font-weight:500;"><?= htmlspecialchars($fVal) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <!-- AI Suggestions -->
            <?php if (!empty($aiSugg)): ?>
            <div class="ai-panel-view card" style="padding:16px;background:linear-gradient(135deg,rgba(99,102,241,0.07),rgba(0,240,255,0.03));border:1px solid rgba(99,102,241,0.2);">
                <h4 style="font-size:0.85rem;font-weight:600;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-robot" style="color:var(--indigo);"></i> AI Design Notes
                </h4>
                <?php if (!empty($aiSugg['template_tip'])): ?>
                <div style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:8px;padding:8px;background:var(--bg-secondary);border-radius:8px;">
                    💡 <?= htmlspecialchars($aiSugg['template_tip']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($aiSugg['missing_fields'])): ?>
                <div style="font-size:0.78rem;color:var(--amber);margin-bottom:8px;padding:8px;background:rgba(245,158,11,0.08);border-radius:8px;border:1px solid rgba(245,158,11,0.2);">
                    📋 <?= htmlspecialchars($aiSugg['missing_fields']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($aiSugg['ai_text'])): ?>
                <div style="font-size:0.78rem;color:var(--cyan);margin-bottom:8px;padding:8px;background:rgba(0,240,255,0.05);border-radius:8px;">
                    🤖 <?= htmlspecialchars($aiSugg['ai_text']) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="delModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:28px;max-width:360px;width:90%;text-align:center;">
        <i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#ff4757;margin-bottom:12px;"></i>
        <h3 style="margin-bottom:8px;">Delete This Card?</h3>
        <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:20px;">This action cannot be undone.</p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button class="btn btn-secondary" onclick="document.getElementById('delModal').style.display='none'">Cancel</button>
            <form method="POST" action="/projects/idcard/delete" style="margin:0;">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id"     value="<?= (int)$card['id'] ?>">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
</div>
