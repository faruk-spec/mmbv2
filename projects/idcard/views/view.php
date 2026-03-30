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

$isPortrait    = ($tplConfig['orientation'] ?? 'landscape') === 'portrait';
$allowedStyles = ['classic','sidebar','wave','bold_header','diagonal',
                  'gradient_pro','neon','executive','stripe','metro',
                  'glass','zigzag','ribbon',
                  'v_sharp','v_curve','v_hex','v_circle','v_split',
                  'v_ribbon','v_arch','v_diamond','v_corner','v_dual',
                  'v_stripe','v_badge'];
$rawStyle      = $design['design_style'] ?? ($isPortrait ? 'v_sharp' : 'classic');
$designStyle   = in_array($rawStyle, $allowedStyles, true) ? $rawStyle : ($isPortrait ? 'v_sharp' : 'classic');

// ── Data helpers ──────────────────────────────────────────────────────────────
$roleKeys = ['designation','title','course','event_name'];
$nameVal  = htmlspecialchars($cd['name']        ?? 'YOUR NAME',   ENT_QUOTES, 'UTF-8');
$orgVal   = htmlspecialchars($cd['company_name'] ?? $cd['school_name'] ?? $tplConfig['name'] ?? '', ENT_QUOTES, 'UTF-8');
$addrVal  = htmlspecialchars($cd['company_address'] ?? $cd['school_address'] ?? '', ENT_QUOTES, 'UTF-8');
$roleVal  = '';
foreach ($roleKeys as $rk) {
    if (!empty($cd[$rk])) { $roleVal = htmlspecialchars($cd[$rk], ENT_QUOTES, 'UTF-8'); break; }
}

// Field label map for short display labels (matching reference images exactly)
$fieldLabelMap = [
    'department'      => 'DEPT',
    'employee_id'     => 'ID NO',
    'roll_number'     => 'ROLL NO',
    'id_number'       => 'ID NO',
    'badge_id'        => 'BADGE',
    'license_no'      => 'LIC NO',
    'blood_group'     => 'B.GROUP',
    'phone'           => 'PHONE',
    'email'           => 'E-MAIL',
    'year'            => 'YEAR',
    'organization'    => 'ORG',
    'host_name'       => 'HOST',
    'purpose'         => 'PURPOSE',
    'visit_date'      => 'DATE',
    'dob'             => 'D.O.B',
    'expiry_date'     => 'EXPIRE',
    'valid_from'      => 'VALID FROM',
    'valid_till'      => 'VALID TILL',
    'nationality'     => 'NATION',
    'branch'          => 'BRANCH',
    'shift'           => 'SHIFT',
    'session'         => 'SESSION',
    'reg_number'      => 'REG NO',
    'zone'            => 'ZONE',
    'rank'            => 'RANK',
    'gender'          => 'GENDER',
    'joining_date'    => 'JOINED',
    'company_address' => 'ADDRESS',
    'school_address'  => 'ADDRESS',
];

// Fields to skip in detail list (shown elsewhere on card)
$skipKeys  = array_merge(['name','company_name','school_name','company_address','school_address'], $roleKeys);
$shownFlds = [];
foreach ($cd as $fKey => $fVal) {
    if (in_array($fKey, $skipKeys) || !$fVal || count($shownFlds) >= 6) continue;
    $shownFlds[] = [
        'label' => $fieldLabelMap[$fKey] ?? strtoupper(str_replace('_',' ',$fKey)),
        'val'   => htmlspecialchars($fVal, ENT_QUOTES, 'UTF-8'),
    ];
}

$photoPath = (!empty($card['photo_path']) && file_exists(BASE_PATH . '/' . $card['photo_path']))
    ? '/' . htmlspecialchars($card['photo_path'], ENT_QUOTES, 'UTF-8') : '';
$logoPath  = (!empty($card['logo_path'])  && file_exists(BASE_PATH . '/' . $card['logo_path']))
    ? '/' . htmlspecialchars($card['logo_path'],  ENT_QUOTES, 'UTF-8') : '';

$tplName = htmlspecialchars($tplConfig['name'] ?? 'CardX', ENT_QUOTES, 'UTF-8');

// ── Helper functions (guarded against duplicate inclusion) ────────────────────
if (!function_exists('icardBarcodeSvg')) {
function icardBarcodeSvg(string $color, string $width = '80%'): string {
    $bars = [2,1,3,1,1,2,1,3,2,1,1,2,1,1,3,1,2,1,1,3,2,1,2,1,1,3,1,1,2,1,3,1,2,1,1,2,3,1,1];
    $svg  = '<svg viewBox="0 0 80 14" xmlns="http://www.w3.org/2000/svg" style="display:block;width:'.$width.';height:auto;">';
    $x = 0; $isBar = true;
    foreach ($bars as $w) {
        if ($isBar) $svg .= '<rect x="'.round($x,2).'" y="0" width="'.$w.'" height="14" fill="'.$color.'"/>';
        $x += $w + 1;
        $isBar = !$isBar;
    }
    return $svg . '</svg>';
}
}

if (!function_exists('icardPhoto')) {
function icardPhoto(string $photoPath, string $sz = '2rem'): string {
    if ($photoPath)
        return '<img src="'.$photoPath.'" style="width:100%;height:100%;object-fit:cover;" alt="Photo">';
    return '<i class="fas fa-user" style="font-size:'.$sz.';opacity:0.55;color:rgba(255,255,255,0.8);"></i>';
}
}

// LABEL : VALUE field row (matching reference image typography)
if (!function_exists('icardRow')) {
function icardRow(string $label, string $value, string $lc, string $vc, string $fs = '0.52rem'): string {
    return '<div style="display:flex;align-items:baseline;font-size:'.$fs.';white-space:nowrap;overflow:hidden;margin-bottom:1.8%;">'
        .'<span style="color:'.$lc.';font-weight:700;min-width:30%;letter-spacing:0.03em;">'.$label.'</span>'
        .'<span style="color:'.$vc.';margin-left:2%;">: '.$value.'</span>'
        .'</div>';
}
}

