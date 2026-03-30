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
$roleKeys = ['designation','title','course','event_name'];
$nameVal  = htmlspecialchars($cd['name'] ?? 'Full Name', ENT_QUOTES, 'UTF-8');
$roleVal  = '';
foreach ($roleKeys as $rk) {
    if (!empty($cd[$rk])) { $roleVal = htmlspecialchars($cd[$rk], ENT_QUOTES, 'UTF-8'); break; }
}

// Build up to 5 visible fields (LABEL : VALUE format, matching reference images)
$fieldLabelMap = [
    'department'  => 'DEPT',
    'employee_id' => 'ID NO',
    'roll_number' => 'ROLL NO',
    'id_number'   => 'ID NO',
    'badge_id'    => 'BADGE',
    'license_no'  => 'LIC NO',
    'blood_group' => 'B.GROUP',
    'phone'       => 'PHONE',
    'email'       => 'E-MAIL',
    'year'        => 'YEAR',
    'organization'=> 'ORG',
    'host_name'   => 'HOST',
    'purpose'     => 'PURPOSE',
    'visit_date'  => 'DATE',
];
$skipKeys  = array_merge(['name'], $roleKeys);
$shownFlds = [];
foreach ($cd as $fKey => $fVal) {
    if (in_array($fKey, $skipKeys) || !$fVal || count($shownFlds) >= 5) continue;
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

// ── Barcode SVG ──────────────────────────────────────────────────────────────
// Pre-computed realistic barcode pattern (bar widths, alternating bar/space)
if (!function_exists('icardBarcodeSvg')) {
function icardBarcodeSvg(string $color, string $width = '52%'): string {
    $bars = [2,1,3,1,1,2,1,3,2,1,1,2,1,1,3,1,2,1,1,3,2,1,2,1,1,3,1,1,2,1,3,1,2,1,1,2,3,1,1];
    $svg  = '<svg viewBox="0 0 80 13" xmlns="http://www.w3.org/2000/svg" style="display:block;width:'.$width.';height:auto;">';
    $x = 0; $isBar = true;
    foreach ($bars as $w) {
        if ($isBar) $svg .= '<rect x="'.round($x,2).'" y="0" width="'.$w.'" height="13" fill="'.$color.'"/>';
        $x += $w + 1;
        $isBar = !$isBar;
    }
    return $svg . '</svg>';
}
}

// ── Photo helper ─────────────────────────────────────────────────────────────
if (!function_exists('icardPhoto')) {
function icardPhoto(string $photoPath, string $sz = '2rem'): string {
    if ($photoPath)
        return '<img src="'.$photoPath.'" style="width:100%;height:100%;object-fit:cover;" alt="Photo">';
    return '<i class="fas fa-user" style="font-size:'.$sz.';opacity:0.55;"></i>';
}
}

// ── LABEL : VALUE field row ──────────────────────────────────────────────────
if (!function_exists('icardRow')) {
function icardRow(string $label, string $value, string $lc, string $vc, string $extra = ''): string {
    return '<div style="display:flex;align-items:baseline;gap:2%;font-size:clamp(0.36rem,0.8vw,0.52rem);white-space:nowrap;overflow:hidden;margin-bottom:1.5%;'.$extra.'">'
        .'<span style="color:'.$lc.';font-weight:700;min-width:28%;letter-spacing:0.04em;">'.$label.'</span>'
        .'<span style="color:'.$vc.';opacity:0.8;">: '.$value.'</span>'
        .'</div>';
}
}

// ── Logo element ─────────────────────────────────────────────────────────────
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
.view-card-wrap { max-width: 960px; margin: 0 auto; }
.id-card-display {
    width: 100%; max-width: 520px; margin: 0 auto;
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 28px 80px rgba(0,0,0,0.5);
    aspect-ratio: 85.6/54;
    position: relative;
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

<!-- ════════════════════════════════════ CARD VISUAL ════════════════════════════════════ -->
<div style="text-align:center;">
<div class="id-card-display" style="font-family:'<?= $font ?>','Poppins',sans-serif;">

<?php if ($designStyle === 'classic'): ?>
<!-- ══════════════════════════════════════════════════════
     STYLE: ANGLED PRO  (Image 1 Card 2 / Image 2 Card 5)
     White card — coloured angled header — centred circle photo
     ══════════════════════════════════════════════════════ -->
<div style="width:100%;height:100%;background:#f7f8fc;position:relative;overflow:hidden;">

    <!-- Angled coloured header (top ~52%) -->
    <div style="position:absolute;top:0;left:0;right:0;height:100%;overflow:hidden;pointer-events:none;">
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg,<?= $pri ?> 0%,<?= $acc ?> 100%);clip-path:polygon(0 0,100% 0,100% 56%,0 72%);"></div>
        <!-- Subtle white strip at diagonal edge -->
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.08);clip-path:polygon(0 70%,100% 54%,100% 59%,0 75%);"></div>
    </div>

    <!-- Logo + company name — top-left of header -->
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <div style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:rgba(255,255,255,0.92);font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></div>
    </div>

    <!-- Infinity badge top-right -->
    <div style="position:absolute;top:4%;right:4%;width:7%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-infinity" style="color:white;font-size:clamp(0.28rem,0.65vw,0.42rem);"></i>
    </div>

    <!-- Circular photo — centred, overlapping header/body boundary -->
    <div style="position:absolute;left:50%;top:32%;transform:translateX(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:3px solid #fff;background:<?= $pri ?>22;overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(0,0,0,0.22);">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>

    <!-- Name + role — centred below photo -->
    <div style="position:absolute;top:58%;left:0;right:0;text-align:center;padding:0 4%;">
        <div style="font-size:clamp(0.62rem,1.55vw,0.9rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:0.02em;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.38rem,0.88vw,0.56rem);color:#888;margin-top:1%;letter-spacing:0.03em;"><?= $roleVal ?></div><?php endif; ?>
    </div>

    <!-- Fields — two-column grid -->
    <div style="position:absolute;top:70%;left:5%;right:5%;display:grid;grid-template-columns:1fr 1fr;column-gap:4%;row-gap:0;">
        <?php foreach ($shownFlds as $f): ?>
        <?= icardRow($f['label'], $f['val'], $pri, '#444') ?>
        <?php endforeach; ?>
    </div>

    <!-- Barcode centred at bottom -->
    <div style="position:absolute;bottom:2.5%;left:50%;transform:translateX(-50%);">
        <?= icardBarcodeSvg($pri, '48%') ?>
    </div>
