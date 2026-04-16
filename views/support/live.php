<?php
/**
 * Live Support Page
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
    <div class="sp-main" style="max-width:760px;">
            <!-- Mobile menu button -->
            <button class="sp-menu-btn" onclick="spOpenMenu()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                Menu
            </button>
            <div style="margin-bottom:22px;">
                <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary);margin:0 0 4px;display:flex;align-items:center;gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--magenta)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    Live Support
                </h1>
                <p style="color:var(--text-secondary);margin:0;font-size:.85rem;">Chat live with our support team for immediate assistance.</p>
            </div>

            <!-- Status card -->
            <div style="background:linear-gradient(135deg,rgba(0,240,255,.07),rgba(255,46,196,.05));border:1px solid rgba(0,240,255,.18);border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--magenta));display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                </div>
                <div style="display:flex;align-items:center;justify-content:center;gap:7px;margin-bottom:10px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);animation:pulse 2s infinite;"></div>
                    <span style="font-weight:600;color:var(--green);font-size:.9rem;">Support Online</span>
                </div>
                <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin:0 0 8px;">Ready to help you</h2>
                <p style="color:var(--text-secondary);font-size:.88rem;margin:0 0 20px;line-height:1.6;">
                    Our live chat widget is available at the bottom-right corner of every page.<br>
                    Click the <strong style="color:var(--magenta);">headset icon</strong> to start chatting instantly.
                </p>
                <button onclick="if(window.toggleSupportChat)toggleSupportChat();" style="display:inline-flex;align-items:center;gap:9px;padding:12px 28px;background:linear-gradient(135deg,var(--cyan),var(--magenta));border:none;border-radius:8px;color:white;font-weight:700;font-size:.95rem;cursor:pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    Open Live Chat
                </button>
            </div>

            <!-- Info cards -->
            <div class="sp-live-grid">
                <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:16px;">
                    <div style="font-weight:600;color:var(--text-primary);font-size:.88rem;margin-bottom:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--cyan)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:6px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Response Time
                    </div>
                    <p style="color:var(--text-secondary);font-size:.82rem;margin:0;line-height:1.55;">Usually under 5 minutes during business hours.</p>
                </div>
                <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:16px;">
                    <div style="font-weight:600;color:var(--text-primary);font-size:.88rem;margin-bottom:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:6px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Business Hours
                    </div>
                    <p style="color:var(--text-secondary);font-size:.82rem;margin:0;line-height:1.55;">Mon–Fri: 9AM–6PM (UTC). Weekends limited.</p>
                </div>
            </div>

            <div style="margin-top:16px;padding:14px 16px;background:color-mix(in srgb,var(--purple) 7%,transparent);border:1px solid color-mix(in srgb,var(--purple) 18%,transparent);border-radius:10px;font-size:.84rem;color:var(--text-secondary);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color:var(--purple)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:7px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                For non-urgent issues, please <a href="/support/new" style="color:var(--purple);">create a ticket</a> for a faster, tracked response.
            </div>
        </div><!-- /main content -->
</div><!-- /sp-layout -->

<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}</style>

<?php View::endSection(); ?>
