<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($resume['title'] ?? 'Resume', ENT_QUOTES, 'UTF-8') ?></title>
<?php
$t = $themeSettings;
$d = $resumeData;
$contact = $d['contact'] ?? [];
$hidden  = $d['hidden_sections'] ?? [];
$order   = $d['section_order'] ?? ['contact','summary','experience','education','skills','projects','certifications','awards','volunteer','languages','hobbies','references','publications'];

$primaryColor    = htmlspecialchars($t['primaryColor']    ?? '#00f0ff');
$secondaryColor  = htmlspecialchars($t['secondaryColor']  ?? '#9945ff');
$backgroundColor = htmlspecialchars($t['backgroundColor'] ?? '#ffffff');
$surfaceColor    = htmlspecialchars($t['surfaceColor']    ?? '#f8fafc');
$textColor       = htmlspecialchars($t['textColor']       ?? '#1e293b');
$textMuted       = htmlspecialchars($t['textMuted']       ?? '#64748b');
$borderColor     = htmlspecialchars($t['borderColor']     ?? '#e2e8f0');
$fontFamily      = htmlspecialchars($t['fontFamily']      ?? 'Arial');
$fontSize        = (int)($t['fontSize'] ?? 13);
$headerStyle     = $t['headerStyle'] ?? 'solid';
$cardStyle       = $t['cardStyle']   ?? 'flat';
$spacing         = $t['spacing']     ?? 'comfortable';
$twoCol          = ($t['layoutMode'] ?? 'single') === 'two-column';

$pad    = match($spacing) { 'compact' => '12px', 'spacious' => '24px', default => '16px' };
$secGap = match($spacing) { 'compact' => '14px', 'spacious' => '28px', default => '18px' };

$cardBg = match($cardStyle) {
    'elevated' => $surfaceColor,
    'glass', 'bordered', 'neon', 'flat' => 'transparent',
    default => 'transparent',
};
$cardBorderStr = match($cardStyle) {
    'bordered', 'glass' => 'border: 1px solid ' . $borderColor . ';',
    'neon'              => 'border: 1px solid ' . $primaryColor . ';',
    'elevated'          => 'box-shadow: 0 1px 6px rgba(0,0,0,0.1);',
    'flat'              => 'border-bottom: 1px solid ' . $borderColor . '; border-radius: 0 !important;',
    default             => 'border: none;',
};

