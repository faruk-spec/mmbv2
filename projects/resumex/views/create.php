<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
/* ── Wrapper ──────────────────────────────────────────────────── */
.rxc-wrap {
    padding: 36px 24px 60px;
}

/* ── Back link ────────────────────────────────────────────────── */
.rxc-back {
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
.rxc-back:hover { color: var(--cyan); text-decoration: none; }
.rxc-back svg { flex-shrink: 0; }

/* ── Page header ──────────────────────────────────────────────── */
.rxc-header {
    text-align: center;
    margin-bottom: 40px;
}
.rxc-header h1 {
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
.rxc-header p {
    color: var(--text-secondary);
    font-size: 1rem;
    margin: 0;
}

/* ── Resume name input ────────────────────────────────────────── */
.rxc-name-row {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px 28px;
    margin-bottom: 32px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rxc-name-row:focus-within {
    border-color: rgba(0, 240, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.08);
}
.rxc-name-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-secondary);
    margin-bottom: 10px;
}
.rxc-name-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.rxc-name-icon {
    position: absolute;
    left: 16px;
    color: var(--cyan);
    opacity: 0.7;
    pointer-events: none;
    display: flex;
}
.rxc-name-input {
    width: 100%;
    padding: 13px 16px 13px 46px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rxc-name-input::placeholder { color: var(--text-secondary); opacity: 0.6; }
.rxc-name-input:focus {
    border-color: rgba(0, 240, 255, 0.5);
    box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.07);
}

/* ── Filter tabs ──────────────────────────────────────────────── */
.rxc-filters {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.rxc-filter-btn {
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
.rxc-filter-btn:hover {
    border-color: rgba(0, 240, 255, 0.35);
    color: var(--cyan);
    background: rgba(0, 240, 255, 0.05);
}
.rxc-filter-btn.active {
    background: linear-gradient(135deg, rgba(0,240,255,0.15), rgba(153,69,255,0.15));
    border-color: rgba(0, 240, 255, 0.4);
    color: var(--cyan);
}

/* ── Theme count badge ────────────────────────────────────────── */
.rxc-count {
    margin-left: auto;
    font-size: 0.78rem;
    color: var(--text-secondary);
}
.rxc-count span {
    color: var(--cyan);
    font-weight: 700;
}

/* ── Template grid ────────────────────────────────────────────── */
.rxc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 40px;
}

/* ── Template card ────────────────────────────────────────────── */
.rxc-card {
    position: relative;
    border-radius: 14px;
    border: 2px solid var(--border-color);
    background: var(--bg-card);
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    overflow: hidden;
    user-select: none;
}
.rxc-card:hover {
    transform: translateY(-4px);
    border-color: rgba(0, 240, 255, 0.3);
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(0, 240, 255, 0.1);
}
.rxc-card.selected {
    border-color: var(--cyan) !important;
    box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.18), 0 12px 36px rgba(0, 0, 0, 0.4);
    transform: translateY(-4px);
}
/* hidden radio */
.rxc-card input[type="radio"] {
    position: absolute;
    width: 1px; height: 1px;
    opacity: 0; pointer-events: none;
}

/* ── Selected check badge ─────────────────────────────────────── */
.rxc-check {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 24px; height: 24px;
    border-radius: 50%;
    background: var(--cyan);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.5);
    transition: opacity 0.2s, transform 0.2s;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(0, 240, 255, 0.5);
}
.rxc-card.selected .rxc-check {
    opacity: 1;
    transform: scale(1);
}

/* ── Color preview box ────────────────────────────────────────── */
.rxc-preview {
    height: 150px;
    position: relative;
    overflow: hidden;
    border-radius: 0;
    flex-shrink: 0;
}

