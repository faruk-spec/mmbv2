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

$bg   = htmlspecialchars($design['bg_color']      ?? $tplConfig['bg'],    ENT_QUOTES, 'UTF-8');
$pri  = htmlspecialchars($design['primary_color'] ?? $tplConfig['color'], ENT_QUOTES, 'UTF-8');
$acc  = htmlspecialchars($design['accent_color']  ?? $tplConfig['accent'],ENT_QUOTES, 'UTF-8');
$txt  = htmlspecialchars($design['text_color']    ?? $tplConfig['text'],  ENT_QUOTES, 'UTF-8');
$font = htmlspecialchars($design['font_family']   ?? 'Poppins',           ENT_QUOTES, 'UTF-8');

$allowedStyles = ['classic','sidebar','wave','bold_header','diagonal'];
$rawStyle      = $design['design_style'] ?? 'classic';
$designStyle   = in_array($rawStyle, $allowedStyles, true) ? $rawStyle : 'classic';

// ── Data helpers ──────────────────────────────────────────────────────────────
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
    $shownFlds[] = ['key'=>$fKey, 'val'=>htmlspecialchars($fVal, ENT_QUOTES, 'UTF-8'), 'icon'=>$icons[$fKey] ?? 'info-circle'];
}

$photoPath = (!empty($card['photo_path']) && file_exists(BASE_PATH . '/' . $card['photo_path']))
    ? '/' . htmlspecialchars($card['photo_path'], ENT_QUOTES, 'UTF-8') : '';
$logoPath  = (!empty($card['logo_path'])  && file_exists(BASE_PATH . '/' . $card['logo_path']))
    ? '/' . htmlspecialchars($card['logo_path'],  ENT_QUOTES, 'UTF-8') : '';

// ── Field-list HTML ─────────────────────────────────────────────────────────
function icardFieldList(array $flds, string $color): string {
    $out = '';
    foreach ($flds as $f) {
        $out .= '<div style="display:flex;align-items:center;gap:4%;font-size:clamp(0.5rem,1.1vw,0.68rem);opacity:0.88;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:' . $color . ';margin-bottom:1%;">';
        $out .= '<i class="fas fa-' . $f['icon'] . '" style="font-size:0.5rem;opacity:0.6;flex-shrink:0;"></i>';
        $out .= '<span>' . $f['val'] . '</span></div>';
    }
    return $out;
}

// ── Photo element ─────────────────────────────────────────────────────────────
function icardPhoto(string $photoPath): string {
    if ($photoPath) {
        return '<img src="' . $photoPath . '" style="width:100%;height:100%;object-fit:cover;" alt="Photo">';
    }
    return '<i class="fas fa-user" style="font-size:2rem;opacity:0.6;"></i>';
}