function pHe(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function pIsHidden(array $hidden, string $sec): bool { return in_array($sec, $hidden); }
function pHasContent(array $d, string $sec): bool {
    if ($sec === 'summary') return !empty(trim($d['summary'] ?? ''));
    return !empty($d[$sec] ?? []);
}
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=<?= urlencode($fontFamily) ?>:wght@400;600;700;800&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

@media screen {
    body {
        background: #e5e7eb;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
        font-family: '<?= $fontFamily ?>', Arial, sans-serif;
    }
    .rx-print-toolbar {
        width: 100%;
        max-width: 860px;
        margin: 20px 0 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .rx-print-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: none;
        font-family: inherit;
        transition: all 0.2s;
    }
    .rx-print-btn-back { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .rx-print-btn-back:hover { background: #f9fafb; text-decoration: none; color: #374151; }
    .rx-print-btn-print { background: #4f46e5; color: #fff; }
    .rx-print-btn-print:hover { background: #4338ca; }
    .rx-paper {
        width: 860px;
        margin-bottom: 40px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.15);
    }
}

.rx-print-toolbar.autoprint-hidden { display: none; }
@media print {
    @page { size: A4; margin: 0; }
    body { background: white; margin: 0; padding: 0; }
    .rx-print-toolbar { display: none; }
    .rx-paper { box-shadow: none; width: 210mm; min-height: 297mm; margin: 0; page-break-after: always; }
}

.rx-paper {
    background: <?= $backgroundColor ?>;
    color: <?= $textColor ?>;
    font-family: '<?= $fontFamily ?>', Arial, sans-serif;
    font-size: <?= $fontSize ?>px;
    line-height: 1.5;
}

/* ── Header ───────────────────────────────────────────────── */
.rx-head { padding: <?= $pad ?>; }
.rx-head-gradient { background: linear-gradient(135deg, <?= $primaryColor ?>22, <?= $secondaryColor ?>22); border-bottom: 2px solid <?= $primaryColor ?>; }
.rx-head-solid    { background: <?= $surfaceColor ?>; border-bottom: 3px solid <?= $primaryColor ?>; }
.rx-head-minimal  { border-bottom: 1px solid <?= $borderColor ?>; }
.rx-head-neon     { background: <?= $surfaceColor ?>; border-bottom: 2px solid <?= $primaryColor ?>; }
.rx-head-underline { border-bottom: 3px double <?= $primaryColor ?>; }

.rx-head-inner { display: flex; align-items: center; gap: 16px; }
.rx-photo { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid <?= $primaryColor ?>; flex-shrink: 0; }
.rx-name { font-size: 2em; font-weight: 800; color: <?= $primaryColor ?>; letter-spacing: -0.5px; margin-bottom: 4px; }
.rx-contacts { display: flex; flex-wrap: wrap; gap: 4px 14px; font-size: 0.78em; color: <?= $textMuted ?>; margin-top: 8px; }
.rx-contacts a { color: <?= $textMuted ?>; text-decoration: none; }
.rx-contacts span { display: inline-flex; align-items: center; gap: 3px; }

/* ── Body ─────────────────────────────────────────────────── */
.rx-body { padding: <?= $pad ?>; <?= $twoCol ? 'display:grid; grid-template-columns:1fr 2fr; gap:' . $pad . ';' : '' ?> }

/* ── Sections ─────────────────────────────────────────────── */
.rx-section { margin-bottom: <?= $secGap ?>; }
.rx-section-title {
    font-size: 0.68em;
    font-weight: 800;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: <?= $primaryColor ?>;
    border-bottom: 1px solid <?= $primaryColor ?>55;
    padding-bottom: 4px;
    margin-bottom: 10px;
}

/* ── Entry cards ──────────────────────────────────────────── */
.rx-entry {
    background: <?= $cardBg ?>;
    <?= $cardBorderStr ?>
    border-radius: <?= $cardStyle === 'flat' ? '0' : '6px' ?>;
    padding: <?= $cardStyle === 'flat' ? '8px 0' : '10px 12px' ?>;
    margin-bottom: 8px;
    page-break-inside: avoid;
}
.rx-entry-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.rx-entry-title { font-size: 0.9em; font-weight: 700; color: <?= $textColor ?>; }
.rx-entry-sub   { font-size: 0.8em; font-weight: 600; color: <?= $primaryColor ?>; margin-top: 1px; }
.rx-entry-loc   { font-size: 0.75em; color: <?= $textMuted ?>; margin-top: 2px; }
.rx-entry-date  { font-size: 0.72em; color: <?= $textMuted ?>; white-space: nowrap; flex-shrink: 0; }
.rx-entry-desc  { font-size: 0.8em; color: <?= $textMuted ?>; line-height: 1.55; margin-top: 5px; }
.rx-bullets     { list-style: none; margin: 5px 0 0; padding: 0; }
.rx-bullets li  { font-size: 0.78em; color: <?= $textMuted ?>; padding: 1px 0 1px 12px; position: relative; line-height: 1.5; }
.rx-bullets li::before { content:''; position: absolute; left:0; top:7px; width:4px; height:4px; border-radius: 50%; background: <?= $primaryColor ?>; }

/* ── Summary ──────────────────────────────────────────────── */
.rx-summary { font-size: 0.85em; color: <?= $textMuted ?>; line-height: 1.65; }

/* ── Skills ───────────────────────────────────────────────── */
.rx-skills  { display: flex; flex-wrap: wrap; gap: 5px; }
.rx-skill   { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 0.75em; font-weight: 600; background: <?= $primaryColor ?>18; border: 1px solid <?= $primaryColor ?>44; color: <?= $primaryColor ?>; }

/* ── Languages ────────────────────────────────────────────── */
.rx-lang-row { display: flex; justify-content: space-between; font-size: 0.82em; margin-bottom: 5px; }
.rx-lang-name { font-weight: 600; color: <?= $textColor ?>; }
.rx-lang-lev  { color: <?= $textMuted ?>; }

/* ── Hobbies ──────────────────────────────────────────────── */
.rx-hobbies { display: flex; flex-wrap: wrap; gap: 5px; }
.rx-hobby   { display: inline-block; padding: 2px 9px; border-radius: 5px; font-size: 0.75em; font-weight: 500; background: <?= $secondaryColor ?>15; border: 1px solid <?= $secondaryColor ?>33; color: <?= $secondaryColor ?>; }
</style>
</head>
<body>

<?php $isAutoprint = !empty($autoPrint); ?>
<div class="rx-print-toolbar<?= $isAutoprint ? ' autoprint-hidden' : '' ?>">
    <a href="/projects/resumex/edit/<?= (int)$resume['id'] ?>" class="rx-print-btn rx-print-btn-back">← Edit</a>
    <a href="/projects/resumex" class="rx-print-btn rx-print-btn-back">Dashboard</a>
    <button onclick="window.print()" class="rx-print-btn rx-print-btn-print">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Print / Save as PDF
    </button>
    <span style="font-size:12px;color:#6b7280;">Tip: In the print dialog, set margins to "None" and enable "Background graphics" for best results.</span>
</div>
<script>
// Auto-open print dialog for download flow or ?print=1 param
(function() {
    var autoprint = <?= $isAutoprint ? 'true' : 'false' ?>;
    if (autoprint || window.location.search.indexOf('print=1') !== -1) {
        window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 300); });
    }
}());
</script>

<div class="rx-paper">

    <!-- Header -->
    <div class="rx-head rx-head-<?= pHe($headerStyle) ?>">
        <div class="rx-head-inner">
            <?php if (!empty($contact['photo'])): ?>
                <img src="<?= pHe($contact['photo']) ?>" alt="" class="rx-photo">
            <?php endif; ?>
            <div>
                <?php if (!empty($contact['name'])): ?>
                    <div class="rx-name"><?= pHe($contact['name']) ?></div>
                <?php endif; ?>
                <div class="rx-contacts">
                    <?php foreach (['email' => 'mailto:', 'phone' => 'tel:', 'location' => false, 'website' => false, 'linkedin' => false, 'github' => false] as $field => $scheme):
                        if (empty($contact[$field])) continue;
                        $v = pHe($contact[$field]);
                        $href = $scheme ? pHe($scheme . $contact[$field]) : $v;
                    ?>
                    <span>
                        <?php if ($scheme || filter_var($contact[$field], FILTER_VALIDATE_URL)): ?>
                            <a href="<?= $href ?>"><?= $v ?></a>
                        <?php else: ?><?= $v ?><?php endif; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Body -->
    <?php
    $renderPSection = function (string $sec) use ($d, $hidden, $primaryColor, $secondaryColor, $textColor, $textMuted) {
        if (pIsHidden($hidden, $sec) || !pHasContent($d, $sec)) return;
        $labels = [
            'summary'=>'Professional Summary','experience'=>'Work Experience','education'=>'Education',
            'skills'=>'Skills','projects'=>'Projects','certifications'=>'Certifications',
            'awards'=>'Awards','volunteer'=>'Volunteer Work','languages'=>'Languages',
            'hobbies'=>'Hobbies','references'=>'References','publications'=>'Publications',
        ];
        echo '<div class="rx-section">';
        if (isset($labels[$sec])) echo '<div class="rx-section-title">' . $labels[$sec] . '</div>';

        switch ($sec) {
            case 'summary':
                echo '<div class="rx-summary">' . nl2br(pHe($d['summary'] ?? '')) . '</div>';
                break;
            case 'experience':
                foreach ($d['experience'] ?? [] as $exp) {
                    $ds = !empty($exp['start_date']) ? pHe($exp['start_date']) . ' – ' . (!empty($exp['current']) ? 'Present' : pHe($exp['end_date'] ?? '')) : '';
                    echo '<div class="rx-entry"><div class="rx-entry-head"><div>';
                    echo '<div class="rx-entry-title">' . pHe($exp['title'] ?? '') . '</div>';
                    if (!empty($exp['company'])) echo '<div class="rx-entry-sub">' . pHe($exp['company']) . '</div>';
                    if (!empty($exp['location'])) echo '<div class="rx-entry-loc">' . pHe($exp['location']) . '</div>';
                    echo '</div>';
                    if ($ds) echo '<div class="rx-entry-date">' . $ds . '</div>';
                    echo '</div>';
                    if (!empty($exp['description'])) echo '<div class="rx-entry-desc">' . nl2br(pHe($exp['description'])) . '</div>';
                    if (!empty($exp['bullets'])) { echo '<ul class="rx-bullets">'; foreach ($exp['bullets'] as $b) if (trim($b)) echo '<li>' . pHe($b) . '</li>'; echo '</ul>'; }
                    echo '</div>';
                }
                break;
            case 'education':
                foreach ($d['education'] ?? [] as $edu) {
                    $ds = (!empty($edu['start_date']) || !empty($edu['end_date'])) ? pHe($edu['start_date'] ?? '') . ' – ' . pHe($edu['end_date'] ?? '') : '';
                    $df = trim(($edu['degree'] ?? '') . (empty($edu['field']) ? '' : ', ' . $edu['field']));
                    echo '<div class="rx-entry"><div class="rx-entry-head"><div>';
                    echo '<div class="rx-entry-title">' . pHe($edu['school'] ?? '') . '</div>';
                    if ($df) echo '<div class="rx-entry-sub">' . pHe($df) . '</div>';
                    if (!empty($edu['location'])) echo '<div class="rx-entry-loc">' . pHe($edu['location']) . '</div>';
                    echo '</div>';
                    if ($ds) echo '<div class="rx-entry-date">' . $ds . '</div>';
                    echo '</div>';
                    if (!empty($edu['gpa'])) echo '<div class="rx-entry-desc">GPA: ' . pHe($edu['gpa']) . '</div>';
                    if (!empty($edu['description'])) echo '<div class="rx-entry-desc">' . nl2br(pHe($edu['description'])) . '</div>';
                    echo '</div>';
                }
                break;
            case 'skills':
                echo '<div class="rx-skills">';
                foreach ($d['skills'] ?? [] as $s) { $n = is_string($s) ? $s : ($s['name'] ?? ''); if ($n) echo '<span class="rx-skill">' . pHe($n) . '</span>'; }
                echo '</div>';
                break;
            case 'projects':
                foreach ($d['projects'] ?? [] as $p) {
                    $techs = is_array($p['technologies'] ?? null) ? implode(', ', $p['technologies']) : ($p['technologies'] ?? '');
                    echo '<div class="rx-entry"><div class="rx-entry-head"><div>';
                    echo '<div class="rx-entry-title">' . pHe($p['name'] ?? '') . '</div>';
                    if ($techs) echo '<div class="rx-entry-sub">' . pHe($techs) . '</div>';
                    echo '</div>';
                    if (!empty($p['url'])) echo '<div class="rx-entry-date"><a href="' . pHe($p['url']) . '" style="color:' . $primaryColor . '">Link</a></div>';
                    echo '</div>';
                    if (!empty($p['description'])) echo '<div class="rx-entry-desc">' . nl2br(pHe($p['description'])) . '</div>';
                    echo '</div>';
                }
                break;
            case 'certifications':
                foreach ($d['certifications'] ?? [] as $c) {
                    $ds = pHe($c['date'] ?? ''); if (!empty($c['expiry'])) $ds .= ' – ' . pHe($c['expiry']);
                    echo '<div class="rx-entry"><div class="rx-entry-head"><div>';
                    echo '<div class="rx-entry-title">' . pHe($c['name'] ?? '') . '</div>';
                    if (!empty($c['issuer'])) echo '<div class="rx-entry-sub">' . pHe($c['issuer']) . '</div>';
                    echo '</div>';
                    if ($ds) echo '<div class="rx-entry-date">' . $ds . '</div>';
                    echo '</div></div>';
                }
                break;
            case 'awards':
                foreach ($d['awards'] ?? [] as $a) {
                    echo '<div class="rx-entry"><div class="rx-entry-head"><div>';
                    echo '<div class="rx-entry-title">' . pHe($a['title'] ?? '') . '</div>';
                    if (!empty($a['issuer'])) echo '<div class="rx-entry-sub">' . pHe($a['issuer']) . '</div>';
                    echo '</div>';
                    if (!empty($a['date'])) echo '<div class="rx-entry-date">' . pHe($a['date']) . '</div>';
                    echo '</div>';
                    if (!empty($a['description'])) echo '<div class="rx-entry-desc">' . pHe($a['description']) . '</div>';
                    echo '</div>';
                }
                break;
            case 'volunteer':
                foreach ($d['volunteer'] ?? [] as $v) {
                    $ds = !empty($v['start_date']) ? pHe($v['start_date']) . ' – ' . pHe($v['end_date'] ?? 'Present') : '';
                    echo '<div class="rx-entry"><div class="rx-entry-head"><div>';
                    echo '<div class="rx-entry-title">' . pHe($v['role'] ?? '') . '</div>';
                    if (!empty($v['organization'])) echo '<div class="rx-entry-sub">' . pHe($v['organization']) . '</div>';
                    echo '</div>';
                    if ($ds) echo '<div class="rx-entry-date">' . $ds . '</div>';
                    echo '</div>';
                    if (!empty($v['description'])) echo '<div class="rx-entry-desc">' . nl2br(pHe($v['description'])) . '</div>';
                    echo '</div>';
                }
                break;
            case 'languages':
                foreach ($d['languages'] ?? [] as $l) {
                    echo '<div class="rx-lang-row"><span class="rx-lang-name">' . pHe($l['language'] ?? '') . '</span><span class="rx-lang-lev">' . pHe($l['level'] ?? '') . '</span></div>';
                }
                break;
            case 'hobbies':
                echo '<div class="rx-hobbies">'; foreach ($d['hobbies'] ?? [] as $h) echo '<span class="rx-hobby">' . pHe($h) . '</span>'; echo '</div>';
                break;
            case 'references':
                foreach ($d['references'] ?? [] as $r) {
                    $rc = trim(($r['title'] ?? '') . (empty($r['company']) ? '' : ', ' . $r['company']));
                    echo '<div class="rx-entry"><div class="rx-entry-title">' . pHe($r['name'] ?? '') . '</div>';
                    if ($rc) echo '<div class="rx-entry-sub">' . pHe($rc) . '</div>';
                    if (!empty($r['email'])) echo '<div class="rx-entry-loc"><a href="mailto:' . pHe($r['email']) . '" style="color:' . $primaryColor . '">' . pHe($r['email']) . '</a></div>';
                    if (!empty($r['phone'])) echo '<div class="rx-entry-loc">' . pHe($r['phone']) . '</div>';
                    echo '</div>';
                }
                break;
            case 'publications':
                foreach ($d['publications'] ?? [] as $p) {
                    echo '<div class="rx-entry"><div class="rx-entry-head"><div>';
                    echo '<div class="rx-entry-title">' . pHe($p['title'] ?? '') . '</div>';
                    if (!empty($p['authors'])) echo '<div class="rx-entry-sub">' . pHe($p['authors']) . '</div>';
                    if (!empty($p['journal'])) echo '<div class="rx-entry-loc">' . pHe($p['journal']) . '</div>';
                    echo '</div>';
                    if (!empty($p['date'])) echo '<div class="rx-entry-date">' . pHe($p['date']) . '</div>';
                    echo '</div>';
                    if (!empty($p['url'])) echo '<div class="rx-entry-desc"><a href="' . pHe($p['url']) . '" style="color:' . $primaryColor . '">View →</a></div>';
                    echo '</div>';
                }
                break;
        }
        echo '</div>';
    };
    ?>

    <?php if ($twoCol): ?>
    <div class="rx-body">
        <div>
            <?php foreach (['skills','languages','hobbies','certifications','awards','references'] as $s): $renderPSection($s); endforeach; ?>
        </div>
        <div>
            <?php foreach (['summary','experience','education','projects','volunteer','publications'] as $s): $renderPSection($s); endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="rx-body" style="display:block;">
        <?php foreach ($order as $sec) { if ($sec !== 'contact') $renderPSection($sec); } ?>
    </div>
    <?php endif; ?>

</div><!-- /rx-paper -->
</body>
</html>
