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

$bg          = htmlspecialchars($design['bg_color']      ?? $tplConfig['bg'],    ENT_QUOTES, 'UTF-8');
$pri         = htmlspecialchars($design['primary_color'] ?? $tplConfig['color'], ENT_QUOTES, 'UTF-8');
$acc         = htmlspecialchars($design['accent_color']  ?? $tplConfig['accent'],ENT_QUOTES, 'UTF-8');
$txt         = htmlspecialchars($design['text_color']    ?? $tplConfig['text'],  ENT_QUOTES, 'UTF-8');
$font        = htmlspecialchars($design['font_family']   ?? 'Poppins',           ENT_QUOTES, 'UTF-8');

$allowedStyles = ['classic','sidebar','wave','bold_header','diagonal'];
$rawStyle      = $design['design_style'] ?? 'classic';
$designStyle   = in_array($rawStyle, $allowedStyles, true) ? $rawStyle : 'classic';

// ── Helpers ──────────────────────────────────────────────────────────────────
$roleKeys  = ['designation','title','course','event_name'];
$nameVal   = htmlspecialchars($cd['name'] ?? 'Name', ENT_QUOTES, 'UTF-8');
$roleVal   = '';
foreach ($roleKeys as $rk) {
    if (!empty($cd[$rk])) { $roleVal = htmlspecialchars($cd[$rk], ENT_QUOTES, 'UTF-8'); break; }
}
$icons = [
    'department'=>'building','employee_id'=>'hashtag','roll_number'=>'hashtag',
    'phone'=>'phone','email'=>'envelope','blood_group'=>'tint','badge_id'=>'hashtag',
    'host_name'=>'user','purpose'=>'clipboard','visit_date'=>'calendar',
    'license_no'=>'certificate','organization'=>'building','id_number'=>'hashtag','year'=>'graduation-cap',
];
$skipKeys  = array_merge(['name'], $roleKeys);
$shownFlds = [];
foreach ($cd as $fKey => $fVal) {
    if (in_array($fKey, $skipKeys) || !$fVal || count($shownFlds) >= 3) continue;
    $shownFlds[] = ['key' => $fKey, 'val' => htmlspecialchars($fVal, ENT_QUOTES, 'UTF-8'), 'icon' => $icons[$fKey] ?? 'info-circle'];
}

$photoPath = (!empty($card['photo_path']) && file_exists(BASE_PATH . '/' . $card['photo_path']))
    ? '/' . htmlspecialchars($card['photo_path'], ENT_QUOTES, 'UTF-8') : '';
$logoPath  = (!empty($card['logo_path'])  && file_exists(BASE_PATH . '/' . $card['logo_path']))
    ? '/' . htmlspecialchars($card['logo_path'],  ENT_QUOTES, 'UTF-8') : '';

// ── Field-list snippet ────────────────────────────────────────────────────────
function icardFieldList(array $flds, string $color): string {
    $out = '';
    foreach ($flds as $f) {
        $out .= '<div style="display:flex;align-items:center;gap:4%;font-size:clamp(0.5rem,1.1vw,0.68rem);opacity:0.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:' . $color . ';">';
        $out .= '<i class="fas fa-' . $f['icon'] . '" style="font-size:0.5rem;opacity:0.6;flex-shrink:0;"></i>';
        $out .= '<span>' . $f['val'] . '</span></div>';
    }
    return $out;
}