if (!function_exists('icardLogoEl')) {
function icardLogoEl(string $logoPath, string $size, string $iconColor = 'rgba(255,255,255,0.9)'): string {
    if ($logoPath)
        return '<img src="'.$logoPath.'" alt="Logo" style="width:'.$size.';height:'.$size.';object-fit:contain;">';
    return '<div style="width:'.$size.';height:'.$size.';border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">'
        .'<i class="fas fa-infinity" style="color:'.$iconColor.';font-size:calc('.$size.' * 0.45);"></i>'
        .'</div>';
}
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=<?= urlencode($font) ?>:wght@400;600;700;800&display=swap');
.view-card-wrap { max-width: 1040px; margin: 0 auto; }
.id-card-display {
    margin: 0 auto;
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 28px 80px rgba(0,0,0,0.5);
    position: relative;
}
.id-card-display.landscape {
    width: 100%; max-width: 520px;
    aspect-ratio: 85.6/54;
}
.id-card-display.portrait {
    width: 100%; max-width: 300px;
    aspect-ratio: 54/85.6;
}
@media print {
    .back-link,.view-actions,.ai-panel-view,.navbar { display: none !important; }
    body { background: white; }
    .cx-sidebar,.sidebar-toggle { display: none !important; }
    .cx-main { margin-left: 0 !important; padding: 0 !important; }
    .id-card-display { box-shadow: none; border-radius: 0; }
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

<!-- ══════════════════════ CARD VISUAL ══════════════════════ -->
<div style="text-align:center;">
<div class="id-card-display <?= $isPortrait ? 'portrait' : 'landscape' ?>" style="font-family:'<?= $font ?>','Poppins',sans-serif;">

<?php /* ══════════════════════════════════════════════════════
        LANDSCAPE STYLES (horizontal cards)
   ══════════════════════════════════════════════════════ */
if ($designStyle === 'classic'): ?>
<!-- ── ANGLED PRO (landscape) ── coloured angled header, centred circle photo ── -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:100%;overflow:hidden;pointer-events:none;">
        <div style="position:absolute;inset:0;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?> 100%);clip-path:polygon(0 0,100% 0,100% 56%,0 72%);"></div>
        <div style="position:absolute;inset:0;background:rgba(255,255,255,0.08);clip-path:polygon(0 70%,100% 54%,100% 59%,0 75%);"></div>
    </div>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:32%;transform:translateX(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:<?= $pri ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(0,0,0,0.22);">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>
    <div style="position:absolute;top:58%;left:0;right:0;text-align:center;padding:0 4%;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:70%;left:5%;right:5%;display:grid;grid-template-columns:1fr 1fr;column-gap:3%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:3%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'48%') ?></div>
</div>

<?php elseif ($designStyle === 'sidebar'): ?>
<!-- ── DARK GEO (landscape) ── dark bg, rotated diamond accent, photo ── -->
<div style="width:100%;height:100%;background:#111827;position:relative;overflow:hidden;">
    <svg style="position:absolute;top:-18%;right:-12%;width:60%;aspect-ratio:1;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <rect x="15" y="15" width="70" height="70" rx="3" fill="<?= $pri ?>" transform="rotate(45 50 50)"/>
    </svg>
    <svg style="position:absolute;top:-8%;right:-5%;width:42%;aspect-ratio:1;opacity:0.35;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <rect x="15" y="15" width="70" height="70" rx="3" fill="<?= $acc ?>" transform="rotate(45 50 50)"/>
    </svg>
    <div style="position:absolute;top:6%;right:6%;width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.7);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,0,0,0.4);">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.34rem,0.78vw,0.5rem);color:rgba(255,255,255,0.8);font-weight:700;letter-spacing:0.07em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;bottom:36%;left:5%;">
        <div style="font-size:clamp(0.7rem,1.7vw,1rem);font-weight:800;color:#fff;letter-spacing:0.03em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:55%;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.38rem,0.85vw,0.54rem);color:<?= $acc ?>;margin-top:1.5%;letter-spacing:0.04em;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;bottom:4%;left:5%;right:52%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.55)', 'rgba(255,255,255,0.88)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:4%;right:4%;width:36%;"><?= icardBarcodeSvg('rgba(255,255,255,0.35)','100%') ?></div>
</div>

<?php elseif ($designStyle === 'wave'): ?>
<!-- ── WAVE PANEL (landscape) ── cream bg, organic wave left, fields right ── -->
<div style="width:100%;height:100%;background:#fdf8f3;position:relative;overflow:hidden;">
    <svg style="position:absolute;top:0;left:0;width:44%;height:100%;" viewBox="0 0 88 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 L60,0 Q80,25 70,55 Q85,80 72,110 Q88,135 65,160 L0,160 Z" fill="<?= $pri ?>"/>
        <path d="M0,0 L45,0 Q62,22 55,52 Q68,78 56,108 Q70,132 50,160 L0,160 Z" fill="rgba(255,255,255,0.07)"/>
    </svg>
    <div style="position:absolute;left:24%;top:18%;transform:translateX(-50%);width:24%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 22px rgba(0,0,0,0.25);">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>
    <div style="position:absolute;bottom:12%;left:5%;max-width:40%;">
        <div style="font-size:clamp(0.6rem,1.4vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.35rem,0.78vw,0.5rem);color:rgba(255,255,255,0.75);margin-top:1.5%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;bottom:3%;left:5%;width:36%;"><?= icardBarcodeSvg('rgba(255,255,255,0.45)','100%') ?></div>
    <div style="position:absolute;top:5%;right:5%;display:flex;align-items:center;gap:5%;">
        <?= icardLogoEl($logoPath,'7%',$pri) ?>
        <span style="font-size:clamp(0.32rem,0.72vw,0.46rem);color:<?= $pri ?>;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;top:14%;right:4%;width:48%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#4a3728') ?><?php endforeach; ?>
    </div>
</div>

<?php elseif ($designStyle === 'bold_header'): ?>
<!-- ── BOLD SPLIT (landscape) ── coloured left panel, white right panel ── -->
<div style="width:100%;height:100%;display:flex;overflow:hidden;position:relative;">
    <div style="width:40%;background:linear-gradient(170deg,<?= $pri ?> 0%,<?= $acc ?> 100%);display:flex;flex-direction:column;align-items:center;position:relative;overflow:hidden;flex-shrink:0;">
        <div style="position:absolute;top:-20%;left:-30%;width:90%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
        <div style="padding:10% 0 5%;position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:6%;">
            <?= icardLogoEl($logoPath,'22%') ?>
            <span style="font-size:clamp(0.3rem,0.7vw,0.44rem);color:rgba(255,255,255,0.7);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;text-align:center;"><?= $tplName ?></span>
        </div>
        <div style="width:45%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;margin-top:4%;">
            <?= icardPhoto($photoPath,'2.2rem') ?>
        </div>
        <div style="margin-top:auto;padding-bottom:6%;width:80%;position:relative;z-index:1;"><?= icardBarcodeSvg('rgba(255,255,255,0.4)','100%') ?></div>
    </div>
    <div style="flex:1;background:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:6% 7%;min-width:0;position:relative;">
        <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,<?= $pri ?>,<?= $acc ?>);"></div>
        <div style="font-size:clamp(0.62rem,1.52vw,0.88rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:#888;margin-top:1.5%;margin-bottom:4%;"><?= $roleVal ?></div><?php endif; ?>
        <div style="width:60%;height:2px;background:linear-gradient(90deg,<?= $acc ?>,transparent);border-radius:2px;margin-bottom:5%;"></div>
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#555') ?><?php endforeach; ?>
        <div style="position:absolute;bottom:4%;right:5%;font-size:clamp(0.3rem,0.68vw,0.44rem);font-family:monospace;color:#bbb;"><?= htmlspecialchars($card['card_number']) ?></div>
    </div>
</div>

<?php elseif ($designStyle === 'diagonal'): ?>
<!-- ── TRIANGLE PRO (landscape) ── dark bg, arrow triangles right ── -->
<div style="width:100%;height:100%;background:#111827;position:relative;overflow:hidden;">
    <svg style="position:absolute;right:0;top:0;width:48%;height:100%;" viewBox="0 0 96 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="40" y="0" width="56" height="160" fill="<?= $pri ?>18"/>
        <polygon points="96,0 96,62 42,31" fill="<?= $pri ?>"/>
        <polygon points="96,52 96,112 48,82" fill="<?= $acc ?>" opacity="0.85"/>
        <polygon points="96,100 96,160 44,130" fill="<?= $pri ?>" opacity="0.7"/>
    </svg>
    <div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid <?= $acc ?>;background:rgba(255,255,255,0.08);overflow:hidden;display:flex;align-items:center;justify-content:center;">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.34rem,0.76vw,0.48rem);color:rgba(255,255,255,0.65);font-weight:700;letter-spacing:0.08em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:32%;top:20%;max-width:30%;">
        <div style="font-size:clamp(0.58rem,1.38vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.34rem,0.76vw,0.5rem);color:<?= $acc ?>;margin-top:2%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;bottom:6%;left:5%;max-width:50%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.55)', 'rgba(255,255,255,0.9)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:4%;right:4%;width:36%;"><?= icardBarcodeSvg('rgba(255,255,255,0.32)','100%') ?></div>
</div>

<?php /* ══════════════════════════════════════════════════════
        PORTRAIT / VERTICAL STYLES (matching reference images)
   ══════════════════════════════════════════════════════ */
elseif ($designStyle === 'v_sharp'): ?>
<!-- ── SHARP V (portrait) ── V-chevron coloured header, circle photo, fields ──
     Matches Image 1 cards 1,2,6,7 and Image 2 cards exactly -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;display:flex;flex-direction:column;">
    <!-- Coloured top section with V/chevron cut ~42% height -->
    <div style="position:absolute;top:0;left:0;right:0;height:100%;overflow:hidden;pointer-events:none;">
        <div style="position:absolute;inset:0;background:linear-gradient(160deg,<?= $pri ?> 0%,<?= $acc ?> 100%);clip-path:polygon(0 0,100% 0,100% 38%,50% 48%,0 38%);"></div>
        <!-- Subtle inner highlight -->
        <div style="position:absolute;inset:0;background:rgba(255,255,255,0.1);clip-path:polygon(0 0,40% 0,0 20%);"></div>
    </div>
    <!-- Logo + company name top row -->
    <div style="position:absolute;top:3%;left:4%;right:4%;display:flex;align-items:center;justify-content:space-between;z-index:2;">
        <div style="display:flex;align-items:center;gap:6%;">
            <?= icardLogoEl($logoPath,'14%') ?>
            <div>
                <div style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.95);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></div>
                <?php if ($orgVal && $addrVal): ?>
                <div style="font-size:clamp(0.3rem,0.7vw,0.44rem);color:rgba(255,255,255,0.7);margin-top:1px;line-height:1.2;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= $addrVal ?></div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Small infinity / badge mark top-right -->
        <div style="width:10%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-infinity" style="color:rgba(255,255,255,0.8);font-size:0.35rem;"></i>
        </div>
    </div>
    <!-- Circular photo centred straddling header/body boundary -->
    <div style="position:absolute;left:50%;top:34%;transform:translateX(-50%);width:26%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:<?= $pri ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.25);z-index:3;">
        <?= icardPhoto($photoPath,'1.8rem') ?>
    </div>
    <!-- Name + role below photo -->
    <div style="position:absolute;top:62%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;letter-spacing:0.04em;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <!-- Thin accent divider -->
    <div style="position:absolute;top:71%;left:10%;right:10%;height:1.5px;background:linear-gradient(90deg,transparent,<?= $acc ?>,transparent);opacity:0.5;"></div>
    <!-- Fields stacked — label : value (matching reference images) -->
    <div style="position:absolute;top:73%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444','clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <!-- Barcode bottom-centre -->
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_curve'): ?>
<!-- ── CURVE WAVE (portrait) ── curved organic wave top, photo on boundary ──
     Matches Image 2 wave/organic-shape cards -->