// ── Dot-grid watermark SVG ────────────────────────────────────────────────────
function icardDotGrid(string $color, string $opacity = '0.055'): string {
    $dots = '';
    for ($r = 0; $r < 10; $r++) {
        for ($c = 0; $c < 16; $c++) {
            $dots .= '<circle cx="' . ($c*10+5) . '" cy="' . ($r*10+5) . '" r="1.2" fill="' . $color . '"/>';
        }
    }
    return '<svg style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:' . $opacity . ';pointer-events:none;" viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg">' . $dots . '</svg>';
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=<?= urlencode($font) ?>:wght@400;600;700&display=swap');

.view-card-wrap { max-width:920px; margin:0 auto; }

.id-card-display {
    width:100%; max-width:500px; margin:0 auto;
    border-radius:18px; overflow:hidden;
    box-shadow:0 28px 80px rgba(0,0,0,0.5);
    font-family:'<?= $font ?>','Poppins',sans-serif;
    aspect-ratio:85.6/54;
    position:relative;
}

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
            <?php if ($designStyle === 'classic'): ?>
            <!-- ═══ CLASSIC: Hexagon mesh + gradient header ═══ -->
            <div style="width:100%;height:100%;background:<?= $bg ?>;font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;">
                <!-- Hexagon mesh texture -->
                <svg style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="vhx" patternUnits="userSpaceOnUse" width="11" height="19.1">
                            <polygon points="5.5,0.4 10,3 10,8.6 5.5,11.2 1,8.6 1,3" fill="none" stroke="<?= $pri ?>" stroke-width="0.5" opacity="0.18"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#vhx)"/>
                </svg>
                <!-- Gradient header with diagonal stripes -->
                <div style="position:absolute;top:0;left:0;right:0;height:10%;background:linear-gradient(90deg,<?= $pri ?> 0%,<?= $acc ?> 100%);overflow:hidden;">
                    <svg style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0.18;" viewBox="0 0 200 20" preserveAspectRatio="none">
                        <path d="M0,0 L10,0 L0,20 Z" fill="white"/><path d="M20,0 L30,0 L20,20 Z" fill="white"/>
                        <path d="M40,0 L50,0 L40,20 Z" fill="white"/><path d="M60,0 L70,0 L60,20 Z" fill="white"/>
                        <path d="M80,0 L90,0 L80,20 Z" fill="white"/><path d="M100,0 L110,0 L100,20 Z" fill="white"/>
                        <path d="M120,0 L130,0 L120,20 Z" fill="white"/><path d="M140,0 L150,0 L140,20 Z" fill="white"/>
                        <path d="M160,0 L170,0 L160,20 Z" fill="white"/><path d="M180,0 L190,0 L180,20 Z" fill="white"/>
                    </svg>
                </div>
                <!-- Bottom footer bar -->
                <div style="position:absolute;bottom:0;left:0;right:0;height:5%;background:linear-gradient(90deg,<?= $acc ?>,<?= $pri ?>);opacity:0.35;"></div>
                <!-- Content -->
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:13% 5% 7%;color:<?= $txt ?>;">
                    <!-- Photo with gradient ring -->
                    <div style="flex-shrink:0;width:20%;aspect-ratio:1;border-radius:50%;background:linear-gradient(135deg,<?= $pri ?>,<?= $acc ?>);padding:2.5px;box-shadow:0 3px 12px rgba(0,0,0,0.2);">
                        <div style="width:100%;height:100%;border-radius:50%;background:<?= $bg ?>;padding:2px;">
                            <div style="width:100%;height:100%;border-radius:50%;background:<?= $pri ?>18;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                <?= icardPhoto($photoPath) ?>
                            </div>
                        </div>
                    </div>
                    <!-- Info -->
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:clamp(0.75rem,2vw,1.1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <div style="width:35%;height:2px;background:linear-gradient(90deg,<?= $acc ?>,transparent);border-radius:2px;margin:3% 0;"></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.55rem,1.3vw,0.75rem);color:<?= $acc ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;margin-bottom:4%;"><?= $roleVal ?></div><?php endif; ?>
                        <div style="display:flex;flex-direction:column;"><?= icardFieldList($shownFlds, $txt) ?></div>
                    </div>
                </div>
                <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:12%;right:4%;width:9%;aspect-ratio:1;object-fit:contain;">
                <?php endif; ?>
                <div style="position:absolute;bottom:7%;right:4%;font-size:clamp(0.38rem,0.85vw,0.52rem);font-family:monospace;opacity:0.45;color:<?= $txt ?>;"><?= htmlspecialchars($card['card_number']) ?></div>
            </div>

            <?php elseif ($designStyle === 'sidebar'): ?>
            <!-- ═══ SIDEBAR: Circuit-board traces + dot watermark ═══ -->
            <div style="width:100%;height:100%;display:flex;font-family:'<?= $font ?>',sans-serif;overflow:hidden;">
                <!-- Left sidebar -->
                <div style="width:29%;background:<?= $pri ?>;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5%;padding:5% 0;flex-shrink:0;position:relative;overflow:hidden;">
                    <!-- Circuit board traces SVG -->
                    <svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 80 120" xmlns="http://www.w3.org/2000/svg">
                        <line x1="0" y1="12" x2="35" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="45" y1="12" x2="80" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="0" y1="28" x2="22" y2="28" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="58" y1="28" x2="80" y2="28" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="8" y1="55" x2="72" y2="55" stroke="rgba(255,255,255,0.18)" stroke-width="0.8"/>
                        <line x1="0" y1="75" x2="48" y2="75" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="32" y1="90" x2="80" y2="90" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="15" y1="108" x2="65" y2="108" stroke="rgba(255,255,255,0.18)" stroke-width="0.8"/>
                        <line x1="18" y1="0" x2="18" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="18" y1="28" x2="18" y2="55" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="35" y1="12" x2="35" y2="28" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="55" y1="0" x2="55" y2="12" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="55" y1="28" x2="55" y2="55" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="62" y1="55" x2="62" y2="75" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="22" y1="75" x2="22" y2="90" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="48" y1="90" x2="48" y2="108" stroke="rgba(255,255,255,0.22)" stroke-width="0.8"/>
                        <line x1="32" y1="108" x2="32" y2="120" stroke="rgba(255,255,255,0.18)" stroke-width="0.8"/>
                        <circle cx="18" cy="12" r="2" fill="rgba(255,255,255,0.65)"/>
                        <circle cx="35" cy="28" r="2" fill="rgba(255,255,255,0.65)"/>
                        <circle cx="55" cy="12" r="2" fill="rgba(255,255,255,0.65)"/>
                        <circle cx="55" cy="55" r="2" fill="rgba(255,255,255,0.65)"/>
                        <circle cx="62" cy="75" r="2" fill="rgba(255,255,255,0.65)"/>
                        <circle cx="22" cy="90" r="2" fill="rgba(255,255,255,0.65)"/>
                        <circle cx="48" cy="108" r="2" fill="rgba(255,255,255,0.65)"/>
                        <circle cx="18" cy="55" r="1.4" fill="rgba(255,255,255,0.4)"/>
                        <circle cx="22" cy="75" r="1.4" fill="rgba(255,255,255,0.4)"/>
                    </svg>
                    <!-- Square rounded-corner photo -->
                    <div style="width:55%;aspect-ratio:1;border-radius:10%;border:2px solid rgba(255,255,255,0.6);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;box-shadow:0 4px 16px rgba(0,0,0,0.2);">
                        <?= icardPhoto($photoPath) ?>
                    </div>
                    <div style="font-size:clamp(0.36rem,0.8vw,0.52rem);color:rgba(255,255,255,0.5);letter-spacing:0.1em;text-transform:uppercase;position:relative;z-index:1;text-align:center;padding:0 8%;"><?= htmlspecialchars($tplConfig['name'] ?? 'ID Card') ?></div>
                </div>
                <!-- Right content panel -->
                <div style="flex:1;background:<?= $bg ?>;position:relative;overflow:hidden;">
                    <?= icardDotGrid($txt) ?>
                    <!-- Left accent bar -->
                    <div style="position:absolute;top:0;bottom:0;left:0;width:3px;background:linear-gradient(180deg,<?= $pri ?>,<?= $acc ?>);"></div>
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0;padding:5% 6% 5% 9%;display:flex;flex-direction:column;justify-content:center;color:<?= $txt ?>;min-width:0;">
                        <div style="font-size:clamp(0.72rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.5rem,1.2vw,0.7rem);color:<?= $acc ?>;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;"><?= $roleVal ?></div><?php endif; ?>
                        <div style="width:40%;height:1.5px;background:linear-gradient(90deg,<?= $acc ?>,transparent);border-radius:2px;margin:4% 0;"></div>
                        <div style="display:flex;flex-direction:column;"><?= icardFieldList($shownFlds, $txt) ?></div>
                        <div style="margin-top:auto;font-size:clamp(0.36rem,0.75vw,0.5rem);font-family:monospace;opacity:0.38;color:<?= $txt ?>;"><?= htmlspecialchars($card['card_number']) ?></div>
                    </div>
                    <?php if ($logoPath): ?>
                    <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:6%;right:5%;width:9%;aspect-ratio:1;object-fit:contain;opacity:0.75;z-index:1;">
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($designStyle === 'wave'): ?>
            <!-- ═══ WAVE: Aurora gradient + 5 layered waves ═══ -->
            <div style="width:100%;height:100%;background:linear-gradient(140deg,<?= $pri ?> 0%,<?= $acc ?> 60%,<?= $pri ?>cc 100%);font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;">
                <!-- 5 layered waves -->
                <svg style="position:absolute;bottom:0;left:0;width:100%;height:68%;" viewBox="0 0 400 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,80 Q50,50 100,72 Q150,94 200,62 Q250,30 300,58 Q350,86 400,55 L400,120 L0,120 Z" fill="rgba(255,255,255,0.04)"/>
                    <path d="M0,90 Q60,65 120,85 Q180,105 240,78 Q300,51 360,74 Q380,82 400,65 L400,120 L0,120 Z" fill="rgba(255,255,255,0.06)"/>
                    <path d="M0,100 Q70,82 140,96 Q210,110 280,88 Q340,70 400,84 L400,120 L0,120 Z" fill="rgba(255,255,255,0.08)"/>
                    <path d="M0,108 Q80,98 160,105 Q240,112 320,100 Q360,94 400,102 L400,120 L0,120 Z" fill="rgba(255,255,255,0.1)"/>
                    <path d="M0,114 Q90,108 180,112 Q270,116 360,108 L400,112 L400,120 L0,120 Z" fill="rgba(255,255,255,0.13)"/>
                </svg>
                <!-- Decorative circles top-right -->
                <div style="position:absolute;top:-22%;right:-12%;width:50%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
                <div style="position:absolute;top:2%;right:8%;width:20%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
                <div style="position:absolute;top:20%;right:-5%;width:14%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.04);"></div>
                <!-- Content -->
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;gap:5%;padding:5% 6%;color:rgba(255,255,255,0.96);">
                    <!-- Photo with double white ring -->
                    <div style="flex-shrink:0;width:21%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.7);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 0 0 4px rgba(255,255,255,0.15);">
                        <?= icardPhoto($photoPath) ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:clamp(0.72rem,1.8vw,1rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.5rem,1.2vw,0.7rem);opacity:0.85;margin-top:2%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:500;"><?= $roleVal ?></div><?php endif; ?>
                        <div style="width:40%;height:1.5px;background:rgba(255,255,255,0.4);border-radius:2px;margin:4% 0;"></div>
                        <div style="display:flex;flex-direction:column;"><?= icardFieldList($shownFlds, 'rgba(255,255,255,0.92)') ?></div>
                    </div>
                </div>
                <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:6%;right:4%;width:9%;aspect-ratio:1;object-fit:contain;filter:brightness(0) invert(1);opacity:0.65;">
                <?php endif; ?>
                <div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.36rem,0.75vw,0.5rem);font-family:monospace;opacity:0.4;color:white;letter-spacing:0.05em;"><?= htmlspecialchars($card['card_number']) ?></div>
            </div>

            <?php elseif ($designStyle === 'bold_header'): ?>
            <!-- ═══ GEOMETRIC: Low-poly triangle tessellation header ═══ -->
            <div style="width:100%;height:100%;background:<?= $bg ?>;font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;color:<?= $txt ?>;">
                <!-- Header block with triangle tessellation -->
                <div style="position:absolute;top:0;left:0;right:0;height:42%;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?>cc 100%);overflow:hidden;">
                    <svg style="position:absolute;right:0;top:0;width:70%;height:135%;opacity:0.13;" viewBox="0 0 200 120" preserveAspectRatio="xMaxYMid slice">
                        <polygon points="0,0 40,0 20,25" fill="rgba(255,255,255,0.9)"/>
                        <polygon points="40,0 80,0 60,25" fill="rgba(255,255,255,0.5)"/>
                        <polygon points="80,0 120,0 100,25" fill="rgba(255,255,255,0.75)"/>
                        <polygon points="120,0 160,0 140,25" fill="rgba(255,255,255,0.4)"/>
                        <polygon points="160,0 200,0 180,25" fill="rgba(255,255,255,0.65)"/>
                        <polygon points="20,25 60,25 40,0" fill="rgba(255,255,255,0.35)"/>
                        <polygon points="60,25 100,25 80,0" fill="rgba(255,255,255,0.7)"/>
                        <polygon points="100,25 140,25 120,0" fill="rgba(255,255,255,0.45)"/>
                        <polygon points="140,25 180,25 160,0" fill="rgba(255,255,255,0.8)"/>
                        <polygon points="0,25 40,25 20,50" fill="rgba(255,255,255,0.6)"/>
                        <polygon points="40,25 80,25 60,50" fill="rgba(255,255,255,0.85)"/>
                        <polygon points="80,25 120,25 100,50" fill="rgba(255,255,255,0.4)"/>
                        <polygon points="120,25 160,25 140,50" fill="rgba(255,255,255,0.7)"/>
                        <polygon points="160,25 200,25 180,50" fill="rgba(255,255,255,0.5)"/>
                        <polygon points="20,50 60,50 40,25" fill="rgba(255,255,255,0.75)"/>
                        <polygon points="60,50 100,50 80,25" fill="rgba(255,255,255,0.4)"/>
                        <polygon points="100,50 140,50 120,25" fill="rgba(255,255,255,0.85)"/>
                        <polygon points="140,50 180,50 160,25" fill="rgba(255,255,255,0.55)"/>
                        <polygon points="0,50 40,50 20,75" fill="rgba(255,255,255,0.5)"/>
                        <polygon points="40,50 80,50 60,75" fill="rgba(255,255,255,0.7)"/>
                        <polygon points="80,50 120,50 100,75" fill="rgba(255,255,255,0.6)"/>
                        <polygon points="120,50 160,50 140,75" fill="rgba(255,255,255,0.4)"/>
                        <polygon points="160,50 200,50 180,75" fill="rgba(255,255,255,0.8)"/>
                        <polygon points="20,75 60,75 40,50" fill="rgba(255,255,255,0.45)"/>
                        <polygon points="60,75 100,75 80,50" fill="rgba(255,255,255,0.65)"/>
                        <polygon points="100,75 140,75 120,50" fill="rgba(255,255,255,0.75)"/>
                        <polygon points="140,75 180,75 160,50" fill="rgba(255,255,255,0.5)"/>
                    </svg>
                    <!-- Name / role in header -->
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;justify-content:center;padding:0 5% 0 34%;">
                        <div style="font-size:clamp(0.65rem,1.7vw,0.95rem);font-weight:700;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.47rem,1.1vw,0.68rem);color:rgba(255,255,255,0.8);margin-top:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $roleVal ?></div><?php endif; ?>
                    </div>
                    <?php if ($logoPath): ?>
                    <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:8%;right:4%;width:9%;aspect-ratio:1;object-fit:contain;opacity:0.8;z-index:1;">
                    <?php endif; ?>
                </div>
                <!-- Photo overlapping header/body -->
                <div style="position:absolute;top:22%;left:4%;width:23%;aspect-ratio:1;border-radius:50%;border:3px solid <?= $bg ?>;background:<?= $acc ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:2;box-shadow:0 4px 18px rgba(0,0,0,0.22);">
                    <?= icardPhoto($photoPath) ?>
                </div>
                <!-- Body / fields -->
                <div style="position:absolute;top:44%;left:0;right:0;bottom:0;padding:2% 5% 4% 5%;display:flex;flex-direction:column;">
                    <div style="display:flex;align-items:center;gap:4%;margin-bottom:3%;">
                        <div style="flex:1;height:1.5px;background:linear-gradient(90deg,<?= $acc ?>,transparent);border-radius:2px;"></div>
                    </div>
                    <div style="display:flex;flex-direction:column;"><?= icardFieldList($shownFlds, $txt) ?></div>
                </div>
                <div style="position:absolute;bottom:3%;right:4%;font-size:clamp(0.35rem,0.7vw,0.5rem);font-family:monospace;opacity:0.3;color:<?= $txt ?>;"><?= htmlspecialchars($card['card_number']) ?></div>
            </div>

            <?php elseif ($designStyle === 'diagonal'): ?>
            <!-- ═══ MOSAIC: Halftone dot grid + gradient diagonal split ═══ -->
            <div style="width:100%;height:100%;background:<?= $bg ?>;font-family:'<?= $font ?>',sans-serif;position:relative;overflow:hidden;">
                <!-- Diagonal gradient polygon background -->
                <svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 256 162" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="vdg" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="<?= $pri ?>"/>
                            <stop offset="100%" stop-color="<?= $acc ?>"/>
                        </linearGradient>
                    </defs>
                    <polygon points="0,0 155,0 105,162 0,162" fill="url(#vdg)"/>
                </svg>
                <!-- Halftone dot grid clipped to colored zone -->
                <svg style="position:absolute;top:0;left:0;width:62%;height:100%;opacity:0.9;" viewBox="0 0 155 162" xmlns="http://www.w3.org/2000/svg">
                    <defs><clipPath id="vdcl"><polygon points="0,0 155,0 105,162 0,162"/></clipPath></defs>
                    <g clip-path="url(#vdcl)">
                    <?php for ($r = 0; $r < 18; $r++): for ($c = 0; $c < 17; $c++): ?>
                    <circle cx="<?= $c*9+5 ?>" cy="<?= $r*9+5 ?>" r="1.8" fill="rgba(255,255,255,0.3)"/>
                    <?php endfor; endfor; ?>
                    </g>
                </svg>
                <!-- Content layer -->
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;">
                    <!-- Left colored zone -->
                    <div style="width:45%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5%;padding:0 3%;">
                        <div style="width:40%;aspect-ratio:1;border-radius:12%;border:2.5px solid rgba(255,255,255,0.65);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,0.15);">
                            <?= icardPhoto($photoPath) ?>
                        </div>
                        <div style="font-size:clamp(0.34rem,0.8vw,0.5rem);color:rgba(255,255,255,0.6);text-align:center;font-family:monospace;text-transform:uppercase;letter-spacing:0.07em;"><?= htmlspecialchars($card['card_number']) ?></div>
                    </div>
                    <!-- Right light zone -->
                    <div style="flex:1;padding:4% 4% 4% 2%;color:<?= $txt ?>;min-width:0;">
                        <div style="font-size:clamp(0.65rem,1.6vw,0.92rem);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
                        <?php if ($roleVal): ?><div style="font-size:clamp(0.47rem,1.1vw,0.67rem);color:<?= $acc ?>;margin-top:3%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;"><?= $roleVal ?></div><?php endif; ?>
                        <div style="display:flex;align-items:center;gap:5%;margin:4% 0;">
                            <div style="flex:1;height:1.5px;background:linear-gradient(90deg,<?= $acc ?>,transparent);border-radius:2px;"></div>
                        </div>
                        <div style="display:flex;flex-direction:column;"><?= icardFieldList($shownFlds, $txt) ?></div>
                    </div>
                </div>
                <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo" style="position:absolute;top:5%;right:3%;width:9%;aspect-ratio:1;object-fit:contain;opacity:0.7;">
                <?php endif; ?>
            </div>

            <?php endif; // end design style ?>
            </div><!-- /.id-card-display -->

            <p style="font-size:0.75rem;color:var(--text-secondary);margin-top:14px;text-align:center;">
                <i class="fas fa-info-circle"></i> Click "Print / Save PDF" to download
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
                <div style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:8px;padding:8px;background:var(--bg-secondary);border-radius:8px;"><?= htmlspecialchars($aiSugg['template_tip']) ?></div>
                <?php endif; ?>
                <?php if (!empty($aiSugg['missing_fields'])): ?>
                <div style="font-size:0.78rem;color:var(--amber);margin-bottom:8px;padding:8px;background:rgba(245,158,11,0.08);border-radius:8px;border:1px solid rgba(245,158,11,0.2);"><?= htmlspecialchars($aiSugg['missing_fields']) ?></div>
                <?php endif; ?>
                <?php if (!empty($aiSugg['ai_text'])): ?>
                <div style="font-size:0.78rem;color:var(--cyan);margin-bottom:8px;padding:8px;background:rgba(0,240,255,0.05);border-radius:8px;"><?= htmlspecialchars($aiSugg['ai_text']) ?></div>
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
