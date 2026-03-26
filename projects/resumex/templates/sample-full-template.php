<?php
/**
 * ResumeX — Sample Full Resume Template
 * ======================================
 *
 * This file is a COMPLETE resume renderer. When uploaded as a "Full Template"
 * it replaces the built-in preview.php / print.php and renders the entire page.
 *
 * AVAILABLE VARIABLES
 * -------------------
 * $resumeData   array   All resume content (see structure below)
 * $resume       array   DB row: id, title, template, user_id, …
 * $themeSettings array  Optional overrides (primaryColor, etc.) – may be empty
 * $isEmbed      bool    True when rendered inside the editor iframe (hide toolbar)
 * $isPdf        bool    True when generating PDF (avoid backgrounds in @media print)
 * $title        string  HTML-escaped resume title
 *
 * $resumeData STRUCTURE
 * ---------------------
 * contact: { name, job_title, email, phone, location, website, linkedin, github, photo }
 * summary:     string
 * experience:  [ { title, company, location, start_date, end_date, current, description, bullets[] } ]
 * education:   [ { school, degree, field, location, start_date, end_date, gpa, description } ]
 * skills:      [ { name } ]  or  [ string ]
 * projects:    [ { name, technologies, url, description } ]
 * certifications: [ { name, issuer, date, expiry, url } ]
 * awards:      [ { title, issuer, date, description } ]
 * volunteer:   [ { role, organization, location, start_date, end_date, description } ]
 * languages:   [ { language, proficiency } ]
 * hobbies:     [ string ]
 * references:  [ { name, title, company, email, phone } ]
 * publications:[ { title, publisher, date, url, description } ]
 * hidden_sections: [ string ]   sections the user has hidden
 * section_order:   [ string ]   user-defined display order
 *
 * CUSTOMISATION GUIDE
 * -------------------
 * 1. Design this file as you would any HTML/PHP file — full control over CSS & layout.
 * 2. Keep the <!DOCTYPE html> … </html> wrapper so the file is a complete page.
 * 3. Use $resumeData to pull in the user's content.
 * 4. Provide key, name, category, display_bg and display_pri via the upload form —
 *    they are used by the template picker card, not by this file.
 * 5. Upload via Admin → ResumeX → Templates → Upload Full Resume Template.
 *
 * @package MMB\Projects\ResumeX\Templates
 */