<div style="width:100%;height:100%;background:#fafafa;position:relative;overflow:hidden;">
    <!-- Organic curve SVG top -->
    <svg style="position:absolute;top:0;left:0;width:100%;height:50%;" viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 L100,0 L100,70 Q75,95 50,80 Q25,65 0,85 Z" fill="<?= $pri ?>"/>
        <path d="M0,0 L100,0 L100,55 Q70,80 50,65 Q25,50 0,70 Z" fill="rgba(255,255,255,0.1)"/>
    </svg>
    <!-- Accent colour blob bottom-right -->
    <div style="position:absolute;bottom:-8%;right:-8%;width:35%;aspect-ratio:1;border-radius:50%;background:<?= $acc ?>;opacity:0.12;"></div>
    <!-- Logo top-left -->
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:8%;z-index:2;">
        <?= icardLogoEl($logoPath,'13%') ?>
        <span style="font-size:clamp(0.4rem,1vw,0.56rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <!-- Photo circle at wave boundary (~44% from top) -->
    <div style="position:absolute;left:50%;top:30%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.9);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.3);z-index:3;">
        <?= icardPhoto($photoPath,'1.8rem') ?>
    </div>
    <!-- Name + role centred -->
    <div style="position:absolute;top:62%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <!-- Divider -->
    <div style="position:absolute;top:70%;left:10%;right:10%;height:1px;background:<?= $pri ?>;opacity:0.2;"></div>
    <!-- Fields -->
    <div style="position:absolute;top:72%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444','clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <!-- Barcode -->
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_hex'): ?>
<!-- ── HEX BADGE (portrait) ── coloured top, hexagonal photo frame ──
     Matches Image 1 cards 4,5 with hexagon/diamond photo frame -->
