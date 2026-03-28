<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
/* ── Wrapper ──────────────────────────────────────────────────── */
.rxt-wrap {
    padding: 36px 24px 60px;
}

/* ── Back link ────────────────────────────────────────────────── */
.rxt-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    margin-bottom: 32px;
    transition: color 0.2s;
}
.rxt-back:hover { color: var(--cyan); text-decoration: none; }
.rxt-back svg { flex-shrink: 0; }

/* ── Page header ──────────────────────────────────────────────── */
.rxt-header {
    text-align: center;
    margin-bottom: 40px;
}
.rxt-header h1 {
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.15;
    background: linear-gradient(135deg, var(--cyan) 0%, var(--purple) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 10px;
}
.rxt-header p {
    color: var(--text-secondary);
    font-size: 1rem;
    margin: 0;
}

/* ── Filter tabs ──────────────────────────────────────────────── */
.rxt-filters {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.rxt-filter-btn {
    padding: 7px 18px;
    border-radius: 30px;
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-secondary);
    font-size: 0.82rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.2px;
}
.rxt-filter-btn:hover {
    border-color: rgba(0, 240, 255, 0.35);
    color: var(--cyan);
    background: rgba(0, 240, 255, 0.05);
}
.rxt-filter-btn.active {
    background: linear-gradient(135deg, rgba(0,240,255,0.15), rgba(153,69,255,0.15));
    border-color: rgba(0, 240, 255, 0.4);
    color: var(--cyan);
}

/* ── Theme count badge ────────────────────────────────────────── */
.rxt-count {
    margin-left: auto;
    font-size: 0.78rem;
    color: var(--text-secondary);
}
.rxt-count span {
    color: var(--cyan);
    font-weight: 700;
}

/* ── Template grid ────────────────────────────────────────────── */
.rxt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 40px;
}

/* ── Template card (anchor) ───────────────────────────────────── */
.rxt-card {
    position: relative;
    border-radius: 14px;
    border: 2px solid var(--border-color);
    background: var(--bg-card);
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    overflow: hidden;
    text-decoration: none;
    display: block;
}
.rxt-card:hover {
    transform: translateY(-4px);
    border-color: rgba(0, 240, 255, 0.3);
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(0, 240, 255, 0.1);
    text-decoration: none;
}

/* ── Color preview box ────────────────────────────────────────── */
.rxt-preview {
    height: 150px;
    position: relative;
    overflow: hidden;
}

