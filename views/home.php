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

    [data-theme="light"] .project-card:hover {
        box-shadow: 0 8px 28px rgba(124, 58, 237, 0.18) !important;
    }

    [data-theme="light"] #hp-mouse-glow {
        background: radial-gradient(circle, rgba(124, 58, 237, 0.10) 0%, rgba(3, 105, 161, 0.04) 40%, transparent 70%);
    }

    [data-theme="light"] .card:hover {
        border-color: rgba(124, 58, 237, 0.30) !important;
        box-shadow: 0 12px 36px rgba(124, 58, 237, 0.18), 0 0 0 1px rgba(124, 58, 237, 0.08) !important;
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
$projectsSectionTitle = $projectsSection['title'] ?? 'Explore Our Super Fast Products';

// Parse global card display settings
$_cardGlobal = [
    'global_thumb_intensity'  => 60,
    'override_thumb_intensity' => 0,
    'global_show_title'       => 1,
    'override_show_title'     => 0,
];
if (!empty($projectsSection['description'])) {
    $_decoded = json_decode($projectsSection['description'], true);
    if (is_array($_decoded)) {
        $_cardGlobal = array_merge($_cardGlobal, $_decoded);
    }
}

// Get hero banner slides
$heroSlides = [];
try {
    $heroSlides = $db->fetchAll("SELECT * FROM home_hero_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
} catch (Exception $e) {
    // Table may not exist yet
}

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

<div class="hero" style="padding: 50px 20px; max-width: 1400px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
        <!-- Left side: Text content -->
        <div style="text-align: left;">
            <h1 style="font-size: 2.5rem; margin-bottom: 16px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1.2;">
                <?= htmlspecialchars($heroTitle) ?>
            </h1>
            <?php if ($heroSubtitle && $heroSubtitle !== $heroTitle): ?>
            <h2 style="font-size: 1.5rem; margin-bottom: 12px; color: var(--text-primary);">
                <?= htmlspecialchars($heroSubtitle) ?>
            </h2>
            <?php endif; ?>
            <p style="font-size: 1.05rem; color: var(--text-secondary); margin-bottom: 30px; line-height: 1.6;">
                <?= htmlspecialchars($heroDescription) ?>
            </p>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <?php if (Auth::check()): ?>
                    <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
                <?php else: ?>
                    <a href="/register" class="btn btn-primary">Get Started</a>
                    <a href="/login" class="btn btn-secondary">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Right side: Hero banner slideshow -->
        <div style="text-align: center;">
            <?php if (!empty($heroSlides)): ?>
            <div class="hero-slideshow" id="heroSlideshow">
                <?php foreach ($heroSlides as $i => $slide): ?>
                <?php $tag = !empty($slide['link_url']) ? 'a' : 'div'; ?>
                <?php $attrs = !empty($slide['link_url']) ? ' href="' . htmlspecialchars($slide['link_url']) . '"' : ''; ?>
                <<?= $tag . $attrs ?> class="hero-slide<?= $i === 0 ? ' hero-slide--active' : '' ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
                    <img src="<?= htmlspecialchars($slide['image_url']) ?>" alt="Banner <?= $i + 1 ?>" class="hero-banner" style="max-width: 100%; height: auto; border-radius: 12px; box-shadow: var(--shadow-glow);">
                </<?= $tag ?>>
                <?php endforeach; ?>
                <?php if (count($heroSlides) > 1): ?>
                <div class="hero-dots" role="tablist" aria-label="Banner slides">
                    <?php foreach ($heroSlides as $i => $slide): ?>
                    <button class="hero-dot<?= $i === 0 ? ' hero-dot--active' : '' ?>" data-index="<?= $i ?>" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <!-- Placeholder if no slides -->
                <div style="width: 100%; aspect-ratio: 16/10; background: linear-gradient(135deg, rgba(0, 240, 255, 0.1), rgba(255, 46, 196, 0.1)); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 2px dashed var(--border-color);">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Hero slideshow */
.hero-slideshow {
    position: relative;
}
.hero-slide {
    display: none;
    text-decoration: none;
}
.hero-slide--active {
    display: block;
}
.hero-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 12px;
}
.hero-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: none;
    background: var(--border-color);
    cursor: pointer;
    padding: 0;
    transition: background 0.2s;
}
.hero-dot--active {
    background: var(--cyan);
}
</style>