<div style="width:100%;height:100%;background:#ffffff;position:relative;overflow:hidden;">
    <!-- Coloured header top ~45% -->
    <div style="position:absolute;top:0;left:0;right:0;height:45%;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?> 100%);overflow:hidden;">
        <!-- Diamond decorative shape top-right -->
        <svg style="position:absolute;top:-15%;right:-12%;width:45%;aspect-ratio:1;opacity:0.18;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect x="10" y="10" width="80" height="80" rx="4" fill="#fff" transform="rotate(45 50 50)"/>
        </svg>
    </div>
    <!-- White curved bottom border of header -->
    <div style="position:absolute;top:38%;left:0;right:0;height:8%;overflow:hidden;">
        <svg viewBox="0 0 100 20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width:100%;height:100%;">
            <path d="M0,20 Q50,-5 100,20 L100,0 L0,0 Z" fill="<?= $pri ?>"/>
            <path d="M0,20 Q50,5 100,20" fill="none" stroke="#fff" stroke-width="1.5"/>
        </svg>
    </div>
    <!-- Logo top-left -->
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:8%;z-index:2;">
        <?= icardLogoEl($logoPath,'13%') ?>
        <div style="font-size:clamp(0.4rem,1vw,0.56rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></div>
    </div>
    <!-- Hexagonal photo: implemented as clipped circle with hex border SVG overlay -->
    <div style="position:absolute;left:50%;top:25%;transform:translateX(-50%);width:28%;aspect-ratio:1;z-index:4;">
        <!-- Background hex shape -->
        <svg style="position:absolute;inset:-15%;width:130%;height:130%;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <polygon points="50,2 95,26 95,74 50,98 5,74 5,26" fill="#fff" stroke="<?= $pri ?>" stroke-width="2"/>
        </svg>
        <div style="position:absolute;inset:0;overflow:hidden;clip-path:polygon(50% 4%,93% 26%,93% 74%,50% 96%,7% 74%,7% 26%);display:flex;align-items:center;justify-content:center;background:<?= $pri ?>20;">
            <?= icardPhoto($photoPath,'1.8rem') ?>
        </div>
    </div>
    <!-- Name + role -->
    <div style="position:absolute;top:58%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <!-- Divider -->
    <div style="position:absolute;top:66%;left:8%;right:8%;height:1.5px;background:<?= $pri ?>;opacity:0.25;"></div>
    <!-- Fields -->
    <div style="position:absolute;top:68%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444','clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <!-- Barcode -->
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_circle'): ?>
<!-- ── CIRCLE TOP (portrait) ── solid top half with large circle photo frame ──
     Matches Image 1 cards 3,8,9,10 with circle cut-out photo -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">
    <!-- Solid colour top -->
    <div style="position:absolute;top:0;left:0;right:0;height:46%;background:linear-gradient(150deg,<?= $pri ?> 0%,<?= $acc ?> 100%);overflow:hidden;">
        <!-- Subtle concentric circle watermark -->
        <svg style="position:absolute;right:-10%;bottom:-20%;width:60%;aspect-ratio:1;opacity:0.1;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="48" fill="none" stroke="#fff" stroke-width="6"/>
            <circle cx="50" cy="50" r="35" fill="none" stroke="#fff" stroke-width="4"/>
            <circle cx="50" cy="50" r="20" fill="none" stroke="#fff" stroke-width="3"/>
        </svg>
    </div>
    <!-- Logo top-left -->
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">
        <?= icardLogoEl($logoPath,'12%') ?>
        <div style="font-size:clamp(0.38rem,0.9vw,0.54rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></div>
    </div>
    <!-- Large circle photo (centred, straddling boundary) -->
    <div style="position:absolute;left:50%;top:25%;transform:translateX(-50%);width:30%;aspect-ratio:1;border-radius:50%;border:4px solid #fff;background:<?= $pri ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 32px rgba(0,0,0,0.28);z-index:3;">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>
    <!-- Name + role -->
    <div style="position:absolute;top:60%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <!-- Accent bar -->
    <div style="position:absolute;top:68%;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,<?= $acc ?>,transparent);opacity:0.6;"></div>
    <!-- Fields -->
    <div style="position:absolute;top:70%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444','clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <!-- Barcode bottom -->
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_split'): ?>
<!-- ── COLOR SPLIT (portrait) ── multi-colour split layout ──
     Matches Image 2 colorful split-tone cards with photo upper-right -->
