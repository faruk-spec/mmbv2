<?php
/**
 * ResumeX - Standalone Resume Preview
 * Renders as a self-contained HTML page (no site layout).
 * ?embed=1  hides the toolbar so the editor iframe shows only the A4 resume.
 *
 * layoutStyle values (set in theme preset):
 *   single       - classic single-column
 *   sidebar-left - coloured left panel + white/dark right panel
 *   sidebar-dark - always-dark left panel
 *   full-header  - wide header band + two-column body
 *   banner       - tall creative header, single-column cards
 *   timeline     - vertical timeline for experience/education
 *   minimal      - ultra-clean typography only
 *   developer    - dark monospace code-inspired
 *   academic     - centred name, ruled sections
 */

$isEmbed    = isset($_GET['embed']);
$isPdf      = isset($_GET['pdf']);       // client-side PDF capture: removes min-height forcing
$isAutoPrint = isset($_GET['autoprint']); // opens print dialog automatically on load

/* ── Theme variables ─────────────────────────────────────────── */
$t       = $themeSettings;
$d       = $resumeData;
$contact = $d['contact']         ?? [];
$hidden  = $d['hidden_sections'] ?? [];
$order   = $d['section_order']   ?? ['contact','summary','experience','education','skills','projects',
           'certifications','awards','volunteer','languages','hobbies','references','publications'];

$primary   = htmlspecialchars($t['primaryColor']    ?? '#00f0ff');
$secondary = htmlspecialchars($t['secondaryColor']  ?? '#9945ff');
$bg        = htmlspecialchars($t['backgroundColor'] ?? '#0a0a0f');
$surface   = htmlspecialchars($t['surfaceColor']    ?? '#12121e');
$text      = htmlspecialchars($t['textColor']       ?? '#e0e6ff');
$muted     = htmlspecialchars($t['textMuted']       ?? '#6b7280');
$border    = htmlspecialchars($t['borderColor']     ?? 'rgba(0,240,255,0.15)');
$font      = htmlspecialchars($t['fontFamily']      ?? 'Poppins');
$fsize     = (int)($t['fontSize']   ?? 14);
$fweight   = (int)($t['fontWeight'] ?? 400);
$headerSt  = $t['headerStyle']  ?? 'gradient';
$cardSt    = $t['cardStyle']    ?? 'glass';
$spacing   = $t['spacing']      ?? 'comfortable';
$layout    = $t['layoutStyle']  ?? 'single';

$pad    = match($spacing){ 'compact'=>'12px','spacious'=>'28px',default=>'18px' };
$secGap = match($spacing){ 'compact'=>'14px','spacious'=>'30px',default=>'20px' };

$cardBg  = match($cardSt){
    'glass'    => 'rgba(255,255,255,0.04)',
    'elevated' => $surface,
    default    => 'transparent',
};
$cardBrd = match($cardSt){
    'glass'    => "border:1px solid {$border};backdrop-filter:blur(4px);",
    'elevated' => 'border:none;box-shadow:0 2px 12px rgba(0,0,0,.25);',
    'bordered' => "border:1px solid {$border};",
    'neon'     => "border:1px solid {$primary};box-shadow:0 0 10px {$primary}44;",
    'flat'     => "border:none;border-bottom:1px solid {$border};border-radius:0!important;",
    default    => "border:1px solid {$border};",
};
$cardR   = ($cardSt === 'flat') ? '0' : '8px';
$cardPad = ($cardSt === 'flat') ? '10px 0' : '11px 13px';

$isDark = (strlen(ltrim($bg,'#')) >= 6)
    && (hexdec(substr(ltrim($bg,'#'),0,2)) < 128);

/* ── Google Fonts ────────────────────────────────────────────── */
$gfMap = [
    'Poppins'          => 'Poppins:wght@400;600;700;800',
    'Inter'            => 'Inter:wght@400;600;700;800',
    'Roboto'           => 'Roboto:wght@400;500;700',
    'Montserrat'       => 'Montserrat:wght@400;600;700;800',
    'Merriweather'     => 'Merriweather:wght@400;700',
    'Playfair Display' => 'Playfair+Display:wght@400;700;800',
    'DM Sans'          => 'DM+Sans:wght@400;600;700',
    'Nunito'           => 'Nunito:wght@400;600;700;800',
    'JetBrains Mono'   => 'JetBrains+Mono:wght@400;700',
    'Bebas Neue'       => 'Bebas+Neue',
];
$gfSlug = $gfMap[$font] ?? null;

/* ── Section labels ──────────────────────────────────────────── */
$labels = [
    'summary'        => 'Professional Summary',
    'experience'     => 'Work Experience',
    'education'      => 'Education',
    'skills'         => 'Skills',
    'projects'       => 'Projects',
    'certifications' => 'Certifications',
    'awards'         => 'Awards &amp; Achievements',
    'volunteer'      => 'Volunteer',
    'languages'      => 'Languages',
    'hobbies'        => 'Interests',
    'references'     => 'References',
    'publications'   => 'Publications',
];

