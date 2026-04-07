<?php use Core\View; use Core\Database; use Core\Auth; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
    /* ===== FUTURISTIC HOMEPAGE DESIGN SYSTEM ===== */

    /* Dark mode: futuristic background base */
    [data-theme="dark"] body,
    html:not([data-theme="light"]) body {
        background: #0b0f19 !important;
    }

    /* Gradient text: purple → cyan */
    .hp-grad-text {
        background: linear-gradient(135deg, #7C3AED, #00F5FF);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Hero h1 gradient override */
    .hero h1 {
        background: linear-gradient(135deg, #7C3AED, #00F5FF) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }

    /* Stat value gradient override */
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #7C3AED, #00F5FF) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        margin-bottom: 8px;
        animation: fadeIn 1s ease-out;
    }

    /* Glassmorphism cards - dark mode */
    [data-theme="dark"] .card,
    html:not([data-theme="light"]) .card {
        --card-inner-bg: rgba(11, 15, 25, 0.92);
        background: rgba(11, 15, 25, 0.6) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border: 1px solid rgba(124, 58, 237, 0.2) !important;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(124, 58, 237, 0.1) !important;
    }

    /* Glassmorphism cards - light mode */
    [data-theme="light"] .card {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(124, 58, 237, 0.15) !important;
        box-shadow: 0 4px 20px rgba(124, 58, 237, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.9) !important;
    }

    /* Smooth card hover transitions with neon accent */
    .card {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    border-color 0.3s ease !important;
    }

    .card:hover {
        transform: translateY(-5px) !important;
        border-color: rgba(0, 245, 255, 0.35) !important;
        box-shadow: 0 12px 36px rgba(124, 58, 237, 0.25), 0 0 0 1px rgba(0, 245, 255, 0.1) !important;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 36px rgba(124, 58, 237, 0.25) !important;
    }

    /* Hero banner neon glow */
    .hero-banner {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 0 40px rgba(124, 58, 237, 0.3) !important;
        margin-bottom: 30px;
        animation: fadeIn 0.8s ease-out;
    }

    /* Timeline badge with neon pulse */
    .timeline-badge {
        position: absolute;
        left: 0;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #7C3AED, #00F5FF) !important;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        font-size: 13px;
        animation: hp-pulse 2.5s ease-in-out infinite;
    }

    /* Subtle pulse animation */
    @keyframes hp-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.5); }
        50% { box-shadow: 0 0 0 10px rgba(124, 58, 237, 0); }
    }

    /* Primary button: purple → cyan gradient */
    .btn-primary {
        background: linear-gradient(135deg, #7C3AED, #00F5FF) !important;
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.35) !important;
        color: #fff !important;
    }

    .btn-primary:hover {
        box-shadow: 0 6px 25px rgba(124, 58, 237, 0.55) !important;
        transform: translateY(-2px) !important;
        -webkit-text-fill-color: #fff !important;
    }

    /* Animate fade in */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }

    /* Timeline item */
    .timeline-item {
        position: relative;
        padding-left: 70px;
        margin-bottom: 30px;
    }

    /* Mouse glow layer */
    #hp-mouse-glow {
        position: fixed;
        width: 480px;
        height: 480px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.18) 0%, rgba(0, 245, 255, 0.06) 40%, transparent 70%);
        pointer-events: none;
        transform: translate(-50%, -50%);
        z-index: 0;
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    /* Particles canvas */
    #hp-particles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    /* Keep content above background layers */
    .hero, .grid, .card, section,
    [style*="max-width: 1400px"],
    [style*="max-width:1400px"] {
        position: relative;
        z-index: 1;
    }

    /* Light mode card text stays readable */
    [data-theme="light"] .card {
        color: #1a1a1a !important;
    }

    /* Light mode — home page specific fixes */
    [data-theme="light"] .hero h1 {
        background: linear-gradient(135deg, #7C3AED, #0369a1) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }

    [data-theme="light"] .hp-grad-text {
        background: linear-gradient(135deg, #7C3AED, #0369a1);
    }

    [data-theme="light"] .stat-value {
        background: linear-gradient(135deg, #7C3AED, #0369a1) !important;
    }

    [data-theme="light"] .btn-primary {
        background: linear-gradient(135deg, #7C3AED, #0369a1) !important;
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.30) !important;
    }

    [data-theme="light"] .timeline-badge {
        background: linear-gradient(135deg, #7C3AED, #0369a1) !important;
    }

    [data-theme="light"] .toggle-details {
        background: rgba(0, 0, 0, 0.04) !important;
        border-color: rgba(0, 0, 0, 0.12) !important;
    }

    [data-theme="light"] .project-details > div {
        background: rgba(124, 58, 237, 0.04) !important;
    }

    [data-theme="light"] .project-card:hover {
        box-shadow: 0 12px 40px rgba(124, 58, 237, 0.20) !important;
    }

    [data-theme="light"] #hp-mouse-glow {
        background: radial-gradient(circle, rgba(124, 58, 237, 0.10) 0%, rgba(3, 105, 161, 0.04) 40%, transparent 70%);
    }

    [data-theme="light"] .card:hover {
        border-color: rgba(124, 58, 237, 0.30) !important;
        box-shadow: 0 12px 36px rgba(124, 58, 237, 0.18), 0 0 0 1px rgba(124, 58, 237, 0.08) !important;
    }

    /* ===== Section Labels & Headings ===== */
    .hp-section-label {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--purple);
        background: rgba(153, 69, 255, 0.1);
        border: 1px solid rgba(153, 69, 255, 0.25);
        padding: 4px 14px;
        border-radius: var(--radius-full);
        margin-bottom: 14px;
        font-family: var(--font-heading);
    }

    .hp-section-h2 {
        font-size: 2rem !important;
        font-family: var(--font-heading) !important;
        font-weight: 800 !important;
        letter-spacing: -0.025em !important;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: inline-block;
    }

    [data-theme="light"] .hp-section-label {
        color: var(--purple);
        background: rgba(124, 58, 237, 0.08);
        border-color: rgba(124, 58, 237, 0.2);
    }

    @media (max-width: 768px) {
        .grid-3, .grid-4 {
            grid-template-columns: 1fr;
        }

        .hero h1 {
            font-size: 1.8rem !important;
        }

        .hero h2 {
            font-size: 1.2rem !important;
        }

        .hp-section-h2 {
            font-size: 1.5rem !important;
        }
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<!-- Particles canvas & mouse glow for homepage -->
<canvas id="hp-particles"></canvas>
<div id="hp-mouse-glow"></div>
<?php 
// Get hero content from database with error handling
$db = Database::getInstance();

try {
    $heroContent = $db->fetch("SELECT * FROM home_content WHERE section = 'hero'");
    $projectsSection = $db->fetch("SELECT * FROM home_content WHERE section = 'projects_section'");
} catch (Exception $e) {
    $heroContent = [];
    $projectsSection = [];
}

$heroTitle = $heroContent['title'] ?? 'Welcome to ' . APP_NAME;
$heroSubtitle = $heroContent['subtitle'] ?? 'A powerful multi-project platform';
$heroDescription = $heroContent['description'] ?? 'A powerful multi-project platform with centralized authentication, unified admin panel, and secure architecture.';
$heroBanner = $heroContent['image_url'] ?? '';
$projectsSectionTitle = $projectsSection['title'] ?? 'Explore Our Super Fast Products';

// Get section headings with error handling
$sections = [];
try {
    $sectionRows = $db->fetchAll("SELECT * FROM home_sections WHERE is_active = 1");
    foreach ($sectionRows as $row) {
        $sections[$row['section_key']] = $row;
    }
} catch (Exception $e) {
    $sections = [];
}

// Get default values
$statsHeading = $sections['stats']['heading'] ?? 'Our Impact in Numbers';
$statsSubheading = $sections['stats']['subheading'] ?? 'Trusted by developers and teams worldwide';
$timelineHeading = $sections['timeline']['heading'] ?? 'Our Journey';
$timelineSubheading = $sections['timeline']['subheading'] ?? 'Milestones and achievements that shaped our platform';
$featuresHeading = $sections['features']['heading'] ?? 'Platform Features';
$featuresSubheading = $sections['features']['subheading'] ?? 'Powerful capabilities across all projects';
?>

<div class="hero" style="padding: 70px 20px 50px; max-width: 1400px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center;">
        <!-- Left side: Text content -->
        <div style="text-align: left;">
            <!-- Animated badge -->
            <div class="hp-hero-badge">
                <span class="hp-hero-badge-dot"></span>
                <span>Multi-Project Platform</span>
            </div>

            <h1 class="hp-hero-h1">
                <?= htmlspecialchars($heroTitle) ?>
            </h1>
            <?php if ($heroSubtitle && $heroSubtitle !== $heroTitle): ?>
            <h2 style="font-size: 1.3rem; margin-bottom: 14px; color: var(--text-primary); font-weight: 500; font-family: var(--font-body);">
                <?= htmlspecialchars($heroSubtitle) ?>
            </h2>
            <?php endif; ?>
            <p style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 34px; line-height: 1.7; max-width: 480px;">
                <?= htmlspecialchars($heroDescription) ?>
            </p>
            
            <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
                <?php if (Auth::check()): ?>
                    <a href="/dashboard" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="/register" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        Get Started Free
                    </a>
                    <a href="/login" class="btn btn-secondary btn-lg">Sign In</a>
                <?php endif; ?>
            </div>

            <!-- Trust indicators -->
            <div class="hp-trust-row">
                <div class="hp-trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>No credit card</span>
                </div>
                <div class="hp-trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>CSRF &amp; XSS protected</span>
                </div>
                <div class="hp-trust-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Dark &amp; Light mode</span>
                </div>
            </div>
        </div>
        
        <!-- Right side: Hero banner image -->
        <div style="text-align: center; position: relative;">
            <?php if (!empty($heroBanner)): ?>
                <img src="<?= htmlspecialchars($heroBanner) ?>" alt="Hero Banner" class="hero-banner" style="max-width: 100%; height: auto; border-radius: var(--radius-lg); box-shadow: var(--glow-purple), 0 24px 64px rgba(0,0,0,0.45);">
            <?php else: ?>
                <!-- Hero visual placeholder -->
                <div class="hp-hero-visual">
                    <div class="hp-hero-orb hp-hero-orb-1"></div>
                    <div class="hp-hero-orb hp-hero-orb-2"></div>
                    <!-- Central icon -->
                    <div class="hp-hero-center-icon">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="url(#hg)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <defs><linearGradient id="hg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#9945ff"/><stop offset="100%" stop-color="#00f0ff"/></linearGradient></defs>
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <path d="M3 9h18M9 21V9"/>
                        </svg>
                    </div>
                    <!-- Floating feature chips -->
                    <div class="hp-chip hp-chip-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Secure Auth
                    </div>
                    <div class="hp-chip hp-chip-2">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2.5"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        12+ Projects
                    </div>
                    <div class="hp-chip hp-chip-3">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Admin Panel
                    </div>
                    <div class="hp-chip hp-chip-4">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.5"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                        Analytics
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* ===== Hero Section v2 ===== */
.hp-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: rgba(153, 69, 255, 0.12);
    border: 1px solid rgba(153, 69, 255, 0.3);
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
    color: var(--purple);
    margin-bottom: 22px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    font-family: var(--font-heading);
}

.hp-hero-badge-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--purple);
    animation: hp-badge-pulse 2s ease-in-out infinite;
    flex-shrink: 0;
}

@keyframes hp-badge-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(153, 69, 255, 0.6); }
    50% { box-shadow: 0 0 0 5px rgba(153, 69, 255, 0); }
}