<div style="width:100%;height:100%;background:#ffffff;position:relative;overflow:hidden;">
    <!-- Left colour strip -->
    <div style="position:absolute;top:0;left:0;width:55%;height:52%;background:<?= $pri ?>;overflow:hidden;">
        <div style="position:absolute;bottom:-4%;right:-4%;width:50%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.08);"></div>
    </div>
    <!-- Right accent strip -->
    <div style="position:absolute;top:0;right:0;width:48%;height:40%;background:<?= $acc ?>;overflow:hidden;clip-path:polygon(10% 0,100% 0,100% 100%,0 100%);"></div>
    <!-- Bottom colour accent bar -->
    <div style="position:absolute;bottom:0;left:0;right:0;height:5%;background:<?= $pri ?>;"></div>
    <!-- Logo top-left -->
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:7%;z-index:2;">
        <?= icardLogoEl($logoPath,'13%') ?>
        <span style="font-size:clamp(0.38rem,0.9vw,0.54rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <!-- Photo upper-right (matching Image 2 layout with photo in right coloured area) -->
    <div style="position:absolute;top:5%;right:4%;width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 22px rgba(0,0,0,0.25);z-index:3;">
        <?= icardPhoto($photoPath,'1.8rem') ?>
    </div>
    <!-- Name + role (lower-left of top coloured section) -->
    <div style="position:absolute;top:46%;left:4%;right:4%;">
        <div style="font-size:clamp(0.82rem,2vw,1.08rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <!-- Accent divider -->
    <div style="position:absolute;top:56%;left:4%;right:4%;height:2px;background:linear-gradient(90deg,<?= $acc ?>,transparent);"></div>
    <!-- Fields -->
    <div style="position:absolute;top:58%;left:4%;right:4%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444','clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <!-- Card number -->
    <div style="position:absolute;bottom:7%;right:4%;font-size:clamp(0.3rem,0.7vw,0.44rem);font-family:monospace;color:#aaa;"><?= htmlspecialchars($card['card_number']) ?></div>
    <!-- Barcode (inline with bottom accent) -->
    <div style="position:absolute;bottom:6%;left:4%;width:50%;"><?= icardBarcodeSvg($pri,'100%') ?></div>
</div>


<?php elseif ($designStyle === 'gradient_pro'): ?>
<!-- ── GRADIENT PRO (landscape) ── full gradient bg, photo left, fields right ── -->
<div style="width:100%;height:100%;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?> 100%);position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.18);"></div>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;z-index:2;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);display:flex;align-items:center;gap:5%;z-index:2;width:90%;">
        <div style="width:26%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(0,0,0,0.3);flex-shrink:0;"><?= icardPhoto($photoPath,'2rem') ?></div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
            <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:rgba(255,255,255,0.75);margin-top:1.5%;margin-bottom:3%;"><?= $roleVal ?></div><?php endif; ?>
            <div style="width:80%;height:1.5px;background:rgba(255,255,255,0.35);border-radius:2px;margin-bottom:4%;"></div>
            <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.65)', 'rgba(255,255,255,0.9)') ?><?php endforeach; ?>
        </div>
    </div>
    <div style="position:absolute;bottom:4%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg('rgba(255,255,255,0.3)','48%') ?></div>
</div>

<?php elseif ($designStyle === 'neon'): ?>
<!-- ── NEON GLOW (landscape) ── black bg, neon borders, glow effects ── -->
<div style="width:100%;height:100%;background:#050a10;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?= $pri ?>;box-shadow:0 0 10px <?= $pri ?>;"></div>
    <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:<?= $acc ?>;box-shadow:0 0 10px <?= $acc ?>;"></div>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;z-index:2;">
        <div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.05);border:1px solid <?= $acc ?>;display:flex;align-items:center;justify-content:center;box-shadow:0 0 6px <?= $acc ?>44;">
            <i class="fas fa-infinity" style="color:<?= $acc ?>;font-size:0.4rem;"></i>
        </div>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:<?= $acc ?>;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;text-shadow:0 0 8px <?= $acc ?>80;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid <?= $acc ?>;background:rgba(255,255,255,0.04);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 0 16px <?= $acc ?>60;"><?= icardPhoto($photoPath,'2rem') ?></div>
    <div style="position:absolute;left:34%;top:20%;max-width:60%;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:<?= $acc ?>;margin-top:2%;text-shadow:0 0 8px <?= $acc ?>60;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;bottom:10%;left:34%;max-width:58%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.45)', 'rgba(255,255,255,0.85)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:4%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg('rgba(255,255,255,0.18)','48%') ?></div>
</div>

<?php elseif ($designStyle === 'executive'): ?>
<!-- ── EXECUTIVE (landscape) ── dark navy, gold accents, premium feel ── -->
<div style="width:100%;height:100%;background:#1a1f2e;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:4px;background:#c9a84c;"></div>
    <div style="position:absolute;bottom:0;left:0;right:0;height:4px;background:#c9a84c;"></div>
    <div style="position:absolute;top:4px;left:4px;right:4px;bottom:4px;border:0.5px solid rgba(201,168,76,0.25);pointer-events:none;"></div>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;z-index:2;">
        <div style="width:8%;aspect-ratio:1;border-radius:50%;background:rgba(201,168,76,0.15);border:1px solid #c9a84c;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-infinity" style="color:#c9a84c;font-size:0.4rem;"></i>
        </div>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:#c9a84c;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid #c9a84c;background:rgba(201,168,76,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;"><?= icardPhoto($photoPath,'2rem') ?></div>
    <div style="position:absolute;left:34%;top:20%;max-width:60%;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:#c9a84c;margin-top:2%;"><?= $roleVal ?></div><?php endif; ?>
        <div style="width:60%;height:1.5px;background:linear-gradient(90deg,#c9a84c,transparent);margin-top:4%;margin-bottom:4%;"></div>
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.45)', 'rgba(255,255,255,0.88)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:4%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg('rgba(201,168,76,0.35)','48%') ?></div>
</div>

