<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<?php
$t = $themeSettings;
$d = $resumeData;
$contact = $d['contact'] ?? [];
$hidden  = $d['hidden_sections'] ?? [];
$order   = $d['section_order'] ?? ['contact','summary','experience','education','skills','projects','certifications','awards','volunteer','languages','hobbies','references','publications'];

$primaryColor    = htmlspecialchars($t['primaryColor']    ?? '#00f0ff');
$secondaryColor  = htmlspecialchars($t['secondaryColor']  ?? '#9945ff');
$backgroundColor = htmlspecialchars($t['backgroundColor'] ?? '#0a0a0f');
$surfaceColor    = htmlspecialchars($t['surfaceColor']    ?? '#12121e');
$textColor       = htmlspecialchars($t['textColor']       ?? '#e0e6ff');
$textMuted       = htmlspecialchars($t['textMuted']       ?? '#6b7280');
$borderColor     = htmlspecialchars($t['borderColor']     ?? 'rgba(0,240,255,0.15)');
$fontFamily      = htmlspecialchars($t['fontFamily']      ?? 'Poppins');
$fontSize        = (int)($t['fontSize'] ?? 14);
$headerStyle     = $t['headerStyle'] ?? 'gradient';
$cardStyle       = $t['cardStyle']   ?? 'glass';
$spacing         = $t['spacing']     ?? 'comfortable';
$twoCol          = ($t['layoutMode'] ?? 'single') === 'two-column';

$pad = match($spacing) { 'compact' => '14px', 'spacious' => '28px', default => '20px' };
$secGap = match($spacing) { 'compact' => '16px', 'spacious' => '32px', default => '22px' };

// Card style -> CSS
$cardBg = match($cardStyle) {
    'glass'    => 'rgba(255,255,255,0.04)',
    'elevated' => $surfaceColor,
    'bordered' => 'transparent',
    'neon'     => 'transparent',
    'flat'     => 'transparent',
    default    => 'transparent',
};
$cardBorderStyle = match($cardStyle) {
    'glass'    => 'border: 1px solid ' . $borderColor . '; backdrop-filter: blur(4px);',
    'elevated' => 'border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.25);',
    'bordered' => 'border: 1px solid ' . $borderColor . ';',
    'neon'     => 'border: 1px solid ' . $primaryColor . '; box-shadow: 0 0 12px rgba(0,0,0,0.3), 0 0 6px ' . $primaryColor . '33;',
    'flat'     => 'border: none; border-bottom: 1px solid ' . $borderColor . '; border-radius: 0 !important;',
    default    => 'border: 1px solid ' . $borderColor . ';',
};

function rxHe(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function rxIsHidden(array $hidden, string $sec): bool { return in_array($sec, $hidden); }
function rxHasContent(array $d, string $sec): bool {
    if ($sec === 'contact') return !empty(array_filter(array_values($d['contact'] ?? [])));
    if ($sec === 'summary') return !empty(trim($d['summary'] ?? ''));
    return !empty($d[$sec] ?? []);
}
?>
<style>
.rxp-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    background: var(--bg-card);
    border-bottom: 1px solid var(--border-color);
    flex-wrap: wrap;
}
.rxp-toolbar-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-secondary);
    transition: all 0.2s;
}
.rxp-toolbar-btn:hover { color: var(--cyan); border-color: rgba(0,240,255,0.35); text-decoration: none; }
.rxp-toolbar-btn.primary {
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    border-color: transparent;
    color: #06060a;
}
.rxp-toolbar-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,240,255,0.3); color: #06060a; }
.rxp-spacer { flex: 1; }
.rxp-meta { font-size: 0.78rem; color: var(--text-secondary); }

/* ── Resume paper ─────────────────────────────────────────── */
.rxp-paper-wrap {
    padding: 32px 24px 60px;
    display: flex;
    justify-content: center;
}
.rxp-paper {
    width: 100%;
    max-width: 860px;
    font-family: '<?= $fontFamily ?>', sans-serif;
    font-size: <?= $fontSize ?>px;
    font-weight: <?= (int)($t['fontWeight'] ?? 400) ?>;
    color: <?= $textColor ?>;
    background: <?= $backgroundColor ?>;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 8px 48px rgba(0,0,0,0.4);
}
.rxp-paper * { box-sizing: border-box; }