<script>
(function () {
    var ss = document.getElementById('heroSlideshow');
    if (!ss) return;
    var slides = ss.querySelectorAll('.hero-slide');
    var dots   = ss.querySelectorAll('.hero-dot');
    if (slides.length < 2) return;
    var current = 0;
    var timer;

    function show(idx) {
        slides[current].classList.remove('hero-slide--active');
        slides[current].setAttribute('aria-hidden', 'true');
        if (dots[current]) { dots[current].classList.remove('hero-dot--active'); dots[current].setAttribute('aria-selected', 'false'); }
        current = (idx + slides.length) % slides.length;
        slides[current].classList.add('hero-slide--active');
        slides[current].setAttribute('aria-hidden', 'false');
        if (dots[current]) { dots[current].classList.add('hero-dot--active'); dots[current].setAttribute('aria-selected', 'true'); }
    }

    function next() { show(current + 1); }

    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { clearInterval(timer); show(i); timer = setInterval(next, 4000); });
    });

    timer = setInterval(next, 4000);
}());
</script>

<style>
/* Responsive hero section */
@media (max-width: 768px) {
    .hero > div {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
    
    .hero h1 {
        font-size: 1.8rem !important;
    }
    
    .hero h2 {
        font-size: 1.2rem !important;
    }
    
    .hero > div > div:first-child {
        text-align: center !important;
    }
    
    .hero > div > div:first-child > div {
        justify-content: center !important;
    }
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
    <h2 style="margin-bottom: 12px; font-size: 1.75rem;"><?= htmlspecialchars($statsHeading) ?></h2>
    <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 40px;"><?= htmlspecialchars($statsSubheading) ?></p>
    
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
        <div class="card stat-card animate-fade-in" style="animation-delay: <?= $delay ?>s;">
            <div class="stat-value" style="font-size: 2.5rem; font-weight: 700;">
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
    <h2 style="margin-bottom: 20px; font-size: 1.75rem;"><?= htmlspecialchars($projectsSectionTitle) ?></h2>
    
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
        
        foreach ($projects as $key => $project): 
            // Handle both database and config array formats
            $projectName = $project['name'] ?? '';
            $projectDescription = $project['description'] ?? '';
            $projectColor = $project['color'] ?? '#00f0ff';
            $projectUrl = $project['url'] ?? '';
            $projectTier = $project['tier'] ?? 'free';
            $showFeaturesText = $project['show_features_text'] ?? 'Show Features';
            $showFeaturesUrl  = $project['show_features_url'] ?? '';

            // Resolve show_title: global override wins if enabled
            if (!empty($_cardGlobal['override_show_title'])) {
                $showTitle = (bool)(int)$_cardGlobal['global_show_title'];
            } else {
                $showTitle = isset($project['show_title']) ? (bool)(int)$project['show_title'] : true;
            }

            // Resolve thumb_intensity: global override wins if enabled
            if (!empty($_cardGlobal['override_thumb_intensity'])) {
                $thumbIntensity = min(100, max(0, (int)$_cardGlobal['global_thumb_intensity']));
            } else {
                $thumbIntensity = isset($project['thumb_intensity']) ? min(100, max(0, (int)$project['thumb_intensity'])) : (int)$_cardGlobal['global_thumb_intensity'];
            }

            // Card link (auth-aware)
            $cardHref = Auth::check() ? htmlspecialchars($projectUrl) : '/login?redirect=' . urlencode($projectUrl);

            // Decode features for display
            $cardFeatures = [];
            if (!empty($project['features'])) {
                $_decoded = json_decode($project['features'], true);
                if (is_array($_decoded)) {
                    $cardFeatures = $_decoded;
                }
            }
        ?>
        <a href="<?= $cardHref ?>" class="project-card" data-tier="<?= htmlspecialchars($projectTier) ?>" style="display:block;text-decoration:none;color:inherit;position:relative;overflow:hidden;">
            <!-- Thumbnail image covers full card -->
            <?php if (!empty($project['image_url'])): ?>
                <img class="project-card__thumb" src="<?= htmlspecialchars($project['image_url']) ?>" alt="" style="opacity:<?= round($thumbIntensity / 100, 2) ?>;">
            <?php else: ?>
                <div class="project-card__thumb project-card__thumb--placeholder" style="background: linear-gradient(135deg, <?= $projectColor ?>33, <?= $projectColor ?>11);"></div>
            <?php endif; ?>

            <!-- Gradient overlay for readability -->
            <div class="project-card__overlay"></div>

            <!-- Tier badge – absolute top-right -->
            <?php
            $badgeStyles = [
                'free'       => 'background:rgba(0,255,136,0.28);color:#00ff88;border:1px solid rgba(0,255,136,0.55);',
                'freemium'   => 'background:rgba(255,170,0,0.28);color:#ffaa00;border:1px solid rgba(255,170,0,0.55);',
                'enterprise' => 'background:rgba(153,69,255,0.28);color:#bb88ff;border:1px solid rgba(153,69,255,0.55);',
            ];
            $badgeStyle = $badgeStyles[$projectTier] ?? $badgeStyles['free'];
            ?>
            <span class="project-card__tier" style="<?= $badgeStyle ?>">
                <?= htmlspecialchars($projectTier === 'enterprise' ? 'Enterprise' : ucfirst($projectTier)) ?>
            </span>

            <!-- Inner content above overlay -->
            <div class="project-card__body">
                <!-- Top-center: logo + title -->
                <div class="project-card__header">
                    <div class="project-card__logo" style="background: <?= $projectColor ?>40; border-color: <?= $projectColor ?>80;">
                        <?php if (!empty($project['logo_url'])): ?>
                            <img src="<?= htmlspecialchars($project['logo_url']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:9px;">
                        <?php else: ?>
                            <span style="font-size: 1.3rem; font-weight: 700; color: <?= $projectColor ?>;"><?= strtoupper(substr($projectName, 0, 2)) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($showTitle): ?>
                    <h3 class="project-card__title" style="color: #fff; text-shadow: 0 0 12px <?= $projectColor ?>, 0 2px 6px rgba(0,0,0,0.8);"><?= htmlspecialchars($projectName) ?></h3>
                    <?php endif; ?>
                </div>

                <!-- Key features list (falls back to description) -->
                <?php if (!empty($cardFeatures)): ?>
                <ul class="project-card__features">
                    <?php foreach ($cardFeatures as $feat): ?>
                    <li><?= htmlspecialchars($feat) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="project-card__desc"><?= htmlspecialchars($projectDescription) ?></p>
                <?php endif; ?>

                <!-- Buttons: bottom-right -->
                <div class="project-card__actions">
                    <?php if (!empty($showFeaturesUrl)): ?>
                        <a href="<?= htmlspecialchars($showFeaturesUrl) ?>" class="project-card__btn project-card__btn--outline" style="border-color:<?= $projectColor ?>80;color:<?= $projectColor ?>;" onclick="event.stopPropagation();">
                            <?= htmlspecialchars($showFeaturesText) ?>
                        </a>
                    <?php else: ?>
                        <button type="button" disabled class="project-card__btn project-card__btn--outline" style="border-color:<?= $projectColor ?>80;color:<?= $projectColor ?>;opacity:0.7;cursor:default;" onclick="event.stopPropagation();">
                            <?= htmlspecialchars($showFeaturesText) ?>
                        </button>
                    <?php endif; ?>
                    <?php if (Auth::check()): ?>
                        <a href="<?= htmlspecialchars($projectUrl) ?>" class="project-card__btn project-card__btn--primary" style="background:<?= $projectColor ?>;border-color:<?= $projectColor ?>;" onclick="event.stopPropagation();">
                            Explore
                        </a>
                    <?php else: ?>
                        <a href="/login?redirect=<?= urlencode($projectUrl) ?>" class="project-card__btn project-card__btn--primary" style="background:<?= $projectColor ?>;border-color:<?= $projectColor ?>;" onclick="event.stopPropagation();">
                            Explore
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
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
    box-shadow: 0 4px 12px rgba(0, 240, 255, 0.25);
}

.filter-btn.active {
    background: linear-gradient(135deg, #00c8ff 0%, #0070f3 100%) !important;
    border-color: transparent !important;
    color: #ffffff !important;
    box-shadow: 0 4px 18px rgba(0, 112, 243, 0.45) !important;
}

/* ── Project Card ── */
.project-card {
    position: relative;
    overflow: hidden;
    border-radius: 14px;
    aspect-ratio: 4 / 3;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255,255,255,0.08);
}

[data-theme="light"] .project-card {
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
    border-color: rgba(0,0,0,0.08);
}

.project-card.filtered-out {
    display: none;
}

/* Thumbnail fills card */
.project-card__thumb {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 0;
}

.project-card__thumb--placeholder {
    position: absolute;
    inset: 0;
    z-index: 0;
}

/* Stronger gradient overlay for legibility */
.project-card__overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    background: linear-gradient(
        to bottom,
        rgba(6, 8, 18, 0.80) 0%,
        rgba(6, 8, 18, 0.40) 40%,
        rgba(6, 8, 18, 0.88) 100%
    );
}