.hp-hero-h1 {
    font-size: 3rem !important;
    line-height: 1.1 !important;
    margin-bottom: 18px !important;
    font-family: var(--font-heading) !important;
    font-weight: 800 !important;
    letter-spacing: -0.03em !important;
    background: linear-gradient(135deg, var(--purple) 0%, var(--cyan) 60%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
}

.hp-trust-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}

.hp-trust-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-secondary);
}

/* Hero visual placeholder */
.hp-hero-visual {
    width: 100%;
    aspect-ratio: 1 / 1;
    max-width: 420px;
    margin: 0 auto;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hp-hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    pointer-events: none;
}

.hp-hero-orb-1 {
    width: 280px;
    height: 280px;
    background: rgba(153, 69, 255, 0.25);
    top: 10%;
    left: 10%;
    animation: hp-float 7s ease-in-out infinite alternate;
}

.hp-hero-orb-2 {
    width: 200px;
    height: 200px;
    background: rgba(0, 240, 255, 0.18);
    bottom: 10%;
    right: 10%;
    animation: hp-float 9s ease-in-out infinite alternate-reverse;
}

@keyframes hp-float {
    from { transform: translateY(0) scale(1); }
    to   { transform: translateY(-20px) scale(1.05); }
}

.hp-hero-center-icon {
    width: 120px;
    height: 120px;
    background: rgba(153, 69, 255, 0.1);
    border: 1px solid rgba(153, 69, 255, 0.3);
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2;
    backdrop-filter: blur(12px);
    box-shadow: 0 0 40px rgba(153, 69, 255, 0.2), 0 0 80px rgba(0, 240, 255, 0.1);
}