// ── Helper: safe HTML escape ──────────────────────────────────────────────────
if (!function_exists('ftHe')) {
    function ftHe(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
if (!function_exists('ftHidden')) {
    function ftHidden(array $data, string $sec): bool {
        return in_array($sec, $data['hidden_sections'] ?? [], true);
    }
}
if (!function_exists('ftHas')) {
    function ftHas(array $data, string $sec): bool {
        if ($sec === 'contact')  { return !empty(array_filter(array_values($data['contact'] ?? []))); }
        if ($sec === 'summary')  { return !empty(trim($data['summary'] ?? '')); }
        if ($sec === 'skills')   { return !empty($data['skills'] ?? []); }
        if ($sec === 'hobbies')  { return !empty($data['hobbies'] ?? []); }
        return !empty($data[$sec] ?? []);
    }
}

// ── Pull data ─────────────────────────────────────────────────────────────────
$d       = $resumeData ?? [];
$contact = $d['contact']         ?? [];
$hidden  = $d['hidden_sections'] ?? [];
$order   = $d['section_order']   ?? [
    'contact','summary','experience','education','skills','projects',
    'certifications','awards','volunteer','languages','hobbies','references','publications'
];

// Allow optional colour overrides from $themeSettings
$ts      = $themeSettings ?? [];
$pri     = $ts['primaryColor']    ?? '#2563eb';
$sec2    = $ts['secondaryColor']  ?? '#1d4ed8';
$bg      = $ts['backgroundColor'] ?? '#ffffff';
$surf    = $ts['surfaceColor']    ?? '#f8fafc';
$txt     = $ts['textColor']       ?? '#0f172a';
$muted   = $ts['textMuted']       ?? '#475569';
$border  = $ts['borderColor']     ?? '#e2e8f0';
$font    = $ts['fontFamily']      ?? 'Inter';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?? ftHe($contact['name'] ?? 'Resume') ?></title>
<?php if (!$isPdf): ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<?php endif; ?>
<style>
/* ── Reset & base ───────────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: '<?= ftHe($font) ?>', 'Inter', Arial, sans-serif;
    font-size: 13px;
    line-height: 1.55;
    color: <?= ftHe($txt) ?>;
    background: <?= ftHe($bg) ?>;
}
a { color: <?= ftHe($pri) ?>; text-decoration: none; }
a:hover { text-decoration: underline; }

/* ── Page layout ────────────────────────────────────────────────────────────── */
.page {
    max-width: 860px;
    margin: 0 auto;
    padding: 0;
    background: <?= ftHe($bg) ?>;
    min-height: 100vh;
}

/* ── Header / hero ──────────────────────────────────────────────────────────── */
.resume-header {
    background: <?= ftHe($pri) ?>;
    color: #fff;
    padding: 36px 40px 28px;
    display: flex;
    align-items: center;
    gap: 28px;
}
.resume-photo {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.35);
    flex-shrink: 0;
}
.resume-header-text { flex: 1; }
.resume-name {
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin-bottom: 4px;
    color: #fff;
}
.resume-jobtitle {
    font-size: 1rem;
    font-weight: 500;
    opacity: 0.85;
    margin-bottom: 12px;
}
.resume-contacts {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 20px;
    font-size: 0.8rem;
    opacity: 0.9;
}
.resume-contacts span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ── Body ───────────────────────────────────────────────────────────────────── */
.resume-body {
    padding: 28px 40px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* ── Section ────────────────────────────────────────────────────────────────── */
.section-title {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: <?= ftHe($pri) ?>;
    border-bottom: 2px solid <?= ftHe($pri) ?>;
    padding-bottom: 5px;
    margin-bottom: 12px;
}
.section-summary {
    color: <?= ftHe($muted) ?>;
    font-size: 0.875rem;
    line-height: 1.65;
}

/* ── Cards ──────────────────────────────────────────────────────────────────── */
.card {
    padding: 12px 14px;
    border: 1px solid <?= ftHe($border) ?>;
    border-radius: 8px;
    background: <?= ftHe($surf) ?>;
    margin-bottom: 10px;
}
.card:last-child { margin-bottom: 0; }
.card-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}
.card-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: <?= ftHe($txt) ?>;
}
.card-subtitle {
    font-size: 0.82rem;
    font-weight: 600;
    color: <?= ftHe($pri) ?>;
    margin-top: 1px;
}
.card-meta {
    font-size: 0.75rem;
    color: <?= ftHe($muted) ?>;
    white-space: nowrap;
    flex-shrink: 0;
}
.card-desc {
    font-size: 0.825rem;
    color: <?= ftHe($muted) ?>;
    margin-top: 6px;
    line-height: 1.55;
}
.card-bullets {
    list-style: none;
    margin-top: 6px;
    padding: 0;
}
.card-bullets li {
    font-size: 0.815rem;
    color: <?= ftHe($muted) ?>;
    padding: 2px 0 2px 14px;
    position: relative;
    line-height: 1.5;
}
.card-bullets li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 9px;
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: <?= ftHe($pri) ?>;
}

/* ── Skills ─────────────────────────────────────────────────────────────────── */
.skills-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
.skill-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    background: <?= ftHe($pri) ?>18;
    border: 1px solid <?= ftHe($pri) ?>44;
    color: <?= ftHe($pri) ?>;
}

/* ── Languages ──────────────────────────────────────────────────────────────── */
.lang-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid <?= ftHe($border) ?>;
    font-size: 0.85rem;
}
.lang-row:last-child { border-bottom: none; }
.lang-level {
    font-size: 0.77rem;
    color: <?= ftHe($pri) ?>;
    font-weight: 600;
}

