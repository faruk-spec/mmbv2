<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
/* ── Wrapper ──────────────────────────────────────────────────── */
.rxc-wrap {
    max-width: 1100px;
    margin: 0 auto;
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

            $defaultKey = 'midnight-pro';
            foreach ($allThemes as $themeKey => $theme):
                $cat = strtolower($theme['category'] ?? 'other');
                // Determine which filter group this card belongs to
                if (in_array($cat, $creativeCategories)) {
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

                <!-- Color preview with mini mockup -->
                <div class="rxc-preview" style="background:<?= $bg ?>;">
                    <div class="rxc-mockup" style="background:<?= $surf ?>;">
                        <!-- Header bar -->
                        <div class="rxc-mockup-header" style="background:<?= $pri ?>22; border-bottom: 1px solid <?= $pri ?>44;">
                            <div class="rxc-mockup-avatar" style="background:<?= $pri ?>55;"></div>
                            <div class="rxc-mockup-title-block">
                                <div class="rxc-mockup-name-bar" style="background:<?= $pri ?>cc;"></div>
                                <div class="rxc-mockup-sub-bar" style="background:<?= $pri ?>66;"></div>
                            </div>
                        </div>
                        <!-- Body lines -->
                        <div class="rxc-mockup-body">
                            <div class="rxc-mockup-section-label" style="background:<?= $pri ?>bb;"></div>
                            <?php for ($i = 0; $i < 3; $i++):
                                $widths = ['80%', '65%', '72%'];
                            ?>
                            <div style="display:flex;align-items:center;gap:4px;margin-bottom:1px;">
                                <div class="rxc-mockup-bullet" style="background:<?= $pri ?>;"></div>
                                <div class="rxc-mockup-line-fill" style="background:rgba(128,128,128,0.3);width:<?= $widths[$i] ?>;height:3px;border-radius:3px;"></div>
                            </div>
                            <?php endfor; ?>
                            <div style="height:4px;"></div>
                            <div class="rxc-mockup-section-label" style="background:<?= $sec ?>bb;width:35%;"></div>
                            <?php for ($i = 0; $i < 2; $i++):
                                $widths2 = ['70%', '55%'];
                            ?>
                            <div style="display:flex;align-items:center;gap:4px;margin-bottom:1px;">
                                <div class="rxc-mockup-bullet" style="background:<?= $sec ?>;"></div>
                                <div class="rxc-mockup-line-fill" style="background:rgba(128,128,128,0.3);width:<?= $widths2[$i] ?>;height:3px;border-radius:3px;"></div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Card info -->
                <div class="rxc-card-info">
                    <span class="rxc-card-name"><?= htmlspecialchars($theme['name']) ?></span>
                    <span class="rxc-cat-badge <?= $catClass ?>">
                        <?= htmlspecialchars(ucfirst($cat)) ?>
                    </span>
                </div>
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
                    <div class="rxc-sel-name" id="rxcSelName">Midnight Pro</div>
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