<?php elseif ($designStyle === 'stripe'): ?>
<!-- ── STRIPE BAND (landscape) ── top/bottom colour bands, white middle ── -->
<div style="width:100%;height:100%;background:#f5f7fa;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:16%;background:<?= $pri ?>;"></div>
    <div style="position:absolute;bottom:0;left:0;right:0;height:16%;background:<?= $acc ?>;"></div>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;z-index:2;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid <?= $pri ?>;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(0,0,0,0.15);"><?= icardPhoto($photoPath,'2rem') ?></div>
    <div style="position:absolute;right:5%;top:20%;max-width:52%;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:#888;margin-top:2%;"><?= $roleVal ?></div><?php endif; ?>
        <div style="width:60%;height:2px;background:linear-gradient(90deg,<?= $acc ?>,transparent);border-radius:2px;margin:4% 0;"></div>
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#555') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:4%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'48%') ?></div>
</div>

<?php elseif ($designStyle === 'metro'): ?>
<!-- ── METRO FLAT (landscape) ── coloured left strip, square photo, flat design ── -->
<div style="width:100%;height:100%;display:flex;overflow:hidden;position:relative;border-top:4px solid <?= $pri ?>;border-bottom:4px solid <?= $acc ?>;">
    <div style="width:35%;background:<?= $pri ?>;display:flex;flex-direction:column;align-items:center;flex-shrink:0;position:relative;">
        <div style="position:absolute;top:0;right:0;width:4px;height:100%;background:<?= $acc ?>88;"></div>
        <div style="padding:10% 0 5%;display:flex;flex-direction:column;align-items:center;gap:6%;">
            <?= icardLogoEl($logoPath,'22%') ?>
            <span style="font-size:clamp(0.3rem,0.7vw,0.44rem);color:rgba(255,255,255,0.7);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;text-align:center;writing-mode:vertical-rl;transform:rotate(180deg);"><?= $tplName ?></span>
        </div>
        <div style="width:55%;aspect-ratio:1;border-radius:0;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;margin-top:4%;"><?= icardPhoto($photoPath,'2.2rem') ?></div>
        <div style="margin-top:auto;padding-bottom:6%;width:80%;"><?= icardBarcodeSvg('rgba(255,255,255,0.4)','100%') ?></div>
    </div>
    <div style="flex:1;background:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:6% 7%;min-width:0;">
        <div style="font-size:clamp(0.62rem,1.52vw,0.88rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:#888;margin-top:1.5%;margin-bottom:4%;"><?= $roleVal ?></div><?php endif; ?>
        <div style="width:50%;height:3px;background:<?= $acc ?>;border-radius:0;margin-bottom:5%;"></div>
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#555') ?><?php endforeach; ?>
    </div>
</div>

<?php elseif ($designStyle === 'glass'): ?>
<!-- ── GLASSMORPHISM (landscape) ── gradient bg with frosted glass panel ── -->
<div style="width:100%;height:100%;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?> 100%);position:relative;overflow:hidden;">
    <div style="position:absolute;inset:8% 6%;background:rgba(255,255,255,0.15);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,0.35);border-radius:10px;overflow:hidden;">
        <div style="position:absolute;top:-30%;left:-20%;width:60%;aspect-ratio:1;background:rgba(255,255,255,0.08);border-radius:50%;"></div>
    </div>
    <div style="position:absolute;top:13%;left:12%;display:flex;align-items:center;gap:5%;z-index:2;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:12%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.2);z-index:3;"><?= icardPhoto($photoPath,'2rem') ?></div>
    <div style="position:absolute;left:40%;top:18%;max-width:54%;z-index:2;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 2px 8px rgba(0,0,0,0.2);"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:rgba(255,255,255,0.8);margin-top:2%;margin-bottom:4%;"><?= $roleVal ?></div><?php endif; ?>
        <div style="width:60%;height:1px;background:rgba(255,255,255,0.4);margin-bottom:4%;"></div>
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.7)', 'rgba(255,255,255,0.92)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:7%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg('rgba(255,255,255,0.3)','48%') ?></div>
</div>

<?php elseif ($designStyle === 'zigzag'): ?>
<!-- ── ZIG-ZAG (landscape) ── sawtooth header bottom, photo at boundary ── -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">
    <svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 85.6 54" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0,0 L85.6,0 L85.6,21.6 L77,27 L68.5,21.6 L60,27 L51.4,21.6 L42.8,27 L34.2,21.6 L25.7,27 L17.1,21.6 L8.6,27 L0,21.6 Z" fill="<?= $pri ?>"/>
        <path d="M0,0 L85.6,0 L85.6,21.6 L77,27 L68.5,21.6 L60,27 L51.4,21.6 L42.8,27 L34.2,21.6 L25.7,27 L17.1,21.6 L8.6,27 L0,21.6 Z" fill="rgba(255,255,255,0.06)"/>
    </svg>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;z-index:2;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:44%;transform:translateX(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:<?= $pri ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(0,0,0,0.22);z-index:3;"><?= icardPhoto($photoPath,'2rem') ?></div>
    <div style="position:absolute;top:62%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:72%;left:5%;right:5%;display:grid;grid-template-columns:1fr 1fr;column-gap:3%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:3%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'48%') ?></div>
</div>

<?php elseif ($designStyle === 'ribbon'): ?>
<!-- ── RIBBON (landscape) ── dark bg with diagonal colour ribbon/sash ── -->
<div style="width:100%;height:100%;background:#111827;position:relative;overflow:hidden;">
    <svg style="position:absolute;inset:0;width:100%;height:100%;" viewBox="0 0 85.6 54" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <polygon points="0,15 85.6,8 85.6,27 0,34" fill="<?= $pri ?>" opacity="0.92"/>
        <polygon points="0,18 85.6,11 85.6,31 0,38" fill="<?= $acc ?>" opacity="0.55"/>
    </svg>
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:5%;z-index:2;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <span style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.7);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></span>
    </div>
    <div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.1);overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:3;"><?= icardPhoto($photoPath,'2rem') ?></div>
    <div style="position:absolute;left:33%;top:14%;max-width:60%;z-index:2;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.85vw,0.54rem);color:rgba(255,255,255,0.75);margin-top:2%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;bottom:8%;left:5%;max-width:52%;z-index:2;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.5)', 'rgba(255,255,255,0.88)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:4%;right:5%;width:34%;z-index:2;"><?= icardBarcodeSvg('rgba(255,255,255,0.25)','100%') ?></div>