/* ── Helpers ─────────────────────────────────────────────────── */
function rpHe(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function rpHidden(array $h, string $sec): bool { return in_array($sec, $h); }
function rpHas(array $d, string $sec): bool {
    if ($sec === 'contact')  return !empty(array_filter(array_values($d['contact'] ?? [])));
    if ($sec === 'summary')  return !empty(trim($d['summary'] ?? ''));
    if ($sec === 'skills')   return !empty($d['skills'] ?? []);
    if ($sec === 'hobbies')  return !empty($d['hobbies'] ?? []);
    return !empty($d[$sec] ?? []);
}
function rpShow(array $d, array $hidden, string $sec): bool {
    return !rpHidden($hidden, $sec) && rpHas($d, $sec);
}

/* ── Section content renderer (returns HTML) ─────────────────── */
function rpSectionBody(string $sec, array $d,
    string $primary, string $textColor, string $muted, string $border,
    string $cardBg, string $cardBrd, string $cardR, string $cardPad,
    array $labels, string $secondary): string
{
    $h = '';
    switch ($sec) {
        case 'summary':
            $h = '<p style="margin:0;line-height:1.65;color:'.$muted.';font-size:.88em;">'.nl2br(rpHe($d['summary'] ?? '')).'</p>';
            break;
        case 'experience':
            foreach ($d['experience'] ?? [] as $e) {
                $ds = '';
                if (!empty($e['start_date'])) $ds = rpHe($e['start_date']).' &ndash; '.(!empty($e['current']) ? 'Present' : rpHe($e['end_date'] ?? ''));
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start;">';
                $h .= '<div><div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($e['title'] ?? '').'</div>';
                if (!empty($e['company'])) $h .= '<div style="font-size:.82em;font-weight:600;color:'.$primary.';">'.rpHe($e['company']).'</div>';
                if (!empty($e['location'])) $h .= '<div style="font-size:.77em;color:'.$muted.';">'.rpHe($e['location']).'</div>';
                $h .= '</div>';
                if ($ds) $h .= '<div style="font-size:.74em;color:'.$muted.';white-space:nowrap;flex-shrink:0;">'.$ds.'</div>';
                $h .= '</div>';
                if (!empty($e['description'])) $h .= '<div style="font-size:.83em;color:'.$muted.';line-height:1.55;margin-top:6px;">'.nl2br(rpHe($e['description'])).'</div>';
                if (!empty($e['bullets'])) {
                    $h .= '<ul style="margin:6px 0 0;padding-left:0;list-style:none;">';
                    foreach ($e['bullets'] as $b) {
                        if (trim($b)) $h .= '<li style="font-size:.82em;color:'.$muted.';padding:2px 0 2px 14px;position:relative;line-height:1.5;"><span style="position:absolute;left:0;top:8px;width:5px;height:5px;border-radius:50%;background:'.$primary.';display:inline-block;"></span>'.rpHe($b).'</li>';
                    }
                    $h .= '</ul>';
                }
                $h .= '</div>';
            }
            break;
        case 'education':
            foreach ($d['education'] ?? [] as $e) {
                $ds = '';
                if (!empty($e['start_date']) || !empty($e['end_date'])) $ds = rpHe($e['start_date'] ?? '').' &ndash; '.rpHe($e['end_date'] ?? '');
                $df = trim(($e['degree'] ?? '').(!empty($e['field']) ? ', '.$e['field'] : ''));
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start;">';
                $h .= '<div><div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($e['school'] ?? '').'</div>';
                if ($df) $h .= '<div style="font-size:.82em;font-weight:600;color:'.$primary.';">'.rpHe($df).'</div>';
                if (!empty($e['location'])) $h .= '<div style="font-size:.77em;color:'.$muted.';">'.rpHe($e['location']).'</div>';
                $h .= '</div>';
                if ($ds) $h .= '<div style="font-size:.74em;color:'.$muted.';white-space:nowrap;flex-shrink:0;">'.$ds.'</div>';
                $h .= '</div>';
                if (!empty($e['gpa'])) $h .= '<div style="font-size:.82em;color:'.$muted.';margin-top:4px;">GPA: '.rpHe($e['gpa']).'</div>';
                if (!empty($e['description'])) $h .= '<div style="font-size:.83em;color:'.$muted.';line-height:1.55;margin-top:6px;">'.nl2br(rpHe($e['description'])).'</div>';
                $h .= '</div>';
            }
            break;
        case 'skills':
            $h .= '<div style="display:flex;flex-wrap:wrap;gap:6px;">';
            foreach ($d['skills'] ?? [] as $s) {
                $n = is_string($s) ? $s : ($s['name'] ?? '');
                if ($n) $h .= '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:.78em;font-weight:600;background:'.$primary.'18;border:1px solid '.$primary.'44;color:'.$primary.';">'.rpHe($n).'</span>';
            }
            $h .= '</div>';
            break;
        case 'projects':
            foreach ($d['projects'] ?? [] as $p) {
                $techs = '';
                if (!empty($p['technologies'])) $techs = is_array($p['technologies']) ? implode(', ', $p['technologies']) : $p['technologies'];
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start;">';
                $h .= '<div><div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($p['name'] ?? '').'</div>';
                if ($techs) $h .= '<div style="font-size:.78em;font-weight:600;color:'.$primary.';">'.rpHe($techs).'</div>';
                $h .= '</div>';
                if (!empty($p['url'])) $h .= '<div style="font-size:.75em;white-space:nowrap;"><a href="'.rpHe($p['url']).'" target="_blank" rel="noopener" style="color:'.$primary.';">View &rarr;</a></div>';
                $h .= '</div>';
                if (!empty($p['description'])) $h .= '<div style="font-size:.83em;color:'.$muted.';line-height:1.55;margin-top:6px;">'.nl2br(rpHe($p['description'])).'</div>';
                $h .= '</div>';
            }
            break;
        case 'certifications':
            foreach ($d['certifications'] ?? [] as $c) {
                $ds = rpHe($c['date'] ?? '');
                if (!empty($c['expiry'])) $ds .= ' &ndash; '.rpHe($c['expiry']);
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start;">';
                $h .= '<div><div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($c['name'] ?? '').'</div>';
                if (!empty($c['issuer'])) $h .= '<div style="font-size:.82em;font-weight:600;color:'.$primary.';">'.rpHe($c['issuer']).'</div>';
                $h .= '</div>';
                if ($ds) $h .= '<div style="font-size:.74em;color:'.$muted.';white-space:nowrap;flex-shrink:0;">'.$ds.'</div>';
                $h .= '</div>';
                if (!empty($c['url'])) $h .= '<div style="font-size:.83em;margin-top:4px;"><a href="'.rpHe($c['url']).'" target="_blank" rel="noopener" style="color:'.$primary.';">View Credential &rarr;</a></div>';
                $h .= '</div>';
            }
            break;
        case 'awards':
            foreach ($d['awards'] ?? [] as $a) {
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="display:flex;justify-content:space-between;gap:8px;"><div>';
                $h .= '<div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($a['title'] ?? '').'</div>';
                if (!empty($a['issuer'])) $h .= '<div style="font-size:.82em;font-weight:600;color:'.$primary.';">'.rpHe($a['issuer']).'</div>';
                $h .= '</div>';
                if (!empty($a['date'])) $h .= '<div style="font-size:.74em;color:'.$muted.';white-space:nowrap;flex-shrink:0;">'.rpHe($a['date']).'</div>';
                $h .= '</div>';
                if (!empty($a['description'])) $h .= '<div style="font-size:.83em;color:'.$muted.';line-height:1.55;margin-top:6px;">'.rpHe($a['description']).'</div>';
                $h .= '</div>';
            }
            break;
        case 'volunteer':
            foreach ($d['volunteer'] ?? [] as $v) {
                $ds = '';
                if (!empty($v['start_date'])) $ds = rpHe($v['start_date']).' &ndash; '.rpHe($v['end_date'] ?? 'Present');
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="display:flex;justify-content:space-between;gap:8px;"><div>';
                $h .= '<div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($v['role'] ?? '').'</div>';
                if (!empty($v['organization'])) $h .= '<div style="font-size:.82em;font-weight:600;color:'.$primary.';">'.rpHe($v['organization']).'</div>';
                $h .= '</div>';
                if ($ds) $h .= '<div style="font-size:.74em;color:'.$muted.';white-space:nowrap;">'.$ds.'</div>';
                $h .= '</div>';
                if (!empty($v['description'])) $h .= '<div style="font-size:.83em;color:'.$muted.';line-height:1.55;margin-top:6px;">'.nl2br(rpHe($v['description'])).'</div>';
                $h .= '</div>';
            }
            break;
        case 'languages':
            foreach ($d['languages'] ?? [] as $l) {
                $h .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;font-size:.85em;">';
                $h .= '<span style="font-weight:600;color:'.$textColor.';">'.rpHe($l['language'] ?? '').'</span>';
                $h .= '<span style="color:'.$muted.';font-size:.9em;">'.rpHe($l['level'] ?? '').'</span>';
                $h .= '</div>';
            }
            break;
        case 'hobbies':
            $h .= '<div style="display:flex;flex-wrap:wrap;gap:6px;">';
            foreach ($d['hobbies'] ?? [] as $hb) {
                $h .= '<span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:.78em;font-weight:500;background:'.$secondary.'15;border:1px solid '.$secondary.'35;color:'.$secondary.';">'.rpHe($hb).'</span>';
            }
            $h .= '</div>';
            break;
        case 'references':
            foreach ($d['references'] ?? [] as $r) {
                $rc = trim(($r['title'] ?? '').(!empty($r['company']) ? ', '.$r['company'] : ''));
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($r['name'] ?? '').'</div>';
                if ($rc) $h .= '<div style="font-size:.82em;font-weight:600;color:'.$primary.';">'.rpHe($rc).'</div>';
                if (!empty($r['email'])) $h .= '<div style="font-size:.82em;color:'.$muted.';"><a href="mailto:'.rpHe($r['email']).'" style="color:'.$primary.';">'.rpHe($r['email']).'</a></div>';
                if (!empty($r['phone'])) $h .= '<div style="font-size:.82em;color:'.$muted.';">'.rpHe($r['phone']).'</div>';
                $h .= '</div>';
            }
            break;
        case 'publications':
            foreach ($d['publications'] ?? [] as $p) {
                $h .= '<div style="background:'.$cardBg.';'.$cardBrd.'border-radius:'.$cardR.';padding:'.$cardPad.';margin-bottom:10px;">';
                $h .= '<div style="display:flex;justify-content:space-between;gap:8px;">';
                $h .= '<div><div style="font-size:.91em;font-weight:700;color:'.$textColor.';">'.rpHe($p['title'] ?? '').'</div>';
                if (!empty($p['authors'])) $h .= '<div style="font-size:.82em;font-weight:600;color:'.$primary.';">'.rpHe($p['authors']).'</div>';
                if (!empty($p['journal'])) $h .= '<div style="font-size:.8em;color:'.$muted.';">'.rpHe($p['journal']).'</div>';
                $h .= '</div>';
                if (!empty($p['date'])) $h .= '<div style="font-size:.74em;color:'.$muted.';white-space:nowrap;">'.rpHe($p['date']).'</div>';
                $h .= '</div>';
                if (!empty($p['url'])) $h .= '<div style="font-size:.83em;margin-top:4px;"><a href="'.rpHe($p['url']).'" target="_blank" rel="noopener" style="color:'.$primary.';">View &rarr;</a></div>';
                $h .= '</div>';
            }
            break;
    }
    return $h;
}

/* ── Titled section wrapper ──────────────────────────────────── */
function rpSec(string $sec, array $d, array $hidden,
    string $primary, string $textColor, string $muted, string $border,
    string $cardBg, string $cardBrd, string $cardR, string $cardPad,
    array $labels, string $secondary, string $secGap,
    string $titleStyle = 'default'): string
{
    if (!rpShow($d, $hidden, $sec)) return '';
    $label = $labels[$sec] ?? ucfirst($sec);
    $body  = rpSectionBody($sec, $d, $primary, $textColor, $muted, $border, $cardBg, $cardBrd, $cardR, $cardPad, $labels, $secondary);
    if (!$body) return '';

    $titleHtml = match($titleStyle) {
        'minimal'   => '<p style="font-size:.68em;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:'.$primary.';margin:0 0 10px;">'.$label.'</p>',
        'underline' => '<p style="font-size:.8em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:'.$primary.';margin:0 0 10px;padding-bottom:5px;border-bottom:2px solid '.$primary.';">'.$label.'</p>',
        'ruled'     => '<p style="font-size:.72em;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;color:'.$primary.';margin:0 0 10px;padding-bottom:5px;border-bottom:1px solid '.$primary.'55;">'.$label.'</p>',
        default     => '<p style="font-size:.7em;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:'.$primary.';margin:0 0 10px;padding-bottom:5px;border-bottom:1px solid '.$primary.'44;">'.$label.'</p>',
    };
    return '<div style="margin-bottom:'.$secGap.';">'.$titleHtml.$body.'</div>';
}

/* ── Contact row helper ──────────────────────────────────────── */
function rpContactRow(array $contact, string $muted, string $primary): string {
    $out = '';
    foreach ([['email','mailto:','&#9993;'],['phone','tel:','&#9990;'],['location',false,'&#9790;'],
              ['website',false,'&#127758;'],['linkedin',false,'in'],['github',false,'&#9095;']] as [$f,$s,$icon]) {
        if (empty($contact[$f])) continue;
        $v = rpHe($contact[$f]);
        $out .= '<span style="display:inline-flex;align-items:center;gap:3px;">'
            .'<span style="color:'.$primary.';">'.$icon.'</span>'.$v.'</span>';
    }
    return $out;
}

/* ── Photo helper ────────────────────────────────────────────── */
function rpPhoto(string $photo, string $primary, string $sz = '90px', string $shape = 'circle'): string {
    if (!$photo) return '';
    $r = ($shape === 'circle') ? '50%' : '8px';
    return '<img src="'.rpHe($photo).'" alt="" style="width:'.$sz.';height:'.$sz.';border-radius:'.$r.';object-fit:cover;border:3px solid '.$primary.';display:block;flex-shrink:0;">';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= rpHe($resume['title'] ?? 'Resume') ?></title>
<?php if ($gfSlug): ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=<?= rawurlencode($gfSlug) ?>&display=swap" rel="stylesheet">
<?php endif; ?>
<style>
*,*::before,*::after{box-sizing:border-box;}
html,body{margin:0;padding:0;background:<?= $isEmbed ? $bg : '#e5e7eb' ?>;font-family:'<?= $font ?>',system-ui,sans-serif;}
a{color:<?= $primary ?>;text-decoration:none;}
<?php if (!$isEmbed): ?>
.rp-toolbar{position:sticky;top:0;z-index:100;display:flex;align-items:center;gap:10px;padding:8px 16px;background:rgba(10,10,20,.93);backdrop-filter:blur(8px);border-bottom:1px solid rgba(255,255,255,.08);flex-wrap:wrap;}
.rp-tbtn{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:6px;font-size:.76rem;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;text-decoration:none;border:1px solid rgba(255,255,255,.15);background:transparent;color:#cbd5e1;transition:all .18s;}
.rp-tbtn:hover{color:#00f0ff;border-color:rgba(0,240,255,.35);text-decoration:none;}
.rp-tbtn.primary{background:linear-gradient(135deg,#00f0ff,#9945ff);border-color:transparent;color:#06060a;}
.rp-tbtn.primary:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(0,240,255,.35);color:#06060a;}
.rp-spacer{flex:1;}
.rp-meta{font-size:.73rem;color:#64748b;}
.rp-wrap{padding:24px 16px 60px;display:flex;justify-content:center;}
<?php else: ?>
.rp-wrap{padding:0;}
<?php endif; ?>
.rp-a4{
  width:794px;
  <?= $isPdf ? 'min-height:auto;' : 'min-height:1123px;' ?>
  background:<?= $bg ?>;
  font-family:'<?= $font ?>',system-ui,sans-serif;
  font-size:<?= $fsize ?>px;
  font-weight:<?= $fweight ?>;
  color:<?= $text ?>;
  overflow:<?= $isPdf ? 'visible' : 'hidden' ?>;
  <?= (!$isEmbed) ? 'box-shadow:0 8px 48px rgba(0,0,0,.35);' : '' ?>
}
@media print{html,body{background:#fff;}.rp-toolbar{display:none!important;}.rp-wrap{padding:0;display:block;}.rp-a4{width:210mm;min-height:297mm;box-shadow:none;margin:0;}@page{size:A4;margin:.5in;}}
</style>
</head>
<body>
<?php if (!$isEmbed): ?>
<div class="rp-toolbar">
  <a href="/projects/resumex" class="rp-tbtn">&#8592; Dashboard</a>
  <a href="/projects/resumex/edit/<?= (int)$resume['id'] ?>" class="rp-tbtn">&#9998; Edit</a>
  <div class="rp-spacer"></div>
  <span class="rp-meta"><?= rpHe($resume['title'] ?? 'Resume') ?> &middot; <?= rpHe($t['name'] ?? 'Theme') ?></span>
  <a href="/projects/resumex/download/<?= (int)$resume['id'] ?>" id="btnDownloadPdf" class="rp-tbtn" onclick="downloadPreviewPdf(event)">&#8659; Download PDF</a>
  <button onclick="window.print()" class="rp-tbtn primary">&#9113; Print</button>
</div>
<?php endif; ?>
<div class="rp-wrap">
<div class="rp-a4">
<?php
/* ══════════════════════════════════════════════════════════════
   LAYOUT DISPATCHER
   ══════════════════════════════════════════════════════════════ */
$name     = rpHe($contact['name']                     ?? '');
$jobTitle = rpHe($contact['job_title'] ?? $contact['title'] ?? '');
$photo    = $contact['photo'] ?? '';

// Closure shortcut for titled section
$sec = function(string $s, string $ts = 'default') use (
    $d, $hidden, $primary, $secondary, $text, $muted, $border,
    $cardBg, $cardBrd, $cardR, $cardPad, $labels, $secGap
): string {
    return rpSec($s,$d,$hidden,$primary,$text,$muted,$border,$cardBg,$cardBrd,$cardR,$cardPad,$labels,$secondary,$secGap,$ts);
};

$sidebarSecs = ['skills','languages','hobbies','certifications','awards','references'];
$mainSecs    = ['summary','experience','education','projects','volunteer','publications'];

switch ($layout) {

/* ── sidebar-left ──────────────────────────────────────────── */
case 'sidebar-left':
    $sbBg   = $isDark ? $surface : $primary;
    $sbText = $isDark ? $text    : '#ffffff';
    $sbMuted= $isDark ? $muted   : 'rgba(255,255,255,0.75)';
    $sbBrd  = $isDark ? $border  : 'rgba(255,255,255,0.2)';
    $acBrd  = $isDark ? $primary : 'rgba(255,255,255,0.5)';
    ?>
    <div style="display:flex;min-height:1123px;">
      <div style="width:265px;flex-shrink:0;background:<?= $sbBg ?>;padding:28px 18px;display:flex;flex-direction:column;gap:18px;">
        <?php if ($photo): ?><div style="text-align:center;"><img src="<?= rpHe($photo) ?>" alt="" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid <?= $acBrd ?>;"></div><?php endif; ?>
        <div style="text-align:center;">
          <?php if ($name): ?><div style="font-size:1.1em;font-weight:800;color:<?= $sbText ?>;line-height:1.2;"><?= $name ?></div><?php endif; ?>
          <?php if ($jobTitle): ?><div style="font-size:.8em;font-weight:600;color:<?= $acBrd ?>;margin-top:4px;"><?= $jobTitle ?></div><?php endif; ?>
        </div>
        <?php
        $cFlds=[['email','&#9993;'],['phone','&#9990;'],['location','&#9790;'],['website','&#127758;'],['linkedin','in'],['github','&#9095;']];
        $cHtml='';
        foreach($cFlds as[$f,$icon]){
            if(empty($contact[$f]))continue;
            $cHtml.='<div style="display:flex;align-items:center;gap:5px;font-size:.77em;color:'.$sbMuted.';margin-bottom:4px;word-break:break-word;">'
                .'<span style="color:'.$acBrd.';width:14px;text-align:center;flex-shrink:0;">'.$icon.'</span>'
                .rpHe($contact[$f]).'</div>';
        }
        if($cHtml):?>
        <div><p style="font-size:.62em;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:<?= $acBrd ?>;margin:0 0 7px;opacity:.7;">Contact</p><?= $cHtml ?></div>
        <?php endif;
        foreach($sidebarSecs as $s):
            if(!rpShow($d,$hidden,$s))continue;
            $lbl=$labels[$s]??ucfirst($s);
            $body=rpSectionBody($s,$d,$isDark?$primary:$sbText,$sbText,$sbMuted,$sbBrd,$isDark?$cardBg:'rgba(255,255,255,.08)',$isDark?$cardBrd:'border:none;',$cardR,$cardPad,$labels,$isDark?$secondary:$sbMuted);
            if(!$body)continue;
        ?><div><p style="font-size:.62em;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:<?= $acBrd ?>;margin:0 0 7px;opacity:.7;"><?= $lbl ?></p><?= $body ?></div><?php endforeach; ?>
      </div>
      <div style="flex:1;min-width:0;padding:26px 20px;background:<?= $bg ?>;">
        <?php foreach($mainSecs as $s): echo $sec($s); endforeach; ?>
      </div>
    </div>
    <?php break;

/* ── sidebar-dark ──────────────────────────────────────────── */
case 'sidebar-dark': ?>
    <div style="display:flex;min-height:1123px;">
      <div style="width:255px;flex-shrink:0;background:<?= $surface ?>;padding:26px 18px;display:flex;flex-direction:column;gap:18px;border-right:1px solid <?= $border ?>;">
        <?php if($photo):?><div style="text-align:center;"><img src="<?= rpHe($photo) ?>" alt="" style="width:92px;height:92px;border-radius:50%;object-fit:cover;border:3px solid <?= $primary ?>;"></div><?php endif;?>
        <div style="text-align:center;">
          <?php if($name):?><div style="font-size:1.1em;font-weight:800;color:<?= $text ?>;line-height:1.2;"><?= $name ?></div><?php endif;?>
          <?php if($jobTitle):?><div style="font-size:.8em;font-weight:600;color:<?= $primary ?>;margin-top:4px;"><?= $jobTitle ?></div><?php endif;?>
        </div>
        <?php
        foreach([['email','&#9993;'],['phone','&#9990;'],['location','&#9790;'],['website','&#127758;'],['linkedin','in'],['github','&#9095;']] as[$f,$icon]){
            if(empty($contact[$f]))continue;
            echo '<div style="display:flex;align-items:center;gap:5px;font-size:.77em;color:'.$muted.';margin-bottom:4px;word-break:break-word;">'
                .'<span style="color:'.$primary.';width:14px;text-align:center;flex-shrink:0;">'.$icon.'</span>'
                .rpHe($contact[$f]).'</div>';
        }
        foreach($sidebarSecs as $s):
            if(!rpShow($d,$hidden,$s))continue;
            $lbl=$labels[$s]??ucfirst($s);
            $body=rpSectionBody($s,$d,$primary,$text,$muted,$border,$cardBg,$cardBrd,$cardR,$cardPad,$labels,$secondary);
            if(!$body)continue;
        ?><div><p style="font-size:.62em;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:<?= $primary ?>;margin:0 0 7px;opacity:.8;"><?= $lbl ?></p><?= $body ?></div><?php endforeach;?>
      </div>
      <div style="flex:1;min-width:0;padding:26px 20px;background:<?= $bg ?>;">
        <?php foreach($mainSecs as $s): echo $sec($s); endforeach;?>
      </div>
    </div>
    <?php break;

/* ── full-header ───────────────────────────────────────────── */
case 'full-header':
    $hBg = ($headerSt==='solid') ? $surface : "linear-gradient(135deg,{$primary}22,{$secondary}22)";
    ?>
    <div style="background:<?= $hBg ?>;border-bottom:3px solid <?= $primary ?>;padding:<?= $pad ?> 26px;">
      <div style="display:flex;align-items:center;gap:18px;">
        <?php if($photo):?><img src="<?= rpHe($photo) ?>" alt="" style="width:82px;height:82px;border-radius:50%;object-fit:cover;border:3px solid <?= $primary ?>;"><?php endif;?>
        <div style="flex:1;min-width:0;">
          <?php if($name):?><div style="font-size:2em;font-weight:800;color:<?= $primary ?>;line-height:1.1;"><?= $name ?></div><?php endif;?>
          <?php if($jobTitle):?><div style="font-size:.92em;font-weight:600;color:<?= $text ?>;margin-top:3px;"><?= $jobTitle ?></div><?php endif;?>
          <div style="display:flex;flex-wrap:wrap;gap:4px 13px;margin-top:8px;font-size:.77em;color:<?= $muted ?>;"><?= rpContactRow($contact,$muted,$primary) ?></div>
        </div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:2fr 3fr;min-height:calc(1123px - 120px);">
      <div style="padding:20px 16px;border-right:1px solid <?= $border ?>;"><?php foreach(['skills','languages','hobbies','certifications','awards'] as $s): echo $sec($s); endforeach;?></div>
      <div style="padding:20px 18px;"><?php foreach(['summary','experience','education','projects','volunteer','references','publications'] as $s): echo $sec($s); endforeach;?></div>
    </div>
    <?php break;

/* ── banner ────────────────────────────────────────────────── */
case 'banner': ?>
    <div style="background:linear-gradient(135deg,<?= $primary ?>33,<?= $secondary ?>22);border-bottom:3px solid <?= $primary ?>;padding:30px 28px 22px;position:relative;overflow:hidden;">
      <div style="position:absolute;right:-50px;top:-50px;width:200px;height:200px;border-radius:50%;background:<?= $primary ?>12;pointer-events:none;"></div>
      <div style="display:flex;align-items:center;justify-content:space-between;gap:18px;position:relative;">
        <div>
          <?php if($name):?><div style="font-size:2.3em;font-weight:800;color:<?= $primary ?>;line-height:1.05;letter-spacing:-1px;"><?= $name ?></div><?php endif;?>
          <?php if($jobTitle):?><div style="font-size:.95em;font-weight:600;color:<?= $text ?>;margin-top:4px;"><?= $jobTitle ?></div><?php endif;?>
          <?php if(!empty($d['summary'])): $sumSnip=mb_strlen($d['summary'])>180?rtrim(mb_substr($d['summary'],0,strrpos(mb_substr($d['summary'],0,180),' '))).'&hellip;':rpHe($d['summary']);?><div style="font-size:.81em;color:<?= $muted ?>;line-height:1.55;margin-top:9px;max-width:400px;"><?= $sumSnip ?></div><?php endif;?>
        </div>
        <?php if($photo):?><img src="<?= rpHe($photo) ?>" alt="" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:4px solid <?= $primary ?>;flex-shrink:0;"><?php endif;?>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:13px;">
        <?php foreach([['email','&#9993;'],['phone','&#9990;'],['location','&#9790;'],['website','&#127758;'],['linkedin','in'],['github','&#9095;']] as[$f,$icon]):if(empty($contact[$f]))continue;?><span style="display:inline-flex;align-items:center;gap:4px;background:<?= $primary ?>18;border:1px solid <?= $primary ?>44;border-radius:20px;padding:3px 10px;font-size:.74em;color:<?= $text ?>;"><span style="color:<?= $primary ?>;"><?= $icon ?></span><?= rpHe($contact[$f]) ?></span><?php endforeach;?>
      </div>
    </div>
    <?php if(rpShow($d,$hidden,'skills')):?>
    <div style="background:<?= $surface ?>;border-bottom:1px solid <?= $border ?>;padding:10px 26px;display:flex;flex-wrap:wrap;gap:5px;">
      <?php foreach($d['skills']??[]as $s):$n=is_string($s)?$s:($s['name']??'');if(!$n)continue;?><span style="display:inline-block;padding:2px 9px;border-radius:20px;font-size:.75em;font-weight:600;background:<?= $primary ?>18;border:1px solid <?= $primary ?>44;color:<?= $primary ?>;"><?= rpHe($n) ?></span><?php endforeach;?>
    </div>
    <?php endif;?>
    <div style="padding:20px 28px;">
      <?php foreach(['experience','education','projects','certifications','awards','languages','hobbies','volunteer','references','publications'] as $s): echo $sec($s); endforeach;?>
    </div>
    <?php break;

/* ── timeline ──────────────────────────────────────────────── */
case 'timeline': ?>
    <div style="background:<?= $surface ?>;border-bottom:2px solid <?= $primary ?>;padding:<?= $pad ?> 26px;">
      <div style="display:flex;align-items:center;gap:16px;">
        <?php if($photo):?><img src="<?= rpHe($photo) ?>" alt="" style="width:78px;height:78px;border-radius:50%;object-fit:cover;border:3px solid <?= $primary ?>;"><?php endif;?>
        <div>
          <?php if($name):?><div style="font-size:1.9em;font-weight:800;color:<?= $primary ?>;line-height:1.1;"><?= $name ?></div><?php endif;?>
          <?php if($jobTitle):?><div style="font-size:.88em;font-weight:600;color:<?= $text ?>;margin-top:3px;"><?= $jobTitle ?></div><?php endif;?>
          <div style="display:flex;flex-wrap:wrap;gap:4px 12px;margin-top:7px;font-size:.76em;color:<?= $muted ?>;"><?= rpContactRow($contact,$muted,$primary) ?></div>
        </div>
      </div>
    </div>
    <div style="padding:20px 26px;">
      <?= $sec('summary') ?><?= $sec('skills') ?>
      <?php if(rpShow($d,$hidden,'experience')&&!empty($d['experience'])):?>
      <div style="margin-bottom:<?= $secGap ?>;">
        <p style="font-size:.7em;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:<?= $primary ?>;margin:0 0 14px;padding-bottom:5px;border-bottom:1px solid <?= $primary ?>44;">Work Experience</p>
        <div style="position:relative;padding-left:26px;">
          <div style="position:absolute;left:8px;top:4px;bottom:0;width:2px;background:<?= $primary ?>33;"></div>
          <?php foreach($d['experience']as $e):$ds='';if(!empty($e['start_date']))$ds=rpHe($e['start_date']).' &ndash; '.(!empty($e['current'])?'Present':rpHe($e['end_date']??''));?>
          <div style="position:relative;margin-bottom:16px;">
            <div style="position:absolute;left:-22px;top:4px;width:11px;height:11px;border-radius:50%;background:<?= $primary ?>;border:2px solid <?= $bg ?>;"></div>
            <div style="font-size:.9em;font-weight:700;color:<?= $text ?>;"><?= rpHe($e['title']??'') ?></div>
            <div style="font-size:.8em;font-weight:600;color:<?= $primary ?>;"><?= rpHe($e['company']??'') ?></div>
            <?php if($ds):?><div style="font-size:.72em;color:<?= $muted ?>;margin-top:1px;"><?= $ds ?></div><?php endif;?>
            <?php if(!empty($e['description'])):?><div style="font-size:.82em;color:<?= $muted ?>;line-height:1.5;margin-top:5px;"><?= nl2br(rpHe($e['description'])) ?></div><?php endif;?>
          </div>
          <?php endforeach;?>
        </div>
      </div>
      <?php endif;?>
      <?php if(rpShow($d,$hidden,'education')&&!empty($d['education'])):?>
      <div style="margin-bottom:<?= $secGap ?>;">
        <p style="font-size:.7em;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:<?= $secondary ?>;margin:0 0 14px;padding-bottom:5px;border-bottom:1px solid <?= $secondary ?>44;">Education</p>
        <div style="position:relative;padding-left:26px;">
          <div style="position:absolute;left:8px;top:4px;bottom:0;width:2px;background:<?= $secondary ?>33;"></div>
          <?php foreach($d['education']as $e):$df=trim(($e['degree']??'').(!empty($e['field'])?', '.$e['field']:''));$ds='';if(!empty($e['start_date'])||!empty($e['end_date']))$ds=rpHe($e['start_date']??'').' &ndash; '.rpHe($e['end_date']??'');?>
          <div style="position:relative;margin-bottom:16px;">
            <div style="position:absolute;left:-22px;top:4px;width:11px;height:11px;border-radius:50%;background:<?= $secondary ?>;border:2px solid <?= $bg ?>;"></div>
            <div style="font-size:.9em;font-weight:700;color:<?= $text ?>;"><?= rpHe($e['school']??'') ?></div>
            <?php if($df):?><div style="font-size:.8em;font-weight:600;color:<?= $secondary ?>;"><?= rpHe($df) ?></div><?php endif;?>
            <?php if($ds):?><div style="font-size:.72em;color:<?= $muted ?>;margin-top:1px;"><?= $ds ?></div><?php endif;?>
          </div>
          <?php endforeach;?>
        </div>
      </div>
      <?php endif;?>
      <?php foreach(['projects','certifications','awards','languages','hobbies','volunteer','references','publications'] as $s): echo $sec($s); endforeach;?>
    </div>
    <?php break;

/* ── minimal ───────────────────────────────────────────────── */
case 'minimal': ?>
    <div style="padding:30px 32px 20px;border-bottom:2px solid <?= $primary ?>;">
      <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div>
          <?php if($name):?><div style="font-size:2.3em;font-weight:800;color:<?= $primary ?>;letter-spacing:-1px;line-height:1;"><?= $name ?></div><?php endif;?>
          <?php if($jobTitle):?><div style="font-size:.92em;font-weight:400;color:<?= $text ?>;margin-top:4px;letter-spacing:.4px;"><?= $jobTitle ?></div><?php endif;?>
        </div>
        <?php if($photo):?><img src="<?= rpHe($photo) ?>" alt="" style="width:68px;height:68px;border-radius:8px;object-fit:cover;"><?php endif;?>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:4px 14px;margin-top:10px;font-size:.75em;color:<?= $muted ?>;"><?= rpContactRow($contact,$muted,$primary) ?></div>
    </div>
    <div style="padding:20px 32px;"><?php foreach($order as $s){if($s==='contact')continue; echo $sec($s,'minimal');} ?></div>
    <?php break;

/* ── developer ─────────────────────────────────────────────── */
case 'developer': ?>
    <div style="background:<?= $surface ?>;border-bottom:1px solid <?= $primary ?>44;padding:22px 26px;">
      <div style="font-size:1.6em;font-weight:700;color:<?= $primary ?>;letter-spacing:-1px;line-height:1.1;"><span style="opacity:.4;font-size:.55em;">$ </span><?= $name ?></div>
      <?php if($jobTitle):?><div style="font-size:.83em;color:<?= $muted ?>;margin-top:3px;"><span style="opacity:.4;">// </span><?= $jobTitle ?></div><?php endif;?>
      <div style="display:flex;flex-wrap:wrap;gap:4px 12px;margin-top:9px;font-size:.74em;color:<?= $muted ?>;">
        <?php foreach([['email','&#128231;'],['phone','&#128241;'],['location','&#128205;'],['website','&#127760;'],['linkedin','&#128188;'],['github','&#128279;']]as[$f,$icon]):if(empty($contact[$f]))continue;?><span style="display:inline-flex;align-items:center;gap:3px;"><?= $icon ?> <?= rpHe($contact[$f]) ?></span><?php endforeach;?>
      </div>
    </div>
    <div style="padding:20px 26px;">
      <?php if(rpShow($d,$hidden,'summary')&&!empty($d['summary'])):?>
      <div style="margin-bottom:<?= $secGap ?>;"><p style="font-size:.7em;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:<?= $primary ?>;margin:0 0 8px;">/* About */</p><p style="font-size:.85em;color:<?= $muted ?>;line-height:1.65;border-left:2px solid <?= $primary ?>;padding-left:12px;margin:0;"><?= nl2br(rpHe($d['summary']??'')) ?></p></div>
      <?php endif;?>
      <?php foreach(['skills','experience','education','projects','certifications','awards','languages','hobbies','references','publications'] as $s): echo $sec($s); endforeach;?>
    </div>
    <?php break;

/* ── academic ──────────────────────────────────────────────── */
case 'academic': ?>
    <div style="text-align:center;padding:30px 30px 16px;border-bottom:3px double <?= $primary ?>;">
      <?php if($photo):?><img src="<?= rpHe($photo) ?>" alt="" style="width:76px;height:76px;border-radius:50%;object-fit:cover;border:2px solid <?= $border ?>;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;"><?php endif;?>
      <?php if($name):?><div style="font-size:2.1em;font-weight:700;color:<?= $text ?>;letter-spacing:.5px;"><?= $name ?></div><?php endif;?>
      <?php if($jobTitle):?><div style="font-size:.92em;color:<?= $primary ?>;margin-top:3px;"><?= $jobTitle ?></div><?php endif;?>
      <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:4px 13px;margin-top:8px;font-size:.75em;color:<?= $muted ?>;"><?= rpContactRow($contact,$muted,$primary) ?></div>
    </div>
    <div style="padding:20px 30px;"><?php foreach($order as $s){if($s==='contact')continue; echo $sec($s,'underline');} ?></div>
    <?php break;

/* ── single / default ──────────────────────────────────────── */
default:
    $hBg = match($headerSt){
        'gradient' => "linear-gradient(135deg,{$primary}22,{$secondary}22)",
        'solid'    => $surface,
        'neon'     => $surface,
        default    => 'transparent',
    };
    $hBdr = match($headerSt){
        'neon'     => "2px solid {$primary}",
        'underline'=> "3px double {$primary}",
        'minimal'  => "1px solid {$border}",
        default    => "3px solid {$primary}",
    };
    ?>
    <div style="background:<?= $hBg ?>;border-bottom:<?= $hBdr ?>;<?= $headerSt==='neon' ? "box-shadow:0 2px 20px {$primary}44;" : '' ?>padding:<?= $pad ?> 26px;">
      <div style="display:flex;align-items:center;gap:16px;">
        <?php if($photo):?><img src="<?= rpHe($photo) ?>" alt="" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid <?= $primary ?>;"><?php endif;?>
        <div>
          <?php if($name):?><div style="font-size:2em;font-weight:800;color:<?= $primary ?>;line-height:1.1;letter-spacing:-.5px;"><?= $name ?></div><?php endif;?>
          <?php if($jobTitle):?><div style="font-size:.88em;font-weight:600;color:<?= $text ?>;margin-top:3px;"><?= $jobTitle ?></div><?php endif;?>
          <div style="display:flex;flex-wrap:wrap;gap:4px 13px;margin-top:8px;font-size:.77em;color:<?= $muted ?>;"><?= rpContactRow($contact,$muted,$primary) ?></div>
        </div>
      </div>
    </div>
    <div style="padding:<?= $pad ?> 26px;"><?php foreach($order as $s){if($s==='contact')continue; echo $sec($s);} ?></div>
    <?php break;
} // end switch layout
?>
</div><!-- /rp-a4 -->
</div><!-- /rp-wrap -->
<?php if (!$isEmbed): ?>
<script>
/* ── Download PDF (client-side via html2pdf.js) ── */
function downloadPreviewPdf(e) {
    e.preventDefault();
    var btn = document.getElementById('btnDownloadPdf');
    if (btn) { btn.style.opacity = '0.6'; btn.style.pointerEvents = 'none'; btn.textContent = 'Generating…'; }

    var safeTitle = (document.title || 'resume').replace(/[^a-zA-Z0-9_\-]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '') || 'resume';

    function restoreBtn() {
        if (btn) { btn.style.opacity = ''; btn.style.pointerEvents = ''; btn.innerHTML = '&#8659; Download PDF'; }
    }

    function generate() {
        // First try server-side PDF (Chromium)
        fetch(btn.getAttribute('href'))
            .then(function(res) {
                var ct = res.headers.get('Content-Type') || '';
                if (ct.indexOf('application/pdf') !== -1) {
                    return res.blob().then(function(blob) {
                        var url = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url; a.download = safeTitle + '.pdf';
                        document.body.appendChild(a); a.click();
                        setTimeout(function() { URL.revokeObjectURL(url); document.body.removeChild(a); }, 1000);
                        restoreBtn();
                    });
                } else {
                    // Fallback: render current page's .rp-a4 with html2pdf.js
                    clientGenerate();
                }
            })
            .catch(clientGenerate);
    }

    function clientGenerate() {
        // Temporarily unset min-height so pdf has exact content height
        var paper = document.querySelector('.rp-a4');
        if (!paper) { restoreBtn(); return; }
        var origStyle = { minHeight: paper.style.minHeight, overflow: paper.style.overflow };
        paper.style.minHeight = 'auto';
        paper.style.overflow  = 'visible';

        html2pdf().from(paper).set({
            margin: 0,
            filename: safeTitle + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        }).save().then(function() {
            paper.style.minHeight = origStyle.minHeight;
            paper.style.overflow  = origStyle.overflow;
            restoreBtn();
        }).catch(function() {
            paper.style.minHeight = origStyle.minHeight;
            paper.style.overflow  = origStyle.overflow;
            restoreBtn();
        });
    }

    if (typeof html2pdf !== 'undefined') {
        generate();
    } else {
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        s.crossOrigin = 'anonymous';
        s.onload = generate;
        s.onerror = restoreBtn;
        document.head.appendChild(s);
    }
}
<?php if ($isAutoPrint): ?>
window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 500); });
<?php endif; ?>
</script>
<?php endif; ?>
</body>
</html>