/* mini resume mockup inside preview */
.rxc-mockup {
    position: absolute;
    inset: 10px;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px rgba(0,0,0,0.35);
}
.rxc-mockup-header {
    height: 32px;
    display: flex;
    align-items: center;
    padding: 0 10px;
    gap: 6px;
    flex-shrink: 0;
}
.rxc-mockup-avatar {
    width: 18px; height: 18px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    flex-shrink: 0;
}
.rxc-mockup-title-block {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
    min-width: 0;
}
.rxc-mockup-name-bar {
    height: 5px;
    border-radius: 3px;
    background: rgba(255,255,255,0.85);
    width: 55%;
}
.rxc-mockup-sub-bar {
    height: 3px;
    border-radius: 3px;
    background: rgba(255,255,255,0.4);
    width: 38%;
}
.rxc-mockup-body {
    flex: 1;
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.rxc-mockup-section-label {
    height: 4px;
    border-radius: 3px;
    width: 40%;
    margin-bottom: 2px;
}
.rxc-mockup-line {
    height: 3px;
    border-radius: 3px;
    background: rgba(128,128,128,0.35);
    display: flex;
    align-items: center;
    gap: 5px;
}
.rxc-mockup-bullet {
    width: 4px; height: 4px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-left: -5px;
}
.rxc-mockup-line-fill {
    height: 3px;
    border-radius: 3px;
    background: rgba(128,128,128,0.35);
    flex: 1;
}

/* ── Card info ────────────────────────────────────────────────── */
.rxc-card-info {
    padding: 13px 14px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.rxc-card-name {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rxc-cat-badge {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    padding: 3px 9px;
    border-radius: 20px;
    flex-shrink: 0;
    white-space: nowrap;
}

/* category badge colours */
.rxc-cat-dark       { background: rgba(0,240,255,0.1);   color: #00f0ff; border: 1px solid rgba(0,240,255,0.2); }
.rxc-cat-light      { background: rgba(255,255,255,0.08); color: #cbd5e1; border: 1px solid rgba(203,213,225,0.2); }
.rxc-cat-professional { background: rgba(99,102,241,0.12); color: #818cf8; border: 1px solid rgba(99,102,241,0.2); }
.rxc-cat-creative   { background: rgba(168,85,247,0.12); color: #c084fc; border: 1px solid rgba(168,85,247,0.2); }
.rxc-cat-tech       { background: rgba(255,46,196,0.1);  color: #ff2ec4; border: 1px solid rgba(255,46,196,0.2); }
.rxc-cat-nature     { background: rgba(34,197,94,0.1);   color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }
.rxc-cat-warm       { background: rgba(245,158,11,0.1);  color: #fbbf24; border: 1px solid rgba(245,158,11,0.2); }
.rxc-cat-pastel     { background: rgba(244,63,94,0.1);   color: #fb7185; border: 1px solid rgba(244,63,94,0.2); }
.rxc-cat-classic    { background: rgba(146,64,14,0.12);  color: #d97706; border: 1px solid rgba(146,64,14,0.2); }
.rxc-cat-bold       { background: rgba(220,38,38,0.1);   color: #f87171; border: 1px solid rgba(220,38,38,0.2); }
.rxc-cat-other      { background: rgba(107,114,128,0.12); color: #9ca3af; border: 1px solid rgba(107,114,128,0.2); }

/* ── Colour variant dots ──────────────────────────────────────── */
.rxc-variants {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 0 14px 12px;
}
.rxc-vdot {
    width: 17px;
    height: 17px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    transition: transform 0.15s, border-color 0.15s, box-shadow 0.15s;
    padding: 0;
    flex-shrink: 0;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    display: block;
}
.rxc-vdot:hover {
    transform: scale(1.3);
}
.rxc-vdot.rxc-vdot-active {
    border-color: rgba(255, 255, 255, 0.9);
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.25), 0 2px 8px rgba(0, 0, 0, 0.4);
    transform: scale(1.2);
}

/* ── No results ───────────────────────────────────────────────── */
.rxc-empty {
    display: none;
    grid-column: 1 / -1;
    text-align: center;
    padding: 48px 24px;
    color: var(--text-secondary);
}
.rxc-empty svg { margin-bottom: 12px; opacity: 0.4; }
.rxc-empty p { margin: 0; font-size: 0.9rem; }

/* ── Submit row ───────────────────────────────────────────────── */
.rxc-submit-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 14px;
    padding: 24px 28px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    position: sticky;
    bottom: 20px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 10;
}
.rxc-selected-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}
.rxc-sel-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,0.2);
}
.rxc-sel-info {
    min-width: 0;
}
.rxc-sel-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--text-secondary);
    font-weight: 600;
}
.rxc-sel-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rxc-btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 12px 22px;
    border-radius: 10px;
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}
.rxc-btn-cancel:hover {
    border-color: rgba(255,255,255,0.2);
    color: var(--text-primary);
    text-decoration: none;
}
.rxc-btn-create {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: #06060a;
    font-size: 0.9rem;
    font-weight: 800;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    box-shadow: 0 4px 20px rgba(0, 240, 255, 0.25);
    letter-spacing: 0.2px;
}
.rxc-btn-create:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0, 240, 255, 0.4);
}
.rxc-btn-create:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none;
}

/* ── Error state ──────────────────────────────────────────────── */
.rxc-error-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: 10px;
    padding: 14px 18px;
    color: #fca5a5;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 24px;
}

/* ── Responsive ───────────────────────────────────────────────── */
@media (max-width: 640px) {
    .rxc-wrap { padding: 24px 16px 80px; }
    .rxc-name-row { padding: 18px 16px; }
    .rxc-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .rxc-submit-row {
        flex-wrap: wrap;
        gap: 10px;
        padding: 16px;
    }
    .rxc-selected-preview { width: 100%; }
    .rxc-btn-cancel, .rxc-btn-create { flex: 1; justify-content: center; }
    .rxc-count { display: none; }
}
@media (max-width: 380px) {
    .rxc-grid { grid-template-columns: 1fr 1fr; }
    .rxc-preview { height: 120px; }
}
</style>

<div class="rxc-wrap">

    <!-- Back link -->
    <a href="/projects/resumex" class="rxc-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to Dashboard
    </a>

    <!-- Error banner -->
    <?php if (!empty($_GET['error'])): ?>
    <div class="rxc-error-banner">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Something went wrong creating your resume. Please try again.
    </div>
    <?php endif; ?>

    <!-- Page header -->
    <div class="rxc-header">
        <h1>Choose Your Template</h1>
        <p>Pick a design that fits your style — you can always change it later.</p>
    </div>

    <!-- Form -->
    <form method="POST" action="/projects/resumex/create" id="rxcForm" novalidate>
        <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
        <input type="hidden" name="color_primary" id="rxcColorPrimary" value="<?= htmlspecialchars(preg_replace('/[^#a-fA-F0-9]/', '', $_GET['color_primary'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="color_secondary" id="rxcColorSecondary" value="<?= htmlspecialchars(preg_replace('/[^#a-fA-F0-9]/', '', $_GET['color_secondary'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <!-- Resume name -->
        <div class="rxc-name-row">
            <label for="rxcTitle" class="rxc-name-label">Resume Name</label>
            <div class="rxc-name-input-wrap">
                <span class="rxc-name-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </span>
                <input
                    type="text"
                    id="rxcTitle"
                    name="title"
                    class="rxc-name-input"
                    placeholder="e.g. Software Engineer Resume"
                    maxlength="120"
                    required
                    autocomplete="off"
                    value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                >
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="rxc-filters">
            <button type="button" class="rxc-filter-btn active" data-filter="all">All</button>
            <button type="button" class="rxc-filter-btn" data-filter="dark">Dark</button>
            <button type="button" class="rxc-filter-btn" data-filter="light">Light</button>
            <button type="button" class="rxc-filter-btn" data-filter="professional">Professional</button>
            <button type="button" class="rxc-filter-btn" data-filter="creative">Creative</button>
            <button type="button" class="rxc-filter-btn" data-filter="custom">Custom</button>
            <button type="button" class="rxc-filter-btn" data-filter="other">Other</button>
            <span class="rxc-count">
                <span id="rxcVisibleCount"><?= count($allThemes) ?></span> / <?= count($allThemes) ?> templates
            </span>
        </div>

        <!-- Template grid -->
        <div class="rxc-grid" id="rxcGrid">
            <?php
            $creativeCategories = ['creative', 'tech', 'bold'];
            $lightCategories    = ['light'];
            $darkCategories     = ['dark'];
            $proCategories      = ['professional'];
            $otherCategories    = ['nature', 'warm', 'pastel', 'classic'];

            $defaultKey = !empty($_GET['template']) && isset($allThemes[$_GET['template']])
                ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['template'])
                : 'ocean-blue';
            foreach ($allThemes as $themeKey => $theme):
                $cat = strtolower($theme['category'] ?? 'other');
                // Determine which filter group this card belongs to
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
                $isSelected = ($themeKey === $defaultKey);
                $catClass = 'rxc-cat-' . (in_array($cat, ['dark','light','professional','creative','tech','nature','warm','pastel','classic','bold']) ? $cat : 'other');
            ?>
            <label
                class="rxc-card<?= $isSelected ? ' selected' : '' ?>"
                data-filter-group="<?= $filterGroup ?>"
                data-key="<?= htmlspecialchars($themeKey) ?>"
                data-primary="<?= $pri ?>"
                data-secondary="<?= $sec ?>"
                data-name="<?= htmlspecialchars($theme['name']) ?>"
            >
                <input
                    type="radio"
                    name="template"
                    value="<?= htmlspecialchars($themeKey) ?>"
                    <?= $isSelected ? 'checked' : '' ?>
                >

                <!-- Selected check mark -->
                <span class="rxc-check" aria-hidden="true">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#06060a" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>

                <!-- Layout-aware SVG thumbnail -->
                <div class="rxc-preview" style="background:<?= $bg ?>;">
                    <?php
                    $ls = $theme['layoutStyle'] ?? 'single';
                    if (!empty($theme['_full_template'])): ?>
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
                        <text x="85" y="130" text-anchor="middle" font-size="9" fill="<?= $pri ?>99" font-family="sans-serif">Full Custom Template</text>
                    </svg>
                    <?php endif; ?>
                    <?php elseif ($ls === 'sidebar-left' || $ls === 'sidebar-dark'): ?>
                    ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="52" height="140" fill="<?= $surf ?>"/>
                        <circle cx="26" cy="22" r="11" fill="<?= $pri ?>44" stroke="<?= $pri ?>" stroke-width="1.5"/>
                        <rect x="8" y="37" width="36" height="4" rx="2" fill="<?= $pri ?>cc"/>
                        <rect x="12" y="44" width="28" height="2.5" rx="1.5" fill="<?= $pri ?>66"/>
                        <rect x="8" y="54" width="20" height="2" rx="1" fill="<?= $pri ?>88"/>
                        <rect x="8" y="59" width="36" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="8" y="64" width="30" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="8" y="74" width="20" height="2" rx="1" fill="<?= $sec ?>88"/>
                        <rect x="8" y="79" width="34" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="8" y="84" width="26" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="60" y="10" width="90" height="5" rx="2" fill="<?= $pri ?>"/>
                        <rect x="60" y="18" width="60" height="3" rx="1.5" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>66"/>
                        <rect x="60" y="28" width="100" height="1.5" rx="1" fill="<?= $pri ?>44"/>
                        <rect x="60" y="35" width="65" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="60" y="40" width="80" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="60" y="45" width="55" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="60" y="55" width="90" height="2" rx="1" fill="<?= $pri ?>66"/>
                        <rect x="60" y="61" width="70" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="60" y="66" width="80" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="60" y="71" width="50" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                    </svg>
                    <?php elseif ($ls === 'banner'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="170" height="46" fill="<?= $pri ?>22"/>
                        <rect x="0" y="43" width="170" height="3" fill="<?= $pri ?>"/>
                        <rect x="12" y="8" width="75" height="8" rx="3" fill="<?= $pri ?>"/>
                        <rect x="12" y="19" width="50" height="4" rx="2" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>88"/>
                        <circle cx="148" cy="22" r="15" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="1.5"/>
                        <rect x="12" y="30" width="22" height="6" rx="3" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="0.5"/>
                        <rect x="38" y="30" width="28" height="6" rx="3" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="0.5"/>
                        <rect x="12" y="55" width="50" height="3" rx="1.5" fill="<?= $pri ?>88"/>
                        <rect x="12" y="62" width="146" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="12" y="67" width="120" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="78" width="50" height="3" rx="1.5" fill="<?= $sec ?>88"/>
                        <rect x="12" y="84" width="140" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="89" width="100" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                    </svg>
                    <?php elseif ($ls === 'full-header'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="170" height="40" fill="<?= $surf ?>"/>
                        <rect x="0" y="37" width="170" height="3" fill="<?= $pri ?>"/>
                        <circle cx="20" cy="20" r="12" fill="<?= $pri ?>44" stroke="<?= $pri ?>" stroke-width="1.5"/>
                        <rect x="36" y="9" width="70" height="7" rx="2" fill="<?= $pri ?>"/>
                        <rect x="36" y="19" width="45" height="3.5" rx="1.5" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>66"/>
                        <rect x="36" y="25" width="95" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="0" y="43" width="62" height="94" fill="<?= $surf ?>11"/>
                        <rect x="62" y="43" width="1.5" height="94" fill="<?= $pri ?>22"/>
                        <rect x="8" y="51" width="30" height="2.5" rx="1.5" fill="<?= $pri ?>88"/>
                        <rect x="8" y="57" width="46" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="8" y="62" width="38" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="8" y="72" width="30" height="2.5" rx="1.5" fill="<?= $sec ?>88"/>
                        <rect x="8" y="78" width="46" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="70" y="51" width="50" height="2.5" rx="1.5" fill="<?= $pri ?>88"/>
                        <rect x="70" y="57" width="90" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="70" y="62" width="80" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="70" y="67" width="60" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="70" y="77" width="50" height="2.5" rx="1.5" fill="<?= $sec ?>88"/>
                        <rect x="70" y="83" width="85" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                    </svg>
                    <?php elseif ($ls === 'timeline'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="170" height="34" fill="<?= $surf ?>"/>
                        <rect x="0" y="31" width="170" height="3" fill="<?= $pri ?>"/>
                        <rect x="12" y="8" width="65" height="7" rx="2" fill="<?= $pri ?>"/>
                        <rect x="12" y="18" width="42" height="3.5" rx="1.5" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>66"/>
                        <line x1="24" y1="42" x2="24" y2="130" stroke="<?= $pri ?>44" stroke-width="2"/>
                        <circle cx="24" cy="48" r="4" fill="<?= $pri ?>"/>
                        <rect x="34" y="45" width="60" height="3.5" rx="1.5" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>cc"/>
                        <rect x="34" y="51" width="45" height="2.5" rx="1" fill="<?= $pri ?>99"/>
                        <rect x="34" y="56" width="90" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <circle cx="24" cy="71" r="4" fill="<?= $pri ?>"/>
                        <rect x="34" y="68" width="55" height="3.5" rx="1.5" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>cc"/>
                        <rect x="34" y="74" width="40" height="2.5" rx="1" fill="<?= $pri ?>99"/>
                        <rect x="34" y="79" width="80" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <circle cx="24" cy="93" r="4" fill="<?= $sec ?>"/>
                        <rect x="34" y="90" width="50" height="3.5" rx="1.5" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>cc"/>
                        <rect x="34" y="96" width="38" height="2.5" rx="1" fill="<?= $sec ?>99"/>
                    </svg>
                    <?php elseif ($ls === 'minimal'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="12" y="10" width="95" height="9" rx="2" fill="<?= $pri ?>"/>
                        <rect x="12" y="22" width="58" height="4" rx="2" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>88"/>
                        <rect x="12" y="30" width="146" height="2" rx="1" fill="<?= $pri ?>66"/>
                        <rect x="12" y="35" width="100" height="2.5" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="12" y="41" width="80" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="55" width="42" height="2.5" rx="1" fill="<?= $pri ?>cc"/>
                        <rect x="12" y="61" width="130" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="12" y="66" width="110" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="71" width="120" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="12" y="83" width="42" height="2.5" rx="1" fill="<?= $sec ?>cc"/>
                        <rect x="12" y="89" width="120" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="94" width="90" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                    </svg>
                    <?php elseif ($ls === 'developer'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="170" height="38" fill="<?= $surf ?>"/>
                        <rect x="0" y="35" width="170" height="3" fill="<?= $pri ?>44"/>
                        <rect x="10" y="7" width="10" height="6" rx="1" fill="<?= $pri ?>44"/>
                        <rect x="22" y="7" width="68" height="9" rx="2" fill="<?= $pri ?>"/>
                        <rect x="10" y="19" width="14" height="3" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="26" y="19" width="52" height="3" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>66"/>
                        <rect x="10" y="8" width="1.5" height="75" fill="<?= $pri ?>66"/>
                        <rect x="18" y="46" width="30" height="3.5" rx="1" fill="<?= $pri ?>88"/>
                        <rect x="18" y="53" width="130" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="18" y="58" width="100" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="18" y="68" width="30" height="3.5" rx="1" fill="<?= $sec ?>88"/>
                        <rect x="18" y="75" width="120" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="18" y="80" width="90" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                    </svg>
                    <?php elseif ($ls === 'academic'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="40" y="8" width="90" height="9" rx="2" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>dd"/>
                        <rect x="60" y="20" width="50" height="4" rx="2" fill="<?= $pri ?>99"/>
                        <rect x="30" y="28" width="110" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="0" y="32" width="170" height="3" fill="<?= $pri ?>"/>
                        <rect x="50" y="37" width="70" height="1.5" fill="<?= $pri ?>22"/>
                        <rect x="12" y="47" width="52" height="3" rx="1.5" fill="<?= $pri ?>aa"/>
                        <rect x="12" y="51" width="146" height="1.5" fill="<?= $pri ?>33"/>
                        <rect x="12" y="56" width="130" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="12" y="61" width="110" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="74" width="52" height="3" rx="1.5" fill="<?= $sec ?>aa"/>
                        <rect x="12" y="78" width="146" height="1.5" fill="<?= $sec ?>33"/>
                        <rect x="12" y="83" width="120" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="88" width="90" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                    </svg>
                    <?php else: /* single / default */ ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 140" style="width:100%;height:100%;display:block;">
                        <rect width="170" height="140" fill="<?= $bg ?>"/>
                        <rect x="0" y="0" width="170" height="40" fill="<?= $surf ?>"/>
                        <rect x="0" y="37" width="170" height="3" fill="<?= $pri ?>"/>
                        <circle cx="20" cy="20" r="12" fill="<?= $pri ?>33" stroke="<?= $pri ?>" stroke-width="1.5"/>
                        <rect x="36" y="9" width="68" height="7" rx="2" fill="<?= $pri ?>"/>
                        <rect x="36" y="19" width="46" height="3.5" rx="1.5" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>66"/>
                        <rect x="36" y="26" width="82" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="48" width="52" height="3" rx="1.5" fill="<?= $pri ?>88"/>
                        <rect x="12" y="52" width="146" height="1.5" fill="<?= $pri ?>33"/>
                        <rect x="12" y="57" width="130" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>44"/>
                        <rect x="12" y="62" width="110" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="67" width="120" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                        <rect x="12" y="79" width="52" height="3" rx="1.5" fill="<?= $sec ?>88"/>
                        <rect x="12" y="83" width="146" height="1.5" fill="<?= $sec ?>22"/>
                        <rect x="12" y="88" width="120" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>33"/>
                        <rect x="12" y="93" width="90" height="2" rx="1" fill="<?= htmlspecialchars($theme['textColor'] ?? '#e0e6ff') ?>22"/>
                    </svg>
                    <?php endif; ?>
                </div>

                <!-- Card info -->
                <div class="rxc-card-info">
                    <span class="rxc-card-name"><?= htmlspecialchars($theme['name']) ?></span>
                    <span class="rxc-cat-badge <?= $catClass ?>">
                        <?= htmlspecialchars(ucfirst($cat)) ?>
                    </span>
                    <?php if (!empty($theme['_is_pro'])): ?>
                    <span style="display:inline-block;padding:1px 7px;border-radius:10px;font-size:0.6rem;font-weight:700;background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.4);margin-left:3px;"><i class="fas fa-star" style="font-size:0.55rem;"></i> PRO</span>
                    <?php endif; ?>
                    <?php if (!empty($theme['_full_template'])): ?>
                    <span style="display:inline-block;padding:1px 7px;border-radius:10px;font-size:0.6rem;font-weight:600;border:1px solid rgba(139,92,246,0.4);color:#a78bfa;margin-left:3px;">Full Design</span>
                    <?php else: ?>
                    <?php $lsLabel = str_replace('-', ' ', $theme['layoutStyle'] ?? 'single'); ?>
                    <span style="display:inline-block;padding:1px 7px;border-radius:10px;font-size:0.6rem;font-weight:600;border:1px solid var(--border-color);color:var(--text-secondary);margin-left:3px;"><?= htmlspecialchars(ucfirst($lsLabel)) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($theme['colorVariants'])): ?>
                <!-- Colour variant circles -->
                <div class="rxc-variants">
                    <?php foreach ($theme['colorVariants'] as $vi => $variant): ?>
                    <button type="button"
                            class="rxc-vdot<?= $vi === 0 ? ' rxc-vdot-active' : '' ?>"
                            data-pri="<?= htmlspecialchars($variant['primary']) ?>"
                            data-sec="<?= htmlspecialchars($variant['secondary']) ?>"
                            title="<?= htmlspecialchars($variant['label']) ?>"
                            style="background: linear-gradient(135deg, <?= htmlspecialchars($variant['primary']) ?>, <?= htmlspecialchars($variant['secondary']) ?>);"
                    ></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </label>
            <?php endforeach; ?>

            <!-- Empty state (shown via JS) -->
            <div class="rxc-empty" id="rxcEmpty">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <p>No templates match this filter.</p>
            </div>
        </div>

        <!-- Sticky submit row -->
        <div class="rxc-submit-row">
            <div class="rxc-selected-preview" id="rxcSelPreview">
                <div class="rxc-sel-dot" id="rxcSelDot" style="background: #00f0ff;"></div>
                <div class="rxc-sel-info">
                    <div class="rxc-sel-label">Selected Template</div>
                    <div class="rxc-sel-name" id="rxcSelName"><?= htmlspecialchars($allThemes[$defaultKey]['name'] ?? 'Ocean Blue', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
            <a href="/projects/resumex" class="rxc-btn-cancel">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Cancel
            </a>
            <button type="submit" class="rxc-btn-create" id="rxcSubmit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create Resume
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    'use strict';

    const form      = document.getElementById('rxcForm');
    const titleInput= document.getElementById('rxcTitle');
    const submitBtn = document.getElementById('rxcSubmit');
    const grid      = document.getElementById('rxcGrid');
    const emptyMsg  = document.getElementById('rxcEmpty');
    const cards     = Array.from(grid.querySelectorAll('.rxc-card[data-key]'));
    const filterBtns= Array.from(document.querySelectorAll('.rxc-filter-btn'));
    const countEl   = document.getElementById('rxcVisibleCount');
    const selDot    = document.getElementById('rxcSelDot');
    const selName   = document.getElementById('rxcSelName');
    const colorPrimaryInput   = document.getElementById('rxcColorPrimary');
    const colorSecondaryInput = document.getElementById('rxcColorSecondary');

    let activeFilter = 'all';

    // ── Filter logic ──────────────────────────────────────────
    function applyFilter(filter) {
        activeFilter = filter;
        let visible = 0;

        cards.forEach(card => {
            const group = card.dataset.filterGroup;
            const show = filter === 'all' || group === filter;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        countEl.textContent = visible;
        emptyMsg.style.display = visible === 0 ? 'flex' : 'none';

        filterBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.filter === filter);
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => applyFilter(btn.dataset.filter));
    });

    // ── Card selection ─────────────────────────────────────────
    function selectCard(card) {
        cards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input[type="radio"]').checked = true;

        // Update sticky preview
        selDot.style.background = card.dataset.primary;
        selName.textContent = card.dataset.name;

        // Sync hidden colour inputs
        colorPrimaryInput.value   = card.dataset.primary   || '';
        colorSecondaryInput.value = card.dataset.secondary || '';

        updateSubmit();
    }

    cards.forEach(card => {
        card.addEventListener('click', () => selectCard(card));
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectCard(card);
            }
        });
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'radio');
        card.setAttribute('aria-checked', card.classList.contains('selected') ? 'true' : 'false');
    });

    // ── Colour variant dot selection ───────────────────────────
    function escapeRxcRe(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Build a regex that matches a 6-digit hex colour followed by an optional
    // 2-digit opacity suffix, ensuring the match is not mid-way through a longer
    // hex value (lookahead asserts next char is non-hex or end-of-string).
    function makeColorRe(color) {
        return new RegExp(
            escapeRxcRe(color) + '([0-9a-fA-F]{2})?(?=[^0-9a-fA-F]|$)',
            'gi'
        );
    }

    cards.forEach(function (card) {
        var dots = Array.from(card.querySelectorAll('.rxc-vdot'));
        dots.forEach(function (dot) {
            dot.addEventListener('click', function (e) {
                e.stopPropagation(); // don't bubble to card click handler
                var newPri = this.dataset.pri;
                var newSec = this.dataset.sec;
                var oldPri = card.dataset.primary;
                var oldSec = card.dataset.secondary;

                // Highlight active dot
                dots.forEach(function (d) { d.classList.remove('rxc-vdot-active'); });
                this.classList.add('rxc-vdot-active');

                // Re-colour the SVG preview, preserving any 2-char opacity suffix
                if (newPri !== oldPri || newSec !== oldSec) {
                    var preview = card.querySelector('.rxc-preview');
                    if (preview) {
                        var html = preview.innerHTML;
                        html = html.replace(makeColorRe(oldPri), function (m, alpha) { return newPri + (alpha || ''); });
                        html = html.replace(makeColorRe(oldSec), function (m, alpha) { return newSec + (alpha || ''); });
                        preview.innerHTML = html;
                    }
                    card.dataset.primary   = newPri;
                    card.dataset.secondary = newSec;
                }

                // Selecting this card also applies the colour override
                selectCard(card);
            });
        });
    });

    // ── Submit state ───────────────────────────────────────────
    function updateSubmit() {
        const hasTitle    = titleInput.value.trim().length > 0;
        const hasTemplate = !!grid.querySelector('input[name="template"]:checked');
        submitBtn.disabled = !(hasTitle && hasTemplate);
    }

    titleInput.addEventListener('input', updateSubmit);
    updateSubmit();

    // ── Form validation ────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        const title = titleInput.value.trim();
        if (!title) {
            e.preventDefault();
            titleInput.focus();
            titleInput.style.borderColor = 'rgba(220,38,38,0.6)';
            titleInput.style.boxShadow   = '0 0 0 3px rgba(220,38,38,0.12)';
            return;
        }
        if (!grid.querySelector('input[name="template"]:checked')) {
            e.preventDefault();
            return;
        }
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:rxcSpin 0.7s linear infinite">
                <line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/>
                <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/>
                <line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>
                <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/>
            </svg>
            Creating…`;
    });

    // Reset border on input
    titleInput.addEventListener('focus', function () {
        this.style.borderColor = '';
        this.style.boxShadow   = '';
    });

    // Keyboard nav between cards (arrow keys)
    grid.addEventListener('keydown', function (e) {
        const visible = cards.filter(c => c.style.display !== 'none');
        const focused = document.activeElement;
        const idx = visible.indexOf(focused);
        if (idx === -1) return;

        let next = -1;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            next = (idx + 1) % visible.length;
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            next = (idx - 1 + visible.length) % visible.length;
        }
        if (next !== -1) {
            e.preventDefault();
            visible[next].focus();
        }
    });
})();
</script>
<style>
@keyframes rxcSpin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
</style>
<?php View::end(); ?>
