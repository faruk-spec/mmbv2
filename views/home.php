<?php use Core\View; use Core\Database; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }
    
    .hero-banner {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        box-shadow: var(--shadow-glow);
        margin-bottom: 30px;
        animation: fadeIn 0.8s ease-out;
    }
    
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 240, 255, 0.3);
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--cyan), var(--magenta));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 8px;
        animation: fadeIn 1s ease-out;
    }
    
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
        background: linear-gradient(135deg, var(--cyan), var(--magenta));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        font-size: 13px;
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

<div class="hero" style="text-align: center; padding: 50px 20px; max-width: 1100px; margin: 0 auto;">
    <?php if (!empty($heroBanner)): ?>
    <div style="margin-bottom: 30px;">
        <img src="<?= htmlspecialchars($heroBanner) ?>" alt="Hero Banner" class="hero-banner" style="display: block;">
    </div>
    <?php endif; ?>
    
    <h1 style="font-size: 2.2rem; margin-bottom: 16px; background: linear-gradient(135deg, var(--cyan), var(--magenta)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
        <?= htmlspecialchars($heroTitle) ?>
    </h1>
    <?php if ($heroSubtitle && $heroSubtitle !== $heroTitle): ?>
    <h2 style="font-size: 1.4rem; margin-bottom: 12px; color: var(--text-primary);">
        <?= htmlspecialchars($heroSubtitle) ?>
    </h2>
    <?php endif; ?>
    <p style="font-size: 1rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto 30px;">
        <?= htmlspecialchars($heroDescription) ?>
    </p>
    
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="/register" class="btn btn-primary">Get Started</a>
        <a href="/login" class="btn btn-secondary">Sign In</a>
    </div>
</div>

<div class="grid grid-3" style="margin-top: 40px; max-width: 1200px; margin-left: auto; margin-right: auto;">
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
<div style="margin-top: 60px; text-align: center; max-width: 1300px; margin-left: auto; margin-right: auto; padding: 0 20px;">
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



<div style="margin-top: 60px; text-align: center; max-width: 1300px; margin-left: auto; margin-right: auto; padding: 0 20px;">
    <h2 style="margin-bottom: 30px; font-size: 1.75rem;"><?= htmlspecialchars($projectsSectionTitle) ?></h2>
    <div class="grid grid-3">
        <?php 
        $projects = require BASE_PATH . '/config/projects.php';
        $delay = 0;
        foreach ($projects as $key => $project): 
        ?>
        <div class="card animate-fade-in" style="border-color: <?= $project['color'] ?>30; animation-delay: <?= $delay ?>s;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div style="width: 36px; height: 36px; background: <?= $project['color'] ?>20; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $project['color'] ?>" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                </div>
                <h3 style="color: <?= $project['color'] ?>; font-size: 1.1rem;"><?= $project['name'] ?></h3>
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 16px;"><?= $project['description'] ?></p>
            
            <?php if (isset($_SESSION['user'])): ?>
                <a href="<?= $project['url'] ?>" class="btn btn-primary" style="width: 100%; background: <?= $project['color'] ?>; border-color: <?= $project['color'] ?>;">
                    <i class="fas fa-arrow-right"></i> Access Project
                </a>
            <?php else: ?>
                <a href="/login?redirect=<?= urlencode($project['url']) ?>" class="btn btn-secondary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Access
                </a>
            <?php endif; ?>
        </div>
        <?php 
        $delay += 0.1;
        endforeach; 
        ?>
    </div>
</div>

<div style="margin-top: 60px; padding: 40px 20px; background: rgba(0, 240, 255, 0.02); border-radius: 16px; max-width: 1300px; margin-left: auto; margin-right: auto;">
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
    
    .timeline-item > div > div:first-child,
    .timeline-item > div > div:last-child {
        display: none !important;
    }
    
    .timeline-item > div > div:nth-child(3) {
        text-align: left !important;
    }
    
    .timeline-card {
        max-width: 100% !important;
    }
}
</style>
<?php endif; ?>
<?php View::endSection(); ?>