</div>

<?php /* ══ New Portrait styles ══ */
elseif ($designStyle === 'v_ribbon'): ?>
<!-- ── RIBBON V (portrait) ── top band + accent ribbon, photo at ribbon edge ── -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:20%;background:<?= $pri ?>;"></div>
    <div style="position:absolute;top:22%;left:0;right:0;height:13%;background:<?= $acc ?>;opacity:0.9;"></div>
    <div style="position:absolute;top:35%;left:0;right:0;height:3%;background:<?= $pri ?>22;"></div>
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">
        <?= icardLogoEl($logoPath,'12%') ?>
        <span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.95);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:18%;transform:translateX(-50%);width:26%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:<?= $pri ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.25);z-index:3;"><?= icardPhoto($photoPath,'1.8rem') ?></div>
    <div style="position:absolute;top:42%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:51%;left:10%;right:10%;height:1.5px;background:linear-gradient(90deg,transparent,<?= $acc ?>,transparent);opacity:0.6;"></div>
    <div style="position:absolute;top:53%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444', 'clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_arch'): ?>
<!-- ── ARCH V (portrait) ── arched dome header, photo inside arch ── -->
<div style="width:100%;height:100%;background:#ffffff;position:relative;overflow:hidden;">
    <svg style="position:absolute;top:0;left:0;width:100%;height:62%;" viewBox="0 0 54 54" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 L54,0 L54,40 Q54,54 27,54 Q0,54 0,40 Z" fill="<?= $pri ?>"/>
        <path d="M0,0 L54,0 L54,28 Q54,40 27,40 Q0,40 0,28 Z" fill="rgba(255,255,255,0.08)"/>
    </svg>
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">
        <?= icardLogoEl($logoPath,'12%') ?>
        <span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:18%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.18);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.28);z-index:3;"><?= icardPhoto($photoPath,'1.8rem') ?></div>
    <div style="position:absolute;top:64%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:72%;left:10%;right:10%;height:1px;background:<?= $pri ?>;opacity:0.2;"></div>
    <div style="position:absolute;top:74%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444', 'clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_diamond'): ?>
<!-- ── DIAMOND V (portrait) ── diamond/rhombus photo frame ── -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:50%;background:<?= $pri ?>;overflow:hidden;">
        <svg style="position:absolute;bottom:-2%;left:0;width:100%;height:22%;" viewBox="0 0 54 12" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <polygon points="27,12 54,0 0,0" fill="#f7f8fc"/>
        </svg>
        <svg style="position:absolute;top:5%;right:5%;width:30%;opacity:0.15;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect x="10" y="10" width="80" height="80" rx="4" fill="#fff" transform="rotate(45 50 50)"/>
        </svg>
    </div>
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">
        <?= icardLogoEl($logoPath,'12%') ?>
        <span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:28%;transform:translateX(-50%);width:28%;aspect-ratio:1;z-index:4;">
        <svg style="position:absolute;inset:-18%;width:136%;height:136%;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <polygon points="50,2 98,50 50,98 2,50" fill="#fff" stroke="<?= $pri ?>" stroke-width="2.5"/>
        </svg>
        <div style="position:absolute;inset:0;overflow:hidden;clip-path:polygon(50% 2%,98% 50%,50% 98%,2% 50%);display:flex;align-items:center;justify-content:center;background:<?= $pri ?>20;"><?= icardPhoto($photoPath,'1.8rem') ?></div>
    </div>
    <div style="position:absolute;top:60%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:68%;left:8%;right:8%;height:1.5px;background:<?= $pri ?>;opacity:0.25;"></div>
    <div style="position:absolute;top:70%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444', 'clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_corner'): ?>
<!-- ── CORNER V (portrait) ── triangle corner accents, clean centre ── -->
<div style="width:100%;height:100%;background:#ffffff;position:relative;overflow:hidden;">
    <svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 54 85.6" xmlns="http://www.w3.org/2000/svg">
        <polygon points="0,0 35,0 0,47" fill="<?= $pri ?>"/>
        <polygon points="0,0 22,0 0,30" fill="rgba(255,255,255,0.1)"/>
        <polygon points="54,85.6 19,85.6 54,39" fill="<?= $acc ?>" opacity="0.85"/>
    </svg>
    <div style="position:absolute;top:3%;left:4%;z-index:2;">
        <div style="width:14%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-infinity" style="color:rgba(255,255,255,0.85);font-size:0.35rem;"></i>
        </div>
    </div>
    <div style="position:absolute;top:3%;right:4%;max-width:45%;text-align:right;z-index:2;">
        <div style="font-size:clamp(0.4rem,1vw,0.58rem);color:<?= $pri ?>;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $orgVal ?: $tplName ?></div>
    </div>
    <div style="position:absolute;left:50%;top:22%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:<?= $pri ?>15;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.2);z-index:3;"><?= icardPhoto($photoPath,'1.8rem') ?></div>
    <div style="position:absolute;top:53%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:61%;left:10%;right:10%;height:1.5px;background:linear-gradient(90deg,transparent,<?= $acc ?>,transparent);opacity:0.5;"></div>
    <div style="position:absolute;top:63%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444', 'clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_dual'): ?>