[data-theme="light"] .project-card__overlay {
    background: linear-gradient(
        to bottom,
        rgba(10, 10, 28, 0.75) 0%,
        rgba(10, 10, 28, 0.38) 40%,
        rgba(10, 10, 28, 0.82) 100%
    );
}

/* Tier badge – absolute top-right corner */
.project-card__tier {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 3;
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

/* Content sits above overlay */
.project-card__body {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    flex-direction: column;
    padding: 16px;
    overflow: hidden;
}

/* Top-center: logo + title */
.project-card__header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
}

/* Logo */
.project-card__logo {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    border: 2px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(0,0,0,0.5);
}

.project-card__title {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: 0.2px;
}

/* Description */
.project-card__desc {
    flex: 1;
    color: rgba(255,255,255,0.90);
    font-size: 12px;
    line-height: 1.55;
    margin: 12px 0 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-shadow: 0 1px 4px rgba(0,0,0,0.7);
}

/* Key features list inside project card */
.project-card__features {
    flex: 1;
    list-style: none;
    padding: 0;
    margin: 10px 0 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    overflow: hidden;
}

.project-card__features li {
    color: rgba(255,255,255,0.88);
    font-size: 11.5px;
    line-height: 1.45;
    padding-left: 14px;
    position: relative;
    text-shadow: 0 1px 4px rgba(0,0,0,0.7);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.project-card__features li::before {
    content: '✦';
    position: absolute;
    left: 0;
    font-size: 8px;
    top: 2px;
    opacity: 0.75;
}

/* Action buttons – bottom-right */
.project-card__actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.project-card__btn {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    white-space: nowrap;
    border: 1.5px solid transparent;
}

.project-card__btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0,0,0,0.4);
    opacity: 0.92;
}

.project-card__btn--outline {
    background: rgba(0,0,0,0.35);
    backdrop-filter: blur(6px);
}

.project-card__btn--primary {
    color: #fff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            projectCards.forEach(card => {
                if (filter === 'all' || card.dataset.tier === filter) {
                    card.classList.remove('filtered-out');
                } else {
                    card.classList.add('filtered-out');
                }
            });
        });
    });
});
</script>

<div style="margin-top: 60px; padding: 40px 20px; background: rgba(0, 240, 255, 0.02); border-radius: 16px; max-width: 1500px; margin-left: auto; margin-right: auto;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="margin-bottom: 12px; font-size: 1.75rem;"><?= htmlspecialchars($featuresHeading) ?></h2>
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
        <h2 style="margin-bottom: 15px; font-size: 2rem;"><?= View::e($timelineHeading) ?></h2>
        <p style="color: var(--text-secondary); font-size: 1.1rem;"><?= View::e($timelineSubheading) ?></p>
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
<?php View::endSection(); ?>