</div>

<?php elseif ($designStyle === 'sidebar'): ?>
<!-- ══════════════════════════════════════════════════════
     STYLE: DARK GEO  (Image 1 Card 1)
     Dark card — coloured rotated-diamond accent shape — photo inside
     ══════════════════════════════════════════════════════ -->
<div style="width:100%;height:100%;background:#111827;position:relative;overflow:hidden;">

    <!-- Large rotated diamond SVG (upper-right) -->
    <svg style="position:absolute;top:-18%;right:-12%;width:60%;aspect-ratio:1;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <rect x="15" y="15" width="70" height="70" rx="3" fill="<?= $pri ?>" transform="rotate(45 50 50)"/>
    </svg>
    <!-- Second smaller diamond accent -->
    <svg style="position:absolute;top:-8%;right:-5%;width:42%;aspect-ratio:1;opacity:0.35;" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <rect x="15" y="15" width="70" height="70" rx="3" fill="<?= $acc ?>" transform="rotate(45 50 50)"/>
    </svg>

    <!-- Photo circle inside diamond area -->
    <div style="position:absolute;top:6%;right:6%;width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid rgba(255,255,255,0.7);background:rgba(255,255,255,0.12);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,0,0,0.4);">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>

    <!-- Logo + company name — top-left, white -->
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <div style="font-size:clamp(0.35rem,0.78vw,0.5rem);color:rgba(255,255,255,0.85);font-weight:700;letter-spacing:0.07em;text-transform:uppercase;"><?= $tplName ?></div>
    </div>

    <!-- NAME — large white, lower-centre-left -->
    <div style="position:absolute;bottom:36%;left:5%;">
        <div style="font-size:clamp(0.7rem,1.7vw,1rem);font-weight:800;color:#fff;letter-spacing:0.03em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:60vw;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.38rem,0.85vw,0.54rem);color:<?= $acc ?>;margin-top:1.5%;letter-spacing:0.04em;"><?= $roleVal ?></div><?php endif; ?>
    </div>

    <!-- Fields — bottom-left stacked -->
    <div style="position:absolute;bottom:4%;left:5%;right:52%;">
        <?php foreach ($shownFlds as $f): ?>
        <?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.55)', 'rgba(255,255,255,0.85)') ?>
        <?php endforeach; ?>
    </div>

    <!-- Barcode — bottom-right -->
    <div style="position:absolute;bottom:4%;right:4%;width:36%;">
        <?= icardBarcodeSvg('rgba(255,255,255,0.4)','100%') ?>
    </div>
</div>

<?php elseif ($designStyle === 'wave'): ?>
<!-- ══════════════════════════════════════════════════════
     STYLE: WAVE PANEL  (Image 2 Card 1)
     Cream background — dark organic wave left — photo at boundary — fields right
     ══════════════════════════════════════════════════════ -->