/* ── Print / PDF ────────────────────────────────────────────────────────────── */
@media print {
    body { background: #fff !important; }
    .page { max-width: 100%; }
    .resume-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>
<div class="page">

<?php
// ── Header ───────────────────────────────────────────────────────────────────
if (!ftHidden($d, 'contact') && ftHas($d, 'contact')):
?>
<div class="resume-header">
    <?php if (!empty($contact['photo'])): ?>
    <img class="resume-photo" src="<?= ftHe($contact['photo']) ?>" alt="Photo">
    <?php endif; ?>
    <div class="resume-header-text">
        <?php if (!empty($contact['name'])): ?>
        <div class="resume-name"><?= ftHe($contact['name']) ?></div>
        <?php endif; ?>
        <?php if (!empty($contact['job_title'])): ?>
        <div class="resume-jobtitle"><?= ftHe($contact['job_title']) ?></div>
        <?php endif; ?>
        <div class="resume-contacts">
            <?php if (!empty($contact['email'])): ?>
            <span>✉ <a href="mailto:<?= ftHe($contact['email']) ?>" style="color:#fff;"><?= ftHe($contact['email']) ?></a></span>
            <?php endif; ?>
            <?php if (!empty($contact['phone'])): ?>
            <span>☏ <?= ftHe($contact['phone']) ?></span>
            <?php endif; ?>
            <?php if (!empty($contact['location'])): ?>
            <span>📍 <?= ftHe($contact['location']) ?></span>
            <?php endif; ?>
            <?php if (!empty($contact['website'])): ?>
            <span>🌐 <a href="<?= ftHe($contact['website']) ?>" style="color:#fff;" target="_blank" rel="noopener"><?= ftHe($contact['website']) ?></a></span>
            <?php endif; ?>
            <?php if (!empty($contact['linkedin'])): ?>
            <span>in <?= ftHe($contact['linkedin']) ?></span>
            <?php endif; ?>
            <?php if (!empty($contact['github'])): ?>
            <span>⚙ <?= ftHe($contact['github']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="resume-body">
<?php
// ── Sections in user-defined order ───────────────────────────────────────────
$sectionLabels = [
    'summary'        => 'Summary',
    'experience'     => 'Work Experience',
    'education'      => 'Education',
    'skills'         => 'Skills',
    'projects'       => 'Projects',
    'certifications' => 'Certifications',
    'awards'         => 'Awards & Achievements',
    'volunteer'      => 'Volunteer Experience',
    'languages'      => 'Languages',
    'hobbies'        => 'Interests & Hobbies',
    'references'     => 'References',
    'publications'   => 'Publications',
];

foreach ($order as $sec):
    if ($sec === 'contact') continue;  // contact is in the header
    if (ftHidden($d, $sec) || !ftHas($d, $sec)) continue;
    $label = $sectionLabels[$sec] ?? ucfirst($sec);
?>
<section>
    <div class="section-title"><?= ftHe($label) ?></div>

    <?php if ($sec === 'summary'): ?>
    <p class="section-summary"><?= nl2br(ftHe($d['summary'] ?? '')) ?></p>

    <?php elseif ($sec === 'experience'): ?>
    <?php foreach ($d['experience'] ?? [] as $e): ?>
    <div class="card">
        <div class="card-row">
            <div>
                <div class="card-title"><?= ftHe($e['title'] ?? '') ?></div>
                <?php if (!empty($e['company'])): ?><div class="card-subtitle"><?= ftHe($e['company']) ?><?= !empty($e['location']) ? ' · ' . ftHe($e['location']) : '' ?></div><?php endif; ?>
            </div>
            <?php
            $ds = '';
            if (!empty($e['start_date'])) {
                $ds = ftHe($e['start_date']) . ' – ' . (!empty($e['current']) ? 'Present' : ftHe($e['end_date'] ?? ''));
            }
            ?>
            <?php if ($ds): ?><div class="card-meta"><?= $ds ?></div><?php endif; ?>
        </div>
        <?php if (!empty($e['description'])): ?><div class="card-desc"><?= nl2br(ftHe($e['description'])) ?></div><?php endif; ?>
        <?php if (!empty($e['bullets'])): ?>
        <ul class="card-bullets">
            <?php foreach ($e['bullets'] as $b): if (trim($b)): ?>
            <li><?= ftHe($b) ?></li>
            <?php endif; endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php elseif ($sec === 'education'): ?>
    <?php foreach ($d['education'] ?? [] as $e): ?>
    <?php $df = trim(($e['degree'] ?? '') . (!empty($e['field']) ? ', ' . $e['field'] : '')); ?>
    <div class="card">
        <div class="card-row">
            <div>
                <div class="card-title"><?= ftHe($e['school'] ?? '') ?></div>
                <?php if ($df): ?><div class="card-subtitle"><?= ftHe($df) ?></div><?php endif; ?>
                <?php if (!empty($e['location'])): ?><div style="font-size:.77em;color:<?= ftHe($muted) ?>;"><?= ftHe($e['location']) ?></div><?php endif; ?>
            </div>
            <?php $ds2 = trim(($e['start_date'] ?? '') . ($e['end_date'] ?? '' ? ' – ' . $e['end_date'] : '')); ?>
            <?php if ($ds2): ?><div class="card-meta"><?= ftHe($ds2) ?></div><?php endif; ?>
        </div>
        <?php if (!empty($e['gpa'])): ?><div class="card-desc">GPA: <?= ftHe($e['gpa']) ?></div><?php endif; ?>
        <?php if (!empty($e['description'])): ?><div class="card-desc"><?= nl2br(ftHe($e['description'])) ?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php elseif ($sec === 'skills'): ?>
    <div class="skills-wrap">
        <?php foreach ($d['skills'] ?? [] as $s):
            $sn = is_string($s) ? $s : ($s['name'] ?? '');
            if ($sn): ?>
        <span class="skill-badge"><?= ftHe($sn) ?></span>
        <?php endif; endforeach; ?>
    </div>

    <?php elseif ($sec === 'projects'): ?>
    <?php foreach ($d['projects'] ?? [] as $p):
        $techs = '';
        if (!empty($p['technologies'])) {
            $techs = is_array($p['technologies']) ? implode(', ', $p['technologies']) : $p['technologies'];
        }
    ?>
    <div class="card">
        <div class="card-row">
            <div>
                <div class="card-title"><?= ftHe($p['name'] ?? '') ?></div>
                <?php if ($techs): ?><div class="card-subtitle"><?= ftHe($techs) ?></div><?php endif; ?>
            </div>
            <?php if (!empty($p['url'])): ?><div class="card-meta"><a href="<?= ftHe($p['url']) ?>" target="_blank" rel="noopener">View →</a></div><?php endif; ?>
        </div>
        <?php if (!empty($p['description'])): ?><div class="card-desc"><?= nl2br(ftHe($p['description'])) ?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php elseif ($sec === 'certifications'): ?>
    <?php foreach ($d['certifications'] ?? [] as $c): ?>
    <div class="card">
        <div class="card-row">
            <div>
                <div class="card-title"><?= ftHe($c['name'] ?? '') ?></div>
                <?php if (!empty($c['issuer'])): ?><div class="card-subtitle"><?= ftHe($c['issuer']) ?></div><?php endif; ?>
            </div>
            <?php $cd = ($c['date'] ?? '') . (!empty($c['expiry']) ? ' – ' . $c['expiry'] : ''); ?>
            <?php if (trim($cd)): ?><div class="card-meta"><?= ftHe(trim($cd)) ?></div><?php endif; ?>
        </div>
        <?php if (!empty($c['url'])): ?><div class="card-desc"><a href="<?= ftHe($c['url']) ?>" target="_blank" rel="noopener">View Credential →</a></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php elseif ($sec === 'awards'): ?>
    <?php foreach ($d['awards'] ?? [] as $a): ?>
    <div class="card">
        <div class="card-row">
            <div>
                <div class="card-title"><?= ftHe($a['title'] ?? '') ?></div>
                <?php if (!empty($a['issuer'])): ?><div class="card-subtitle"><?= ftHe($a['issuer']) ?></div><?php endif; ?>
            </div>
            <?php if (!empty($a['date'])): ?><div class="card-meta"><?= ftHe($a['date']) ?></div><?php endif; ?>
        </div>
        <?php if (!empty($a['description'])): ?><div class="card-desc"><?= nl2br(ftHe($a['description'])) ?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php elseif ($sec === 'volunteer'): ?>
    <?php foreach ($d['volunteer'] ?? [] as $v): ?>
    <div class="card">
        <div class="card-row">
            <div>
                <div class="card-title"><?= ftHe($v['role'] ?? '') ?></div>
                <?php if (!empty($v['organization'])): ?><div class="card-subtitle"><?= ftHe($v['organization']) ?><?= !empty($v['location']) ? ' · ' . ftHe($v['location']) : '' ?></div><?php endif; ?>
            </div>
            <?php $vd = ($v['start_date'] ?? '') . (!empty($v['end_date']) ? ' – ' . $v['end_date'] : ''); ?>
            <?php if (trim($vd)): ?><div class="card-meta"><?= ftHe(trim($vd)) ?></div><?php endif; ?>
        </div>
        <?php if (!empty($v['description'])): ?><div class="card-desc"><?= nl2br(ftHe($v['description'])) ?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php elseif ($sec === 'languages'): ?>
    <div>
        <?php foreach ($d['languages'] ?? [] as $lang): ?>
        <div class="lang-row">
            <span><?= ftHe($lang['language'] ?? '') ?></span>
            <?php if (!empty($lang['proficiency'])): ?>
            <span class="lang-level"><?= ftHe($lang['proficiency']) ?></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php elseif ($sec === 'hobbies'): ?>
    <div class="skills-wrap">
        <?php foreach ($d['hobbies'] ?? [] as $h):
            $hn = is_string($h) ? $h : ($h['name'] ?? '');
            if ($hn): ?>
        <span class="skill-badge"><?= ftHe($hn) ?></span>
        <?php endif; endforeach; ?>
    </div>

    <?php elseif ($sec === 'references'): ?>
    <?php foreach ($d['references'] ?? [] as $r): ?>
    <div class="card">
        <div class="card-title"><?= ftHe($r['name'] ?? '') ?></div>
        <?php $rt = trim(($r['title'] ?? '') . (!empty($r['company']) ? ' · ' . $r['company'] : '')); ?>
        <?php if ($rt): ?><div class="card-subtitle"><?= ftHe($rt) ?></div><?php endif; ?>
        <?php if (!empty($r['email'])): ?><div class="card-desc"><a href="mailto:<?= ftHe($r['email']) ?>"><?= ftHe($r['email']) ?></a></div><?php endif; ?>
        <?php if (!empty($r['phone'])): ?><div class="card-desc"><?= ftHe($r['phone']) ?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php elseif ($sec === 'publications'): ?>
    <?php foreach ($d['publications'] ?? [] as $p): ?>
    <div class="card">
        <div class="card-row">
            <div>
                <div class="card-title"><?= ftHe($p['title'] ?? '') ?></div>
                <?php if (!empty($p['publisher'])): ?><div class="card-subtitle"><?= ftHe($p['publisher']) ?></div><?php endif; ?>
            </div>
            <?php if (!empty($p['date'])): ?><div class="card-meta"><?= ftHe($p['date']) ?></div><?php endif; ?>
        </div>
        <?php if (!empty($p['description'])): ?><div class="card-desc"><?= nl2br(ftHe($p['description'])) ?></div><?php endif; ?>
        <?php if (!empty($p['url'])): ?><div class="card-desc"><a href="<?= ftHe($p['url']) ?>" target="_blank" rel="noopener">Read →</a></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</section>
<?php endforeach; ?>
</div><!-- /.resume-body -->
</div><!-- /.page -->
</body>
</html>