/* ── Card info row ────────────────────────────────────────────── */
.rxt-card-info {
    padding: 13px 14px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.rxt-card-name {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rxt-cat-badge {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    padding: 3px 9px;
    border-radius: 20px;
    flex-shrink: 0;
    white-space: nowrap;
}
.rxt-pro-badge {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 20px;
    background: linear-gradient(135deg, rgba(0,240,255,0.2), rgba(153,69,255,0.2));
    border: 1px solid rgba(153,69,255,0.35);
    color: #a78bfa;
    flex-shrink: 0;
    white-space: nowrap;
}

/* category badge colours */
.rxt-cat-dark         { background: rgba(0,240,255,0.1);    color: #00f0ff; border: 1px solid rgba(0,240,255,0.2); }
.rxt-cat-light        { background: rgba(255,255,255,0.08); color: #cbd5e1; border: 1px solid rgba(203,213,225,0.2); }
.rxt-cat-professional { background: rgba(99,102,241,0.12);  color: #818cf8; border: 1px solid rgba(99,102,241,0.2); }
.rxt-cat-creative     { background: rgba(168,85,247,0.12);  color: #c084fc; border: 1px solid rgba(168,85,247,0.2); }
.rxt-cat-tech         { background: rgba(255,46,196,0.1);   color: #ff2ec4; border: 1px solid rgba(255,46,196,0.2); }
.rxt-cat-nature       { background: rgba(34,197,94,0.1);    color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }
.rxt-cat-warm         { background: rgba(245,158,11,0.1);   color: #fbbf24; border: 1px solid rgba(245,158,11,0.2); }
.rxt-cat-pastel       { background: rgba(244,63,94,0.1);    color: #fb7185; border: 1px solid rgba(244,63,94,0.2); }
.rxt-cat-classic      { background: rgba(146,64,14,0.12);   color: #d97706; border: 1px solid rgba(146,64,14,0.2); }
.rxt-cat-bold         { background: rgba(220,38,38,0.1);    color: #f87171; border: 1px solid rgba(220,38,38,0.2); }
.rxt-cat-other        { background: rgba(107,114,128,0.12); color: #9ca3af; border: 1px solid rgba(107,114,128,0.2); }

/* ── Colour variant dots ──────────────────────────────────────── */
.rxt-variants {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 0 14px 12px;
}
.rxt-vdot {
    width: 17px;
    height: 17px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    transition: transform 0.15s, border-color 0.15s, box-shadow 0.15s;
    padding: 0;
    flex-shrink: 0;
    outline: none;
    display: block;
}
.rxt-vdot:hover {
    transform: scale(1.3);
}
.rxt-vdot.rxt-vdot-active {
    border-color: rgba(255, 255, 255, 0.9);
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.25), 0 2px 8px rgba(0, 0, 0, 0.4);
    transform: scale(1.2);
}

/* ── No results ───────────────────────────────────────────────── */
.rxt-empty {
    display: none;
    grid-column: 1 / -1;
    text-align: center;
    padding: 48px 24px;
    color: var(--text-secondary);
}
.rxt-empty svg { margin-bottom: 12px; opacity: 0.4; }
.rxt-empty p { margin: 0; font-size: 0.9rem; }

/* ── Responsive ───────────────────────────────────────────────── */
@media (max-width: 640px) {
    .rxt-wrap { padding: 24px 16px 60px; }
    .rxt-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .rxt-count { display: none; }
}
@media (max-width: 380px) {
    .rxt-grid { grid-template-columns: 1fr 1fr; }
    .rxt-preview { height: 120px; }
}
</style>

<div class="rxt-wrap">

    <a href="/projects/resumex" class="rxt-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to Dashboard
    </a>

    <div class="rxt-header">
        <h1>Resume Templates</h1>
        <p><?= count($allThemes) ?> professionally designed themes — click to start building.</p>
    </div>



    <!-- Filter tabs -->
    <div class="rxt-filters">
        <button type="button" class="rxt-filter-btn active" data-filter="all">All</button>
        <button type="button" class="rxt-filter-btn" data-filter="dark">Dark</button>
        <button type="button" class="rxt-filter-btn" data-filter="light">Light</button>
        <button type="button" class="rxt-filter-btn" data-filter="professional">Professional</button>
        <button type="button" class="rxt-filter-btn" data-filter="creative">Creative</button>
        <button type="button" class="rxt-filter-btn" data-filter="custom">Custom</button>
        <button type="button" class="rxt-filter-btn" data-filter="other">Other</button>
        <span class="rxt-count">
            <span id="rxtVisibleCount"><?= count($allThemes) ?></span> / <?= count($allThemes) ?> templates
        </span>
    </div>

    <!-- Template grid -->
    <div class="rxt-grid" id="rxtGrid">
        <?php
        $creativeCategories  = ['creative', 'tech', 'bold'];
        $lightCategories     = ['light'];
        $darkCategories      = ['dark'];
        $proCategories       = ['professional'];

        foreach ($allThemes as $themeKey => $theme):
            $cat = strtolower($theme['category'] ?? 'other');
            if (!empty($theme['_full_template'])) {
                $filterGroup = 'custom';
            } elseif (in_array($cat, $creativeCategories)) {
                $filterGroup = 'creative';
            } elseif (in_array($cat, $lightCategories)) {
                $filterGroup = 'light';
            } elseif (in_array($cat, $darkCategories)) {
                $filterGroup = 'dark';
            } elseif (in_array($cat, $proCategories)) {
                $filterGroup = 'professional';
            } else {
                $filterGroup = 'other';
            }

            $bg  = htmlspecialchars($theme['backgroundColor'] ?? '#111');
            $surf= htmlspecialchars($theme['surfaceColor'] ?? '#1a1a2e');
            $pri = htmlspecialchars($theme['primaryColor'] ?? '#00f0ff');
            $sec = htmlspecialchars($theme['secondaryColor'] ?? '#9945ff');
            $txt = htmlspecialchars($theme['textColor'] ?? '#e0e6ff');
            $ls  = $theme['layoutStyle'] ?? 'single';

            $catClass = 'rxt-cat-' . (in_array($cat, ['dark','light','professional','creative','tech','nature','warm','pastel','classic','bold']) ? $cat : 'other');
            $isPro = !empty($theme['_is_pro']);

            // Build create link pre-selecting this template
            $createUrl = '/projects/resumex/create?template=' . urlencode($themeKey);
        ?>
        <a  class="rxt-card"
            href="<?= $createUrl ?>"
            data-filter-group="<?= $filterGroup ?>"
            data-key="<?= htmlspecialchars($themeKey) ?>"
            data-primary="<?= $pri ?>"
            data-secondary="<?= $sec ?>"
            data-name="<?= htmlspecialchars($theme['name']) ?>"
            title="<?= htmlspecialchars($theme['name']) ?> — click to use this template"
        >
            <!-- SVG preview thumbnail -->
            <div class="rxt-preview" style="background:<?= $bg ?>;">
                <?php if (!empty($theme['_full_template'])): ?>
                    <?php if (!empty($theme['_preview_image'])): ?>
                    <img src="<?= htmlspecialchars($theme['_preview_image']) ?>" alt="preview"
                         style="width:100%;height:100%;object-fit:cover;display:block;">
                    <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="170" height="35" fill="<?= $pri ?>"/>
                        <rect x="10" y="11" width="80" height="7" rx="3" fill="#ffffff88"/>
                        <rect x="10" y="21" width="50" height="4" rx="2" fill="#ffffff55"/>
                        <rect x="10" y="45" width="40" height="3" rx="1.5" fill="<?= $pri ?>cc"/>
                        <rect x="10" y="53" width="150" height="2" rx="1" fill="<?= $pri ?>40"/>
                        <rect x="10" y="60" width="145" height="2" rx="1" fill="#ffffff22"/>
                        <rect x="10" y="65" width="120" height="2" rx="1" fill="#ffffff18"/>
                        <rect x="10" y="80" width="40" height="3" rx="1.5" fill="<?= $pri ?>cc"/>
                        <rect x="10" y="88" width="150" height="2" rx="1" fill="#ffffff22"/>
                        <rect x="10" y="93" width="130" height="2" rx="1" fill="#ffffff18"/>
                        <rect x="10" y="98" width="100" height="2" rx="1" fill="#ffffff14"/>
                        <text x="85" y="130" text-anchor="middle" font-size="9" fill="<?= $pri ?>99" font-family="sans-serif">Custom Template</text>
                    </svg>
                    <?php endif; ?>
                <?php elseif ($ls === 'sidebar-left' || $ls === 'sidebar-dark'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="0" y="0" width="52" height="140" fill="<?= $surf ?>"/>
                    <circle cx="26" cy="22" r="11" fill="<?= $pri ?>44" stroke="<?= $pri ?>" stroke-width="1.5"/>
                    <rect x="8" y="37" width="36" height="4" rx="2" fill="<?= $pri ?>cc"/>
                    <rect x="12" y="44" width="28" height="2.5" rx="1.5" fill="<?= $pri ?>66"/>
                    <rect x="8" y="54" width="20" height="2" rx="1" fill="<?= $pri ?>88"/>
                    <rect x="8" y="59" width="36" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="8" y="64" width="30" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="8" y="74" width="20" height="2" rx="1" fill="<?= $sec ?>88"/>
                    <rect x="8" y="79" width="34" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="8" y="84" width="26" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="60" y="10" width="90" height="5" rx="2" fill="<?= $pri ?>"/>
                    <rect x="60" y="18" width="60" height="3" rx="1.5" fill="<?= $txt ?>66"/>
                    <rect x="60" y="28" width="100" height="1.5" rx="1" fill="<?= $pri ?>44"/>
                    <rect x="60" y="35" width="65" height="2" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="60" y="40" width="80" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="60" y="45" width="55" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="60" y="55" width="90" height="2" rx="1" fill="<?= $pri ?>66"/>
                    <rect x="60" y="61" width="70" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="60" y="66" width="80" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="60" y="71" width="50" height="2" rx="1" fill="<?= $txt ?>33"/>
                </svg>
                <?php elseif ($ls === 'banner'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="0" y="0" width="170" height="46" fill="<?= $pri ?>22"/>
                    <rect x="0" y="43" width="170" height="3" fill="<?= $pri ?>"/>
                    <rect x="12" y="8" width="75" height="8" rx="3" fill="<?= $pri ?>"/>
                    <rect x="12" y="19" width="50" height="4" rx="2" fill="<?= $txt ?>88"/>
                    <circle cx="148" cy="22" r="15" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="1.5"/>
                    <rect x="12" y="30" width="22" height="6" rx="3" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="0.5"/>
                    <rect x="38" y="30" width="28" height="6" rx="3" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="0.5"/>
                    <rect x="12" y="55" width="50" height="3" rx="1.5" fill="<?= $pri ?>88"/>
                    <rect x="12" y="62" width="146" height="2" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="12" y="67" width="120" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="78" width="50" height="3" rx="1.5" fill="<?= $sec ?>88"/>
                    <rect x="12" y="84" width="140" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="89" width="100" height="2" rx="1" fill="<?= $txt ?>22"/>
                </svg>
                <?php elseif ($ls === 'full-header'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="0" y="0" width="170" height="40" fill="<?= $surf ?>"/>
                    <rect x="0" y="37" width="170" height="3" fill="<?= $pri ?>"/>
                    <circle cx="20" cy="20" r="12" fill="<?= $pri ?>44" stroke="<?= $pri ?>" stroke-width="1.5"/>
                    <rect x="36" y="9" width="70" height="7" rx="2" fill="<?= $pri ?>"/>
                    <rect x="36" y="19" width="45" height="3.5" rx="1.5" fill="<?= $txt ?>66"/>
                    <rect x="36" y="25" width="95" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="0" y="43" width="62" height="94" fill="<?= $surf ?>11"/>
                    <rect x="62" y="43" width="1.5" height="94" fill="<?= $pri ?>22"/>
                    <rect x="8" y="51" width="30" height="2.5" rx="1.5" fill="<?= $pri ?>88"/>
                    <rect x="8" y="57" width="46" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="8" y="62" width="38" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="8" y="72" width="30" height="2.5" rx="1.5" fill="<?= $sec ?>88"/>
                    <rect x="8" y="78" width="46" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="70" y="51" width="50" height="2.5" rx="1.5" fill="<?= $pri ?>88"/>
                    <rect x="70" y="57" width="90" height="2" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="70" y="62" width="80" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="70" y="67" width="60" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="70" y="77" width="50" height="2.5" rx="1.5" fill="<?= $sec ?>88"/>
                    <rect x="70" y="83" width="85" height="2" rx="1" fill="<?= $txt ?>33"/>
                </svg>
                <?php elseif ($ls === 'timeline'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="0" y="0" width="170" height="34" fill="<?= $surf ?>"/>
                    <rect x="0" y="31" width="170" height="3" fill="<?= $pri ?>"/>
                    <rect x="12" y="8" width="65" height="7" rx="2" fill="<?= $pri ?>"/>
                    <rect x="12" y="18" width="42" height="3.5" rx="1.5" fill="<?= $txt ?>66"/>
                    <line x1="24" y1="42" x2="24" y2="130" stroke="<?= $pri ?>44" stroke-width="2"/>
                    <circle cx="24" cy="48" r="4" fill="<?= $pri ?>"/>
                    <rect x="34" y="45" width="60" height="3.5" rx="1.5" fill="<?= $txt ?>cc"/>
                    <rect x="34" y="51" width="45" height="2.5" rx="1" fill="<?= $pri ?>99"/>
                    <rect x="34" y="56" width="90" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <circle cx="24" cy="71" r="4" fill="<?= $pri ?>"/>
                    <rect x="34" y="68" width="55" height="3.5" rx="1.5" fill="<?= $txt ?>cc"/>
                    <rect x="34" y="74" width="40" height="2.5" rx="1" fill="<?= $pri ?>99"/>
                    <rect x="34" y="79" width="80" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <circle cx="24" cy="93" r="4" fill="<?= $sec ?>"/>
                    <rect x="34" y="90" width="50" height="3.5" rx="1.5" fill="<?= $txt ?>cc"/>
                    <rect x="34" y="96" width="38" height="2.5" rx="1" fill="<?= $sec ?>99"/>
                </svg>
                <?php elseif ($ls === 'minimal'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="12" y="10" width="95" height="9" rx="2" fill="<?= $pri ?>"/>
                    <rect x="12" y="22" width="58" height="4" rx="2" fill="<?= $txt ?>88"/>
                    <rect x="12" y="30" width="146" height="2" rx="1" fill="<?= $pri ?>66"/>
                    <rect x="12" y="35" width="100" height="2.5" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="12" y="41" width="80" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="55" width="42" height="2.5" rx="1" fill="<?= $pri ?>cc"/>
                    <rect x="12" y="61" width="130" height="2" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="12" y="66" width="110" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="71" width="120" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="12" y="83" width="42" height="2.5" rx="1" fill="<?= $sec ?>cc"/>
                    <rect x="12" y="89" width="120" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="94" width="90" height="2" rx="1" fill="<?= $txt ?>22"/>
                </svg>
                <?php elseif ($ls === 'developer'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="0" y="0" width="170" height="38" fill="<?= $surf ?>"/>
                    <rect x="0" y="35" width="170" height="3" fill="<?= $pri ?>44"/>
                    <rect x="10" y="7" width="10" height="6" rx="1" fill="<?= $pri ?>44"/>
                    <rect x="22" y="7" width="68" height="9" rx="2" fill="<?= $pri ?>"/>
                    <rect x="10" y="19" width="14" height="3" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="26" y="19" width="52" height="3" rx="1" fill="<?= $txt ?>66"/>
                    <rect x="10" y="8" width="1.5" height="75" fill="<?= $pri ?>66"/>
                    <rect x="18" y="46" width="30" height="3.5" rx="1" fill="<?= $pri ?>88"/>
                    <rect x="18" y="53" width="130" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="18" y="58" width="100" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="18" y="68" width="30" height="3.5" rx="1" fill="<?= $sec ?>88"/>
                    <rect x="18" y="75" width="120" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="18" y="80" width="90" height="2" rx="1" fill="<?= $txt ?>22"/>
                </svg>
                <?php elseif ($ls === 'academic'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="40" y="8" width="90" height="9" rx="2" fill="<?= $txt ?>dd"/>
                    <rect x="60" y="20" width="50" height="4" rx="2" fill="<?= $pri ?>99"/>
                    <rect x="30" y="28" width="110" height="2" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="0" y="32" width="170" height="3" fill="<?= $pri ?>"/>
                    <rect x="50" y="37" width="70" height="1.5" fill="<?= $pri ?>22"/>
                    <rect x="12" y="47" width="52" height="3" rx="1.5" fill="<?= $pri ?>aa"/>
                    <rect x="12" y="51" width="146" height="1.5" fill="<?= $pri ?>33"/>
                    <rect x="12" y="56" width="130" height="2" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="12" y="61" width="110" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="74" width="52" height="3" rx="1.5" fill="<?= $sec ?>aa"/>
                    <rect x="12" y="78" width="146" height="1.5" fill="<?= $sec ?>33"/>
                    <rect x="12" y="83" width="120" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="88" width="90" height="2" rx="1" fill="<?= $txt ?>22"/>
                </svg>
                <?php else: /* single / default */ ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                    <rect width="170" height="140" fill="<?= $bg ?>"/>
                    <rect x="0" y="0" width="170" height="40" fill="<?= $surf ?>"/>
                    <rect x="0" y="37" width="170" height="3" fill="<?= $pri ?>"/>
                    <circle cx="20" cy="20" r="12" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="1.5"/>
                    <rect x="36" y="9" width="68" height="7" rx="2" fill="<?= $pri ?>"/>
                    <rect x="36" y="19" width="46" height="3.5" rx="1.5" fill="<?= $txt ?>66"/>
                    <rect x="36" y="26" width="90" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="48" width="50" height="2.5" rx="1.5" fill="<?= $pri ?>88"/>
                    <rect x="12" y="54" width="146" height="2" rx="1" fill="<?= $txt ?>44"/>
                    <rect x="12" y="59" width="120" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="64" width="100" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="12" y="76" width="50" height="2.5" rx="1.5" fill="<?= $sec ?>88"/>
                    <rect x="12" y="82" width="140" height="2" rx="1" fill="<?= $txt ?>33"/>
                    <rect x="12" y="87" width="110" height="2" rx="1" fill="<?= $txt ?>22"/>
                    <rect x="12" y="92" width="80" height="2" rx="1" fill="<?= $txt ?>18"/>
                </svg>
                <?php endif; ?>

                <?php if ($isPro): ?>
                <span style="position:absolute;top:8px;right:8px;background:linear-gradient(135deg,rgba(0,240,255,0.85),rgba(153,69,255,0.85));color:#06060a;font-size:0.6rem;font-weight:800;letter-spacing:0.6px;padding:2px 7px;border-radius:10px;text-transform:uppercase;">PRO</span>
                <?php endif; ?>
            </div>

            <!-- Card info -->
            <div class="rxt-card-info">
                <span class="rxt-card-name"><?= htmlspecialchars($theme['name']) ?></span>
                <span class="rxt-cat-badge <?= $catClass ?>"><?= htmlspecialchars(ucfirst($cat)) ?></span>
            </div>

            <?php if (!empty($theme['colorVariants'])): ?>
            <!-- Colour variant dots (live-update the SVG preview) -->
            <div class="rxt-variants" onclick="event.preventDefault();">
                <?php foreach ($theme['colorVariants'] as $vi => $variant): ?>
                <button type="button"
                        class="rxt-vdot<?= $vi === 0 ? ' rxt-vdot-active' : '' ?>"
                        data-pri="<?= htmlspecialchars($variant['primary']) ?>"
                        data-sec="<?= htmlspecialchars($variant['secondary']) ?>"
                        title="<?= htmlspecialchars($variant['label']) ?>"
                        style="background: linear-gradient(135deg, <?= htmlspecialchars($variant['primary']) ?>, <?= htmlspecialchars($variant['secondary']) ?>);"
                ></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>

        <!-- Empty state -->
        <div class="rxt-empty" id="rxtEmpty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <p>No templates match this filter.</p>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var grid       = document.getElementById('rxtGrid');
    var emptyMsg   = document.getElementById('rxtEmpty');
    var cards      = Array.from(grid.querySelectorAll('.rxt-card[data-key]'));
    var filterBtns = Array.from(document.querySelectorAll('.rxt-filter-btn'));
    var countEl    = document.getElementById('rxtVisibleCount');

    // ── Filter logic ──────────────────────────────────────────
    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var filter = this.dataset.filter;
            var visible = 0;

            cards.forEach(function (card) {
                var show = filter === 'all' || card.dataset.filterGroup === filter;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            countEl.textContent = visible;
            emptyMsg.style.display = visible === 0 ? 'grid' : 'none';

            filterBtns.forEach(function (b) {
                b.classList.toggle('active', b.dataset.filter === filter);
            });
        });
    });

    // ── Colour variant dots (update SVG, update card href) ────
    function escRe(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    function makeColorRe(color) {
        return new RegExp(escRe(color) + '([0-9a-fA-F]{2})?(?=[^0-9a-fA-F]|$)', 'gi');
    }

    cards.forEach(function (card) {
        var dots = Array.from(card.querySelectorAll('.rxt-vdot'));
        dots.forEach(function (dot) {
            dot.addEventListener('click', function (e) {
                e.preventDefault(); // don't navigate
                e.stopPropagation();

                var newPri = this.dataset.pri;
                var newSec = this.dataset.sec;
                var oldPri = card.dataset.primary;
                var oldSec = card.dataset.secondary;

                dots.forEach(function (d) { d.classList.remove('rxt-vdot-active'); });
                this.classList.add('rxt-vdot-active');

                if (newPri !== oldPri || newSec !== oldSec) {
                    var preview = card.querySelector('.rxt-preview');
                    if (preview) {
                        var html = preview.innerHTML;
                        html = html.replace(makeColorRe(oldPri), function (m, alpha) { return newPri + (alpha || ''); });
                        html = html.replace(makeColorRe(oldSec), function (m, alpha) { return newSec + (alpha || ''); });
                        preview.innerHTML = html;
                    }
                    card.dataset.primary   = newPri;
                    card.dataset.secondary = newSec;

                    // Update create link with chosen colors
                    var key = card.dataset.key;
                    card.href = '/projects/resumex/create?template=' + encodeURIComponent(key)
                              + '&color_primary=' + encodeURIComponent(newPri)
                              + '&color_secondary=' + encodeURIComponent(newSec);
                }
            });
        });
    });
}());
</script>
<?php View::end(); ?>
