<?php use Core\View; use Core\Helpers; use Core\GoogleOAuth; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
/* ===== Auth Split Panel ===== */
.auth-split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: calc(100vh - 62px);
}

/* Left visual panel */
.auth-visual {
    background: var(--bg-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 40px;
    position: relative;
    overflow: hidden;
    border-right: 1px solid var(--border-color);
}

.auth-visual-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
}

.auth-visual-orb-1 {
    width: 320px; height: 320px;
    background: rgba(153, 69, 255, 0.22);
    top: -80px; left: -80px;
}

.auth-visual-orb-2 {
    width: 250px; height: 250px;
    background: rgba(0, 240, 255, 0.16);
    bottom: -60px; right: -60px;
}

.auth-visual-icon {
    width: 96px; height: 96px;
    background: rgba(153, 69, 255, 0.12);
    border: 1px solid rgba(153, 69, 255, 0.3);
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 32px;
    position: relative;
    z-index: 1;
    box-shadow: 0 0 40px rgba(153, 69, 255, 0.2), 0 0 80px rgba(0, 240, 255, 0.08);
}

.auth-visual-title {
    font-family: var(--font-heading);
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-align: center;
    position: relative;
    z-index: 1;
    margin-bottom: 12px;
}

.auth-visual-sub {
    font-size: 0.9rem;
    color: var(--text-secondary);
    text-align: center;
    max-width: 300px;
    line-height: 1.6;
    position: relative;
    z-index: 1;
}

.auth-feature-list {
    margin-top: 36px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 300px;
}

.auth-feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--text-secondary);
}

.auth-feature-icon {
    width: 32px; height: 32px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Right form panel */
.auth-form-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 50px;
    background: var(--bg-primary);
}

.auth-form-inner {
    width: 100%;
    max-width: 420px;
}

.auth-form-title {
    font-family: var(--font-heading);
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    margin-bottom: 6px;
    color: var(--text-primary);
}

.auth-form-sub {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 32px;
}

/* OR divider */
.auth-divider {
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 20px 0;
    color: var(--text-secondary);
    font-size: 12px;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

/* Google button */
.auth-google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 11px 20px;
    border-radius: var(--radius-full);
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-primary);
    font-family: var(--font-heading);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.auth-google-btn:hover {
    border-color: var(--purple);
    background: rgba(153, 69, 255, 0.06);
    color: var(--text-primary);
    box-shadow: var(--glow-purple);
}

[data-theme="light"] .auth-visual {
    background: var(--bg-secondary);
}

@media (max-width: 860px) {
    .auth-split {
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .auth-visual {
        padding: 40px 30px;
        border-right: none;
        border-bottom: 1px solid var(--border-color);
    }

    .auth-visual-orb-1 { width: 200px; height: 200px; }
    .auth-visual-orb-2 { width: 160px; height: 160px; }
    .auth-feature-list { display: none; }

    .auth-form-panel {
        padding: 40px 24px;
    }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="auth-split">
    <!-- Left Visual Panel -->
    <div class="auth-visual">
        <div class="auth-visual-orb auth-visual-orb-1"></div>
        <div class="auth-visual-orb auth-visual-orb-2"></div>

        <div class="auth-visual-icon">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="url(#ag1)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <defs><linearGradient id="ag1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#9945ff"/><stop offset="100%" stop-color="#00f0ff"/></linearGradient></defs>
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
        </div>

        <div class="auth-visual-title"><?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'MyMultiBranch') ?></div>
        <p class="auth-visual-sub">Your unified platform for 12+ powerful tools. One login, full access.</p>

        <div class="auth-feature-list">
            <div class="auth-feature-item">
                <div class="auth-feature-icon" style="background: rgba(0, 240, 255, 0.1);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <span>Argon2id encryption &amp; CSRF protection</span>
            </div>
            <div class="auth-feature-item">
                <div class="auth-feature-icon" style="background: rgba(153, 69, 255, 0.1);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <span>Single sign-on across all 12+ projects</span>
            </div>
            <div class="auth-feature-item">
                <div class="auth-feature-icon" style="background: rgba(0, 255, 136, 0.1);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                </div>
                <span>Two-factor authentication (2FA)</span>
            </div>
            <div class="auth-feature-item">
                <div class="auth-feature-icon" style="background: rgba(255, 170, 0, 0.1);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span>Session alerts &amp; concurrent login detection</span>
            </div>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            <h1 class="auth-form-title">Welcome back</h1>
            <p class="auth-form-sub">Sign in to your account to continue</p>

            <?php if (Helpers::hasFlash('error')): ?>
                <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
            <?php endif; ?>
            
            <?php if (Helpers::hasFlash('success')): ?>
                <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
            <?php endif; ?>
            
            <?php if (Helpers::hasFlash('info')): ?>
                <div class="alert alert-info"><?= View::e(Helpers::getFlash('info')) ?></div>
            <?php endif; ?>

            <?php if (GoogleOAuth::isEnabled()): ?>
            <a href="/auth/google" class="auth-google-btn">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" fill-rule="evenodd">
                        <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                    </g>
                </svg>
                Continue with Google
            </a>
            <div class="auth-divider">or</div>
            <?php endif; ?>

            <?php
            $loginRedirect = '';
            if (!empty($_GET['redirect'])) {
                $loginRedirect = '?redirect=' . urlencode($_GET['redirect']);
            } elseif (!empty($_GET['return'])) {
                $loginRedirect = '?return=' . urlencode($_GET['return']);
            }
            ?>
            <form method="POST" action="<?= htmlspecialchars('/login' . $loginRedirect) ?>">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?= View::old('email') ?>" placeholder="you@example.com" required autocomplete="email">
                    <?php if (View::hasError('email')): ?>
                        <div class="form-error"><?= View::error('email') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" style="margin: 0;" for="password">Password</label>
                        <a href="/forgot-password" style="font-size: 12px; color: var(--purple);">Forgot password?</a>
                    </div>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="••••••••" required minlength="1" autocomplete="current-password">
                    <?php if (View::hasError('password')): ?>
                        <div class="form-error"><?= View::error('password') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="remember">
                        <span>Keep me signed in</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px 20px; font-size: 14px; border-radius: var(--radius-full);">
                    Sign In
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; color: var(--text-secondary); font-size: 14px;">
                Don't have an account? <a href="/register" style="color: var(--purple); font-weight: 600;">Create one free</a>
            </p>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