// ── Photo element ─────────────────────────────────────────────────────────────
function icardPhoto(string $photoPath): string {
    if ($photoPath) {
        return '<img src="' . $photoPath . '" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" alt="Photo">';
    }
    return '<i class="fas fa-user" style="font-size:2rem;opacity:0.6;"></i>';
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=<?= urlencode($font) ?>:wght@400;600;700&display=swap');

.view-card-wrap { max-width:920px; margin:0 auto; }

/* ── Shared card shell ── */
.id-card-display {
    width:100%; max-width:480px; margin:0 auto;
    border-radius:18px; overflow:hidden;
    box-shadow:0 24px 70px rgba(0,0,0,0.45);
    font-family:'<?= $font ?>','Poppins',sans-serif;
    aspect-ratio:85.6/54;
    position:relative;
}

/* Print */
@media print {
    .back-link,.view-actions,.ai-panel-view,.navbar { display:none !important; }
    body { background:white; }
    .cx-sidebar,.sidebar-toggle { display:none !important; }
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
            <div class="id-card-display">
            <?php

            // ── STYLE: Classic ───────────────────────────────────────────────
            if ($designStyle === 'classic'): ?>
            <div style="width:100%;height:100%;background:<?= $bg ?>;font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:8%;background:<?= $pri ?>;"></div>
                <div style="position:absolute;bottom:0;left:0;right:0;height:14%;background:<?= $pri ?>;opacity:0.2;"></div>
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:12% 5% 5%;color:<?= $txt ?>;">
                    <div style="flex-shrink:0;width:20%;aspect-ratio:1;border-radius:50%;border:2.5px solid <?= $acc ?>;background:<?= $pri ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                        <?= icardPhoto($photoPath) ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:clamp(0.75rem,2vw,1.1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.55rem,1.3vw,0.78rem);color:<?= $acc ?>;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $roleVal ?></div><?php endif; ?>
                        <div style="margin-top:4%;display:flex;flex-direction:column;gap:2%;"><?= icardFieldList($shownFlds, $txt) ?></div>
                    </div>
                </div>
                <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:10%;right:4%;width:10%;aspect-ratio:1;object-fit:contain;">
                <?php endif; ?>
                <div style="position:absolute;bottom:2%;right:4%;font-size:clamp(0.4rem,0.9vw,0.55rem);font-family:monospace;opacity:0.5;color:<?= $txt ?>;"><?= htmlspecialchars($card['card_number']) ?></div>
            </div>

            <?php // ── STYLE: Sidebar ──────────────────────────────────────────
            elseif ($designStyle === 'sidebar'): ?>
            <div style="width:100%;height:100%;display:flex;font-family:'<?= $font ?>',sans-serif;overflow:hidden;position:relative;">
                <div style="width:28%;background:<?= $pri ?>;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6%;padding:5% 0;flex-shrink:0;position:relative;">
                    <div style="position:absolute;top:-15%;left:-20%;width:100%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.06);"></div>
                    <div style="position:absolute;bottom:-20%;right:-30%;width:80%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.04);"></div>
                    <div style="width:54%;aspect-ratio:1;border-radius:50%;border:2px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;">
                        <?= icardPhoto($photoPath) ?>
                    </div>
                    <div style="writing-mode:vertical-rl;transform:rotate(180deg);font-size:clamp(0.38rem,0.85vw,0.55rem);color:rgba(255,255,255,0.45);letter-spacing:0.08em;text-transform:uppercase;position:relative;z-index:1;"><?= htmlspecialchars($tplConfig['name'] ?? 'ID Card') ?></div>
                </div>
                <div style="flex:1;background:<?= $bg ?>;padding:5% 6%;display:flex;flex-direction:column;justify-content:center;color:<?= $txt ?>;min-width:0;">
                    <div style="width:28%;height:2px;background:<?= $acc ?>;border-radius:2px;margin-bottom:6%;"></div>
                    <div style="font-size:clamp(0.72rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                    <?php if ($roleVal): ?><div style="font-size:clamp(0.5rem,1.2vw,0.72rem);color:<?= $acc ?>;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $roleVal ?></div><?php endif; ?>
                    <div style="margin-top:4%;display:flex;flex-direction:column;gap:3%;"><?= icardFieldList($shownFlds, $txt) ?></div>
                    <div style="margin-top:auto;font-size:clamp(0.38rem,0.75vw,0.5rem);font-family:monospace;opacity:0.4;color:<?= $txt ?>;"><?= htmlspecialchars($card['card_number']) ?></div>
                </div>
            </div>

            <?php // ── STYLE: Wave Gradient ─────────────────────────────────────
            elseif ($designStyle === 'wave'): ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?> 100%);font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;">
                <svg style="position:absolute;bottom:0;left:0;width:100%;height:45%;" viewBox="0 0 400 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,60 Q100,20 200,55 Q300,90 400,40 L400,100 L0,100 Z" fill="rgba(255,255,255,0.12)"/>
                    <path d="M0,78 Q130,50 260,72 Q340,88 400,62 L400,100 L0,100 Z" fill="rgba(255,255,255,0.07)"/>
                </svg>
                <div style="position:absolute;top:-8%;right:-8%;width:34%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.08);"></div>
                <div style="position:absolute;top:5%;right:8%;width:14%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.06);"></div>
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:5% 6%;color:rgba(255,255,255,0.95);">
                    <div style="flex-shrink:0;width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.6);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;">
                        <?= icardPhoto($photoPath) ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:clamp(0.72rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.5rem,1.2vw,0.72rem);opacity:0.82;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $roleVal ?></div><?php endif; ?>
                        <div style="margin-top:5%;display:flex;flex-direction:column;gap:3%;"><?= icardFieldList($shownFlds, 'rgba(255,255,255,0.92)') ?></div>
                    </div>
                </div>
                <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:8%;right:4%;width:10%;aspect-ratio:1;object-fit:contain;filter:brightness(0) invert(1);opacity:0.7;">
                <?php endif; ?>
                <div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.38rem,0.75vw,0.52rem);font-family:monospace;opacity:0.45;color:white;"><?= htmlspecialchars($card['card_number']) ?></div>
            </div>

            <?php // ── STYLE: Bold Header ────────────────────────────────────────
            elseif ($designStyle === 'bold_header'): ?>
            <div style="width:100%;height:100%;background:<?= $bg ?>;font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;color:<?= $txt ?>;">
                <div style="position:absolute;top:0;left:0;right:0;height:40%;background:<?= $pri ?>;overflow:hidden;">
                    <svg style="position:absolute;right:0;top:0;height:130%;opacity:0.14;" viewBox="0 0 120 80" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="10" r="30" fill="white"/>
                        <circle cx="85"  cy="45" r="22" fill="white"/>
                        <circle cx="110" cy="62" r="18" fill="white"/>
                        <polygon points="60,0 90,0 75,20" fill="white" opacity="0.5"/>
                    </svg>
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;justify-content:center;padding:0 5% 0 35%;">
                        <div style="font-size:clamp(0.65rem,1.7vw,0.95rem);font-weight:700;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.47rem,1.1vw,0.68rem);color:rgba(255,255,255,0.75);margin-top:4%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $roleVal ?></div><?php endif; ?>
                    </div>
                </div>
                <div style="position:absolute;top:22%;left:4%;width:22%;aspect-ratio:1;border-radius:50%;border:3px solid <?= $bg ?>;background:<?= $acc ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:2;box-shadow:0 2px 10px rgba(0,0,0,0.18);">
                    <?= icardPhoto($photoPath) ?>
                </div>
                <div style="position:absolute;top:44%;left:0;right:0;bottom:0;padding:2% 5% 4% 5%;display:flex;flex-direction:column;gap:3%;">
                    <div style="width:22%;height:2px;background:<?= $acc ?>;border-radius:2px;"></div>
                    <div style="display:flex;flex-direction:column;gap:3%;margin-top:1%;"><?= icardFieldList($shownFlds, $txt) ?></div>
                </div>
                <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:5%;right:4%;width:9%;aspect-ratio:1;object-fit:contain;z-index:1;opacity:0.85;">
                <?php endif; ?>
                <div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.36rem,0.7vw,0.5rem);font-family:monospace;opacity:0.35;color:<?= $txt ?>;"><?= htmlspecialchars($card['card_number']) ?></div>
            </div>

            <?php // ── STYLE: Diagonal Split ─────────────────────────────────────
            elseif ($designStyle === 'diagonal'): ?>
            <div style="width:100%;height:100%;background:<?= $bg ?>;font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;">
                <svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 256 162" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="0,0 148,0 100,162 0,162" fill="<?= $pri ?>"/>
                    <circle cx="22"  cy="28"  r="18" fill="rgba(255,255,255,0.07)"/>
                    <circle cx="70"  cy="15"  r="24" fill="rgba(255,255,255,0.05)"/>
                    <circle cx="18"  cy="130" r="22" fill="rgba(255,255,255,0.06)"/>
                    <circle cx="90"  cy="100" r="14" fill="rgba(255,255,255,0.04)"/>
                    <line x1="0" y1="55"  x2="122" y2="55"  stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
                    <line x1="0" y1="105" x2="108" y2="105" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
                </svg>
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;">
                    <div style="width:44%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5%;padding:0 2%;">
                        <div style="width:38%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;">
                            <?= icardPhoto($photoPath) ?>
                        </div>
                        <div style="font-size:clamp(0.36rem,0.85vw,0.52rem);color:rgba(255,255,255,0.55);text-align:center;font-family:monospace;text-transform:uppercase;letter-spacing:0.05em;"><?= htmlspecialchars($card['card_number']) ?></div>
                    </div>
                    <div style="flex:1;padding:4% 4% 4% 1%;color:<?= $txt ?>;min-width:0;">
                        <div style="font-size:clamp(0.65rem,1.6vw,0.92rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.47rem,1.1vw,0.67rem);color:<?= $acc ?>;margin-top:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $roleVal ?></div><?php endif; ?>
                        <div style="width:55%;height:1.5px;background:<?= $acc ?>;border-radius:2px;margin-top:4%;margin-bottom:4%;opacity:0.5;"></div>
                        <div style="display:flex;flex-direction:column;gap:3%;"><?= icardFieldList($shownFlds, $txt) ?></div>
                    </div>
                </div>
                <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:5%;right:3%;width:9%;aspect-ratio:1;object-fit:contain;opacity:0.7;">
                <?php endif; ?>
            </div>

            <?php endif; // end design style ?>
            </div><!-- /.id-card-display -->

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
                        <td style="padding:6px 0;color:var(--text-secondary);">Design Style</td>
                        <td style="padding:6px 0;font-weight:500;text-transform:capitalize;"><?= htmlspecialchars(str_replace('_',' ',$designStyle)) ?></td>
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
                    <?= htmlspecialchars($aiSugg['template_tip']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($aiSugg['missing_fields'])): ?>
                <div style="font-size:0.78rem;color:var(--amber);margin-bottom:8px;padding:8px;background:rgba(245,158,11,0.08);border-radius:8px;border:1px solid rgba(245,158,11,0.2);">
                    <?= htmlspecialchars($aiSugg['missing_fields']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($aiSugg['ai_text'])): ?>
                <div style="font-size:0.78rem;color:var(--cyan);margin-bottom:8px;padding:8px;background:rgba(0,240,255,0.05);border-radius:8px;">
                    <?= htmlspecialchars($aiSugg['ai_text']) ?>
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