/* ── Header styles ────────────────────────────────────────── */
.rxp-head {
    padding: <?= $pad ?>;
}
.rxp-head-gradient {
    background: linear-gradient(135deg, <?= $primaryColor ?>22, <?= $secondaryColor ?>22);
    border-bottom: 2px solid <?= $primaryColor ?>;
}
.rxp-head-solid {
    background: <?= $surfaceColor ?>;
    border-bottom: 3px solid <?= $primaryColor ?>;
}
.rxp-head-minimal {
    background: transparent;
    border-bottom: 1px solid <?= $borderColor ?>;
}
.rxp-head-neon {
    background: <?= $surfaceColor ?>;
    border-bottom: 2px solid <?= $primaryColor ?>;
    box-shadow: 0 2px 20px <?= $primaryColor ?>44;
}
.rxp-head-underline {
    background: transparent;
    border-bottom: 3px double <?= $primaryColor ?>;
}
.rxp-name {
    font-size: 2.2em;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.1;
    margin: 0 0 6px;
    color: <?= $primaryColor ?>;
}
.rxp-contact-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 16px;
    margin-top: 10px;
    font-size: 0.82em;
    color: <?= $textMuted ?>;
}
.rxp-contact-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.rxp-contact-item a { color: <?= $textMuted ?>; text-decoration: none; }
.rxp-contact-item a:hover { color: <?= $primaryColor ?>; }

/* ── Body ─────────────────────────────────────────────────── */
.rxp-body {
    padding: <?= $pad ?>;
    <?= $twoCol ? 'display: grid; grid-template-columns: 1fr 2fr; gap: ' . $pad . ';' : '' ?>
}
.rxp-body-left, .rxp-body-right { min-width: 0; }

/* ── Section ──────────────────────────────────────────────── */
.rxp-section {
    margin-bottom: <?= $secGap ?>;
}
.rxp-section-title {
    font-size: 0.72em;
    font-weight: 800;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: <?= $primaryColor ?>;
    margin: 0 0 12px;
    padding-bottom: 6px;
    border-bottom: 1px solid <?= $primaryColor ?>55;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ── Entry cards ──────────────────────────────────────────── */
.rxp-entry {
    background: <?= $cardBg ?>;
    <?= $cardBorderStyle ?>
    border-radius: <?= $cardStyle === 'flat' ? '0' : '8px' ?>;
    padding: <?= $cardStyle === 'flat' ? '10px 0' : '12px 14px' ?>;
    margin-bottom: 10px;
}
.rxp-entry-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}
.rxp-entry-title {
    font-size: 0.92em;
    font-weight: 700;
    color: <?= $textColor ?>;
    line-height: 1.3;
}
.rxp-entry-subtitle {
    font-size: 0.82em;
    color: <?= $primaryColor ?>;
    font-weight: 600;
}
.rxp-entry-date {
    font-size: 0.75em;
    color: <?= $textMuted ?>;
    white-space: nowrap;
    flex-shrink: 0;
}
.rxp-entry-location {
    font-size: 0.78em;
    color: <?= $textMuted ?>;
    margin-top: 2px;
}
.rxp-entry-desc {
    font-size: 0.83em;
    color: <?= $textMuted ?>;
    line-height: 1.55;
    margin-top: 6px;
}
.rxp-bullets {
    margin: 6px 0 0 0;
    padding-left: 0;
    list-style: none;
}
.rxp-bullets li {
    font-size: 0.82em;
    color: <?= $textMuted ?>;
    padding: 2px 0 2px 14px;
    position: relative;
    line-height: 1.5;
}
.rxp-bullets li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 9px;
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: <?= $primaryColor ?>;
}

/* ── Summary ──────────────────────────────────────────────── */
.rxp-summary {
    font-size: 0.88em;
    color: <?= $textMuted ?>;
    line-height: 1.65;
}

/* ── Skills ───────────────────────────────────────────────── */
.rxp-skills-wrap { display: flex; flex-wrap: wrap; gap: 6px; }
.rxp-skill-tag {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.78em;
    font-weight: 600;
    background: <?= $primaryColor ?>18;
    border: 1px solid <?= $primaryColor ?>44;
    color: <?= $primaryColor ?>;
}

/* ── Languages ────────────────────────────────────────────── */
.rxp-lang-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; font-size: 0.85em; }
.rxp-lang-name { color: <?= $textColor ?>; font-weight: 600; }
.rxp-lang-level { color: <?= $textMuted ?>; font-size: 0.88em; }

/* ── Hobbies ──────────────────────────────────────────────── */
.rxp-hobbies-wrap { display: flex; flex-wrap: wrap; gap: 6px; }
.rxp-hobby-tag {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.78em;
    font-weight: 500;
    background: <?= $secondaryColor ?>15;
    border: 1px solid <?= $secondaryColor ?>35;
    color: <?= $secondaryColor ?>;
}

