<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
/* ── Page wrapper ─────────────────────────────────────────────── */
.rxnf-page {
    max-width: 780px;
    margin: 0 auto;
    padding: 52px 20px 64px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

/* ── Icon ─────────────────────────────────────────────────────── */
.rxnf-icon-wrap {
    position: relative;
    margin-bottom: 28px;
}
.rxnf-icon {
    width: 100px;
    height: 100px;
    border-radius: 28px;
    background: linear-gradient(135deg, var(--cyan, #00f0ff) 0%, var(--purple, #9945ff) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 52px;
    line-height: 1;
    box-shadow: 0 8px 32px rgba(0, 240, 255, 0.2);
}
.rxnf-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2.5px solid var(--bg-main, #0a0a12);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}
.rxnf-badge svg { display: block; }

/* ── Heading & text ───────────────────────────────────────────── */
.rxnf-page h1 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 12px;
    letter-spacing: -0.5px;
}
.rxnf-desc {
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 1.65;
    max-width: 460px;
    margin: 0 auto 10px;
}
.rxnf-id {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--bg-card, #13131f);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    padding: 5px 14px;
    font-size: 0.82rem;
    color: var(--text-secondary);
    font-family: monospace;
    margin-bottom: 32px;
}
.rxnf-id span { color: var(--cyan, #00f0ff); font-weight: 700; }

/* ── CTA buttons ──────────────────────────────────────────────── */
.rxnf-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 48px;
}
.rxnf-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 26px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}
.rxnf-btn.primary {
    background: linear-gradient(135deg, var(--cyan, #00f0ff), var(--purple, #9945ff));
    color: #06060a;
    box-shadow: 0 4px 20px rgba(0, 240, 255, 0.25);
}
.rxnf-btn.secondary {
    background: var(--bg-card, #13131f);
    color: var(--text-primary);
    border: 1px solid var(--border-color, #2a2a40);
}
.rxnf-btn.primary:hover  { opacity: 0.88; text-decoration: none; color: #06060a; transform: translateY(-1px); }
.rxnf-btn.secondary:hover { border-color: var(--cyan, #00f0ff); color: var(--cyan, #00f0ff); text-decoration: none; }

/* ── Template showcase ────────────────────────────────────────── */
.rxnf-templates {
    width: 100%;
    background: var(--bg-card, #13131f);
    border: 1px solid var(--border-color, #2a2a40);
    border-radius: 18px;
    padding: 28px 28px 32px;
    margin-bottom: 32px;
    text-align: left;
}
.rxnf-templates-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 8px;
}
.rxnf-templates-title {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-secondary);
}
.rxnf-templates-link {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--cyan, #00f0ff);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.rxnf-templates-link:hover { text-decoration: underline; }
.rxnf-tpl-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
}
.rxnf-tpl-card {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    border: 1.5px solid var(--border-color, #2a2a40);
    transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    display: block;
    background: var(--bg-main, #0a0a12);
}
.rxnf-tpl-card:hover {
    border-color: var(--cyan, #00f0ff);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 240, 255, 0.15);
    text-decoration: none;
}
.rxnf-tpl-thumb {
    height: 100px;
    position: relative;
    overflow: hidden;
}
.rxnf-tpl-thumb svg { display: block; width: 100%; height: 100%; }
.rxnf-tpl-name {
    padding: 8px 10px;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Reasons card ─────────────────────────────────────────────── */
.rxnf-reasons {
    width: 100%;
    background: var(--bg-card, #13131f);
    border: 1px solid var(--border-color, #2a2a40);
    border-radius: 14px;
    padding: 20px 24px;
    text-align: left;
}
.rxnf-reasons-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-secondary);
    margin-bottom: 12px;
}
.rxnf-reasons ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.rxnf-reasons li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.875rem;
    color: var(--text-secondary);
    line-height: 1.5;
}
.rxnf-reasons li svg { flex-shrink: 0; margin-top: 2px; color: var(--cyan, #00f0ff); }

@media (max-width: 520px) {
    .rxnf-page h1 { font-size: 1.5rem; }
    .rxnf-tpl-grid { grid-template-columns: repeat(2, 1fr); }
    .rxnf-templates { padding: 20px 16px 24px; }
    .rxnf-page { padding: 36px 16px 48px; }
}
</style>

<?php
// Pick a handful of themes to showcase (first 6)
$showcaseThemes = array_slice(array_values($allThemes ?? []), 0, 6);
?>

<div class="rxnf-page">

    <!-- Icon -->
    <div class="rxnf-icon-wrap">
        <div class="rxnf-icon">📄</div>
        <div class="rxnf-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </div>
    </div>

    <h1>Resume Not Found</h1>

    <p class="rxnf-desc">
        This resume doesn't exist, may have been deleted, or you don't have access to it.
        <?php if (!empty($user)): ?>
        Why not create a brand-new resume right now?
        <?php endif; ?>
    </p>

    <?php if (!empty($id)): ?>
    <div class="rxnf-id">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
        </svg>
        Resume ID: <span>#<?= (int)$id ?></span>
    </div>
    <?php endif; ?>

    <!-- CTA buttons -->
    <div class="rxnf-actions">
        <a href="/projects/resumex/create" class="rxnf-btn primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create New Resume
        </a>
        <a href="/projects/resumex" class="rxnf-btn secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            My Resumes
        </a>
    </div>

    <?php if (!empty($showcaseThemes)): ?>
    <!-- Template showcase -->
    <div class="rxnf-templates">
        <div class="rxnf-templates-header">
            <span class="rxnf-templates-title">✨ Pick a template &amp; start now</span>
            <a href="/projects/resumex/create" class="rxnf-templates-link">
                See all templates
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div class="rxnf-tpl-grid">
            <?php foreach ($showcaseThemes as $tpl):
                $pri  = htmlspecialchars($tpl['primaryColor']    ?? '#00f0ff');
                $sec  = htmlspecialchars($tpl['secondaryColor']  ?? '#9945ff');
                $bg   = htmlspecialchars($tpl['backgroundColor'] ?? '#ffffff');
                $surf = htmlspecialchars($tpl['surfaceColor']    ?? '#f1f5f9');
                $txt  = htmlspecialchars($tpl['textColor']       ?? '#1e293b');
                $ls   = $tpl['layoutStyle'] ?? 'single';
                $key  = htmlspecialchars($tpl['key'] ?? '');
                $name = htmlspecialchars($tpl['name'] ?? '');
            ?>
            <a href="/projects/resumex/create?template=<?= $key ?>" class="rxnf-tpl-card" title="<?= $name ?>">
                <div class="rxnf-tpl-thumb">
                    <?php if ($ls === 'sidebar-left' || $ls === 'sidebar-dark'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130 100">
                        <rect width="130" height="100" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="38" height="100" fill="<?= $surf ?>"/>
                        <circle cx="19" cy="18" r="9" fill="<?= $pri ?>44" stroke="<?= $pri ?>" stroke-width="1.2"/>
                        <rect x="6" y="31" width="26" height="3" rx="1.5" fill="<?= $pri ?>cc"/>
                        <rect x="9" y="37" width="20" height="2" rx="1" fill="<?= $pri ?>66"/>
                        <rect x="6" y="44" width="14" height="1.5" rx="1" fill="<?= $pri ?>88"/>
                        <rect x="6" y="49" width="26" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="6" y="54" width="22" height="1.5" rx="1" fill="<?= $txt ?>22"/>
                        <rect x="6" y="62" width="14" height="1.5" rx="1" fill="<?= $sec ?>88"/>
                        <rect x="6" y="67" width="26" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="6" y="72" width="18" height="1.5" rx="1" fill="<?= $txt ?>22"/>
                        <rect x="46" y="8" width="70" height="4" rx="2" fill="<?= $pri ?>"/>
                        <rect x="46" y="15" width="46" height="2.5" rx="1" fill="<?= $txt ?>66"/>
                        <rect x="46" y="24" width="76" height="1" rx="0.5" fill="<?= $pri ?>44"/>
                        <rect x="46" y="28" width="50" height="1.5" rx="1" fill="<?= $txt ?>44"/>
                        <rect x="46" y="32" width="62" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="46" y="36" width="40" height="1.5" rx="1" fill="<?= $txt ?>22"/>
                        <rect x="46" y="44" width="70" height="1.5" rx="1" fill="<?= $pri ?>66"/>
                        <rect x="46" y="49" width="55" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="46" y="54" width="65" height="1.5" rx="1" fill="<?= $txt ?>22"/>
                        <rect x="46" y="59" width="38" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                    </svg>
                    <?php elseif ($ls === 'banner' || $ls === 'full-header'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130 100">
                        <rect width="130" height="100" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="130" height="32" fill="<?= $pri ?>22"/>
                        <rect x="0" y="30" width="130" height="2" fill="<?= $pri ?>"/>
                        <rect x="10" y="6" width="56" height="6" rx="2.5" fill="<?= $pri ?>"/>
                        <rect x="10" y="15" width="38" height="3" rx="1.5" fill="<?= $txt ?>88"/>
                        <circle cx="112" cy="16" r="11" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="1.2"/>
                        <rect x="10" y="40" width="38" height="2.5" rx="1" fill="<?= $pri ?>88"/>
                        <rect x="10" y="46" width="110" height="1.5" rx="1" fill="<?= $txt ?>44"/>
                        <rect x="10" y="51" width="90" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="10" y="60" width="38" height="2.5" rx="1" fill="<?= $sec ?>88"/>
                        <rect x="10" y="66" width="106" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="10" y="71" width="76" height="1.5" rx="1" fill="<?= $txt ?>22"/>
                    </svg>
                    <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130 100">
                        <rect width="130" height="100" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="130" height="24" fill="<?= $surf ?>"/>
                        <rect x="0" y="22" width="130" height="2" fill="<?= $pri ?>"/>
                        <rect x="10" y="6" width="62" height="5" rx="2" fill="<?= $pri ?>"/>
                        <rect x="10" y="15" width="100" height="2" rx="1" fill="<?= $txt ?>44"/>
                        <rect x="10" y="32" width="30" height="2" rx="1" fill="<?= $pri ?>88"/>
                        <rect x="0" y="36" width="130" height="0.8" fill="<?= $pri ?>33"/>
                        <rect x="10" y="41" width="110" height="1.5" rx="1" fill="<?= $txt ?>44"/>
                        <rect x="10" y="46" width="90" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="10" y="51" width="100" height="1.5" rx="1" fill="<?= $txt ?>22"/>
                        <rect x="10" y="60" width="30" height="2" rx="1" fill="<?= $sec ?>88"/>
                        <rect x="0" y="64" width="130" height="0.8" fill="<?= $sec ?>33"/>
                        <rect x="10" y="69" width="80" height="1.5" rx="1" fill="<?= $txt ?>44"/>
                        <rect x="10" y="74" width="95" height="1.5" rx="1" fill="<?= $txt ?>33"/>
                        <rect x="10" y="79" width="62" height="1.5" rx="1" fill="<?= $txt ?>22"/>
                    </svg>
                    <?php endif; ?>
                </div>
                <div class="rxnf-tpl-name"><?= $name ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reasons -->
    <div class="rxnf-reasons">
        <div class="rxnf-reasons-title">Why might this happen?</div>
        <ul>
            <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                The resume was deleted by its owner.
            </li>
            <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                The link is incorrect or the resume ID doesn't exist.
            </li>
            <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                You may be logged in with a different account than the resume owner.
            </li>
        </ul>
    </div>

</div>
<?php View::end(); ?>
