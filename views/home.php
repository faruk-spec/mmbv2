<?php use Core\View; use Core\Database; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
    /* ── Futuristic Home Page Theme ── */

    /* Dark theme: override background for homepage — matches new indigo-black palette */
    [data-theme="dark"] body.home-page,
    :root:not([data-theme="light"]) body.home-page {
        background: #08091a !important;
    }

    /* Particles layer */
    #home-particles {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
    }

    /* Mouse glow overlay */
    #home-mouse-glow {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background: radial-gradient(600px circle at var(--mx, 50%) var(--my, 50%), rgba(124, 58, 237, 0.12) 0%, transparent 70%);
        transition: background 0.05s ease;
    }

    /* Ensure content sits above particles */
    .home-page > main,
    .home-page .hero,
    .home-page .card,
    .home-page [class*="grid"],
    .home-page footer {
        position: relative;
        z-index: 1;
    }

    /* ── Gradient text: purple → cyan ── */
    .home-page .hero h1,
    .home-page .stat-value {
        background: linear-gradient(135deg, #7C3AED, #00F5FF) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }

    /* ── Glassmorphism cards ── */
    .home-page .card {
        background: rgba(12, 13, 36, 0.65) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        border: 1px solid rgba(100, 120, 255, 0.2) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.03) !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease !important;
    }

    .home-page .card:hover {
        transform: translateY(-6px) !important;
        box-shadow: 0 16px 40px rgba(124, 58, 237, 0.3), 0 0 20px rgba(0, 245, 255, 0.15) !important;
        border-color: rgba(0, 245, 255, 0.45) !important;
    }

    /* ── Neon stat value ── */
    .home-page .stat-value {
        text-shadow: 0 0 20px rgba(0, 245, 255, 0.4);
    }

    /* ── Pulse animation for stat values ── */
    @keyframes homePulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }

    .home-page .stat-value {
        animation: homePulse 3s ease-in-out infinite;
    }

    /* ── fadeIn animation ── */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }

    /* ── Hero banner ── */
    .hero-banner {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 0 40px rgba(124, 58, 237, 0.35);
        margin-bottom: 30px;
        animation: fadeIn 0.8s ease-out;
    }

    /* ── Timeline item ── */
    .timeline-item {
        position: relative;
        padding-left: 70px;
        margin-bottom: 30px;
    }

    .timeline-badge {
        position: absolute;
        left: 0;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #7C3AED, #00F5FF);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 0 15px rgba(124, 58, 237, 0.5);
    }

    /* ── Three.js sphere canvas ── */
    #home-sphere-canvas {
        width: 100% !important;
        height: 100% !important;
        border-radius: 12px;
    }

    /* ── Filter buttons neon style ── */
    .home-page .filter-btn {
        transition: all 0.3s ease !important;
    }

    .home-page .filter-btn.active {
        box-shadow: 0 0 15px rgba(0, 245, 255, 0.5) !important;
    }

    /* ── Neon hover on project cards ── */
    .home-page .project-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
    }

    .home-page .project-card:hover {
        box-shadow: 0 0 25px rgba(124, 58, 237, 0.4), 0 8px 30px rgba(0,0,0,0.4) !important;
    }

    /* ── Subtle neon glow on buttons ── */
    .home-page .btn-primary {
        box-shadow: 0 0 20px rgba(124, 58, 237, 0.4) !important;
        transition: box-shadow 0.3s ease, transform 0.3s ease !important;
    }

    .home-page .btn-primary:hover {
        box-shadow: 0 0 30px rgba(0, 245, 255, 0.6) !important;
        transform: translateY(-2px) !important;
    }

    @media (max-width: 768px) {
        .grid-3, .grid-4 { grid-template-columns: 1fr; }
        .hero h1 { font-size: 2.2rem !important; }
        .hero h2 { font-size: 1.7rem !important; }
    }

    /* ── Light theme overrides ── */
    /* Particles show in light mode too, with light-appropriate colors */
    [data-theme="light"] .home-page .card {
        background: rgba(255, 255, 255, 0.88) !important;
        border: 1px solid rgba(99, 102, 241, 0.22) !important;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.08) !important;
        backdrop-filter: blur(10px) !important;
    }

    [data-theme="light"] .home-page .card:hover {
        box-shadow: 0 8px 32px rgba(99, 102, 241, 0.18) !important;
        border-color: rgba(99, 102, 241, 0.45) !important;
    }

    [data-theme="light"] .home-page .hero h1,
    [data-theme="light"] .home-page .stat-value {
        background: linear-gradient(135deg, #6366f1, #0891b2) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }

    [data-theme="light"] .home-page .btn-primary {
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.3) !important;
    }

    /* Light-mode mouse glow uses softer indigo tint */
    [data-theme="light"] #home-mouse-glow {
        background: radial-gradient(500px circle at var(--mx, 50%) var(--my, 50%), rgba(99, 102, 241, 0.08) 0%, transparent 70%) !important;
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
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

<div class="hero" style="padding: 50px 20px; max-width: 1400px; margin: 0 auto;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
        <!-- Left side: Text content -->
        <div style="text-align: left;">
            <h1 style="font-size: 3rem; margin-bottom: 16px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1.2;">
                <?= htmlspecialchars($heroTitle) ?>
            </h1>
            <?php if ($heroSubtitle && $heroSubtitle !== $heroTitle): ?>
            <h2 style="font-size: 1.9rem; margin-bottom: 12px; color: var(--text-primary);">
                <?= htmlspecialchars($heroSubtitle) ?>
            </h2>
            <?php endif; ?>
            <p style="font-size: 1.5rem; color: var(--text-secondary); margin-bottom: 30px; line-height: 1.6;">
                <?= htmlspecialchars($heroDescription) ?>
            </p>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
                    <a href="/projects" class="btn btn-secondary">Browse Projects</a>
                <?php else: ?>
                    <a href="/register" class="btn btn-primary">Get Started</a>
                    <a href="/login" class="btn btn-secondary">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Right side: Hero banner image -->
        <div style="text-align: center;">
            <?php if (!empty($heroBanner)): ?>
                <img src="<?= htmlspecialchars($heroBanner) ?>" alt="Hero Banner" class="hero-banner" style="max-width: 100%; height: auto; border-radius: 12px; box-shadow: var(--shadow-glow);">
            <?php else: ?>
                <!-- Placeholder if no image -->
                <div data-home-sphere-target style="width: 100%; aspect-ratio: 16/10; background: linear-gradient(135deg, rgba(0, 240, 255, 0.1), rgba(255, 46, 196, 0.1)); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 2px dashed var(--border-color);">
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
/* Responsive hero section */
@media (max-width: 768px) {
    .hero > div {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
    
    .hero h1 {
        font-size: 2.2rem !important;
    }
    
    .hero h2 {
        font-size: 1.7rem !important;
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
        <h3 style="margin-bottom: 8px; font-size: 1.4rem;">Secure by Design</h3>
        <p style="color: var(--text-secondary); font-size: 1.4rem;">
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
        <h3 style="margin-bottom: 8px; font-size: 1.4rem;">Single Sign-On</h3>
        <p style="color: var(--text-secondary); font-size: 1.4rem;">
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
        <h3 style="margin-bottom: 8px; font-size: 1.4rem;">Unified Admin</h3>
        <p style="color: var(--text-secondary); font-size: 1.4rem;">
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
    <h2 style="margin-bottom: 12px; font-size: 2.2rem;"><?= htmlspecialchars($statsHeading) ?></h2>
    <p style="color: var(--text-secondary); font-size: 1.4rem; margin-bottom: 40px;"><?= htmlspecialchars($statsSubheading) ?></p>
    
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
            <h4 style="margin-bottom: 4px; font-size: 1.4rem; color: var(--text-primary); font-weight: 600;"><?= htmlspecialchars($stat['label']) ?></h4>
            <?php if (!empty($stat['description'])): ?>
            <p style="font-size: 1.3rem; color: var(--text-secondary);"><?= htmlspecialchars($stat['description']) ?></p>
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
    <h2 style="margin-bottom: 20px; font-size: 2.2rem;"><?= htmlspecialchars($projectsSectionTitle) ?></h2>
    
    <!-- Filter Buttons -->
    <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 40px; flex-wrap: wrap;">
        <button class="filter-btn active" data-filter="all" style="padding: 10px 24px; border-radius: 25px; border: 2px solid var(--cyan); background: var(--cyan); color: var(--bg-primary); font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px;">
            All Tools
        </button>
        <button class="filter-btn" data-filter="free" style="padding: 10px 24px; border-radius: 25px; border: 2px solid var(--border-color); background: transparent; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px;">
            Free Tools
        </button>
        <button class="filter-btn" data-filter="freemium" style="padding: 10px 24px; border-radius: 25px; border: 2px solid var(--border-color); background: transparent; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px;">
            Freemium
        </button>
        <button class="filter-btn" data-filter="enterprise" style="padding: 10px 24px; border-radius: 25px; border: 2px solid var(--border-color); background: transparent; color: var(--text-primary); font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 14px;">
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
            <div style="position: absolute; top: 12px; right: 12px; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 700; text-transform: uppercase; 
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
                <div style="width: 36px; height: 36px; background: <?= $projectColor ?>20; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $projectColor ?>" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                </div>
                <h3 style="color: <?= $projectColor ?>; font-size: 1.5rem;"><?= htmlspecialchars($projectName) ?></h3>
            </div>
            
            <p style="color: var(--text-secondary); font-size: 1.4rem; margin-bottom: 16px; line-height: 1.5;">
                <?= htmlspecialchars($projectDescription) ?>
            </p>
            
            <!-- Collapsible Features Section -->
            <div class="project-details" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease; margin-bottom: 16px;">
                <div style="padding: 12px; background: rgba(0, 240, 255, 0.03); border-radius: 8px; margin-bottom: 12px;">
                    <h4 style="font-size: 14px; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Key Features</h4>
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
                        <li style="font-size: 14px; color: var(--text-primary); padding: 4px 0; display: flex; align-items: center; gap: 8px;">
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
            <button class="toggle-details" style="width: 100%; padding: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-secondary); font-size: 14px; cursor: pointer; margin-bottom: 12px; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span class="toggle-text">Show Features</span>
                <svg class="toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.3s ease;">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            
            <?php if (isset($_SESSION['user'])): ?>
                <a href="<?= htmlspecialchars($projectUrl) ?>" class="btn btn-primary" style="width: 100%; background: <?= $projectColor ?>; border-color: <?= $projectColor ?>;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                    Access Project
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
.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 240, 255, 0.3);
}

.filter-btn.active {
    background: var(--cyan) !important;
    border-color: var(--cyan) !important;
    color: var(--bg-primary) !important;
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

<div style="margin-top: 60px; padding: 40px 20px; background: rgba(0, 240, 255, 0.02); border-radius: 16px; max-width: 1500px; margin-left: auto; margin-right: auto;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="margin-bottom: 12px; font-size: 2.2rem;"><?= htmlspecialchars($featuresHeading) ?></h2>
        <p style="color: var(--text-secondary); font-size: 1.4rem;"><?= htmlspecialchars($featuresSubheading) ?></p>
    </div>
    
    <div class="grid grid-4" style="padding: 0 10px;">
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0s;">
            <div style="width: 45px; height: 45px; background: rgba(0, 240, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--cyan); font-size: 1.4rem;">Performance</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">Smart caching, optimized queries, asset bundling</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.1s;">
            <div style="width: 45px; height: 45px; background: rgba(255, 46, 196, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                    <polyline points="16 6 12 2 8 6"/>
                    <line x1="12" y1="2" x2="12" y2="15"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--magenta); font-size: 1.4rem;">REST API</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">Full API access with authentication & rate limiting</p>
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
            <h4 style="margin-bottom: 4px; color: var(--green); font-size: 1.4rem;">Real-time</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">WebSocket server for live collaboration</p>
        </div>
        
        <div class="animate-fade-in" style="text-align: center; padding: 16px; animation-delay: 0.3s;">
            <div style="width: 45px; height: 45px; background: rgba(255, 170, 0, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <h4 style="margin-bottom: 4px; color: var(--orange); font-size: 1.4rem;">Notifications</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">Email queue, multi-channel alerts</p>
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
            <h4 style="margin-bottom: 4px; color: var(--purple); font-size: 1.4rem;">Analytics</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">Track usage, generate reports</p>
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
            <h4 style="margin-bottom: 4px; color: var(--cyan); font-size: 1.4rem;">Templates</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">Code templates & snippets library</p>
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
            <h4 style="margin-bottom: 4px; color: var(--magenta); font-size: 1.4rem;">OCR Advanced</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">PDF processing, table detection, batch jobs</p>
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
            <h4 style="margin-bottom: 4px; color: var(--green); font-size: 1.4rem;">Sharing</h4>
            <p style="font-size: 1.3rem; color: var(--text-secondary);">QR codes, social media, custom links</p>
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
        <h2 style="margin-bottom: 15px; font-size: 2.4rem;"><?= View::e($timelineHeading) ?></h2>
        <p style="color: var(--text-secondary); font-size: 1.5rem;"><?= View::e($timelineSubheading) ?></p>
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
                            <h3 style="color: <?= View::e($itemColor) ?>; margin-bottom: 10px; font-size: 1.6rem;">
                                <?= View::e($item['title']) ?>
                            </h3>
                            <?php if ($item['description']): ?>
                            <p style="color: var(--text-secondary); font-size: 1.35rem; line-height: 1.6; margin-bottom: 10px;">
                                <?= View::e($item['description']) ?>
                            </p>
                            <?php endif; ?>
                            <?php if ($item['date_display']): ?>
                            <div style="color: <?= View::e($itemColor) ?>; font-size: 1.3rem; font-weight: 600;">
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
                            <h3 style="color: <?= View::e($itemColor) ?>; margin-bottom: 10px; font-size: 1.6rem;">
                                <?= View::e($item['title']) ?>
                            </h3>
                            <?php if ($item['description']): ?>
                            <p style="color: var(--text-secondary); font-size: 1.35rem; line-height: 1.6; margin-bottom: 10px;">
                                <?= View::e($item['description']) ?>
                            </p>
                            <?php endif; ?>
                            <?php if ($item['date_display']): ?>
                            <div style="color: <?= View::e($itemColor) ?>; font-size: 1.3rem; font-weight: 600;">
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
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
(function() {
    // Add home-page class to body for scoped CSS
    document.body.classList.add('home-page');

    var isDark = document.documentElement.getAttribute('data-theme') !== 'light';

    // ── Color palettes per theme ──
    var DARK_COLORS  = ['#7C3AED', '#00F5FF', '#a855f7'];
    var LIGHT_COLORS = ['rgba(109,40,217,0.6)', 'rgba(8,145,178,0.5)', 'rgba(139,92,246,0.55)'];
    var DARK_LINE    = function(alpha) { return 'rgba(124,58,237,' + alpha + ')'; };
    var LIGHT_LINE   = function(alpha) { return 'rgba(109,40,217,' + alpha + ')'; };

    // ── Pure-JS canvas particle system (runs in both dark and light modes) ──
    var pCanvas = document.createElement('canvas');
    pCanvas.id = 'home-particles';
    pCanvas.style.cssText = 'position:fixed;inset:0;z-index:0;pointer-events:none;';
    document.body.insertBefore(pCanvas, document.body.firstChild);

    var ctx = pCanvas.getContext('2d');
    var particles = [];
    var COLORS = isDark ? DARK_COLORS : LIGHT_COLORS;
    var lineColorFn = isDark ? DARK_LINE : LIGHT_LINE;

    function resizeParticles() {
        pCanvas.width  = window.innerWidth;
        pCanvas.height = window.innerHeight;
    }

    function mkParticle() {
        return {
            x: Math.random() * pCanvas.width,
            y: Math.random() * pCanvas.height,
            vx: (Math.random() - 0.5) * 1.2,
            vy: (Math.random() - 0.5) * 1.2,
            r:  Math.random() * 2 + 0.5,
            op: Math.random() * 0.4 + 0.15,
            color: COLORS[Math.floor(Math.random() * COLORS.length)]
        };
    }

    function initParticles() {
        resizeParticles();
        particles = Array.from({ length: 70 }, mkParticle);
    }

    function drawParticles() {
        ctx.clearRect(0, 0, pCanvas.width, pCanvas.height);
        var len = particles.length;
        for (var i = 0; i < len; i++) {
            var p = particles[i];
            p.x += p.vx;  p.y += p.vy;
            if (p.x < 0 || p.x > pCanvas.width)  { p.vx *= -1; p.x = Math.max(0, Math.min(p.x, pCanvas.width)); }
            if (p.y < 0 || p.y > pCanvas.height) { p.vy *= -1; p.y = Math.max(0, Math.min(p.y, pCanvas.height)); }
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = p.op;
            ctx.fill();
            for (var j = i + 1; j < len; j++) {
                var q = particles[j];
                var dx = p.x - q.x, dy = p.y - q.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 140) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(q.x, q.y);
                    ctx.strokeStyle = lineColorFn(0.18 * (1 - dist / 140));
                    ctx.globalAlpha = 1;
                    ctx.lineWidth = 0.7;
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(drawParticles);
    }

    initParticles();
    drawParticles();
    window.addEventListener('resize', function() {
        resizeParticles();
        particles.forEach(function(p) {
            p.x = Math.min(p.x, pCanvas.width);
            p.y = Math.min(p.y, pCanvas.height);
        });
    });

    // ── Radial mouse glow (throttled to rAF) — always shown ──
    var glowEl = document.createElement('div');
    glowEl.id = 'home-mouse-glow';
    document.body.insertBefore(glowEl, document.body.firstChild);
    var rafPending = false;
    document.addEventListener('mousemove', function(e) {
        if (!rafPending) {
            rafPending = true;
            requestAnimationFrame(function() {
                glowEl.style.setProperty('--mx', e.clientX + 'px');
                glowEl.style.setProperty('--my', e.clientY + 'px');
                rafPending = false;
            });
        }
    });

    // Listen for theme changes: re-color particles to match new theme
    document.addEventListener('themeChanged', function(e) {
        var dark = e.detail.theme === 'dark';
        COLORS = dark ? DARK_COLORS : LIGHT_COLORS;
        lineColorFn = dark ? DARK_LINE : LIGHT_LINE;
        // Re-assign particle colors
        particles.forEach(function(p) {
            p.color = COLORS[Math.floor(Math.random() * COLORS.length)];
        });
    });

    // ── Pure-JS canvas wireframe sphere (no Three.js dependency) ──
    var heroPlaceholder = document.querySelector('[data-home-sphere-target]');
    if (heroPlaceholder) {
        heroPlaceholder.innerHTML = '';
        var sphCanvas = document.createElement('canvas');
        sphCanvas.id = 'home-sphere-canvas';
        sphCanvas.style.cssText = 'width:100%;height:100%;border-radius:12px;display:block;';
        heroPlaceholder.style.background = 'transparent';
        heroPlaceholder.appendChild(sphCanvas);

        var sc = sphCanvas.getContext('2d');
        var sphAngle = 0;

        function resizeSphere() {
            sphCanvas.width  = heroPlaceholder.offsetWidth  || 400;
            sphCanvas.height = heroPlaceholder.offsetHeight || 260;
        }

        function drawSphere() {
            var W = sphCanvas.width, H = sphCanvas.height;
            sc.clearRect(0, 0, W, H);
            var cx = W / 2, cy = H / 2;
            var R  = Math.min(cx, cy) * 0.78;
            var a  = sphAngle;

            // Latitude rings
            for (var lat = -75; lat <= 75; lat += 25) {
                var phi   = lat * Math.PI / 180;
                var rRing = R * Math.cos(phi);
                var yRing = cy + R * Math.sin(phi);
                var xScale = Math.abs(Math.cos(a));
                sc.beginPath();
                sc.ellipse(cx, yRing, rRing, rRing * 0.28 * xScale, 0, 0, Math.PI * 2); /* 0.28: perspective foreshortening for rings viewed at ~73° inclination */
                sc.strokeStyle = 'rgba(124,58,237,' + (0.25 + 0.45 * Math.abs(Math.cos(phi))) + ')';
                sc.lineWidth = 0.9;
                sc.stroke();
            }

            // Meridian rings (vertical)
            for (var lng = 0; lng < 180; lng += 30) {
                var theta  = (lng * Math.PI / 180) + a;
                var xScale2 = Math.abs(Math.cos(theta));
                sc.beginPath();
                sc.ellipse(cx, cy, R * xScale2, R, 0, 0, Math.PI * 2);
                sc.strokeStyle = 'rgba(0,245,255,' + (0.08 + 0.3 * xScale2) + ')';
                sc.lineWidth = 0.9;
                sc.stroke();
            }
        }

        function animateSphereCanvas() {
            sphAngle += 0.008;
            drawSphere();
            requestAnimationFrame(animateSphereCanvas);
        }

        resizeSphere();
        animateSphereCanvas();
        window.addEventListener('resize', resizeSphere);
    }
})();
</script>
<?php View::endSection(); ?>