/* ── Photo ────────────────────────────────────────────────── */
.rxp-photo {
    width: 80px; height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid <?= $primaryColor ?>;
    flex-shrink: 0;
}
.rxp-head-inner {
    display: flex;
    align-items: center;
    gap: 18px;
}
</style>

<!-- Toolbar -->
<div class="rxp-toolbar">
    <a href="/projects/resumex" class="rxp-toolbar-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Dashboard
    </a>
    <a href="/projects/resumex/edit/<?= (int)$resume['id'] ?>" class="rxp-toolbar-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        Edit
    </a>
    <div class="rxp-spacer"></div>
    <span class="rxp-meta"><?= rxHe($resume['title'] ?? 'Resume') ?> &nbsp;·&nbsp; <?= rxHe($t['name'] ?? 'Default') ?></span>
    <a href="/projects/resumex/download/<?= (int)$resume['id'] ?>" target="_blank" class="rxp-toolbar-btn primary">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Download / Print
    </a>
</div>

<!-- Resume paper -->
<div class="rxp-paper-wrap">
<div class="rxp-paper">

    <!-- ── Header ─────────────────────────────────────────── -->
    <div class="rxp-head rxp-head-<?= rxHe($headerStyle) ?>">
        <div class="rxp-head-inner">
            <?php if (!empty($contact['photo'])): ?>
                <img src="<?= rxHe($contact['photo']) ?>" alt="Photo" class="rxp-photo">
            <?php endif; ?>
            <div>
                <?php if (!empty($contact['name'])): ?>
                    <div class="rxp-name"><?= rxHe($contact['name']) ?></div>
                <?php endif; ?>
                <div class="rxp-contact-row">
                    <?php foreach ([
                        ['email', 'mailto:', '✉'],
                        ['phone', 'tel:', '✆'],
                        ['location', false, '◎'],
                        ['website', false, '🌐'],
                        ['linkedin', false, 'in'],
                        ['github', false, '⌥'],
                    ] as [$field, $scheme, $icon]):
                        if (empty($contact[$field])) continue;
                        $val = rxHe($contact[$field]);
                        $href = $scheme ? rxHe($scheme . $contact[$field]) : $val;
                    ?>
                    <span class="rxp-contact-item">
                        <span><?= $icon ?></span>
                        <?php if ($scheme || filter_var($contact[$field], FILTER_VALIDATE_URL)): ?>
                            <a href="<?= $href ?>" target="_blank" rel="noopener"><?= $val ?></a>
                        <?php else: ?>
                            <?= $val ?>
                        <?php endif; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Body ───────────────────────────────────────────── -->
    <?php
    $renderSection = function (string $sec) use ($d, $hidden, $primaryColor, $textColor, $textMuted) {
        if (rxIsHidden($hidden, $sec) || !rxHasContent($d, $sec)) return;

        $labels = [
            'summary' => 'Professional Summary',
            'experience' => 'Work Experience',
            'education' => 'Education',
            'skills' => 'Skills',
            'projects' => 'Projects',
            'certifications' => 'Certifications',
            'awards' => 'Awards &amp; Achievements',
            'volunteer' => 'Volunteer Work',
            'languages' => 'Languages',
            'hobbies' => 'Hobbies &amp; Interests',
            'references' => 'References',
            'publications' => 'Publications',
        ];

        echo '<div class="rxp-section">';
        if ($sec !== 'contact' && isset($labels[$sec])) {
            echo '<div class="rxp-section-title">' . $labels[$sec] . '</div>';
        }

        switch ($sec) {
            case 'summary':
                echo '<div class="rxp-summary">' . nl2br(rxHe($d['summary'] ?? '')) . '</div>';
                break;

            case 'experience':
                foreach ($d['experience'] ?? [] as $exp) {
                    $dateStr = '';
                    if (!empty($exp['start_date'])) {
                        $dateStr = rxHe($exp['start_date']) . ' – ' . (!empty($exp['current']) ? 'Present' : rxHe($exp['end_date'] ?? ''));
                    }
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-head">';
                    echo '<div>';
                    echo '<div class="rxp-entry-title">' . rxHe($exp['title'] ?? '') . '</div>';
                    echo '<div class="rxp-entry-subtitle">' . rxHe($exp['company'] ?? '') . '</div>';
                    if (!empty($exp['location'])) echo '<div class="rxp-entry-location">' . rxHe($exp['location']) . '</div>';
                    echo '</div>';
                    if ($dateStr) echo '<div class="rxp-entry-date">' . $dateStr . '</div>';
                    echo '</div>';
                    if (!empty($exp['description'])) echo '<div class="rxp-entry-desc">' . nl2br(rxHe($exp['description'])) . '</div>';
                    if (!empty($exp['bullets'])) {
                        echo '<ul class="rxp-bullets">';
                        foreach ($exp['bullets'] as $b) if (trim($b)) echo '<li>' . rxHe($b) . '</li>';
                        echo '</ul>';
                    }
                    echo '</div>';
                }
                break;

            case 'education':
                foreach ($d['education'] ?? [] as $edu) {
                    $dateStr = '';
                    if (!empty($edu['start_date']) || !empty($edu['end_date'])) {
                        $dateStr = rxHe($edu['start_date'] ?? '') . ' – ' . rxHe($edu['end_date'] ?? '');
                    }
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-head">';
                    echo '<div>';
                    echo '<div class="rxp-entry-title">' . rxHe($edu['school'] ?? '') . '</div>';
                    $degField = trim(($edu['degree'] ?? '') . (empty($edu['field']) ? '' : ', ' . $edu['field']));
                    if ($degField) echo '<div class="rxp-entry-subtitle">' . rxHe($degField) . '</div>';
                    if (!empty($edu['location'])) echo '<div class="rxp-entry-location">' . rxHe($edu['location']) . '</div>';
                    echo '</div>';
                    if ($dateStr) echo '<div class="rxp-entry-date">' . $dateStr . '</div>';
                    echo '</div>';
                    if (!empty($edu['gpa'])) echo '<div class="rxp-entry-desc">GPA: ' . rxHe($edu['gpa']) . '</div>';
                    if (!empty($edu['description'])) echo '<div class="rxp-entry-desc">' . nl2br(rxHe($edu['description'])) . '</div>';
                    echo '</div>';
                }
                break;

            case 'skills':
                echo '<div class="rxp-skills-wrap">';
                foreach ($d['skills'] ?? [] as $s) {
                    $name = is_string($s) ? $s : ($s['name'] ?? '');
                    if ($name) echo '<span class="rxp-skill-tag">' . rxHe($name) . '</span>';
                }
                echo '</div>';
                break;

            case 'projects':
                foreach ($d['projects'] ?? [] as $proj) {
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-head"><div>';
                    echo '<div class="rxp-entry-title">' . rxHe($proj['name'] ?? '') . '</div>';
                    if (!empty($proj['technologies'])) {
                        $techs = is_array($proj['technologies']) ? implode(', ', $proj['technologies']) : $proj['technologies'];
                        echo '<div class="rxp-entry-subtitle">' . rxHe($techs) . '</div>';
                    }
                    echo '</div>';
                    if (!empty($proj['url'])) echo '<div class="rxp-entry-date"><a href="' . rxHe($proj['url']) . '" target="_blank" rel="noopener" style="color:' . $primaryColor . '">View →</a></div>';
                    echo '</div>';
                    if (!empty($proj['description'])) echo '<div class="rxp-entry-desc">' . nl2br(rxHe($proj['description'])) . '</div>';
                    echo '</div>';
                }
                break;

            case 'certifications':
                foreach ($d['certifications'] ?? [] as $cert) {
                    $dateStr = rxHe($cert['date'] ?? '');
                    if (!empty($cert['expiry'])) $dateStr .= ' – ' . rxHe($cert['expiry']);
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-head">';
                    echo '<div>';
                    echo '<div class="rxp-entry-title">' . rxHe($cert['name'] ?? '') . '</div>';
                    if (!empty($cert['issuer'])) echo '<div class="rxp-entry-subtitle">' . rxHe($cert['issuer']) . '</div>';
                    if (!empty($cert['id'])) echo '<div class="rxp-entry-location">ID: ' . rxHe($cert['id']) . '</div>';
                    echo '</div>';
                    if ($dateStr) echo '<div class="rxp-entry-date">' . $dateStr . '</div>';
                    echo '</div>';
                    if (!empty($cert['url'])) echo '<div class="rxp-entry-desc"><a href="' . rxHe($cert['url']) . '" target="_blank" rel="noopener" style="color:' . $primaryColor . '">View Credential →</a></div>';
                    echo '</div>';
                }
                break;

            case 'awards':
                foreach ($d['awards'] ?? [] as $award) {
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-head">';
                    echo '<div>';
                    echo '<div class="rxp-entry-title">' . rxHe($award['title'] ?? '') . '</div>';
                    if (!empty($award['issuer'])) echo '<div class="rxp-entry-subtitle">' . rxHe($award['issuer']) . '</div>';
                    echo '</div>';
                    if (!empty($award['date'])) echo '<div class="rxp-entry-date">' . rxHe($award['date']) . '</div>';
                    echo '</div>';
                    if (!empty($award['description'])) echo '<div class="rxp-entry-desc">' . rxHe($award['description']) . '</div>';
                    echo '</div>';
                }
                break;

            case 'volunteer':
                foreach ($d['volunteer'] ?? [] as $vol) {
                    $dateStr = '';
                    if (!empty($vol['start_date'])) $dateStr = rxHe($vol['start_date']) . ' – ' . rxHe($vol['end_date'] ?? 'Present');
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-head">';
                    echo '<div>';
                    echo '<div class="rxp-entry-title">' . rxHe($vol['role'] ?? '') . '</div>';
                    if (!empty($vol['organization'])) echo '<div class="rxp-entry-subtitle">' . rxHe($vol['organization']) . '</div>';
                    echo '</div>';
                    if ($dateStr) echo '<div class="rxp-entry-date">' . $dateStr . '</div>';
                    echo '</div>';
                    if (!empty($vol['description'])) echo '<div class="rxp-entry-desc">' . nl2br(rxHe($vol['description'])) . '</div>';
                    echo '</div>';
                }
                break;

            case 'languages':
                foreach ($d['languages'] ?? [] as $lang) {
                    echo '<div class="rxp-lang-row">';
                    echo '<span class="rxp-lang-name">' . rxHe($lang['language'] ?? '') . '</span>';
                    echo '<span class="rxp-lang-level">' . rxHe($lang['level'] ?? '') . '</span>';
                    echo '</div>';
                }
                break;

            case 'hobbies':
                echo '<div class="rxp-hobbies-wrap">';
                foreach ($d['hobbies'] ?? [] as $h) {
                    echo '<span class="rxp-hobby-tag">' . rxHe($h) . '</span>';
                }
                echo '</div>';
                break;

            case 'references':
                foreach ($d['references'] ?? [] as $ref) {
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-title">' . rxHe($ref['name'] ?? '') . '</div>';
                    $roleComp = trim(($ref['title'] ?? '') . (empty($ref['company']) ? '' : ', ' . $ref['company']));
                    if ($roleComp) echo '<div class="rxp-entry-subtitle">' . rxHe($roleComp) . '</div>';
                    if (!empty($ref['email'])) echo '<div class="rxp-entry-location"><a href="mailto:' . rxHe($ref['email']) . '" style="color:' . $primaryColor . '">' . rxHe($ref['email']) . '</a></div>';
                    if (!empty($ref['phone'])) echo '<div class="rxp-entry-location">' . rxHe($ref['phone']) . '</div>';
                    echo '</div>';
                }
                break;

            case 'publications':
                foreach ($d['publications'] ?? [] as $pub) {
                    echo '<div class="rxp-entry">';
                    echo '<div class="rxp-entry-head">';
                    echo '<div>';
                    echo '<div class="rxp-entry-title">' . rxHe($pub['title'] ?? '') . '</div>';
                    if (!empty($pub['authors'])) echo '<div class="rxp-entry-subtitle">' . rxHe($pub['authors']) . '</div>';
                    if (!empty($pub['journal'])) echo '<div class="rxp-entry-location">' . rxHe($pub['journal']) . '</div>';
                    echo '</div>';
                    if (!empty($pub['date'])) echo '<div class="rxp-entry-date">' . rxHe($pub['date']) . '</div>';
                    echo '</div>';
                    if (!empty($pub['url'])) echo '<div class="rxp-entry-desc"><a href="' . rxHe($pub['url']) . '" target="_blank" rel="noopener" style="color:' . $primaryColor . '">View Publication →</a></div>';
                    echo '</div>';
                }
                break;
        }

        echo '</div>';
    };
    ?>

    <?php if ($twoCol): ?>
    <div class="rxp-body">
        <div class="rxp-body-left">
            <?php foreach (['skills','languages','hobbies','certifications','awards','references'] as $sec):
                $renderSection($sec);
            endforeach; ?>
        </div>
        <div class="rxp-body-right">
            <?php foreach (['summary','experience','education','projects','volunteer','publications'] as $sec):
                $renderSection($sec);
            endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="rxp-body" style="display:block;">
        <?php
        foreach ($order as $sec) {
            if ($sec === 'contact') continue;
            $renderSection($sec);
        }
        ?>
    </div>
    <?php endif; ?>

</div><!-- /rxp-paper -->
</div><!-- /rxp-paper-wrap -->

<?php View::end(); ?>
