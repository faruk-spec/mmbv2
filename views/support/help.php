<?php
/**
 * Help & Resources / Knowledge Base
 */
use Core\View;
View::extend('main');
?>

<?php View::section('styles'); ?>
<?php include __DIR__ . '/_styles.php'; ?>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="sp-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div class="sp-main">
            <!-- Mobile menu button -->
            <button class="sp-menu-btn" onclick="spOpenMenu()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                Menu
            </button>
            <div style="margin-bottom:22px;">
                <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary);margin:0 0 4px;display:flex;align-items:center;gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--orange)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    Help & Resources
                </h1>
                <p style="color:var(--text-secondary);margin:0;font-size:.85rem;">Guides, documentation, and knowledge base articles.</p>
            </div>

            <!-- Topic cards -->
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:24px;">
                <?php
                $topics = [
                    ['icon'=>'rocket',       'color'=>'#00f0ff', 'title'=>'Getting Started',     'desc'=>'Set up your account and learn the basics.'],
                    ['icon'=>'ticket',       'color'=>'#ff9f43', 'title'=>'Ticket Management',   'desc'=>'Creating, replying, and managing support tickets.'],
                    ['icon'=>'user-shield',  'color'=>'#a78bfa', 'title'=>'Account & Security',  'desc'=>'Passwords, two-factor auth, and billing.'],
                    ['icon'=>'puzzle-piece', 'color'=>'#ff2ec4', 'title'=>'Integrations',        'desc'=>'Connect your apps and configure workflows.'],
                    ['icon'=>'chart-bar',    'color'=>'#00ff88', 'title'=>'Analytics',           'desc'=>'Understand your usage and performance stats.'],
                    ['icon'=>'gear',         'color'=>'#ff6b6b', 'title'=>'Settings',            'desc'=>'Platform configuration and preferences.'],
                ];
                foreach ($topics as $t):
                ?>
                <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:18px;cursor:default;transition:border-color .15s;" onmouseover="this.style.borderColor='<?= $t['color'] ?>44'" onmouseout="this.style.borderColor='rgba(255,255,255,.07)'">
                    <div style="width:36px;height:36px;border-radius:9px;background:<?= $t['color'] ?>18;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                        <i class="fas fa-<?= $t['icon'] ?>" style="color:<?= $t['color'] ?>;font-size:.95rem;"></i>
                    </div>
                    <div style="font-weight:600;color:var(--text-primary);font-size:.88rem;margin-bottom:5px;"><?= $t['title'] ?></div>
                    <div style="color:var(--text-secondary);font-size:.78rem;line-height:1.5;"><?= $t['desc'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Knowledge base coming soon notice -->
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:28px;text-align:center;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--orange)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.5;display:block;margin:0 auto 14px;"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <div style="font-weight:600;color:var(--text-primary);margin-bottom:8px;">Knowledge Base Coming Soon</div>
                <p style="color:var(--text-secondary);font-size:.87rem;margin:0 0 16px;">Detailed guides and documentation articles will be available here.</p>
                <a href="/support/faq" style="display:inline-flex;align-items:center;gap:7px;padding:8px 18px;background:rgba(255,159,67,.12);border:1px solid rgba(255,159,67,.25);border-radius:7px;color:var(--orange);text-decoration:none;font-size:.87rem;font-weight:600;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    View FAQ Instead
                </a>
            </div>
        </div><!-- /main content -->
</div><!-- /sp-layout -->

<?php View::endSection(); ?>