.hp-chip {
    position: absolute;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-full);
    font-size: 11px;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    backdrop-filter: blur(10px);
    z-index: 3;
    font-family: var(--font-heading);
}

.hp-chip-1 { top: 12%;    left: 2%;   animation: hp-chip-bob 5s ease-in-out infinite; }
.hp-chip-2 { top: 5%;    right: 5%;  animation: hp-chip-bob 6s ease-in-out infinite 0.8s; }
.hp-chip-3 { bottom: 12%; left: 2%;   animation: hp-chip-bob 7s ease-in-out infinite 1.4s; }
.hp-chip-4 { bottom: 5%;  right: 3%;  animation: hp-chip-bob 5.5s ease-in-out infinite 0.4s; }

@keyframes hp-chip-bob {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

[data-theme="light"] .hp-hero-badge {
    background: rgba(124, 58, 237, 0.08);
    border-color: rgba(124, 58, 237, 0.25);
    color: var(--purple);
}

[data-theme="light"] .hp-hero-badge-dot { background: var(--purple); }

[data-theme="light"] .hp-hero-h1 {
    background: linear-gradient(135deg, var(--purple) 0%, var(--cyan) 60%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
}

/* Responsive hero section */
@media (max-width: 900px) {
    .hero > div {
        grid-template-columns: 1fr !important;
        gap: 40px !important;
    }
    .hp-hero-h1 { font-size: 2.2rem !important; }
}

@media (max-width: 480px) {
    .hp-hero-h1 { font-size: 1.8rem !important; }
    .hero { padding: 40px 16px 30px !important; }
    .hp-trust-row { gap: 12px; }
}
</style>

<div class="grid grid-3" style="margin-top: 40px; max-width: 1400px; margin-left: auto; margin-right: auto;">
    <div class="card animate-fade-in" style="animation-delay: 0.1s;">
        <div style="width: 45px; height: 45px; background: rgba(0, 240, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <h3 style="margin-bottom: 8px; font-size: 1.1rem;">Secure by Design</h3>
        <p style="color: var(--text-secondary); font-size: 13px;">
            Argon2id password hashing, CSRF protection, XSS sanitization, and rate limiting built-in.
        </p>
    </div>
    
    <div class="card animate-fade-in" style="animation-delay: 0.2s;">
        <div style="width: 45px; height: 45px; background: rgba(255, 46, 196, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <h3 style="margin-bottom: 8px; font-size: 1.1rem;">Single Sign-On</h3>
        <p style="color: var(--text-secondary); font-size: 13px;">
            One account to access all projects. Seamless authentication across your entire platform.
        </p>
    </div>
    
    <div class="card animate-fade-in" style="animation-delay: 0.3s;">
        <div style="width: 45px; height: 45px; background: rgba(0, 255, 136, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="3" y1="9" x2="21" y2="9"/>
                <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
        </div>
        <h3 style="margin-bottom: 8px; font-size: 1.1rem;">Unified Admin</h3>
        <p style="color: var(--text-secondary); font-size: 13px;">
            Manage all projects from a single dashboard with role-based permissions.
        </p>
    </div>
</div>

<!-- Stats Section -->
<?php 
// Fetch stats - show section even if query fails, ensure no duplicates
$showStats = false;
$stats = [];
try {
    // Use DISTINCT and LIMIT to prevent duplicates
    $stats = $db->fetchAll("SELECT DISTINCT id, value, label, description, sort_order, is_active FROM home_stats WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8");
    $showStats = !empty($stats);
} catch (Exception $e) {
    $showStats = false;
    error_log("Stats fetch error: " . $e->getMessage());
}

if ($showStats): 
?>
<div style="margin-top: 60px; text-align: center; max-width: 1500px; margin-left: auto; margin-right: auto; padding: 0 20px;">
    <div class="hp-section-label">Platform Stats</div>
    <h2 class="hp-section-h2" style="margin-bottom: 12px;"><?= htmlspecialchars($statsHeading) ?></h2>
    <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 48px;"><?= htmlspecialchars($statsSubheading) ?></p>
    
    <div class="grid grid-4">
        <?php 
        $delay = 0;
        $renderedStatIds = []; // Track rendered IDs to prevent duplicates
        foreach ($stats as $stat): 
            // Skip if already rendered (safety check)
            if (in_array($stat['id'], $renderedStatIds)) {
                continue;
            }
            $renderedStatIds[] = $stat['id'];
        ?>
        <div class="card stat-card animate-fade-in" style="text-align: center; animation-delay: <?= $delay ?>s;">
            <div class="stat-value hp-stat-count" data-target="<?= htmlspecialchars($stat['value']) ?>" style="font-size: 2.5rem; font-weight: 700;">
                <?= htmlspecialchars($stat['value']) ?>
            </div>
            <h4 style="margin-bottom: 4px; font-size: 1rem; color: var(--text-primary); font-weight: 600;"><?= htmlspecialchars($stat['label']) ?></h4>
            <?php if (!empty($stat['description'])): ?>
            <p style="font-size: 13px; color: var(--text-secondary);"><?= htmlspecialchars($stat['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php 
        $delay += 0.1;
        endforeach;
        ?>
    </div>
</div>
<?php endif; ?>



<div style="margin-top: 60px; text-align: center; max-width: 1500px; margin-left: auto; margin-right: auto; padding: 0 20px;">
    <div class="hp-section-label">Our Products</div>
    <h2 class="hp-section-h2" style="margin-bottom: 20px;"><?= htmlspecialchars($projectsSectionTitle) ?></h2>
    
    <!-- Filter Buttons -->
    <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 40px; flex-wrap: wrap;">
        <button class="filter-btn active" data-filter="all">
            All Tools
        </button>
        <button class="filter-btn" data-filter="free">
            Free Tools
        </button>
        <button class="filter-btn" data-filter="freemium">
            Freemium
        </button>
        <button class="filter-btn" data-filter="enterprise">
            Enterprise Grade
        </button>
    </div>
    
    <div class="grid grid-3" id="projectsGrid">
        <?php 
        // Show enabled DB rows; merge config projects that have NO DB row at all.
        // Projects in DB with is_enabled=0 must not be re-added from config.
        $_configProjects = require BASE_PATH . '/config/projects.php';
        try {
            $_allDbRows = $db->fetchAll("SELECT * FROM home_projects ORDER BY sort_order ASC");
            $projects   = [];
            $_dbKeys    = [];
            foreach ($_allDbRows as $_row) {
                $_dbKeys[] = $_row['project_key'];
                if ((int) $_row['is_enabled'] === 1) {
                    $projects[$_row['project_key']] = $_row;
                }
            }
        } catch (Exception $e) {
            $projects = [];
            $_dbKeys  = [];
        }
        foreach ($_configProjects as $_key => $_cfg) {
            if (!empty($_cfg['enabled']) && !in_array($_key, $_dbKeys, true)) {
                $projects[$_key] = array_merge($_cfg, ['project_key' => $_key]);
            }
        }
        
        $delay = 0;
        foreach ($projects as $key => $project): 
            // Handle both database and config array formats
            $projectName = $project['name'] ?? '';
            $projectDescription = $project['description'] ?? '';
            $projectColor = $project['color'] ?? '#00f0ff';
            $projectUrl = $project['url'] ?? '';
            $projectTier = $project['tier'] ?? 'free'; // Default to free
        ?>
        <div class="card animate-fade-in project-card" data-tier="<?= htmlspecialchars($projectTier) ?>" style="border-color: <?= $projectColor ?>30; animation-delay: <?= $delay ?>s; position: relative; overflow: hidden; transition: all 0.4s ease;">
            <!-- Tier Badge -->
            <div style="position: absolute; top: 12px; right: 12px; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; 
                <?php 
                $badgeStyles = [
                    'free' => 'background: rgba(0, 255, 136, 0.2); color: var(--green); border: 1px solid var(--green);',
                    'freemium' => 'background: rgba(255, 170, 0, 0.2); color: var(--orange); border: 1px solid var(--orange);',
                    'enterprise' => 'background: rgba(153, 69, 255, 0.2); color: var(--purple); border: 1px solid var(--purple);'
                ];
                echo $badgeStyles[$projectTier] ?? $badgeStyles['free'];
                ?>">
                <?= htmlspecialchars(ucfirst($projectTier === 'enterprise' ? 'Enterprise' : $projectTier)) ?>
            </div>
            
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; margin-top: 8px;">
                <div style="width: 48px; height: 48px; background: <?= $projectColor ?>20; border-radius: 10px; border: 1px solid <?= $projectColor ?>40; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                    <?php if (!empty($project['logo_url'])): ?>
                        <img src="<?= htmlspecialchars($project['logo_url']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:9px;">
                    <?php else: ?>
                        <span style="font-size: 1.1rem; font-weight: 700; color: <?= $projectColor ?>;"><?= strtoupper(substr($projectName, 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                <h3 style="color: <?= $projectColor ?>; font-size: 1.1rem;"><?= htmlspecialchars($projectName) ?></h3>
            </div>
            
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 16px; line-height: 1.5;">
                <?= htmlspecialchars($projectDescription) ?>
            </p>
            
            <!-- Collapsible Features Section -->
            <div class="project-details" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease; margin-bottom: 16px;">
                <div style="padding: 12px; background: rgba(0, 240, 255, 0.03); border-radius: 8px; margin-bottom: 12px;">
                    <h4 style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Key Features</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php
                        // Get features from database or use default
                        $projectFeatures = [];
                        if (!empty($project['features'])) {
                            $projectFeatures = json_decode($project['features'], true) ?? [];
                        }
                        
                        // Fallback to default features if none in database
                        if (empty($projectFeatures)) {
                            $projectFeatures = [
                                'Advanced capabilities',
                                'Professional tools',
                                'Cloud integration'
                            ];
                        }
                        
                        foreach (array_slice($projectFeatures, 0, 3) as $feature): 
                        ?>
                        <li style="font-size: 12px; color: var(--text-primary); padding: 4px 0; display: flex; align-items: center; gap: 8px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?= $projectColor ?>" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <?= htmlspecialchars($feature) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Toggle Details Button -->
            <button class="toggle-details" style="width: 100%; padding: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-secondary); font-size: 12px; cursor: pointer; margin-bottom: 12px; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span class="toggle-text">Show Features</span>
                <svg class="toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.3s ease;">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            
            <?php if (Auth::check()): ?>
                <a href="<?= htmlspecialchars($projectUrl) ?>" class="btn btn-primary" style="width: 100%; background: <?= $projectColor ?>; border-color: <?= $projectColor ?>;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                    Access Application
                </a>
            <?php else: ?>
                <a href="/login?redirect=<?= urlencode($projectUrl) ?>" class="btn btn-secondary" style="width: 100%;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    Sign In to Access
                </a>
            <?php endif; ?>
        </div>
        <?php 
        $delay += 0.1;
        endforeach; 
        ?>
    </div>
</div>

<style>
/* Filter Buttons Styles */
.filter-btn {
    padding: 10px 24px;
    border-radius: 25px;
    border: 2px solid var(--border-color);
    background: transparent;
    color: var(--text-primary);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 240, 255, 0.3);
}

[data-theme="light"] .filter-btn:hover {
    box-shadow: 0 4px 12px color-mix(in srgb, var(--cyan) 25%, transparent);
}

.filter-btn.active {
    background: var(--cyan) !important;
    border-color: var(--cyan) !important;
    color: #ffffff !important;
}

/* Project Card Enhancements */
.project-card {
    cursor: pointer;
}

.project-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
}

.project-card .toggle-details:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--cyan);
}

.project-card.expanded .project-details {
    max-height: 300px !important;
}

.project-card.expanded .toggle-icon {
    transform: rotate(180deg);
}

/* Project Card Animation */
.project-card.filtered-out {
    display: none;
    opacity: 0;
    transform: scale(0.8);
}
</style>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            // Filter projects with animation
            projectCards.forEach((card, index) => {
                const tier = card.dataset.tier;
                
                if (filter === 'all' || tier === filter) {
                    card.classList.remove('filtered-out');
                    card.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1}s`;
                } else {
                    card.classList.add('filtered-out');
                }
            });
        });
    });
    
    // Toggle details functionality
    document.querySelectorAll('.toggle-details').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const card = this.closest('.project-card');
            const isExpanded = card.classList.contains('expanded');
            const toggleText = this.querySelector('.toggle-text');
            
            card.classList.toggle('expanded');
            toggleText.textContent = isExpanded ? 'Show Features' : 'Hide Features';
        });
    });
});
</script>

<div style="margin-top: 60px; padding: 40px 20px; background: rgba(0, 240, 255, 0.02); border-radius: var(--radius-lg); max-width: 1500px; margin-left: auto; margin-right: auto;">
    <div style="text-align: center; margin-bottom: 36px;">
        <div class="hp-section-label">Capabilities</div>
        <h2 class="hp-section-h2" style="margin-bottom: 10px;"><?= htmlspecialchars($featuresHeading) ?></h2>
        <p style="color: var(--text-secondary); font-size: 0.95rem;"><?= htmlspecialchars($featuresSubheading) ?></p>
    </div>
    
    <div class="grid grid-4" style="padding: 0 10px;">
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0s;">
            <div style="width: 45px; height: 45px; background: rgba(0, 240, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--cyan); font-size: 0.95rem;">Performance</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">Smart caching, optimized queries, asset bundling</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.1s;">
            <div style="width: 45px; height: 45px; background: rgba(255, 46, 196, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                    <polyline points="16 6 12 2 8 6"/>
                    <line x1="12" y1="2" x2="12" y2="15"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--magenta); font-size: 0.95rem;">REST API</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">Full API access with authentication & rate limiting</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.2s;">
            <div style="width: 45px; height: 45px; background: rgba(0, 255, 136, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                    <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                    <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                    <circle cx="12" cy="20" r="1"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--green); font-size: 0.95rem;">Real-time</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">WebSocket server for live collaboration</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.3s;">
            <div style="width: 45px; height: 45px; background: rgba(255, 170, 0, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--orange); font-size: 0.95rem;">Notifications</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">Email queue, multi-channel alerts</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.4s;">
            <div style="width: 45px; height: 45px; background: rgba(153, 69, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                    <path d="M3 3v18h18"/>
                    <path d="M18 17V9"/>
                    <path d="M13 17V5"/>
                    <path d="M8 17v-3"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--purple); font-size: 0.95rem;">Analytics</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">Track usage, generate reports</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.5s;">
            <div style="width: 45px; height: 45px; background: rgba(0, 240, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="7.5 4.21 12 6.81 16.5 4.21"/>
                    <polyline points="7.5 19.79 7.5 14.6 3 12"/>
                    <polyline points="21 12 16.5 14.6 16.5 19.79"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--cyan); font-size: 0.95rem;">Templates</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">Code templates & snippets library</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.6s;">
            <div style="width: 45px; height: 45px; background: rgba(255, 46, 196, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--magenta); font-size: 0.95rem;">OCR Advanced</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">PDF processing, table detection, batch jobs</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.7s;">
            <div style="width: 45px; height: 45px; background: rgba(0, 255, 136, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                    <circle cx="18" cy="5" r="3"/>
                    <circle cx="6" cy="12" r="3"/>
                    <circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--green); font-size: 0.95rem;">Sharing</h4>
            <p style="font-size: 12px; color: var(--text-secondary);">QR codes, social media, custom links</p>
        </div>
    </div>
</div>

<?php
// Get timeline items from database
$timelineItems = $db->fetchAll("SELECT * FROM home_timeline WHERE is_active = 1 ORDER BY sort_order ASC");
?>

<!-- Attractive Timeline Section -->
<?php if (!empty($timelineItems)): ?>
<div style="margin-top: 80px; padding: 60px 0;">
    <div style="text-align: center; margin-bottom: 60px;">
        <div class="hp-section-label">Milestones</div>
        <h2 class="hp-section-h2" style="margin-bottom: 15px;"><?= View::e($timelineHeading) ?></h2>
        <p style="color: var(--text-secondary); font-size: 1rem;"><?= View::e($timelineSubheading) ?></p>
    </div>
    
    <div style="max-width: 900px; margin: 0 auto; position: relative; padding: 0 40px;">
        <!-- Timeline Line -->
        <div style="position: absolute; left: 50%; transform: translateX(-50%); top: 0; bottom: 0; width: 2px; background: linear-gradient(180deg, var(--cyan), var(--magenta), var(--green), var(--orange)); opacity: 0.3;"></div>
        
        <?php 
        $isLeft = true;
        foreach ($timelineItems as $index => $item): 
            $itemColor = $item['color'] ?? '#00f0ff';
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $itemColor)) {
                $itemColor = '#00f0ff';
            }
            $side = $isLeft ? 'left' : 'right';
            $isLeft = !$isLeft;
        ?>
        <div class="timeline-item" style="position: relative; margin-bottom: 60px; opacity: 0; transform: translateY(30px); transition: all 0.6s ease; animation: fadeInUp 0.8s ease forwards; animation-delay: <?= $index * 0.2 ?>s;">
            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 30px; align-items: center;">
                <?php if ($side === 'left'): ?>
                    <!-- Content on left -->
                    <div style="text-align: right;">
                        <div class="timeline-card" style="background: var(--card-bg); border: 1px solid <?= View::e($itemColor) ?>30; border-radius: 15px; padding: 25px; position: relative; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                            <h3 style="color: <?= View::e($itemColor) ?>; margin-bottom: 10px; font-size: 1.3rem;">
                                <?= View::e($item['title']) ?>
                            </h3>
                            <?php if ($item['description']): ?>
                            <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 10px;">
                                <?= View::e($item['description']) ?>
                            </p>
                            <?php endif; ?>
                            <?php if ($item['date_display']): ?>
                            <div style="color: <?= View::e($itemColor) ?>; font-size: 0.85rem; font-weight: 600;">
                                <?= View::e($item['date_display']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Circle in center -->
                    <div style="width: 60px; height: 60px; background: <?= View::e($itemColor) ?>20; border: 3px solid <?= View::e($itemColor) ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10; position: relative;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= View::e($itemColor) ?>" stroke-width="2">
                            <?php if ($item['icon'] === 'rocket'): ?>
                                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                                <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                                <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
                                <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
                            <?php elseif ($item['icon'] === 'grid'): ?>
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            <?php elseif ($item['icon'] === 'shield'): ?>
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <?php elseif ($item['icon'] === 'code'): ?>
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            <?php elseif ($item['icon'] === 'star'): ?>
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            <?php else: ?>
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            <?php endif; ?>
                        </svg>
                    </div>
                    
                    <!-- Empty space on right -->
                    <div></div>
                <?php else: ?>
                    <!-- Empty space on left -->
                    <div></div>
                    
                    <!-- Circle in center -->
                    <div style="width: 60px; height: 60px; background: <?= View::e($itemColor) ?>20; border: 3px solid <?= View::e($itemColor) ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10; position: relative;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="<?= View::e($itemColor) ?>" stroke-width="2">
                            <?php if ($item['icon'] === 'rocket'): ?>
                                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                                <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                                <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
                                <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
                            <?php elseif ($item['icon'] === 'grid'): ?>
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            <?php elseif ($item['icon'] === 'shield'): ?>
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <?php elseif ($item['icon'] === 'code'): ?>
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            <?php elseif ($item['icon'] === 'star'): ?>
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            <?php else: ?>
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            <?php endif; ?>
                        </svg>
                    </div>
                    
                    <!-- Content on right -->
                    <div style="text-align: left;">
                        <div class="timeline-card" style="background: var(--card-bg); border: 1px solid <?= View::e($itemColor) ?>30; border-radius: 15px; padding: 25px; position: relative; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                            <h3 style="color: <?= View::e($itemColor) ?>; margin-bottom: 10px; font-size: 1.3rem;">
                                <?= View::e($item['title']) ?>
                            </h3>
                            <?php if ($item['description']): ?>
                            <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 10px;">
                                <?= View::e($item['description']) ?>
                            </p>
                            <?php endif; ?>
                            <?php if ($item['date_display']): ?>
                            <div style="color: <?= View::e($itemColor) ?>; font-size: 0.85rem; font-weight: 600;">
                                <?= View::e($item['date_display']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.timeline-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
}

@media (max-width: 768px) {
    .timeline-item > div {
        grid-template-columns: auto 1fr !important;
        gap: 15px !important;
    }
    
    .timeline-item > div > div:first-child {
        display: none !important;
    }
    
    .timeline-item > div > div:nth-child(2) {
        /* Circle - keep visible */
    }
    
    .timeline-item > div > div:nth-child(3) {
        display: block !important;
        text-align: left !important;
    }
    
    .timeline-card {
        max-width: 100% !important;
    }
    
    /* Show content from hidden columns */
    .timeline-item > div > div:last-child .timeline-card {
        display: block !important;
    }
}
</style>
<?php endif; ?>

<script>
/* Homepage: AI Network — gradient synapses, comet packets, node pulses, depth parallax */
(function() {
    var isDark = function() {
        return document.documentElement.getAttribute('data-theme') !== 'light';
    };

    /* ---- Mouse Glow ---- */
    var glow = document.getElementById('hp-mouse-glow');
    if (glow) {
        document.addEventListener('mousemove', function(e) {
            glow.style.left = e.clientX + 'px';
            glow.style.top  = e.clientY + 'px';
            glow.style.opacity = isDark() ? '1' : '0.5';
        });
        document.addEventListener('mouseleave', function() { glow.style.opacity = '0'; });
    }

    /* ---- AI Network ---- */
    var canvas = document.getElementById('hp-particles');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var nodes = [], packets = [], raf, time = 0;
    var CONNECT_DIST = 160;  /* max px for a synapse edge */
    var MAX_PACKETS  = 45;   /* simultaneous data packets */

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    function rnd(a, b) { return a + Math.random() * (b - a); }

    /* Hue oscillates: cyan 185° ↔ purple 270°, ~250 s period (2π / 0.025 ≈ 251 s) */
    function globalHue() { return 185 + 85 * (0.5 + 0.5 * Math.sin(time * 0.025)); }

    function createNode() {
        /* 3 depth layers: 0 = near (large/fast/bright), 2 = far (small/slow/dim) */
        var depth = Math.floor(Math.random() * 3);
        var sf    = 1 - depth * 0.3;   /* speed scale per layer */
        return {
            x:         rnd(0, canvas.width),
            y:         rnd(0, canvas.height),
            depth:     depth,
            r:         rnd(1.0, 2.5) + (2 - depth) * 0.6,  /* size parallax */
            dx:        rnd(-0.28, 0.28) * sf,
            dy:        rnd(-0.28, 0.28) * sf,               /* all directions */
            baseAlpha: rnd(0.4, 0.75) + (2 - depth) * 0.1,
            phase:     rnd(0, Math.PI * 2),
            pulse:     0,   /* 0–1: glow boost when a packet departs/arrives */
        };
    }

    function init() {
        nodes = []; packets = [];
        /* ~1 node per 10,000 px², max 80 */
        var count = Math.min(80, Math.floor(canvas.width * canvas.height / 10000));
        for (var i = 0; i < count; i++) nodes.push(createNode());
    }

    /* Draw a gradient line — purple hue on one end, cyan on the other */
    function gradientLine(x1, y1, h1, x2, y2, h2, alpha, lineW, dark) {
        var grad = ctx.createLinearGradient(x1, y1, x2, y2);
        if (dark) {
            grad.addColorStop(0, 'hsla(' + h1 + ',100%,65%,' + alpha + ')');
            grad.addColorStop(1, 'hsla(' + h2 + ',100%,65%,' + alpha + ')');
        } else {
            grad.addColorStop(0, 'hsla(' + h1 + ',70%,42%,' + alpha + ')');
            grad.addColorStop(1, 'hsla(' + h2 + ',70%,42%,' + alpha + ')');
        }
        ctx.lineWidth   = lineW;
        ctx.strokeStyle = grad;
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.stroke();
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        var dark = isDark();
        time += 0.016;
        var gHue = globalHue();

        /* ---- synapse lines — also builds edge list for packet spawning ---- */
        var edges = [];
        for (var ii = 0; ii < nodes.length - 1; ii++) {
            for (var jj = ii + 1; jj < nodes.length; jj++) {
                var na = nodes[ii], nb = nodes[jj];
                var ddx = na.x - nb.x, ddy = na.y - nb.y;
                var dist = Math.sqrt(ddx * ddx + ddy * ddy);
                if (dist < CONNECT_DIST) {
                    var dDepth = Math.abs(na.depth - nb.depth);
                    var lineA  = (1 - dist / CONNECT_DIST)
                                 * (dark ? 0.38 : 0.15)
                                 * (1 - dDepth * 0.2);
                    /* pulse-brighten edges connected to active nodes */
                    var pulseBoost = 1 + Math.max(na.pulse, nb.pulse) * 2;
                    lineA = Math.min(lineA * pulseBoost, dark ? 0.75 : 0.35);
                    var lineW = (0.75 - dDepth * 0.12)
                                * (1 + Math.max(na.pulse, nb.pulse) * 0.6);
                    /* per-node hue offset for colour variety along the network */
                    var h1 = gHue + (ii % 7 - 3) * 12;
                    var h2 = gHue + (jj % 7 - 3) * 12;
                    gradientLine(na.x, na.y, h1, nb.x, nb.y, h2, lineA, lineW, dark);
                    edges.push(ii, jj);
                }
            }
        }

        /* ---- spawn data packets ---- */
        if (packets.length < MAX_PACKETS && edges.length > 0 && Math.random() < 0.09) {
            var pick = Math.floor(Math.random() * (edges.length / 2)) * 2;
            var ai   = edges[pick], bi = edges[pick + 1];
            packets.push({
                ai:    ai,
                bi:    bi,
                t:     0,
                speed: rnd(0.012, 0.030),
                hue:   gHue + rnd(-45, 45),
                trail: [],   /* [{x,y}] for comet effect, max 8 points */
            });
            if (nodes[ai]) nodes[ai].pulse = 1;   /* source node fires */
        }

        /* ---- draw data packets with comet trail ---- */
        for (var k = packets.length - 1; k >= 0; k--) {
            var pk  = packets[k];
            var pna = nodes[pk.ai], pnb = nodes[pk.bi];
            if (!pna || !pnb) { packets.splice(k, 1); continue; }
            pk.t += pk.speed;
            if (pk.t >= 1) {
                if (pnb) pnb.pulse = 1;   /* destination node lights up */
                packets.splice(k, 1);
                continue;
            }
            var px = pna.x + (pnb.x - pna.x) * pk.t;
            var py = pna.y + (pnb.y - pna.y) * pk.t;

            /* store trail, keep last 8 positions */
            pk.trail.push({ x: px, y: py });
            if (pk.trail.length > 8) pk.trail.shift();

            /* draw fading comet tail */
            for (var ti = 0; ti < pk.trail.length; ti++) {
                var tf = (ti + 1) / pk.trail.length;   /* fade factor: older→newer, opacity increases toward packet head */
                ctx.save();
                ctx.globalAlpha = tf * (dark ? 0.55 : 0.28);
                ctx.beginPath();
                ctx.arc(pk.trail[ti].x, pk.trail[ti].y, 1.4 * tf, 0, Math.PI * 2);
                ctx.fillStyle = 'hsla(' + pk.hue + ',100%,82%,1)';
                ctx.fill();
                ctx.restore();
            }

            /* bright packet head */
            ctx.save();
            ctx.shadowBlur  = 14;
            ctx.shadowColor = 'hsla(' + pk.hue + ',100%,85%,1)';
            ctx.beginPath();
            ctx.arc(px, py, 2.6, 0, Math.PI * 2);
            ctx.fillStyle = dark
                ? 'hsla(' + pk.hue + ',100%,92%,1)'
                : 'hsla(' + pk.hue + ',90%,55%,0.95)';
            ctx.fill();
            ctx.restore();
        }

        /* ---- neuron nodes ---- */
        for (var ni = 0; ni < nodes.length; ni++) {
            var p = nodes[ni];
            var tw = p.baseAlpha * (0.55 + 0.45 * Math.sin(time * 1.4 + p.phase));
            if (!dark) tw *= 0.55;
            var nHue     = gHue + (p.depth - 1) * 28;
            var glowMult = 1 + p.pulse * 3.5;   /* dramatic pulse flare */

            ctx.save();
            ctx.shadowBlur  = p.r * 9 * glowMult;
            ctx.shadowColor = dark
                ? 'hsla(' + nHue + ',100%,72%,' + Math.min(0.9 * glowMult, 1) + ')'
                : 'hsla(' + nHue + ',80%,45%,0.55)';
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r * (1 + p.pulse * 0.6), 0, Math.PI * 2);
            ctx.fillStyle = dark
                ? 'hsla(' + nHue + ',100%,80%,' + Math.min(tw + p.pulse * 0.45, 1) + ')'
                : 'hsla(' + nHue + ',75%,46%,' + tw + ')';
            ctx.fill();
            ctx.restore();

            /* decay pulse */
            if (p.pulse > 0) p.pulse = Math.max(0, p.pulse - 0.035);

            /* drift — bidirectional, wrap all four edges */
            p.x += p.dx;
            p.y += p.dy;
            if (p.x < -10)               p.x = canvas.width  + 10;
            if (p.x > canvas.width  + 10) p.x = -10;
            if (p.y < -10)               p.y = canvas.height + 10;
            if (p.y > canvas.height + 10) p.y = -10;
        }

        raf = requestAnimationFrame(draw);
    }

    resize(); init(); draw();
    window.addEventListener('resize', function() { cancelAnimationFrame(raf); resize(); init(); draw(); });
})();
</script>

<!-- ===== Bottom CTA Section ===== -->
<div class="hp-cta-section">
    <div class="hp-cta-bg-orb hp-cta-bg-orb-1"></div>
    <div class="hp-cta-bg-orb hp-cta-bg-orb-2"></div>
    <div style="position: relative; z-index: 1; text-align: center;">
        <div class="hp-section-label" style="margin-bottom: 20px;">Ready to build?</div>
        <h2 style="font-size: 2.2rem; font-family: var(--font-heading); font-weight: 800; letter-spacing: -0.025em; margin-bottom: 16px; color: var(--text-primary);">
            Launch your projects today
        </h2>
        <p style="font-size: 1rem; color: var(--text-secondary); max-width: 520px; margin: 0 auto 36px; line-height: 1.7;">
            Join developers and teams using <?= htmlspecialchars(APP_NAME) ?> — one platform, unified login, 12+ tools ready to go.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <?php if (Auth::check()): ?>
                <a href="/dashboard" class="btn btn-primary btn-lg">Go to Dashboard</a>
            <?php else: ?>
                <a href="/register" class="btn btn-primary btn-lg">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    Create Free Account
                </a>
                <a href="/login" class="btn btn-secondary btn-lg">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.hp-cta-section {
    margin: 80px auto 40px;
    max-width: 900px;
    padding: 70px 30px;
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-xl);
    border: 1px solid rgba(153, 69, 255, 0.2);
    background: rgba(153, 69, 255, 0.04);
}

.hp-cta-bg-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
    z-index: 0;
}

.hp-cta-bg-orb-1 {
    width: 350px;
    height: 350px;
    background: rgba(153, 69, 255, 0.18);
    top: -100px;
    left: -80px;
}

.hp-cta-bg-orb-2 {
    width: 280px;
    height: 280px;
    background: rgba(0, 240, 255, 0.14);
    bottom: -80px;
    right: -60px;
}

[data-theme="light"] .hp-cta-section {
    background: rgba(124, 58, 237, 0.03);
    border-color: rgba(124, 58, 237, 0.15);
}

[data-theme="light"] .hp-cta-bg-orb-1 {
    background: rgba(124, 58, 237, 0.10);
}

[data-theme="light"] .hp-cta-bg-orb-2 {
    background: rgba(3, 105, 161, 0.08);
}

@media (max-width: 600px) {
    .hp-cta-section { margin: 50px 16px 20px; padding: 50px 20px; }
    .hp-cta-section h2 { font-size: 1.6rem !important; }
}
</style>

<script>
/* Animated stat counters — count up when scrolled into view */
(function() {
    function parseNum(val) {
        var clean = val.replace(/[^0-9]/g, '');
        return parseInt(clean, 10) || 0;
    }
    function formatNum(n, template) {
        var rounded = Math.round(n);
        // Re-apply suffix/prefix found in the original string
        var prefix = template.match(/^[^0-9]*/)[0] || '';
        var suffix = template.match(/[^0-9]+$/);
        suffix = suffix ? suffix[0] : '';
        // Format with commas
        var formatted = rounded.toLocaleString();
        return prefix + formatted + suffix;
    }
    function animateCounter(el) {
        var target = el.dataset.target || el.textContent;
        var num = parseNum(target);
        if (num === 0) return;
        var duration = 1600;
        var start = null;
        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            // ease-out
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNum(num * eased, target);
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target; // restore original
        }
        requestAnimationFrame(step);
    }

    if (!window.IntersectionObserver) return;
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    document.querySelectorAll('.hp-stat-count').forEach(function(el) {
        observer.observe(el);
    });
})();
</script>
<?php View::endSection(); ?>