<!-- ── DUAL BAND V (portrait) ── top band + bottom band, white middle with photo ── -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;left:0;right:0;height:20%;background:<?= $pri ?>;"></div>
    <div style="position:absolute;bottom:0;left:0;right:0;height:18%;background:<?= $acc ?>;"></div>
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">
        <?= icardLogoEl($logoPath,'12%') ?>
        <span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:26%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid <?= $pri ?>;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.2);z-index:3;"><?= icardPhoto($photoPath,'1.8rem') ?></div>
    <div style="position:absolute;top:57%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#777;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:65%;left:10%;right:10%;height:1px;background:<?= $pri ?>;opacity:0.2;"></div>
    <div style="position:absolute;top:67%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444', 'clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:20%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_stripe'): ?>
<!-- ── STRIPE V (portrait) ── full gradient bg with frosted glass panel ── -->
<div style="width:100%;height:100%;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?> 100%);position:relative;overflow:hidden;">
    <div style="position:absolute;top:5%;left:5%;right:5%;bottom:5%;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);border-radius:8px;backdrop-filter:blur(3px);"></div>
    <div style="position:absolute;top:8%;left:9%;display:flex;align-items:center;gap:6%;z-index:2;">
        <?= icardLogoEl($logoPath,'12%') ?>
        <span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.9);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:22%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 30px rgba(0,0,0,0.3);z-index:3;"><?= icardPhoto($photoPath,'1.8rem') ?></div>
    <div style="position:absolute;top:56%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-shadow:0 2px 8px rgba(0,0,0,0.2);"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:rgba(255,255,255,0.8);margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:64%;left:10%;right:10%;height:1px;background:rgba(255,255,255,0.4);"></div>
    <div style="position:absolute;top:66%;left:9%;right:9%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.7)', 'rgba(255,255,255,0.92)', 'clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:7%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg('rgba(255,255,255,0.3)','62%') ?></div>
</div>

<?php elseif ($designStyle === 'v_badge'): ?>
<!-- ── BADGE V (portrait) ── shield/badge shape header, photo inside shield ── -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">
    <svg style="position:absolute;top:0;left:0;width:100%;height:100%;" viewBox="0 0 54 85.6" xmlns="http://www.w3.org/2000/svg">
        <path d="M4,0 L50,0 L54,5 L54,36 Q54,48 27,52 Q0,48 0,36 L0,5 Z" fill="<?= $pri ?>"/>
        <path d="M4,0 L50,0 L54,5 L54,25 Q54,36 27,38 Q0,36 0,25 L0,5 Z" fill="rgba(255,255,255,0.08)"/>
        <circle cx="3" cy="3" r="2.5" fill="rgba(255,255,255,0.25)"/>
    </svg>
    <div style="position:absolute;top:3%;left:4%;display:flex;align-items:center;gap:6%;z-index:2;">
        <?= icardLogoEl($logoPath,'12%') ?>
        <span style="font-size:clamp(0.42rem,1vw,0.6rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.05em;text-transform:uppercase;"><?= $orgVal ?: $tplName ?></span>
    </div>
    <div style="position:absolute;left:50%;top:18%;transform:translateX(-50%);width:28%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.85);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(0,0,0,0.28);z-index:3;"><?= icardPhoto($photoPath,'1.8rem') ?></div>
    <div style="position:absolute;top:60%;left:4%;right:4%;text-align:center;">
        <div style="font-size:clamp(0.8rem,2vw,1.05rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.42rem,1vw,0.58rem);color:#888;margin-top:1%;"><?= $roleVal ?></div><?php endif; ?>
    </div>
    <div style="position:absolute;top:68%;left:8%;right:8%;height:1.5px;background:<?= $pri ?>;opacity:0.25;"></div>
    <div style="position:absolute;top:70%;left:6%;right:6%;">
        <?php foreach ($shownFlds as $f): ?><?= icardRow($f['label'], $f['val'], $pri, '#444', 'clamp(0.38rem,0.9vw,0.54rem)') ?><?php endforeach; ?>
    </div>
    <div style="position:absolute;bottom:2%;left:50%;transform:translateX(-50%);"><?= icardBarcodeSvg($pri,'62%') ?></div>
</div>

<?php endif; // end design styles ?>

</div><!-- /.id-card-display -->
<p style="font-size:0.75rem;color:var(--text-secondary);margin-top:14px;text-align:center;">
    <i class="fas fa-info-circle"></i> <?= $isPortrait ? 'Portrait' : 'Landscape' ?> card &mdash; click "Print / Save PDF" to download
</p>
</div><!-- /text-align center -->

<!-- ════════════════════════ DETAILS PANEL ════════════════════════ -->
<div>
    <div class="card" style="margin-bottom:16px;padding:16px;">
        <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:14px;color:var(--indigo);display:flex;align-items:center;gap:6px;">
            <i class="fas fa-info-circle"></i> Card Details
        </h3>
        <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
            <tr>
                <td style="padding:6px 0;color:var(--text-secondary);width:42%;">Template</td>
                <td style="padding:6px 0;font-weight:600;"><?= htmlspecialchars($tplConfig['name'] ?? $card['template_key']) ?></td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:var(--text-secondary);">Orientation</td>
                <td style="padding:6px 0;font-weight:500;text-transform:capitalize;"><?= $isPortrait ? 'Portrait (Vertical)' : 'Landscape (Horizontal)' ?></td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:var(--text-secondary);">Design Style</td>
                <td style="padding:6px 0;font-weight:500;text-transform:capitalize;"><?= htmlspecialchars(str_replace(['v_','_'],' ',$designStyle)) ?></td>
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
        <div style="font-size:0.78rem;color:var(--cyan);padding:8px;background:rgba(0,240,255,0.05);border-radius:8px;"><?= htmlspecialchars($aiSugg['ai_text']) ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

</div><!-- /grid -->
</div><!-- /view-card-wrap -->

<!-- Delete Confirmation Modal -->
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