<div style="width:100%;height:100%;background:#fdf8f3;position:relative;overflow:hidden;">

    <!-- Organic wave/blob on left -->
    <svg style="position:absolute;top:0;left:0;width:44%;height:100%;" viewBox="0 0 88 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 L60,0 Q80,25 70,55 Q85,80 72,110 Q88,135 65,160 L0,160 Z" fill="<?= $pri ?>"/>
        <!-- Inner lighter wave -->
        <path d="M0,0 L45,0 Q62,22 55,52 Q68,78 56,108 Q70,132 50,160 L0,160 Z" fill="rgba(255,255,255,0.07)"/>
        <!-- Decorative arc highlight -->
        <path d="M0,30 Q30,45 28,80 Q30,115 0,130" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2"/>
    </svg>

    <!-- Photo circle — on wave boundary, left-centre -->
    <div style="position:absolute;left:24%;top:18%;transform:translateX(-50%);width:24%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.2);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 22px rgba(0,0,0,0.25);">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>

    <!-- NAME + role — bottom-left of wave area -->
    <div style="position:absolute;bottom:12%;left:5%;max-width:42%;">
        <div style="font-size:clamp(0.6rem,1.4vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:0.03em;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.35rem,0.78vw,0.5rem);color:rgba(255,255,255,0.75);margin-top:1.5%;letter-spacing:0.04em;"><?= $roleVal ?></div><?php endif; ?>
    </div>

    <!-- Barcode — bottom-left under name -->
    <div style="position:absolute;bottom:3%;left:5%;width:36%;">
        <?= icardBarcodeSvg('rgba(255,255,255,0.45)','100%') ?>
    </div>

    <!-- Logo — top-right -->
    <div style="position:absolute;top:5%;right:5%;display:flex;align-items:center;gap:5%;">
        <?= icardLogoEl($logoPath,'7%', $pri) ?>
        <div style="font-size:clamp(0.32rem,0.72vw,0.46rem);color:<?= $pri ?>;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;"><?= $tplName ?></div>
    </div>

    <!-- Fields — right side, stacked -->
    <div style="position:absolute;top:14%;right:4%;width:48%;">
        <?php foreach ($shownFlds as $f): ?>
        <?= icardRow($f['label'], $f['val'], $pri, '#4a3728') ?>
        <?php endforeach; ?>
    </div>
</div>

<?php elseif ($designStyle === 'bold_header'): ?>
<!-- ══════════════════════════════════════════════════════
     STYLE: BOLD SPLIT  (Image 2 side-panel cards)
     Coloured left panel — white right panel — photo in coloured area
     ══════════════════════════════════════════════════════ -->
<div style="width:100%;height:100%;display:flex;overflow:hidden;position:relative;">

    <!-- Coloured left panel (~40% width) -->
    <div style="width:40%;background:linear-gradient(170deg,<?= $pri ?> 0%,<?= $acc ?> 100%);display:flex;flex-direction:column;align-items:center;position:relative;overflow:hidden;flex-shrink:0;">
        <!-- Decorative circles -->
        <div style="position:absolute;top:-20%;left:-30%;width:90%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
        <div style="position:absolute;bottom:-15%;right:-25%;width:70%;aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
        <!-- Logo top -->
        <div style="padding:10% 0 5%;position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:8%;">
            <?= icardLogoEl($logoPath,'20%') ?>
            <div style="font-size:clamp(0.3rem,0.7vw,0.44rem);color:rgba(255,255,255,0.7);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;text-align:center;"><?= $tplName ?></div>
        </div>
        <!-- Photo circle — centred in panel -->
        <div style="width:45%;aspect-ratio:1;border-radius:50%;border:3px solid rgba(255,255,255,0.8);background:rgba(255,255,255,0.15);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(0,0,0,0.25);position:relative;z-index:1;margin-top:4%;">
            <?= icardPhoto($photoPath,'2.2rem') ?>
        </div>
        <!-- Barcode at bottom of panel -->
        <div style="margin-top:auto;padding-bottom:6%;width:80%;position:relative;z-index:1;">
            <?= icardBarcodeSvg('rgba(255,255,255,0.4)','100%') ?>
        </div>
    </div>

    <!-- White right panel -->
    <div style="flex:1;background:#ffffff;display:flex;flex-direction:column;justify-content:center;padding:6% 7%;min-width:0;position:relative;">
        <!-- Accent top line -->
        <div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,<?= $pri ?>,<?= $acc ?>);"></div>
        <!-- Name -->
        <div style="font-size:clamp(0.62rem,1.52vw,0.88rem);font-weight:800;color:<?= $pri ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:0.02em;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.36rem,0.82vw,0.52rem);color:#888;margin-top:1.5%;letter-spacing:0.04em;margin-bottom:4%;"><?= $roleVal ?></div><?php endif; ?>
        <!-- Divider -->
        <div style="width:60%;height:2px;background:linear-gradient(90deg,<?= $acc ?>,transparent);border-radius:2px;margin-bottom:5%;"></div>
        <!-- Fields -->
        <?php foreach ($shownFlds as $f): ?>
        <?= icardRow($f['label'], $f['val'], $pri, '#555') ?>
        <?php endforeach; ?>
        <!-- Card number -->
        <div style="position:absolute;bottom:4%;right:5%;font-size:clamp(0.3rem,0.68vw,0.44rem);font-family:monospace;color:#bbb;letter-spacing:0.06em;"><?= htmlspecialchars($card['card_number']) ?></div>
    </div>
</div>

<?php elseif ($designStyle === 'diagonal'): ?>
<!-- ══════════════════════════════════════════════════════
     STYLE: TRIANGLE PRO  (Image 2 dark with arrow shapes)
     Dark background — coloured arrow/triangle shapes right — photo+text left
     ══════════════════════════════════════════════════════ -->
<div style="width:100%;height:100%;background:#111827;position:relative;overflow:hidden;">

    <!-- Arrow/chevron triangle shapes — right side -->
    <svg style="position:absolute;right:0;top:0;width:48%;height:100%;" viewBox="0 0 96 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Background right fill -->
        <rect x="40" y="0" width="56" height="160" fill="<?= $pri ?>18"/>
        <!-- Arrow 1 (top) -->
        <polygon points="96,0 96,62 42,31" fill="<?= $pri ?>"/>
        <!-- Arrow 2 (middle) -->
        <polygon points="96,52 96,112 48,82" fill="<?= $acc ?>" opacity="0.85"/>
        <!-- Arrow 3 (bottom) -->
        <polygon points="96,100 96,160 44,130" fill="<?= $pri ?>" opacity="0.7"/>
        <!-- Thin accent lines -->
        <line x1="96" y1="62" x2="96" y2="52" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
        <line x1="96" y1="112" x2="96" y2="100" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
    </svg>

    <!-- Photo circle — left area -->
    <div style="position:absolute;left:5%;top:50%;transform:translateY(-50%);width:22%;aspect-ratio:1;border-radius:50%;border:2.5px solid <?= $acc ?>;background:rgba(255,255,255,0.08);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,0,0,0.4);">
        <?= icardPhoto($photoPath,'2rem') ?>
    </div>

    <!-- Logo top-left -->
    <div style="position:absolute;top:5%;left:5%;display:flex;align-items:center;gap:6%;">
        <?= icardLogoEl($logoPath,'8%') ?>
        <div style="font-size:clamp(0.34rem,0.76vw,0.48rem);color:rgba(255,255,255,0.7);font-weight:700;letter-spacing:0.08em;text-transform:uppercase;"><?= $tplName ?></div>
    </div>

    <!-- Name + role — right of photo, left of arrows -->
    <div style="position:absolute;left:32%;top:20%;max-width:30%;">
        <div style="font-size:clamp(0.58rem,1.38vw,0.82rem);font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $nameVal ?></div>
        <?php if ($roleVal): ?><div style="font-size:clamp(0.34rem,0.76vw,0.5rem);color:<?= $acc ?>;margin-top:2%;letter-spacing:0.04em;"><?= $roleVal ?></div><?php endif; ?>
    </div>

    <!-- Fields — centre-left, stacked -->
    <div style="position:absolute;bottom:6%;left:5%;max-width:50%;">
        <?php foreach ($shownFlds as $f): ?>
        <?= icardRow($f['label'], $f['val'], 'rgba(255,255,255,0.55)', 'rgba(255,255,255,0.9)') ?>
        <?php endforeach; ?>
    </div>

    <!-- Barcode — right of fields, bottom -->
    <div style="position:absolute;bottom:6%;right:52%;left:auto;width:0;overflow:visible;">
        <!-- positioned via flex below -->
    </div>
    <div style="position:absolute;bottom:4%;right:4%;width:36%;">
        <?= icardBarcodeSvg('rgba(255,255,255,0.35)','100%') ?>
    </div>
</div>
<?php endif; // end design style ?>

</div><!-- /.id-card-display -->
<p style="font-size:0.75rem;color:var(--text-secondary);margin-top:14px;text-align:center;">
    <i class="fas fa-info-circle"></i> Click "Print / Save PDF" to download
</p>
</div>

<!-- ════════════════════════ DETAILS PANEL ════════════════════════ -->
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
